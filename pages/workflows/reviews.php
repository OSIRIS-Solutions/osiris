<?php

/**
 * Workflow Reviews
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 *
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph ph-highlighter"></i>
    <?= lang('My Reviews', 'Meine Überprüfungen') ?>
</h1>

<div class="">
    <div class="mb-10">
        <div class="input-group">
            <input id="q" class="form-control" placeholder="<?= lang('Search', 'Suche') ?>">
            <div class="input-group-append">
                <button class="btn" id="refresh"><i class="ph ph-arrow-clockwise"></i></button>
            </div>
        </div>
    </div>

    <div class="filters mb-10 d-flex align-items-center" style="gap:1rem;">
        <select id="f-category" class="form-control w-auto">
            <option value=""><?= lang('All categories', 'Alle Kategorien') ?></option>
            <?php
            $cats = $osiris->adminCategories->find([], ['sort' => ['order' => 1]])->toArray();
            foreach ($cats as $cat) {
                echo '<option value="' . htmlspecialchars($cat['id']) . '">' . lang($cat['name'], $cat['name_de'] ?? null) . '</option>';
            }
            ?>
        </select>
        <input type="date" id="f-since" class="form-control w-auto" placeholder="<?= lang('Since', 'Seit') ?>">
        <label class="w-200 m-0"><input type="checkbox" id="f-scope" class="form-check-input"> <?= lang('Same OU only', 'Nur eigene OU') ?></label>
    </div>

    <table class="table" id="review-list">
        <thead>
            <tr>
                <th style="width:28px"></th>
                <th><?= lang('Activity', 'Aktivität') ?></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="flex justify-between mt-10">
        <div id="pager"></div>
        <button class="btn success" id="batch-approve" disabled>
            <i class="ph ph-check"></i> <?= lang('Approve selected', 'Auswahl freigeben') ?>
        </button>
    </div>
</div>

<script>
    (function() {
        let page = 1,
            pageSize = 25,
            items = [];
        const $tb = $('#review-list tbody');

        function load() {
            const params = {
                q: $('#q').val() || '',
                role: $('#f-role').val() || '',
                category: $('#f-category').val() || '',
                ou: $('#f-ou').val() || '',
                since: $('#f-since').val() || '',
                scope: $('#f-scope').is(':checked') ? 'same_org_only' : '',
                page,
                pageSize
            };
            $.getJSON('<?= ROOTPATH ?>/api/workflow-reviews/list', params, function(res) {
                items = res.items || [];
                render();
            });
        }

        function render() {
            $tb.empty();
            if (!items.length) {
                $tb.append('<tr><td colspan="7"><em><?= lang('Nothing to review', 'Nichts zu prüfen') ?></em></td></tr>');
                return;
            }
            for (const it of items) {
                const row = `
        <tr data-id="${it.id}" data-step="${it.step.id}">
          <td><input type="checkbox" class="sel"></td>
          <td>
                <a href="<?= ROOTPATH ?>/activities/view/${it.id}" target="_blank" class="colorless link">${it.title}</a>
                <p class="text-muted font-size-12 mb-0 d-flex align-items-center justify-content-between">
                    <span>
                        <b>${lang('Completed', 'Abgeschlossen')}</b>: ${it.completed} | 
                        <b>${lang('Your Step', 'Dein Schritt')}</b>: ${it.step.label}
                    </span>

                    <span class="">
                        <button class="btn small success btn-approve"><i class="ph ph-check"></i></button>
                        <button class="btn small text-danger btn-reject"><i class="ph ph-x"></i></button>
                        <a href="<?= ROOTPATH ?>/activities/view/${it.id}" target="_blank" class="btn small"><i class="ph ph-arrow-right"></i></a>
                    </span>
                </p>
          </td>
        </tr>`;
                $tb.append(row);
            }
        }

        // actions
        $(document).on('click', '.btn-approve', function() {
            const $tr = $(this).closest('tr'),
                id = $tr.data('id'),
                step = $tr.data('step');
            $.post('<?= ROOTPATH ?>/crud/activities/workflow/approve/' + id, {
                stepId: step
            }, function(res) {
                if (res.status === 'ok') {
                    $tr.remove();
                } else toastError(res.error || 'Error');
            }, 'json');
        });

        $(document).on('click', '.btn-reject', function() {
            const $tr = $(this).closest('tr'),
                id = $tr.data('id'),
                step = $tr.data('step');
            const comment = prompt("<?= lang('Comment', 'Kommentar') ?>");
            if (comment === null) return;
            $.post('<?= ROOTPATH ?>/crud/activities/workflow/reject/' + id, {
                stepId: step,
                comment
            }, function(res) {
                if (res.status === 'ok') {
                    $tr.remove();
                } else toastError(res.error || 'Error');
            }, 'json');
        });

        // batch
        $('#review-list').on('change', '.sel', function() {
            const any = $('.sel:checked').length > 0;
            $('#batch-approve').prop('disabled', !any);
        });
        $('#batch-approve').on('click', function() {
            const rows = $('.sel:checked').closest('tr').toArray();
            if (!rows.length) return;
            if (!confirm('<?= lang('Approve all selected?', 'Alle ausgewählten freigeben?') ?>')) return;

            // naive sequential (einfach halten)
            (async () => {
                for (const tr of rows) {
                    const $tr = $(tr),
                        id = $tr.data('id'),
                        step = $tr.data('step');
                    await $.post('<?= ROOTPATH ?>/crud/activities/workflow/approve/' + id, {
                        stepId: step
                    });
                    $tr.remove();
                }
            })();
        });

        // load & filters
        $('#q,#f-role,#f-category,#f-ou,#f-since,#f-scope,#refresh').on('input click change', function() {
            page = 1;
            load();
        });

        function escapeHtml(s) {
            return ('' + s).replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]));
        }

        load();
    })();
</script>