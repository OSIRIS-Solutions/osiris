<?php

/**
 * Page to see the queue
 * 
 * Show activities that were lately added via CRON Job.
 * Either user specific or all (editor).
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /queue/user
 * @link        /queue/editor
 *
 * @package     OSIRIS
 * @since       1.1.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document();
$user = $_SESSION['username'];
if ($Settings->hasPermission('report.queue')) {
    $filter = ['declined' => ['$ne' => true]];
} else {
    $filter = ['authors.user' => $user, 'declined' => ['$ne' => true]];
}
$n_queue = $osiris->queue->count($filter);
$queue = $osiris->queue->find($filter, ['sort' => ['duplicate' => 1]])->toArray();
?>

<h1>
    <i class="ph ph-queue text-osiris"></i>
    <?= lang('Queue', 'Warteschlange') ?>
</h1>

<?php
if ($n_queue == 0) {
    echo "<p>" . lang(
        "No items in your queue.",
        "Keine Elemente in der Warteschlange."
    ) . "</p>";
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
                            <button class="btn danger mr-10" onclick="_queue('<?= $id ?>', false)" data-toggle="tooltip" data-title="<?= lang('It is a duplicate: remove from queue.', 'Es ist ein Duplikat: aus der Warteschlange entfernen.') ?>">
                                <i class="ph ph-x ph-fw"></i>
                            </button>
                            <button class="btn text-success" onclick="_queue('<?= $id ?>', true)" data-toggle="tooltip" data-title="<?= lang('No duplicate: Accept and add to the database.', 'Kein Duplikat: akzeptieren und zur Datenbank hinzufügen.') ?>">
                                <i class="ph ph-check ph-fw"></i>
                            </button>
                            <a target="_self" href="<?= ROOTPATH ?>/add-activity?doi=<?= $doc['doi'] ?>" class="btn text-secondary" data-toggle="tooltip" data-title="<?= lang('Add manually', 'Manuell hinzufügen') ?>">
                                <i class="ph ph-pencil-simple-line"></i>
                            </a>
                        <?php } else { ?>
                            <button class="btn success mr-10" onclick="_queue('<?= $id ?>', true)" data-toggle="tooltip" data-title="<?= lang('Accept and add to the database.', 'Akzeptieren und zur Datenbank hinzufügen.') ?>">
                                <i class="ph ph-check ph-fw"></i>
                            </button>
                            <button class="btn text-danger" onclick="_queue('<?= $id ?>', false)" data-toggle="tooltip" data-title="<?= lang('Decline and remove from queue.', 'Ablehnen und aus der Warteschlange entfernen.') ?>">
                                <i class="ph ph-x ph-fw"></i>
                            </button>
                            <a target="_self" href="<?= ROOTPATH ?>/add-activity?doi=<?= $doc['doi'] ?>" class="btn text-secondary" data-toggle="tooltip" data-title="<?= lang('Add manually', 'Manuell hinzufügen') ?>">
                                <i class="ph ph-pencil-simple-line"></i>
                            </a>
                        <?php } ?>
                    </div>
                    <?php if (isset($doc['duplicate'])) {
                        $duplicate = $osiris->activities->findOne(['_id' => $doc['duplicate']]);
                    ?>
                        <p class="text-danger">
                            <?= lang('Possible duplicate of ', 'Mögliches Duplikat von ') ?>
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
                    p.html(lang('Added new activity: ', 'Neue Aktivität hinzugefügt: '))
                    var a = $('<a>')
                    a.attr('href', ROOTPATH + '/activities/view/' + response)
                    a.attr('target', '_blank')
                    a.html(response)
                    p.append(a)
                    $('#tr-' + id).append(p)
                    toastSuccess(
                        lang('Added new activity to the database.', 'Neue Aktivität zur Datenbank hinzugefügt.'),
                        lang('Accepted', 'Akzeptiert')
                    )
                } else {
                    $('#tr-' + id).remove()
                    toastSuccess(
                        lang('Activity has not been added to the database.', 'Aktivität wurde nicht zur Datenbank hinzugefügt.'),
                        lang('Declined', 'Abgelehnt')
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