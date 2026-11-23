<?php

require_once __DIR__ . '/NewsOutlets.php';
require_once __DIR__ . '/rich-text.php';
require_once __DIR__ . '/csrf.php';

/** The outlet lifecycle: requests, editorial drafts and shared rules. */
class NewsEditorial
{
    public static function usesOutletWorkflow(array $news): bool
    {
        return ($news['publication_mode'] ?? null) === 'outlets';
    }

    public static function workflowAvailable(array $configuration, array $news = []): bool
    {
        // Deactivating outlets for new requests must not change existing publication rules.
        return (!empty($configuration['enabled']) && !empty($configuration['outlets'])) ||
            self::usesOutletWorkflow($news) || self::requests($news) !== [];
    }

    public static function internalOnly(array $news): bool
    {
        if (array_key_exists('internal_only', $news)) return $news['internal_only'] === true;
        if (self::usesOutletWorkflow($news) || ($news['visibility'] ?? '') === 'public') return false;
        // Legacy requests remain visible without automatically releasing their original.
        foreach (self::requests($news) as $request) {
            if (!in_array($request['status'] ?? '', ['withdrawn', 'declined'], true)) return false;
        }
        return isset($news['_id']);
    }

    public static function canChooseInternalOnly(array $news): bool
    {
        if (self::internalOnly($news)) return true;
        foreach (self::requests($news) as $request) {
            if (self::locked($request) && !in_array($request['status'] ?? '', ['withdrawn', 'declined'], true)) return false;
        }
        return true;
    }

    /** Old originals keep their release until the author changes the outlet selection. */
    public static function publicationFields(array $input, array $configuration, ?array $news = null): array
    {
        $available = self::workflowAvailable($configuration, $news ?? []);
        $changed = ($input['publication_choice_changed'] ?? null) === '1';
        if ($available && ($news === null || self::usesOutletWorkflow($news) || $changed || ($input['internal_only'] ?? null) === '1')) {
            $value = $input['internal_only'] ?? (self::internalOnly($news ?? []) ? '1' : '0');
            if (!in_array($value, ['0', '1'], true)) {
                throw new InvalidArgumentException(t('news.invalid_outlet_selection'));
            }
            $internalOnly = $value === '1';
            if ($internalOnly && !self::canChooseInternalOnly($news ?? [])) {
                throw new InvalidArgumentException(t('news.the_editorial_team_has_already_started_work_contact_them_to_stop_t'));
            }
            if ($internalOnly && !empty($input['outlets'])) {
                throw new InvalidArgumentException(t('news.choose_either_internal_only_or_editorial_outlets'));
            }
            return ['publication_mode' => 'outlets', 'internal_only' => $internalOnly];
        }
        if ($news !== null && $available) {
            return ['visibility' => $news['visibility'] ?? 'internal'];
        }
        $visibility = $input['visibility'] ?? $news['visibility'] ?? 'internal';
        if (!in_array($visibility, ['internal', 'public'], true)) {
            throw new InvalidArgumentException(t('news.invalid_news_visibility'));
        }
        return ['visibility' => $visibility];
    }

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

    /** Old author-approved requests are final releases too; no database migration needed. */
    public static function requests(array $news): array
    {
        $requests = [];
        foreach (DB::doc2Arr($news['outlet_requests'] ?? []) as $request) {
            $request = DB::doc2Arr($request);
            if (($request['status'] ?? '') === 'approved') {
                $request['status'] = 'released';
                $request['release_mode'] ??= 'author_approved';
            }
            $requests[] = $request;
        }
        return $requests;
    }

    public static function locked(array $request): bool
    {
        return !in_array($request['status'] ?? '', ['requested', 'withdrawn'], true) ||
            ($request['source'] ?? 'author') !== 'author' || !empty($request['started_at']);
    }

    private static function status(string $status): array
    {
        return match ($status) {
            'requested' => [t('common.requested'), 'badge', 'ph-hourglass'],
            'withdrawn' => [t('projects.withdrawn'), 'badge muted', 'ph-x-circle'],
            'declined' => [t('news.declined_by_the_editorial_team'), 'badge danger', 'ph-x-circle'],
            'in_progress' => [t('common.in_progress'), 'badge signal', 'ph-spinner'],
            'ready' => [t('news.ready'), 'badge success', 'ph-check-circle'],
            'pending_approval' => [t('news.awaiting_author_review'), 'badge signal', 'ph-hourglass'],
            'approved', 'released' => [t('news.released'), 'badge success', 'ph-check-circle'],
            'published' => [t('news.published'), 'badge success', 'ph-check-circle'],
            default => [t('news.managed_by_the_editorial_team'), 'badge', 'ph-circle'],
        };
    }

    public static function statusLabel(string $status): string { return self::status($status)[0]; }
    public static function statusClass(string $status): string { return self::status($status)[1]; }
    public static function statusIcon(string $status): string { return self::status($status)[2]; }

    public static function isReleased(array $request): bool
    {
        return in_array($request['status'] ?? '', ['approved', 'released'], true);
    }

    public static function prepared(array $news): array
    {
        return array_values(array_filter(self::requests($news), static fn($request) =>
            in_array($request['status'] ?? '', ['ready', 'pending_approval'], true)));
    }

    /** The same text requirement applies when finishing, reviewing or releasing a draft. */
    public static function assertReady(array $contribution, array $outlet): void
    {
        $fields = NewsOutlets::fields($outlet);
        $required = in_array('content', $fields, true) ? ['content'] : $fields;
        foreach ($required as $field) {
            $format = $field === 'content' ? ($contribution['format'] ?? 'plain') : 'plain';
            if (rich_text_has_text($contribution[$field]['en'] ?? '', $format) || rich_text_has_text($contribution[$field]['de'] ?? '', $format)) return;
        }
        throw new InvalidArgumentException(t('news.please_enter_text_in_the_configured_fields_before_completing_this'));
    }

    public static function releaseLabel(string $mode): string
    {
        return match ($mode) {
            'author_approved' => t('news.released_by_the_author'),
            'without_response' => t('news.released_without_an_author_response'),
            default => t('news.released_without_author_review'),
        };
    }

    /** Include inactive outlets only if they already belong to this news item. */
    public static function rows(array $configuration, array $news): array
    {
        if (!self::workflowAvailable($configuration, $news)) return [];
        $requests = array_column(self::requests($news), null, 'outlet_id');
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
    public static function reconcileRequests($selection, array $configuration, array $news, string $actor, string $now): array
    {
        if (($news['created_by'] ?? $actor) !== $actor) {
            throw new InvalidArgumentException(t('news.only_the_author_can_change_outlet_requests_here'));
        }
        if (!is_array($selection)) {
            throw new InvalidArgumentException(t('news.invalid_outlet_selection'));
        }
        if ($selection && !self::workflowAvailable($configuration, $news)) {
            throw new InvalidArgumentException(t('news.the_editorial_news_workflow_is_disabled'));
        }
        $outlets = array_column($configuration['outlets'], null, 'id');
        $requests = array_column(self::requests($news), null, 'outlet_id');
        $selected = [];
        foreach ($selection as $id) {
            if (!is_string($id) || !preg_match('/^[a-f0-9]{24}$/', $id)) {
                throw new InvalidArgumentException(t('news.invalid_outlet_selection'));
            }
            $request = $requests[$id] ?? null;
            if (empty($outlets[$id]['active']) && ($request === null || ($request['status'] ?? '') === 'withdrawn')) {
                throw new InvalidArgumentException(t('news.an_outlet_is_no_longer_available_reload_the_news_form'));
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
            $requests[$id] = $request;
            $added[] = $id;
        }
        return ['requests' => array_values($requests), 'added' => $added, 'withdrawn' => $withdrawn];
    }

    /** One inbox message per change, grouping all affected outlets. */
    public static function notifyRequests(DB $db, array $news, string $id, array $changes, array $configuration): void
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

    /** A form only conflicts with changes to the data it can actually overwrite. */
    public static function editToken(array $news, string $scope): string
    {
        if ($scope === 'original' || $scope === 'connections') {
            $fields = ['persons', 'activities', 'projects', 'events', 'infrastructures', 'topics', 'featured'];
            if ($scope === 'original') $fields = array_merge($fields,
                ['type', 'title', 'title_de', 'teaser', 'teaser_de', 'content', 'content_de', 'date', 'visibility', 'publication_mode', 'internal_only']);
            $state = array_intersect_key($news, array_flip($fields));
            if ($scope === 'original') {
                // Author selection changes conflict; editorial progress and additions do not.
                $state['author_requests'] = [];
                foreach (self::requests($news) as $request) {
                    if (($request['source'] ?? 'author') !== 'author' && ($request['requested_by'] ?? null) !== ($news['created_by'] ?? '')) continue;
                    $state['author_requests'][$request['outlet_id']] = array_intersect_key($request,
                        array_flip(['requested_at', 'withdrawn_at', 'resubmitted_at']));
                }
            }
            ksort($state);
        } else {
            $requests = array_column(self::requests($news), null, 'outlet_id');
            $contributions = self::contributions($news);
            if (str_starts_with($scope, 'outlet:')) {
                $id = substr($scope, 7);
                $state = [$requests[$id] ?? null, $contributions[$id] ?? null, self::internalOnly($news)];
            } else {
                $state = [$requests, $contributions, $news['author_reviews'] ?? [], self::internalOnly($news)];
            }
        }
        return hash_hmac('sha256', json_encode([(string) ($news['_id'] ?? ''), $scope, $state], JSON_THROW_ON_ERROR), csrf_token('news'));
    }

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
        if (!is_string($value)) throw new InvalidArgumentException(t('news.invalid_editorial_text'));
        $value = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $limit) throw new InvalidArgumentException(t('news.the_editorial_text_is_too_long'));
        return $value;
    }

    private static function localized($value, int $limit): array
    {
        if (!is_array($value)) throw new InvalidArgumentException(t('news.invalid_localized_editorial_text'));
        return ['en' => self::text($value['en'] ?? '', $limit), 'de' => self::text($value['de'] ?? '', $limit)];
    }

    public static function history(array $news): array
    {
        return array_map(static fn($event) => DB::doc2Arr($event), array_values(DB::doc2Arr($news['editorial_history'] ?? [])));
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'add' => t('news.outlet_added'),
            'start' => t('news.editing_started'),
            'reopen' => t('news.editing_reopened'),
            'save' => t('news.draft_saved'),
            'ready' => t('news.editorial_work_completed'),
            'decline' => t('news.outlet_declined'),
            'connections' => t('news.shared_connections_updated'),
            'request_review' => t('news.author_review_requested'),
            'approve_review' => t('news.contributions_released_by_the_author'),
            'request_changes' => t('news.author_requested_changes'),
            'cancel_review' => t('news.author_review_ended'),
            'release' => t('news.prepared_outlets_released'),
            'revise' => t('news.outlet_reopened_for_revision'),
            default => t('news.editorial_change'),
        };
    }

    public static function apply(string $action, array $input, array $news, array $configuration, string $actor, string $now): array
    {
        if (!in_array($action, ['add', 'start', 'reopen', 'save', 'ready', 'decline'], true)) {
            throw new InvalidArgumentException(t('news.unknown_editorial_action'));
        }
        if (!self::workflowAvailable($configuration, $news)) {
            throw new InvalidArgumentException(t('news.the_editorial_news_workflow_is_disabled'));
        }
        if (NewsEditorial::internalOnly($news)) {
            throw new InvalidArgumentException(t('news.the_author_selected_internal_only_outlet_contributions_cannot_be_e'));
        }
        $id = self::text($input['outlet_id'] ?? '', 24);
        if (!preg_match('/^[a-f0-9]{24}$/', $id)) throw new InvalidArgumentException(t('news.invalid_outlet'));
        $outlets = array_column($configuration['outlets'], null, 'id');
        $requests = array_column(NewsEditorial::requests($news), null, 'outlet_id');
        $contributions = self::contributions($news);
        $request = $requests[$id] ?? null;
        $before = $request['status'] ?? null;
        $originalRevision = (int) ($news['original_revision'] ?? 1);

        if ($action === 'add') {
            if ($request !== null || empty($outlets[$id]['active'])) {
                throw new InvalidArgumentException(t('news.this_outlet_already_exists_or_is_inactive'));
            }
            $request = ['outlet_id' => $id, 'source' => 'editorial', 'requested_by' => $actor, 'requested_at' => $now];
        } elseif ($request === null) {
            throw new InvalidArgumentException(t('news.outlet_request_not_found'));
        }

        if ($action === 'start' && $before !== 'requested') {
            throw new InvalidArgumentException(t('news.this_request_can_no_longer_be_started'));
        }
        if ($action === 'reopen' && (!in_array($before, ['withdrawn', 'declined'], true) || empty($outlets[$id]['active']))) {
            throw new InvalidArgumentException(t('news.this_outlet_cannot_be_reopened'));
        }
        if (in_array($action, ['save', 'ready'], true) && !in_array($before, ['in_progress', 'ready'], true)) {
            throw new InvalidArgumentException(t('news.start_editing_this_outlet_first'));
        }
        if ($action === 'decline' && !in_array($before, ['requested', 'in_progress', 'ready'], true)) {
            throw new InvalidArgumentException(t('news.this_outlet_can_no_longer_be_declined_here'));
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
            if ($reason === '') throw new InvalidArgumentException(t('news.please_give_a_reason_for_declining_this_outlet'));
            $request['status'] = 'declined';
            $request['decline_reason'] = $reason;
            $request['declined_at'] = $now;
            $request['declined_by'] = $actor;
            unset($request['ready_at'], $request['ready_by']);
        } else {
            if (!isset($contributions[$id])) throw new InvalidArgumentException(t('news.editorial_draft_not_found'));
            $textFields = NewsOutlets::fields($outlets[$id] ?? []);
            $format = $contributions[$id]['format'] ?? 'plain';
            if (in_array('content', $textFields, true) && array_key_exists('content', $input)) {
                $format = $input['format'] ?? $format;
                if (!in_array($format, ['plain', 'html'], true)) throw new InvalidArgumentException(t('news.invalid_contribution_format'));
            }
            $texts = [];
            foreach ($textFields as $field) {
                $limit = ['title' => 240, 'teaser' => 2000, 'content' => 1000000][$field];
                // A field enabled after this form was opened may not have been submitted.
                $value = array_key_exists($field, $input) ? $input[$field] : ($contributions[$id][$field] ?? []);
                $texts[$field] = self::localized($value, $limit);
                if ($field === 'content' && $format === 'html') $texts[$field] = array_map('sanitize_rich_text', $texts[$field]);
            }
            if ($action === 'ready') self::assertReady(array_merge($contributions[$id], $texts, ['format' => $format]), $outlets[$id] ?? []);
            // Hidden fields are preserved and cannot be overwritten by submitted values.
            $contributions[$id] = array_merge($contributions[$id], $texts, [
                'format' => $format,
                'revision' => (int) ($contributions[$id]['revision'] ?? 0) + 1,
                'original_revision' => $originalRevision,
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
            'from' => $before, 'to' => $request['status']];
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
            if (!is_array($values)) throw new InvalidArgumentException(t('news.invalid_news_connections'));
            $clean = [];
            foreach ($values as $value) {
                $value = self::text($value, 200);
                if ($value === '') continue;
                if ($field !== 'infrastructures' && !preg_match('/^[a-f0-9]{24}$/', $value)) {
                    throw new InvalidArgumentException(t('news.invalid_linked_entity'));
                }
                $clean[] = $value;
            }
            $result[$field] = array_values(array_unique($clean));
        }
        return $result;
    }
}
