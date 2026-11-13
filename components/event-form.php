<?php
require_once BASEPATH . '/php/EventForms.php';
?>
<div id="data-modules" class="row row-eq-spacing">
    <?php EventForms::render($osiris, $Settings, DB::doc2Arr($form ?? [])); ?>
</div>
<?php if (!isset($form['_id'])): ?>
<input type="hidden" name="attending" value="0">
<div class="custom-checkbox my-20">
    <input type="checkbox" name="attending" id="event-attended" value="1" <?= in_array($_SESSION['username'], DB::doc2Arr($form['participants'] ?? []), true) ? 'checked' : '' ?>>
    <label for="event-attended"><?= lang('events.i_participate') ?></label>
</div>

<?php endif; ?>
