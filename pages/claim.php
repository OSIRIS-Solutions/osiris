<?php
/**
 * Claim activities by author names
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /claim
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     AGPL-3.0
 */

$last = [trim($scientist['last'])];
$first = [trim($scientist['first'])];


$names = $scientist['names'] ?? [];
foreach ($names as $name) {
    $name = explode(',', $name);
    $last[] = trim($name[0]);
    $first[] = trim($name[1]);
}

$last = array_values(array_unique($last));
$first = array_values(array_unique($first));

$last = array_map(fn($n) => normalizer_normalize($n, Normalizer::FORM_C), $last);
$first = array_map(fn($n) => normalizer_normalize($n, Normalizer::FORM_C), $first);

$filter = ['authors' => ['$elemMatch' => ['user' => null, 'last' => ['$in' => $last], 'first' => ['$in' => $first]]]];
$options = ['collation' => ['locale' => 'en', 'strength' => 1]]; // case-insensitive

$activities = $osiris->activities->find($filter, $options)->toArray();
?>

<h1>
    <i class="ph-duotone ph-hand"></i>
    <?= lang('common.claim_activities') ?>
</h1>

<?= lang('activities.the_following_names_are_used_to_search_in_activities_where_your_user_accoun') ?>

<p>
    <b><?= lang('activities.last_names') ?>:</b>
    <?php foreach ($last as $l) { ?>
        <span class="badge primary"><?= $l ?></span>
    <?php } ?>

</p>
<p>
    <b><?= lang('activities.first_names') ?>:</b>
    <?php foreach ($first as $f) { ?>
        <span class="badge primary"><?= $f ?></span>
    <?php } ?>
</p>
<p>
    <?= lang('activities.update_your_names') ?>
    <a href="<?= ROOTPATH ?>/user/edit/<?= $scientist['username'] ?>" class="link"><?= lang('activities.here') ?></a>
</p>

<?php if (empty($activities)) { ?>
    <div class="alert danger mb-10 ">
        <?= lang('activities.no_activities_found') ?>
    </div>
    <a href="<?= ROOTPATH ?>/profile/<?= $scientist['username'] ?>" class="btn primary">
        <?= lang('activities.back_to_profile') ?>
    </a>
    <?php return; ?>

<?php } ?>

<form action="#" method="post">
    <input type="hidden" name="last" value="<?= implode(';', $last) ?>">
    <input type="hidden" name="first" value="<?= implode(';', $first) ?>">

    <table class="table mb-10">
        <thead>
            <th>
                <?= lang('common.activities') ?>
            </th>
            <th>
                <?= lang('activities.matched_author') ?>
            </th>
            <th>
                <?= lang('action.claim') ?>
                <div class="custom-checkbox">
                    <input type="checkbox" id="claim-all" onclick="$('.claim-checkbox').attr('checked', $(this).is(':checked'))">
                    <label for="claim-all" class="empty"></label>
                </div>
            </th>
        </thead>
        <tbody>
            <?php foreach ($activities as $activity) : ?>
                <tr>
                    <td>
                        <?= $activity['rendered']['web'] ?>
                    </td>
                    <td>
                        <?php
                        $authors = $activity['authors'];
                        $author = null;
                        foreach ($authors as $a) {
                            if (empty($a['user']) && in_array($a['last'], $last) && in_array($a['first'], $first)) {
                                $author = $a;
                                break;
                            }
                        }
                        if ($author) {
                            echo $author['last'] . ', ' . $author['first'];
                        } else {
                            echo lang('activities.no_matching_author_found');
                        }
                        ?>
                    </td>
                    <td class="unbreakable">
                        <!-- checkbox -->
                        <div class="custom-checkbox">
                            <input type="checkbox" name="activity[]" value="<?= $activity['_id'] ?>" id="claim-<?= $activity['_id'] ?>" class="claim-checkbox">
                            <label for="claim-<?= $activity['_id'] ?>"><?= lang('action.claim') ?></label>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="btn primary">
        <?= lang('activities.claim_selected_activities') ?>
    </button>
</form>