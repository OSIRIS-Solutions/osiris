<?php
$today = date('Y-m-d') == $deadline['date'];
$in_past = !$today && strtotime($deadline['date']) < time();

$days = false;
if ($today) {
    $days = lang('common.today');
} elseif (!$in_past) {
    $days = ceil((strtotime($deadline['date']) - time()) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? lang('common.today') : 'in ' . $days . ' ' . lang('common.days_view');
}
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
require_once BASEPATH . '/php/DeadlineForms.php';
require_once BASEPATH . '/php/Modules.php';
require_once BASEPATH . '/php/Document.php';
$fields = DeadlineForms::visibleFields($osiris, $Settings);
$modules = new Modules(DB::doc2Arr($deadline));
$modules->setFields($fields);
$document = new Document(false, 'list');
?>


<h1><?= e($deadline['title']) ?></h1>

<div class="btn-toolbar">
    <?php if ($deadline['created_by'] == $_SESSION['username'] || $Settings->hasPermission('deadlines.edit')) { ?>
        <a href="<?= ROOTPATH ?>/deadlines/edit/<?= $deadline['_id'] ?>" class="btn text-primary">
            <i class="ph ph-edit"></i>
            <?= lang('deadlines.edit_deadline') ?>
        </a>
    <?php } ?>

    <?php if ($deadline['created_by'] == $_SESSION['username'] || $Settings->hasPermission('deadlines.delete')) { ?>
        <div class="dropdown">
            <button class="btn text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-trash"></i> <?= lang('action.delete') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <form action="<?= ROOTPATH ?>/crud/deadlines/delete/<?= $deadline['_id'] ?>" method="post" class="content">
                    <?= lang('deadlines.do_you_want_to_delete_this_deadline') ?>
                    <?= lang('common.please_note_this_cannot_be_undone') ?>
                    <button class="btn danger" type="submit"><?= lang('action.delete') ?></button>
                </form>
            </div>
        </div>
    <?php } ?>
</div>


<div class="row row-eq-spacing">
    <div class="col">
        <table class="table">
            <?php foreach ($fields as $field):
                $fieldType = $field['type'] ?? 'field';
                $id = $field['id'] ?? '';
                $props = $field['props'] ?? [];
                if ($id === 'deadline-title') continue;
            ?>
                <tr><td colspan="2">
                    <?php if ($fieldType === 'heading'): ?>
                        <strong><?= e(lang($props['text'] ?? '', $props['text_de'] ?? null)) ?></strong>
                    <?php elseif ($fieldType === 'paragraph'): ?>
                        <?= nl2br(e(lang($props['text'] ?? '', $props['text_de'] ?? null))) ?>
                    <?php elseif ($fieldType === 'hr'): ?><hr>
                    <?php else: ?>
                        <span class="key"><?= e($modules->get_name($id)) ?></span>
                        <?= $document->getDeadlineField(DB::doc2Arr($deadline), $id) ?: '-' ?>
                        <?php if ($id === 'deadline-date'): ?>
                            <b class="badge <?= $in_past ? 'muted' : 'danger' ?> ml-10"><?= e($in_past ? lang('deadlines.already_over') : $days) ?></b>
                        <?php endif; ?>
                    <?php endif; ?>
                </td></tr>
            <?php endforeach; ?>
            <?php if (!$in_past): ?>
                <tr><td><a class="btn small" href="<?= ROOTPATH ?>/deadline/ics/<?= e($deadline['_id']) ?>"><i class="ph ph-calendar-plus"></i> <?= translate('common.add_to_calendar') ?></a></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php
if (isset($_GET['verbose'])) {
    dump($deadline, true);
}
?>