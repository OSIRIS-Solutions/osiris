<?php

/**
 * Migration script for OSIRIS v1.5.0
 * 
 * TODO: Check if there are stipendiates in the collection
 *       If there are any: create a new project type for them 
 *       and migrate existing datasets
 * TODO: Check if there are proposals in the collection
 *       If there are some, migrate them to the new proposals collection
 */



include_once BASEPATH . "/php/Project.php";

set_time_limit(6000);

include_once BASEPATH . "/php/Project.php";
$Project = new Project;

?>
<h1>
    <?= lang('Migrating to OSIRIS v1.5.0', 'Migration auf OSIRIS v1.5.0') ?>
</h1>
<b class="text-danger">
    <i class="ph ph-warning"></i>
    <?= lang('Please read the following information carefully. It is important for the migration of your data.', 'Bitte lesen Sie die folgenden Informationen sorgfältig durch. Sie sind wichtig für die Migration Ihrer Daten.') ?>
</b>

<h2>
    <?= lang('Project data fields', 'Projekt-Datenfelder') ?>
</h2>

<p>
    <?= lang('In the new version of OSIRIS, we have changed the some project data fields and added new ones. We will need to migrate the old data fields to the new ones. This is a one-time process and will not be repeated in the future.', 'In der neuen Version von OSIRIS haben wir einige Projektdatenfelder geändert und neue hinzugefügt. Wir müssen die alten Datenfelder in die neuen migrieren. Dies ist ein einmaliger Vorgang und wird sich in Zukunft nicht wiederholen.') ?>
</p>

<?php
$funding_organizations = $osiris->projects->distinct('funding_organization');

if (!empty($funding_organizations)) { ?>
    <h4>
        <?= lang('Funding organizations', 'Förderorganisationen') ?>
    </h4>
    <p>
        <?= lang('We have found the following funding organizations in the projects collection:', 'In der Projektsammlung haben wir die folgenden Förderorganisationen gefunden:') ?>
    </p>
    <ul class="list box padded">
        <?php
        foreach ($funding_organizations as $org) {
            if (is_string($org)) {
                echo "<li>" . $org . "</li>";
            }
        }
        ?>
    </ul>
    <p>
        <?= lang('In the future, OSIRIS will no longer support free text fields for funding organizations, universities or scholarships. However, we will not be able to migrate the data you entered in the past automatically. The data will be shown correctly in the frontend, but when editing it, you will be forced to select an organization from the list.', 'OSIRIS wird in Zukunft keine Freitextfelder für Förderorganisationen, Universitäten oder Stipendien mehr unterstützen. Allerdings können wir die von Ihnen bisher eingegebenen Daten nicht automatisch migrieren. Die Daten werden im Frontend korrekt angezeigt, bei der Bearbeitung sind Sie jedoch gezwungen, eine Organisation aus der Liste auszuwählen.') ?>
    </p>
    <p>
        <?= lang('Note: If you still want to enable free text fields, please activate the field "Funding Program" in the project type settings. You will be able to find the data you entered in the field "funding organization" in the past immediately in the field "Funding Program".', 'Hinweis: Wenn Sie dennoch Freitextfelder aktivieren möchten, aktivieren Sie bitte das Feld "Förderprogramm" in den Projekttyp-Einstellungen. Die Daten, die Sie in der Vergangenheit in das Feld "Förderorganisation" eingegeben haben, finden Sie ab sofort im Feld "Förderprogramm" wieder.') ?>
    </p>
<?php
    flush();
    ob_flush();
}



$projects = $osiris->projects->find(['contact' => ['$exists' => true, '$ne' => null]])->toArray();
$count = count($projects);
if ($count > 0) {
?>
    <h4>
        <?= lang('Contact persons', 'Ansprechpartner') ?>
    </h4>
    <p>
        <?= lang('We have found ' . $count . ' projects with a contact person. The contact person is no longer supported by OSIRIS and is replaced by the new data field "applicants". By doing so, we clean up with misunderstandings regarding this field and make it possible to add multiple applicants to a project immediately. If several applicants are already assigned to a project, we will now migrate them accordingly.', 'Wir haben ' . $count . ' Projekte mit einem Ansprechpartner gefunden. Der Ansprechpartner wird von OSIRIS nicht mehr unterstützt und wird durch das neue Datenfeld "Antragsteller:innen" ersetzt. Damit räumen wir mit Missverständnissen bezüglich dieses Feldes auf und ermöglichen es, einem Projekt gleich mehrere Antragsteller:innen hinzuzufügen. Falls einem Projekt bereits mehrere Antragsteller:innen zugeordnet sind, migrieren wir das jetzt entsprechend.') ?>
    </p>
    <?php
    foreach ($projects as $project) {
        // check if persons with role 'applicant' already exist
        $persons = DB::doc2Arr($project['persons'] ?? []);
        $applicants = array_filter($persons, function ($person) {
            return $person['role'] == 'applicant';
        });
        if (count($applicants) > 0) {
            // remove the contact person from the list of applicants
            $osiris->projects->updateOne(
                ['_id' => $project['_id']],
                ['$set' => ['applicants' => array_column($applicants, 'user')]]
            );
        } else if (isset($project['contact'])) {
            $osiris->projects->updateOne(
                ['_id' => $project['_id']],
                ['$set' => ['applicants' => [$project['contact']]]]
            );
        }
    }
    ?>
    <p>
        <?= lang('Migration of the contact person to the new field "applicants" was successful. You can now add multiple applicants to a project.', 'Die Migration des Ansprechpartners in das neue Feld "Antragsteller:innen" war erfolgreich. Sie können nun mehrere Personen zu einem Projekt hinzufügen.') ?>
    </p>
<?php
    flush();
    ob_flush();
}

?>

<h2>
    <?= lang('Migration of project types', 'Migration der Projekttypen') ?>
</h2>

<p>
    <?= lang('In the new version of OSIRIS, we have changed the project types fundamentally. They can now be completely customized and are no longer limited to the old data fields. They now also support many new data fields, including your own custom fields. Additionally, we have now added a new collection for proposals to better separate them from running projects. This allows us to better manage the data and makes confidential information more secure.', 'In der neuen Version von OSIRIS haben wir die Projekttypen grundlegend verändert. Sie können nun vollständig angepasst werden und sind nicht mehr auf die alten Datenfelder beschränkt. Sie unterstützen jetzt auch viele neue Datenfelder, einschließlich Ihrer eigenen benutzerdefinierten Felder. Außerdem haben wir eine neue Sammlung für Vorschläge hinzugefügt, um sie besser von laufenden Projekten zu trennen. Dadurch können wir die Daten besser verwalten und vertrauliche Informationen sicherer machen.') ?>
</p>
<p>
    <?= lang('In the next step, we will migrate the old project types to the new ones. This is a one-time process and will not be repeated in the future. The migration might take some time, so please be patient.', 'Im nächsten Schritt werden wir die alten Projekttypen auf die neuen migrieren. Dies ist ein einmaliger Vorgang und wird sich in Zukunft nicht wiederholen. Die Migration kann einige Zeit in Anspruch nehmen, also haben Sie bitte etwas Geduld.') ?>
</p>
<?php

// first check if there are stipendiates. If yes, we need to migrate them into a new project type
$stipendiates = $osiris->projects->find(['type' => 'Stipendium'])->toArray();
$count = count($stipendiates);
if ($count > 0) {
?>
    <h4>
        <?= lang('Stipendiates', 'Stipendien') ?>
    </h4>
    <p>
        <?= lang('We have found ' . $count . ' projects with the type "Stipendium". They will be migrated to the new project type "stipendate".', 'Wir haben ' . $count . ' Projekte mit dem Typ "Stipendium" gefunden. Sie werden in den neuen Projekttyp "Stipendium" migriert..') ?>
    </p>

<?php
    flush();
    ob_flush();

    $osiris->adminProjects->deleteOne(['id' => 'stipendate']);
    $osiris->adminProjects->insertOne([
        "id" => "stipendate",
        "icon" => "tip-jar",
        "color" => "#63a308",
        "name" => "Scholarship",
        "name_de" => "Stipendium",
        "modules" => [
            "abstract",
            "public",
            "internal_number",
            "website",
            "grant_sum",
            "supervisor",
            "scholar",
            "scholarship",
            "university"
        ],
        "disabled" => false,
        "portfolio" => true,
        "process" => "project",
        "stage" => "2",
        "updated" => "2025-04-23",
        "updated_by" => "juk20",
        "phases" => [
            [
                "id" => "project",
                "name" => "Project",
                "name_de" => "Projekt",
                "color" => "primary",
                "modules" => [
                    [
                        "module" => "abstract",
                        "required" => false
                    ],
                    [
                        "module" => "internal_number",
                        "required" => false
                    ],
                    [
                        "module" => "website",
                        "required" => false
                    ],
                    [
                        "module" => "scholar",
                        "required" => true
                    ],
                    [
                        "module" => "supervisor",
                        "required" => true
                    ],
                    [
                        "module" => "university",
                        "required" => true
                    ],
                    [
                        "module" => "scholarship",
                        "required" => true
                    ]
                ]
            ]
        ]
    ]);

    foreach ($stipendiates as $project) {
        $osiris->projects->updateOne(
            ['_id' => $project['_id']],
            ['$set' => [
                'type' => 'stipendate',
                'status' => 'project',
                'funding_organization' => $project['scholarship'] ?? null,
                'updated' => date('Y-m-d'),
                'updated_by' => 'system',
            ]]
        );
    }
}


// now we need to migrate the old project types to the new ones
$osiris->adminProjects->deleteOne(['id' => 'third-party']);
$osiris->adminProjects->insertOne([
    "id" => "third-party",
    "icon" => "hand-coins",
    "color" => "#b61f29",
    "name" => "Third-party funding",
    "name_de" => "Drittmittel",
    "phases" => [
        [
            "id" => "proposed",
            "name" => "Proposed",
            "name_de" => "Beantragt",
            "color" => "signal",
            "modules" => [
                ["module" => "abstract", "required" => false],
                ["module" => "coordinator", "required" => true],
                ["module" => "funding_organization", "required" => true],
                ["module" => "grant_sum_proposed", "required" => false],
                ["module" => "nagoya", "required" => false],
                ["module" => "purpose", "required" => true],
                ["module" => "personnel", "required" => false],
                ["module" => "ressources", "required" => false],
                ["module" => "role", "required" => false],
                ["module" => "funding_type", "required" => true],
                ["module" => "project_type", "required" => false],
            ],
        ],
        [
            "id" => "approved",
            "name" => "Approved",
            "name_de" => "Bewilligt",
            "color" => "success",
            "modules" => [
                ["module" => "funding_number", "required" => true],
                ["module" => "grant_sum", "required" => false],
                ["module" => "internal_number", "required" => false],
                ["module" => "nagoya", "required" => false],
                ["module" => "kdsf-ffk", "required" => false],
            ],
        ],
        [
            "id" => "rejected",
            "name" => "Rejected",
            "name_de" => "Abgelehnt",
            "color" => "danger",
            "modules" => [
                ["module" => "comment", "required" => false],
            ],
        ],
        [
            "id" => "project",
            "name" => "Project",
            "name_de" => "Projekt",
            "color" => "primary",
            "modules" => [
                ["module" => "name_de", "required" => false],
                ["module" => "title_de", "required" => false],
                ["module" => "abstract", "required" => false],
                ["module" => "abstract_de", "required" => false],
                ["module" => "coordinator", "required" => false],
                ["module" => "countries", "required" => false],
                ["module" => "funding_number", "required" => false],
                ["module" => "funding_organization", "required" => false],
                ["module" => "kdsf-ffk", "required" => false],
                ["module" => "purpose", "required" => false],
                ["module" => "role", "required" => false],
                ["module" => "website", "required" => false],
                ["module" => "funding_type", "required" => false],
                ["module" => "topics", "required" => false],
            ],
        ],
    ],
    "disabled" => false,
    "portfolio" => true,
    "process" => "proposal",
]);

$project_fields = file_get_contents(BASEPATH . "/data/project-fields.json");
$project_fields = json_decode($project_fields, true);

$fields_proposals = array_filter($project_fields, function ($field) {
    return array_key_exists('proposed', $field['scope']) || array_key_exists('approved', $field['scope']) || array_key_exists('rejected', $field['scope']);
});
$fields_projects = array_filter($project_fields, function ($field) {
    return array_key_exists('project', $field['scope']);
});

$fields_proposals = array_column($fields_proposals, 'id');
$fields_projects = array_column($fields_projects, 'id');
$fields_projects = array_merge($fields_projects, [
    "collaborators",
    // "public_title",
    // "public_title_de",
    // "public_subtitle",
    // "public_subtitle_de",
    // "public_abstract",
    // "public_abstract_de",
    "public_image",
    "subprojects",
    "teaser_de",
    "teaser_en",
]);
$general_fields = [
    'created',
    'created_by',
    'updated',
    'updated_by',
    'persons',
    'units',
    "start_date",
    "end_date"
];

// migrate all projects with the type "Drittmittel" to the new project type
$projects = $osiris->projects->find(['type' => 'Drittmittel'])->toArray();
$count = count($projects);
?>

<h4>
    <?= lang('Third-party funding', 'Drittmittel') ?>
</h4>
<p>
    <?= lang('We have found ' . $count . ' projects with the type "Drittmittel".
    They will be moved to the new area "Proposals". For projects that are already running, we will create new projects and link them to the proposals.', 'Wir haben gefunden ' . $count . ' Projekte mit dem Typ "Drittmittel" gefunden.
    Sie werden in den neuen Bereich "Projektanträge" verschoben. Für bereits laufende Projekte werden wir neue Projekte anlegen und mit den Anträgen verknüpfen.') ?>
</p>
<?php
flush();
ob_flush();
// now we need to migrate the old project types to the new ones
foreach ($projects as $project) {
    // delete the old project
    $osiris->projects->deleteOne(['_id' => $project['_id']]);

    $status = $project['status'] ?? 'proposed';
    if ($status == 'applied') {
        $status = 'proposed';
    }
    // set up the base fields for the new project
    $new_proposal = [
        '_id' => $project['_id'],
        'type' => 'third-party',
        'status' => $project['status'],
        'submission_date' => $project['created'] ?? date('Y-m-d'),
        'start_proposed' => $project['start_date'] ?? null,
        'end_proposed' => $project['end_date'] ?? null,
    ];
    if ($status == 'approved') {
        $new_proposal['approval_date'] = $project['updated'] ?? $project['created'] ?? date('Y-m-d');
    } else if ($status == 'rejected') {
        $new_proposal['rejection_date'] = $project['updated'] ?? $project['created'] ?? date('Y-m-d');
    }
    $new_project = [
        '_id' => $project['_id'],
        'type' => 'third-party',
        'status' => 'project',
        'proposal_id' => $project['_id'],
        "funding_program" => $project['funding_organization'] ?? null,
    ];

    // add the general fields to the proposal and project
    foreach ($general_fields as $field) {
        if (isset($project[$field]) && !array_key_exists($field, $new_proposal)) {
            $new_proposal[$field] = $project[$field];
            $new_project[$field] = $project[$field];
        }
    }

    // add the new fields to the proposal
    foreach ($fields_proposals as $field) {
        if (isset($project[$field]) && !array_key_exists($field, $new_proposal)) {
            $new_proposal[$field] = $project[$field];
        }
    }
    // add the new fields to the project
    foreach ($fields_projects as $field) {
        if (isset($project[$field]) && !array_key_exists($field, $new_project)) {
            $new_project[$field] = $project[$field];
        }
    }
    $replace = [
        "public_title" => "name",
        "public_title_de" => "name_de",
        "public_subtitle" => "title",
        "public_subtitle_de" => "title_de",
        "public_abstract" => "abstract",
        "public_abstract_de" => "abstract_de",
    ];
    // replace the old fields with the new ones, overwrite the new ones if they exist
    foreach ($replace as $old => $new) {
        if (isset($project[$old]) && !empty($project[$old])) {
            $new_project[$new] = $project[$old];
        }
    }
    // check if the project is already running
    if ($project['status'] == 'approved' || $project['status'] == 'finished') {
        // insert the new project
        $osiris->projects->insertOne($new_project);
        // add the project key to the proposal
        $new_proposal['project_id'] = $new_project['_id'];
    }
    // remove the old project from the proposals collection in case it exists
    $osiris->proposals->deleteOne(['_id' => $project['_id']]);
    $osiris->proposals->insertOne($new_proposal);
}

// finish message
?>

<div class="alert success">
    <h4 class="title">
        <i class="ph ph-check-circle"></i>
        <?= lang('Migration finished', 'Migration abgeschlossen') ?>
    </h4>
    <?= lang('Migration of project types was successful. You can now use the new project types and the new proposals collection.', 'Migration der Projekttypen war erfolgreich. Du kannst jetzt die neuen Projekttypen und die neue Antragsammlung verwenden.') ?>
</div>