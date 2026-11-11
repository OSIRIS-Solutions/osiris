<?php
$required = $req || !empty($props['required']);
$startDate = valueFromDateArray($this->form['start'] ?? $this->form['start_date'] ?? null);
$endDate = valueFromDateArray($this->form['end'] ?? $this->form['end_date'] ?? null);
?>
<div class="data-module col-sm-<?= $width ?>" data-module="project-date" id="module-project-date">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <div class="row row-eq-spacing">
            <div class="col-sm-6">
                <label for="project-start"><?= lang('projects.project_start') ?></label>
                <input type="date" class="form-control" name="values[start]" id="project-start" value="<?= e($startDate) ?>" <?= $required ? 'required' : '' ?>>
            </div>
            <div class="col-sm-6">
                <label for="project-end"><?= lang('projects.project_end') ?></label>
                <input type="date" class="form-control" name="values[end]" id="project-end" value="<?= e($endDate) ?>" <?= $required ? 'required' : '' ?>>
            </div>
        </div>
        <div class="btn-group mt-10">
            <?php foreach ([6, 12, 36] as $months): ?>
                <button type="button" class="btn small" data-months="<?= $months ?>"><?= $months ?> <?= lang('Months', 'Monate') ?></button>
            <?php endforeach; ?>
        </div>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<script>
(() => {
    const widget = document.getElementById('module-project-date');
    const start = widget.querySelector('[name="values[start]"]');
    const end = widget.querySelector('[name="values[end]"]');
    const update = () => { end.min = start.value; };
    start.addEventListener('change', update);
    widget.closest('form')?.addEventListener('reset', () => setTimeout(update, 0));
    widget.querySelectorAll('[data-months]').forEach(button => button.addEventListener('click', () => {
        if (!start.valueAsDate) { start.focus(); return; }
        const date = new Date(start.valueAsDate);
        date.setUTCMonth(date.getUTCMonth() + Number(button.dataset.months));
        date.setUTCDate(date.getUTCDate() - 1);
        end.valueAsDate = date;
        end.dispatchEvent(new Event('change', {bubbles: true}));
    }));
    update();
})();
</script>
