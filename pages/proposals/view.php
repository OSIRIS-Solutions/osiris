<?php
$status = $project['status'] ?? 'proposed';
?>


<div class="row row-eq-spacing">
    <div class="col-md-8">

        <h1>
            <?= lang('Project proposal', 'Projektantrag') ?>
            <q><?= $project['name'] ?></q>
        </h1>

    </div>

    <div class="col-md-4">
        <?php if ($status == 'proposed') { ?>
            <div class="dropdown">
                <button class="btn large signal" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                    <?= lang('Proposed', 'Beantragt') ?>
                    <i class="ph ph-edit mr-0 ml-10" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-1">
                    <a href="<?= ROOTPATH ?>/proposals/edit/<?=$id?>?phase=approved" class="item font-size-18 badge success mb-5"><?= lang('Approved', 'Angenommen') ?></a>
                    <a href="<?= ROOTPATH ?>/proposals/edit/<?=$id?>?phase=rejected" class="item font-size-18 badge danger"><?= lang('Rejected', 'Abgelehnt') ?></a>
                </div>
            </div>
        <?php } else if ($status == 'accepted') { ?>
            <span class="badge success border-success font-size-18">
                <?= lang('Accepted', 'Angenommen') ?>
                <i class="ph ph-check-circle" aria-hidden="true"></i>
            </span>
            <br>

            <a href="<?= ROOTPATH ?>/projects/<?= $project['_id'] ?>" class="btn mt-10">
                <?= lang('View project', 'Projekt anzeigen') ?>
            </a>
        <?php } else { ?>
            <span class="badge danger border-danger font-size-18">
                <?= lang('Rejected', 'Abgelehnt') ?>
                <i class="ph ph-x-circle" aria-hidden="true"></i>
            </span>
        <?php } ?>

    </div>

</div>

<?php
dump($project);
?>