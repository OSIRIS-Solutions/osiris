<?php

/**
 * Class for all project associated methods.
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

class Groups
{
    public $groups = array();
    public $tree = array();
    private $DB;
    private $UNITS = [
        'institute' => [
            'name' => 'Institute',
            'name_de' => 'Institut',
            'head' => 'Directorate',
            'head_de' => 'Direktorat',
        ],
        'department' => [
            'name' => 'Department',
            'name_de' => 'Abteilung',
            'head' => 'Head of Department',
            'head_de' => 'Abteilungsleitung',
        ],
        'group' => [
            'name' => 'Group',
            'name_de' => 'Gruppe',
            'head' => 'Head of Group',
            'head_de' => 'Arbeitsgruppenleitung',
        ],
        'research group' => [
            'name' => 'Research Group',
            'name_de' => 'Forschungsgruppe',
            'head' => 'Head of Research Group',
            'head_de' => 'Leitung der Forschungsgruppe',
        ],
        'junior research group' => [
            'name' => 'Junior Research Group',
            'name_de' => 'Nachwuchsgruppe',
            'head' => 'Head of Junior Research Group',
            'head_de' => 'Leitung der Nachwuchsgruppe',
        ],
        'infrastructure' => [
            'name' => 'Infrastructure',
            'name_de' => 'Infrastruktur',
            'head' => 'Head of Infrastructure',
            'head_de' => 'Leitung der Infrastruktur',
        ],
        'unit' => [
            'name' => 'Unit',
            'name_de' => 'Einheit',
            'head' => 'Head of Unit',
            'head_de' => 'Leitung der Organisationseinheit',
        ]
    ];

    function __construct()
    {
        $this->DB = new DB;

        $groups = $this->DB->db->groups->find(
            [],
            ['sort' => ['level' => 1, 'inactive' => 1]]
        )->toArray();
        foreach ($groups as $g) {
            $this->groups[$g['id']] = $g;
        }

        $g = array_values($this->groups);
        $this->tree = $this->tree($g)[0];
    }


    private function tree($data, $parent = 0, $depth = 0)
    {
        $ni = count($data);
        if ($ni === 0 || $depth > 100) return ''; // Make sure not to have an endless recursion
        $tree = [];
        for ($i = 0; $i < $ni; $i++) {
            if ($data[$i]['parent'] == $parent) {
                $tree[] = [
                    'id' => $data[$i]['id'],
                    'name' => lang($data[$i]['name'], $data[$i]['name_de'] ?? null),
                    'unit' => $data[$i]['unit'],
                    'color' => $data[$i]['color'] ?? '#000000',
                    'level' => $depth,
                    'inactive' => $data[$i]['inactive'] ?? false,
                    // 'head' => $v,
                    'children' => $this->tree($data, $data[$i]['id'], $depth + 1),
                ];
            }
        }
        return $tree;
    }


    public function getGroup($id)
    {
        $group = $this->groups[$id] ?? [
            'id' => '',
            'name' => 'Unknown Unit',
            'color' => '#000000',
            'level' => -1,
            'unit' => 'Unknown',
            'head' => []
        ];
        if (isset($group['head'])) {
            $head = $group['head'];
            if (is_string($head)) $group['head'] = [$head];
            else $group['head'] = DB::doc2Arr($head);
        }

        return $group;
    }

    public function findGroup($name)
    {
        if (empty($name)) return null;
        if (isset($this->groups[$name])) return $this->groups[$name];
        foreach ($this->groups as $g) {
            if ($g['name'] == $name || ($g['name_de'] ?? false) == $name) return $g;
        }
        return null;
    }

    public function getName($id)
    {
        return $this->getGroup($id)['name'];
    }

    public function getUnit($unit = null, $key = null)
    {
        if ($unit !== null)
            $unit = strtolower($unit);
        if (isset($this->UNITS[$unit])) {
            $info = $this->UNITS[$unit];
        } else {
            $info = [
                'name' => ucfirst($unit),
                'name_de' => ucfirst($unit),
                'head' => 'Head of Unit',
                'head_de' => 'Leitung der Organisationseinheit',
            ];
        }
        if ($key === null) return $info;

        if ($key == 'name')
            return lang($info['name'], $info['name_de']);
        if ($key == 'head')
            return lang($info['head'], $info['head_de']);
        return $info[$key] ?? '';
    }

    public function cssVar($id)
    {
        $color = $this->getGroup($id)['color'] ?? '#000000';
        return "style=\"--highlight-color: $color;\"";
    }

    public function deptHierarchy($depts, $level = false)
    {
        $result = ['level' => 0, 'name' => '', 'id' => ''];
        if (empty($depts)) return $result;
        foreach ($depts as $d) {
            foreach ($this->getParents($d) as $id) {
                $dept = $this->getGroup($id);
                if (!isset($dept['level'])) $dept['level'] = $this->getLevel($id);
                if ($dept['level'] === $level) return $dept;
                if ($dept['level'] > $result['level'])
                    $result = $dept;
            }
        }
        return $result;
    }
    public function deptHierarchies($depts)
    {
        $result = [];
        foreach ($depts as $d) {
            $p = $this->getParents($d);
            if ($p && $p[0] && !in_array($p[0], $result)) {
                $result[] = $p[0];
            }
        }
        return $result;
    }


    public function allPersonUnits($units)
    {
        dump($units);
        $result = $units;
        foreach ($units as $d) {
            dump($d);
            $p = $this->getParents($d, true);
            if ($p && $p[0] && !in_array($p[0], $result)) {
                $result[] = $p[0];
            }
        }
        return $result;
    }
    public function editPermission($id, $user = null)
    {
        if ($user === null) $user = $_SESSION['username'];
        $edit_perm = false;
        // get all parent units
        $parents = $this->getParents($id);
        foreach ($parents as $p) {
            // if ($p == $id) continue;
            $g = $this->getGroup($p);
            if (isset($g) && isset($g['head'])) {
                $head = $g['head'];
                if (is_string($head)) $head = [$head];
                else $head = DB::doc2Arr($head);
                if (in_array($_SESSION['username'], $head)) {
                    $edit_perm = true;
                    break;
                }
            }
        }
        return $edit_perm;
    }

    public function getDeptFromAuthors($authors)
    {
        $result = [];
        $authors = DB::doc2Arr($authors);
        if (empty($authors)) return [];
        $users = array_filter(array_column($authors, 'user'));
        foreach ($users as $user) {
            $user = $this->DB->getPerson($user);
            if (empty($user) || empty($user['depts'])) continue;
            $dept = $this->deptHierarchy($user['depts'], 1)['id'];
            if (in_array($dept, $result)) continue;
            $result[] = $dept;
        }
        return $result;
    }

    public function getHierarchy()
    {
        $groups = array_values($this->groups);
        return Groups::hierarchyList($groups);
    }

    static function hierarchyList($datas, $parent = 0, $depth = 0)
    {
        $ni = count($datas);
        if ($ni === 0 || $depth > 10) return ''; // Make sure not to have an endless recursion
        $tree = '<ul class="list">';
        for ($i = 0; $i < $ni; $i++) {
            if ($datas[$i]['parent'] == $parent) {
                $tree .= '<li>';
                $tree .= "<a class='colorless' href='" . ROOTPATH . "/groups/view/" . $datas[$i]['id'] . "' >";
                $tree .= lang($datas[$i]['name'], $datas[$i]['name_de'] ?? null);
                $tree .= "</a>";
                $tree .= Groups::hierarchyList($datas, $datas[$i]['id'], $depth + 1);
                $tree .= '</li>';
            }
        }
        $tree .= '</ul>';
        return $tree;
    }

    public function getHierarchyTree()
    {
        $groups = array_values($this->groups);
        return Groups::hierarchyTree($groups);
    }

    static function hierarchyTree($datas, $parent = 0, $depth = 0)
    {
        $ni = count($datas);
        $tree = [];
        if ($ni === 0 || $depth > 1000) return ''; // Make sure not to have an endless recursion
        for ($i = 0; $i < $ni; $i++) {
            if ($datas[$i]['parent'] == $parent) {
                $element = $datas[$i]['name'];
                if ($depth > 0) {
                    $element = str_repeat('-', $depth) . ' ' . $element;
                }
                $tree[$datas[$i]['id']] = $element;
                $tree = array_merge($tree, Groups::hierarchyTree($datas, $datas[$i]['id'], $depth + 1));
            }
        }
        return $tree;
    }

    // get the parent of a unit with a certain level
    public function getUnitParent($unit, $level = 1)
    {
        $el = $this->getGroup($unit);
        $i = 0;
        while ($el['level'] > $level) {
            $el = $this->getGroup($el['parent']);
            if ($i++ > 9) break;
        }
        return $el;
    }

    public function getParents($id, $to0 = false)
    {
        $groups = [$id];
        $el = $this->getGroup($id);
        $i = 0;
        while (!empty($el['parent'])) {
            $el = $this->getGroup($el['parent']);
            if (!$to0 && $el['level'] == 0) break; // do not show institute
            $groups[] = $el['id'];
            if ($i++ > 9) break;
        }
        $groups = array_reverse($groups);
        return $groups;
    }

    public function getChildren($id, $only_id = true)
    {
        $el = Groups::findTreeNode($this->tree, $id);
        if (!$only_id)
            return $el;

        $ids = [];
        if ($el == null) return [];
        array_walk_recursive($el, function ($v, $k) use (&$ids) {
            if ($k == 'id') $ids[] = $v;
        });
        return $ids;
    }

    public function getLevel($id)
    {
        $group = $this->getGroup($id);
        $level = $group['level'] ?? null;
        if ($level === null) {
            $parents = $this->getParents($id);
            $level = count($parents);
        }
        return $level;
    }

    private function findTreeNode($array, $find)
    {
        if ($array['id'] == $find) {
            return $array;
        }

        if (empty($array['children'])) {
            return null;
        }

        foreach ($array['children'] as $child) {
            $result = $this->findTreeNode($child, $find);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }




    /**
     * Get the hierarchy tree for a given list of person units
     *
     * @param array $personUnits Liste der Einheiten, denen eine Person angehört
     * @return array Hierarchiebaum der Einheiten
     */
    public function getPersonHierarchyTree($personUnits)
    {
        $result = [];

        foreach ($personUnits as $unit) {
            $path = $this->findUnitPath($unit, $this->tree);
            if ($path) {
                $this->mergePaths($result, $path);
            }
        }

        return $result;
    }

    /**
     * Find the path to a specific unit within the hierarchy
     *
     * @param string $unit Die zu findende Einheit
     * @param array $hierarchy Der aktuelle Hierarchieknoten
     * @param array $currentPath Der bisherige Pfad
     * @return array|null Pfad zur Einheit oder null, wenn nicht gefunden
     */
    private function findUnitPath($unit, $hierarchy, $currentPath = [])
    {
        $newPath = array_merge($currentPath, [$hierarchy['id']]);

        if ($hierarchy['id'] === $unit) {
            return $newPath;
        }

        if (!empty($hierarchy['children'])) {
            foreach ($hierarchy['children'] as $child) {
                $path = $this->findUnitPath($unit, $child, $newPath);
                if ($path) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Merge a path into the result tree
     *
     * @param array $result Referenz auf den Ergebnisbaum
     * @param array $path Der zu mergende Pfad
     */
    private function mergePaths(&$result, $path)
    {
        $current = &$result;
        foreach ($path as $node) {
            if (!isset($current[$node])) {
                $current[$node] = [];
            }
            $current = &$current[$node];
        }
    }

    /**
     * Print the hierarchy tree
     *
     * @param array $tree Der Hierarchiebaum
     * @param int $indent Die aktuelle Einrückungsebene
     */
    public function printPersonHierarchyTree($tree, $indent = 0)
    {
        foreach ($tree as $key => $subTree) {
            echo str_repeat("  ", $indent) . ($indent > 0 ? str_repeat(">", $indent) . " " : "") . "$key<br>";
            if (!empty($subTree)) {
                $this->printPersonHierarchyTree($subTree, $indent + 1);
            }
        }
    }
    public function readableHierarchy($tree, $indent = 0)
    {
        $result = [];
        foreach ($tree as $key => $subTree) {
            $group = $this->getGroup($key);
            $unit = $this->getUnit($group['unit'] ?? null);
            $result[] = [
                'id' => $key,
                'name_en' => $group['name'],
                'name_de' => ($group['name_de'] ?? null),
                'unit_en' => $unit['name'],
                'unit_de' => $unit['name_de'],
                'indent' => $indent,
                'hasChildren' => !empty($subTree) ? true : false
            ];
            // $result[] = str_repeat("  ", $indent) . "$key <br>";
            if (!empty($subTree)) {
                $result = array_merge($result, $this->readableHierarchy($subTree, $indent + 1));
            }
        }
        return $result;
    }

    /**
     * Display the hierarchy tree for a person
     *
     * @param array $personUnits Liste der Einheiten, denen eine Person angehört
     */
    public function displayPersonHierarchy($personUnits)
    {
        $tree = $this->getPersonHierarchyTree($personUnits);
        $this->printPersonHierarchyTree($tree);
    }

    /**
     * Get person unit from username and date
     *
     * @param string $user Username.
     * @param string $date Date in ISO format.
     * @param bool $include_parents Include parent units.
     * @return array Unit array.
     */
    public function getPersonUnit($user, $date = null, $include_parents = false, $only_scientific = true)
    {
        if (is_string($user)) {
            $person = $this->DB->getPerson($user);
        } else {
            $person = $user;
        }
        if (empty($person) || empty($person['units'])) return [];
        if (empty($date)) $date = date('Y-m-d');

        $units = DB::doc2Arr($person['units']);
        $units = array_filter($units, function ($unit) use ($date, $only_scientific) {
            if ($only_scientific && !$unit['scientific']) return false; // we are only interested in scientific units
            if (empty($unit['start'])) return true; // we have basically no idea when this unit was active
            return strtotime($unit['start']) <= $date && (empty($unit['end']) || strtotime($unit['end']) >= $date);
        });

        if ($include_parents) {
            $result = [];
            foreach ($units as $unit) {
                $parents = $this->getParents($unit['unit']);
                $result[] = $parents;
            }
            return $result;
        }
        return $units;
    }


    function getAllPersons($units, $date = null, $include_parents = false, $only_scientific = false)
    {
        if (is_string($units)) {
            $units = [$units];
        }
        if (empty($date)) $date = date('Y-m-d');

        $filter = [
            'units' => [
                '$elemMatch' => [
                    'unit' => ['$in' => $units],
                    '$and' => [
                        ['$or' => [
                            ['start' => null],
                            ['start' => ['$lte' => $date]]
                        ]],
                        ['$or' => [
                            ['end' => null],
                            ['end' => ['$gte' => $date]]
                        ]]
                    ]
                ]
            ],
            'is_active' => ['$ne' => false]
        ];
        if ($only_scientific) {
            $filter['units']['$elemMatch']['scientific'] = true;
        }

        $persons = $this->DB->db->persons->find($filter)->toArray();
        return $persons;
    }


    function countAllPersons($units, $date = null, $include_parents = false, $only_scientific = true)
    {
        if (is_string($units)) {
            $units = [$units];
        }
        if (empty($date)) $date = date('Y-m-d');
        $persons = $this->DB->db->persons->find(
            [
                'units' => ['$elemMatch' => [
                    'unit' => ['$in' => $units],
                    '$and' => [
                        ['$or' => [
                            ['start' => null],
                            ['start' => ['$lte' => $date]]
                        ]],
                        ['$or' => [
                            ['end' => null],
                            ['end' => ['$gte' => $date]]
                        ]]
                    ]
                ]],
                'is_active' => ['$ne' => false]
            ]
        )->toArray();
        return $persons;
    }
}
