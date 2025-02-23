<?php
// migrate old documents, convert old history (created_by, edited_by) to new history format
$cursor = $osiris->activities->find(['history' => ['$exists' => false], '$or' => [['created_by' => ['$exists' => true]], ['edited_by' => ['$exists' => true]]]]);
foreach ($cursor as $doc) {
    if (isset($doc['history'])) continue;
    $id = $doc['_id'];
    $values = ['history' => []];
    if (isset($doc['created_by'])) {
        $values['history'][] = [
            'date' => $doc['created'],
            'user' => $doc['created_by'],
            'type' => 'created',
            'changes' => []
        ];
    }
    if (isset($doc['edited_by'])) {
        $values['history'][] = [
            'date' => $doc['edited'],
            'user' => $doc['edited_by'],
            'type' => 'edited',
            'changes' => []
        ];
    }

    // $values['history'][count($values['history']) - 1]['current'] = $doc['rendered']['print'] ?? 'unknown';

    $osiris->activities->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );
    // remove old fields
    $osiris->activities->updateOne(
        ['_id' => $id],
        ['$unset' => ['edited_by' => '', 'created' => '', 'edited' => '']]
    );
}
