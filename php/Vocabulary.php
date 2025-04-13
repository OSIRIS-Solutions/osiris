<?php

/**
 * Vocabulary class
 * 
 * This class is part of the OSIRIS package.
 * 
 * @package     OSIRIS
 * @since       1.4.1
 *
 * @copyright  2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author    Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once "DB.php";

class Vocabulary extends DB
{
    /**
     * @var array $vocabularies
     */
    private $vocabularies = [];

    // init
    public function __construct()
    {
        parent::__construct();

        // load vocabularies
        $this->load();
    }

    /**
     * Load vocabularies from json file
     */
    private function load()
    {
        // open data/vocabulary.json
        $vocabularies = file_get_contents(BASEPATH . "/data/vocabulary.json");
        $this->vocabularies = json_decode($vocabularies, true);

        // transform to associative array
        $this->vocabularies = array_column($this->vocabularies, null, 'id');

        // get all vocabularies from the database
        $vocabularies_db = $this->db->adminVocabularies->find()->toArray();

        // let db values overwrite the json file
        foreach ($vocabularies_db as $v) {
            $this->vocabularies[$v['id']]['values'] = $v['values'];
        }
    }

    /**
     * Get all vocabularies
     * 
     * @return array
     */
    public function getVocabularies()
    {
        return $this->vocabularies;
    }

    /**
     * Get vocabulary by id
     * 
     * @param string $id
     * @return array
     */
    public function getVocabulary($id)
    {
        return $this->vocabularies[$id] ?? null;
    }

    /**
     * Get values of a vocabulary
     * 
     * @param string $id
     * @return array
     */
    public function getValues($id, $all=false)
    {
        $vocab = $this->getVocabulary($id);
        $values = DB::doc2Arr($vocab['values'] ?? []);
        // filter inactive
        if ($all) {
            return $values;
        }
        $values = array_filter($values, function ($v) {
            return !($v['inactive'] ?? false);
        });
        return $values;
    }

    /**
     * Get value in a specific language
     * 
     * @param string $id
     * @param string $key
     * @param string $lang
     * @return string
     */
    public function getValue($id, $key, $default='')
    {
        $lang = lang('en', 'de');
        $values = $this->getValues($id, true);
        // get value by key
        $values = array_column($values, $lang, 'id');
        $value = $values[$key] ?? $default;
        return $value;
    }

}
