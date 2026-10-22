<?php

/** Author requests are independent of original news content and editorial texts. */
class NewsRequests
{
    public static function canCreate(Settings $settings): bool
    {
        return isset($_SESSION['username']) && ($settings->hasPermission('news.add') || $settings->hasPermission('news.edit'));
    }

    public static function isAuthor(array $news): bool
    {
        return !empty($_SESSION['username']) && ($news['created_by'] ?? null) === $_SESSION['username'];
    }

    public static function canEdit(array $news, Settings $settings): bool
    {
        return isset($_SESSION['username']) && ($settings->hasPermission('news.edit') ||
            (self::isAuthor($news) && $settings->hasPermission('news.edit-own')));
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['news_csrf'])) $_SESSION['news_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['news_csrf'];
    }

    public static function validCsrf($token): bool
    {
        return is_string($token) && !empty($_SESSION['news_csrf']) && hash_equals($_SESSION['news_csrf'], $token);
    }

    public static function read(array $news): array
    {
        return array_map(static fn($request) => DB::doc2Arr($request), array_values(DB::doc2Arr($news['outlet_requests'] ?? [])));
    }

    public static function label($localized): string
    {
        $localized = DB::doc2Arr($localized ?? []);
        return lang($localized['en'] ?? '', ($localized['de'] ?? '') !== '' ? $localized['de'] : null);
    }

    public static function locked(array $request): bool
    {
        return !in_array($request['status'] ?? '', ['requested', 'withdrawn'], true) ||
            ($request['source'] ?? 'author') !== 'author' || !empty($request['started_at']);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'requested' => lang('Requested', 'Beantragt'),
            'withdrawn' => lang('Withdrawn', 'Zurückgezogen'),
            'declined' => lang('Declined by the editorial team', 'Von der Redaktion abgelehnt'),
            'in_progress' => lang('In progress', 'In Bearbeitung'),
            'ready' => lang('Ready', 'Fertig vorbereitet'),
            'pending_approval' => lang('Awaiting author review', 'Wartet auf Autorenprüfung'),
            'approved' => lang('Approved', 'Bestätigt'),
            'published' => lang('Published', 'Veröffentlicht'),
            default => lang('Managed by the editorial team', 'In redaktioneller Bearbeitung'),
        };
    }

    /** Include inactive outlets only if they already belong to this news item. */
    public static function rows(array $configuration, array $news): array
    {
        $requests = array_column(self::read($news), null, 'outlet_id');
        $rows = [];
        foreach ($configuration['outlets'] as $outlet) {
            $request = $requests[$outlet['id']] ?? null;
            if (empty($outlet['active']) && $request === null) continue;
            $rows[] = ['outlet' => $outlet, 'request' => $request];
            unset($requests[$outlet['id']]);
        }
        foreach ($requests as $id => $request) {
            $rows[] = ['outlet' => ['id' => $id, 'name' => ['en' => $id], 'active' => false], 'request' => $request];
        }
        return $rows;
    }

    /** Never accept client-supplied status, source, timestamps or editorial content. */
    public static function reconcile($selection, array $configuration, array $news, string $actor, string $now, int $originalRevision): array
    {
        if (($news['created_by'] ?? $actor) !== $actor) {
            throw new InvalidArgumentException(lang('Only the author can change outlet requests here.', 'Nur die Autorin oder der Autor kann hier Outlet-Anfragen ändern.'));
        }
        if (!is_array($selection)) {
            throw new InvalidArgumentException(lang('Invalid outlet selection.', 'Ungültige Outlet-Auswahl.'));
        }
        $outlets = array_column($configuration['outlets'], null, 'id');
        $requests = array_column(self::read($news), null, 'outlet_id');
        $selected = [];
        foreach ($selection as $id) {
            if (!is_string($id) || !preg_match('/^[a-f0-9]{24}$/', $id)) {
                throw new InvalidArgumentException(lang('Invalid outlet selection.', 'Ungültige Outlet-Auswahl.'));
            }
            $request = $requests[$id] ?? null;
            if (empty($outlets[$id]['active']) && ($request === null || ($request['status'] ?? '') === 'withdrawn')) {
                throw new InvalidArgumentException(lang('An outlet is no longer available. Reload the news form.', 'Ein Outlet ist nicht mehr verfügbar. Bitte lade das News-Formular neu.'));
            }
            $selected[$id] = true;
        }
        $added = [];
        $withdrawn = [];
        foreach ($requests as $id => &$request) {
            if (self::locked($request)) continue;
            if (($request['status'] ?? '') === 'requested' && !isset($selected[$id])) {
                $request['status'] = 'withdrawn';
                $request['withdrawn_at'] = $now;
                $withdrawn[] = $id;
            }
        }
        unset($request);
        foreach ($selected as $id => $_) {
            if (isset($requests[$id]) && ($requests[$id]['status'] ?? '') !== 'withdrawn') continue;
            $request = $requests[$id] ?? [
                'outlet_id' => $id,
                'source' => 'author',
                'requested_by' => $actor,
                'requested_at' => $now,
            ];
            // A withdrawn request with editorial work remains locked, even if forged into the form.
            if (isset($requests[$id]) && self::locked($requests[$id])) continue;
            if (isset($requests[$id])) $request['resubmitted_at'] = $now;
            $request['status'] = 'requested';
            $request['original_revision'] = $originalRevision;
            $requests[$id] = $request;
            $added[] = $id;
        }
        return ['requests' => array_values($requests), 'added' => $added, 'withdrawn' => $withdrawn];
    }

    /** One inbox message per change, grouping all affected outlets. */
    public static function notify(DB $db, array $news, string $id, array $changes, array $configuration): void
    {
        if (empty($changes['added']) && empty($changes['withdrawn'])) return;
        $outlets = array_column($configuration['outlets'], null, 'id');
        $messages = [];
        foreach (['en', 'de'] as $language) {
            $parts = [];
            foreach (['added', 'withdrawn'] as $action) {
                $names = [];
                foreach ($changes[$action] as $outletId) {
                    $name = $outlets[$outletId]['name'] ?? [];
                    $names[] = e(($name[$language] ?? '') ?: ($name['en'] ?? $outletId));
                }
                if ($names) $parts[] = ($action === 'added'
                    ? ($language === 'de' ? 'Beantragt: ' : 'Requested: ')
                    : ($language === 'de' ? 'Zurückgezogen: ' : 'Withdrawn: ')) . implode(', ', $names);
            }
            $title = $language === 'de' ? ($news['title_de'] ?? '') : '';
            $title = $title ?: ($news['title'] ?? '');
            $messages[$language] = ($language === 'de' ? 'Outlet-Anfragen für die News ' : 'Outlet requests for news ') .
                '<b>' . e($title) . '</b> (' . e($news['created_by']) . '): ' . implode('; ', $parts);
        }
        $db->addMessages('right:news.editorial', $messages['en'], $messages['de'], 'news', '/news/view/' . $id);
    }
}
