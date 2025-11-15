<h1>
    <i class="ph-duotone ph-scales" aria-hidden="true"></i>
    <?= lang('Nagoya Protocol', 'Nagoya-Protokoll') ?>
</h1>

<?php
$Project = new Project();
?>

<div id="nagoya" class="row row-eq-spacing">
    <?php foreach ($nagoya as $project) {
        $Project->setProject($project);
    ?>
        <div class="col-md-6">
            <div class="module">
                <span class="float-right">
                    <?= $Project->getStatus() ?>
                </span>
                <h5 class="m-0">
                    <a href="<?= ROOTPATH ?>/proposals/view/<?= $project['_id'] ?>" class="link">
                        <?= $project['name'] ?>
                    </a>
                </h5>
                <small class="d-block text-muted mb-5"><?= $project['title'] ?></small>
                <div>
                    <?php
                    echo $Project->printField('persons', $project['persons']);
                    ?>
                </div>


                <span class="text-muted"><?= $Project->getDateRange() ?></span>

                <h6 class="title"><?= lang('Countries', 'Länder:') ?></h6>
                <ul class="list signal mb-0">
                    <?php
                    $lang = lang('name', 'name_de');
                    foreach ($project['nagoya_countries'] ?? [] as $c) { ?>
                        <li><?= $DB->getCountry($c, $lang) ?></li>
                    <?php } ?>
                </ul>

            </div>
        </div>
    <?php } ?>

</div>