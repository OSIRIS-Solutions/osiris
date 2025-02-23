<?php
echo "<p>Update descriptions and other things in markdown</p>";

include(BASEPATH . '/php/MyParsedown.php');
$parsedown = new Parsedown();

// start with groups
$cursor = $osiris->groups->find([]);
foreach ($cursor as $group) {
    $result = [];
    foreach (['description', 'description_de'] as $key) {
        if (isset($group[$key]) && is_string($group[$key])) {
            $result[$key] = $parsedown->text($group[$key]);
        }
    }


    if (isset($group['research'])) {
        $result['research'] = $group['research'];

        foreach ($group['research'] as $key => $value) {
            if (!empty($value['info'] ?? ''))
                $result['research'][$key]['info'] = $parsedown->text($value['info']);

            if (!empty($value['info_de'] ?? ''))
                $result['research'][$key]['info_de'] = $parsedown->text($value['info_de']);
        }
    }
    if (empty($result)) continue;
    $osiris->groups->updateOne(
        ['_id' => $group['_id']],
        ['$set' => $result]
    );
}

// then projects
$cursor = $osiris->projects->find([]);
foreach ($cursor as $project) {
    $result = [];
    foreach (['public_abstract', 'public_abstract_de'] as $key) {
        if (isset($project[$key]) && is_string($project[$key])) {
            $result[$key] = $parsedown->text($project[$key]);
        }
    }
    if (empty($result)) continue;
    $osiris->projects->updateOne(
        ['_id' => $project['_id']],
        ['$set' => $result]
    );
}
