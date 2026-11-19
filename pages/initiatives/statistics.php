<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>
<h1><i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i> <?= translate('common.statistics') ?> <small class="text-muted"><?= e($statisticsLabel) ?></small></h1>
<div class="btn-toolbar"><a href="<?= ROOTPATH ?>/initiatives<?= $statisticsView === 'objectives' ? '?status=objectives' : '' ?>"><i class="ph ph-arrow-left" aria-hidden="true"></i> <?= translate('initiatives.overview') ?></a></div>
<nav class="pills mt-20 mb-20" aria-label="<?= e(translate('common.statistics')) ?>">
    <?php foreach (['initiatives' => ['common.initiatives', 'compass'], 'objectives' => ['initiatives.objectives', 'target']] as $view => [$label, $icon]): if ($view === 'objectives' && !$statisticsHasObjectives) continue; ?>
        <a class="btn <?= $statisticsView === $view ? 'active' : '' ?>" href="<?= ROOTPATH ?>/initiatives/statistics?<?= e(http_build_query(array_replace($statisticsQuery, ['view' => $view, 'id' => '']))) ?>" <?= $statisticsView === $view ? 'aria-current="page"' : '' ?>><i class="ph ph-<?= $icon ?>" aria-hidden="true"></i> <?= translate($label) ?></a>
    <?php endforeach; ?>
</nav>
<div class="alert signal">
    <?= translate('projects.all_of_the_following_statistics_are_based_on_the_selected_reporting_time') ?>
    <div class="row row-eq-spacing my-0">
        <?php foreach (['year', 'period', 'date'] as $mode): ?>
            <div class="col-lg p-10">
                <form method="get" action="<?= ROOTPATH ?>/initiatives/statistics">
                    <input type="hidden" name="view" value="<?= $statisticsView ?>"><input type="hidden" name="id" value="<?= e($statisticsSelected) ?>"><input type="hidden" name="include_archived" value="<?= $statisticsIncludeArchived ? '1' : '0' ?>">
                    <label for="statistics-<?= $mode ?>"><?= translate('common.change_reporting_' . ($mode === 'period' ? 'period' : $mode)) ?></label>
                    <div class="d-flex align-items-center" style="gap:.5rem;flex-wrap:wrap">
                        <?php if ($mode === 'year'): ?>
                            <input id="statistics-year" type="number" class="form-control w-auto" name="reportyear" min="1900" max="2155" value="<?= e(substr($reportstart, 0, 4)) ?>" required>
                        <?php elseif ($mode === 'period'): ?>
                            <input id="statistics-period" type="date" class="form-control w-auto" name="reportstart" value="<?= e($reportstart) ?>" aria-label="<?= e(translate('common.start')) ?>" required>
                            <input type="date" class="form-control w-auto" name="reportend" value="<?= e($reportend) ?>" aria-label="<?= e(translate('common.end')) ?>" required>
                        <?php else: ?>
                            <input id="statistics-date" type="date" class="form-control w-auto" name="reportdate" value="<?= e($reportstart === $reportend ? $reportstart : date('Y-m-d')) ?>" required>
                        <?php endif; ?>
                        <button class="btn signal filled" type="submit"><?= translate('action.update') ?></button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<form class="form-inline my-20 d-flex align-items-end" style="gap:1rem;flex-wrap:wrap" method="get" action="<?= ROOTPATH ?>/initiatives/statistics">
    <?php foreach ($statisticsDateQuery + ['view' => $statisticsView] as $key => $value): ?><input type="hidden" name="<?= $key ?>" value="<?= e($value) ?>"><?php endforeach; ?>
    <div>
        <label for="statistics-entity"><?= e($statisticsLabel) ?></label><select id="statistics-entity" name="id" class="form-control">
            <option value=""><?= translate('common.all') ?></option><?php foreach ($statisticsChoices as $id => $name): ?><option value="<?= e($id) ?>" <?= $statisticsSelected === $id ? 'selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="statistics-archived"><?= translate('common.status') ?></label><select id="statistics-archived" name="include_archived" class="form-control">
            <option value="1" <?= $statisticsIncludeArchived ? 'selected' : '' ?>><?= translate('initiatives.all') ?></option>
            <option value="0" <?= !$statisticsIncludeArchived ? 'selected' : '' ?>><?= translate('initiatives.statistics_without_archived') ?></option>
        </select>
    </div>
    <button type="submit" class="btn secondary"><?= translate('action.update') ?></button>
</form>
<h2 class="text-decoration-underline"><?= e($statisticsTimeFrame) ?></h2>
<p class="text-muted"><?= translate('initiatives.statistics_basis') ?></p>
<?php if ($statisticsUndated): ?><p class="text-muted"><?= translate('initiatives.statistics_undated', ['count' => $statisticsUndated]) ?></p><?php endif; ?>
<?php if (!$statisticsRows): ?><p class="text-muted"><?= translate('initiatives.no_entries') ?></p><?php else: ?>
    <div class="box padded mb-20">
        <div class="d-flex align-items-center justify-content-between" style="gap:1rem;flex-wrap:wrap">
            <h3 class="m-0"><?= translate('initiatives.linked_content') ?></h3>
            <div><label class="sr-only" for="statistics-content-type"><?= translate('common.type') ?></label><select id="statistics-content-type" class="form-control w-auto"><?php foreach ($statisticsTypes as $type => $definition): ?><option value="<?= $type ?>"><?= e($definition['label']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div id="initiative-statistics-chart"></div>
        <p id="initiative-statistics-chart-empty" class="text-muted mt-20" hidden><?= translate('initiatives.statistics_no_content') ?></p>
        <p class="text-muted font-size-12 mb-0"><?= translate('initiatives.statistics_chart_hint') ?></p>
    </div>
    <h2><?= translate('initiatives.overview') ?></h2>
    <p class="text-muted"><?= translate('initiatives.statistics_counting_hint') ?></p>
    <div class="table-responsive">
        <table class="table" id="initiative-statistics-overview">
            <thead>
                <tr>
                    <th><?= e($statisticsLabel) ?></th><?php if ($statisticsView === 'objectives'): ?><th><?= translate('common.initiative') ?></th><?php endif; ?><th><?= translate('initiatives.statistics_period') ?></th>
                    <th><?= translate('common.current_status') ?></th><?php foreach ($statisticsTypes as $definition): ?><th><?= e($definition['label']) ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody><?php foreach ($statisticsRows as $row): ?><tr>
                        <td><a href="<?= ROOTPATH . e($row['url']) ?>"><?= e($row['name']) ?></a></td><?php if ($statisticsView === 'objectives'): ?><td><?= e($row['parent']) ?></td><?php endif; ?><td><?= e(Initiatives::period($row['document'])) ?></td>
                        <td><?= translate($row['status']) ?></td><?php foreach ($row['counts'] as $count): ?><td><?= $count ?></td><?php endforeach; ?>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <a class="btn secondary my-10" href="<?= ROOTPATH ?>/initiatives/statistics?<?= e(http_build_query($statisticsQuery + ['download' => 'csv'])) ?>"><i class="ph ph-download-simple" aria-hidden="true"></i> <?= translate('initiatives.statistics_download_data') ?></a>
    <details class="mt-20" id="initiative-statistics-evidence">
        <summary class="font-size-18"><?= translate('initiatives.statistics_evidence') ?> <span class="badge"><?= count($statisticsDetails) ?></span></summary>
        <div class="table-responsive mt-20">
            <table class="table" id="initiative-statistics-details">
                <thead>
                    <tr>
                        <th><?= e($statisticsLabel) ?></th><?php if ($statisticsView === 'objectives'): ?><th><?= translate('common.initiative') ?></th><?php endif; ?><th><?= translate('common.type') ?></th>
                        <th><?= translate('common.title') ?></th>
                        <th><?= translate('common.date') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statisticsDetails as $detail): ?><tr>
                            <td><?= e($detail['entity']) ?></td><?php if ($statisticsView === 'objectives'): ?><td><?= e($detail['parent']) ?></td><?php endif; ?><td><?= e($detail['type']) ?></td>
                            <td><a href="<?= ROOTPATH . e($detail['url']) ?>"><?= e($detail['title']) ?></a></td>
                            <td data-order="<?= e($detail['start']) ?>"><?= e($detail['date']) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </details>
    <script type="application/json" id="initiative-statistics-data">
        <?= json_encode(['rows' => array_values(array_map(fn($row) => ['name' => $row['name'], 'parent' => $row['parent'], 'counts' => $row['counts']], $statisticsRows)), 'types' => $statisticsTypes, 'title' => $statisticsExportTitle], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <script src="<?= ROOTPATH ?>/js/initiative-statistics.js?v=<?= OSIRIS_BUILD ?>"></script>
<?php endif; ?>