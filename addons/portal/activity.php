<?php if ($Portfolio->isPreview()) { ?>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/portal.css?v=<?= OSIRIS_BUILD ?>">
<?php } ?>

<div class="container-lg">
    <?php if ($data): ?>
        <h1 class="title"><?= $data['title']; ?></h1>

        <div class="row row-eq-spacing my-0">
            <div class="col-md-8">
                <?php if (!empty($data['authors'])): ?>
                    <ul class="authors">
                        <?php foreach ($data['authors'] as $i => $author): ?>
                            <li style="<?= $i > 10 ? 'display:none;' : '' ?>">
                                <?php if (!empty($author['id'])): ?>
                                    <a href="<?= $base ?>/person/<?= $author['id'] ?>">
                                        <?= $author['name']; ?>
                                    </a>
                                <?php else: ?>
                                    <?= $author['name'] ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($data['authors']) > 10): ?>
                        <a href="#" onclick="$(this).prev().find('li').show(); $(this).remove();">
                            <?= lang("Show all " . count($data['authors']) . " authors", "Alle " . count($data['authors']) . " Autoren anzeigen"); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($data['depts'])): ?>
                    <h3 class="title"><?= lang("Departments", "Abteilungen") ?></h3>
                    <p>
                        <?php foreach ($data['depts'] as $deptId => $d): ?>
                            <a href="<?= $base ?>/group/<?= $deptId; ?>" class="badge primary mr-5 mb-5">
                                <?= lang($d['en'], $d['de'] ?? null); ?>
                            </a>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($data['abstract'])): ?>
                    <h3 class="title"><?= lang("Abstract"); ?></h3>
                    <p><?= $data['abstract']; ?></p>
                <?php endif; ?>

                <?php if (!empty($data['connected_activities'])) { ?>
                    <h3 class="title"><?= lang("Related Activities", "Verknüpfte Aktivitäten"); ?></h3>
                    <table class="table">
                        <tbody>
                            <?php foreach ($data['connected_activities'] as $conn) { ?>
                                <tr>
                                    <td>
                                       <div class="font-size-16 mb-10">
                                        <b><?=lang('This', 'Dies')?> <?= lang($conn['relationship']['en'], $conn['relationship']['de'] ?? null); ?></b><br />
                                       </div>
                                        <div class="d-flex align-items-center">
                                            <div class="w-50">
                                         <!-- <i class="ph ph-arrow-elbow-down-right align-baseline"></i> -->
                                                <?= $conn['icon']; ?>
                                            </div>
                                            <div class="w-full">
                                                <?= $conn['html']; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>


                <ul class="horizontal font-size-16">
                    <?php if (!empty($data['doi'])): ?>
                        <li>
                            <a href="https://doi.org/<?= $data['doi']; ?>" target="_blank">
                                <?= lang("DOI"); ?>: <?= $data['doi']; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($data['pubmed'])): ?>
                        <li>
                            <a href="https://pubmed.ncbi.nlm.nih.gov/<?= $data['pubmed']; ?>" target="_blank">
                                <?= lang("PubMed"); ?>: <?= $data['pubmed']; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>



                <?php if (!empty($data['infrastructures'])): ?>
                    <h3 class="title">
                        <?= lang("Associated Infrastructures", "Assoziierte Infrastrukturen"); ?>
                    </h3>
                    <div class="cards">
                        <?php foreach ($data['infrastructures'] as $infrastructure): ?>
                            <div class="card">
                                <div>
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/infrastructure/<?= $infrastructure['id']; ?>"> <?= $infrastructure['name']; ?> </a>
                                    </h5>
                                    <small class="text-muted"><?= $infrastructure['subtitle'] ?? '' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['projects'])): ?>
                    <h3 class="title"><?= lang("Associated Projects", "Assoziierte Projekte"); ?></h3>
                    <div class="cards">
                        <?php foreach ($data['projects'] as $project): ?>
                            <div class="card">
                                <div>
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/project/<?= $project['id']; ?>"> <?= $project['name']; ?> </a>
                                    </h5>
                                    <small class="text-muted"><?= $project['title'] ?? '' ?></small>
                                    <hr />
                                    <b> <?= $project['funding_organization'] ?? $project['funder'] ?? $project['scholarship'] ?? "" ?> </b> &nbsp;
                                    <p><?= fromToDate($project['start'], $project['end']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


                <h3><?= lang("Cite this activity", "Zitiere diese Aktivität"); ?></h3>
                <nav class="pills">
                    <a class="btn active" onclick="nav('citation')">Citation</a>
                    <?php if (!empty($data['bibtex'])): ?>
                        <a class="btn" onclick="nav('bibtex')">BibTeX</a>
                    <?php endif; ?>
                    <?php if (!empty($data['ris'])): ?>
                        <a class="btn" onclick="nav('ris')">RIS</a>
                    <?php endif; ?>
                </nav>

                <div id="tabs">
                    <div class="box padded" id="citation-box">
                        <span><?= $data['print'] ?></span>
                    </div>
                    <div class="box padded" id="bibtex-box" style="display: none;">
                        <pre><?= $data['bibtex'] ?? '' ?></pre>
                    </div>
                    <div class="box padded" id="ris-box" style="display: none;">
                        <pre><?= $data['ris'] ?? '' ?></pre>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <h3 class="title"><?= lang("Details"); ?></h3>
                <table class="table" id="detail-table">
                    <tbody>
                        <!-- topics -->
                        <?php if (!empty($data['topics'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= $Settings->topicLabel() ?></span>
                                    <div class="topics">
                                        <?php foreach ($data['topics'] as $t) { ?>
                                            <a href="<?= $base ?>/topic/<?= $t['id'] ?>" class="topic-badge" style="--primary-color: <?= $t['color'] ?? 'var(--primary-color)' ?>; --primary-color-20: <?= isset($t['color']) ? $t['color'] . '33' : 'var(--primary-color-20)' ?>">
                                                <i class="ph ph-arrow-circle-right"></i>
                                                <?= lang($t['name'], $t['name_de'] ?? null) ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php foreach ($data['fields'] as $field):
                            if (empty($field['value'])) continue; ?>
                            <tr>
                                <td>
                                    <b class="key"><?= lang($field['key_en'], $field['key_de']); ?></b>
                                    <span><?= $field['value']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <p id="disclaimer" class="text-muted">
            <?= lang(
                "The content on this page is maintained by the authors.",
                "Die Inhalte auf dieser Seite werden von den Autor:innen selbst gepflegt."
            ); ?>
        </p>
        <script>
            function nav(id) {
                document.querySelectorAll('.pills .btn').forEach(btn => btn.classList.remove('active'));
                document.getElementById(id + '-box').style.display = 'block';
                document.querySelector('.pills .btn[onclick="nav(\'' + id + '\')"]').classList.add('active');
                ['citation', 'bibtex', 'ris'].forEach(box => {
                    if (box !== id) {
                        document.getElementById(box + '-box').style.display = 'none';
                    }
                });
            }
        </script>
    <?php endif; ?>
</div>