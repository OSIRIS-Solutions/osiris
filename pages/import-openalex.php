<!-- https://api.openalex.org/works?filter=author.id:A5022180345 -->

<?php
require_once BASEPATH . '/php/OpenAlexParser.php';
// get openalex-id
$openalex_id = $_GET['openalex-id'];

// get data from openalex
$openalex = new OpenAlexParser($USER['mail']);

$data = $openalex->get_works_from_author($openalex_id);

foreach ($data as $doc) {
?>

    <div class="alert <?= $doc['status'] ?> mb-10">
        <?= $doc['msg'] ?>:
        <br>
        <strong><?= $doc['title'] ?></strong>
        <br>
        <?php if (isset($doc['id'])) { ?>
            <a href="<?= ROOTPATH ?>/activities/view/<?= $doc['id'] ?>" target="_blank">
                <?= lang('import.review') ?>
            </a>
        <?php } else { ?>
            <a href="<?= $doc['link'] ?>" target="_blank" rel="noopener noreferrer"><?= $doc['link'] ?></a>
        <?php } ?>

    </div>
<?php

    // dump($doc, true);
}
// // check if data is empty
// if (empty($data) || $data['meta']['count'] == 0) {
//     echo "No data found";
//     exit;
// }

// // display data
// foreach ($data['results'] as $pub) {
//     echo "<h2>" . $pub['title'] . "</h2>";
//     echo "<p>" . $pub['authors'] . "</p>";
//     echo "<p>" . $pub['venue'] . "</p>";
// }
?>