<?php
$Format = new Document(false, 'portal');
?>
<style>
    img.profile-img {
        max-width: 8rem;
        margin-right: 2rem;
    }

    /* .module {
        background-color: transparent;
        border: none;
        box-shadow: none;
        padding: 1rem 0;
    } */
    #activities-table thead,
    #publication-table thead,
    #past-project-table thead {
        display: none;
    }
</style>

<div class="container">

    <div class="profile-header" style="display: flex; align-items: center;">
        <div class="col mr-20" style="flex-grow: 0;">
           <?php if (isset($scientist['public_image']) && $scientist['public_image'] === true) { ?>
            <?= $Settings->printProfilePicture($user, 'profile-img rounded') ?>
           <?php } else { ?>
            <img src="<?= ROOTPATH ?>/img/no-photo.png" alt="Profilbild" class="profile-img rounded">
           <?php } ?>
            
        </div>

        <div class="col">
            <h1 class="m-0">
                <?= $scientist['academic_title'] ?? '' ?>
                <?= $scientist['first'] ?? '' ?>
                <?= $scientist['last'] ?>
            </h1>
            <p class="my-0 lead"><?= lang($scientist['position'] ?? '', $scientist['position_de'] ?? null) ?></p>
            <?php
            foreach ($scientist['units'] as $i => $d) {
                $dept = $Groups->getGroup($d['unit']);
                if ($i > 0) echo ', ';
            ?>
                <a href="<?= PORTALPATH ?>/group/<?= $dept['id'] ?>" style="color:<?= $dept['color'] ?? 'inherit' ?>">
                    <?= $dept['name'] ?>
                </a>
            <?php } ?>

        </div>
    </div>

    <div class="row row-eq-spacing my-0">
        <div class="col-md-8" id="research">
            <?php if (isset($scientist['research']) && !empty($scientist['research'])) { ?>
                <div class="pb-10">
                    <h2 class="title"><?= lang('Research interest', 'Forschungsinteressen') ?></h2>

                    <ul class="list">
                        <?php foreach ($scientist['research'] as $key) { ?>
                            <li><?= $key ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <?php if (isset($scientist['cv']) && !empty($scientist['cv'])) {
                $cv = DB::doc2Arr($scientist['cv']);
                $cv = array_filter($cv, function ($entry) {
                    return ($entry['hide'] ?? false) != true;
                });
            ?>
                <div class="pb-10">
                    <h2 class="title"><?= lang('Curriculum Vitae') ?></h2>

                    <div class="biography">
                        <?php foreach ($cv as $entry) { ?>
                            <div class="cv">
                                <span class="time"><?= $entry['time'] ?></span>
                                <h5 class="title"><?= $entry['position'] ?></h5>
                                <span class="affiliation"><?= $entry['affiliation'] ?></span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>






            <?php
            $highlights = DB::doc2Arr($scientist['highlighted'] ?? array());
            if (!empty($highlights)) { ?>
                <div class="pb-10">

                    <h2>Highlighted research</h2>
                    <table class="table">
                        <?php
                        // $highlights = ['632da4672199cd3df8dbc166'];

                        foreach ($highlights as $h) {
                            $doc = $DB->getActivity($h);
                            echo "<tr><td class='w-50'>";
                            echo $doc['rendered']['icon'];
                            echo "</td><td>";
                            // echo $doc['rendered']['web'];
                            $Format->setDocument($doc);
                            echo $Format->formatShort();
                            echo "</td></tr>";
                        } ?>

                    </table>
                </div>
            <?php } ?>



            <?php
            $filter = ['authors.user' => $user, 'type' => 'publication', 'hide' => ['$ne' => true]];
            $options = ['sort' => ['year' => -1, 'month' => -1, 'day' => -1]];
            $N = $osiris->activities->count($filter);

            if ($N > 0) { ?>
                <div class="pb-10">
                    <h2>Publications</h2>
                    <table class="table" id="publication-table">
                        <thead>
                            <tr>
                                <th><?= lang('Type', 'Typ') ?></th>
                                <th><?= lang('Title', 'Titel') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($osiris->activities->find($filter, $options) as $doc) {
                                if (!empty($highlights) && in_array(strval($doc['_id']), $highlights))
                                    continue;
                                echo "<tr><td class='w-50'>";
                                echo $doc['rendered']['icon'];
                                echo "</td><td>";
                                // echo $doc['rendered']['web'];
                                $Format->setDocument($doc);
                                echo $Format->formatShort();
                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($N > 6) { ?>
                    <script>
                        $(document).ready(function() {
                            $('#publication-table').DataTable({
                                "sort": false,

                                "pageLength": 6,
                                "lengthChange": false,
                                "searching": false,
                                // "info": false,
                                "pagingType": "numbers"
                            });
                        });
                    </script>
                <?php } ?>

            <?php } ?>


            <?php
            $filter = ['authors.user' => $user, 'type' => ['$in' => ['poster', 'lecture', 'award', 'software']], 'hide' => ['$ne' => true]];
            $N = $osiris->activities->count($filter);

            if ($N > 0) { ?>
                <div class="pb-10">
                    <h2><?= lang('Research activities', 'Forschungsaktivitäten') ?></h2>
                    <table class="table" id="activities-table">
                        <thead>
                            <tr>
                                <th><?= lang('Type', 'Typ') ?></th>
                                <th><?= lang('Title', 'Titel') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($osiris->activities->find($filter, $options) as $doc) {
                                if (!empty($highlights) && in_array(strval($doc['_id']), $highlights))
                                    continue;
                                echo "<tr><td class='w-50'>";
                                echo $doc['rendered']['icon'];
                                echo "</td><td>";
                                // echo $doc['rendered']['web'];
                                $Format->setDocument($doc);
                                echo $Format->formatShort();
                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($N > 6) { ?>
                    <script>
                        $(document).ready(function() {
                            $('#activities-table').DataTable({
                                "sort": false,

                                "pageLength": 6,
                                "lengthChange": false,
                                "searching": false,
                                // "info": false,
                                "pagingType": "numbers"
                            });
                        });
                    </script>
                <?php } ?>

            <?php } ?>


            <!-- Teaching activities -->
            <?php
            $teaching = $osiris->activities->aggregate([
                ['$match' => ['authors.user' => $user, 'type' => 'teaching', 'module_id' => ['$ne' => null], 'hide' => ['$ne' => true]]],
                [
                    '$group' => [
                        '_id' => '$module_id',
                        'count' => ['$sum' => 1],
                        // 'doc' => ['$push' => '$$ROOT']
                    ]
                ],
                ['$sort' => ['count' => -1]]
            ])->toArray();

            if (count($teaching) > 0) { ?>

                <h2><?= lang('Participation in Teaching', 'Lehrbeteiligung') ?></h2>

                <table class="table">
                    <thead></thead>
                    <tbody>
                        <?php foreach ($teaching as $t) {
                            $module = $osiris->teaching->findOne(['_id' => DB::to_ObjectID($t['_id'])]);
                        ?>
                            <tr>
                                <td id="<?= $t['_id'] ?>">
                                    <h5 class="mt-0">
                                        <span class="highlight-text"><?= $module['module'] ?></span>
                                        <?= $module['title'] ?>
                                    </h5>

                                    <em><?= $module['affiliation'] ?></em>
                                </td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
            <?php } ?>

        </div>


        <div class="col-md-4" id="contact">
            <h2 class="title">Contact</h2>
            <table class="table small">
                <tbody>
                    <?php if (($scientist['public_email'] ?? true && !empty($scientist['mail'])) || !empty($scientist['mail_alternative'] ?? null)) { ?>
                        <tr>
                            <td>
                                <?php if ($scientist['public_email'] ?? true && !empty($scientist['mail'])) { ?>
                                    <span class="key">Email</span>
                                    <a href="mailto:<?= $scientist['mail'] ?>"><?= $scientist['mail'] ?></a>
                                <?php } ?>

                                <?php if (isset($scientist['mail_alternative']) && !empty($scientist['mail_alternative'])) { ?>
                                    <p class="mb-0 font-weight-bold"><?= $scientist['mail_alternative_comment'] ?? '' ?></p>
                                    <a href="mailto:<?= $scientist['mail_alternative'] ?>"><?= $scientist['mail_alternative'] ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($scientist['public_phone'] ?? false) { ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Telephone', 'Telefon') ?></span>
                                <?= $scientist['telephone'] ?? '' ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (!empty($scientist['twitter'] ?? null)) { ?>
                        <tr>
                            <td>
                                <span class="key">Twitter</span>

                                <a href="https://twitter.com/<?= $scientist['twitter'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['twitter'] ?></a>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($scientist['orcid'] ?? null)) { ?>
                        <tr>
                            <td>
                                <span class="key">ORCID</span>

                                <a href="http://orcid.org/<?= $scientist['orcid'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['orcid'] ?></a>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($scientist['researchgate'] ?? null)) { ?>
                        <tr>
                            <td>
                                <span class="key">ResearchGate</span>

                                <a href="https://www.researchgate.net/profile/<?= $scientist['researchgate'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['researchgate'] ?></a>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($scientist['google_scholar'] ?? null)) { ?>
                        <tr>
                            <td>
                                <span class="key">Google Scholar</span>

                                <a href="https://scholar.google.com/citations?user=<?= $scientist['google_scholar'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['google_scholar'] ?></a>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($scientist['webpage'] ?? null)) {
                        $web = preg_replace('/^https?:\/\//', '', $scientist['webpage']);
                    ?>
                        <tr>
                            <td>
                                <span class="key">Personal web page</span>

                                <a href="https://<?= $web ?>" target="_blank" rel="noopener noreferrer"><?= $web ?></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


            <?php
            $project_filter = [
                '$or' => array(
                    ['contact' => $user],
                    ['persons.user' => $user],
                ),
                // "status" => ['$ne' => "rejected"],
                'public' => true
            ];

            $count_projects = $osiris->projects->count($project_filter);
            if ($count_projects > 0) { ?>

                <h2><?= lang('Projects', 'Projekte') ?></h2>
                <?php
                $projects = $osiris->projects->find($project_filter, ['sort' => ["start" => -1, "end" => -1]]);

                $ongoing = [];
                $past = [];

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();
                foreach ($projects as $project) {
                    $Project->setProject($project);
                    if ($Project->inPast()) {
                        $past[] = $Project->widgetPortal($user);
                    } else {
                        $ongoing[] = $Project->widgetPortal($user);
                    }
                }
                ?>
                <?php if (!empty($ongoing)) { ?>

                    <!-- <h3><?= lang('Ongoing projects', 'Laufende Projekte') ?></h3> -->
                    <table class="table">
                        <thead></thead>
                        <tbody>
                            <?php foreach ($ongoing as $html) {
                                echo "<tr><td>$html</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <?php if (!empty($past)) { ?>
                    <h3><?= lang('Past projects', 'Vergangene Projekte') ?></h3>

                    <table class="table" id="past-project-table">
                        <thead>
                            <tr>
                                <th><?= lang('Project', 'Projekt') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($past as $html) {
                                echo "<tr><td>$html</td></tr>";
                            } ?>
                        </tbody>
                    </table>

                    <?php if (count($past) > 3) { ?>

                        <script>
                            $(document).ready(function() {
                                $('#past-project-table').DataTable({
                                    "sort": false,
                                    "pageLength": 3,
                                    "lengthChange": false,
                                    "searching": false,
                                    "info": false,
                                    "pagingType": "numbers"
                                });
                            });
                        </script>
                    <?php } ?>
                <?php } ?>

            <?php } ?>
        </div>
    </div>
</div>

<script>
    // remove research col if empty
    if (document.getElementById('research').innerHTML.trim() == '') {
        document.getElementById('research').remove();
    }

    // remove contact col if empty (text)
    if (document.getElementById('contact').textContent.trim() == 'Contact') {
        document.getElementById('contact').remove();
    }
</script>