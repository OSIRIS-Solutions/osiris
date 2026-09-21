<?php

/**
 * Admin Workflow Edit Page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
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
        <h4 class="header"><?= e($name) ?></h4>
        <div class="content">
            <p><b>ID:</b> <code class="code"><?= $form['id'] ?></code></p>
            <div class="form-group">
                <label for="name" class="required"><?= lang('common.name_of_the_workflow') ?></label>
                <input type="text" class="form-control" name="values[name]" required value="<?= e($form['name'] ?? '') ?>" maxlength="30">
                <small class="form-text text-muted"><?= lang('common.max_30_characters') ?></small>
            </div>
        </div>
        <hr>
        <div class="content">
            <h5><?= lang('common.steps') ?></h5>

            <table id="steps-table" class="table mb-20">
                <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th><?= lang('admin.step_title') ?></th>
                        <th style="width:90px"><?= lang('admin.phase') ?>*</th>
                        <th style="width:200px"><?= lang('common.role') ?></th>
                        <th style="width:130px"><?= lang('admin.ou_scope') ?></th>
                        <th style="width:100px"><?= lang('admin.required') ?></th>
                        <th style="width:120px"><?= lang('admin.lock_after') ?></th>
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
                                    <input type="text" class="form-control" name="values[steps][<?= $i ?>][label]" value="<?= e($s['label'] ?? '') ?>" placeholder="e.g. Department review" required>
                                    <label><?= lang('admin.step_title_workflow') ?></label>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control" min="0" step="1" name="values[steps][<?= $i ?>][index]" value="<?= intval($s['index'] ?? 0) ?>">
                            </td>
                            <td>
                                <select name="values[steps][<?= $i ?>][role]" class="form-control">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= e($r) ?>" <?= (($s['role'] ?? '') === $r ? 'selected' : '') ?>><?= strtoupper($r) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="values[steps][<?= $i ?>][orgScope]" class="form-control">
                                    <?php $scope = $s['orgScope'] ?? 'any'; ?>
                                    <option value="any" <?= $scope === 'any' ? 'selected' : '' ?>><?= lang('admin.any') ?></option>
                                    <option value="same_org_only" <?= $scope === 'same_org_only' ? 'selected' : '' ?>><?= lang('admin.same_unit_only') ?></option>
                                </select>
                            </td>
                            <td class="text-center">
                                <?php $reqd = !isset($s['required']) ? true : (bool)$s['required']; ?>
                                <input type="checkbox" name="values[steps][<?= $i ?>][required]" value="1" <?= $reqd ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="values[steps][<?= $i ?>][locksAfterApproval]" value="1" <?= !empty($s['locksAfterApproval']) ? 'checked' : '' ?>>
                            </td>
                            <td class="step-actions">
                                <button type="button" class="btn danger icon-only btn-delete" title="<?= lang('admin.remove') ?>"><i class="ph ph-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <button class="btn" type="button" id="btn-add-step">
                                <i class="ph ph-plus-circle"></i> <?= lang('admin.add_step') ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <p class="text-sm text-muted">
                * <?= lang('admin.multiple_steps_with_the_same_phase_number_are_executed_in_parallel_all_othe') ?><br>
            </p>

            <button type="submit" class="btn success" id="submitBtn">
                <i class="ph ph-check"></i> <?= lang('action.update') ?>
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
                    <label><?= lang('admin.step_title_workflow') ?></label>
                </div>
            </td>
            <td>
                <input type="number" class="form-control" min="0" step="1" name="__name__[index]" value="0">
            </td>
            <td>
                <select name="__name__[role]" class="form-control">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e($r) ?>"><?= strtoupper($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="__name__[orgScope]" class="form-control">
                    <option value="any"><?= lang('admin.any') ?></option>
                    <option value="same_org_only"><?= lang('admin.same_unit_only') ?></option>
                </select>
            </td>
            <td class="text-center">
                <input type="checkbox" name="__name__[required]" value="1" checked>
            </td>
            <td class="text-center">
                <input type="checkbox" name="__name__[locksAfterApproval]" value="1">
            </td>
            <td class="step-actions">
                <button type="button" class="btn danger icon-only btn-delete" title="<?= lang('admin.remove') ?>"><i class="ph ph-trash"></i></button>
            </td>
        </tr>
    </tbody>
</table>



<article class="box padded">

    <h4 class="title">
        <?= lang('admin.associated_to_activities') ?>
    </h4>

    <?php
    $activities = $osiris->adminCategories->find(['workflow' => $form['id'] ?? null])->toArray();
    if (empty($activities)) {
        echo '<p>' . lang('admin.no_activities_are_associated_with_this_workflow') . '</p>';
    } else {
    ?>
        <table class="table simple">
            <thead>
                <tr>
                    <th><?= lang('common.category') ?></th>
                    <th><?= lang('common.number_of_activities') ?></th>
                    <th><?= lang('admin.thereof_with_workflow') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act) { ?>
                    <tr>
                        <td><a href="<?= ROOTPATH ?>/admin/categories/<?= $act['id'] ?>"><?= e($act['name'] ?? $act['id']) ?></a></td>
                        <td><?= $osiris->activities->count(['type' => $act['id']]) ?></td>
                        <td><?= $osiris->activities->count(['type' => $act['id'], 'workflow' => ['$ne' => null]]) ?></td>
                        <td class="text-right">
                            <a href="#" class="btn-migrate"
                                data-category-id="<?= e($act['id']) ?>"
                                data-category-name="<?= e($act['name'] ?? $act['id']) ?>"
                                title="<?= lang('admin.migrate_existing_activities') ?>">
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
            <h5 class="title"><?= lang('admin.migrate_existing_activities') ?></h5>

            <div class="mb-10">
                <div><b><?= lang('common.category') ?>:</b> <span id="mig-cat-name"></span></div>
                <div class="text-sm" id="mig-counts"></div>
            </div>

            <div class="form-group">
                <label class="required"><?= lang('common.mode') ?></label>
                <div>
                    <label class="radio">
                        <input type="radio" name="mig-mode" value="attach-missing" checked>
                        <span><?= lang('admin.attach_missing_only_recommended') ?></span>
                    </label>
                    <label class="radio text-muted">
                        <input type="radio" disabled>
                        <span><?= lang('admin.upgrade_compatible_coming_soon') ?></span>
                    </label>
                    <label class="radio text-muted">
                        <input type="radio" disabled>
                        <span><?= lang('admin.hard_replace_coming_soon') ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?= lang('admin.filters_optional') ?></label>
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap:8px">
                    <input type="date" class="form-control" id="mig-from" placeholder="from">
                    <input type="date" class="form-control" id="mig-to" placeholder="to">
                </div>
            </div>

            <div class="flex items-center justify-between mt-10">
                <div>
                    <label class="checkbox">
                        <input type="checkbox" id="mig-dryrun" checked>
                        <span><?= lang('admin.dry_run_first_show_counts') ?></span>
                    </label>
                </div>
                <div>
                    <button class="btn" id="btn-mig-cancel"><?= lang('action.close') ?></button>
                    <button class="btn primary" id="btn-mig-apply">
                        <i class="ph ph-play"></i> <?= lang('admin.run') ?>
                    </button>
                </div>
            </div>

            <div class="mt-15" id="mig-result" style="display:none"></div>
        </div>
    </div>
</div>


<!-- crud/workflows/delete/(.*) -->
<?php if (empty($activities)) { ?>
    <div class="dropdown">
        <button class="btn danger" data-toggle="dropdown" type="button" id="delete-workflow" aria-haspopup="true" aria-expanded="false">
            <i class="ph ph-trash"></i>
            <?= lang('admin.delete_workflow') ?>
        </button>
        <div class="dropdown-menu" aria-labelledby="delete-workflow">
            <form action="<?= ROOTPATH ?>/crud/workflows/delete/<?= ($form['_id']) ?>" method="post" class="content">
                <?= lang('admin.are_you_sure_you_want_to_delete_this_workflow_this_action_cannot_be_undone') ?>
                <button type="submit" class="btn danger block">
                    <i class="ph ph-trash"></i>
                    <?= lang('admin.yes_delete_workflow') ?>
                </button>
            </form>
        </div>
    </div>
<?php } else { ?>
    <button class="btn danger" disabled title="<?= lang('admin.cannot_delete_workflow_while_associated_to_activities') ?>">
        <i class="ph ph-trash"></i>
        <?= lang('admin.delete_workflow') ?>
    </button>
<?php } ?>


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
            $('#mig-counts').text('<?= lang('admin.loading_counts') ?>');
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
                            '<?= lang('common.total') ?>: <b>' + res.total +
                            '</b> — <?= lang('admin.with_workflow') ?>: <b>' + res.withWorkflow +
                            '</b> — <?= lang('admin.without') ?>: <b>' + res.withoutWorkflow + '</b><br>' +
                            '<?= lang('admin.will_attach_to') ?>: <b>' + res.willUpdate + '</b>'
                        );
                    } else {
                        $('#mig-result').show().html(
                            '<div class="alert success"><?= lang('common.done') ?>: ' +
                            '<?= lang('admin.updated') ?> <b>' + res.updatedCount + '</b>, ' +
                            '<?= lang('admin.skipped') ?> <b>' + res.skippedCount + '</b>.</div>'
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
            if (!confirm('<?= lang('admin.apply_to_existing_activities_now') ?>')) return;
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