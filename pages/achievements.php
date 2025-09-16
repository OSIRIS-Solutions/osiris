<?php

/**
 * Page to show user achievements
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /achievements/<username>
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<div class="content mt-0">
    <h1>
        <i class="ph ph-trophy text-signal"></i>
        <?= lang('Achievements', 'Errungenschaften') ?>
    </h1>

    <?php
    // SassCompiler::run("scss/", "css/");
    if ($scientist['hide_achievements'] ?? false) { ?>
        <div class="alert signal">
            <?= lang('This person has disabled achievements in their profile.', 'Diese Person hat Errungenschaften im Profil ausgeschaltet.') ?>
        </div>

    <?php
        echo "</div>";
        include BASEPATH . "/footer.php";
        die;
    } ?>

    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/achievements.css?<?= filemtime(BASEPATH . '/css/achievements.css') ?>">

    <?php
    $Achievement = new Achievement($osiris);
    $Achievement->initUser($user);


    $Achievement->checkAchievements();
    $user_ac = $Achievement->userac;
    if ($user == $_SESSION['username'] && !empty($Achievement->new)) {
        echo '<div class="alert signal m-10">';
        echo '<h5 class="title font-size-16">' . lang('Congratulation, you achieved something new: ', 'Glückwunsch, du hast neue Errungenschaften erlangt:') . '</h5>';

        foreach ($Achievement->new as $i => $n) {
            $Achievement->snack($n);
        }

        echo '</div>';
        $Achievement->save();
    }
    $Achievement->userOrder();

    // dump($Achievement->userac, true);

    $firstname = $scientist['first'];

    $Achievement->widget('lg');
    ?>

</div>

<div class="row row-eq-spacing-sm">

    <?php foreach ($Achievement->achievements as $id => $ac) {
        $uac = $user_ac[$id] ?? [];
        // dump($ac);
        if (($ac['visible'] ?? true) === false && empty($uac)) {
            continue;
        }
        $user_descr = lang('<em>Unachieved.</em>', '<em>Noch nicht erreicht.</em>');
    ?>
        <div class="col-sm-6 col-xl-4">
            <div class="box mt-0" style="height: calc(100% - 2rem);">
                <div class="content row achievement max-<?= $ac['maxlvl'] ?> lvl<?= $uac['level'] ?? 0 ?>" id="<?= $id ?>">
                    <div class="col flex-grow-0 mr-20 text-center">
                        <div class="w-100">
                            <?php
                            include BASEPATH . "/img/achievements/ac_$ac[icon].svg";
                            ?>
                            <div class="level">
                                <?php foreach ($ac['levels'] ?? [] as $lvl) {
                                    $descr = $lvl[$Achievement->lang];
                                    if ($user == $_SESSION['username']) {
                                        $descr = $lvl[$Achievement->lang . '_single'] ?? $descr;
                                    } else {
                                        $descr = str_replace('*', $firstname, $descr);
                                    }
                                    if ($lvl['level'] == ($uac['level'] ?? 0)) {
                                        $user_descr = $descr;
                                    }
                                ?>
                                    <span data-toggle="tooltip" data-title="<?= $descr ?>"></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <h3 class="title"><?= $ac['title'][$Achievement->lang_g] ?></h3>
                        <h5>Level
                            <?php
                            $lvl = $uac['level'] ?? 0;
                            if ($lvl == $ac['maxlvl']) $lvl = "max";
                            echo $lvl;
                            ?>
                        </h5>
                        <div class="tile-content">
                            <small class="text-muted"><?= $uac['achieved'] ?? '-' ?></small>
                            <p>
                                <?= $user_descr ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php } ?>

</div>
<div class="row">

    <?php foreach ([] as $id => $ac) {
        $uac = $user_ac[$id] ?? [];
        // dump($ac);
        if (($ac['visible'] ?? true) === false && empty($uac)) {
            continue;
        }
        $user_descr = lang('<em>Unachieved.</em>', '<em>Noch nicht erreicht.</em>');
    ?>
        <div class="d-flex col flex-grow-0">
            <div class="tile w-200 achievement max-<?= $ac['maxlvl'] ?> lvl<?= $uac['level'] ?? 0 ?>" id="<?= $id ?>" style="max-width:25rem">
                <h3 class="title"><?= $ac['title'][$Achievement->lang_g] ?></h3>
                <h5>Level
                    <?php
                    $lvl = $uac['level'] ?? 0;
                    if ($lvl == $ac['maxlvl']) $lvl = "max";
                    echo $lvl;
                    ?>
                </h5>
                <div class="tile-content">
                    <small class="text-muted"><?= $uac['achieved'] ?? '-' ?></small>
                    <?php
                    include BASEPATH . "/img/achievements/ac_$ac[icon].svg";
                    ?>
                    <div class="level">
                        <?php foreach ($ac['levels'] ?? [] as $lvl) {
                            $descr = $lvl[$Achievement->lang];
                            if ($user == $_SESSION['username']) {
                                $descr = str_replace(lang('have', 'hat'), lang('has', 'hast'), $descr);
                                $descr = str_replace('*', 'Du', $descr);
                            } else {
                                $descr = str_replace('*', $firstname, $descr);
                            }
                            if ($lvl['level'] == ($uac['level'] ?? 0)) {
                                $user_descr = $descr;
                            }
                        ?>
                            <span data-toggle="tooltip" data-title="<?= $descr ?>"></span>
                        <?php } ?>
                    </div>

                    <p>
                        <?= $user_descr ?>
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>

</div>