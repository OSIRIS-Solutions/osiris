<?php 
require_once "Settings.php";
require_once "Vocabulary.php";

class Fields {
    
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
}