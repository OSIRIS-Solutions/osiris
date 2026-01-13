<?php

/**
 * Page to preview a single infrastructure
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.7.1
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$name = $data['name'];
?>
<style>
    .infrastructure-logo {
        width: 200px;
        height: 150px;
        object-fit: contain;
        border-radius: 8px;
        border: 1px solid #ddd;
        background-color: #fff;
        padding: 10px;
    }

    img.profile-img.small {
        max-width: 10rem;
        max-height: 10rem;
    }

    .on-this-page-nav {
        z-index: 10;
        top: 0;
    }
</style>

<?php if ($Portfolio->isPreview()) { ?>
    <!-- adjust style for a top margin of 4rem for all links and fixed -->
    <style>
        .content-wrapper {
            scroll-padding-top: 6rem;

        }

        .on-this-page-nav {
            top: 9rem !important;
        }
    </style>
<?php } ?>

<div class="container-lg">
    <?php if (!$data): ?>
        <p><?= lang("Infrastructure not found", "Infrastruktur nicht gefunden"); ?></p>
    <?php else: ?>

        <div class="profile-header" style="display: flex; align-items: center">
            <?php if (empty($data['inactive'])): ?>
                <div class="col mr-20" style="flex-grow: 0">
                    <?php
                    echo $data['logo'] ?? '';
                    ?>
                </div>
            <?php endif; ?>
            <div class="col" id="infrastructure-header-info">
                <h1 class="title">
                    <?= lang($data['name'], $data['name_de'] ?? null) ?>
                </h1>

                <h2 class="subtitle">
                    <?= lang($data['subtitle'], $data['subtitle_de'] ?? null) ?>
                </h2>
            </div>
        </div>

        <div class="row row-eq-spacing">
            <div class="col-sm-8 order-sm-first order-last" id="about">
                <?php if (!empty($data['description']) || !empty($data['description_de']) || !empty($data['link'])) { ?>
                    <h3 id="about"><?= lang("About ".$name, "Über ".$name); ?></h3>

                    <div class="mt-20">
                        <?= lang($data['description'], $data['description_de'] ?? null); ?>
                    </div>
                    <?php if (isset($data['link'])) { ?>
                        <a class="btn primary" href="<?= htmlspecialchars($data['link']) ?>" target="_blank" rel="noopener noreferrer">
                            <i class="ph ph-globe"></i>
                            <?= lang('Visit website', 'Webseite besuchen') ?>
                        </a>
                    <?php } ?>
                <?php } ?>

                <?php
                $persons = $data['persons'] ?? [];
                if (count($persons) > 0): ?>

                    <h3 id="staff">
                        <?= lang("Staff managing ".$name, "Das Team hinter ".$name) ?>
                    </h3>

                    <div class="row row-eq-spacing">
                        <?php
                        foreach ($persons as $person) {
                        ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center box mt-0 p-10">

                                    <?= $person['img'] ?>
                                    <div class="">
                                        <h5 class="my-0">
                                            <a href="<?= $base ?>/person/<?= $person['id'] ?>" class="colorless">
                                                <?= $person['displayname'] ?>
                                            </a>
                                        </h5>
                                        <?= $person['role'] ?? '' ?>
                                        <?php
                                        if (!empty($person['depts'])) {
                                            foreach ($person['depts'] as $d => $dept) {
                                        ?>
                                                <br>
                                                <a href="<?= $base ?>/group/<?= $d ?>">
                                                    <?= lang($dept['en'] ?? '', $dept['de'] ?? null) ?>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                <?php endif; ?>

                <?php if (($data['n_activities'] ?? 0) > 0) { ?>

                    <h3 id="activities">
                        <?= lang("Activities involving ".$name, "Aktivitäten, die ".$name." involvieren") ?>
                    </h3>
                    <div class="pb-10">
                        <table class="table datatable" id="activity-table"
                            data-table="activities"
                            data-source="./activities.json"
                            data-lang="<?= lang('en', 'de') ?>">
                            <thead>
                                <tr>
                                    <th data-col="icon" data-orderable="false" data-searchable="false">Type</th>
                                    <th data-col="html" data-search-col="search">Title</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <?php if ($Portfolio->isPreview()): ?>
                        <script>
                            $(document).ready(function() {
                                $('#activity-table').DataTable({
                                    "ajax": {
                                        "url": ROOTPATH + '/portfolio/infrastructure/<?= $id ?>/activities',
                                        dataSrc: 'data'
                                    },
                                    "sort": false,
                                    "pageLength": 6,
                                    "lengthChange": false,
                                    "searching": false,
                                    "pagingType": "numbers",
                                    columnDefs: [{
                                            targets: 0,
                                            data: 'icon',
                                            className: 'w-50'
                                        },
                                        {
                                            targets: 1,
                                            data: 'html',
                                            render: function(data, type, row) {
                                                // replace links to activities with links to the activity page
                                                data = data.replace(/href='\//g, "href='<?= $base ?>/");
                                                return data;
                                            }
                                        },
                                    ],
                                });
                            });
                        </script>
                    <?php endif; ?>
                <?php } ?>



                <?php if ($data['collaborative'] ?? false) { ?>
                    <div id="collaborative">
                        <h3>
                            <?= lang('This is a collaborative infrastructure', 'Dies ist eine kollaborative Infrastruktur') ?>
                        </h3>

                        <h6>
                            <?= lang('Coordinated by', 'Koordiniert durch') ?>
                        </h6>
                        <table class="table">

                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($data['coordinator']['type'] == 'self') { ?>
                                                <span class="badge mr-10 success">
                                                    <i class="ph ph-map-pin-area ph-fw ph-2x m-0"></i>
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge mr-10">
                                                    <?= Organization::getIcon($org['type'], 'ph-fw ph-2x m-0') ?>
                                                </span>
                                            <?php } ?>
                                            <div>
                                                <b><?= $data['coordinator']['name'] ?></b>
                                                <br>
                                                <?= $data['coordinator']['location'] ?? '' ?>
                                                <?php if (isset($data['coordinator']['ror'])) { ?>
                                                    <a href="<?= $data['coordinator']['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    </td>
                                </tr>

                            </tbody>
                        </table>

                        <h6>
                            <?= lang('Together with the following partners', 'Zusammen mit den folgenden Partnern') ?>
                        </h6>
                        <table class="table">
                            <tbody>
                                <?php if (empty($data['collaborators'])) { ?>
                                    <tr>
                                        <td colspan="2">
                                            <?= lang('No partners connected.', 'Keine Partner verknüpft.') ?>
                                        </td>
                                    </tr>
                                    <?php } else foreach ($data['collaborators'] as $org) {
                                    if ($org && isset($org['name'])) { ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($org['type'] == 'self') { ?>
                                                        <span class="badge mr-10 success">
                                                            <i class="ph ph-map-pin-area ph-fw ph-2x m-0"></i>
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="badge mr-10">
                                                            <?= Organization::getIcon($org['type'], 'ph-fw ph-2x m-0') ?>
                                                        </span>
                                                    <?php } ?>
                                                    <div class="">
                                                        <?= $org['name'] ?><br>
                                                        <?= $org['location'] ?>
                                                        <?php if (isset($org['ror'])) { ?>
                                                            <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                                        <?php } ?>

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                <?php  }
                                }  ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

            </div>

            <div class="col-sm-4 position-relative">
                <h3><?= lang("Details", "Details"); ?></h3>
                <table class="table small">
                    <tbody>
                        <?php if (!empty($data['contact_email'])): ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang("Contact Email", "Kontakt E-Mail") ?></span>
                                    <a href="mailto:<?= htmlspecialchars($data['contact_email']) ?>"><?= htmlspecialchars($data['contact_email']) ?></a>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td>
                                <span class="key"><?= lang("Operating Period", "Betriebszeitraum") ?></span>
                                <?php
                                echo fromToDate($data['start_date'], $data['end_date'] ?? null, true);
                                ?>
                            </td>
                        </tr>

                        <?php if (!empty($data['category'])): ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang("Category", "Kategorie"); ?></span>
                                    <?= htmlspecialchars($data['category']) ?>
                                </td>
                            </tr>

                            <?php if (!empty($data['type'])): ?>
                                <tr>
                                    <td>
                                        <span class="key"><?= lang("Type", "Typ"); ?></span>
                                        <?= htmlspecialchars($data['type']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($data['access'])): ?>
                                <tr>
                                    <td>
                                        <span class="key"><?= lang("Access", "Zugang"); ?></span>
                                        <?= htmlspecialchars($data['access']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                    </tbody>
                </table>


                <nav class="on-this-page-nav">
                    <div class="content">
                        <div class="title"><?= lang('On this page', 'Auf dieser Seite') ?></div>
                        <a href="#about"><?= lang('About '.$name, 'Über '.$name) ?>
                        </a>
                        <?php if (count($persons) > 0): ?>
                            <a href="#staff"> <?= lang('Staff', 'Mitarbeitende') ?></a>
                        <?php endif; ?>
                        <?php if (($data['n_activities'] ?? 0) > 0): ?>
                            <a href="#activities"> <?= lang('Activities', 'Aktivitäten') ?></a>
                        <?php endif; ?>
                        <?php if ($data['collaborative'] ?? false): ?>
                            <a href="#collaborative"> <?= lang('Collaborative Infrastructure', 'Kollaborative Infrastruktur') ?></a>
                        <?php endif; ?>
                        </ul>
                    </div>
                </nav>


            </div>


        <?php endif; ?>
    <?php endif; ?>
        </div>
</div>