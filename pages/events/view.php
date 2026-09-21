<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$today = date('Y-m-d');
$start = $conference['start'];
$end = $conference['end'];

$is_today = ($today == $start && $today == $end);
$in_past = $end < $today;

$days = false;
if ($is_today) {
    $days = lang('common.today');
} elseif (!$in_past) {
    $days = ceil((strtotime($start) - time()) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? lang('events.currently_ongoing') : 'in ' . $days . ' ' . lang('common.days_view');
} elseif ($in_past) {
    $days = ceil((time() - strtotime($end)) / 86400);
    $days = $days > 0 ? $days : 0;
    $days = $days == 0 ? lang('events.until_today') : lang('events.ended') . ' ' . $days . ' ' . lang('events.days_ago');
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
            <?= lang('events.edit_event') ?>
        </a>

        <div class="dropdown">
            <button class="btn text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-trash"></i> Delete <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                <form action="<?= ROOTPATH ?>/crud/conferences/delete/<?= $conference['_id'] ?>" method="post" class="content">
                    <?= lang('events.do_you_want_to_delete_this_event') ?>
                    <?= lang('common.please_note_this_cannot_be_undone') ?>
                    <button class="btn danger" type="submit"><?= lang('action.delete') ?></button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>


<div class="row row-eq-spacing">

    <div class="col-md-6 col-lg-4">

        <table class="table">
            <tr>
                <td colspan="2">
                    <span class="key"><?= lang('common.location') ?></span>
                    <?= $conference['location'] ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="key"><?= lang('common.country') ?></span>
                    <?= $DB->getCountry($conference['country'] ?? '', lang('common.field_name_language')) ?>
                </td>
            </tr>
            <?php if (isset($conference['type'])) { ?>
                <tr>
                    <td colspan="2">
                        <span class="key"><?= lang('common.type') ?></span>
                        <?= $Vocabulary->getValue('event-type', $conference['type']) ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (isset($conference['internal_id'])) { ?>
                <tr>
                    <td colspan="2">
                        <span class="key"><?= lang('common.internal_id') ?></span>
                        <?= $conference['internal_id'] ?>
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td>
                    <span class="key"><?= lang('common.start_view') ?></span>
                    <?= format_date($conference['start']) ?><br>
                    <b class="badge <?= ($in_past ? 'danger' : 'success') ?>"><?= $days ?></b>
                </td>
                <td>
                    <span class="key"><?= lang('common.end') ?></span>
                    <?= format_date($conference['end']) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="key"><?= lang('common.url') ?></span>
                    <?php if (!empty($conference['url'])) {
                        $short_url = str_replace('https://', '', $conference['url']);
                        if (strlen($short_url) > 50) {
                            $short_url = substr($short_url, 0, 50) . '...';
                        }
                    ?>
                        <a href="<?= $conference['url'] ?>" target="_blank"><i class="ph ph-link"></i> <?= $short_url ?></a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
                <?php if ($Settings->featureEnabled('portal')) {
                    $public = $conference['public'] ?? false;
                ?>
                    <tr>
                        <td colspan="2">
                            <span class="key"><?= lang('common.portfolio_visibility') ?>: </span>
                            <?php if ($public) { ?>
                                <span class="badge success">
                                    <i class="ph ph-globe m-0"></i> <?= lang('common.shown') ?>
                                </span>
                            <?php } else { ?>
                                <span class="badge signal">
                                    <i class="ph ph-globe-x m-0"></i> <?= lang('common.not_shown') ?>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <tr>
                    <td colspan="2">
                        <span class="key"><?= $Settings->tagLabel() ?></span>
                        <?= $Settings->printTags($conference['tags'] ?? [], 'conferences') ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!$in_past) { ?>
                <tr>
                    <td colspan="2">
                        <a class="btn small" href="<?= ROOTPATH ?>/conference/ics/<?= $conference['_id'] ?>">
                            <i class="ph ph-calendar-plus"></i>
                            <?= lang('common.add_to_calendar') ?>
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
            <h5 class="mt-0"><?= lang('events.participating_persons') ?>:</h5>
            <?php if ($participate) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-minus"></i> <?= lang('events.withdraw_participation') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'participants')">
                    <i class="ph ph-user-circle-plus"></i> <?= lang('events.participate') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['participants'])) : ?>
            <div class="box padded">
                <?= lang('events.no_one_will_participate_or_has_participated') ?>
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
            <h5><?= lang('events.interested_persons') ?>:</h5>
            <?php if ($interest) { ?>
                <a class="btn small active primary" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-minus"></i> <?= lang('events.withdraw_interest') ?>
                </a>
            <?php } else { ?>
                <a class="btn small" onclick="conferenceToggle(this, '<?= $conference['_id'] ?>', 'interests')">
                    <i class="ph ph-user-circle-plus"></i> <?= lang('events.show_interest') ?>
                </a>
            <?php } ?>
        </div>

        <?php if (empty($conference['interests'])) : ?>
            <div class="box padded">
                <?= lang('events.no_one_is_currently_interested') ?>
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


<h2><?= lang('common.activities') ?></h2>
<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/add-activity?type=lecture&conference=<?= $id ?>">
        <i class="ph ph-plus-circle"></i>
        <?= lang('common.add_contribution') ?>
    </a>
</div>

<?php if (empty($activities)) : ?>
    <div class="alert muted">
        <?= lang('events.no_activities_connected') ?>
    </div>
<?php else : ?>

    <table class="table" id="result-table">
        <thead>
            <tr>
                <th><?= lang('common.type') ?></th>
                <th><?= lang('common.activity') ?></th>
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