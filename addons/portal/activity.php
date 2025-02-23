<?php
$Format = new Document;
$Format->setDocument($doc);
$selected = $Format->subtypeArr['modules'] ?? array();
$Modules = new Modules($doc);

$Format->usecase = "portal";
?>

<div class="container">

    <p class="lead">
        <?= $Format->formatPortal($link = false) ?>
    </p>

    <?php
        // List of departments
        if (isset($doc['authors']) && !empty($doc['authors'])) {
            $authors = DB::doc2Arr($doc['authors']);
            // $users = array_column($authors, 'user');
            $units = $doc['units'] ?? [];
            if (!empty($units)) {
                foreach ($units as $i => $dept) {
                    $group = $Groups->getGroup($dept);
                    $name = $group['name'];
                    $units[$i] = "<a style='color:$group[color]' href='" . PORTALPATH . "/group/$dept'>$name</a>";
                }
                echo "<p><b>" . lang('Departments', 'Abteilungen') . ':</b><br>';
                echo implode(', ', $units);
                echo "</p>";
            }
        }

    ?>
    

    <div class="row row-eq-spacing my-0">
        <div class="col-md-8">

            <h2 class="title">
                <?= lang('Details') ?>
            </h2>


            <table class="table" id="detail-table">

                <?php
                $Format->usecase = "list";
                foreach ($selected as $module) {
                    if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
                    if (in_array($module, ['authors', "editors", "semester-select"])) continue;
                ?>
                    <?php if ($module == 'teaching-course' && isset($doc['module_id'])) :
                        $module = $DB->getConnected('teaching', $doc['module_id']);
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Teaching Module', 'Lehrveranstaltung') ?>:</span>

                                <div class="">
                                    <p class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></p>
                                    <span class="text-muted"><?= $module['affiliation'] ?></span>
                                </div>
                            </td>
                        </tr>



                    <?php elseif ($module == 'journal' && isset($doc['journal_id'])) :
                        $journal = $DB->getConnected('journal', $doc['journal_id']);
                    ?>

                        <tr>
                            <td><span class="key"><?= lang('Journal') ?>:</span>
                                <div class="">

                                    <p class="m-0"><?= $journal['journal'] ?></p>
                                    <span class="float-right text-muted"><?= $journal['publisher'] ?></span>
                                    <span class="text-muted">
                                        ISSN: <?= print_list($journal['issn']) ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php elseif (!empty($Format->get_field($module))) : ?>

                        <tr>
                            <td>
                                <span class="key"><?= $Modules->get_name($module) ?>:</span>
                                <?= $Format->get_field($module) ?>
                            </td>
                        </tr>

                    <?php endif; ?>
                <?php } ?>


            </table>


        </div>


        <div class="col-md-4">

            <h2 class="title">
                <?= lang('Associated Projects', 'Assoziierte Projekte') ?>
            </h2>

            <?php if (!empty($doc['projects'] ?? '') && !empty($doc['projects'][0])) {

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();

                foreach ($doc['projects'] as $project_id) {
                    $project = $osiris->projects->findOne(['name' => $project_id]);
                    if (empty($project)) continue;
                    $Project->setProject($project);
            ?>

                    <?= $Project->widgetPortal() ?>
                <?php } ?>

            <?php } else { ?>
                <?= lang('No projects connected.', 'Noch keine Projekte verknüpft.') ?>
            <?php } ?>
        </div>

    </div>
</div>