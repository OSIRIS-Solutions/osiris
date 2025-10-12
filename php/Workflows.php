<?php
// php/classes/Workflows.php
class Workflows
{
    public static function nowIso(): string
    {
        return date('Y-m-d');
    }

    /** Initialzustand für neue Aktivität */
    public static function buildInitialState(array $tpl): array
    {
        return [
            'workflow_id' => $tpl['id'],
            'status'      => 'in_progress',
            'verifiedAt'  => null,
            'approved'    => []     // keine Steps approved
        ];
    }

    /** UI-View: merge Template + approved[] -> [{id,label,index,state,required}] */
    public static function view(array $tpl, array $wf): array
    {
        $approved = DB::doc2Arr($wf['approved'] ?? []);
        $approvedSet = array_flip($approved);
        $steps = [];
        foreach (DB::doc2Arr($tpl['steps'] ?? []) as $s) {
            $steps[] = [
                'id'       => $s['id'],
                'label'    => $s['label'],
                'index'    => intval($s['index'] ?? 0),
                'required' => !empty($s['required']),
                'state'    => isset($approvedSet[$s['id']]) ? 'approved' : 'pending',
                'orgScope' => ($s['orgScope'] ?? 'any'),
            ];
        }
        usort($steps, fn($a, $b) => $a['index'] <=> $b['index']);
        return $steps;
    }

    /** Darf User diesen Step jetzt freigeben? (Rolle/OU/Phasen) */
    public static function canApprove(array $activity, array $tpl, array $wf, string $stepId, array $user): bool
    {
        $approved = DB::doc2Arr($wf['approved'] ?? []);
        $step = null;
        $steps = DB::doc2Arr($tpl['steps'] ?? []);
        foreach ($steps as $s) if ($s['id'] === $stepId) {
            $step = $s;
            break;
        }
        if (!$step) return false;

        // bereits approved?
        if (in_array($stepId, $approved, true)) return false;

        // Rolle
        if (!in_array(($step['role'] ?? ''), ($user['roles'] ?? []), true)) return false;

        // OU
        if (($step['orgScope'] ?? 'any') === 'same_org_only') {
            $uOrg = $user['units'] ?? [];
            $aOrg = DB::doc2Arr($activity['units'] ?? []);
            if (!self::intersects($uOrg, $aOrg)) return false;
        }

        // Phase: alle required Steps mit kleinerem index müssen approved sein
        $idx = intval($step['index'] ?? 0);
        $approvedSet = array_flip($approved);
        foreach ($steps as $s) {
            if (!empty($s['required']) && intval($s['index'] ?? 0) < $idx) {
                if (!array_key_exists($s['id'], $approvedSet)) return false;
            }
        }
        return true;
    }

    /** Approve: stepId in approved[] aufnehmen + Status neu berechnen */
    public static function approveStep(array $activity, array $tpl, array $wf, string $stepId, array $user): array
    {
        if (!self::canApprove($activity, $tpl, $wf, $stepId, $user)) {
            throw new RuntimeException('Not allowed to approve this step');
        }
        $approved = DB::doc2Arr($wf['approved'] ?? []);

        $approved[] = $stepId;
        $approved = array_values(array_unique($approved)); // Safety
        $wf['approved'] = $approved;
        // verified?
        $allReqApproved = true;
        $steps = DB::doc2Arr($tpl['steps'] ?? []);
        $approvedSet = array_flip($approved);
        foreach ($steps as $s) {
            if (!empty($s['required']) && !isset($approvedSet[$s['id']])) {
                $allReqApproved = false;
                break;
            }
        }
        // check if we have a rejection that needs to be cleared
        if (!empty($wf['rejectedDetails'])) {
            // check if rejected step is now approved
            if (in_array($wf['rejectedDetails']['stepId'], $approved, true)) {
                // clear rejection
                unset($wf['rejectedDetails']);
            }
        }
        if ($allReqApproved) {
            $wf['status']     = 'verified';
            $wf['verifiedAt'] = $wf['verifiedAt'] ?? self::nowIso();
            // helper states
            $wf['currentIndex'] = null;
        } else {
            $wf['status']     = 'in_progress';
            $wf['verifiedAt'] = null;
            // helper states
            $wf['currentIndex'] = self::currentPhaseIndex($tpl, $wf);
        }
        return $wf;
    }

    /** Optional: nur für UI-Rendering, welcher Step ist "current" (erste pending-Phase) */
    public static function firstPendingId(array $tpl, array $wf): ?string
    {
        $steps = self::view($tpl, $wf);
        foreach ($steps as $s) if ($s['state'] === 'pending') return $s['id'];
        return null;
    }

    private static function intersects(array $a, array $b): bool
    {
        if (!$a || !$b) return false;
        $set = array_fill_keys($a, true);
        foreach ($b as $x) if (isset($set[$x])) return true;
        return false;
    }

    public static function currentPhaseIndex(array $tpl, array $wf): ?int
    {
        $steps = DB::doc2Arr($tpl['steps'] ?? []);
        if (!$steps) return null;

        $approved = DB::doc2Arr($wf['approved'] ?? []);
        $approved = array_flip($approved);
        // alle Phasen ermitteln
        $indices = [];
        foreach ($steps as $s) {
            $indices[] = intval($s['index'] ?? 0);
        }
        $indices = array_values(array_unique($indices));
        sort($indices);

        foreach ($indices as $idx) {
            // sind alle required Steps in früheren Phasen approved?
            $ok = true;
            foreach ($steps as $s) {
                if (!empty($s['required']) && intval($s['index'] ?? 0) < $idx) {
                    if (!isset($approved[$s['id']])) {
                        $ok = false;
                        break;
                    }
                }
            }
            if (!$ok) continue;

            // gibt es in dieser Phase noch pending Steps?
            $hasPending = false;
            foreach ($steps as $s) {
                if (intval($s['index'] ?? 0) === $idx && !isset($approved[$s['id']])) {
                    $hasPending = true;
                    break;
                }
            }
            if ($hasPending) return $idx;
        }
        return null;
    }
}
