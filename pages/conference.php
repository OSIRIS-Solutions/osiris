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


<h1><?= $conference['title'] ?></h1>
<h2 class="subtitle">
    <?= $conference['title_full'] ?>
</h2>

<div id="description">
    <?= $conference['description'] ?? '' ?>
</div>

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
                <b class="badge success"><?= $days ?></b>
            <?php } else { ?>
                <b class="badge danger"> <?= lang('already over', 'bereits vorbei') ?></b>
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
            <a href="<?= $conference['url'] ?>" target="_blank"><?= $conference['url'] ?></a>
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

<h2>
    <?= lang('Persons', 'Personen') ?>
</h2>

<h4><?= lang('Participating persons', 'Teilnehmende Personen') ?>:</h4>

<div class="btn-toolbar">
    <?php if ($participate) { ?>
        <a class="btn active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
            <i class="ph ph-user-circle-minus"></i> <?= lang('Withdraw participation', 'Teilnahme zurückziehen') ?>
        </a>
    <?php } else { ?>
        <a class="btn" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
            <i class="ph ph-user-circle-plus"></i> <?= lang('Participate', 'Teilnehmen') ?>
        </a>
    <?php } ?>
</div>

<table class="table">
    <tbody>

        <?php if (empty($conference['participants'])) : ?>
            <tr class="text-muted">
                <td>
                    <?= lang('No one will participate or has participated', 'Niemand wird teilnehmen oder hat teilgenommen') ?>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ($conference['participants'] as $username) : ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">

                            <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                            <div class="">
                                <h5 class="my-0">
                                    <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                        <?= $DB->getNameFromId($username) ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>



<h4><?= lang('Interested persons', 'Interessierte Personen') ?>:</h4>
<div class="btn-toolbar">

    <?php if ($interest) { ?>
        <a class="btn active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
            <i class="ph ph-user-circle-minus"></i> <?= lang('Withdraw interest', 'Interesse zurückziehen') ?>
        </a>
    <?php } else { ?>
        <a class="btn" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
            <i class="ph ph-user-circle-plus"></i> <?= lang('Show interest', 'Interesse bekunden') ?>
        </a>
    <?php } ?>
</div>

<table class="table">
    <tbody>
        <?php if (empty($conference['interests'])) : ?>
            <tr class="text-muted">
                <td>
                    <?= lang('No one is currently interested', 'Keine Personen sind zurzeit interessiert') ?>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ($conference['interests'] as $username) : ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">

                            <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                            <div class="">
                                <h5 class="my-0">
                                    <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                        <?= $DB->getNameFromId($username) ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>



<h2><?= lang('Activities', 'Aktivitäten') ?></h2>
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

<?php if ($conference['created_by'] == $_SESSION['username']) { ?>
    <form action="<?= ROOTPATH ?>/crud/conferences/delete/<?= $conference['_id'] ?>" method="post">
        <div class="alert danger mt-20">
            <p>
                <?= lang('Do you want to delete this event?', 'Möchten Sie diese Event löschen?') ?>
                <?= lang('Please note: this cannot be undone.', 'Achtung: dies kann nicht rückgängig gemacht werden.') ?>
            </p>
            <button class="btn danger" type="submit"><?= lang('Delete', 'Löschen') ?></button>
        </div>
    </form>
<?php } ?>


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