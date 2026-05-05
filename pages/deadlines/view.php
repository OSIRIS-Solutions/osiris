<?php
$today = date('Y-m-d') == $deadline['date'];
$in_past = !$today && strtotime($deadline['date']) < time();

$days = false;
if ($today) {
    $days = lang('today', 'heute');
} elseif (!$in_past) {
    $days = ceil((strtotime($deadline['date']) - time()) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? lang('today', 'heute') : 'in ' . $days . ' ' . lang('days', 'Tagen');
}
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
?>


<h1><?= $deadline['title'] ?></h1>

<div class="btn-toolbar">
    <?php if ($deadline['created_by'] == $_SESSION['username'] || $Settings->hasPermission('deadlines.edit')) { ?>
        <a href="<?= ROOTPATH ?>/deadlines/edit/<?= $deadline['_id'] ?>" class="btn text-primary">
            <i class="ph ph-edit"></i>
            <?= lang('Edit deadline', 'Deadline bearbeiten') ?>
        </a>
    <?php } ?>

    <?php if ($deadline['created_by'] == $_SESSION['username'] || $Settings->hasPermission('deadlines.delete')) { ?>
        <div class="dropdown">
            <button class="btn text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <form action="<?= ROOTPATH ?>/crud/deadlines/delete/<?= $deadline['_id'] ?>" method="post" class="content">
                    <?= lang('Do you want to delete this deadline?', 'Möchten Sie diese Deadline löschen?') ?>
                    <?= lang('Please note: this cannot be undone.', 'Achtung: dies kann nicht rückgängig gemacht werden.') ?>
                    <button class="btn danger" type="submit"><?= lang('Delete', 'Löschen') ?></button>
                </form>
            </div>
        </div>
    <?php } ?>
</div>


<div class="row row-eq-spacing">

    <div class="col-md-6 col-lg-4">

        <table class="table">
            <?php if (isset($deadline['type'])) { ?>
                <tr>
                    <td>
                        <span class="key"><?= lang('Type', 'Typ') ?></span>
                        <?= $Vocabulary->getValue('deadline-type', $deadline['type']) ?>
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td>
                    <span class="key"><?= lang('Date', 'Datum') ?></span>
                    <?= format_date($deadline['date']) ?>

                    <?php if (!$in_past) { ?>
                        <b class="badge danger ml-10"><?= $days ?></b>
                    <?php } else { ?>
                        <b class="badge muted ml-10"> <?= lang('already over', 'bereits vorbei') ?></b>
                    <?php } ?>

                </td>
            </tr>
            <tr>
                <td>
                    <span class="key"><?= lang('URL', 'URL') ?></span>
                    <?php if (!empty($deadline['url'])) {
                        $short_url = str_replace('https://', '', $deadline['url']);
                        if (strlen($short_url) > 50) {
                            $short_url = substr($short_url, 0, 50) . '...';
                        }
                    ?>
                        <a href="<?= $deadline['url'] ?>" target="_blank"><i class="ph ph-link"></i> <?= $short_url ?></a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
            <?php if (!$in_past) { ?>
                <tr>
                    <td>
                        <a class="btn small" href="<?= ROOTPATH ?>/deadline/ics/<?= $deadline['_id'] ?>">
                            <i class="ph ph-calendar-plus"></i>
                            <?= lang('Add to calendar', 'Zum Kalender hinzufügen') ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>
    <?php if (isset($deadline['description'])) { ?>

        <div class="col">
            <div id="description" class="box padded m-0" style="max-height: 36rem; overflow-x: auto;">
                <?= $deadline['description'] ?? '' ?>
            </div>
        </div>
    <?php } ?>

</div>



<?php
if (isset($_GET['verbose'])) {
    dump($deadline, true);
}
?>