<?php

require_once __DIR__ . '/NewsEditorial.php';

/** Optional author review: confirmation completes release of the submitted outlet texts. */
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

    private static function snapshot(array $request, array $contribution, array $outlet): array
    {
        $fields = NewsOutlets::fields($outlet);
        $snapshot = ['outlet_id' => $request['outlet_id'], 'name' => $outlet['name'] ?? ['en' => $request['outlet_id']],
            'fields' => $fields, 'revision' => (int) $contribution['revision']];
        // Older plain-text review snapshots remain comparable without a migration.
        if (($contribution['format'] ?? 'plain') === 'html') $snapshot['format'] = 'html';
        foreach ($fields as $field) $snapshot[$field] = $contribution[$field] ?? ['en' => '', 'de' => ''];
        return $snapshot;
    }

    public static function matches(array $review, array $news): bool
    {
        $contributions = NewsEditorial::contributions($news);
        $requests = array_column(NewsEditorial::requests($news), null, 'outlet_id');
        $found = false;
        foreach ($review['contributions'] as $snapshot) {
            $id = $snapshot['outlet_id'];
            $found = true;
            if (!isset($contributions[$id], $requests[$id])) return false;
            if ((int) ($contributions[$id]['revision'] ?? -1) !== (int) $snapshot['revision']) return false;
        }
        return $found;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => lang('Awaiting response', 'Rückmeldung ausstehend'),
            'approved' => lang('Released by the author', 'Von Autor:in freigegeben'),
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
        if (NewsEditorial::internalOnly($news)) throw new InvalidArgumentException(lang('This news is internal only.', 'Diese News ist nur intern.'));
        $requests = array_column(NewsEditorial::requests($news), null, 'outlet_id');
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
                NewsEditorial::assertReady($contributions[$id], $outlets[$id] ?? []);
                $snapshots[] = self::snapshot($request, $contributions[$id], $outlets[$id] ?? []);
                $request['status'] = 'pending_approval';
                $request['author_review_id'] = $reviewId;
            }
            unset($request);
            if (!$snapshots) throw new InvalidArgumentException(lang('Mark at least one outlet ready first.', 'Markiere zuerst mindestens ein Outlet als fertig.'));
            $reviews[] = ['id' => $reviewId, 'status' => 'pending', 'requested_by' => $actor, 'requested_at' => $now,
                'contributions' => $snapshots];
            $event['review_id'] = $reviewId;
        } elseif ($authorAction || $action === 'cancel_review') {
            if ($pending === null || !is_string($input['review_id'] ?? null) || $input['review_id'] !== $pending['id']) {
                throw new InvalidArgumentException(lang('This review is no longer open. Reload the news item.', 'Diese Prüfung ist nicht mehr offen. Bitte lade die News neu.'));
            }
            if ($action === 'approve_review' && !self::matches($pending, $news)) {
                throw new InvalidArgumentException(lang('The outlet texts have changed. The editorial team needs to prepare a new review.', 'Die Outlet-Texte wurden geändert. Die Redaktion muss eine neue Prüfung vorbereiten.'));
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
                $requests[$id]['status'] = match ($action) { 'approve_review' => 'released', 'request_changes' => 'in_progress', default => 'ready' };
                if ($action === 'approve_review') {
                    $requests[$id]['release_mode'] = 'author_approved';
                    $requests[$id]['released_at'] = $now;
                    $requests[$id]['released_by'] = $actor;
                }
                if ($action !== 'approve_review') unset($requests[$id]['author_review_id']);
            }
            $event['review_id'] = $pending['id'];
        } elseif ($action === 'release') {
            if ($pending !== null && ($input['confirm_without_response'] ?? null) !== '1') {
                throw new InvalidArgumentException(lang('Confirm that you want to release without the pending author response.', 'Bestätige, dass du ohne die ausstehende Rückmeldung freigeben möchtest.'));
            }
            $releasedIds = [];
            foreach ($requests as $id => &$request) {
                if (!in_array($request['status'], ['ready', 'pending_approval'], true)) continue;
                if (!isset($contributions[$id])) throw new InvalidArgumentException(lang('Editorial draft not found.', 'Redaktioneller Entwurf nicht gefunden.'));
                NewsEditorial::assertReady($contributions[$id], $outlets[$id] ?? []);
                if ($request['status'] === 'pending_approval') {
                    $reviewIndex = array_search($request['author_review_id'] ?? '', array_column($reviews, 'id'), true);
                    if ($reviewIndex === false || !self::matches($reviews[$reviewIndex], $news)) {
                        throw new InvalidArgumentException(lang('The reviewed version has changed. Prepare the outlet again.', 'Die geprüfte Fassung wurde geändert. Bereite das Outlet erneut vor.'));
                    }
                }
                $request['release_mode'] = match ($request['status']) {
                    'pending_approval' => 'without_response', default => 'without_review',
                };
                $request['status'] = 'released';
                $request['released_by'] = $actor;
                $request['released_at'] = $now;
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

    /** Called only after a successful database write. */
    public static function notify(DB $db, string $action, array $news): void
    {
        $text = match ($action) {
            'request_review' => ['Please review the prepared outlet contributions for', 'Bitte prüfe die vorbereiteten Outlet-Fassungen zur News'],
            'approve_review' => ['The author has released the outlet contributions for', 'Die Outlet-Fassungen wurden von der Autorin oder dem Autor freigegeben. News:'],
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
