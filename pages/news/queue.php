<?php
$outlets = array_column($Settings->getNewsOutlets()['outlets'], null, 'id');
$actionableCount = count(array_filter($queue, static fn($row) => $row['actionable']));
?>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">

<div class="container news-workflow">
    <h1><i class="ph-duotone ph-highlighter"></i> <?= t('news.editorial_overview') ?></h1>
    <p class="text-muted"><?= t('news.open_outlet_requests_contributions_with_an_editorial_task_appear_f') ?></p>
    <div class="btn-toolbar mb-20">
        <a class="btn" href="<?= ROOTPATH ?>/news"><i class="ph ph-arrow-left"></i> <?= t('common.news') ?></a>
        <span class="badge signal"><?= $actionableCount ?> <?= t('news.with_editorial_tasks') ?></span>
        <span class="badge"><?= count($queue) - $actionableCount ?> <?= t('news.awaiting_author_response') ?></span>
    </div>
    <div class="row row-eq-spacing mb-20">
        <div class="col-sm-8">
            <label for="queue-search"><?= t('action.search') ?></label>
            <input class="form-control" id="queue-search" type="search" placeholder="<?= t('news.title_or_author') ?>">
        </div>
        <div class="col-sm-4">
            <label for="queue-scope"><?= t('news.tasks') ?></label>
            <select class="form-control" id="queue-scope"><option value="all"><?= t('news.all_open_items') ?></option>
                <option value="actionable"><?= t('news.editorial_tasks') ?></option>
                <option value="waiting"><?= t('news.awaiting_response') ?></option></select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table news-queue-table">
            <thead><tr><th><?= t('news.news_item') ?></th><th><?= t('news.outlets_and_status') ?></th><th><?= t('news.next_step') ?></th></tr></thead>
            <tbody id="editorial-queue">
            <?php foreach ($queue as $row) {
                $news = $row['news'];
                $author = $DB->getNameFromId($news['created_by'] ?? '');
                $title = lang($news['title'] ?? '', $news['title_de'] ?? null);
                $needsEditing = (bool) array_filter($row['requests'], static fn($request) => in_array($request['status'], ['requested', 'in_progress'], true));
                $nextSection = $needsEditing ? 'news-editorial' : 'editorial-review-controls';
                $newsDate = strtotime($news['date'] ?? '');
                ?>
                <tr data-queue-item data-scope="<?= $row['actionable'] ? 'actionable' : 'waiting' ?>" data-search="<?= e($title . ' ' . $author) ?>">
                    <td><a href="<?= ROOTPATH ?>/news/view/<?= e($news['_id']) ?>#news-editorial" class="<?= $row['actionable'] ? 'font-weight-bold' : '' ?>"><?= e($title) ?></a><p class="text-muted font-size-12 mt-5 mb-0"><?= $newsDate === false ? e($news['date'] ?? '') : e(date('d.m.Y', $newsDate)) ?> · <?= e($author) ?></p></td>
                    <td>
                        <?php foreach ($row['requests'] as $request) { ?>
                            <div class="news-queue-status"><span><?= e(NewsOutlets::label($outlets[$request['outlet_id']]['name'] ?? ['en' => $request['outlet_id']])) ?></span> <span class="<?= NewsEditorial::statusClass($request['status']) ?>"><?= NewsEditorial::statusLabel($request['status']) ?></span>
                            </div>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['actionable']) { ?>
                            <a class="btn small primary" href="<?= ROOTPATH ?>/news/view/<?= e($news['_id']) ?>#<?= $nextSection ?>"><i class="ph <?= $needsEditing ? 'ph-pencil-line' : 'ph-check-circle' ?>"></i> <?= $needsEditing ? t('activities.continue_editing') : t('news.review_release') ?></a>
                        <?php } else { ?>
                            <span class="text-muted"><i class="ph ph-hourglass"></i> <?= t('news.awaiting_author_response') ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr id="queue-empty" <?= $queue ? 'hidden' : '' ?>><td colspan="3" class="text-muted"><?= t('news.no_open_items_found') ?></td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
(function () {
    const search = document.getElementById('queue-search'), scope = document.getElementById('queue-scope');
    function filter() {
        let visible = 0;
        document.querySelectorAll('[data-queue-item]').forEach(row => {
            row.hidden = !(row.dataset.search.toLocaleLowerCase().includes(search.value.toLocaleLowerCase()) && (scope.value === 'all' || row.dataset.scope === scope.value));
            if (!row.hidden) visible++;
        });
        document.getElementById('queue-empty').hidden = visible > 0;
    }
    search.addEventListener('input', filter);
    scope.addEventListener('change', filter);
})();
</script>
