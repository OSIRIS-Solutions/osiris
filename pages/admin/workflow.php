<?php
$req = $osiris->adminGeneral->findOne(['key' => 'roles']);
$roles = DB::doc2Arr($req['value'] ?? ['user', 'scientist', 'admin']);

$steps = $form['steps'] ?? []; // erwartet Array von Arrays
?>
<style>
    #steps-table td {
        vertical-align: top;
    }

    .step-row {
        background: var(--bg, #fff);
    }

    .step-actions {
        white-space: nowrap;
    }

    .drag-handle {
        cursor: move;
        opacity: .6;
    }

    tr.placeholder {
        outline: 2px dashed var(--border-color);
        height: 48px;
    }
</style>

<form action="<?= ROOTPATH ?>/crud/workflows/update/<?= $form['id'] ?>" method="post" id="workflow-form">
    <div class="box">
        <h4 class="header"><?= htmlspecialchars($name) ?></h4>
        <div class="content">
            <p><b>ID:</b> <code class="code"><?= $form['id'] ?></code></p>
            <div class="form-group">
                <label for="name" class="required"><?= lang('Name of the workflow', 'Name des Workflow') ?></label>
                <input type="text" class="form-control" name="values[name]" required value="<?= htmlspecialchars($form['name'] ?? '') ?>" maxlength="30">
                <small class="form-text text-muted"><?= lang('Max 30 characters', 'Maximal 30 Zeichen') ?></small>
            </div>
        </div>
        <hr>
        <div class="content">
            <h5><?= lang('Steps', 'Schritte') ?></h5>

            <table id="steps-table" class="table mb-20">
                <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th><?= lang('Step title', 'Titel') ?></th>
                        <th style="width:90px"><?= lang('Phase', 'Phase') ?>*</th>
                        <th style="width:200px"><?= lang('Role', 'Rolle') ?></th>
                        <th style="width:130px"><?= lang('OU scope', 'OU-Scope') ?></th>
                        <!-- <th style="width:100px"><?= lang('Required', 'Erforderlich') ?></th> -->
                        <th style="width:120px"><?= lang('Lock after', 'Sperren') ?></th>
                        <th style="width:80px"></th>
                    </tr>
                </thead>
                <tbody id="steps-tbody">
                    <?php if (empty($steps)) {
                        $steps = [[]];
                    } ?>
                    <?php foreach ($steps as $i => $s): ?>
                        <tr class="step-row">
                            <td class="drag-handle"><i class="ph ph-dots-six-vertical"></i></td>
                            <td>
                                <div class="form-group floating-form mb-0">
                                    <input type="text" class="form-control" name="values[steps][<?= $i ?>][label]" value="<?= htmlspecialchars($s['label'] ?? '') ?>" placeholder="e.g. Department review" required>
                                    <label><?= lang('Step title', 'Titel des Schrittes') ?></label>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control" min="0" step="1" name="values[steps][<?= $i ?>][index]" value="<?= intval($s['index'] ?? 0) ?>">
                            </td>
                            <td>
                                <select name="values[steps][<?= $i ?>][role]" class="form-control">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= htmlspecialchars($r) ?>" <?= (($s['role'] ?? '') === $r ? 'selected' : '') ?>><?= strtoupper($r) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="values[steps][<?= $i ?>][orgScope]" class="form-control">
                                    <?php $scope = $s['orgScope'] ?? 'any'; ?>
                                    <option value="any" <?= $scope === 'any' ? 'selected' : '' ?>><?= lang('Any', 'Beliebig') ?></option>
                                    <option value="same_org_only" <?= $scope === 'same_org_only' ? 'selected' : '' ?>><?= lang('Same OU only', 'Nur eigene OU') ?></option>
                                </select>
                            </td>
                            <!-- <td class="text-center">
                                <?php $reqd = !isset($s['required']) ? true : (bool)$s['required']; ?>
                                <input type="checkbox" name="values[steps][<?= $i ?>][required]" value="1" <?= $reqd ? 'checked' : '' ?>>
                            </td> -->
                            <td class="text-center">
                                <input type="checkbox" name="values[steps][<?= $i ?>][locksAfterApproval]" value="1" <?= !empty($s['locksAfterApproval']) ? 'checked' : '' ?>>
                            </td>
                            <td class="step-actions">
                                <button type="button" class="btn danger icon-only btn-delete" title="<?= lang('Remove', 'Löschen') ?>"><i class="ph ph-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <button class="btn" type="button" id="btn-add-step">
                                <i class="ph ph-plus-circle"></i> <?= lang('Add step', 'Schritt hinzufügen') ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <p class="text-sm text-muted">
                * <?= lang('Multiple steps with the same phase number are executed in parallel, all others sequentially.', 'Mehrere Schritte mit gleicher Phasennummer werden parallel ausgeführt, alle anderen nacheinander.') ?><br>
            </p>

            <button type="submit" class="btn success" id="submitBtn">
                <i class="ph ph-check"></i> <?= lang('Update', 'Aktualisieren') ?>
            </button>
        </div>
    </div>
</form>

<!-- Hidden template -->
<table class="d-none">
    <tbody>
        <tr id="step-template" class="step-row">
            <td class="drag-handle"><i class="ph ph-dots-six-vertical"></i></td>
            <td>
                <div class="form-group floating-form mb-0">
                    <input type="text" class="form-control" name="__name__[label]" placeholder="e.g. Department review" required>
                    <label><?= lang('Step title', 'Titel des Schrittes') ?></label>
                </div>
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="1" name="__name__[index]" value="0">
            </td>
            <td>
                <select name="__name__[role]" class="form-control">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= htmlspecialchars($r) ?>"><?= strtoupper($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="__name__[orgScope]" class="form-control">
                    <option value="any"><?= lang('Any', 'Beliebig') ?></option>
                    <option value="same_org_only"><?= lang('Same unit only', 'Nur eigene Einheit') ?></option>
                </select>
            </td>
            <!-- <td class="text-center">
                <input type="checkbox" name="__name__[required]" value="1" checked>
            </td> -->
            <td class="text-center">
                <input type="checkbox" name="__name__[locksAfterApproval]" value="1">
            </td>
            <td class="step-actions">
                <button type="button" class="btn danger icon-only btn-delete" title="<?= lang('Remove', 'Löschen') ?>"><i class="ph ph-trash"></i></button>
            </td>
        </tr>
    </tbody>
</table>



<article class="box padded">

    <h4 class="title">
        <?= lang('Associated to activities', 'Mit Aktivitäten verknüpft') ?>
    </h4>

    <?php
    $activities = $osiris->adminCategories->find(['workflow' => $form['id'] ?? null])->toArray();
    if (empty($activities)) {
        echo '<p>' . lang('No activities are associated with this workflow.', 'Keine Aktivitäten sind mit diesem Workflow verknüpft.') . '</p>';
    } else {
    ?>
        <table class="table simple">
            <thead>
                <tr>
                    <th><?= lang('Category', 'Kategorie') ?></th>
                    <th><?= lang('Number of Activities', 'Anzahl der Aktivitäten') ?></th>
                    <th><?= lang('thereof with workflow', 'davon mit Workflow') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act) { ?>
                    <tr>
                        <td><a href="<?= ROOTPATH ?>/admin/categories/<?= $act['id'] ?>"><?= htmlspecialchars($act['name'] ?? $act['id']) ?></a></td>
                        <td><?= $osiris->activities->count(['type' => $act['id']]) ?></td>
                        <td><?= $osiris->activities->count(['type' => $act['id'], 'workflow' => ['$ne' => null]]) ?></td>
                        <td class="text-right">
                            <a href="#" class="btn-migrate"
                                data-category-id="<?= htmlspecialchars($act['id']) ?>"
                                data-category-name="<?= htmlspecialchars($act['name'] ?? $act['id']) ?>"
                                title="<?= lang('Migrate existing activities', 'Bestehende Aktivitäten migrieren') ?>">
                                <i class="ph ph-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php }
    ?>
</article>

<div class="modal" id="modal-migrate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('Migrate existing activities', 'Bestehende Aktivitäten migrieren') ?></h5>

            <div class="mb-10">
                <div><b><?= lang('Category', 'Kategorie') ?>:</b> <span id="mig-cat-name"></span></div>
                <div class="text-sm" id="mig-counts"></div>
            </div>

            <div class="form-group">
                <label class="required"><?= lang('Mode', 'Modus') ?></label>
                <div>
                    <label class="radio">
                        <input type="radio" name="mig-mode" value="attach-missing" checked>
                        <span><?= lang('Attach missing only (recommended)', 'Nur fehlende anhängen (empfohlen)') ?></span>
                    </label>
                    <label class="radio text-muted">
                        <input type="radio" disabled>
                        <span><?= lang('Upgrade compatible (coming soon)', 'Upgrade kompatibel (bald)') ?></span>
                    </label>
                    <label class="radio text-muted">
                        <input type="radio" disabled>
                        <span><?= lang('Hard replace (coming soon)', 'Hard replace (bald)') ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?= lang('Filters (optional)', 'Filter (optional)') ?></label>
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap:8px">
                    <input type="date" class="form-control" id="mig-from" placeholder="from">
                    <input type="date" class="form-control" id="mig-to" placeholder="to">
                </div>
            </div>

            <div class="flex items-center justify-between mt-10">
                <div>
                    <label class="checkbox">
                        <input type="checkbox" id="mig-dryrun" checked>
                        <span><?= lang('Dry-run first (show counts)', 'Erst Dry-run (nur Zählung)') ?></span>
                    </label>
                </div>
                <div>
                    <button class="btn" id="btn-mig-cancel"><?= lang('Close', 'Schließen') ?></button>
                    <button class="btn primary" id="btn-mig-apply">
                        <i class="ph ph-play"></i> <?= lang('Run', 'Ausführen') ?>
                    </button>
                </div>
            </div>

            <div class="mt-15" id="mig-result" style="display:none"></div>
        </div>
    </div>
</div>



<?php include_once BASEPATH . '/header-editor.php'; ?>
<script>
    (function() {
        const $modal = $('#modal-migrate');
        let currentCatId = null;
        const workflowId = <?= json_encode($form['id']) ?>;

        function openModal(catId, catName) {
            currentCatId = catId;
            $('#mig-cat-name').text(catName);
            $('#mig-result').hide().empty();
            $('#mig-dryrun').prop('checked', true);
            $('#mig-from').val('');
            $('#mig-to').val('');
            $('#mig-counts').text('<?= lang('Loading counts…', 'Zähle…') ?>');
            $modal.addClass('show');

            // Dry-run count
            fetchApply(true);
        }

        function closeModal() {
            $modal.removeClass('show');
        }

        function fetchApply(dryrun) {
            const payload = {
                category: currentCatId,
                mode: 'attach-missing',
                dryrun: dryrun,
                from: $('#mig-from').val() || null,
                to: $('#mig-to').val() || null
            };

            $('#btn-mig-apply').prop('disabled', true);
            $.ajax({
                url: '<?= ROOTPATH ?>/crud/workflows/apply/' + encodeURIComponent(workflowId),
                method: 'POST',
                data: payload,
                success: function(res) {
                    // res: { total, withWorkflow, withoutWorkflow, willUpdate, updatedCount, skippedCount }
                    if (dryrun) {
                        $('#mig-counts').html(
                            '<?= lang('Total', 'Gesamt') ?>: <b>' + res.total +
                            '</b> — <?= lang('with workflow', 'mit Workflow') ?>: <b>' + res.withWorkflow +
                            '</b> — <?= lang('without', 'ohne') ?>: <b>' + res.withoutWorkflow + '</b><br>' +
                            '<?= lang('Will attach to', 'Wird anhängen an') ?>: <b>' + res.willUpdate + '</b>'
                        );
                    } else {
                        $('#mig-result').show().html(
                            '<div class="alert success"><?= lang('Done', 'Fertig') ?>: ' +
                            '<?= lang('updated', 'aktualisiert') ?> <b>' + res.updatedCount + '</b>, ' +
                            '<?= lang('skipped', 'übersprungen') ?> <b>' + res.skippedCount + '</b>.</div>'
                        );
                        // Tabelle nachziehen: ersetze die Zelle "davon mit Workflow"
                        $('a.btn-migrate[data-category-id="' + currentCatId + '"]').closest('tr').find('td').eq(2).text(res.withWorkflow + res.updatedCount);
                        // (optional) location.reload();
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.error || xhr.statusText || 'Error';
                    $('#mig-result').show().html('<div class="alert danger">' + msg + '</div>');
                },
                complete: function() {
                    $('#btn-mig-apply').prop('disabled', false);
                }
            });
        }

        // Wiring
        $(document).on('click', '.btn-migrate', function(e) {
            e.preventDefault();
            openModal($(this).data('category-id'), $(this).data('category-name'));
        });
        $('#btn-mig-cancel, a[href="#close-modal"]').on('click', function(e) {
            e.preventDefault();
            closeModal();
        });
        $('#btn-mig-apply').on('click', function() {
            const dry = $('#mig-dryrun').is(':checked');
            if (dry) {
                fetchApply(true);
                $('#mig-dryrun').prop('checked', false); // nächster Klick führt aus
                return;
            }
            if (!confirm('<?= lang('Apply to existing activities now?', 'Jetzt auf bestehende Aktivitäten anwenden?') ?>')) return;
            fetchApply(false);
        });
    })();

    // minimal jQuery helpers (keine externen Abhängigkeiten)
    function reindexSteps() {
        $('#steps-tbody .step-row').each(function(idx) {
            $(this).find('input, select, textarea').each(function() {
                const n = $(this).attr('name');
                if (!n) return;
                // ersetze __name__ oder [<altIndex>] durch [idx]
                const newName = n
                    .replace(/values\[steps]\[\d+]/, 'values[steps][' + idx + ']')
                    .replace(/__name__/, 'values[steps][' + idx + ']');
                $(this).attr('name', newName);
            });
        });
    }

    function addStepRow() {
        const $tpl = $('#step-template').clone().removeAttr('id').removeClass('d-none');
        $('#steps-tbody').append($tpl);
        reindexSteps();
    }

    $('#btn-add-step').on('click', addStepRow);

    $('#steps-tbody')
        .on('click', '.btn-delete', function() {
            const rows = $('#steps-tbody .step-row').length;
            if (rows <= 1) {
                alert('At least one step is required.');
                return;
            }
            $(this).closest('tr').remove();
            reindexSteps();
        })
    // .on('click', '.btn-up', function(){
    //   const $row = $(this).closest('tr');
    //   const $prev = $row.prev('.step-row');
    //   if ($prev.length) { $row.insertBefore($prev); reindexSteps(); }
    // })
    // .on('click', '.btn-down', function(){
    //   const $row = $(this).closest('tr');
    //   const $next = $row.next('.step-row');
    //   if ($next.length) { $row.insertAfter($next); reindexSteps(); }
    // });

    // Optional: Drag&Drop Sort, falls jQuery UI vorhanden
    if ($.fn.sortable) {
        $('#steps-tbody').sortable({
            handle: '.drag-handle',
            placeholder: 'placeholder',
            helper: function(e, tr) {
                const $orig = tr.children();
                const $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($orig.eq(index).width());
                });
                return $helper;
            },
            stop: reindexSteps
        });
    }

    // sehr einfache Validierung beim Submit
    $('#workflow-form').on('submit', function(e) {
        let valid = true;
        $('#steps-tbody .step-row').each(function() {
            const title = $(this).find('input[name*="[label]"]').val().trim();
            if (!title) {
                valid = false;
                $(this).find('input[name*="[label]"]').focus();
                return false;
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Please fill all required step titles.');
        }
    });
</script>