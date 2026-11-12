<?php
$required = $req || !empty($props['required']);
$form = $this->form;
$proposed = $module === 'project-date-proposed';
$startKey = $proposed ? 'start_proposed' : 'start';
$endKey = $proposed ? 'end_proposed' : 'end';
$shortcut = $proposed ? 'timeframeProposed' : 'timeframe';
$customLabel = (!empty($props['label']) || !empty($props['label_de'])) && $label !== translate($m['label']);
$reqField = fn($field) => $required ? 'required' : '';
?>
<div class="data-module col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
<?php if ($customLabel): ?><label class="floating-title <?= $labelClass ?>"><?= e($label) ?></label><?php endif; ?>
<?php
$start = valueFromDateArray($form[$startKey] ?? $form[$startKey . '_date'] ?? null);
$end = valueFromDateArray($form[$endKey] ?? $form[$endKey . '_date'] ?? null);
?>
                    <div class="row row-eq-spacing my-0 align-items-end ">
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[<?= e($startKey) ?>]" value="<?= $start ?>" id="<?= e($startKey) ?>" required placeholder=" ">

                            <label for="<?= e($startKey) ?>" class="required">
                                <?= lang($proposed ? 'projects.proposed_start_date' : 'projects.project_start') ?>
                            </label>
                        </div>
                        <div class="col-sm-4">
                            <span class="floating-title">
                                <?= lang('projects.shortcut_length') ?>
                            </span>
                            <div class="btn-group w-full">
                                <div class="btn small" onclick="<?= $shortcut ?>(36)"><?= lang('projects.3_yr') ?></div>
                                <div class="btn small" onclick="<?= $shortcut ?>(12)"><?= lang('projects.1_yr') ?></div>
                                <div class="btn small" onclick="<?= $shortcut ?>(6)"><?= lang('projects.6_mo') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[<?= e($endKey) ?>]" value="<?= $end ?>" id="<?= e($endKey) ?>" required placeholder=" ">

                            <label for="<?= e($endKey) ?>" class="required">
                                <?= lang($proposed ? 'projects.proposed_end_date' : 'projects.project_end') ?>
                            </label>
                        </div>
                    </div>

                    <script>
                        function <?= $shortcut ?>(month) {
                            let startField = document.querySelector('#<?= e($startKey) ?>');
                            let start = startField.valueAsDate;
                            if (start == '' || start === null) {
                                toastError(<?= json_encode(lang('projects.please_select_a_start_date_first'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                                return;
                            }

                            let end = new Date(start.setMonth(start.getMonth() + month));
                            end.setDate(end.getDate() - 1);
                            let endField = document.querySelector('#<?= e($endKey) ?>');
                            endField.valueAsDate = end;
                        }
                    </script>

<?= $this->render_help($help) ?>
</div>
