<?php

/**
 * Page to add or edit journal
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal/add
 * @link        /journal/edit/<journal_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1><i class="ph ph-stack-plus text-primary"></i>
    <?php
    if ($id === null || empty($data)) {
        echo lang("Add journal", "Journal hinzufügen");
    } else {
        echo $data['journal'];
    }
    ?>
</h1>

<!-- 
<?php
dump($data, true);
?> -->

<?php
if ($id === null || empty($data)) {
    $formaction = ROOTPATH . "/crud/journal/create";
    $url = ROOTPATH . "/journal/view/*";
} else {
    $formaction = ROOTPATH . "/crud/journal/update/$id";
    $url = ROOTPATH . "/journal/view/$id";
}
?>


<form action="<?= $formaction ?>" method="post">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

    <div class="form-row row-eq-spacing-sm">
        <div class="col-sm">
            <label for="journal" class="required"><?= lang('Journal name', 'Name des Journals') ?></label>
            <input type="text" name="values[journal]" id="journal" class="form-control" value="<?= $data['journal'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label for="abbr"><?= lang('Abbr. name', 'Abkürzung') ?></label>
            <input type="text" name="values[abbr]" id="abbr" class="form-control" value="<?= $data['abbr'] ?? '' ?>">
        </div>
    </div>

    <div class="form-row row-eq-spacing-sm">
        <div class="col-sm">
            <label class="required" for="issn"><?= lang('ISSN') ?></label>
            <?php
            $issn = "";
            if (isset($data['issn'])) {
                $issn = $data['issn'];
                try {
                    $issn = DB::doc2Arr($issn);
                } catch (\Throwable $th) {
                }
                if (is_array($issn)) $issn = implode(' ', $issn);
            }
            ?>
            <input type="text" name="values[issn]" id="issn" class="form-control" value="<?= $issn ?>">
        </div>
        <div class="col-sm">
            <label for="publisher" class="required">Publisher</label>
            <input type="text" name="values[publisher]" id="publisher" class="form-control" value="<?= $data['publisher'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label for="open_access">Open access <?= lang('seit', 'since') ?></label>
            <?php
            $oa = $data['oa'] ?? 'false';
            if ($oa === false) $oa = 'false';
            ?>

            <input type="text" name="values[oa]" id="oa" class="form-control" value="<?= $oa ?>">
            <small class="text-muted">
                </code>
                <?= lang(
                    'enter <code class="code">false</code> if not open access, <code class="code">0</code> if always, <code class="code">2010</code> if since 2010 ',
                    'Trage ein: <code class="code">false</code> wenn nicht open access, <code class="code">0</code> wenn immer OA, <code class="code">2010</code> wenn OA seit 2010 '
                ) ?>
            </small>
        </div>

    </div>


    <button type="submit" class="btn secondary">
        Update
    </button>
</form>