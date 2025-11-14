<?php
/**
 * Claim activities by author names
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /claim
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
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
    <?= lang('Claim activities', 'Aktivitäten beanspruchen') ?>
</h1>

<?= lang(
    'The following names are used to search in activities, where your user account is not connected yet.',
    'Die folgenden Namen werden für die Suche in Aktivitäten verwendet, mit denen Ihr Benutzerkonto noch nicht verbunden ist'
) ?>

<p>
    <b><?= lang('Last names', 'Nachnamen') ?>:</b>
    <?php foreach ($last as $l) { ?>
        <span class="badge primary"><?= $l ?></span>
    <?php } ?>

</p>
<p>
    <b><?= lang('First names', 'Vornamen') ?>:</b>
    <?php foreach ($first as $f) { ?>
        <span class="badge primary"><?= $f ?></span>
    <?php } ?>
</p>
<p>
    <?= lang('Update your names ', 'Aktualisiere deine Namen ') ?>
    <a href="<?= ROOTPATH ?>/user/edit/<?= $scientist['username'] ?>" class="link"><?= lang('here', 'hier') ?></a>
</p>

<?php if (empty($activities)) { ?>
    <div class="alert danger mb-10 ">
        <?= lang('No activities found', 'Keine Aktivitäten gefunden') ?>
    </div>
    <a href="<?= ROOTPATH ?>/profile/<?= $scientist['username'] ?>" class="btn primary">
        <?= lang('Back to profile', 'Zurück zum Profil') ?>
    </a>
    <?php return; ?>

<?php } ?>

<form action="#" method="post">
    <input type="hidden" name="last" value="<?= implode(';', $last) ?>">
    <input type="hidden" name="first" value="<?= implode(';', $first) ?>">

    <table class="table mb-10">
        <thead>
            <th>
                <?= lang('Activities', 'Aktivitäten') ?>
            </th>
            <th>
                <?= lang('Matched author', 'Übereinstimmende:r Autor:in') ?>
            </th>
            <th>
                <?= lang('Claim', 'Beanspruchen') ?>
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
                            echo lang('No matching author found', 'Kein:e passende:r Autor:in gefunden');
                        }
                        ?>
                    </td>
                    <td class="unbreakable">
                        <!-- checkbox -->
                        <div class="custom-checkbox">
                            <input type="checkbox" name="activity[]" value="<?= $activity['_id'] ?>" id="claim-<?= $activity['_id'] ?>" class="claim-checkbox">
                            <label for="claim-<?= $activity['_id'] ?>"><?= lang('Claim', 'Beanspruchen') ?></label>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="btn primary">
        <?= lang('Claim selected activities', 'Ausgewählte Aktivitäten beanspruchen') ?>
    </button>
</form>