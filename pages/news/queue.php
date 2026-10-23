<?php
$outlets = array_column($Settings->getNewsOutlets()['outlets'], null, 'id');
$actionableCount = count(array_filter($queue, static fn($row) => $row['actionable']));
?>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">

<div class="container news-workflow">
    <h1><i class="ph-duotone ph-highlighter"></i> <?= lang('Editorial overview', 'Redaktionsübersicht') ?></h1>
    <p class="text-muted"><?= lang('Open outlet requests. Contributions with an editorial task appear first.', 'Offene Outlet-Anfragen. Beiträge mit einer Aufgabe für die Redaktion stehen zuerst.') ?></p>
    <div class="btn-toolbar mb-20">
        <a class="btn" href="<?= ROOTPATH ?>/news"><i class="ph ph-arrow-left"></i> <?= lang('News', 'News') ?></a>
        <span class="badge signal"><?= $actionableCount ?> <?= lang('with editorial tasks', 'mit redaktionellen Aufgaben') ?></span>
        <span class="badge"><?= count($queue) - $actionableCount ?> <?= lang('awaiting author response', 'warten auf Rückmeldung') ?></span>
    </div>
    <div class="row row-eq-spacing mb-20">
        <div class="col-sm-8">
            <label for="queue-search"><?= lang('Search', 'Suche') ?></label>
            <input class="form-control" id="queue-search" type="search" placeholder="<?= lang('Title or author', 'Titel oder Autor:in') ?>">
        </div>
        <div class="col-sm-4">
            <label for="queue-scope"><?= lang('Tasks', 'Aufgaben') ?></label>
            <select class="form-control" id="queue-scope"><option value="all"><?= lang('All open items', 'Alle offenen Vorgänge') ?></option>
                <option value="actionable"><?= lang('Editorial tasks', 'Redaktionelle Aufgaben') ?></option>
                <option value="waiting"><?= lang('Awaiting response', 'Rückmeldung ausstehend') ?></option></select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table news-queue-table">
            <thead><tr><th><?= lang('News item', 'Nachricht') ?></th><th><?= lang('Outlets and status', 'Outlets und Status') ?></th><th><?= lang('Next step', 'Nächster Schritt') ?></th></tr></thead>
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
                            <a class="btn small primary" href="<?= ROOTPATH ?>/news/view/<?= e($news['_id']) ?>#<?= $nextSection ?>"><i class="ph <?= $needsEditing ? 'ph-pencil-line' : 'ph-check-circle' ?>"></i> <?= $needsEditing ? lang('Continue editing', 'Jetzt bearbeiten') : lang('Review release', 'Freigabe prüfen') ?></a>
                        <?php } else { ?>
                            <span class="text-muted"><i class="ph ph-hourglass"></i> <?= lang('Awaiting author response', 'Wartet auf Autor:in') ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr id="queue-empty" <?= $queue ? 'hidden' : '' ?>><td colspan="3" class="text-muted"><?= lang('No open items found.', 'Keine offenen Vorgänge gefunden.') ?></td></tr>
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
