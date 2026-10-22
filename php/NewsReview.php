<?php

require_once __DIR__ . '/NewsEditorial.php';

/** Optional, version-bound author reviews. No deadlines or automatic approvals. */
class NewsReview
{
    private static function arrayValue($value)
    {
        if (!is_iterable($value)) return $value;
        $result = [];
        foreach ($value as $key => $item) $result[$key] = self::arrayValue($item);
        return $result;
    }

    public static function read(array $news): array
    {
        return array_values(self::arrayValue($news['author_reviews'] ?? []));
    }

    public static function pending(array $news): ?array
    {
        foreach (self::read($news) as $review) if ($review['status'] === 'pending') return $review;
        return null;
    }

    public static function prepared(array $news): array
    {
        return array_values(array_filter(NewsRequests::read($news), static fn($request) =>
            in_array($request['status'] ?? '', ['ready', 'approved', 'pending_approval'], true)));
    }

    private static function snapshot(array $request, array $contribution, array $outlet): array
    {
        $fields = NewsOutlets::fields($outlet);
        $snapshot = ['outlet_id' => $request['outlet_id'], 'name' => $outlet['name'] ?? ['en' => $request['outlet_id']],
            'fields' => $fields, 'revision' => (int) $contribution['revision']];
        foreach ($fields as $field) $snapshot[$field] = $contribution[$field] ?? ['en' => '', 'de' => ''];
        return $snapshot;
    }

    public static function matches(array $review, array $news, array $configuration, ?string $onlyOutlet = null): bool
    {
        if ((int) $review['original_revision'] !== (int) ($news['original_revision'] ?? 1)) return false;
        $contributions = NewsEditorial::contributions($news);
        $outlets = array_column($configuration['outlets'], null, 'id');
        $requests = array_column(NewsRequests::read($news), null, 'outlet_id');
        $found = false;
        foreach ($review['contributions'] as $snapshot) {
            $id = $snapshot['outlet_id'];
            if ($onlyOutlet !== null && $id !== $onlyOutlet) continue;
            $found = true;
            if (!isset($contributions[$id], $requests[$id])) return false;
            if (self::snapshot($requests[$id], $contributions[$id], $outlets[$id] ?? []) != $snapshot) return false;
        }
        return $found;
    }

    private static function assertReady(array $contribution, array $outlet, array $news): void
    {
        if ((int) ($contribution['original_revision'] ?? 1) !== (int) ($news['original_revision'] ?? 1)) {
            throw new InvalidArgumentException(lang('Review the changed original and mark the outlet ready again.', 'Bitte prüfe den geänderten Originalbeitrag und markiere das Outlet erneut als fertig.'));
        }
        $fields = NewsOutlets::fields($outlet);
        $required = in_array('content', $fields, true) ? ['content'] : $fields;
        foreach ($required as $field) {
            if (trim($contribution[$field]['en'] ?? '') !== '' || trim($contribution[$field]['de'] ?? '') !== '') return;
        }
        throw new InvalidArgumentException(lang('A prepared outlet needs text in its configured fields.', 'Ein fertiges Outlet benötigt Text in seinen konfigurierten Feldern.'));
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => lang('Awaiting response', 'Rückmeldung ausstehend'),
            'approved' => lang('Confirmed by the author', 'Von Autor:in bestätigt'),
            'changes_requested' => lang('Changes requested', 'Änderung angefragt'),
            'cancelled' => lang('Ended by the editorial team', 'Von der Redaktion beendet'),
            'closed_without_response' => lang('Closed without a response', 'Ohne Rückmeldung abgeschlossen'),
            default => $status,
        };
    }

    public static function apply(string $action, array $input, array $news, array $configuration, string $actor, bool $isEditor, string $now): array
    {
        $authorAction = in_array($action, ['approve_review', 'request_changes'], true);
        if ($authorAction ? $actor !== ($news['created_by'] ?? null) : !$isEditor) {
            throw new InvalidArgumentException(lang('You cannot perform this review action.', 'Du darfst diese Prüfaktion nicht ausführen.'));
        }
        if (!in_array($action, ['request_review', 'approve_review', 'request_changes', 'cancel_review', 'release', 'revise'], true)) {
            throw new InvalidArgumentException(lang('Unknown review action.', 'Unbekannte Prüfaktion.'));
        }
        if (NewsRequests::internalOnly($news)) throw new InvalidArgumentException(lang('This news is internal only.', 'Diese News ist nur intern.'));
        $requests = array_column(NewsRequests::read($news), null, 'outlet_id');
        $contributions = NewsEditorial::contributions($news);
        $outlets = array_column($configuration['outlets'], null, 'id');
        $reviews = self::read($news);
        $pending = self::pending($news);
        $history = NewsEditorial::history($news);
        $event = ['action' => $action, 'actor' => $actor, 'at' => $now];
        $pendingIndex = $pending === null ? null : array_search($pending['id'], array_column($reviews, 'id'), true);

        if ($action === 'request_review') {
            if ($pending !== null) throw new InvalidArgumentException(lang('An author review is already open.', 'Es läuft bereits eine Autorenprüfung.'));
            if (empty($news['created_by'])) throw new InvalidArgumentException(lang('This news has no author to contact.', 'Für diese News ist keine Autorin oder kein Autor hinterlegt.'));
            $snapshots = [];
            $reviewId = bin2hex(random_bytes(12));
            foreach ($requests as $id => &$request) {
                if ($request['status'] !== 'ready') continue;
                if (!isset($contributions[$id])) throw new InvalidArgumentException(lang('Editorial draft not found.', 'Redaktioneller Entwurf nicht gefunden.'));
                self::assertReady($contributions[$id], $outlets[$id] ?? [], $news);
                $snapshots[] = self::snapshot($request, $contributions[$id], $outlets[$id] ?? []);
                $request['status'] = 'pending_approval';
                $request['author_review_id'] = $reviewId;
            }
            unset($request);
            if (!$snapshots) throw new InvalidArgumentException(lang('Mark at least one outlet ready first.', 'Markiere zuerst mindestens ein Outlet als fertig.'));
            $reviews[] = ['id' => $reviewId, 'status' => 'pending', 'requested_by' => $actor, 'requested_at' => $now,
                'original_revision' => (int) ($news['original_revision'] ?? 1), 'contributions' => $snapshots];
            $event['review_id'] = $reviewId;
        } elseif ($authorAction || $action === 'cancel_review') {
            if ($pending === null || !is_string($input['review_id'] ?? null) || $input['review_id'] !== $pending['id']) {
                throw new InvalidArgumentException(lang('This review is no longer open. Reload the news item.', 'Diese Prüfung ist nicht mehr offen. Bitte lade die News neu.'));
            }
            if ($action === 'approve_review' && !self::matches($pending, $news, $configuration)) {
                throw new InvalidArgumentException(lang('The source or outlet configuration has changed. The editorial team needs to prepare a new review.', 'Der Originalbeitrag oder die Outlet-Konfiguration wurde geändert. Die Redaktion muss eine neue Prüfung vorbereiten.'));
            }
            $status = match ($action) { 'approve_review' => 'approved', 'request_changes' => 'changes_requested', default => 'cancelled' };
            $comment = $input['comment'] ?? '';
            if ($action === 'request_changes') {
                if (!is_string($comment) || trim($comment) === '' || (function_exists('mb_strlen') ? mb_strlen($comment) : strlen($comment)) > 4000) {
                    throw new InvalidArgumentException(lang('Describe the requested change (up to 4,000 characters).', 'Beschreibe die gewünschte Änderung (maximal 4.000 Zeichen).'));
                }
                $reviews[$pendingIndex]['comment'] = trim($comment);
                $event['comment'] = trim($comment);
            }
            $reviews[$pendingIndex]['status'] = $status;
            $reviews[$pendingIndex]['closed_by'] = $actor;
            $reviews[$pendingIndex]['closed_at'] = $now;
            foreach ($pending['contributions'] as $snapshot) {
                $id = $snapshot['outlet_id'];
                if (($requests[$id]['status'] ?? '') !== 'pending_approval' || ($requests[$id]['author_review_id'] ?? '') !== $pending['id']) {
                    throw new InvalidArgumentException(lang('The review has changed. Reload the news item.', 'Die Prüfung wurde geändert. Bitte lade die News neu.'));
                }
                $requests[$id]['status'] = match ($action) { 'approve_review' => 'approved', 'request_changes' => 'in_progress', default => 'ready' };
                if ($action !== 'approve_review') unset($requests[$id]['author_review_id']);
            }
            $event['review_id'] = $pending['id'];
        } elseif ($action === 'release') {
            if ($pending !== null && ($input['confirm_without_response'] ?? null) !== '1') {
                throw new InvalidArgumentException(lang('Confirm that you want to release without the pending author response.', 'Bestätige, dass du ohne die ausstehende Rückmeldung freigeben möchtest.'));
            }
            $releasedIds = [];
            foreach ($requests as $id => &$request) {
                if (!in_array($request['status'], ['ready', 'approved', 'pending_approval'], true)) continue;
                if (!isset($contributions[$id])) throw new InvalidArgumentException(lang('Editorial draft not found.', 'Redaktioneller Entwurf nicht gefunden.'));
                self::assertReady($contributions[$id], $outlets[$id] ?? [], $news);
                if (in_array($request['status'], ['approved', 'pending_approval'], true)) {
                    $reviewIndex = array_search($request['author_review_id'] ?? '', array_column($reviews, 'id'), true);
                    if ($reviewIndex === false || !self::matches($reviews[$reviewIndex], $news, $configuration, $request['status'] === 'approved' ? $id : null)) {
                        throw new InvalidArgumentException(lang('The reviewed version has changed. Prepare the outlet again.', 'Die geprüfte Fassung wurde geändert. Bereite das Outlet erneut vor.'));
                    }
                }
                $request['release_mode'] = match ($request['status']) {
                    'approved' => 'author_approved', 'pending_approval' => 'without_response', default => 'without_review',
                };
                $request['status'] = 'released';
                $request['released_by'] = $actor;
                $request['released_at'] = $now;
                $request['released_revision'] = (int) $contributions[$id]['revision'];
                $releasedIds[] = $id;
            }
            unset($request);
            if (!$releasedIds) throw new InvalidArgumentException(lang('No prepared outlets are available for release.', 'Es sind keine fertigen Outlets zur Freigabe vorhanden.'));
            if ($pending !== null) {
                $reviews[$pendingIndex]['status'] = 'closed_without_response';
                $reviews[$pendingIndex]['closed_by'] = $actor;
                $reviews[$pendingIndex]['closed_at'] = $now;
                $event['review_id'] = $pending['id'];
            }
            $event['outlet_ids'] = $releasedIds;
            $event['release_modes'] = array_map(static fn($id) => $requests[$id]['release_mode'], $releasedIds);
        } else {
            $id = $input['outlet_id'] ?? null;
            if (!is_string($id) || !isset($requests[$id]) || !in_array($requests[$id]['status'], ['approved', 'released'], true)) {
                throw new InvalidArgumentException(lang('This outlet cannot be revised here.', 'Dieses Outlet kann hier nicht erneut bearbeitet werden.'));
            }
            $requests[$id]['status'] = 'in_progress';
            unset($requests[$id]['author_review_id'], $requests[$id]['release_mode'], $requests[$id]['released_by'], $requests[$id]['released_at'], $requests[$id]['released_revision']);
            $event['outlet_id'] = $id;
        }
        $history[] = $event;
        return ['outlet_requests' => array_values($requests), 'author_reviews' => $reviews, 'editorial_history' => $history];
    }

    public static function releaseLabel(string $mode): string
    {
        return match ($mode) {
            'author_approved' => lang('Released after author confirmation', 'Nach Bestätigung durch Autor:in freigegeben'),
            'without_response' => lang('Released without an author response', 'Ohne Rückmeldung redaktionell freigegeben'),
            default => lang('Released without author review', 'Ohne Autorenprüfung freigegeben'),
        };
    }

    /** Called only after a successful database write. */
    public static function notify(DB $db, string $action, array $news): void
    {
        $text = match ($action) {
            'request_review' => ['Please review the prepared outlet contributions for', 'Bitte prüfe die vorbereiteten Outlet-Fassungen zur News'],
            'approve_review' => ['The author has confirmed the outlet contributions for', 'Die Outlet-Fassungen wurden von der Autorin oder dem Autor bestätigt. News:'],
            'request_changes' => ['The author has requested changes to the outlet contributions for', 'Die Autorin oder der Autor bittet um Änderungen an den Outlet-Fassungen. News:'],
            'cancel_review' => ['The editorial team has ended the author review for', 'Die Redaktion hat die Autorenprüfung beendet. News:'],
            'release' => ['The editorial team has released the prepared outlet contributions for', 'Die Redaktion hat die vorbereiteten Outlet-Fassungen freigegeben. News:'],
            default => null,
        };
        if ($text === null) return;
        $group = in_array($action, ['approve_review', 'request_changes'], true) ? 'right:news.editorial' : 'user:' . ($news['created_by'] ?? '');
        if ($group === 'user:') return;
        $en = $text[0] . ' <b>' . e($news['title'] ?? '') . '</b>.';
        $de = $text[1] . ' <b>' . e(($news['title_de'] ?? '') ?: ($news['title'] ?? '')) . '</b>.';
        $db->addMessages($group, $en, $de, 'news', '/news/view/' . (string) $news['_id'] . '#news-author-review');
    }
}
