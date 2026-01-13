<div class="container-lg">
    <?php if ($data): ?>
        <h2 class="title"><?= $data['title']; ?></h2>

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
                    <p><b><?= lang("Departments", "Abteilungen"); ?>:</b><br />
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

                <?php if (!empty($data['infrastructures'])): ?>
                    <h3 class="title">
                        <?= lang("Associated Infrastructures", "Assoziierte Infrastrukturen"); ?>
                    </h3>
                    <?php foreach ($data['infrastructures'] as $infrastructure): ?>
                        <div class="project-card">
                            <div>
                                <h5 class="my-0">
                                    <a href="<?= $base ?>/infrastructure/<?= $infrastructure['id']; ?>"> <?= $infrastructure['name']; ?> </a>
                                </h5>
                                <small class="text-muted"><?= $infrastructure['subtitle'] ?? '' ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($data['projects'])): ?>
                    <h3 class="title"><?= lang("Associated Projects", "Assoziierte Projekte"); ?></h3>
                    <?php foreach ($data['projects'] as $project): ?>
                        <div class="project-card">
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
                <?php endif; ?>
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

<style>
    #tabs pre {
        white-space: pre-wrap;
        margin: 0;
    }

    ul.authors {
        list-style: none;
        padding: 0;
    }

    ul.authors>li {
        display: inline-block;
        margin-right: .5rem;
        margin-bottom: .2rem;
    }

    ul.authors>li::after {
        content: ",";
    }

    ul.authors>li:last-child::after {
        content: "";
    }

    .project-card {
        width: 100%;
        margin: 0.5rem 0;
        border: var(--border-width) solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        background: var(--box-bg-color);
        display: flex;
        align-items: center;
        padding: 1rem 1.4rem;
    }

    .project-card div {
        border: 0;
        box-shadow: none;
        /* width: 100%; */
        height: 100%;
        display: block;
    }

    .project-card small,
    .project-card p {
        display: block;
        margin: 0;
    }
</style>