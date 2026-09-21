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
    <?= lang('admin.migrating_to_osiris_v1_5_0') ?>
</h1>
<b class="text-danger">
    <i class="ph ph-warning"></i>
    <?= lang('admin.please_read_the_following_information_carefully_it_is_important_for_the_mig') ?>
</b>

<h2>
    <?= lang('admin.project_data_fields') ?>
</h2>

<p>
    <?= lang('admin.in_the_new_version_of_osiris_we_have_changed_the_some_project_data_fields_a') ?>
</p>

<?php
$funding_organizations = $osiris->projects->distinct('funding_organization');

if (!empty($funding_organizations)) { ?>
    <h4>
        <?= lang('admin.funding_organizations') ?>
    </h4>
    <details class="collapse-panel">
        <summary class="collapse-header">
            <?= lang('admin.we_have_found_the_following_funding_organizations_in_the_projects_collectio') ?>
        </summary>
        <div class="collapse-content">
            <ul class="list">
                <?php
                foreach ($funding_organizations as $org) {
                    if (is_string($org)) {
                        echo "<li>" . $org . "</li>";
                    }
                }
                ?>
            </ul>
        </div>
    </details>
    <p>
        <?= lang('admin.in_the_future_osiris_will_no_longer_support_free_text_fields_for_funding_or') ?>
    </p>
    <p>
        <?= lang('admin.note_if_you_still_want_to_enable_free_text_fields_please_activate_the_field') ?>
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
        <?= lang('admin.contact_persons') ?>
    </h4>
    <p>
        <?= lang('admin.we_have_found_count_projects_with_a_contact_person_the_contact_person_is_no', replace: ['count' => $count]) ?>
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
        <?= lang('admin.migration_of_the_contact_person_to_the_new_field_applicants_was_successful') ?>
    </p>
<?php
    flush();
    ob_flush();
}

?>

<h2>
    <?= lang('admin.migration_of_project_types') ?>
</h2>

<p>
    <?= lang('admin.in_the_new_version_of_osiris_we_have_changed_the_project_types_fundamentall') ?>
</p>
<p>
    <?= lang('admin.in_the_next_step_we_will_migrate_the_old_project_types_to_the_new_ones_this') ?>
</p>
<?php

// first check if there are stipendiates. If yes, we need to migrate them into a new project type
$stipendiates = $osiris->projects->find(['type' => 'Stipendium'])->toArray();
$count = count($stipendiates);
if ($count > 0) {
?>
    <h4>
        <?= lang('admin.stipendiates') ?>
    </h4>
    <p>
        <?= lang('admin.we_have_found_count_projects_with_the_type_stipendium_they_will_be_migrated', replace: ['count' => $count]) ?>
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
    "subprojects" => true,
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
                ["module" => "research-countries", "required" => false],
                ["module" => "funding_number", "required" => false],
                ["module" => "funding_organization", "required" => false],
                ["module" => "kdsf-ffk", "required" => false],
                ["module" => "purpose", "required" => false],
                ["module" => "role", "required" => false],
                ["module" => "website", "required" => false],
                ["module" => "funding_type", "required" => false],
                ["module" => "topics", "required" => false],
                ["module" => "image", "required" => false]
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
    "subprojects",
    "teaser_de",
    "teaser_en",
    "topics"
]);
$general_fields = [
    'created',
    'created_by',
    'updated',
    'updated_by',
    'persons',
    'units',
    "start_date",
    "end_date",
    'nagoya',
    'nagoya_countries'
];

// migrate all projects with the type "Drittmittel" to the new project type
$projects = $osiris->projects->find(['type' => 'Drittmittel'])->toArray();
$count = count($projects);

if ($count > 0) {
    ?>

    <h4>
        <?= lang('admin.third_party_funding') ?>
    </h4>
    <p>
        <?= lang('admin.we_have_found_count_projects_with_the_type_drittmittel_they_will_be_moved_t', replace: ['count' => $count]) ?>
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
        if ($status == 'finished') {
            $status = 'approved';
        }
        // set up the base fields for the new project
        $new_proposal = [
            '_id' => $project['_id'],
            'type' => 'third-party',
            'status' => $status,
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
            "public_image" => "image"
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
}


// migrate all projects with the type "Teilprojekt" to the new project type
$projects = $osiris->projects->find(['type' => 'Teilprojekt'])->toArray();
$count = count($projects);
if ($count > 0) {
    ?>
    <h4>
        <?= lang('common.subprojects') ?>
    </h4>
    <p>
        <?= lang('admin.we_have_found_count_projects_with_the_type_teilprojekt_in_the_future_they_w', replace: ['count' => $count]) ?>
    </p>
    <?php
    flush();
    ob_flush();
    // now we need to migrate the old project types to the new ones
    foreach ($projects as $project) {
        // set up the base fields for the new project
        $new_project = [
            '_id' => $project['_id'],
            'type' => $project['parent_type'] ?? 'third-party',
            'status' => $project['status'] ?? 'project',
            'subproject' => true,
            "funding_program" => $project['funding_organization'] ?? null,
            "parent" => $project['parent'],
            "parent_id" => $project['parent_id'],
            "proposal_id" => $project['parent_id'],
        ];
        if (isset($project['parent_id']) && DB::is_ObjectID($project['parent_id'])) {
            $new_project['parent_id'] = DB::to_ObjectID($project['parent_id']);
        }
        // add the general fields to the project
        foreach ($general_fields as $field) {
            if (isset($project[$field]) && !array_key_exists($field, $new_project)) {
                $new_project[$field] = $project[$field];
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
            "public_image" => "image"
        ];
        // replace the old fields with the new ones, overwrite the new ones if they exist
        foreach ($replace as $old => $new) {
            if (isset($project[$old]) && !empty($project[$old])) {
                $new_project[$new] = $project[$old];
            }
        }

        // find parent project
        $parent = $osiris->projects->findOne(['_id' => $project['parent_id'] ?? null]);
        if (!empty($project)) {
            // add inherited fields
            $inherited = [
                'website',
                'funder',
                'funding_organization',
                'purpose',
                'role',
                'coordinator',
            ];
            foreach ($inherited as $key) {
                if (isset($parent[$key])) {
                    $new_project[$key] = $parent[$key];
                }
            }
        }
        $osiris->projects->deleteOne(['_id' => $project['_id']]);
        $osiris->projects->insertOne($new_project);
    }
    ?>
    <p>
        <?= lang('admin.successfully_migrated_all_subprojects') ?>
    </p>

<?php
}
// migrate all "subprojects" from parents
$projects = $osiris->projects->find(['subprojects' => ['$exists' => true, '$ne' => null]])->toArray();

foreach ($projects as $p) {
    $subprojects = [];
    foreach ($p['subprojects'] as $key) {
        $sub = $osiris->projects->findOne(['name' => $key]);
        if (!empty($sub)) {
            $subprojects[] = $sub['_id'];
        }
    }
    if (empty($subprojects)) continue;

    $osiris->projects->updateOne(
        ['_id' => $p['_id']],
        [
            '$set' => ['subprojects' => $subprojects]
        ]
    );
}

// next migrate activities to use the ObjectId instead of the name string
$activities = $osiris->activities->find(['projects' => ['$exists' => true, '$ne' => null]])->toArray();
$count = count($activities);
if ($count > 0) {
?>
    <h4>
        <?= lang('common.activities') ?>
    </h4>
    <p>
        <?= lang('admin.we_have_found_count_activities_with_projects_we_will_now_migrate_the_projec', replace: ['count' => $count]) ?>
    </p>
    <?php
    flush();
    ob_flush();
    foreach ($activities as $activity) {
        $projects = DB::doc2Arr($activity['projects'] ?? []);
        if (!empty($projects)) {
            $new_projects = [];
            foreach ($projects as $project) {
                if (empty($project)) {
                    continue;
                } else if (is_string($project)) {
                    $p = $osiris->projects->findOne(['name' => $project]);
                    if ($p) {
                        $new_projects[] = $p['_id'];
                    } else {
                        continue;
                    }
                } else {
                    $new_projects[] = $project;
                }
            }
            // update the activity
            $osiris->activities->updateOne(
                ['_id' => $activity['_id']],
                ['$set' => ['projects' => array_values(array_unique($new_projects))]]
            );
        }
    }
    ?>
    <p>
        <?= lang('admin.migration_of_the_activities_was_successful_you_can_now_use_the_new_project') ?>
    </p>
<?php
    flush();
    ob_flush();
}

// finish message
?>

<div class="alert success">
    <h4 class="title">
        <i class="ph ph-check-circle"></i>
        <?= lang('admin.migration_finished') ?>
    </h4>
    <?= lang('admin.migration_of_project_types_was_successful_you_can_now_use_the_new_project_t') ?>
</div>

<b class="text-danger">
    <?=lang('admin.start_rerendering_all_activities_please_have_patience')?>
</b>