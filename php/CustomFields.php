<?php
include_once "_config.php";
include_once "init.php";
include_once "Vocabulary.php";

class CustomFields
{

    public $fields = array();
    public $form = array();
    private DB $DB;
    private $user = '';
    private $lang = 'en';
    public $usecase = 'list';

    function __construct($form = array())
    {

        $this->form = DB::doc2Arr($form);

        $this->DB = new DB;
        $this->fields = $this->DB->db->adminFields->find([], ['sort' => ['name' => 1]])->toArray();
        $this->fields = array_column($this->fields, null, 'id');

        $this->user = $_SESSION['username'] ?? '';
        $this->lang = lang('en', 'de');
    }

    public function name(string $module)
    {
        $field = $this->fields[$module] ?? null;
        if (!isset($field)) return $module;
        return e($this->lang($field['name'], $field['name_de'] ?? null));
    }


    public function value(string $module, $default = '')
    {
        $field = $this->fields[$module] ?? null;
        if (!isset($field)) return $default;

        $format = $field['format'] ?? '';
        if (!array_key_exists($field['id'], $this->form)) return $default;

        $val = ($this->form[$field['id']] ?? '');

        if ($format == 'date') {
            return Document::format_date($val);
        }
        if ($format == 'url' && !empty($val) && $val != '-') {
            return "<a href='$val' target='_blank' class='link'>$val</a>";
        }

        // only in german because standard is always english
        if (isset($field['values']) && !empty($field['values'])) {
            if (is_array($val)) {
                $values = [];
                foreach ($val as $v) {
                    // check if the value is in the custom field values
                    foreach ($field['values'] as $f) {
                        if (in_array($v, DB::doc2Arr($f))) {
                            $values[] = $this->lang(...$f);
                            continue 2;
                        }
                    }
                    $values[] = $v;
                }
                return implode(", ", $values);
            } else {
                foreach ($field['values'] as $f) {
                    if (in_array($val, DB::doc2Arr($f))) return $this->lang(...$f);
                }
            }
        }

        if ($val === true || $val === false) {
            if ($this->usecase == 'list') return $val ? lang('Yes', 'Ja') : lang('No', 'Nein');
            if (!isset($field['name'])) {
                $field['name'] = $module;
            }
            $label = '[' . $this->lang($field['name'], $field['name_de'] ?? null) . ']';
            return $val ? $label : '';
        }
        if ($val instanceof MongoDB\Model\BSONArray) {
            $val = DB::doc2Arr($val);
        }
        if (is_array($val)) {
            return implode(", ", $val);
        }
        return $val;
    }

    private function lang($en, $de = null)
    {
        if ($de === null) return $en;
        if ($this->lang == "de") return $de;
        return $en;
    }


    private function val(string $module, $default = '')
    {
        $val = $this->form[$module] ?? $default;
        if (is_string($val)) {
            return e($val);
        }
        return $val;
    }

    function render_help(?string $text): string
    {
        if (!$text) return '';
        // $hid = 'help-'.preg_replace('/[^a-z0-9_-]+/i','-',$inputId);
        return '<div class="form-help" role="note">' . $text . '</div>';
    }

    function custom_field(string $module, $req = false, $props = [])
    {
        $field = $this->DB->db->adminFields->findOne(['id' => $module]);
        if (!isset($field)) {
            echo '<b>Data field <code>' . $module . '</code> is not defined. </b>';
            return;
        }
        $width = $field['width'] ?? 12;
        // $width = 12 / $width;
        $labelClass = ($req ? "required" : "");
        $label = lang($field['name'] ?? $field['id'], $field['name_de'] ?? null);
        $help = '';

        if (isset($props['label'])) {
            $label = lang($props['label'], $props['label_de'] ?? null);
        }
        if (isset($props['width']) && $props['width'] > 0 && $props['width'] <= 12) {
            $width = $props['width'];
        }
        if (isset($props['required']) && $props['required'] == true) {
            $labelClass = "required";
        }
        if (isset($props['help'])) {
            $help = lang($props['help'], $props['help_de'] ?? null);
            $labelClass .= " has-help";
        }

        if ($field['format'] == 'bool') {
            echo '<div class="data-module col-sm-' . $width . '" data-module="' . $module . '">';
            echo '<label for="' . $module . '" class="' . $labelClass . ' floating-title">' . $label . '</label>';

            $val = boolval($this->val($module, $field['default'] ?? ''));
            echo '<br>
                <div class="custom-radio d-inline-block">
                    <input type="radio" id="' . $module . '-true" value="true" name="values[' . $module . ']" ' . ($val == true ? 'checked' : '') . '>
                    <label for="' . $module . '-true">' . lang('Yes', 'Ja') . '</label>
                </div>
                <div class="custom-radio d-inline-block ml-20">
                    <input type="radio" id="' . $module . '-false" value="false" name="values[' . $module . ']" ' . ($val == false ? 'checked' : '') . '>
                    <label for="' . $module . '-false">' . lang('No', 'Nein') . '</label>
                </div>';
            echo $this->render_help($help);
            echo '</div>';
            return;
        } elseif ($field['format'] == 'bool-check') {
            echo '<div class="data-module col-sm-' . $width . '" data-module="' . $module . '">';
            echo '<input type="hidden" name="values[' . $module . ']" value="false">';
            echo '<div class="custom-checkbox">';
            echo '<input type="checkbox" id="' . $module . '" name="values[' . $module . ']" value="true" ' . ($this->val($module, $field['default'] ?? '') == 'true' ? 'checked' : '') . '>';
            echo '<label for="' . $module . '">' . $label . '</label>';
            echo '</div>';
            echo $this->render_help($help);
            echo '</div>';
            return;
        }
        $value = ($this->val($module, ''));
        if (!array_key_exists($module, $this->form) && isset($field['default']) && !empty($field['default'])) {
            $value = $field['default'];
        }

        if ($field['format'] == 'wikidata') {
?>
            <style>
                .wikidata-results {
                    margin-top: .5rem;
                    border: 1px solid var(--border-color, #ddd);
                    border-radius: .5rem;
                    overflow: hidden;
                }

                .wikidata-result {
                    display: block;
                    width: 100%;
                    padding: .75rem;
                    text-align: left;
                    background: white;
                    border: 0;
                    border-bottom: 1px solid #eee;
                    cursor: pointer;
                }

                .wikidata-result:hover {
                    background: #f6f8f8;
                }

                .wikidata-result span,
                .wikidata-selection span {
                    display: block;
                    color: #666;
                }

                .wikidata-result small,
                .wikidata-selection small {
                    color: #888;
                }

                .wikidata-selection {
                    margin-top: .75rem;
                    padding: .75rem;
                    border-radius: .5rem;
                    background: #f3fafa;
                    display: flex;
                    gap: 1rem;
                    align-items: center;
                    justify-content: space-between;
                }
            </style>
            <div class="data-module col-sm-<?= $width ?> wikidata-widget" id="wikidata-widget-<?= $module ?>" data-lang="<?= lang('en', 'de') ?>" data-module="<?= $module ?>">
                <label for="wikidata-search-<?= $module ?>" class="<?= $labelClass ?> floating-title"><?= $label ?></label>
                <input type="text" class="wikidata-search form-control"
                    placeholder="<?= lang('Search Wikidata ...', 'Wikidata durchsuchen ...') ?>">

                <input type="hidden" name="values[<?= $module ?>][id]" class="wikidata-id" value="<?= $value['id'] ?? '' ?>">
                <input type="hidden" name="values[<?= $module ?>][label]" class="wikidata-label" value="<?= $value['label'] ?? '' ?>">
                <input type="hidden" name="values[<?= $module ?>][description]" class="wikidata-description" value="<?= $value['description'] ?? '' ?>">

                <div class="wikidata-results"></div>
                <div class="wikidata-selected">
                    <?php if (!empty($value) && isset($value['id'])): ?>
                        <div class="wikidata-selection" data-id="<?= $value['id'] ?>">
                            <div>
                                <strong><?= $value['label'] ?? $value['id'] ?></strong>
                                <span><?= $value['description'] ?? '' ?></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-light" onclick="removeWikidataSelection(this);">&times;</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <script>
                initWikidataWidget($("#wikidata-widget-<?= $module ?>"));
            </script>
        <?php
            return;
        }

        if ($field['format'] == 'text-format') {
            $id = rand(1000, 9999);
        ?>
            <div class="data-module col-sm-<?= $width ?> lang-<?= lang('en', 'de') ?>" data-module="<?= $module ?>">
                <label for="description" class="floating-title <?= $labelClass ?>"><?= $label ?></label>
                <div class="form-group title-editor" id="<?= $module ?>-quill"><?= $value ?></div>
                <textarea name="values[<?= $module ?>]" id="<?= $module ?>" class="d-none" readonly><?= $value ?></textarea>

                <script>
                    quillEditor('<?= $module ?>');
                </script>
                <?= $this->render_help($help) ?>
            </div>
        <?php
            return;
        }
        if ($field['format'] == 'str-list') {
            $rand_id = bin2hex(random_bytes(4));
        ?>

            <div class="data-module col-sm-<?= $width ?>" data-module="<?= $module ?>">
                <label for="list-input-<?= $rand_id ?>" class="<?= $labelClass ?> floating-title"><?= $label ?></label>
                <div id="list-widget-<?= $rand_id ?>" class="list-widget" data-name="values[<?= $module ?>][]">
                    <input
                        id="list-input-<?= $rand_id ?>"
                        class="list-widget-input"
                        type="text"
                        autocomplete="off"
                        placeholder="<?= lang('Enter value and press Enter', 'Wert eingeben und Enter drücken') ?>" />
                </div>
                <?= $this->render_help($help) ?>
            </div>
            <script>
                initListWidget($("#list-widget-<?= $rand_id ?>"), <?= json_encode($this->val($module, [])) ?>);
            </script>
        <?php
            return;
        }

        if ($field['format'] == 'list' && ($field['multiple'] ?? false)) {
        ?>
            <div class="data-module col-sm-<?= $width ?>" data-module="<?= $module ?>">
                <label for="<?= $module ?>" class="<?= $labelClass ?> floating-title"><?= $label ?>

                    <?= $this->render_help($help) ?>
                </label>
                <select class="form-control" name="values[<?= $module ?>][]" id="<?= $module ?>" <?= $labelClass ?> multiple <?= $labelClass ?>>
                    <?php
                    if ($value instanceof MongoDB\Model\BSONArray) {
                        $value = DB::doc2Arr($value);
                    }
                    foreach ($field['values'] as $opt) {
                        // if is type MongoDB\Model\BSONArray, convert to array
                        if ($opt instanceof MongoDB\Model\BSONArray) $opt = DB::doc2Arr($opt);
                        $val = $opt;
                        if (is_array($opt)) {
                            $val = $opt[0];
                            $opt = lang(...$opt);
                        }
                        $selected = false;
                        if (is_array($value)) {
                            $selected = in_array($val, $value);
                        } else {
                            $selected = ($value == $val);
                        }
                    ?>
                        <option <?= ($selected ? 'selected' : '') ?> value="<?= $val ?>"><?= $opt ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <script>
                $('#<?= $module ?>').multiSelect({});
            </script>
            <?php
            return;
        }

        echo '<div class="data-module floating-form col-sm-' . $width . '" data-module="' . $module . '">';

        switch ($field['format']) {
            case 'string':
                // make sure that value is string
                if (!is_string($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    $value = e($value);
                }
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'text':
                echo '<textarea name="values[' . $module . ']" id="' . $module . '" cols="30" rows="5" class="form-control" placeholder="custom-field" ' . $labelClass . '>' . $value . '</textarea>';
                break;
            case 'int':
                echo '<input type="number" step="1" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'float':
                echo '<input type="number" step="any" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            case 'list':
                $multiple = $field['multiple'] ?? false;
                $name = 'values[' . $module . ']' . ($multiple ? '[]' : '');
                echo '<select class="form-control" name="' . $name . '" id="' . $module . '" ' . $labelClass . ' ' . ($multiple ? 'multiple' : '') . '>';
                if (!$req) {
                    echo '<option value="" ' . (empty($value) ? 'selected' : '') . '>-</option>';
                }
                if ($value instanceof MongoDB\Model\BSONArray) {
                    $value = DB::doc2Arr($value);
                }
                // keep track if any field was selected
                $any = empty($value) || $value == ($field['default'] ?? ''); // empty value is also a selection

                foreach ($field['values'] as $opt) {
                    // if is type MongoDB\Model\BSONArray, convert to array
                    if ($opt instanceof MongoDB\Model\BSONArray) $opt = DB::doc2Arr($opt);
                    $val = $opt;
                    if (is_array($opt)) {
                        $val = $opt[0];
                        $opt = lang(...$opt);
                    }
                    $selected = false;
                    if (is_array($value)) {
                        $selected = in_array($val, $value);
                    } else {
                        $selected = ($value == $val);
                    }
                    if ($selected) $any = true;
                    echo '<option ' . ($selected ? 'selected' : '') . ' value="' . $val . '">' . $opt . '</option>';
                }
                if ($field['others'] ?? false) {
                    // if nothing was selected but value is not empty, select others
                    echo '<option ' . (!$any ? 'selected' : '') . ' value="others">' . lang('Others (please specify)', 'Sonstiges (bitte angeben)') . ':</option>';
                    // echo '</select>';
            ?>
            <?php
                }
                echo '</select>';
                break;
            case 'date':
                echo '<input type="date" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . valueFromDateArray($value) . '" placeholder="custom-field">';
                break;
            case 'url':
                echo '<input type="url" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '" placeholder="custom-field">';
                break;
            default:
                echo '<input type="text" class="form-control" name="values[' . $module . ']" id="' . $module . '" ' . $labelClass . ' value="' . $value . '">';
                break;
        }

        echo '<label for="' . $module . '" class="' . $labelClass . '">' . $label . '</label>';
        echo $this->render_help($help);

        if ($field['format'] == 'list' && ($field['others'] ?? false)) {
            $any = $any ?? true;
            echo '<input type="text" class="other-input ' . ($any ? 'hidden" disabled' : '"') . ' name="values[' . $module . ']" id="' . $module . '-others" ' . $labelClass . ' value="' . $value . '">';
            ?>
            <script>
                $('#<?= $module ?>').on('change', function() {
                    if ($(this).val() == 'others') {
                        $('#<?= $module ?>-others').removeClass('hidden').attr('disabled', false);
                    } else {
                        $('#<?= $module ?>-others').addClass('hidden').attr('disabled', true);
                    }
                });
            </script>
            <style>
                .other-input {
                    color: var(--text-color);
                    background-color: white;
                    border: var(--border-width) solid var(--border-color);
                    border-radius: var(--border-radius);
                }

                .other-input::before {
                    content: '<?= lang('Other', 'Weiteres') ?>';
                    display: inline-block;
                }
            </style>
<?php
        }
        echo '</div>';
    }
}
