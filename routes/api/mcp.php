<?php

/**
 * Narrow, read-only API endpoints intended for MCP clients.
 *
 * These routes deliberately use fixed filters and projections. They must never
 * accept raw MongoDB queries or arbitrary field selections from the caller.
 */

include_once BASEPATH . '/php/ApiClient.php';

function mcp_request_id(): string
{
    if (empty($GLOBALS['MCP_REQUEST_ID'])) {
        $GLOBALS['MCP_REQUEST_ID'] = 'req_' . bin2hex(random_bytes(16));
    }
    return $GLOBALS['MCP_REQUEST_ID'];
}

function mcp_log_unexpected_error(\Throwable $error): void
{
    $message = preg_replace('/\s+/u', ' ', $error->getMessage());
    $entry = json_encode([
        'channel' => 'mcp',
        'request_id' => mcp_request_id(),
        'endpoint' => $GLOBALS['MCP_ENDPOINT'] ?? 'unknown',
        'client_id' => $GLOBALS['API_CLIENT']['client_id'] ?? null,
        'error_type' => get_class($error),
        'message' => substr($message ?? '', 0, 1000),
        'file' => $error->getFile(),
        'line' => $error->getLine(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    error_log($entry !== false ? $entry : '[mcp] Failed to encode error log entry.');
}

function mcp_clear_response_buffer(): void
{
    $baseLevel = $GLOBALS['MCP_BUFFER_BASE_LEVEL'] ?? null;
    if (!is_int($baseLevel)) {
        return;
    }
    while (ob_get_level() > $baseLevel) {
        ob_end_clean();
    }
}

function mcp_return_unexpected_error(\Throwable $error): void
{
    mcp_log_unexpected_error($error);
    if (($GLOBALS['MCP_RESPONSE_SENT'] ?? false) === true) {
        return;
    }
    mcp_return_json([
        'status' => 500,
        'error' => 'InternalServerError',
        'msg' => 'The request could not be completed.',
    ], 500);
}

function mcp_begin_request(string $endpoint): void
{
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(
        E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR |
        E_WARNING | E_USER_WARNING | E_RECOVERABLE_ERROR
    );

    $GLOBALS['MCP_ENDPOINT'] = $endpoint;
    $GLOBALS['MCP_RESPONSE_SENT'] = false;
    $GLOBALS['MCP_BUFFER_BASE_LEVEL'] = ob_get_level();
    ob_start();

    header('X-Request-ID: ' . mcp_request_id());
    header('X-Content-Type-Options: nosniff');

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        if (!in_array($severity, [E_WARNING, E_USER_WARNING, E_RECOVERABLE_ERROR], true)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });
    set_exception_handler(function (\Throwable $error) {
        mcp_return_unexpected_error($error);
    });
    register_shutdown_function(function () {
        if (($GLOBALS['MCP_RESPONSE_SENT'] ?? false) === true) {
            return;
        }
        $error = error_get_last();
        if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            return;
        }
        mcp_return_unexpected_error(new \ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    });
}

function mcp_api_key_check(string $scope): bool
{
    $Settings = new Settings();
    $configured = $Settings->get('apikey');
    $provided = ApiClient::requestSecret();

    if (!empty($configured) && $provided !== '' && hash_equals((string) $configured, $provided)) {
        return true;
    }

    $clients = new ApiClient();
    return $clients->authenticate('mcp', [$scope]);
}

function mcp_return_json(array $payload, int $status = 200): void
{
    $requestId = mcp_request_id();
    if ($status >= 400 && empty($payload['request_id'])) {
        $payload['request_id'] = $requestId;
    }

    $json = json_encode(
        $payload,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
    mcp_clear_response_buffer();
    $GLOBALS['MCP_RESPONSE_SENT'] = true;

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Request-ID: ' . $requestId);
    header('X-Content-Type-Options: nosniff');
    echo $json;
}

function mcp_project_projection(): array
{
    return [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
        'name' => 1,
        'acronym' => 1,
        'title' => 1,
        'status' => 1,
        'start_date' => 1,
        'end_date' => 1,
        'abstract' => 1,
        'topics' => ['$ifNull' => ['$topics', []]],
        'units' => ['$ifNull' => ['$units', []]],
        'persons' => [
            '$map' => [
                'input' => ['$ifNull' => ['$persons', []]],
                'as' => 'person',
                'in' => [
                    'user' => '$$person.user',
                    'name' => '$$person.name',
                    'role' => '$$person.role',
                ],
            ],
        ],
    ];
}

function mcp_parse_date(string $value): ?string
{
    $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = \DateTimeImmutable::getLastErrors();
    if (
        $date === false ||
        ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) ||
        $date->format('Y-m-d') !== $value
    ) {
        return null;
    }
    return $date->format('Y-m-d');
}

function mcp_text($value, int $maxLength = 1000): string
{
    $normalized = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $value)));
    $text = trim($normalized ?? '');
    return mb_substr($text, 0, $maxLength);
}

function mcp_catalog_limit(): int
{
    $limit = filter_var(
        $_GET['limit'] ?? 50,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 200]]
    );
    return $limit === false ? 0 : $limit;
}

function mcp_offset(): int
{
    $offset = filter_var(
        $_GET['offset'] ?? 0,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 1000000]]
    );
    return $offset === false ? -1 : $offset;
}

function mcp_pagination(int $total, int $offset, int $limit, int $count): array
{
    $hasMore = $offset + $count < $total;
    return [
        'count' => $count,
        'total' => $total,
        'offset' => $offset,
        'limit' => $limit,
        'has_more' => $hasMore,
        'next_offset' => $hasMore ? $offset + $count : null,
    ];
}

function mcp_activity_projection(): array
{
    return [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
        'type_id' => '$type',
        'subtype_id' => '$subtype',
        'type_label' => '$rendered.type',
        'subtype_label' => '$rendered.subtype',
        'title' => ['$ifNull' => ['$rendered.title', '$title']],
        'start_date' => 1,
        'end_date' => 1,
        'person_ids' => ['$ifNull' => ['$rendered.users', []]],
        'unit_ids' => ['$ifNull' => ['$units', []]],
        'citation' => '$rendered.plain',
        'doi' => 1,
        'pubmed' => 1,
        'affiliated' => ['$ifNull' => ['$affiliated', null]],
        'online_ahead_of_print' => ['$ifNull' => ['$epub', false]],
        'metrics' => [
            'impact_factor' => '$impact',
            'citation_count' => '$openalex.cited_by_count',
            'sjr' => '$metrics.sjr',
            'quartile' => '$quartile',
            'metrics_year' => '$metrics.year',
            'citation_count_updated_at' => '$openalex.fetched_at',
        ],
    ];
}

function mcp_activity_result($document, $DB, $Groups, array &$personCache, array &$unitCache): array
{
    $personIds = array_filter(
        DB::doc2Arr($document['person_ids'] ?? []),
        fn($personId) => is_string($personId) && $personId !== ''
    );
    $persons = [];
    foreach (array_values(array_unique($personIds)) as $personId) {
        if (!isset($personCache[$personId])) {
            $person = $DB->getPerson($personId);
            $personCache[$personId] = [
                'id' => $personId,
                'name' => $person['name'] ?? $personId,
            ];
        }
        $persons[] = $personCache[$personId];
    }

    $unitIds = DB::doc2Arr($document['unit_ids'] ?? []);
    $units = [];
    foreach (array_values(array_unique($unitIds)) as $unitId) {
        if (!isset($unitCache[$unitId])) {
            $unit = $Groups->getGroup($unitId);
            $unitCache[$unitId] = [
                'id' => $unitId,
                'name' => $unit['name'] ?? $unitId,
                'name_de' => $unit['name_de'] ?? null,
            ];
        }
        $units[] = $unitCache[$unitId];
    }

    $identifiers = [];
    foreach (['doi', 'pubmed'] as $identifier) {
        if (!empty($document[$identifier])) {
            $identifiers[$identifier] = mcp_text($document[$identifier], 300);
        }
    }

    $rawMetrics = DB::doc2Arr($document['metrics'] ?? []);
    $metrics = [];
    foreach (['impact_factor', 'sjr'] as $metric) {
        if (array_key_exists($metric, $rawMetrics) && is_numeric($rawMetrics[$metric])) {
            $metrics[$metric] = (float) $rawMetrics[$metric];
        }
    }
    foreach (['citation_count', 'metrics_year'] as $metric) {
        if (array_key_exists($metric, $rawMetrics) && is_numeric($rawMetrics[$metric])) {
            $metrics[$metric] = (int) $rawMetrics[$metric];
        }
    }
    foreach (['quartile', 'citation_count_updated_at'] as $metric) {
        if (array_key_exists($metric, $rawMetrics) && $rawMetrics[$metric] !== null && $rawMetrics[$metric] !== '') {
            $metrics[$metric] = mcp_text($rawMetrics[$metric], 100);
        }
    }

    return [
        'id' => $document['id'],
        'type' => [
            'id' => $document['type_id'] ?? null,
            'label' => mcp_text($document['type_label'] ?? $document['type_id'] ?? '', 200),
        ],
        'subtype' => [
            'id' => $document['subtype_id'] ?? null,
            'label' => mcp_text($document['subtype_label'] ?? $document['subtype_id'] ?? '', 200),
        ],
        'title' => mcp_text($document['title'] ?? '', 1000),
        'start_date' => $document['start_date'] ?? null,
        'end_date' => $document['end_date'] ?? null,
        'persons' => $persons,
        'units' => $units,
        'citation' => mcp_text($document['citation'] ?? '', 4000),
        'identifiers' => (object) $identifiers,
        'affiliated' => isset($document['affiliated'])
            ? (bool) $document['affiliated']
            : null,
        'online_ahead_of_print' => (bool) ($document['online_ahead_of_print'] ?? false),
        'metrics' => empty($metrics) ? null : $metrics,
    ];
}

function mcp_person_units($value, $Groups): array
{
    $today = date('Y-m-d');
    $units = [];
    foreach (DB::doc2Arr($value ?? []) as $assignment) {
        $assignment = DB::doc2Arr($assignment);
        if (!is_array($assignment) || empty($assignment['unit'])) {
            continue;
        }
        $start = $assignment['start'] ?? null;
        $end = $assignment['end'] ?? null;
        if ((!empty($start) && $start > $today) || (!empty($end) && $end < $today)) {
            continue;
        }
        $unitId = $assignment['unit'];
        $unit = $Groups->getGroup($unitId);
        $units[] = [
            'id' => $unitId,
            'name' => $unit['name'] ?? $unitId,
            'name_de' => $unit['name_de'] ?? null,
            'scientific' => (bool) ($assignment['scientific'] ?? false),
        ];
    }
    return $units;
}

function mcp_person_topics($value, $osiris): array
{
    $topicIds = array_values(array_filter(
        DB::doc2Arr($value ?? []),
        fn($topicId) => is_string($topicId) && $topicId !== ''
    ));
    if (empty($topicIds)) {
        return [];
    }
    $documents = $osiris->topics->find(
        ['id' => ['$in' => $topicIds]],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1]]
    )->toArray();
    $byId = [];
    foreach ($documents as $topic) {
        $byId[$topic['id']] = [
            'id' => $topic['id'],
            'name' => $topic['name'] ?? $topic['id'],
            'name_de' => $topic['name_de'] ?? null,
        ];
    }
    $topics = [];
    foreach ($topicIds as $topicId) {
        $topics[] = $byId[$topicId] ?? [
            'id' => $topicId,
            'name' => $topicId,
            'name_de' => null,
        ];
    }
    return $topics;
}

function mcp_person_summary($person, $Groups): array
{
    $name = $person['displayname'] ?? trim(
        ($person['first'] ?? '') . ' ' . ($person['last'] ?? '')
    );
    return [
        'id' => $person['username'],
        'name' => $name !== '' ? $name : $person['username'],
        'academic_title' => $person['academic_title'] ?? null,
        'position' => mcp_text($person['position'] ?? '', 300),
        'position_de' => mcp_text($person['position_de'] ?? '', 300),
        'active' => ($person['is_active'] ?? true) !== false,
        'units' => mcp_person_units($person['units'] ?? [], $Groups),
    ];
}

function mcp_matching_strings($value, string $query): array
{
    $matches = [];
    foreach (DB::doc2Arr($value ?? []) as $item) {
        if (is_string($item) && preg_match('/' . preg_quote($query, '/') . '/iu', $item)) {
            $matches[] = mcp_text($item, 300);
        }
    }
    return array_values(array_unique($matches));
}

function mcp_strings($value): array
{
    $strings = [];
    foreach (DB::doc2Arr($value ?? []) as $item) {
        if (is_string($item) && trim($item) !== '') {
            $strings[] = mcp_text($item, 300);
        }
    }
    return array_values(array_unique($strings));
}

Route::get('/api/mcp/instance', function () {
    mcp_begin_request('instance.get');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('catalogs.read')) {
        mcp_return_json([
            'status' => 403,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $affiliation = $Settings->get('affiliation_details', []);
    $rootUnit = $osiris->groups->findOne(
        ['level' => 0],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1]]
    );
    $topicsAvailable = $Settings->featureEnabled('topics') &&
        $osiris->topics->count(['inactive' => ['$ne' => true]]) > 0;

    $topicLabel = $Settings->get('topics_label', []);
    $topicLabel = is_array($topicLabel) ? $topicLabel : DB::doc2Arr($topicLabel);

    mcp_return_json([
        'status' => 200,
        'data' => [
            'instance' => [
                'id' => $affiliation['id'] ?? $rootUnit['id'] ?? null,
                'name' => $affiliation['name'] ?? $rootUnit['name'] ?? 'OSIRIS',
                'name_de' => $rootUnit['name_de'] ?? null,
                'osiris_version' => defined('OSIRIS_VERSION') ? OSIRIS_VERSION : null,
                'base_url' => rtrim(
                    $Settings->getRequestScheme() . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ROOTPATH,
                    '/'
                ),
                'default_language' => 'en',
                'available_languages' => ['en', 'de'],
                'timezone' => date_default_timezone_get(),
            ],
            'features' => [
                'projects' => $Settings->featureEnabled('projects'),
                'activities' => true,
                'topics' => $topicsAvailable,
                'units' => $osiris->groups->count() > 0,
                'infrastructures' => $Settings->featureEnabled('infrastructures'),
                'spectrum' => $Settings->featureEnabled('spectrum'),
            ],
            'catalogs' => [
                'topics' => [
                    'available' => $topicsAvailable,
                    'count' => $topicsAvailable
                        ? $osiris->topics->count(['inactive' => ['$ne' => true]])
                        : 0,
                    'label' => [
                        'en' => $topicLabel['en'] ?? 'Research Topics',
                        'de' => $topicLabel['de'] ?? 'Forschungsbereiche',
                    ],
                ],
                'units' => [
                    'available' => $osiris->groups->count() > 0,
                    'count' => $osiris->groups->count([
                        'inactive' => ['$ne' => true],
                        'hide' => ['$ne' => true],
                    ]),
                ],
                'activity_types' => [
                    'available' => $osiris->adminTypes->count() > 0,
                    'count' => $osiris->adminTypes->count(),
                ],
            ],
            'supported_project_filters' => array_values(array_filter([
                'query',
                'active_on',
                'status',
                $topicsAvailable ? 'topic' : null,
                'unit',
                'limit',
                'offset',
            ])),
            'supported_activity_filters' => array_values(array_filter([
                'query',
                'from_date',
                'to_date',
                'type',
                'subtype',
                'person',
                'unit',
                $topicsAvailable ? 'topic' : null,
                'include_unaffiliated',
                'include_online_ahead_of_print',
                'limit',
                'offset',
            ])),
            'supported_person_searches' => [
                'identity',
                'expertise',
            ],
            'pagination' => [
                'parameter' => 'offset',
                'max_page_size' => 50,
                'max_catalog_page_size' => 200,
            ],
        ],
    ]);
});

Route::get('/api/mcp/units', function () {
    mcp_begin_request('units.list');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('catalogs.read')) {
        mcp_return_json([
            'status' => 403,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $limit = mcp_catalog_limit();
    if ($limit === 0) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 200.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'q must not exceed 200 characters.',
        ], 400);
        return;
    }

    $filter = ['inactive' => ['$ne' => true], 'hide' => ['$ne' => true]];
    if ($query !== '') {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');
        $filter['$or'] = [
            ['id' => ['$regex' => $regex]],
            ['name' => ['$regex' => $regex]],
            ['name_de' => ['$regex' => $regex]],
        ];
    }

    $groups = $osiris->groups->find(
        $filter,
        [
            'sort' => ['level' => 1, 'order' => 1, 'name' => 1, 'id' => 1],
            'skip' => $offset,
            'limit' => $limit,
            'projection' => [
                '_id' => 0,
                'id' => 1,
                'name' => 1,
                'name_de' => 1,
                'parent' => 1,
                'unit' => 1,
                'level' => 1,
            ],
        ]
    )->toArray();

    $units = [];
    foreach ($groups as $group) {
        $pathIds = $Groups->getParents($group['id'], true);
        $path = [];
        foreach ($pathIds as $pathId) {
            $pathGroup = $Groups->getGroup($pathId);
            if (!empty($pathGroup['id'])) {
                $path[] = [
                    'id' => $pathGroup['id'],
                    'name' => $pathGroup['name'] ?? $pathGroup['id'],
                    'name_de' => $pathGroup['name_de'] ?? null,
                ];
            }
        }
        $units[] = [
            'id' => $group['id'],
            'name' => $group['name'] ?? $group['id'],
            'name_de' => $group['name_de'] ?? null,
            'type' => $group['unit'] ?? null,
            'parent_id' => $group['parent'] ?? null,
            'level' => $group['level'] ?? null,
            'path' => $path,
            'active' => true,
        ];
    }

    $response = [
        'status' => 200,
        'data' => $units,
    ] + mcp_pagination($osiris->groups->countDocuments($filter), $offset, $limit, count($units));
    mcp_return_json($response);
});

Route::get('/api/mcp/topics', function () {
    mcp_begin_request('topics.list');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('catalogs.read')) {
        mcp_return_json([
            'status' => 403,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $available = $Settings->featureEnabled('topics') &&
        $osiris->topics->count(['inactive' => ['$ne' => true]]) > 0;
    if (!$available) {
        mcp_return_json([
            'status' => 200,
            'count' => 0,
            'total' => 0,
            'offset' => 0,
            'limit' => 0,
            'has_more' => false,
            'next_offset' => null,
            'data' => [
                'available' => false,
                'reason' => 'The topics feature is not enabled for this OSIRIS instance.',
                'topics' => [],
            ],
        ]);
        return;
    }

    $limit = mcp_catalog_limit();
    if ($limit === 0) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 200.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'error' => 'InvalidParameter',
            'msg' => 'q must not exceed 200 characters.',
        ], 400);
        return;
    }

    $filter = ['inactive' => ['$ne' => true]];
    if ($query !== '') {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');
        $filter['$or'] = [
            ['id' => ['$regex' => $regex]],
            ['name' => ['$regex' => $regex]],
            ['name_de' => ['$regex' => $regex]],
            ['subtitle' => ['$regex' => $regex]],
            ['subtitle_de' => ['$regex' => $regex]],
            ['description' => ['$regex' => $regex]],
            ['description_de' => ['$regex' => $regex]],
        ];
    }

    $documents = $osiris->topics->find(
        $filter,
        [
            'sort' => ['order' => 1, 'name' => 1, 'id' => 1],
            'skip' => $offset,
            'limit' => $limit,
            'projection' => [
                '_id' => 0,
                'id' => 1,
                'name' => 1,
                'name_de' => 1,
                'subtitle' => 1,
                'subtitle_de' => 1,
                'description' => 1,
                'description_de' => 1,
            ],
        ]
    )->toArray();
    $topics = [];
    foreach ($documents as $topic) {
        $topics[] = [
            'id' => $topic['id'],
            'name' => $topic['name'] ?? $topic['id'],
            'name_de' => $topic['name_de'] ?? null,
            'subtitle' => mcp_text($topic['subtitle'] ?? '', 300),
            'subtitle_de' => mcp_text($topic['subtitle_de'] ?? '', 300),
            'description' => mcp_text($topic['description'] ?? ''),
            'description_de' => mcp_text($topic['description_de'] ?? ''),
        ];
    }

    $response = [
        'status' => 200,
        'data' => [
            'available' => true,
            'reason' => null,
            'topics' => $topics,
        ],
    ] + mcp_pagination($osiris->topics->countDocuments($filter), $offset, $limit, count($topics));
    mcp_return_json($response);
});

Route::get('/api/mcp/activity-types', function () {
    mcp_begin_request('activity_types.list');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('catalogs.read')) {
        mcp_return_json([
            'status' => 403,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $categories = $osiris->adminCategories->find(
        [],
        [
            'sort' => ['name' => 1],
            'projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1],
        ]
    )->toArray();
    $subtypes = $osiris->adminTypes->find(
        [],
        [
            'sort' => ['parent' => 1, 'name' => 1],
            'projection' => ['_id' => 0, 'id' => 1, 'parent' => 1, 'name' => 1, 'name_de' => 1],
        ]
    )->toArray();

    $subtypesByParent = [];
    foreach ($subtypes as $subtype) {
        $parent = $subtype['parent'] ?? '';
        if (!isset($subtypesByParent[$parent])) {
            $subtypesByParent[$parent] = [];
        }
        $subtypesByParent[$parent][] = [
            'id' => $subtype['id'],
            'name' => $subtype['name'] ?? $subtype['id'],
            'name_de' => $subtype['name_de'] ?? null,
        ];
    }

    $types = [];
    foreach ($categories as $category) {
        $types[] = [
            'id' => $category['id'],
            'name' => $category['name'] ?? $category['id'],
            'name_de' => $category['name_de'] ?? null,
            'subtypes' => $subtypesByParent[$category['id']] ?? [],
        ];
    }

    mcp_return_json([
        'status' => 200,
        'data' => [
            'available' => !empty($types),
            'types' => $types,
        ],
    ]);
});

Route::get('/api/mcp/persons', function () {
    mcp_begin_request('persons.search');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('persons.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) < 2 || mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'q must contain between 2 and 200 characters.',
        ], 400);
        return;
    }
    $limit = filter_var(
        $_GET['limit'] ?? 10,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 50]]
    );
    if ($limit === false) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 50.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $activeOnly = filter_var(
        $_GET['active_only'] ?? true,
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    );
    if ($activeOnly === null) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'active_only must be true or false.',
        ], 400);
        return;
    }

    $regex = new \MongoDB\BSON\Regex(preg_quote(mb_strtolower($query), '/'), 'i');
    $filter = [
        // 'hide' => ['$ne' => true],
        'username' => ['$exists' => true, '$ne' => ''],
        'search_text' => ['$regex' => $regex],
    ];
    if ($activeOnly) {
        $filter['is_active'] = ['$ne' => false];
    }
    $unit = trim((string) ($_GET['unit'] ?? ''));
    if ($unit !== '') {
        $filter['current_units'] = $unit;
    }

    $documents = $osiris->persons->find(
        $filter,
        [
            'sort' => ['displayname' => 1, 'last' => 1, 'first' => 1, 'username' => 1],
            'skip' => $offset,
            'limit' => $limit,
            'projection' => [
                '_id' => 0,
                'username' => 1,
                'displayname' => 1,
                'first' => 1,
                'last' => 1,
                'academic_title' => 1,
                'position' => 1,
                'position_de' => 1,
                'is_active' => 1,
                'units' => 1,
            ],
        ]
    )->toArray();

    $persons = [];
    foreach ($documents as $person) {
        if (!empty($person['username'])) {
            $persons[] = mcp_person_summary($person, $Groups);
        }
    }
    $response = [
        'status' => 200,
        'data' => $persons,
    ] + mcp_pagination($osiris->persons->countDocuments($filter), $offset, $limit, count($persons));
    mcp_return_json($response);
});

Route::get('/api/mcp/experts', function () {
    mcp_begin_request('experts.search');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('persons.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) < 2 || mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'q must contain between 2 and 200 characters.',
        ], 400);
        return;
    }
    $limit = filter_var(
        $_GET['limit'] ?? 10,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 25]]
    );
    if ($limit === false) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 25.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');
    $matchingTopics = $osiris->topics->find(
        ['$or' => [
            ['id' => ['$regex' => $regex]],
            ['name' => ['$regex' => $regex]],
            ['name_de' => ['$regex' => $regex]],
            ['subtitle' => ['$regex' => $regex]],
            ['subtitle_de' => ['$regex' => $regex]],
        ]],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1]]
    )->toArray();
    $topicIds = [];
    $topicsById = [];
    foreach ($matchingTopics as $topic) {
        $topicIds[] = $topic['id'];
        $topicsById[$topic['id']] = [
            'id' => $topic['id'],
            'name' => $topic['name'] ?? $topic['id'],
            'name_de' => $topic['name_de'] ?? null,
        ];
    }

    $openAlexByUser = [];
    $openAlexEnabled = $Settings->featureEnabled('spectrum');
    if ($openAlexEnabled) {
        $topicMatch = ['$or' => [
            ['openalex.topics.name' => ['$regex' => $regex]],
            ['openalex.topics.path' => ['$regex' => $regex]],
            ['openalex.topics.domain' => ['$regex' => $regex]],
            ['openalex.topics.field' => ['$regex' => $regex]],
            ['openalex.topics.subfield' => ['$regex' => $regex]],
        ]];
        $rows = $osiris->activities->aggregate([
            ['$match' => ['$and' => [
                ['type' => 'publication'],
                ['hide' => ['$ne' => true]],
                ['rendered.affiliated_users' => ['$exists' => true, '$ne' => []]],
                $topicMatch,
            ]]],
            ['$unwind' => '$openalex.topics'],
            ['$match' => ['$and' => [
                ['$or' => [
                    ['openalex.topics.name' => ['$regex' => $regex]],
                    ['openalex.topics.path' => ['$regex' => $regex]],
                    ['openalex.topics.domain' => ['$regex' => $regex]],
                    ['openalex.topics.field' => ['$regex' => $regex]],
                    ['openalex.topics.subfield' => ['$regex' => $regex]],
                ]],
                ['openalex.topics.score' => ['$gte' => 0.5]],
            ]]],
            ['$unwind' => '$rendered.affiliated_users'],
            ['$group' => [
                '_id' => [
                    'username' => '$rendered.affiliated_users',
                    'topic' => '$openalex.topics.id',
                ],
                'name' => ['$first' => '$openalex.topics.name'],
                'path' => ['$first' => '$openalex.topics.path'],
                'count' => ['$sum' => 1],
                'max_score' => ['$max' => '$openalex.topics.score'],
            ]],
            ['$sort' => ['count' => -1, 'max_score' => -1]],
        ])->toArray();
        foreach ($rows as $row) {
            $username = $row['_id']['username'] ?? null;
            if (empty($username)) {
                continue;
            }
            if (!isset($openAlexByUser[$username])) {
                $openAlexByUser[$username] = [];
            }
            if (count($openAlexByUser[$username]) < 5) {
                $openAlexByUser[$username][] = [
                    'id' => $row['_id']['topic'] ?? null,
                    'name' => $row['name'] ?? null,
                    'path' => $row['path'] ?? null,
                    'publication_count' => intval($row['count'] ?? 0),
                    'max_score' => round(floatval($row['max_score'] ?? 0), 3),
                ];
            }
        }
    }

    $expertiseClauses = [
        ['expertise' => ['$regex' => $regex]],
        ['research' => ['$regex' => $regex]],
        ['research_de' => ['$regex' => $regex]],
        ['research_profile' => ['$regex' => $regex]],
        ['research_profile_de' => ['$regex' => $regex]],
    ];
    if (!empty($topicIds)) {
        $expertiseClauses[] = ['topics' => ['$in' => $topicIds]];
    }
    if (!empty($openAlexByUser)) {
        $expertiseClauses[] = ['username' => ['$in' => array_keys($openAlexByUser)]];
    }
    $filter = [
        'hide' => ['$ne' => true],
        'is_active' => ['$ne' => false],
        '$or' => $expertiseClauses,
    ];
    $unitFilter = trim((string) ($_GET['unit'] ?? ''));
    if ($unitFilter !== '') {
        $filter['current_units'] = $unitFilter;
    }
    $documents = $osiris->persons->find(
        $filter,
        [
            'sort' => ['displayname' => 1, 'last' => 1, 'first' => 1, 'username' => 1],
            'projection' => [
                '_id' => 0,
                'username' => 1,
                'displayname' => 1,
                'first' => 1,
                'last' => 1,
                'academic_title' => 1,
                'position' => 1,
                'position_de' => 1,
                'is_active' => 1,
                'units' => 1,
                'expertise' => 1,
                'research' => 1,
                'research_de' => 1,
                'research_profile' => 1,
                'research_profile_de' => 1,
                'topics' => 1,
            ],
        ]
    )->toArray();

    $experts = [];
    foreach ($documents as $person) {
        if (empty($person['username'])) {
            continue;
        }
        $evidence = [
            'expertise' => mcp_matching_strings($person['expertise'] ?? [], $query),
            'research_interests' => mcp_matching_strings($person['research'] ?? [], $query),
            'research_interests_de' => mcp_matching_strings($person['research_de'] ?? [], $query),
            'topics' => [],
            'openalex_topics' => $openAlexByUser[$person['username']] ?? [],
        ];
        foreach (DB::doc2Arr($person['topics'] ?? []) as $topicId) {
            if (isset($topicsById[$topicId])) {
                $evidence['topics'][] = $topicsById[$topicId];
            }
        }
        foreach (['research_profile', 'research_profile_de'] as $field) {
            $profile = mcp_text($person[$field] ?? '', 1000);
            if ($profile !== '' && preg_match('/' . preg_quote($query, '/') . '/iu', $profile)) {
                $evidence[$field] = $profile;
            }
        }
        $expert = mcp_person_summary($person, $Groups);
        $expert['evidence'] = array_filter($evidence, fn($value) => !empty($value));
        $expert['_relevance'] =
            count($evidence['expertise']) * 100 +
            count($evidence['research_interests']) * 80 +
            count($evidence['research_interests_de']) * 80 +
            count($evidence['topics']) * 60 +
            (isset($evidence['research_profile']) ? 40 : 0) +
            (isset($evidence['research_profile_de']) ? 40 : 0) +
            count($evidence['openalex_topics']) * 10;
        $experts[] = $expert;
    }
    usort($experts, function ($a, $b) {
        $score = ($b['_relevance'] ?? 0) <=> ($a['_relevance'] ?? 0);
        if ($score !== 0) {
            return $score;
        }
        $name = strcasecmp($a['name'], $b['name']);
        return $name !== 0 ? $name : strcmp($a['id'], $b['id']);
    });
    $total = count($experts);
    $experts = array_slice($experts, $offset, $limit);
    foreach ($experts as &$expert) {
        unset($expert['_relevance']);
    }
    unset($expert);
    $response = [
        'status' => 200,
        'data' => [
            'openalex_enabled' => $openAlexEnabled,
            'experts' => $experts,
        ],
    ] + mcp_pagination($total, $offset, $limit, count($experts));
    mcp_return_json($response);
});

Route::get('/api/mcp/persons/([^/]+)', function ($id) {
    mcp_begin_request('persons.get');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('persons.read')) {
        mcp_return_json([
            'status' => 403,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $person = $osiris->persons->findOne(
        ['username' => $id, 'hide' => ['$ne' => true]],
        ['projection' => [
            '_id' => 0,
            'username' => 1,
            'displayname' => 1,
            'first' => 1,
            'last' => 1,
            'academic_title' => 1,
            'position' => 1,
            'position_de' => 1,
            'is_active' => 1,
            'units' => 1,
            'orcid' => 1,
            'expertise' => 1,
            'research' => 1,
            'research_de' => 1,
            'research_profile' => 1,
            'research_profile_de' => 1,
            'topics' => 1,
        ]]
    );
    if (empty($person) || empty($person['username'])) {
        mcp_return_json([
            'status' => 404,
            'error' => 'NotFound',
            'msg' => 'The requested person was not found.',
        ], 404);
        return;
    }

    $result = mcp_person_summary($person, $Groups);
    $result['orcid'] = $person['orcid'] ?? null;
    $result['expertise'] = mcp_strings($person['expertise'] ?? []);
    $result['research_interests'] = [
        'en' => mcp_strings($person['research'] ?? []),
        'de' => mcp_strings($person['research_de'] ?? []),
    ];
    $result['topics'] = mcp_person_topics($person['topics'] ?? [], $osiris);
    $result['research_profile'] = [
        'en' => mcp_text($person['research_profile'] ?? '', 2000),
        'de' => mcp_text($person['research_profile_de'] ?? '', 2000),
    ];

    mcp_return_json([
        'status' => 200,
        'data' => $result,
    ]);
});

Route::get('/api/mcp/activities', function () {
    mcp_begin_request('activities.search');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('activities.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $limit = filter_var(
        $_GET['limit'] ?? 10,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 50]]
    );
    if ($limit === false) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 50.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $booleanFilters = [];
    foreach (['include_unaffiliated', 'include_online_ahead_of_print'] as $parameter) {
        $value = filter_var(
            $_GET[$parameter] ?? false,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        if ($value === null) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'InvalidParameter',
                'msg' => $parameter . ' must be true or false.',
            ], 400);
            return;
        }
        $booleanFilters[$parameter] = $value;
    }

    $clauses = [];
    if (!$booleanFilters['include_unaffiliated']) {
        $clauses[] = ['affiliated' => true];
    }
    if (!$booleanFilters['include_online_ahead_of_print']) {
        // $ne also matches documents where epub is null or not present.
        $clauses[] = ['epub' => ['$ne' => true]];
    }
    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'q must not exceed 200 characters.',
        ], 400);
        return;
    }
    if ($query !== '') {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');
        $clauses[] = ['$or' => [
            ['title' => ['$regex' => $regex]],
            ['abstract' => ['$regex' => $regex]],
            ['rendered.plain' => ['$regex' => $regex]],
        ]];
    }

    foreach (['from_date', 'to_date'] as $parameter) {
        $value = trim((string) ($_GET[$parameter] ?? ''));
        if ($value === '') {
            continue;
        }
        $date = mcp_parse_date($value);
        if ($date === null) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'InvalidParameter',
                'msg' => $parameter . ' must use the YYYY-MM-DD format.',
            ], 400);
            return;
        }
        // MCP date ranges describe when an activity starts. Treating a missing
        // end date as open-ended would otherwise make old publications match
        // every later reporting period.
        $operator = $parameter === 'from_date' ? '$gte' : '$lte';
        $clauses[] = ['start_date' => [$operator => $date]];
    }

    $exactFilters = [
        'type' => 'type',
        'subtype' => 'subtype',
        'person' => 'rendered.users',
        'unit' => 'units',
        'topic' => 'topics',
    ];
    foreach ($exactFilters as $parameter => $field) {
        $value = trim((string) ($_GET[$parameter] ?? ''));
        if ($value === '') {
            continue;
        }
        if (mb_strlen($value) > 100) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'InvalidParameter',
                'msg' => $parameter . ' must not exceed 100 characters.',
            ], 400);
            return;
        }
        if ($parameter === 'topic' && !$Settings->featureEnabled('topics')) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'UnsupportedFilter',
                'msg' => 'The topic filter is not available for this OSIRIS instance.',
            ], 400);
            return;
        }
        $clauses[] = [$field => $value];
    }

    $filter = empty($clauses) ? [] : ['$and' => $clauses];
    $pipeline = [];
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    $pipeline[] = ['$sort' => ['start_date' => -1, '_id' => -1]];
    $pipeline[] = ['$skip' => $offset];
    $pipeline[] = ['$limit' => $limit];
    $pipeline[] = ['$project' => mcp_activity_projection()];
    $documents = $osiris->activities->aggregate($pipeline)->toArray();
    $personCache = [];
    $unitCache = [];
    $activities = [];
    foreach ($documents as $document) {
        $activities[] = mcp_activity_result($document, $DB, $Groups, $personCache, $unitCache);
    }

    $response = [
        'status' => 200,
        'data' => $activities,
    ] + mcp_pagination($osiris->activities->countDocuments($filter), $offset, $limit, count($activities));
    mcp_return_json($response);
});

Route::get('/api/mcp/activities/([a-fA-F0-9]{24})', function ($id) {
    mcp_begin_request('activities.get');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('activities.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $documents = $osiris->activities->aggregate([
        ['$match' => [
            '_id' => DB::to_ObjectID($id),
        ]],
        ['$limit' => 1],
        ['$project' => mcp_activity_projection()],
    ])->toArray();
    if (empty($documents)) {
        mcp_return_json([
            'status' => 404,
            'count' => 0,
            'error' => 'NotFound',
            'msg' => 'The requested activity was not found.',
        ], 404);
        return;
    }

    $personCache = [];
    $unitCache = [];
    $activity = mcp_activity_result($documents[0], $DB, $Groups, $personCache, $unitCache);
    mcp_return_json([
        'status' => 200,
        'count' => 1,
        'data' => [$activity],
    ]);
});

Route::get('/api/mcp/projects', function () {
    mcp_begin_request('projects.search');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('projects.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $limit = filter_var(
        $_GET['limit'] ?? 10,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 50]]
    );
    if ($limit === false) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'limit must be an integer between 1 and 50.',
        ], 400);
        return;
    }
    $offset = mcp_offset();
    if ($offset < 0) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'offset must be an integer between 0 and 1000000.',
        ], 400);
        return;
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query) > 200) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'InvalidParameter',
            'msg' => 'q must not exceed 200 characters.',
        ], 400);
        return;
    }

    $clauses = [];
    if ($query !== '') {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');
        $clauses[] = ['$or' => [
            ['name' => ['$regex' => $regex]],
            ['acronym' => ['$regex' => $regex]],
            ['title' => ['$regex' => $regex]],
            ['abstract' => ['$regex' => $regex]],
        ]];
    }

    $activeOnInput = trim((string) ($_GET['active_on'] ?? ''));
    if ($activeOnInput !== '') {
        $activeOn = mcp_parse_date($activeOnInput);
        if ($activeOn === null) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'InvalidParameter',
                'msg' => 'active_on must use the YYYY-MM-DD format.',
            ], 400);
            return;
        }
        $clauses[] = ['start_date' => ['$lte' => $activeOn]];
        $clauses[] = ['$or' => [
            ['end_date' => ['$gte' => $activeOn]],
            ['end_date' => null],
            ['end_date' => ['$exists' => false]],
        ]];
    }

    $topicInput = trim((string) ($_GET['topic'] ?? ''));
    $topicsAvailable = $Settings->featureEnabled('topics') &&
        $osiris->topics->count(['inactive' => ['$ne' => true]]) > 0;
    if ($topicInput !== '' && !$topicsAvailable) {
        mcp_return_json([
            'status' => 400,
            'count' => 0,
            'error' => 'UnsupportedFilter',
            'msg' => 'The topic filter is not available for this OSIRIS instance.',
        ], 400);
        return;
    }

    foreach (['status', 'topic', 'unit'] as $parameter) {
        $value = trim((string) ($_GET[$parameter] ?? ''));
        if ($value === '') {
            continue;
        }
        if (mb_strlen($value) > 100) {
            mcp_return_json([
                'status' => 400,
                'count' => 0,
                'error' => 'InvalidParameter',
                'msg' => $parameter . ' must not exceed 100 characters.',
            ], 400);
            return;
        }
        $field = $parameter === 'unit' ? 'units' : ($parameter === 'topic' ? 'topics' : 'status');
        $clauses[] = [$field => $value];
    }

    $filter = count($clauses) === 1 ? $clauses[0] : (empty($clauses) ? [] : ['$and' => $clauses]);

    $pipeline = [];
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    $pipeline[] = ['$sort' => ['start_date' => -1, '_id' => -1]];
    $pipeline[] = ['$skip' => $offset];
    $pipeline[] = ['$limit' => $limit];
    $pipeline[] = ['$project' => mcp_project_projection()];

    $projects = $osiris->projects->aggregate($pipeline)->toArray();
    $response = [
        'status' => 200,
        'data' => $projects,
    ] + mcp_pagination($osiris->projects->countDocuments($filter), $offset, $limit, count($projects));
    mcp_return_json($response);
});

Route::get('/api/mcp/projects/([a-fA-F0-9]{24})', function ($id) {
    mcp_begin_request('projects.get');
    include_once BASEPATH . '/php/init.php';

    if (!mcp_api_key_check('projects.read')) {
        mcp_return_json([
            'status' => 403,
            'count' => 0,
            'error' => 'PermissionDenied',
            'msg' => 'A valid MCP API key is required.',
        ], 403);
        return;
    }

    $pipeline = [
        ['$match' => ['_id' => DB::to_ObjectID($id)]],
        ['$limit' => 1],
        ['$project' => mcp_project_projection()],
    ];
    $projects = $osiris->projects->aggregate($pipeline)->toArray();
    if (empty($projects)) {
        mcp_return_json([
            'status' => 404,
            'count' => 0,
            'error' => 'DataNotFound',
            'msg' => 'Project not found.',
        ], 404);
        return;
    }

    mcp_return_json([
        'status' => 200,
        'count' => 1,
        'data' => [$projects[0]],
    ]);
});
