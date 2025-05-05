<?php

/**
 * Page to edit finance related information of a proposal.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$grant_years = $project['grant_years'] ?? array();
$formaction = ROOTPATH . "/crud/proposals/finance/" . $form['_id'];
$url = ROOTPATH . "/proposals/view/" . $form['_id'];
?>

<div class="container">

    <form action="<?= $formaction ?>" method="post" id="proposal-form">
        <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">
        <h2><?= lang('Third-party funding per year', 'Drittmitteleinnahmen pro Jahr') ?></h2>
        <table class="table mb-20">
            <thead>
                <tr>
                    <th><?=lang('Year', 'Jahr')?></th>
                    <th><?=lang('Amount', 'Summe')?> in EUR</th>
                </tr>
            </thead>
            <tbody id="grant-years">
                <?php foreach ($grant_years as $year => $amount): ?>
                    <tr>
                        <td><input required class="form-control" type="number" step="1" min="1900" max="2050" name="values[grant_years][]" value="<?= $year ?>" /></td>
                        <td><input required class="form-control" type="number" step="0.01" name="values[grant_amounts][]" value="<?= $amount ?>" /></td>
                        <td>
                            <button type="button" class="btn" onclick="$(this).closest('tr').remove()">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <button type="button" class="btn" id="add-year" onclick="addYear()">
                            <i class="ph ph-plus"></i>
                            <?= lang('Add Year', 'Jahr hinzufügen') ?>
                        </button>
                    </td>
                    </td>
                </tr>
            </tfoot>
        </table>

        <button type="submit" class="btn primary">Save Changes</button>
    </form>
</div>

<script>
    function addYear() {
        const tableBody = $('#grant-years');
        const newRow = `
            <tr>
                <td><input required class="form-control" type="number" step="1" min="1900" max="2050" name="values[grant_years][]" /></td>
                <td><input required class="form-control" type="number" step="0.01" name="values[grant_amounts][]" /></td>
                <td>
                    <button type="button" class="btn" onclick="$(this).closest('tr').remove()">
                        <i class="ph ph-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tableBody.append(newRow);
    };
</script>