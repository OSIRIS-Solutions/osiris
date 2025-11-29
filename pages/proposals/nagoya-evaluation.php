<?php
$nagoya     = $project['nagoya'] ?? [];
$countries  = DB::doc2Arr($nagoya['countries'] ?? []);
$absCountries = [];
$nonAbsCountries = [];

foreach ($countries as $c) {
    if ($c['abs'] ?? false) {
        $absCountries[] = $c;
    } else {
        $nonAbsCountries[] = $c;
    }
}
?>

<style>
    .table,
    .table th,
    .table td {
        border-color: var(--primary-color);
    }

    .table thead th {
        background-color: var(--primary-color-20);
    }

    .table tbody th {
        width: 20rem;
    }

    .box .header {
        cursor: pointer;
        background-color: var(--primary-color-20);
    }

    .box .header small {
        margin-left: auto;
    }

    .box .header h2 {
        margin: 0;
        padding: 1rem;
    }
</style>

<h1 class="title">
    <i class="ph-duotone ph-scales"></i>
    <?= lang('ABS evaluation per country (A/B/C)', 'ABS-Bewertung pro Land (A/B/C)') ?>
</h1>
<p class="text-muted">
    <?= lang(
        'Please review the scope and ABS information for each ABS-relevant country and assign an A/B/C label. You can also document required permits. The overall project label will be derived automatically.',
        'Bitte prüfe die Scope- und ABS-Informationen für jedes ABS-relevante Land und vergebe ein A/B/C-Label. Zusätzlich können erforderliche Genehmigungen dokumentiert werden. Das Gesamtlabel für das Projekt wird automatisch daraus abgeleitet.'
    ) ?>
</p>
<div class="mb-20">
    <b><?= lang('Current Nagoya status', 'Aktueller Nagoya-Status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>
</div>

<form method="post" action="<?= ROOTPATH ?>/crud/nagoya/evaluate-abs/<?= $id ?>">

    <?php if (empty($absCountries)): ?>
        <div class="alert info">
            <?= lang(
                'There are currently no ABS-relevant countries for this project.',
                'Für dieses Projekt sind derzeit keine ABS-relevanten Länder hinterlegt.'
            ) ?>
        </div>
    <?php else: ?>

        <?php foreach ($absCountries as $c):
            $cid   = $c['id'] ?? '';
            $code  = $c['code'] ?? '';
            $scope = $c['scope']['groups'] ?? [];
            $review = $c['review'] ?? [];
            $eval   = $c['evaluation'] ?? [];
            $label  = $eval['label'] ?? null;
            $permits = $eval['permits'] ?? [];
            if (empty($permits)) {
                // ensure at least one empty row for UI
                $permits = [
                    ['name' => '', 'status' => '', 'comment' => '']
                ];
            }
        ?>
            <div class="box" id="country-<?= htmlspecialchars($cid) ?>">
                <div class="header" onclick="$(this).toggleClass('open').next('.content').toggleClass('hidden');">
                    <h2>
                        <i class="ph-duotone ph-globe-stand"></i>
                        <?= $DB->getCountry($code, lang('name', 'name_de')) ?>
                    </h2>
                </div>

                <div class="content">
                    <h4><?= lang('Country review', 'Länderbewertung') ?></h4>

                    <small class="text-muted">
                        <?= lang('Review of countries as part of the ABS evaluation process was conducted by:', 'Die Bewertung der Länder im Rahmen des ABS-Bewertungsprozesses wurde durchgeführt von:') ?>
                        <strong><?= htmlspecialchars($DB->getNameFromId($review['reviewed_by']) ?? $review['reviewed_by']) ?></strong>
                        <?= lang('on', 'am') ?> <?= format_date($review['reviewed'] ?? '') ?>
                    </small>
                    <div class="mb-10">
                        <strong><?= lang('Nagoya Party', 'Nagoya-Partei') ?>:</strong>
                        <?php
                        $nagoyaParty = $review['nagoyaParty'] ?? 'unknown';
                        if ($nagoyaParty === 'yes') {
                            echo '<span class="badge success">' . lang('Yes', 'Ja') . '</span>';
                        } elseif ($nagoyaParty === 'no') {
                            echo '<span class="badge danger">' . lang('No', 'Nein') . '</span>';
                        } else {
                            echo '<span class="badge muted">' . lang('Unknown', 'Unbekannt') . '</span>';
                        }
                        ?>
                    </div>
                    <div class="mb-10">
                        <strong><?= lang('Own ABS measures', 'Eigene ABS-Maßnahmen') ?>:</strong>
                        <?php
                        $ownABSMeasures = $review['ownABSMeasures'] ?? 'unknown';
                        if ($ownABSMeasures === 'yes') {
                            echo '<span class="badge success">' . lang('Yes', 'Ja') . '</span>';
                        } elseif ($ownABSMeasures === 'no') {
                            echo '<span class="badge danger">' . lang('No', 'Nein') . '</span>';
                        } else {
                            echo '<span class="badge muted">' . lang('Unknown', 'Unbekannt') . '</span>';
                        }
                        ?>
                    </div>
                    <div class="small text-muted">
                        <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                    </div>
                    <div>
                        <strong><?= lang('Comment', 'Kommentar') ?>:</strong><br>
                        <span><?= nl2br(htmlspecialchars($review['comment'] ?? lang('No comment provided.', 'Kein Kommentar hinterlegt.'))) ?></span>
                    </div>
                </div>

                <hr>

                <div class="content">
                    <!-- Scope overview (read-only) -->
                    <?php if (empty($scope)): ?>
                        <p class="text-muted small">
                            <?= lang('No scope information provided yet for this country.', 'Für dieses Land wurden noch keine Scope-Informationen hinterlegt.') ?>
                        </p>
                    <?php else: ?>
                        <div class="mb-10">
                            <h4 class="mb-5"><?= lang('Scope overview', 'Scope-Übersicht') ?></h4>

                            <?php if (!empty($review['reviewed_by'])): ?>
                                <p class="font-size-12 text-muted">
                                    <?= lang('Country review by', 'Länderbewertung von') ?>
                                    <?= htmlspecialchars($DB->getNameFromId($review['reviewed_by']) ?? $review['reviewed_by']) ?>
                                    <?php if (!empty($review['reviewed'])): ?>
                                        <?= lang('on', 'am') ?> <?= format_date($review['reviewed']) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <?php foreach ($scope as $i => $g): ?>
                                <table class="table small mb-20">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="text-primary">
                                                <?= lang('Sample Collection', 'Probensammlung') ?> <?= $i + 1 ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php if (!empty($g['geo'])): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('Geographical scope', 'Geographischer Scope') ?>:</th>
                                                <td><?= nl2br(htmlspecialchars($g['geo'])) ?></td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if (!empty($g['temporal']) || !empty($g['temporal_ongoing'])): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('Temporal scope', 'Zeitlicher Scope') ?>:</th>
                                                <td>
                                                    <?php if (!empty($g['temporal'])): ?>
                                                        <?= htmlspecialchars($g['temporal']) ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($g['temporal_ongoing'])): ?>
                                                        <em><?= lang('ongoing / planned', 'laufend / geplant') ?></em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php
                                        $mat = DB::doc2Arr($g['material'] ?? []);
                                        $util = DB::doc2Arr($g['utilization'] ?? []);
                                        ?>
                                        <?php if (!empty($mat)): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('Material scope', 'Material-Scope') ?>:</th>
                                                <td>
                                                    <?= htmlspecialchars(implode(', ', $mat)) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if (!empty($util)): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('Utilization scope', 'Nutzung / Utilisation') ?>:</th>
                                                <td>
                                                    <?= htmlspecialchars(implode(', ', $util)) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>

                            <?php
                            $atk_used    = $c['scope']['atk_used'] ?? false;
                            $atk_details = $c['scope']['atk_details'] ?? '';
                            $notes       = $c['scope']['notes'] ?? '';
                            ?>
                            <?php if ($atk_used || $atk_details): ?>
                                <div class="mb-5">
                                    <strong><?= lang('Associated traditional knowledge (aTK)', 'Assoziiertes traditionelles Wissen (aTK)') ?>:</strong><br>
                                    <?php if ($atk_used): ?>
                                        <span class="badge signal">
                                            <?= lang('aTK involved', 'aTK beteiligt') ?>
                                        </span><br>
                                    <?php endif; ?>
                                    <?php if ($atk_details): ?>
                                        <span>
                                            <?= nl2br(htmlspecialchars($atk_details)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($notes): ?>
                                <div>
                                    <strong><?= lang('Additional notes', 'Weitere Hinweise') ?>:</strong><br>
                                    <?= nl2br(htmlspecialchars($notes)) ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <hr class="my-10">

                        <!-- Country-level evaluation (A/B/C + rationale + permits) -->
                        <input type="hidden" name="evaluation[<?= htmlspecialchars($cid) ?>][country_id]" value="<?= htmlspecialchars($cid) ?>">

                        <div class="form-group mb-10">
                            <label class="font-weight-bold required">
                                <?= lang('Classification for this country (A/B/C)', 'Klassifikation für dieses Land (A/B/C)') ?>
                            </label>
                            <div class="mt-5 small">
                                <label class="d-block">
                                    <input type="radio" name="evaluation[<?= htmlspecialchars($cid) ?>][label]" value="A" <?= $label === 'A' ? 'checked' : '' ?>>
                                    <strong>A</strong> – <?= lang('in scope of EU Regulation (Nagoya Protocol)', 'im Geltungsbereich der EU-Verordnung (Nagoya-Protokoll)') ?>
                                </label>
                                <label class="d-block mt-5">
                                    <input type="radio" name="evaluation[<?= htmlspecialchars($cid) ?>][label]" value="B" <?= $label === 'B' ? 'checked' : '' ?>>
                                    <strong>B</strong> – <?= lang('in scope of national ABS measures only', 'nur im Geltungsbereich nationaler ABS-Maßnahmen') ?>
                                </label>
                                <label class="d-block mt-5">
                                    <input type="radio" name="evaluation[<?= htmlspecialchars($cid) ?>][label]" value="C" <?= $label === 'C' ? 'checked' : '' ?>>
                                    <strong>C</strong> – <?= lang('out of scope', 'nicht im Geltungsbereich') ?>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-10">
                            <label class="font-weight-bold required">
                                <?= lang('Rationale for this country', 'Begründung für dieses Land') ?>
                            </label>
                            <small class="d-block text-muted mb-5">
                                <?= lang(
                                    'Please briefly justify the A/B/C classification for this country (e.g. type of resources, time frame, legal situation).',
                                    'Bitte begründe kurz die A/B/C-Klassifikation für dieses Land (z.B. Art der Ressourcen, Zeitraum, rechtliche Situation).'
                                ) ?>
                            </small>
                            <textarea
                                name="evaluation[<?= htmlspecialchars($cid) ?>][rationale]"
                                rows="3"
                                class="form-control"><?= htmlspecialchars($eval['rationale'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">
                                <?= lang('ABS permits for this country', 'ABS-Genehmigungen für dieses Land') ?>
                            </label>
                            <small class="d-block text-muted mb-5">
                                <?= lang(
                                    'List any required or already obtained ABS permits. You can use free text names and track the status.',
                                    'Liste alle erforderlichen oder bereits erhaltenen ABS-Genehmigungen auf. Die Namen können als Freitext erfasst werden, der Status kann nachverfolgt werden.'
                                ) ?>
                            </small>

                            <table class="table table-sm mb-5 nagoya-permits-table" data-country="<?= htmlspecialchars($cid) ?>">
                                <thead>
                                    <tr>
                                        <th><?= lang('Permit name', 'Genehmigungsname') ?></th>
                                        <th><?= lang('Status', 'Status') ?></th>
                                        <th><?= lang('Comment', 'Kommentar') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($permits as $pi => $p): ?>
                                        <tr>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="evaluation[<?= htmlspecialchars($cid) ?>][permits][<?= $pi ?>][name]"
                                                    class="form-control form-control-sm"
                                                    value="<?= htmlspecialchars($p['name'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <?php $status = $p['status'] ?? ''; ?>
                                                <select
                                                    name="evaluation[<?= htmlspecialchars($cid) ?>][permits][<?= $pi ?>][status]"
                                                    class="form-control form-control-sm">
                                                    <option value=""><?= lang('– select –', '– auswählen –') ?></option>
                                                    <option value="needed" <?= $status === 'needed'   ? 'selected' : '' ?>><?= lang('Needed', 'Erforderlich') ?></option>
                                                    <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>><?= lang('Requested', 'Beantragt') ?></option>
                                                    <option value="granted" <?= $status === 'granted'  ? 'selected' : '' ?>><?= lang('Granted', 'Erteilt') ?></option>
                                                    <option value="not-applicable" <?= $status === 'not-applicable' ? 'selected' : '' ?>><?= lang('Not applicable', 'Nicht zutreffend') ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="evaluation[<?= htmlspecialchars($cid) ?>][permits][<?= $pi ?>][comment]"
                                                    class="form-control form-control-sm"
                                                    value="<?= htmlspecialchars($p['comment'] ?? '') ?>">
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn small text-danger remove-permit-row">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="button"
                                class="btn small outline add-permit-row"
                                data-country="<?= htmlspecialchars($cid) ?>">
                                <i class="ph ph-plus"></i>
                                <?= lang('Add permit', 'Genehmigung hinzufügen') ?>
                            </button>
                        </div>

                        <?php if (!empty($eval['by']) && !empty($eval['at'])): ?>
                            <div class="small text-muted mt-5">
                                <?= lang('Last evaluation for this country by', 'Letzte Bewertung für dieses Land von') ?>
                                <?= htmlspecialchars($DB->getNameFromId($eval['by']) ?? $eval['by']) ?>
                                <?= lang('on', 'am') ?> <?= format_date($eval['at']) ?>
                            </div>
                        <?php endif; ?>
                        </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="mt-20">
        <button type="submit" class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save ABS evaluation', 'ABS-Bewertung speichern') ?>
        </button>
    </div>
</form>

<script>
    // simple permit row handling (no dependencies beyond jQuery)
    // comments in English for consistency with your codebase

    $(function() {
        $('.add-permit-row').on('click', function() {
            var cid = $(this).data('country');
            var $table = $('.nagoya-permits-table[data-country="' + cid + '"]');
            var $tbody = $table.find('tbody');
            var rows = $tbody.find('tr');
            var next = rows.length;

            // clone last row as template
            var $tmpl = rows.last().clone();

            // clear values
            $tmpl.find('input').val('');
            $tmpl.find('select').val('');

            // update name indices
            $tmpl.find('[name]').each(function() {
                this.name = this.name.replace(/\[permits]\[\d+]/, '[permits][' + next + ']');
            });

            $tbody.append($tmpl);
        });

        $(document).on('click', '.remove-permit-row', function() {
            var $tbody = $(this).closest('tbody');
            var rows = $tbody.find('tr');
            if (rows.length <= 1) {
                // keep at least one row
                $(this).closest('tr').find('input').val('');
                $(this).closest('tr').find('select').val('');
                return;
            }
            $(this).closest('tr').remove();

            // reindex names to keep them compact
            $tbody.find('tr').each(function(idx) {
                $(this).find('[name]').each(function() {
                    this.name = this.name.replace(/\[permits]\[\d+]/, '[permits][' + idx + ']');
                });
            });
        });
    });
</script>