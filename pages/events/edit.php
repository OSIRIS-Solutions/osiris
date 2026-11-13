<?php
$editing = isset($form['_id']);
$action = ROOTPATH . ($editing ? '/crud/conferences/update/' . $form['_id'] : '/crud/conferences/add');
include_once BASEPATH . '/header-editor.php';
?>
<div class="container w-600 mw-full">
    <?php if (empty($embedded)): ?>
        <h1><i class="ph-duotone ph-calendar-plus"></i> <?= lang($editing ? 'events.edit_event' : 'action.add_event') ?></h1>
        <blockquote><i class="ph ph-info text-primary"></i> <?= lang('events.here_you_can_create_events_such_as_conferences_workshops_or_other_events_th') ?></blockquote>
    <?php endif; ?>
    <form action="<?= e($action) ?>" method="post" id="conference-form">
        <?php include BASEPATH . '/components/event-form.php'; ?>
        <button class="btn secondary mb-10" type="submit"><?= lang($editing ? 'events.save_event' : 'action.add_event') ?></button>
    </form>
</div>
