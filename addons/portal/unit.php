<?php

/**
 * Page to see details on a single unit
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /unit/<id>
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$ch = curl_init($total_path . '/numbers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$numbers = json_decode($response, true);
$numbers = $numbers['data'] ?? [];

?>
<div class="container">
    <style>
        .filter {
            overflow-y: auto;
            padding: 1rem 2rem;
            max-height: 100%;
        }

        .filter tr td {
            border-left: 3px solid transparent;
            border-bottom: none;
        }

        .filter tr td:hover {
            border-left-color: var(--primary-color);
        }

        .filter tr td.active {
            background: var(--primary-color-20);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }


        #research p,
        #general p {
            text-align: justify;
        }

        @media (min-width: 768px) {

            #research figure,
            #general .head {
                max-width: 100%;
                float: right;
                margin: 0 0 1rem 2rem;
            }
        }

        #research figure figcaption {
            font-size: 1.2rem;
            color: var(--muted-color);
            font-style: italic;
        }
        .description img {
            max-width: 100%;
            height: auto;
        }
    </style>


    <!-- all necessary javascript -->
    <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/popover.js"></script>

    <script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>


    <!-- <script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= CSS_JS_VERSION ?>"></script> -->
    <!-- <script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script> -->

    <!-- all variables for this page -->

    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css">
    <script>
        const PORTALPATH = '<?= PORTALPATH ?>';
        const DEPT = '<?=$id?>';
    </script>
    <script src="<?= ROOTPATH ?>/js/units.portfolio.js?v=<?= CSS_JS_VERSION ?>"></script>


    <style>
        .unit-name {
            margin: 0;
        }

        .unit-type {
            margin: 0 0 1rem 0;
            font-size: 1.4rem;
            color: var(--primary-color);
        }
    </style>

    <h2 class="unit-name"><?= lang($data['name'], $data['name_de'] ?? null) ?></h2>
    <h4 class="unit-type"><?= lang($data['unit']['name'] ?? '', $data['unit']['name_de'] ?? null) ?></h4>

    <!-- TAB AREA -->
    <style>
        .pills.small .btn,
        .pills.small .badge {
            font-size: 1.2rem;
        }

        .pills.small .index {
            font-size: 1rem;
        }
    </style>

    <nav class="pills small mb-10">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('Info', 'Info') ?>
        </a>

        <?php if (!empty($data['research'] ?? null)) { ?>
            <a onclick="navigate('research')" id="btn-research" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Research', 'Forschung') ?>
            </a>
        <?php } ?>

        <a onclick="navigate('persons')" id="btn-persons" class="btn">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Team', 'Team') ?>
            <span class="index"><?= $numbers['persons'] ?></span>
        </a>

        <?php
        if ($numbers['publications'] > 0) { ?>
            <a onclick="navigate('publications')" id="btn-publications" class="btn">
                <i class="ph ph-books" aria-hidden="true"></i>
                <?= lang('Publications', 'Publikationen')  ?>
                <span class="index"><?= $numbers['publications'] ?></span>
            </a>
        <?php } ?>

        <?php
        if ($numbers['activities'] > 0) { ?>
            <a onclick="navigate('activities')" id="btn-activities" class="btn">
                <i class="ph ph-briefcase" aria-hidden="true"></i>
                <?= lang('Activities', 'Aktivitäten')  ?>
                <span class="index"><?= $numbers['activities'] ?></span>
            </a>
        <?php } ?>

            <?php
            if ($numbers['projects'] > 0) { ?>
                <a onclick="navigate('projects')" id="btn-projects" class="btn">
                    <i class="ph ph-tree-structure" aria-hidden="true"></i>
                    <?= lang('Projects', 'Projekte')  ?>
                    <span class="index"><?= $numbers['projects'] ?></span>
                </a>
            <?php } ?>
    </nav>


    <section id="general">
        <!-- head -->
        <?php
        $head = $data['heads'] ?? [];
        if (is_string($head)) $head = [$head];
        else $head = DB::doc2Arr($head);
        if (!empty($head)) { ?>
            <div class="head">
                <h5 class="mt-0"><?= lang($data['unit']['head'] ?? '', $data['unit']['head_de'] ?? null) ?></h5>
                <div>
                    <?php foreach ($head as $h) { ?>
                        <a href="<?= ROOTPATH ?>/profile/<?= $h['id'] ?>" class="colorless d-flex align-items-center border bg-white p-10 rounded mt-10">
                            <?= $h['img'] ?>
                            <div class="ml-20">
                                <h5 class="my-0">
                                    <?= $h['name'] ?>
                                </h5>
                                <small>
                                    <?=lang($h['position'], $h['position_de'] ?? null)?>
                                </small>
                            </div>
                        </a>
                    <?php } ?>
                </div>

            </div>
        <?php } ?>



        <?php if (isset($data['description']) || isset($data['description_de'])) { ?>

            <h5>
                <?= lang('About', 'Information') ?>
            </h5>
            <div class="description">
                <?= lang($data['description'] ?? '-', $data['description_de'] ?? null) ?>
            </div>
        <?php } ?>





    </section>

    <section id="research" style="display:none;">

        <h3><?= lang('Research interests', 'Forschungsinteressen') ?></h3>

        <?php if (isset($data['research']) && !empty($data['research'])) { ?>
            <?php foreach ($data['research'] as $r) { ?>
                <div class="box">
                    <h5 class="header">
                        <?= lang($r['title'], $r['title_de'] ?? null) ?>
                    </h5>
                    <div class="content description">
                        <?= (lang($r['info'], $r['info_de'] ?? null)) ?>
                    </div>
                </div>

            <?php } ?>
        <?php } ?>

    </section>


    <section id="persons" style="display: none;">

        <!-- <h3><?= lang('Employees', 'Mitarbeitende Personen') ?></h3> -->

        <table class="table cards w-full" id="user-table">
            <thead>
                <th></th>
                <th></th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </section>


    <section id="publications" style="display:none">

        <!-- <h2><?= lang('Publications', 'Publikationen') ?></h2> -->

        <div class="w-full">
            <table class="table dataTable responsive" id="publication-table">
                <thead>
                    <tr>
                        <th><?= lang('Type', 'Typ') ?></th>
                        <th><?= lang('Activity', 'Aktivität') ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>


    <section id="activities" style="display:none">


        <!-- <h2><?= lang('Other activities', 'Andere Aktivitäten') ?></h2> -->

        <div class="w-full">
            <table class="table dataTable responsive" id="activities-table">
                <thead>
                    <tr>
                        <th><?= lang('Type', 'Typ') ?></th>
                        <th><?= lang('Activity', 'Aktivität') ?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>

            </table>
        </div>


    </section>


        <section id="projects" style="display:none">


            <?php if ($numbers['projects'] > 0) { ?>
                <!-- collaborators -->
                <h1>
                    <?= lang('Projects', 'Projekte') ?>
                </h1>

                <div class="w-full">
                    <table class="table dataTable responsive" id="projects-table">
                        <thead >
                            <tr>
                                <th><?= lang('Project', 'Projekt') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>



                <div id="collaborators">
                    <div class="box mt-0 ">
                        <div id="map" class="h-300"></div>
                    </div>
                </div>


                <style>
               #projects-table {
  border: none;
  background: transparent;
  box-shadow: none;
  display: block;
}

#projects-table thead {
  display: none;
}

#projects-table tbody {
  display: flex;
  flex-grow: column;
  flex-direction: row;
  flex-wrap: wrap;
}

#projects-table tbody tr {
  width: 100%;
  margin: 0.5rem;
  border: var(--border-width) solid var(--border-color);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  background: var(--box-bg-color);
  display: flex;
  align-items: center;
}

#projects-table tbody tr td {
  border: 0;
  box-shadow: none;
  width: 100%;
  height: 100%;
  display: block;
}

#projects-table tbody tr small, #projects-table tbody tr p {
  display: block;
  margin: 0;
}

#projects-table tbody tr td {
  display: flex;
  /* align-items: center; */
  border: 0;
}

@media (min-width: 768px) {
  #projects-table tbody tr {
    width: 48%;
  }
}
/* 
@media (min-width: 1200px) {
  .table.cards tbody tr {
    width: calc(33.3% - 1rem);
  }
} */

 span.link {
  color: var(--primary-color);
  cursor: pointer;
  display: inline-block;
}
span.link::after {
  content: " »";
}


                </style>

            <?php } ?>


        </section>


</div>