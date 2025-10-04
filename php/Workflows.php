<?php
// php/classes/Workflows.php
class Workflows
{
    /** ISO datetime helper */
    public static function nowIso(): string {
        return date('Y-m-d');
    }

    /** Snapshot aus Template (array) bauen */
    public static function buildSnapshot(array $tpl): array
    {
        $steps = array_map(function ($s) {
            return [
                'step_id'            => $s['id'],
                'label'              => $s['label'],
                'index'              => intval($s['index'] ?? 0),
                'role'               => $s['role'],
                'orgScope'           => $s['orgScope'] ?? 'any',         // 'any' | 'same_org_only'
                'required'           => !empty($s['required']),
                'locksAfterApproval' => !empty($s['locksAfterApproval']),
                'state'              => 'pending',                        // pending|approved|rejected
                'approvedBy'         => null,
                'approvedAt'         => null,
                'comment'            => null,
            ];
        }, DB::doc2Arr($tpl['steps'] ?? []));

        $wf = [
            'workflow_id'        => $tpl['id'],
            'status'             => 'in_progress',                       // in_progress|verified|locked
            'isLocked'           => false,
            'verifiedAt'         => null,
            'steps'              => array_values($steps),
            'pendingAssignments' => self::makeAssignments($steps),
        ];
        return $wf;
    }

    /** Assignments aus Steps bauen */
    public static function makeAssignments(array $steps): array
    {
        return array_map(function ($s) {
            return [
                'stepId'   => $s['step_id'],
                'roles'    => [$s['role']],
                'orgScope' => $s['orgScope'],
            ];
        }, $steps);
    }

    /** Pending-Assignments auffrischen (z.B. nach Approve/Reset) */
    public static function refreshAssignments(array $workflow): array
    {
        $pending = [];
        foreach ($workflow['steps'] as $s) {
            if (($s['state'] ?? 'pending') === 'pending') {
                $pending[] = [
                    'stepId'   => $s['step_id'],
                    'roles'    => [$s['role']],
                    'orgScope' => $s['orgScope'] ?? 'any',
                ];
            }
        }
        $workflow['pendingAssignments'] = $pending;
        return $workflow;
    }

    /** Status + verifiedAt + isLocked neu berechnen */
    public static function recomputeStatus(array $workflow): array
    {
        $allRequiredApproved = true;
        $isLocked = false;
        foreach ($workflow['steps'] as $s) {
            if (!empty($s['locksAfterApproval']) && ($s['state'] ?? 'pending') === 'approved') {
                $isLocked = true;
            }
            if (!empty($s['required']) && ($s['state'] ?? 'pending') !== 'approved') {
                $allRequiredApproved = false;
            }
        }

        $workflow['isLocked'] = $isLocked;
        if ($allRequiredApproved) {
            $workflow['status'] = 'verified';
            $workflow['verifiedAt'] = self::nowIso();
        } else {
            // locked bleibt als Status optional bestehen; wir lassen "in_progress" wenn nicht explizit locked
            $workflow['status'] = $isLocked ? 'locked' : 'in_progress';
            if (!empty($workflow['verifiedAt'])) {
                $workflow['verifiedAt'] = null; // falls vorher verified, aber nun wieder offen
            }
        }
        return $workflow;
    }

    /** Prüfen, ob User step approven darf (Rolle/OU/Phase) */
    public static function canApprove(array $activity, array $workflow, string $stepId, array $user): bool
    {
        // 1) Step finden & pending?
        $step = null;
        foreach ($workflow['steps'] as $s) {
            if ($s['step_id'] === $stepId) { $step = $s; break; }
        }
        if (!$step || ($step['state'] ?? 'pending') !== 'pending') return false;

        // 2) Rolle
        $userRoles = $user['roles'] ?? [];
        if (!in_array($step['role'], $userRoles, true)) return false;

        // 3) OU-Scope
        if (($step['orgScope'] ?? 'any') === 'same_org_only') {
            $uOrg = $user['units'] ?? [];
            $aOrg = $activity['units'] ?? [];
            if (!self::hasIntersection($uOrg, $aOrg)) return false;
        }

        // 4) Phasenregel: Alle required Steps mit niedrigeren index müssen approved sein
        $targetIdx = intval($step['index'] ?? 0);
        foreach ($workflow['steps'] as $s) {
            if (intval($s['index'] ?? 0) < $targetIdx && !empty($s['required'])) {
                if (($s['state'] ?? 'pending') !== 'approved') return false;
            }
        }

        // Gleiche Phase (parallele Steps) brauchen keine Freigabe untereinander
        return true;
    }

    /** Step als approved markieren, Locks/Status/Assignments aktualisieren */
    public static function approveStep(array $activity, array $workflow, string $stepId, array $user): array
    {
        if (!self::canApprove($activity, $workflow, $stepId, $user)) {
            throw new RuntimeException('Not allowed to approve this step');
        }

        // approve
        foreach ($workflow['steps'] as &$s) {
            if ($s['step_id'] === $stepId) {
                $s['state']      = 'approved';
                $s['approvedBy'] = $user['username'] ?? null;
                $s['approvedAt'] = self::nowIso();
            }
        }
        unset($s);

        // assignments & status
        $workflow = self::refreshAssignments($workflow);
        $workflow = self::recomputeStatus($workflow);
        return $workflow;
    }

    /** Schritte ab gegebener Phase zurücksetzen (z. B. nach Edit) */
    public static function resetFromPhase(array $workflow, int $thresholdPhase): array
    {
        foreach ($workflow['steps'] as &$s) {
            if (intval($s['index'] ?? 0) >= $thresholdPhase) {
                $s['state']      = 'pending';
                $s['approvedBy'] = null;
                $s['approvedAt'] = null;
                $s['comment']    = null;
            }
        }
        unset($s);
        $workflow['isLocked']   = false;
        $workflow['verifiedAt'] = null;
        $workflow['status']     = 'in_progress';
        return self::refreshAssignments($workflow);
    }

    /** Kleinhelfer: Schnittmenge */
    private static function hasIntersection(array $a, array $b): bool
    {
        if (!$a || !$b) return false;
        $set = array_fill_keys($a, true);
        foreach ($b as $x) if (isset($set[$x])) return true;
        return false;
    }

    /** Liefert das nächste prüfbare Step-Objekt für den User oder null */
    public static function getActionableStep(array $activity, array $user): ?array {
        $wf = $activity['workflow'] ?? null;
        if (!$wf || empty($wf['steps'])) return null;

        // get index of first pending step
        $firstPendingIndex = null;
        foreach ($wf['steps'] as $index => $s) {
            if (($s['state'] ?? 'pending') === 'pending') {
                $firstPendingIndex = intval($index);
                break;
            }
        }

        // nächster pending Step, den der User prüfen DARF (Rolle/OU/Phase)
        foreach ($wf['steps'] as $s) {
            if (!isset($s['step_id']) || !isset($s['label'])) continue;
            if ($firstPendingIndex !== null && intval($s['index'] ?? 0) > $firstPendingIndex) {
                // nur bis zur ersten pending Phase
                break;
            }
            if (($s['state'] ?? 'pending') !== 'pending') continue;
            if (!self::canApprove($activity, $wf, $s['step_id'], $user)) continue;
            return $s; // erstes match
        }
        return null;
    }

    /** Liefert flachen Progress für UI: [{id,label,state,index}] */
    public static function getProgress(array $workflow): array {
        $out = [];
        $pendingIndex = null;
        foreach ($workflow['steps'] as $s) {
            if (!isset($s['step_id']) || !isset($s['label'])) continue;
            $out[] = [
                'id'    => $s['step_id'],
                'label' => $s['label'],
                'state' => $s['state'] ?? 'pending',
                'index' => intval($s['index'] ?? 0),
                'required' => !empty($s['required']),
                'future' => $pendingIndex !== null && intval($s['index'] ?? 0) > $pendingIndex,
                'orgScope' => $s['orgScope'] ?? 'any',
            ];
            if (($s['state'] ?? 'pending') === 'pending') {
                $pendingIndex = intval($s['index'] ?? 0);
            }
        }
        // sort nach index, dann Einfügereihenfolge
        usort($out, fn($a,$b) => ($a['index'] <=> $b['index']));
        return $out;
    }
}