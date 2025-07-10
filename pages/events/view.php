<?php
$in_past = strtotime($conference['end']) < time();

$days = false;
if (!$in_past) {
    $days = ceil((strtotime($conference['start']) - time()) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? lang('today', 'heute') : 'in ' . $days . ' ' . lang('days', 'Tagen');
}

$conference['participants'] = DB::doc2Arr($conference['participants']);
$conference['interests'] = DB::doc2Arr($conference['interests']);

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
        border: 1px solid var(--border-color);
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

<h1><?= $conference['title'] ?></h1>
<h2 class="subtitle">
    <?= $conference['title_full'] ?>
</h2>


<!-- show research topics -->
<?php
$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
if ($topicsEnabled) {
    echo $Settings->printTopics($conference['topics'] ?? [], 'mb-20', false);
}
?>

<?php if ($conference['created_by'] == $_SESSION['username'] || $Settings->hasPermission('conferences.edit')) { ?>
    <div class="btn-toolbar">
        <a href="<?= ROOTPATH ?>/conferences/edit/<?= $conference['_id'] ?>" class="btn text-primary">
            <i class="ph ph-edit"></i>
            <?= lang('Edit event', 'Event bearbeiten') ?>
        </a>

        <div class="dropdown">
            <button class="btn text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-trash"></i> Delete <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <form action="<?= ROOTPATH ?>/crud/conferences/delete/<?= $conference['_id'] ?>" method="post" class="content">
                    <?= lang('Do you want to delete this event?', 'Möchten Sie diese Event löschen?') ?>
                    <?= lang('Please note: this cannot be undone.', 'Achtung: dies kann nicht rückgängig gemacht werden.') ?>
                    <button class="btn danger" type="submit"><?= lang('Delete', 'Löschen') ?></button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>


<div class="row row-eq-spacing">

    <div class="col-md-6 col-lg-4">

        <table class="table">
            <tr>
                <td>
                    <span class="key"><?= lang('Location', 'Ort') ?></span>
                    <?= $conference['location'] ?>
                </td>
            </tr>
            <?php if (isset($conference['type'])) { ?>
                <tr>
                    <td>
                        <span class="key"><?= lang('Type', 'Typ') ?></span>
                        <?= $conference['type'] ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (isset($conference['internal_id'])) { ?>
                <tr>
                    <td>
                        <span class="key"><?= lang('Internal ID', 'Interne ID') ?></span>
                        <?= $conference['internal_id'] ?>
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td>
                    <span class="key"><?= lang('Start', 'Beginn') ?></span>
                    <?= format_date($conference['start']) ?>

                    <?php if (!$in_past) { ?>
                        <b class="badge success ml-10"><?= $days ?></b>
                    <?php } else { ?>
                        <b class="badge danger ml-10"> <?= lang('already over', 'bereits vorbei') ?></b>
                    <?php } ?>

                </td>
            </tr>
            <tr>
                <td>
                    <span class="key"><?= lang('End', 'Ende') ?></span>
                    <?= format_date($conference['end']) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="key"><?= lang('URL', 'URL') ?></span>
                    <?php if (!empty($conference['url'])) { ?>
                        <a href="<?= $conference['url'] ?>" target="_blank"><i class="ph ph-link"></i></a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
            <?php if (!$in_past) { ?>
                <tr>
                    <td>
                        <a class="btn small" href="<?= ROOTPATH ?>/conference/ics/<?= $conference['_id'] ?>">
                            <i class="ph ph-calendar-plus"></i>
                            <?= lang('Add to calendar', 'Zum Kalender hinzufügen') ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>

        </table>
    </div>
    <?php if (isset($conference['description'])) { ?>

        <div class="col">
            <div id="description" class="box padded m-0" style="max-height: 36rem; overflow-x: auto;">
                <?= $conference['description'] ?? '' ?>
            </div>
        </div>
    <?php } ?>

</div>


<div class="row row-eq-spacing">
    <div class="col">
        <div class="header d-flex align-items-center justify-content-between">
            <h5 class="mt-0"><?= lang('Participating persons', 'Teilnehmende Personen') ?>:</h5>
            <?php if ($participate) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-minus"></i> <?= lang('Withdraw participation', 'Teilnahme zurückziehen') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-plus"></i> <?= lang('Participate', 'Teilnehmen') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['participants'])) : ?>
            <div class="box padded">
                <?= lang('No one will participate or has participated', 'Niemand wird teilnehmen oder hat teilgenommen') ?>
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
            <h5><?= lang('Interested persons', 'Interessierte Personen') ?>:</h5>
            <?php if ($interest) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-minus"></i> <?= lang('Withdraw interest', 'Interesse zurückziehen') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-plus"></i> <?= lang('Show interest', 'Interesse bekunden') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['interests'])) : ?>
            <div class="box padded">
                <?= lang('No one is currently interested', 'Keine Personen sind zurzeit interessiert') ?>
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


<h2><?= lang('Activities', 'Aktivitäten') ?></h2>
<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/add-activity?type=lecture&conference=<?= $id ?>">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add contribution', 'Beitrag hinzufügen') ?>
    </a>
</div>

<?php if (empty($activities)) : ?>
    <div class="alert muted">
        <?= lang('No activities connected', 'Noch keine Aktivitäten verknüpft') ?>
    </div>
<?php else : ?>

    <table class="table" id="result-table">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Activity', 'Aktivität') ?></th>
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
                        <a href="<?= ROOTPATH ?>/activities/<?= $activity['_id'] ?>">
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