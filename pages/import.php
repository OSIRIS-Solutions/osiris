<?php

/**
 * Page to import activities
 * 
 * e.g. from file or Google Scholar
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /expertise
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$googlescholar = $Settings->featureEnabled('googlescholar', true);
$openalex = $Settings->featureEnabled('openalex', true);
$orcid = $Settings->featureEnabled('orcid', false);

require_once BASEPATH . '/php/Orcid.php';


?>

<h1>
    <i class="ph-duotone ph-upload"></i>
    Import
</h1>

<?php if (!$googlescholar && !$openalex && !$orcid) { ?>
    <div class="alert danger">
        <h2 class="title">
            <?= lang('import.import_not_available') ?>
        </h2>
        <p>
            <?= lang('import.the_import_feature_is_not_available_for_your_institute') ?>
        </p>
    </div>
<?php } ?>


<!-- import from OpenAlex -->
<?php
if ($openalex) {
    $affiliation = $Settings->get('affiliation_details');
    if (!empty($affiliation['openalex'] ?? null)) { ?>
        <div class="box">

            <div class="content">
                <b class="badge success">
                    RECOMMENDED
                </b>
                <h2 class="title mt-10">OpenAlex Import</h2>
                <p>
                    <?= lang('import.you_can_import_data_from_openalex_this_method_is_very_reliable_so_we_recomm') ?>
                </p>
                <p>
                    <b>
                        <?= lang('import.how_you_can_find_your_openalex_id') ?>
                    </b>
                </p>

                <ol class="list success">
                    <li>
                        <?= lang('import.go_to_openalex_and_search_for_your_name_or_for_one_of_your_publications') ?>
                    </li>
                    <li>
                        <?= lang('import.click_on_one_of_your_publications_a_side_window_will_open_showing_the_detai') ?>
                    </li>
                    <li>
                        <?= lang('import.you_are_now_on_your_openalex_profile_page_to_import_all_the_publications_sh') ?>
                    </li>

                    <form action="<?= ROOTPATH ?>/import/openalex" method="get">
                        <div class="form-group">
                            <label for="openalex-id">OpenAlex ID</label>
                            <input type="text" name="openalex-id" id="openalex-id" class="form-control" required>
                        </div>
                        <button type="submit" class="btn">Import</button>
                    </form>
            </div>


        </div>

    <?php } else { ?>
        <div class="box">
            <div class="content">
                <h2 class="title">OpenAlex Import</h2>
                <p>
                    <?= lang('import.your_institute_must_add_the_institutional_openalex_id_in_their_general_sett') ?>
                </p>
            </div>
        </div>
<?php }
} ?>

<?php
if ($googlescholar) {
    if (!empty($USER['google_scholar'] ?? null)) { ?>

        <div class="box">
            <div class="content">
                <h2 class="title">Google Scholar Import</h2>
                <p>
                    <?= lang('import.you_can_import_data_from_your_google_scholar_account') ?>:
                </p>
                <p class="mt-0 font-size-16 font-weight-bold">
                    Account-ID: <a href="https://scholar.google.com/citations?user=<?= $USER['google_scholar'] ?>"><?= $USER['google_scholar'] ?></a>
                </p>

                <p class="font-size-12 text-muted">
                    <?= lang('import.please_note_that_only_the_100_latest_entries_can_be_imported') ?>
                </p>

                <form action="<?= ROOTPATH ?>/import/googlescholar/<?= $USER['google_scholar'] ?>" method="get">
                    <button type="submit" class="btn">Import</button>
                </form>
            </div>
        </div>

    <?php } else { ?><!-- if empty(USER[googlescholar]) -->
        <div class="box">
            <div class="content">
                <h2 class="title">Google Scholar Import</h2>
                <p>
                    <?= lang('import.you_must_connect_a_google_scholar_account_to_your_profile_to_use_this_featu') ?>
                </p>

                <a href="<?= ROOTPATH ?>/user/edit/<?= $_SESSION['username'] ?>" class="btn"><?= lang('import.update_profile') ?></a>

            </div>
        </div>

<?php }
}
?>

<?php
if($orcid) {
    $orcid = new Orcid_Settings();
    if ($orcid->client_id && $orcid->client_secret) {
        if ($USER['orcid_validated'] ?? null) { ?>

            <div class="box">
                <div class="content">
                    <h2 class="title">ORCID Import</h2>
                    <p>
                        <?= lang('import.you_can_import_data_from_your_orcid_account') ?>:
                    </p>
                    <p class="mt-0">
                        Account-ID: <a href="<?= $orcid->api_base_url . $USER['orcid'] ?>"><?= $USER['orcid'] ?></a>
                    </p>

                    <form action="<?= ROOTPATH ?>/orcid/import" method="get">
                        <button type="submit" class="btn">Import</button>
                    </form>
                </div>
            </div>

        <?php } else { ?><!-- if empty(USER[orcid]) -->
            <div class="box">
                <div class="content">
                    <h2 class="title">ORCID Import</h2>
                    <p>
                        <?= lang('import.you_must_connect_an_orcid_account_to_your_profile_to_use_this_feature') ?>
                    </p>

                    <a href="<?= ROOTPATH ?>/user/edit/<?= $_SESSION['username'] ?>#section-contact" class="btn"><?= lang('import.update_profile') ?></a>

                </div>
            </div>

    <?php }
    }
}
?>




<!-- 

<div class="box box-signal">
    <div class="content">
        <h2 class="title">
            <?= lang('common.import_activities_from_file') ?>
        </h2>
        <form action="<?= ROOTPATH ?>/crud/import/file" method="post" enctype="multipart/form-data">
            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
            <div class="custom-file mb-20" id="file-input-div" >
                <input type="file" id="file-input" name="file" data-default-value="<?= lang('common.no_file_chosen') ?>">
                <label for="file-input"><?= lang('common.upload_a_bibtex_file') ?></label>
                <br><small class="text-danger">Max. 16 MB.</small>
            </div>

            <div class="form-group">
                <label for="">Format:</label>
                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-bibtex" value="bibtex" checked="checked" >
                    <label for="format-bibtex">BibTeX</label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-nbib" value="nbib">
                    <label for="format-nbib">NBIB (Pubmed)</label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-ris" value="ris">
                    <label for="format-ris">RIS</label>
                </div>
            </div>

            <button class="btn secondary">
                <i class="ph ph-upload"></i>
                Upload
            </button>
        </form>
    </div>
</div>
 -->