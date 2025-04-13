<h1>
    <i class="ph ph-scales" aria-hidden="true"></i>
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
            <?=$Project->getStatus()?>
            </span>
           <h5 class="m-0">
                <a href="<?= ROOTPATH ?>/projects/view/<?= $project['_id'] ?>" class="link">
                    <?= $project['name'] ?>
                </a>
            </h5>
            <small class="d-block text-muted mb-5"><?= $project['title'] ?></small>
            <?php if ($project['contact']) { 
                $contact = $DB->getPerson($project['contact']);
                echo "<p class='mb-0'><strong>" . lang('Contact', 'Kontakt') . ":</strong> " . $contact['name'] . "</p>";
             } ?>
            

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