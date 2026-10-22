<?php

require_once __DIR__ . '/NewsRequests.php';
require_once __DIR__ . '/NewsOutlets.php';

/** Editorial mutations only return editorial fields, never the author's original text. */
class NewsEditorial
{
    public static function contributions(array $news): array
    {
        $result = [];
        foreach (DB::doc2Arr($news['editorial_contributions'] ?? []) as $contribution) {
            $contribution = DB::doc2Arr($contribution);
            foreach (NewsOutlets::TEXT_FIELDS as $field) $contribution[$field] = DB::doc2Arr($contribution[$field] ?? []);
            $result[$contribution['outlet_id']] = $contribution;
        }
        return $result;
    }

    private static function text($value, int $limit): string
    {
        if (!is_string($value)) throw new InvalidArgumentException(lang('Invalid editorial text.', 'Ungültiger Redaktionstext.'));
        $value = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $limit) throw new InvalidArgumentException(lang('The editorial text is too long.', 'Der Redaktionstext ist zu lang.'));
        return $value;
    }

    private static function localized($value, int $limit): array
    {
        if (!is_array($value)) throw new InvalidArgumentException(lang('Invalid localized editorial text.', 'Ungültiger mehrsprachiger Redaktionstext.'));
        return ['en' => self::text($value['en'] ?? '', $limit), 'de' => self::text($value['de'] ?? '', $limit)];
    }

    public static function history(array $news): array
    {
        return array_map(static fn($event) => DB::doc2Arr($event), array_values(DB::doc2Arr($news['editorial_history'] ?? [])));
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'add' => lang('Outlet added', 'Outlet ergänzt'),
            'start' => lang('Editing started', 'Bearbeitung gestartet'),
            'reopen' => lang('Editing reopened', 'Bearbeitung wieder aufgenommen'),
            'save' => lang('Draft saved', 'Entwurf gespeichert'),
            'ready' => lang('Editorial work completed', 'Redaktionell fertiggestellt'),
            'decline' => lang('Outlet declined', 'Outlet abgelehnt'),
            'connections' => lang('Shared connections updated', 'Gemeinsame Verknüpfungen aktualisiert'),
            'request_review' => lang('Author review requested', 'Autorenprüfung angefordert'),
            'approve_review' => lang('Versions confirmed by the author', 'Fassungen von Autor:in bestätigt'),
            'request_changes' => lang('Author requested changes', 'Änderung von Autor:in angefragt'),
            'cancel_review' => lang('Author review ended', 'Autorenprüfung beendet'),
            'release' => lang('Prepared outlets released', 'Vorbereitete Outlets freigegeben'),
            'revise' => lang('Outlet reopened for revision', 'Outlet zur Überarbeitung geöffnet'),
            default => lang('Editorial change', 'Redaktionelle Änderung'),
        };
    }

    public static function apply(string $action, array $input, array $news, array $configuration, string $actor, string $now): array
    {
        if (!in_array($action, ['add', 'start', 'reopen', 'save', 'ready', 'decline'], true)) {
            throw new InvalidArgumentException(lang('Unknown editorial action.', 'Unbekannte Redaktionsaktion.'));
        }
        if (NewsRequests::internalOnly($news)) {
            throw new InvalidArgumentException(lang('The author selected internal only. Outlet contributions cannot be edited.', 'Die Autorin oder der Autor hat Nur intern gewählt. Outlet-Beiträge können nicht bearbeitet werden.'));
        }
        $id = self::text($input['outlet_id'] ?? '', 24);
        if (!preg_match('/^[a-f0-9]{24}$/', $id)) throw new InvalidArgumentException(lang('Invalid outlet.', 'Ungültiges Outlet.'));
        $outlets = array_column($configuration['outlets'], null, 'id');
        $requests = array_column(NewsRequests::read($news), null, 'outlet_id');
        $contributions = self::contributions($news);
        $request = $requests[$id] ?? null;
        $before = $request['status'] ?? null;
        $originalRevision = (int) ($news['original_revision'] ?? 1);

        if ($action === 'add') {
            if ($request !== null || empty($outlets[$id]['active'])) {
                throw new InvalidArgumentException(lang('This outlet already exists or is inactive.', 'Dieses Outlet ist bereits vorhanden oder deaktiviert.'));
            }
            $request = ['outlet_id' => $id, 'source' => 'editorial', 'requested_by' => $actor, 'requested_at' => $now, 'original_revision' => $originalRevision];
        } elseif ($request === null) {
            throw new InvalidArgumentException(lang('Outlet request not found.', 'Outlet-Anfrage nicht gefunden.'));
        }

        if ($action === 'start' && $before !== 'requested') {
            throw new InvalidArgumentException(lang('This request can no longer be started.', 'Diese Anfrage kann nicht mehr gestartet werden.'));
        }
        if ($action === 'reopen' && (!in_array($before, ['withdrawn', 'declined'], true) || empty($outlets[$id]['active']))) {
            throw new InvalidArgumentException(lang('This outlet cannot be reopened.', 'Dieses Outlet kann nicht wieder aufgenommen werden.'));
        }
        if (in_array($action, ['save', 'ready'], true) && !in_array($before, ['in_progress', 'ready'], true)) {
            throw new InvalidArgumentException(lang('Start editing this outlet first.', 'Starte zuerst die Bearbeitung dieses Outlets.'));
        }
        if ($action === 'decline' && !in_array($before, ['requested', 'in_progress', 'ready'], true)) {
            throw new InvalidArgumentException(lang('This outlet can no longer be declined here.', 'Dieses Outlet kann hier nicht mehr abgelehnt werden.'));
        }

        if (in_array($action, ['add', 'start', 'reopen'], true)) {
            if ($before === 'withdrawn') $request['source'] = 'editorial';
            $request['status'] = 'in_progress';
            $request['started_at'] ??= $now;
            $request['started_by'] ??= $actor;
            unset($request['decline_reason'], $request['declined_at'], $request['declined_by'], $request['ready_at'], $request['ready_by']);
            $contributions[$id] ??= [
                'outlet_id' => $id,
                'title' => ['en' => (string) ($news['title'] ?? ''), 'de' => (string) ($news['title_de'] ?? '')],
                'teaser' => ['en' => '', 'de' => ''],
                'content' => ['en' => '', 'de' => ''],
                'format' => 'plain',
                'revision' => 0,
                'original_revision' => $originalRevision,
                'created_by' => $actor,
                'created_at' => $now,
            ];
        } elseif ($action === 'decline') {
            $reason = self::text($input['decline_reason'] ?? '', 2000);
            if ($reason === '') throw new InvalidArgumentException(lang('Please give a reason for declining this outlet.', 'Bitte begründe die Ablehnung dieses Outlets.'));
            $request['status'] = 'declined';
            $request['decline_reason'] = $reason;
            $request['declined_at'] = $now;
            $request['declined_by'] = $actor;
            unset($request['ready_at'], $request['ready_by']);
        } else {
            if (!isset($contributions[$id])) throw new InvalidArgumentException(lang('Editorial draft not found.', 'Redaktioneller Entwurf nicht gefunden.'));
            $textFields = NewsOutlets::fields($outlets[$id] ?? []);
            $texts = [];
            foreach ($textFields as $field) {
                $limit = ['title' => 240, 'teaser' => 2000, 'content' => 100000][$field];
                // A field enabled after this form was opened may not have been submitted.
                $value = array_key_exists($field, $input) ? $input[$field] : ($contributions[$id][$field] ?? []);
                $texts[$field] = self::localized($value, $limit);
            }
            // A configured contribution needs a body; outlets without one need another text.
            $requiredTexts = isset($texts['content']) ? [$texts['content']] : $texts;
            $hasText = false;
            foreach ($requiredTexts as $text) {
                if ($text['en'] !== '' || $text['de'] !== '') $hasText = true;
            }
            if ($action === 'ready' && !$hasText) {
                throw new InvalidArgumentException(lang('Please enter a contribution before marking it ready.', 'Bitte erfasse einen Beitrag, bevor du ihn als fertig markierst.'));
            }
            $sourceRevision = (int) ($contributions[$id]['original_revision'] ?? 1);
            if (($input['source_reviewed'] ?? null) === '1') $sourceRevision = $originalRevision;
            if ($action === 'ready' && $sourceRevision < $originalRevision) {
                throw new InvalidArgumentException(lang('Review the changes to the original first.', 'Bitte prüfe zuerst die Änderungen am Originalbeitrag.'));
            }
            // Hidden fields are preserved and cannot be overwritten by submitted values.
            $contributions[$id] = array_merge($contributions[$id], $texts, [
                'revision' => (int) ($contributions[$id]['revision'] ?? 0) + 1,
                'original_revision' => $sourceRevision,
            ]);
            $request['status'] = $action === 'ready' ? 'ready' : 'in_progress';
            if ($action === 'ready') {
                $request['ready_at'] = $now;
                $request['ready_by'] = $actor;
            } else {
                unset($request['ready_at'], $request['ready_by']);
            }
        }
        if (isset($contributions[$id])) {
            $contributions[$id]['updated_by'] = $actor;
            $contributions[$id]['updated_at'] = $now;
        }
        $requests[$id] = $request;
        $history = self::history($news);
        $event = ['action' => $action, 'outlet_id' => $id, 'actor' => $actor, 'at' => $now,
            'from' => $before, 'to' => $request['status'], 'contribution_revision' => $contributions[$id]['revision'] ?? null];
        if ($action === 'decline') $event['reason'] = $request['decline_reason'];
        $history[] = $event;
        return ['outlet_requests' => array_values($requests), 'editorial_contributions' => array_values($contributions), 'editorial_history' => $history];
    }

    /** Shared links retain fields omitted by disabled feature sections. */
    public static function connections(array $input, array $news): array
    {
        $result = [];
        foreach (['persons', 'activities', 'projects', 'events', 'infrastructures'] as $field) {
            $values = array_key_exists($field, $input) ? $input[$field] : DB::doc2Arr($news[$field] ?? []);
            if ($values === '') $values = [];
            if (!is_array($values)) throw new InvalidArgumentException(lang('Invalid news connections.', 'Ungültige News-Verknüpfungen.'));
            $clean = [];
            foreach ($values as $value) {
                $value = self::text($value, 200);
                if ($value === '') continue;
                if ($field !== 'infrastructures' && !preg_match('/^[a-f0-9]{24}$/', $value)) {
                    throw new InvalidArgumentException(lang('Invalid linked entity.', 'Ungültige verknüpfte Entität.'));
                }
                $clean[] = $value;
            }
            $result[$field] = array_values(array_unique($clean));
        }
        return $result;
    }
}
