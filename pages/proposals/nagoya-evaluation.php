<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = $project['nagoya'] ?? [];
// dump($nagoya);
?>
<h1 class="mb-3"><?= lang('Nagoya Evaluation', 'Nagoya-Bewertung') ?></h1>
<h2 class="subtitle">
    <a href="<?= ROOTPATH ?>/proposals/view/<?= $id ?>">
        <i class="ph ph-arrow-left"></i>
        <?= ($project['name'] ?? '') ?>
    </a>
</h2>

<div class="mb-20">
    <b><?= lang('Current Status', 'Aktueller Status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>
</div>