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
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/portal.css?v=<?= CSS_JS_VERSION ?>">
<?php } ?>

<section class="container-lg">
    <?php if (!$data): ?>
        <p><?= lang("Person not found", "Person nicht gefunden") ?></p>
    <?php else: ?>

        <div class="profile-header" style="display: flex; align-items: center">
            <?php if (empty($data['inactive'])): ?>
                <div class="col mr-20" style="flex-grow: 0">
                    <?php
                    echo $data['img'];
                    ?>
                </div>
            <?php endif; ?>
            <div class="col" id="person">
                <div class="academic-title"><?= $data['academic_title'] ?? "" ?></div>
                <h1 class="m-0 person-name">
                    <?= $data['first'] ?? "" ?> <?= $data['last'] ?>
                </h1>
                <p class="my-0 lead text-secondary position">
                    <?php if (!empty($data['inactive'])): ?>
                        <?= lang("Former Employee", "Ehemalige Beschäftigte") ?>
                    <?php else: ?>
                        <?= lang($data['position'], $data['position_de'] ?? null) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="row row-eq-spacing mt-0">
            <div class="col-sm-8 order-sm-first order-last" id="research">
                <?php if (!empty($data['research'])): ?>
                    <h2 class="title" id="research">
                        <?= lang("Research interest", "Forschungsinteressen") ?>
                    </h2>
                    <ul class="list">
                        <?php foreach ($data['research'] as $item): ?>
                            <li><?= lang($item['en'] ?? $item, $item['de'] ?? null) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($data['cv'])): ?>
                    <h2 class="title" id="cv">
                        <?= lang("Curriculum Vitae") ?>
                    </h2>
                    <div class="biography">
                        <?php foreach ($data['cv'] as $entry): if (!empty($entry['hide'])) continue ?>
                            <div class="cv">
                                <span class="time"><?= $entry['time'] ?></span>
                                <h5 class="title"><?= $entry['position'] ?></h5>
                                <span class="affiliation"><?= $entry['affiliation'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['highlighted'])): ?>
                    <h2><?= lang("Highlighted research", "Hervorgehobene Forschung") ?></h2>
                    <table class="table">
                        <?php foreach ($data['highlighted'] as $h): ?>
                            <tr>
                                <td class="w-50"><?= $h['icon'] ?></td>
                                <td><?= $Portfolio->replaceLink($h['html']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>


                <?php if (!empty($data['numbers']['publications'])): ?>
                    <div class="pb-10">
                        <h2 id="publications"><?= lang("Publications", "Publikationen") ?></h2>

                        <table class="table datatable"
                            id="publication-table"
                            data-table="publications"
                            data-source="./publications.json"
                            data-lang="<?= lang('en', 'de') ?>">
                            <thead>
                                <tr>
                                    <th data-col="icon" data-orderable="false" data-searchable="false">Type</th>
                                    <th data-col="html" data-search-col="search">Title</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <?php if ($Portfolio->isPreview()): ?>
                            <script>
                                $(document).ready(function() {
                                    $('#publication-table').DataTable({
                                        "ajax": {
                                            "url": ROOTPATH + '/portfolio/person/<?= $id ?>/publications',
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
                                                    data = data.replace(/href='\//g, "href='<?= $base ?>/");
                                                    return data;
                                                }
                                            },
                                        ],
                                    });
                                });
                            </script>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <?php if (!empty($data['numbers']['activities'])): ?>
                    <div class="pb-10">
                        <h2 id="activities"><?= lang("Other Activities", "Weitere Aktivitäten") ?></h2>

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
                                        "url": ROOTPATH + '/portfolio/person/<?= $id ?>/activities',
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
                <?php endif; ?>

                <?php if (!empty($data['numbers']['teaching'])): ?>
                    <div class="pb-10">
                        <h2 id="teaching"><?= lang("Teaching activity", "Lehrbeteiligung") ?></h2>
                        <table class="table" id="teaching-table" data-lang="<?= lang('en', 'de') ?>" data-source="./teaching.json" data-table="teaching">
                            <thead>
                                <tr>
                                    <th data-><?= lang('Title', 'Titel') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $teaching = $Portfolio->fetch_entity('person', $id, 'teaching', $lang);
                                foreach ($teaching['data'] as $t):
                                    $title = str_replace("href='/", "href='" . $base . "/",  $t['title']);
                                ?>
                                    <tr>
                                        <td>
                                            <h5 class="mt-0">
                                                <?= $title ?>
                                            </h5>

                                            <em><?= $t['affiliation'] ?></em>
                                        </td>
                                    </tr>
                                <?php endforeach;
                                ?>

                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>


                <?php if (!empty($data['numbers']['infrastructures'])):
                    $infrastructures = $Portfolio->fetch_entity('person', $id, 'infrastructures');
                ?>
                    <div id="infrastructures">
                        <h2 class="title">
                            <?= lang('Involved in', 'Beteiligt an') ?>
                            <?= $Settings->infrastructureLabel() ?>
                        </h2>
                        <div class="cards">
                            <?php foreach ($infrastructures as $i) { ?>
                                <div class="card">
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/infrastructure/<?= $i['id'] ?>"> <?= $i['name'] ?> </a>
                                    </h5>
                                    <small class="text-muted" v-html="i.title ?? ''"></small>
                                    <hr />
                                    <div>
                                        <b> <?= $i['role'] ?> </b> &nbsp;
                                    </div>
                                    <p><?= fromToDate($i['start'], $i['end'], true) ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php endif; ?>


                <div id="projects">
                    <?php if (!empty($data['projects']['current'])): ?>
                        <h2><?= lang("Current Projects", "Aktuelle Projekte") ?></h2>
                        <div class="cards">
                            <?php foreach ($data['projects']['current'] as $project): ?>
                                <div class="card">
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/project/<?= $project['id'] ?>"> <?= $project['name'] ?> </a>
                                    </h5>
                                    <small class="text-muted" v-html="project.title ?? ''"></small>
                                    <hr />
                                    <?php if ($project['personRole']) { ?>
                                        <div>
                                            <b> <?= lang($project['personRole']['en'], $project['personRole']['de'] ?? '') ?> </b> &nbsp;
                                        </div>
                                    <?php } else { ?>
                                        <b> <?= $project['funding_organization'] ?? $project['funder'] ?? $project['scholarship'] ?? "" ?> </b> &nbsp;
                                    <?php } ?>
                                    <p><?= fromToDate($project['start'], $project['end']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($data['projects']['past'])): ?>
                        <h2><?= lang("Past Projects", "Abgeschlossene Projekte") ?></h2>
                        <div class="cards">
                            <?php foreach ($data['projects']['past'] as $project): ?>
                                <div class="card">
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/project/<?= $project['id'] ?>"> <?= $project['name'] ?> </a>
                                    </h5>
                                    <small class="text-muted" v-html="project.title ?? ''"></small>
                                    <hr />
                                    <?php if ($project['personRole']) { ?>
                                        <div>
                                            <b> <?= lang($project['personRole']['en'], $project['personRole']['de'] ?? '') ?> </b> &nbsp;
                                        </div>
                                    <?php } else { ?>
                                        <b> <?= $project['funding_organization'] ?? $project['funder'] ?? $project['scholarship'] ?? "" ?> </b> &nbsp;
                                    <?php } ?>
                                    <p><?= fromToDate($project['start'], $project['end']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-sm-4 position-relative">
                <?php if ((!empty($data['depts']) || !empty($data['topics'])) && empty($data['inactive'])): ?>
                    <h2><?= lang("Affiliation", "Zugehörigkeit") ?></h2>
                    <table class="table small unit-table w-full">
                        <tbody>
                            <!-- topics -->
                            <?php if (!empty($data['topics'])) { ?>
                                <tr>
                                    <td>
                                        <div class="topics">
                                            <?php foreach ($data['topics'] as $t) { ?>
                                                <a href="<?= $base ?>/topic/<?= $t['id'] ?>" class="topic-badge simple" style="--primary-color: <?= $t['color'] ?? 'var(--primary-color)' ?>; --primary-color-20: <?= isset($t['color']) ? $t['color'] . '33' : 'var(--primary-color-20)' ?>">
                                                    <i class="ph ph-arrow-circle-right"></i>
                                                    <?= lang($t['name'], $t['name_de'] ?? null) ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                        <span class="key"><?= $Settings->topicLabel() ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php foreach (($data['depts'] ?? []) as $d): ?>
                                <tr>
                                    <td class="indent-<?= $d['indent'] ?>">
                                        <a href="<?= $base ?>/group/<?= $d['id'] ?>" class="d-block">
                                            <b><?= lang($d['name_en'], $d['name_de'] ?? null) ?></b><br />
                                            <small class="text-muted"><?= lang($d['unit_en'], $d['unit_de']) ?></small>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>


                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if (!empty($data['contact']) && (empty($data['inactive']))): ?>
                    <div id="contact">
                        <h2 class="title"><?= lang("Contact", "Kontakt") ?></h2>
                        <table class="table small">
                            <tbody>
                                <?php if (!empty($data['contact']['mail']) || !empty($data['contact']['mail_alternative'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">Email</span>
                                            <?php if (!empty($data['contact']['mail'])): ?>
                                                <span id="mail">
                                                    <a class="hidden"><?= htmlspecialchars($data['contact']['mail']) ?></a>
                                                    <button class="btn small" onclick="document.getElementById('mail').querySelector('a').classList.remove('hidden'); this.style.display='none';">
                                                        <?= lang("Show mail", "Zeige Mail") ?>
                                                    </button>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($data['contact']['mail_alternative'])): ?>
                                                <?php if (!empty($data['contact']['mail_alternative_comment'])): ?>
                                                    <p class="mb-0 font-weight-bold">
                                                        <?= htmlspecialchars($data['contact']['mail_alternative_comment']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <span id="mail-alt">
                                                    <a class="hidden"><?= htmlspecialchars($data['contact']['mail_alternative']) ?></a>
                                                    <button class="btn small" onclick="document.getElementById('mail-alt').querySelector('a').classList.remove('hidden'); this.style.display='none';">
                                                        <?= lang("Show mail", "Zeige Mail") ?>
                                                    </button>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['phone'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key"><?= lang("Telephone", "Telefon") ?></span>
                                            <?= htmlspecialchars($data['contact']['phone']) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['orcid'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">ORCID</span>
                                            <a href="http://orcid.org/<?= htmlspecialchars($data['contact']['orcid']) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($data['contact']['orcid']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['researchgate'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">ResearchGate</span>
                                            <a href="https://www.researchgate.net/profile/<?= htmlspecialchars($data['contact']['researchgate']) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($data['contact']['researchgate']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['google_scholar'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">Google Scholar</span>
                                            <a href="https://scholar.google.com/citations?user=<?= htmlspecialchars($data['contact']['google_scholar']) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($data['contact']['google_scholar']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['linkedin'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">LinkedIn</span>
                                            <a href="https://linkedin.com/in/<?= htmlspecialchars($data['contact']['linkedin']) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($data['contact']['linkedin']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['twitter'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">Twitter</span>
                                            <a href="https://twitter.com/<?= htmlspecialchars($data['contact']['twitter']) ?>" target="_blank" rel="noopener noreferrer">
                                                @<?= htmlspecialchars($data['contact']['twitter']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($data['contact']['webpage'])): ?>
                                    <tr>
                                        <td>
                                            <span class="key">Personal web page</span>
                                            <?php
                                            $webpage = preg_replace('/^https?:\/\//', '', $data['contact']['webpage']);
                                            ?>
                                            <a href="https://<?= htmlspecialchars($webpage) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($webpage) ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>


                <?php
                $numbers = array_sum(array_values($data['numbers'] ?? []));
                if (!empty($data['research']) || !empty($data['cv']) || !empty($numbers)) { ?>
                    <nav class="on-this-page-nav">
                        <div class="content">
                            <div class="title"><?= lang('On this page', 'Auf dieser Seite') ?></div>
                            <?php if (!empty($data['research'])): ?>
                                <a href="#research"><?= lang("Research interest", "Forschungsinteressen") ?></a>
                            <?php endif; ?>
                            <?php if (!empty($data['cv'])): ?>
                                <a href="#cv"><?= lang("Curriculum Vitae") ?></a>
                            <?php endif; ?>
                            <?php if (!empty($data['numbers']['publications'])): ?>
                                <a href="#publications"><?= lang("Publications", "Publikationen") ?></a>
                            <?php endif; ?>
                            <?php if (!empty($data['numbers']['activities'])): ?>
                                <a href="#activities"><?= lang("Other Activities", "Weitere Aktivitäten") ?></a>
                            <?php endif; ?>
                            <?php if (!empty($data['numbers']['teaching'])): ?>
                                <a href="#teaching"><?= lang("Teaching activity", "Lehrbeteiligung") ?></a>
                            <?php endif; ?>
                            <?php if (!empty($data['numbers']['infrastructures'])): ?>
                                <a href="#infrastructures">
                                    <?= $Settings->infrastructureLabel() ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($data['numbers']['projects'])): ?>
                                <a href="#projects"><?= lang("Projects", "Projekte") ?></a>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php } ?>

            </div>
        </div>

        <p id="disclaimer">
            <?= lang(
                "The content on this page is maintained by the individual and is not official information from the institute.",
                "Die Inhalte auf dieser Seite werden von der Person selbst gepflegt und sind keine offiziellen Informationen des Instituts."
            ) ?>
        </p>
    <?php endif; ?>
</section>