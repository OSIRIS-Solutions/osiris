<?php

/**
 * Page to search users
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/search
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$Format = new Document(true);

$fields = file_get_contents(BASEPATH . '/data/person-fields.json');
$fields = json_decode($fields, true);

$active_fields = $Settings->get('person-data');
if (!is_null($active_fields)) {
    $active_fields = DB::doc2Arr($active_fields);
} else {
    $active_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $active_fields = array_column($active_fields, 'id');
}

foreach ($osiris->adminFields->find() as $field) {
    $f = [
        'id' => $field['id'],
        'en' => $field['name'],
        'de' => $field['name_de'] ?? null,
        'type' => $field['format'] == 'int' ? 'integer' : $field['format'],
        'input' => 'text',
        'searchable' => true,
    ];

    if ($field['format'] == 'bool') {
        $f['type'] = 'boolean';
    }

    if ($field['format'] == 'list') {
        $f['values'] =  [];
        foreach ($field['values'] as $value) {
            $f['values'][$value[1]] = lang($value[0], $value[1] ?? null);
        }
        $f['input'] = 'select';
    }
    if ($field['format'] == 'url') {
        $f['type'] = 'string';
    }

    $fields[] = $f;
}

$active = function ($field) use ($active_fields) {
    return in_array($field, $active_fields);
};

$active_fields = array_filter($fields, function ($field) use ($active) {
    return $active($field['id']);
});
$active_fields = array_values($active_fields);

?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/query-builder.default.min.css">
<script src="<?= ROOTPATH ?>/js/query-builder.standalone.js"></script>

<div class="content">
    <div class="btn-group float-right">
        <a href="<?= ROOTPATH ?>/activities/search" class="btn osiris">
            <i class="ph ph-magnifying-glass-plus"></i> <?= lang('Activities', 'Aktivitäten') ?>
        </a>
        <a href="#close-modal" class="btn osiris active">
            <i class="ph ph-student"></i> <?= lang('Users', 'Personen') ?>
        </a>
    </div>

    <h1>
        <i class="ph-duotone ph-student"></i>
        <?= lang('Advanced user search', 'Erweiterte Nutzer-Suche') ?>
    </h1>
    <!-- <form action="#" method="get"> -->

    <div id="builder"></div>

    <button class="btn osiris" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Search', 'Suchen') ?></button>

    <pre id="result" class="code my-20"></pre>

    <table class="table" id="activity-table">
        <thead>
            <th>User</th>
            <th><?= lang('First', 'Vorname') ?></th>
            <th><?= lang('Last', 'Nachname') ?></th>
            <th><?= lang('Title', 'Titel') ?></th>
            <th><?= lang('Email', 'Email') ?></th>
            <th><?= lang('Telephone', 'Telefon') ?></th>
            <th><?= lang('Position', 'Position') ?></th>
            <th><?= lang('Department', 'Abteilung') ?></th>
            <th><?= lang('ORCID', 'ORCID') ?></th>
        </thead>
        <tbody>
        </tbody>
    </table>

    <?php
    $depts = [];
    foreach ($Groups->groups as $dept) {
        $depts[$dept['id']] = $dept['name'];
    }


    $filters = [
        [
            'id' => "username",
            'label' => lang('Username', 'Kürzel'),
            'type' => 'string',
            'input' => 'text',
        ],
        [
            'id' => "first",
            'label' => lang('First name', 'Vorname'),
            'type' => 'string',
            'input' => 'text',
        ],
        [
            'id' => "last",
            'label' => lang('Last name', 'Nachname'),
            'type' => 'string',
            'input' => 'text',
        ],
        [
            'id' => "academic_title",
            'label' => lang('Acad. title', 'Akad. Titel'),
            'type' => 'string',
            'input' => 'text',
            'default_value' => 'Dr.'
        ],
        [
            'id' => "mail",
            'label' => lang('Mail', 'Email'),
            'type' => 'string',
            'input' => 'text',
        ],
        [
            'id' => "orcid",
            'label' => lang('ORCID', 'ORCID'),
            'type' => 'string',
            'input' => 'text',
        ],
        [
            'id' => 'is_active',
            'label' => lang('Is active', 'Ist aktiv'),
            'type' => 'boolean',
            'values' => [
                'true' => lang('yes', 'ja'),
                'false' => lang('no', 'nein')
            ],
            'input' => 'radio',
            'default_value' => true
        ],
        [
            'id' => 'created',
            'label' => lang('Created at', 'Angelegt am'),
            'type' => 'datetime',
            'input' => 'date',
        ],
        [
            'id' => 'updated',
            'label' => lang('Updated at', 'Geändert am'),
            'type' => 'datetime',
            'input' => 'date',
        ],
        [
            'id' => 'roles',
            'label' => lang('Roles', 'Rollen'),
            'type' => 'string',
            'input' => 'text',
        ]
    ];
    foreach ($active_fields as $field) {
        if ($field['searchable'] ?? false) {
            $field['label'] = lang($field['en'], $field['de'] ?? null);
            $filters[] = $field;
        }
    }
    if ($Settings->featureEnabled('topics')) {
        $topics = $osiris->topics->find()->toArray();
        $topics = array_column($topics, 'name', 'id');
        $FIELDS[] = [
            'id' => 'topics',
            'module_of' => $typeModules['topics'] ?? [],
            'usage' => [
                'filter',
                'columns'
            ],
            'label' => lang('Research Topics', 'Forschungsbereiche'),
            'type' => 'list',
            'input' => 'select',
            'values' => $topics
        ];
    }
    $filters = array_map(function ($f) {
        if (!isset($f['type'])) {
            dump($f);
        }
        if ($f['type'] == 'boolean') {
            $f['input'] = 'radio';
            $f['values'] = ['true' => lang('Yes', 'Ja'), 'false' => lang('No', 'Nein')];
        }
        if ($f['type'] == 'list') {
            $f['type'] = 'string';
        }
        if (isset($f['usage'])) {
            unset($f['usage']);
        }
        return $f;
    }, $filters);
    ?>


    <script>
        let filters = <?= json_encode($filters) ?>;
        var mongoQuery = $('#builder').queryBuilder({
            filters: filters,

            'lang_code': lang('en', 'de'),
            'icons': {
                add_group: 'ph ph-plus-circle',
                add_rule: 'ph ph-plus',
                remove_group: 'ph ph-x-circle',
                remove_rule: 'ph ph-x',
                error: 'ph ph-warning',
            },
            allow_empty: true,
            default_filter: 'is_active'
        });

        var dataTable;

        function getResult() {
            dataTable.ajax.reload()
        }


        $(document).ready(function() {
            var hash = window.location.hash.substr(1);
            if (hash !== undefined && hash != "") {
                try {
                    var rules = JSON.parse(decodeURI(hash))
                    $('#builder').queryBuilder('setRulesFromMongo', rules);
                } catch (SyntaxError) {
                    console.info('invalid hash')
                }
            }

            dataTable = $('#activity-table').DataTable({
                ajax: {
                    "url": ROOTPATH + '/api/users',
                    data: function(d) {
                        // https://medium.com/code-kings/datatables-js-how-to-update-your-data-object-for-ajax-json-data-retrieval-c1ac832d7aa5
                        var rules = $('#builder').queryBuilder('getMongo')
                        if (rules === null) rules = []
                        console.log(rules);

                        rules = JSON.stringify(rules)
                        $('#result').html('filter = ' + rules)
                        d.json = rules

                        window.location.hash = rules
                    },
                },
                language: {
                    "zeroRecords": lang("No matching records found", 'Keine passenden Aktivitäten gefunden'),
                    "emptyTable": lang('No activities found for your filters.', 'Für diese Filter konnten keine Aktivitäten gefunden werden.'),
                },
                // "pageLength": 5,
                columnDefs: [{
                        targets: 0,
                        data: 'username',
                        "render": function(data, type, full, meta) {
                            return `<a href="${ROOTPATH}/profile/${data}">${data}</a>`;
                        }
                    },
                    {
                        target: 1,
                        data: 'first',
                        defaultContent: ''
                    },
                    {
                        target: 2,
                        data: 'last',
                        defaultContent: ''
                    },
                    {
                        target: 3,
                        data: 'academic_title',
                        defaultContent: ''
                    },
                    {
                        target: 4,
                        data: 'mail',
                        defaultContent: ''
                    },
                    {
                        target: 5,
                        data: 'telephone',
                        defaultContent: ''
                    },
                    {
                        target: 6,
                        data: 'position',
                        defaultContent: ''
                    },
                    {
                        targets: 7,
                        data: 'dept',
                        defaultContent: ''
                    },
                    {
                        target: 8,
                        data: 'orcid',
                        defaultContent: ''
                    },
                ]
            });

        });
    </script>

</div>