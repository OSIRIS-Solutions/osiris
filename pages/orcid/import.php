<?php

/**
* See import-openalex.php for reference
* 
* Trigger once the function to get all works form ORCID and parse
*
* Visualize a list of all works of the user found in ORCID that are not yet in Osiris
* Make import/reject buttons for each work
*/

require_once BASEPATH . '/php/OrcidParser.php';

$username = $_SESSION['username'];

$orcid_parser = new OrcidParser($username);

// $data = $orcid_parser->getWorks();
// $data = $orcid_parser->getWork('2489030');

// echo '<pre>';
// print_r($data);
// echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
// echo '</pre>';

foreach ($orcid_parser->getWorksForImport() as $doc) {
?>
    <div class="alert mb-10">
        Ready to import - <?= $doc['type'], ' ', $doc['subtype'] ?>:
        <br>
        <strong><?= $doc['title'] ?></strong>
        <?php if (!empty($doc['journal'])) { ?><br>in <em><?= $doc['journal'] ?></em><?php } ?>
        <br>
        <?php foreach ($doc['authors'] as $author) { ?>
            <span><?= $author['first'] ?? '', ' ', $author['last'] ?? '', ', ' ?></span>
        <?php } ?>
    </div>
<?php } ?>