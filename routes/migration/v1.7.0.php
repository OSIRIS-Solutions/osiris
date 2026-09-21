<?php

/**
 * Migration script for OSIRIS v1.7.0
 * Transforms Nagoya permit info in proposals to new format.
 */

// check if Nagoya is enabled
$nagoyaEnabled = $Settings->get('features.nagoya.enabled', false);


// transform nagoya info from proposals
$proposals = $osiris->proposals->find(['nagoya' => ['$in' => ['yes', 'no']]])->toArray();
$N_ = count($proposals);

if ($N_ == 0 && !$nagoyaEnabled) {
    echo "<p>". lang('admin.no_proposals_with_nagoya_info_found_and_nagoya_feature_is_disabled_no_chang') . "</p>";
} else {
    foreach ($proposals as $proposal) {
        $enabled = ($proposal['nagoya'] == 'yes');
        $countries = [];
        foreach ($proposal['nagoya_countries'] ?? [] as $iso) {
            $countries[] = [
                'id' => uniqid(),
                'code' => $iso,
                'abs' => null
            ];
        }
        $nagoya = [
            'enabled' => $enabled,
            'countries' => $countries,
            'status' => (empty($countries) ? 'incomplete' : 'abs-review')
        ];
        $osiris->proposals->updateOne(
            ['_id' => $proposal['_id']],
            ['$set' => [
                'nagoya' => $nagoya,
            ]]
        );
    }
    echo "<p>". lang('admin.nagoya_info_transformed_for_proposals_proposals', replace: ['proposals' => count($proposals)]) . "</p>";
}


// get queries without 'type' field
$queries = $osiris->queries->find()->toArray();
$N_ = count($queries);

foreach ($queries as $query) {
    $type = $query['type'] ?? 'activities';
    if ($type == 'activity') {
        $type = 'activities';
    } elseif ($type == 'project') {
        $type = 'projects';
    } elseif ($type == 'proposal') {
        $type = 'proposals';
    }
    $osiris->queries->updateOne(
        ['_id' => $query['_id']],
        ['$set' => [
            'type' => $type,
        ]]
    );
}
echo "<p>" . lang('admin.type_field_added_to_n_queries', replace: ['N' => $N_]) . "</p>";