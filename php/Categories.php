<?php

/**
 * Class for Categories and types.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @package OSIRIS
 * @since 1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once "DB.php";

// require_once "Country.php";

class Categories
{
    /**
     * All categories including associated types in a children array
     *
     * @var array
     */
    public $categories = array();
    /**
     * All types from the database as associative array
     *
     * @var array
     */
    public $types = array();
    
    /**
     * Database connector
     *
     * @var DB
     */
    private $osiris;

    function __construct()
    {
        $this->osiris = new DB;

        $categories = $this->osiris->db->adminCategories->find([], ['sort'=>['order'=>1]])->toArray();
        foreach ($categories as $c) {
            $c['children'] = [];
            $this->categories[$c['id']] = $c;
        }
        $types = $this->osiris->db->adminTypes->find([], ['sort'=>['order'=>1]])->toArray();
        foreach ($types as $c) {
            if (!isset($this->categories[$c['parent']])) continue;
            $this->types[$c['id']] = $c;
            $this->categories[$c['parent']]['children'][$c['id']] = $c;
        }
    }

    public function getCategory($id)
    {
        $category = $this->categories[$id] ?? [
            'id' => '',
            'name' => 'Unknown Category',
            'name_de' => 'Unbekannte Kategorie',
            'color' => '#000000'
        ];
        return $category;
    }


    public function getName($id)
    {
        return $this->getCategory($id)['name'];
    }

    public function cssVar($id)
    {
        $color = $this->getCategory($id)['color'];
        return "style=\"--highlight-color: $color;--highlight-color-20: ".$color."20;\"";
    }

}
