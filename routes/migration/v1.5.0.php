
<?php
/**
 * Migration script for OSIRIS v1.5.0
 * 
 * TODO: Check if there are stipendiates in the collection
 *       If there are any: create a new activity type for them 
 *       and migrate existing datasets
 * TODO: Check if there are proposals in the collection
 *       If there are some, migrate them to the new proposals collection
 */


// include_once BASEPATH . "/php/Project.php";
// $Project = new Project;
// // Drittmittel
// $osiris->adminProjects->deleteOne(['id' => 'third-party']);
// $osiris->adminProjects->insertOne([
//     'id' => 'third-party',
//     'icon' => 'hand-coins',
//     'color' => '#B61F29',
//     'name' => 'Third-party funding',
//     'name_de' => 'Drittmittel',
//     'modules' => [
//         'abstract',
//         'public',
//         'internal_number',
//         'website',
//         'grant_sum',
//         'funder',
//         'funding_number',
//         'grant_sum_proposed',
//         'personnel',
//         'ressources',
//         'contact',
//         'purpose',
//         'role',
//         'coordinator',
//         'nagoya',
//     ],
//     'topics' => true,
//     'disabled' => false,
//     'portfolio' => true,
//     'has_subprojects' => true,
//     'inherits' => [
//         'status',
//         'website',
//         'grant_sum',
//         'funder',
//         'grant_sum_proposed',
//         'purpose',
//         'role',
//         'coordinator',
//     ]
// ]);
// $osiris->projects->updateMany(
//     ['type' => 'Drittmittel'],
//     ['$set' => ['type' => 'third-party']]
// );

// $osiris->adminProjects->deleteOne(['id' => 'stipendate']);
// $osiris->adminProjects->insertOne([
//     'id' => 'stipendate',
//     'icon' => 'tip-jar',
//     'color' => '#63a308',
//     'name' => 'Scholarship',
//     'name_de' => 'Stipendium',
//     'modules' => [
//         'abstract',
//         'public',
//         'internal_number',
//         'website',
//         'grant_sum',
//         'supervisor',
//         'scholar',
//         'scholarship',
//         'university',
//     ],
//     'topics' => false,
//     'disabled' => false,
//     'portfolio' => true
// ]);
// $osiris->projects->updateMany(
//     ['type' => 'Stipendium'],
//     ['$set' => ['type' => 'stipendiate']]
// );

// $osiris->adminProjects->deleteOne(['id' => 'subproject']);
// $osiris->projects->updateMany(
//     ['type' => ['$in' => ['Teilprojekt', 'subproject']]],
//     ['$set' => ['type' => 'third-party', 'subproject' => true]]
// );

// $osiris->adminProjects->deleteOne(['id' => 'self-funded']);
// $osiris->adminProjects->insertOne([
//     'id' => 'self-funded',
//     'icon' => 'piggy-bank',
//     'color' => '#ECAF00',
//     'name' => 'Self-funded',
//     'name_de' => 'Eigenfinanziert',
//     'modules' => [
//         'abstract',
//         'public',
//         'internal_number',
//         'website',
//         'personnel',
//         'ressources',
//         'contact',
//     ],
//     'topics' => false,
//     'disabled' => false,
//     'portfolio' => false
// ]);
// $osiris->projects->updateMany(
//     ['type' => 'Eigenfinanziert'],
//     ['$set' => ['type' => 'self-funded']]
// );

// echo "<p>Updated project types.</p>";