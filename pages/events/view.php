<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
require_once BASEPATH . '/php/EventForms.php';
require_once BASEPATH . '/php/Modules.php';
require_once BASEPATH . '/php/Document.php';
$eventFields = EventForms::visibleFields($osiris, $Settings);
$eventModules = new Modules(DB::doc2Arr($conference));
$eventModules->setFields($eventFields);
$eventFormat = new Document(false, 'list');

$today = date('Y-m-d');
$start = $conference['start'];
$end = $conference['end'] ?: $start;

$is_today = ($today == $start && $today == $end);
$in_past = $end < $today;

$days = false;
if ($is_today) {
    $days = t('common.today');
} elseif (!$in_past) {
    $days = ceil((strtotime($start) - time()) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? t('events.currently_ongoing') : 'in ' . $days . ' ' . t('common.days_view');
} elseif ($in_past) {
    $days = ceil((time() - strtotime($end)) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? t('events.until_today') : t('events.ended') . ' ' . $days . ' ' . t('events.days_ago');
}

$conference['participants'] = DB::doc2Arr($conference['participants'] ?? []);
$conference['interests'] = DB::doc2Arr($conference['interests'] ?? []);

$interest = in_array($_SESSION['username'], $conference['interests']);
$participate = in_array($_SESSION['username'], $conference['participants']);
?>

<style>
    .badge.person {
        /* d-flex align-items-center */
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: white;
        border: var(--border-width) solid var(--border-color);
    }

    .badge.person:hover {
        box-shadow: 0px 3px 3px 0px var(--primary-color-20);
    }

    .badge.person img {

        /* profile-img small mr-20 */
        height: 5rem;
        margin-right: 1rem;
    }
</style>

<h1><?= e($conference['title']) ?></h1>
<?php if (in_array('title_full', array_column($eventFields, 'id'), true) && !empty($conference['title_full'])): ?>
    <h2 class="subtitle"><?= e($conference['title_full']) ?></h2>
<?php endif; ?>


<?php if ($conference['created_by'] == $_SESSION['username'] || $Settings->hasPermission('conferences.edit')) { ?>
    <div class="btn-toolbar">
        <a href="<?= ROOTPATH ?>/conferences/edit/<?= $conference['_id'] ?>" class="btn text-primary">
            <i class="ph ph-edit"></i>
            <?= t('events.edit_event') ?>
        </a>

        <div class="dropdown">
            <button class="btn text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-trash"></i> Delete <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <form action="<?= ROOTPATH ?>/crud/conferences/delete/<?= $conference['_id'] ?>" method="post" class="content">
                    <?= t('events.do_you_want_to_delete_this_event') ?>
                    <?= t('common.please_note_this_cannot_be_undone') ?>
                    <button class="btn danger" type="submit"><?= t('action.delete') ?></button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>


<div class="row row-eq-spacing">

    <div class="col">

        <table class="table">
            <?php foreach ($eventFields as $field):
                $fieldType = $field['type'] ?? 'field';
                $id = $field['id'] ?? '';
                $props = $field['props'] ?? [];
                if (in_array($id, ['event-title', 'title_full'], true)) continue;
            ?>
                <tr><td colspan="2">
                    <?php if ($fieldType === 'heading'): ?>
                        <strong><?= e(lang($props['text'] ?? '', $props['text_de'] ?? null)) ?></strong>
                    <?php elseif ($fieldType === 'paragraph'): ?>
                        <?= nl2br(e(lang($props['text'] ?? '', $props['text_de'] ?? null))) ?>
                    <?php elseif ($fieldType === 'hr'): ?><hr>
                    <?php else: ?>
                        <span class="key"><?= e($eventModules->get_name($id)) ?></span>
                        <?= $eventFormat->getEventField(DB::doc2Arr($conference), $id) ?: '-' ?>
                    <?php endif; ?>
                </td></tr>
            <?php endforeach; ?>
            <?php if (isset($conference['internal_id']) && !in_array('internal_id', array_column(EventForms::fields($osiris), 'id'), true)): ?>
                <tr><td><span class="key"><?= t('common.internal_id') ?></span><?= e($conference['internal_id']) ?></td></tr>
            <?php endif; ?>
            <tr><td><b class="badge <?= $in_past ? 'danger' : 'success' ?>"><?= e($days) ?></b></td></tr>
            <?php if (!$in_past): ?>
                <tr><td><a class="btn small" href="<?= ROOTPATH ?>/conference/ics/<?= e($conference['_id']) ?>"><i class="ph ph-calendar-plus"></i> <?= t('common.add_to_calendar') ?></a></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>


<div class="row row-eq-spacing">
    <div class="col">
        <div class="header d-flex align-items-center justify-content-between">
            <h5 class="mt-0"><?= t('events.participating_persons') ?>:</h5>
            <?php if ($participate) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-minus"></i> <?= t('events.withdraw_participation') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-plus"></i> <?= t('events.participate') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['participants'])) : ?>
            <div class="box padded">
                <?= t('events.no_one_will_participate_or_has_participated') ?>
            </div>
        <?php else : ?>
            <?php foreach ($conference['participants'] as $username) : ?>
                <div class="badge person">
                    <?= $Settings->printProfilePicture($username, 'img') ?>
                    <div class="">
                        <b class="my-0">
                            <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                <?= $DB->getNameFromId($username) ?>
                            </a>
                        </b>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>


        <div class="header d-flex align-items-center justify-content-between">
            <h5><?= t('events.interested_persons') ?>:</h5>
            <?php if ($interest) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-minus"></i> <?= t('events.withdraw_interest') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-plus"></i> <?= t('events.show_interest') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['interests'])) : ?>
            <div class="box padded">
                <?= t('events.no_one_is_currently_interested') ?>
            </div>
        <?php else : ?>
            <?php foreach ($conference['interests'] as $username) : ?>
                <div class="badge person">
                    <?= $Settings->printProfilePicture($username, 'img') ?>
                    <div class="">
                        <b class="my-0">
                            <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                <?= $DB->getNameFromId($username) ?>
                            </a>
                        </b>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<h2><?= t('common.activities') ?></h2>
<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/add-activity?type=lecture&conference=<?= $id ?>">
        <i class="ph ph-plus-circle"></i>
        <?= t('common.add_contribution') ?>
    </a>
</div>

<?php if (empty($activities)) : ?>
    <div class="alert muted">
        <?= t('events.no_activities_connected') ?>
    </div>
<?php else : ?>

    <table class="table" id="result-table">
        <thead>
            <tr>
                <th><?= t('common.type') ?></th>
                <th><?= t('common.activity') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities as $activity) :
                $rend = $activity['rendered'] ?? array();
            ?>
                <tr>
                    <td class="w-50"><?= $rend['icon'] ?? '' ?></td>
                    <td><?= $rend['web'] ?? '' ?></td>
                    <td class="w-50">
                        <a href="<?= ROOTPATH ?>/activities/view/<?= $activity['_id'] ?>">
                            <i class="ph ph-arrow-fat-line-right"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<script>
    function conferenceToggle(el, id, type = 'interests') {
        // ajax call to update user's conference interests
        $.ajax({
            url: ROOTPATH + '/ajax/conferences/toggle-interest',
            type: 'POST',
            data: {
                type: type,
                conference: id
            },
            success: function(data) {
                if (data) {
                    // reload page
                    location.reload();
                }

            }
        })
    }
</script>


<?php
if (isset($_GET['verbose'])) {
    dump($conference, true);
}
?>