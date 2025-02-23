<?php
    echo "<p>Update persons</p>";

    $cursor = $osiris->projects->find(['teaser' => ['$exists' => false]]);
    foreach ($cursor as $doc) {
        $abstract_en = $doc['public_abstract'] ?? $doc['abstract'] ?? '';
        $abstract_de = $doc['public_abstract_de'] ?? $abstract_en;
        // $teaser_de = substr($doc['abstract'], 0, 200);
        // break at words or sentences
        $teaser_en = get_preview($abstract_en, 200);
        $teaser_de = get_preview($abstract_de, 200);

        if (empty($teaser_en) && empty($teaser_de)) continue;

        $osiris->projects->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => ['teaser_en' => $teaser_en, 'teaser_de' => $teaser_de]]
        );
    }
?>
