<?php
$cursor = $osiris->activities->find(['subtype' => ['$exists' => false]]);
foreach ($cursor as $doc) {
    $osiris->activities->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['subtype' => $doc['pubtype'], 'history' => [
            ['date' => date('Y-m-d'), 'type' => 'imported', 'user' => $_SESSION['username']]
        ]]]
    );
}
