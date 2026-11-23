<?php

Route::get('/news/editorial', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->featureEnabled('news', true) || !$Settings->featureEnabled('news-editorial') || !$Settings->hasPermission('news.editorial')) abortwith(403);
    require_once BASEPATH . '/php/NewsEditorial.php';
    $queue = [];
    foreach ($osiris->news->find(['outlet_requests.status' => ['$in' => ['requested', 'in_progress', 'ready', 'pending_approval']]], ['projection' => ['image' => 0, 'content' => 0, 'content_de' => 0, 'editorial_contributions' => 0, 'author_reviews' => 0, 'editorial_history' => 0]]) as $document) {
        $news = DB::doc2Arr($document);
        if (NewsEditorial::internalOnly($news)) continue;
        $open = array_values(array_filter(NewsEditorial::requests($news), static fn($request) =>
            in_array($request['status'] ?? '', ['requested', 'in_progress', 'ready', 'pending_approval'], true)));
        if (!$open) continue;
        $actionable = count(array_filter($open, static fn($request) => $request['status'] !== 'pending_approval')) > 0;
        $queue[] = ['news' => $news, 'requests' => $open, 'actionable' => $actionable];
    }
    usort($queue, static fn($a, $b) => ($b['actionable'] <=> $a['actionable']) ?: strcmp($a['news']['date'] ?? '', $b['news']['date'] ?? ''));
    $breadcrumb = [['path' => '/news', 'name' => t('common.news')], ['path' => '/news/editorial', 'name' => t('news.editorial_overview')]];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/news/queue.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::get('/news/export', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->featureEnabled('news', true) || !$Settings->featureEnabled('news-editorial') || !$Settings->hasPermission('news.export')) abortwith(403);
    require_once BASEPATH . '/php/NewsExport.php';
    $configuration = $Settings->getNewsOutlets();
    $error = null;
    try {
        $filters = NewsExport::filters($_GET, $configuration);
    } catch (InvalidArgumentException $exception) {
        if (isset($_GET['download'])) abortwith(400, $exception->getMessage());
        $error = $exception->getMessage();
        $filters = NewsExport::filters([], $configuration);
    }
    $entries = NewsExport::entries($osiris->news->find(NewsExport::query($filters), ['sort' => ['date' => 1], 'projection' => ['editorial_history' => 0]]), $configuration, $filters);
    $exportOutlet = array_column($configuration['outlets'], null, 'id')[$filters['channel']] ?? null;
    $exportChannelName = $exportOutlet ? (($exportOutlet['name'][$filters['language']] ?? '') ?: NewsOutlets::label($exportOutlet['name'])) : ($filters['language'] === 'de' ? 'Originalbeiträge' : 'Original contributions');
    $reportTitle = ($filters['language'] === 'de' ? 'News-Bericht' : 'News report') . ': ' . $exportChannelName;
    if (isset($_GET['download']) && !$entries) {
        $error = t('news.no_contributions_match_these_filters_adjust_the_channel_or_date_ra');
    }
    if (isset($_GET['download']) && $entries) {
        $filename = 'News_' . $filters['channel'] . '_' . date('Y-m-d') . '.' . $filters['format'];
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        if ($filters['format'] === 'html') {
            header('Content-Type: text/html; charset=UTF-8');
            echo NewsExport::html($entries, $reportTitle, $filters['language']);
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            \PhpOffice\PhpWord\IOFactory::createWriter(NewsExport::word($entries, $reportTitle), 'Word2007')->save('php://output');
        }
        exit;
    }
    $breadcrumb = [['path' => '/news', 'name' => t('common.news')], ['path' => '/news/export', 'name' => t('news.export_report')]];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/news/export.php';
    include BASEPATH . '/footer.php';
}, 'login');
