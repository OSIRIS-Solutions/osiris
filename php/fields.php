<?php
require_once "Settings.php";
require_once "Vocabulary.php";

class Fields
{

    public $fields = array();
    private $Vocabulary;

    function __construct()
    {
        // $Settings = new Settings();
        $DB = new DB();
        $this->Vocabulary = new Vocabulary();
        // $osiris = $DB->db;
    }

    public static function typeConvert($type)
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'double',
            'bool', 'bool-check' => 'boolean',
            'list' => 'string',
            'url' => 'string',
            'text' => 'string',
            default => 'string',
        };
    }

    public function getField($id)
    {
        foreach ($this->fields as $f) {
            if ($f['id'] == $id) return $f;
        }
        return null;
    }

    public function vocabularyValues($vocabularyId)
    {
        $voc = $this->Vocabulary->getValues($vocabularyId);
        $list = [];
        foreach ($voc as $v) {
            $list[$v['id']] = lang($v['en'], $v['de'] ?? null);
        }
        return $list;
    }

    public function addCustomFields($FIELDS, $osiris, $typeModules = [])
    {
        foreach ($osiris->adminFields->find() as $field) {
            // make sure that id does not exist yet
            $exists = false;
            foreach ($FIELDS as $existingField) {
                if ($existingField['id'] == $field['id']) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) continue;
            $f = [
                'id' => $field['id'],
                'module_of' => $typeModules[$field['id']] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang($field['name'], $field['name_de'] ?? null) . ' (' . $field['id'] . ')',
                'type' => self::typeConvert($field['format'] ?? 'string'),
                'custom' => true
            ];

            if ($field['format'] == 'list') {
                $values =  DB::doc2Arr($field['values'] ?? []);
                // convert from indexed array to associative array
                // if english and german values are set, use only the english as keys and lang as values
                $newValues = [];
                foreach ($values as $v) {
                    if (is_string($v)) {
                        // Simple case: only one string -> key = value
                        $newValues[$v] = $v;
                    } elseif (is_iterable($v) && isset($v[0])) {
                        // Array: first value as key, language-dependent as value
                        try {
                            $key = $v[0];
                            $langKey = lang(0, 1);
                            $newValues[$key] = $v[$langKey] ?? $key;
                        } catch (\Throwable $th) {
                            // log error and continue
                            error_log("Error processing field values for field " . $field['id'] . ": " . $th->getMessage());
                        }
                    } else {
                        // ignore
                    }
                }

                $f['values'] = $newValues;
                $f['input'] = 'select';
                if ($field['multiple'] ?? false) {
                    $f['type'] = 'list';
                }
            }

            $FIELDS[] = $f;
        }
        return $FIELDS;
    }
}
