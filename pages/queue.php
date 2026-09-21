<?php

/**
 * Page to see the queue
 * 
 * Show activities that were lately added via CRON Job.
 * Either user specific or all (editor).
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /queue/user
 * @link        /queue/editor
 *
 * @package     OSIRIS
 * @since       1.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document();
$user = $_SESSION['username'];
if ($Settings->hasPermission('report.queue') && $role == 'editor') {
    $filter = ['declined' => ['$ne' => true]];
} else {
    $filter = ['authors.user' => $user, 'declined' => ['$ne' => true]];
}
$n_queue = $osiris->queue->count($filter);
$queue = $osiris->queue->find($filter, ['sort' => ['duplicate' => 1]])->toArray();
?>

<h1>
    <i class="ph-duotone ph-queue"></i>
    <?= lang('import.queue') ?>
</h1>

<?php
if ($n_queue == 0) {
    echo "<p>" . lang('import.no_items_in_your_queue') . "</p>";
} else { ?>
    <?php
    foreach ($queue as $doc) {

        $Format->setDocument($doc);
        $id = $doc['_id'];
        $type = $doc['type'];
    ?>

        <div id="tr-<?= $id ?>">
            <div class="box mt-0 <?= isset($doc['duplicate']) ? 'duplicate' : '' ?>" id="<?= $id ?>">
                <div class="content my-10">

                    <p>
                        <span class="mr-20"><?= $Format->activity_icon($doc); ?></span>
                        <?= $Format->format(); ?>
                    </p>
                    <div class='' id="approve-<?= $id ?>">
                        <?php if (isset($doc['duplicate'])) { ?>
                            <button class="btn danger mr-10" onclick="_queue('<?= $id ?>', false)" data-toggle="tooltip" data-title="<?= lang('import.it_is_a_duplicate_remove_from_queue') ?>">
                                <i class="ph ph-x ph-fw"></i>
                            </button>
                            <button class="btn text-success" onclick="_queue('<?= $id ?>', true)" data-toggle="tooltip" data-title="<?= lang('import.no_duplicate_accept_and_add_to_the_database') ?>">
                                <i class="ph ph-check ph-fw"></i>
                            </button>
                            <a target="_self" href="<?= ROOTPATH ?>/add-activity?doi=<?= $doc['doi'] ?>" class="btn text-secondary" data-toggle="tooltip" data-title="<?= lang('import.add_manually') ?>">
                                <i class="ph ph-pencil-simple-line"></i>
                            </a>
                        <?php } else { ?>
                            <button class="btn success mr-10" onclick="_queue('<?= $id ?>', true)" data-toggle="tooltip" data-title="<?= lang('import.accept_and_add_to_the_database') ?>">
                                <i class="ph ph-check ph-fw"></i>
                            </button>
                            <button class="btn text-danger" onclick="_queue('<?= $id ?>', false)" data-toggle="tooltip" data-title="<?= lang('import.decline_and_remove_from_queue') ?>">
                                <i class="ph ph-x ph-fw"></i>
                            </button>
                            <a target="_self" href="<?= ROOTPATH ?>/add-activity?doi=<?= $doc['doi'] ?>" class="btn text-secondary" data-toggle="tooltip" data-title="<?= lang('import.add_manually') ?>">
                                <i class="ph ph-pencil-simple-line"></i>
                            </a>
                        <?php } ?>
                    </div>
                    <?php if (isset($doc['duplicate'])) {
                        $duplicate = $osiris->activities->findOne(['_id' => $doc['duplicate']]);
                    ?>
                        <p class="text-danger">
                            <?= lang('import.possible_duplicate_of') ?>
                            <a class="link colorless font-weight-bold" href="<?= ROOTPATH ?>/activities/view/<?= $doc['duplicate'] ?>" target="_blank" rel="noopener noreferrer"><?= $duplicate['title'] ?? 'Activity' ?></a>
                        </p>
                    <?php } ?>

                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>


<script>
    function _queue(id, accept = true) {
        $('.loader').addClass('show')
        $.ajax({
            type: "POST",
            // data: {
            //     accept: approval
            // },
            dataType: "html",
            url: ROOTPATH + '/queue/' + (accept ? 'accept' : 'decline') + '/' + id,
            success: function(response) {
                console.log(response);
                $('.loader').removeClass('show')

                // if (approval == 3) {
                if (accept) {
                    $('#tr-' + id).empty()
                    var p = $('<p>')
                    p.html(<?= json_encode(lang('import.added_new_activity'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                    var a = $('<a>')
                    a.attr('href', ROOTPATH + '/activities/view/' + response)
                    a.attr('target', '_blank')
                    a.html(response)
                    p.append(a)
                    $('#tr-' + id).append(p)
                    toastSuccess(
                        <?= json_encode(lang('import.added_new_activity_to_the_database'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                        <?= json_encode(lang('import.accepted'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                    )
                } else {
                    $('#tr-' + id).remove()
                    toastSuccess(
                        <?= json_encode(lang('import.activity_has_not_been_added_to_the_database'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                        <?= json_encode(lang('import.declined'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                    )
                }
            },
            error: function(response) {
                $('.loader').removeClass('show')
                toastError(response.responseText)
            }
        })
    }
</script>