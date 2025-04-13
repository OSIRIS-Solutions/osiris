
<?php
$projects = $osiris->projects->find(['collaborators' => ['$exists' => true]], ['projection' => ['collaborators' => 1]])->toArray();

foreach ($projects as $project) {
    $collaborators = $project['collaborators'];
    if (empty($collaborators)) continue;
    foreach ($collaborators as $i => $p) {
        if (isset($p['organization'])) continue;
        // check if organisation already exists
        $coll_id = $osiris->organizations->findOne(['$or' => [
            ['name' => $p['name'], 'country' => $p['country']],
            ['ror' => $p['ror']]
        ]]);
        if (!empty($coll_id)) {
            $coll_id = $coll_id['_id'];
        } else {
            $new_org = $osiris->organizations->insertOne([
                'name' => $p['name'],
                'type' => $p['type'] ?? 'other',
                'location' => $p['location'] ?? null,
                'country' => $p['country'],
                'ror' => $p['ror'],
                'lat' => $p['lat'] ?? null,
                'lng' => $p['lng'] ?? null,
                'created_by' => $_SESSION['username'] ?? 'system',
                'created' => date('Y-m-d')
            ]);
            $coll_id = $new_org->getInsertedId();
        }
        $collaborators[$i]['organization'] = $coll_id;
    }
    $osiris->projects->updateOne(
        ['_id' => $project['_id']],
        ['$set' => ['collaborators' => $collaborators]]
    );
}
?>
