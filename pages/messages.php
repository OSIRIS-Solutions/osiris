<?php

/**
 * User messages
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
 * @link        /messages
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

// retrieve messages from the database
$messages = $DB->getMessages();
// sort messages by created_at and read status
usort($messages, function ($a, $b) {
    if ($a['read'] == $b['read']) {
        return $b['created_at'] <=> $a['created_at'];
    }
    return ($a['read'] ?? false) <=> ($b['read'] ?? false);
});

$types = [
    'project' => [
        'en' => 'Project',
        'de' => 'Projekt',
        'icon' => 'tree-structure'
    ],
    'proposal' => [
        'en' => 'Proposal',
        'de' => 'Antrag',
        'icon' => 'tree-structure'
    ],
    'activity' => [
        'en' => 'Activity',
        'de' => 'Aktivität',
        'icon' => 'book-bookmark'
    ],
    'nagoya' => [
        'en' => 'Nagoya Protocol',
        'de' => 'Nagoya-Protokoll',
        'icon' => 'scales'
    ],
]
?>

<style>
    .table tr.read {
        background-color: var(--muted-color-20);
        opacity: 0.7;
    }
</style>


<h1>
    <i class="ph-duotone ph-chat-circle-text"></i>
    <?= lang('Messages', 'Nachrichten') ?>
</h1>

<?php if (!empty($messages)) { ?>
    <div class="btn-toolbar">
        <button class="btn primary small" type="button" onclick="markAllAsRead()">
            <i class="ph ph-eye-closed"></i>
            <?= lang('Mark all as read', 'Alle als gelesen markieren') ?>
        </button>
        <button class="btn danger small" type="button" onclick="deleteAllMessages()">
            <i class="ph ph-trash"></i>
            <?= lang('Delete all', 'Alle löschen') ?>
        </button>
    </div>
<?php } ?>


<div id="no-messages" class="text-center" style="<?= empty($messages) ? '' : 'display:none;' ?>">
    <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-messages.png" alt="" class="sophie-img w-400">
    <h2 class="mt-0"><?= lang('No messages', 'Keine Nachrichten') ?></h2>
    <p><?= lang('You have no messages at the moment.', 'Du hast momentan keine Nachrichten.') ?></p>
</div>

<?php if (!empty($messages)) { ?>
    <table class="table" id="messages-table">
        <?php foreach ($messages as $message) {
            $time = date('d.m.Y H:i', $message['created_at']);
            $type = $types[$message['type']] ?? [
                'en' => 'Unknown',
                'de' => 'Unbekannt',
                'icon' => 'question'
            ];
            // dump($message);
        ?>
            <tr id="message-<?= $message['id'] ?>" class="<?= ($message['read'] ?? false) ? 'read' : '' ?>">
                <td class="w-50 text-center text-primary">
                    <i class="ph ph-<?= $type['icon'] ?> ph-fw ph-2x"></i>
                    <span>
                        <?= lang($type['en'], $type['de']) ?>
                    </span>
                </td>
                <td>
                    <span class="text-muted">
                        <i class="ph ph-clock text-muted"></i>
                        <?= $time ?>
                    </span>
                    <br>

                    <?= lang($message['en'], $message['de']) ?>
                    <div class="btn-toolbar justify-content-between">
                        <?php if (isset($message['link'])) { ?>
                            <a href="<?= ROOTPATH . $message['link'] ?>" class="btn primary small">
                                <i class="ph ph-link"></i>
                                <?= lang('View', 'Anzeigen') ?>
                            </a>
                        <?php } ?>

                        <span>
                            <!-- mark as read -->
                            <?php if (!$message['read']) { ?>
                                <button class="btn primary small mark-as-read" type="button" onclick="markAsRead('<?= $message['id'] ?>')" data-toggle="tooltip" data-title="<?= lang('Mark as read', 'Als gelesen markieren') ?>">
                                    <i class="ph ph-eye-closed"></i>
                                </button>
                            <?php } ?>
                            <!-- delete message -->
                            <button class="btn danger small" type="button" onclick="deleteMessage('<?= $message['id'] ?>')" data-toggle="tooltip" data-title="<?= lang('Delete', 'Löschen') ?>">
                                <i class="ph ph-trash"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        <?php } ?>

    </table>
<?php } ?>




<script>
    function markAsRead(id) {
        console.log(id);
        $.post(ROOTPATH + '/crud/messages/mark-as-read/' + id, function(data) {
            console.log(data);
            if (data.success) {
                let msg = $('#message-' + id);
                // order to bottom
                msg.remove();
                msg.appendTo('#messages-table tbody');
                msg.addClass('read');
                msg.find('.read').remove();
            } else {
                toastError(lang('Error marking message as read', 'Fehler beim Markieren der Nachricht als gelesen'));
            }
        }, 'json');
    }

    function markAllAsRead() {
        $.post(ROOTPATH + '/crud/messages/mark-all-as-read', function(data) {
            if (data.success) {
                $('#messages-table tbody tr').addClass('read');
                $('#messages-table tbody .mark-as-read').remove();
            } else {
                toastError(lang('Error marking all messages as read', 'Fehler beim Markieren aller Nachrichten als gelesen'));
            }
        }, 'json');
    }

    function deleteMessage(id) {
        $.post(ROOTPATH + '/crud/messages/delete/' + id, function(data) {
            if (data.success) {
                $('#message-' + id).remove();
            } else {
                toastError(lang('Error deleting message', 'Fehler beim Löschen der Nachricht'));
            }
            if ($('#messages-table tbody tr').length == 0) {
                $('#messages-table').hide();
                $('.btn-toolbar').hide();
                $('#no-messages').show();
            }
        }, 'json');
    }

    function deleteAllMessages() {
        $.post(ROOTPATH + '/crud/messages/delete-all', function(data) {
            if (data.success) {
                $('#messages-table').hide();
                $('.btn-toolbar').hide();
                $('#no-messages').show();
            } else {
                toastError(lang('Error deleting all messages', 'Fehler beim Löschen aller Nachrichten'));
            }
        }, 'json');
    }
</script>