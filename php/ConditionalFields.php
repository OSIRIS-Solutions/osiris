<?php

/**
 * Optional Yes/No questions for custom fields.
 * The answer is stored separately; the field keeps its original value format.
 */
class ConditionalFields
{
    public static function answer($field, $form)
    {
        $id = $field['id'];
        $key = $id . '--confirmed';
        if (array_key_exists($key, $form)) {
            $answer = $form[$key];
            return in_array($answer, [true, 'true', 1, '1'], true) ? true
                : (in_array($answer, [false, 'false', 0, '0'], true) ? false : null);
        }
        // Existing values count as Yes, including zero and false.
        $value = $form[$id] ?? null;
        return $value !== null && $value !== '' && $value !== [] ? true : null;
    }

    public static function render($field, $form, $req, $props, $render)
    {
        $id = 'conditional-' . bin2hex(random_bytes(4));
        $module = $field['id'];
        $answer = self::answer($field, $form);
        $required = $req || !empty($props['required']);
        $width = $props['width'] ?? $field['width'] ?? 12;
        if ($width < 1 || $width > 12) $width = 12;
        $question = lang($field['condition_label'] ?? '', $field['condition_label_de'] ?? null);
        if (trim($question) === '') $question = lang($field['name'], $field['name_de'] ?? null);
        ?>
        <div class="col-sm-<?= $width ?> conditional-field" id="<?= $id ?>">
            <fieldset class="m-0 p-0 border-0">
                <legend class="font-size-16 <?= $required ? 'required' : '' ?>" id="<?= $id ?>-question"><?= e($question) ?></legend>
                <input type="hidden" name="values[<?= e($module) ?>--confirmed]" value="">
                <?php foreach (['true' => lang('Yes', 'Ja'), 'false' => lang('No', 'Nein')] as $value => $label) { ?>
                    <div class="custom-radio d-inline-block mr-20">
                        <input type="radio" class="conditional-answer" id="<?= $id ?>-<?= $value ?>"
                            name="values[<?= e($module) ?>--confirmed]" value="<?= $value ?>"
                            <?= $answer !== null && $answer === ($value === 'true') ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>
                            aria-controls="<?= $id ?>-input">
                        <label for="<?= $id ?>-<?= $value ?>"><?= $label ?></label>
                    </div>
                <?php } ?>
            </fieldset>
            <fieldset class="m-0 p-0 border-0 conditional-input" id="<?= $id ?>-input" aria-labelledby="<?= $id ?>-question"
                <?= $answer === true ? '' : 'disabled style="display: none;"' ?>>
                <div class="conditional-details">
                    <div class="row">
                        <?php $render(); ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <script>
            (function() {
                var field = $('#<?= $id ?>');
                var input = field.find('.conditional-input');
                var requiredInputs = input.find('[required]');
                function updateInput() {
                    var active = field.find('.conditional-answer:checked').val() === 'true';
                    // Some form validators inspect the input itself rather than its fieldset.
                    requiredInputs.prop('required', active);
                    input.prop('disabled', !active).toggle(active);
                    input.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                }
                field.find('.conditional-answer').on('change', updateInput);
                updateInput();
                // List widgets and rich text editors do not have a native required input.
                var required = <?= $required ? 'true' : 'false' ?>;
                var list = input.find('.list-widget-input');
                var editor = input.find('.ql-editor');
                function validateInput() {
                    var message = <?= json_encode(lang('Please enter a value.', 'Bitte gib einen Wert ein.'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                    if (list.length) {
                        var filled = input.find('input[type="hidden"][name]').length > 0;
                        list[0].setCustomValidity(required && !input.prop('disabled') && !filled ? message : '');
                    }
                    if (editor.length) {
                        // The textarea is readonly, so validate the editor on submission.
                        var filled = editor.text().trim() !== '' || editor.find('img').length > 0;
                        return !required || input.prop('disabled') || filled;
                    }
                    return true;
                }
                if (list.length) {
                    new MutationObserver(validateInput).observe(input[0], { childList: true, subtree: true });
                }
                field.on('change input', validateInput);
                field.closest('form').on('submit', function(event) {
                    if (!field[0].isConnected) {
                        $(this).off(event);
                        return;
                    }
                    if (this.action.indexOf('/save-draft') !== -1) return;
                    if (!validateInput()) {
                        event.preventDefault();
                        editor.addClass('is-invalid').trigger('focus');
                        if (typeof toastError === 'function') toastError(<?= json_encode(lang('Please fill in the required field: ', 'Bitte fülle das Pflichtfeld aus: ') . $question, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
                    }
                });
                validateInput();
            })();
        </script>
        <?php
    }

    public static function normalize($values, $DB, $old = [])
    {
        $old = DB::doc2Arr($old);
        $answers = [];
        $ids = [];
        foreach ($values as $key => $answer) {
            if (str_ends_with($key, '--confirmed')) {
                $answers[$key] = $answer;
                unset($values[$key]);
                $ids[] = substr($key, 0, -11);
            } else {
                $ids[] = $key;
            }
        }
        if (empty($ids)) return $values;
        foreach ($DB->db->adminFields->find(['id' => ['$in' => $ids]]) as $field) {
            if (empty($field['conditional'])) continue;
            $id = $field['id'];
            $key = $id . '--confirmed';
            if (!array_key_exists($id, $values) && !array_key_exists($key, $answers)) continue;
            // Submitted answers take precedence over stored answers, then infer from the value.
            $answer = self::answer($field, array_replace($old, $values, $answers));
            $values[$key] = $answer;
            if ($answer !== true || !array_key_exists($id, $values)) $values[$id] = null;
        }
        return $values;
    }

    public static function initializeAnswers($field, $old, $osiris)
    {
        if (empty($old) || !empty($old['conditional']) || empty($field['conditional'])) return;
        $id = $field['id'];
        foreach (['activities', 'activitiesDrafts', 'projects', 'proposals', 'persons', 'organizations', 'infrastructures', 'journals'] as $name) {
            // Compare the complete value, so zero, false and nonempty lists count as filled.
            $osiris->$name->updateMany(
                [$id => ['$exists' => true], '$expr' => ['$not' => [['$in' => ['$' . $id, [null, '', []]]]]]],
                ['$set' => [$id . '--confirmed' => true]]
            );
        }
    }

    public static function update($values)
    {
        $update = ['$set' => $values];
        foreach ($values as $key => $answer) {
            if (!str_ends_with($key, '--confirmed') || $answer === true) continue;
            $id = substr($key, 0, -11);
            unset($update['$set'][$id]);
            $update['$unset'][$id] = '';
        }
        return $update;
    }
}
