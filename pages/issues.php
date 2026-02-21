<?php

/**
 * Page to show open issues
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /issues
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document($user);
$issues = $DB->getUserIssues($user);
?>

<style>
    .box:target {
        -moz-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
        -webkit-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
        box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
    }
</style>

<div class="modal" id="why-approval" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang(
                                    'Why do I have to confirm my authorships?',
                                    'Warum muss ich meine Autorenschaften bestätigen?'
                                ) ?></h5>
            <p>
                <?= lang('
                    Sometimes other scientists or members of the institute add scientific activities that you were also involved in. 
                    The system tries to assign them automatically, which is why they show up here in this list. 
                    However, a lot can go wrong with this. For reporting purposes, for example, it is not only important that the 
                    bibliographic data is correct, the users must also be correctly assigned. Therefore it is important, <b>if this 
                    is you at all</b> or maybe someone with a similar name, that your <b>name is spelled correctly</b> and 
                    that you were also <b>affiliated with the ' . $Settings->get('affiliation') . '</b>. 
                    ', '
                    Manchmal fügen andere Wissenschaftler:innen oder Mitglieder des Institutes wissenschaftliche Aktivitäten hinzu,
                    an denen du ebenfalls beteiligt warst. Das System versucht, diese automatisch zuzuordnen, weshalb sie hier 
                    in dieser Liste auftauchen. Allerdings kann dabei sehr viel schief gehen. Für die Berichterstattung ist es z.B. 
                    nicht nur wichtig, dass die bibliographischen Daten korrekt sind, die Nutzer müssen auch korrekt zugeordnet sein. 
                    Deshalb ist es wichtig, <b>ob du das überhaupt bist</b> (oder vielleicht jemand mit einem ähnlichen Namen), 
                    dass <b>dein Name korrekt geschrieben</b> ist und du außerdem <b>der ' . $Settings->get('affiliation') . ' zugehörig</b> bist. 
                ') ?>
            </p>
            <p>
                <?= lang('
                    <q><b>But I have already confirmed this activity once.</b></q><br>
                    That might be. 
                    Because as soon as an activity is edited, even if it is only that a document was deposited or a spelling mistake in the title was corrected, the confirmation of all authors is reset. 
                    This is to avoid that already confirmed activities are edited without your knowledge. 
                    ', '
                    <q><b>Ich habe diese Aktivität doch aber schon einmal bestätigt.</b></q><br>
                    Das kann sehr gut sein. 
                    Denn sobald eine Aktivität bearbeitet wird, und sei es nur, dass ein Dokument hinterlegt oder ein Rechtschreibfehler im Titel korrigiert wurde, wird die Bestätigung aller Autoren zurückgesetzt. 
                    Dadurch soll vermieden werden, dass ohne dein Wissen bereits bestätigte Aktivitäten bearbeitet werden. 
                ') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="why-epub" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('Why do I have to review <q>Online ahead of print</q> Articles?', 'Warum muss ich <q>Online ahead of print</q>-Artikel reviewen?') ?></h5>
            <p>
                <?= lang('
                    [Online ahead of print] means that the publication is already available online, but the actual publication in an issue is still pending. 
                    An example is the NAR database issue, where publications are already available online in September or October, although the 
                    issue is not published until the following January.  
                    ', '
                    [Online ahead of print] bedeutet, dass die Publikation bereits online verfügbar ist, 
                    die eigentliche Publikation in einem Issue jedoch noch aussteht. Als Beispiel kann man das NAR database issue nennen,
                    bei dem Publikationen bereits im September oder Oktober online verfügbar sind, obwohl das Issue erst im darauffolgenden Januar
                    erscheint.  
                ') ?>
            </p>
            <p>
                <?= lang('
                    <b>These publications cannot be included in the reports.</b>
                    They are included in OSIRIS in order not to lose sight of them and because they represent achievements already made.
                    But for this reason, it is regularly queried whether the publication has now been published. 
                    Because only then can it be taken into account in the reporting.
                    ', '
                    <b>Diese Publikationen können in den Berichterstattungen nicht berücksichtigt werden.</b>
                    Sie werden in OSIRIS aufgenommen, um sie nicht aus den Augen zu verlieren und weil sie bereits erbrachte Leistungen darstellen.
                    Doch aus diesem Grund wird regelmäßig abgefragt, ob die Publikation nun veröffentlicht wurde. Denn erst dann kann sie in der 
                    Berichterstattung berücksichtigt werden.
                ') ?>
            </p>
            <p>
                <?= lang('
                    <b>The bibliographic data must be checked again.</b> The check mark for <q>Online ahead of print</q> must be removed and the publication date is usually also adjusted. 
                    Furthermore, it also happens that something changes in the bibliographic data itself. Therefore, please check carefully if all data is correct.
                    ', '
                    <b>Die bibliographischen Daten müssen dazu erneut überprüft werden.</b> Dabei muss der Haken bei <q>Online ahead of print</q> entfernt werden und i.d.R. wird
                    auch das Veröffentlichungsdatum angepasst. 
                    Des Weiteren passiert es auch, dass sich an den bibliographischen Daten selbst noch etwas ändert. Deshalb überprüft bitte sorgfältig, 
                    ob alle Daten stimmen.
                ') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="why-status" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('Why do I have to review these activities?', 'Warum muss ich diese Aktivitäten überprüfen?') ?></h5>
            <p>
                <?= lang('
                    In order to ensure that the correct status and completion date is always indicated for activities with a status, OSIRIS will issue a warning 
                    if this work is still "in preparation" although the start date is in the past or "in progress" although the completion date is in the past.
                    <b>Please check if the work has already been completed.</b> 
                    If so, please enter if the work has been successfully completed or not and provide the correct completion date.
                    If the work is still "in preparation", please extend the period by entering a new expected completion date.
                    If the work is still "in progress", please extend the period by entering a new expected completion date.
                    OSIRIS will then ask you again in due course if the thesis has been successfully completed.
                    ', '
                   Um sicherzustellen, dass bei Aktivitäten mit einem Status immer der richtige Status und das richtige Fertigstellungsdatum angegeben wird, gibt OSIRIS eine Warnung aus
                    wenn diese Arbeit noch "in Vorbereitung" ist, obwohl das Startdatum in der Vergangenheit liegt oder "in Arbeit", obwohl das Fertigstellungsdatum in der Vergangenheit liegt.
                    <b>Bitte prüfen Sie, ob die Arbeit bereits abgeschlossen ist.</b>
                    Wenn ja, geben Sie bitte an, ob die Arbeiten erfolgreich abgeschlossen wurden oder nicht, und geben Sie das korrekte Abschlussdatum an.
                    Wenn die Arbeiten noch "in Vorbereitung" sind, verlängern Sie bitte die Frist, indem Sie ein neues voraussichtliches Fertigstellungsdatum angeben.
                    Wenn die Arbeit noch "in Arbeit" ist, verlängern Sie bitte die Frist, indem Sie ein neues voraussichtliches Fertigstellungsdatum eingeben.
                    OSIRIS wird Sie dann zu gegebener Zeit erneut fragen, ob die Arbeit erfolgreich abgeschlossen wurde.
                ') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>


<a target="_blank" href="https://wiki.osiris-app.de/users/issues/" class="btn tour float-right" id="">
    <i class="ph ph-lg ph-question mr-5"></i>
    <?= lang('Read the Docs', 'Zur Hilfeseite') ?>
</a>
<h1 class="mt-0">
    <i class="ph ph-duotone ph-warning"></i>
    <?= lang('Warnings', 'Warnungen') ?>
</h1>

<?php
$a = array_map(function ($a) {
    return empty($a) ? 0 : 1;
}, $issues);
if (array_sum($a) === 0) { ?>

    <div class="text-center">
        <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-tasks.png" alt="" class="sophie-img w-300">
        <h2 class="mt-0"><?= lang('No warnings', 'Keine Warnmeldungen') ?></h2>
        <p><?= lang('Here is currently nothing that requires your attention. Great work!', 'Hier gibt es momentan nichts, was deine Aufmerksamkeit erfordert. Großartige Arbeit!') ?></p>
    </div>
<?php
}
?>

<?php if ($Settings->featureEnabled('quality-workflow') && !empty($issues['rejected'])) { ?>
    <h4 class="mb-0">
        <?= lang('Please review the following activities that were rejected in the quality workflow:', 'Bitte überprüfe die folgenden Aktivitäten, die im Qualitäts-Workflow abgelehnt wurden:') ?>
    </h4>
    <p class="mt-0">
        <a href="<?= ROOTPATH ?>/docs/warnings#Überprüfung-abgelehnter-Aktivitäten"><?= lang('What does it mean?', 'Was bedeutet das?') ?></a>
    </p>

    <?php
    foreach ($issues['rejected'] as $doc) {
        $details = $doc['details'] ?? '';
        $comment = $details['comment'] ?? '';
        $doc = $DB->getActivity($doc['id']);
        $id = $doc['_id'];
        $type = $doc['type'];
    ?>
        <div id="tr-<?= $id ?>">
            <div class="box padded mt-0" id="<?= $id ?>">
                <p class="mt-0">
                    <b class="text-<?= $doc['type'] ?>">
                        <?= $doc['rendered']['icon'] ?>
                        <?= $doc['rendered']['subtype'] ?>
                    </b> <br>
                    <?= $doc['rendered']['web'] ?>
                    <hr>
                <blockquote class="alert danger without-icon">
                    <div class="title">
                        <?= lang('Reason for rejection', 'Ablehnungsgrund') ?>
                    </div>
                    <?= nl2br(e($comment)) ?>
                </blockquote>
                </p>

                <!-- reply and restart -->
                <div class="">
                    <a target="_self" href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn" data-toggle="tooltip" data-title="<?= lang('Edit activity', 'Aktivität bearbeiten') ?>">
                        <i class="ph ph-pencil-simple-line"></i>
                        <?= lang('Edit activity', 'Aktivität bearbeiten') ?>
                    </a>
                    <a target="_blank" href="<?= ROOTPATH ?>/activities/view/<?= $id ?>" class="btn" data-toggle="tooltip" data-title="<?= lang('View activity', 'Aktivität ansehen') ?>">
                        <i class="ph ph-arrow-fat-line-right"></i>
                        <?= lang('View activity', 'Aktivität ansehen') ?>
                    </a>
                    <button class="btn" data-toggle="tooltip" data-title="<?= lang('Reply', 'Antworten') ?>" onclick="$(this).next().toggle()">
                        <i class="ph ph-chat-dots"></i>
                        <?= lang('Reply', 'Antworten') ?>
                    </button>
                    <div class="mt-10" style="display:none">
                        <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-reply/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <textarea name="comment" class="form-control small" rows="3" placeholder="<?= lang('Your reply to the reviewer', 'Deine Antwort an die Prüfer:in') ?>"></textarea>
                            <button class="btn small success mt-5" type="submit"><?= lang('Send reply', 'Antwort senden') ?></button>
                            <button class="btn small mt-5" type="button" onclick="$(this).parent().hide()"><?= lang('Cancel', 'Abbrechen') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($issues['approval'])) { ?>
        <h4 class="mb-0">
            <?= lang(
                'Please review the following authorships:',
                'Bitte überprüfe die folgenden Autorenschaften:'
            ) ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#Überprüfung-der-autorenschaft-nötig"><?= lang('What does it mean?', 'Was bedeutet das?') ?></a>
        </p>

        <div class="dropdown">
            <button class="btn mb-10 text-success" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-check"></i>
                <?= lang('Approve all', 'Alle bestätigen') ?>
            </button>
            <div class="dropdown-menu w-300" aria-labelledby="dropdown-1">
                <div class="content">
                    <form action="<?= ROOTPATH ?>/crud/activities/approve-all" method="post">
                        <input type="hidden" name="user" value="<?= $user ?>">
                        <?= lang(
                            'I confirm that I am the author of <b>all</b> of the following publications and that my affiliation has always been the ' . $Settings->get('affiliation') . '.',
                            'Ich bestätige, dass ich Autor:in <b>aller</b> folgenden Publikationen bin und meine Affiliation dabei immer die ' . $Settings->get('affiliation') . ' war.'
                        ) ?>
                        <button class="btn block success" type="submit"><?= lang('Approve all', 'Alle bestätigen') ?></button>
                    </form>

                </div>
            </div>
        </div>


        <?php
        include_once BASEPATH . '/php/Modules.php';
        $Modules = new Modules();
        foreach ($issues['approval'] as $doc) {
            $doc = $DB->getActivity($doc);

            $id = $doc['_id'];
            $type = $doc['type'];
        ?>

            <div id="tr-<?= $id ?>">

                <div class="box mt-0" id="<?= $id ?>">
                    <div class="row py-10 px-20">
                        <div class="col-md-6">
                            <p class="mt-0">
                                <b class="text-<?= $doc['type'] ?>">
                                    <?= $doc['rendered']['icon'] ?>
                                    <?= $doc['rendered']['subtype'] ?>
                                </b> <br>
                                <?= $doc['rendered']['web'] ?>
                            </p>
                            <div class='' id="approve-<?= $id ?>">
                                <?php if (isset($updated_by) && !empty($updated_by)) { ?>
                                    <?= lang('Please confirm (possibly again) that you are the author and all details are correct: ', 'Bitte bestätige (evtl. erneut), dass du Autor:in bist und alle Angaben korrekt sind:') ?>
                                <?php } else { ?>
                                    <?= lang('Is this your activity?', 'Ist dies deine Aktivität?') ?>
                                <?php } ?>
                                <br>

                                <div class="btn-group mr-10">
                                    <button class="btn small text-success" onclick="_approve('<?= $id ?>', 1)" data-toggle="tooltip" data-title="<?= lang('Yes, and I was affiliated to the' . $Settings->get('affiliation'), 'Ja, und ich war affiliiert mit ' . $Settings->get('affiliation')) ?>">
                                        <i class="ph ph-check ph-fw"></i>
                                    </button>
                                    <button class="btn small text-signal" onclick="_approve('<?= $id ?>', 2)" data-toggle="tooltip" data-title="<?= lang('Yes, but I was not affiliated to the ' . $Settings->get('affiliation'), 'Ja, aber ich war nicht affiliiert mit ' . $Settings->get('affiliation')) ?>">
                                        <i class="ph ph-push-pin-slash ph-fw"></i>
                                    </button>
                                    <button class="btn small text-danger" onclick="_approve('<?= $id ?>', 3)" data-toggle="tooltip" data-title="<?= lang('No, this is not me', 'Nein, das bin ich nicht') ?>">
                                        <i class="ph ph-x ph-fw"></i>
                                    </button>
                                </div>

                                <?php if (!($doc['locked'] ?? false)) { ?>
                                    <a target="_self" href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn small text-secondary" data-toggle="tooltip" data-title="<?= lang('Edit activity', 'Aktivität bearbeiten') ?>">
                                        <i class="ph ph-pencil-simple-line"></i>
                                    </a>
                                <?php } ?>
                                <a target="_blank" href="<?= ROOTPATH ?>/activities/view/<?= $id ?>" class="btn small text-secondary" data-toggle="tooltip" data-title="<?= lang('View activity', 'Aktivität ansehen') ?>">
                                    <i class="ph ph-arrow-fat-line-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <?php
                            if (isset($doc['history']) && !empty($doc['history'])) {
                                $hist = $doc['history'];
                                // get last element of history
                                $h = $hist[count($hist) - 1];
                            ?>
                                <span class="badge primary float-md-right"><?= date('d.m.Y', strtotime($h['date'])) ?></span>
                                <b class="d-block">
                                    <?php if ($h['type'] == 'created') {
                                        echo lang('Created by ', 'Erstellt von ');
                                    } else if ($h['type'] == 'edited') {
                                        echo lang('Edited by ', 'Bearbeitet von ');
                                    } else if ($h['type'] == 'imported') {
                                        echo lang('Imported by ', 'Importiert von ');
                                    } else {
                                        echo $h['type'] . lang(' by ', ' von ');
                                    }
                                    if (isset($h['user']) && !empty($h['user'])) {
                                        echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                                    } else {
                                        echo "System";
                                    }
                                    ?>
                                </b>

                                <?php
                                if (isset($h['comment']) && !empty($h['comment'])) { ?>
                                    <blockquote class="alert signal without-icon">
                                        <div class="title">
                                            <?= lang('Comment', 'Kommentar') ?>
                                        </div>
                                        <?= $h['comment'] ?>
                                    </blockquote>
                            <?php
                                }
                                // dump($h, true);

                                if (isset($h['changes']) && !empty($h['changes'])) {
                                    echo '<small class="font-weight-bold mt-10">' .
                                        lang('Changes to the activity:', 'Änderungen an der Aktivität:') .
                                        '</small>';
                                    echo '<table class="table simple w-auto small border px-10">';
                                    foreach ($h['changes'] as $key => $change) {
                                        $before = $change['before'] ?? '<em>empty</em>';
                                        $after = $change['after'] ?? '<em>empty</em>';
                                        if ($before == $after) continue;
                                        if (empty($before)) $before = '<em>empty</em>';
                                        if (empty($after)) $after = '<em>empty</em>';
                                        echo '<tr>
                                        <td class="pl-0">
                                            <span class="key">' . $Modules->get_name($key) . '</span> 
                                            <span class="del">' . $before . '</span>
                                            <i class="ph ph-arrow-right mx-10"></i>
                                            <span class="ins">' . $after . '</span>
                                        </td>
                                    </tr>';
                                    }
                                    echo '</table>';
                                } else if (isset($h['data']) && !empty($h['data'])) {
                                    echo '<a class="font-weight-bold mt-10"  onclick="$(this).next().fadeToggle()">' .
                                        '<i class="ph ph-caret-down"></i> ' .
                                        lang('Status at this time point', 'Status zu diesem Zeitpunkt') .
                                        '</a>';

                                    echo '<table class="table simple w-auto small border px-10" style="display:none";>';
                                    foreach ($h['data'] as $key => $datum) {
                                        echo '<tr>
                                        <td class="pl-0" style="font-size: .8em;">
                                            <span class="key">' . $Modules->get_name($key) . '</span> 
                                            ' . $datum . ' 
                                        </td>
                                    </tr>';
                                    }
                                    echo '</table>';
                                } else if ($h['type'] == 'edited') {
                                    echo lang('No changes tracked.', 'Es wurden keine Änderungen verfolgt.');
                                }
                            } else {
                                echo lang('No history available.', 'Keine Historie verfügbar.');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($issues['epub'])) { ?>
        <h4 class="mb-0">
            <?= lang(
                'Please review the following <q>Online ahead of print</q> articles:',
                'Bitte überprüfe die folgenden <q>Online ahead of print</q>-Artikel:'
            ) ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#online-ahead-of-print"><?= lang('What does it mean?', 'Was bedeutet das?') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['epub'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
                $type = $doc['type'];
            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">
                            <?= lang(
                                'This publication is marked as <q>Online ahead of print</q>. Is it still not officially published?',
                                'Diese Aktivität ist markiert als <q>Online ahead of print</q>. Ist sie noch nicht offiziell publiziert?'
                            ) ?>
                            <br>
                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                <input type="hidden" name="values[epub-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                <button class="btn small">
                                    <i class="ph ph-check"></i>
                                    <?= lang('Yes, still <q>Online ahead of print</q> (ask again later).', 'Ja, noch immer <q>Online ahead of print</q> (frag später noch mal).') ?>
                                </button>
                            </form>


                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>?epub=true" class="btn small">
                                <?= lang('No longer <q>Online ahead of print</q> (Review)', 'Nicht länger <q>Online ahead of print</q> (Review)') ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if (!empty($issues['status'])) { ?>
        <h4 class="mb-0">
            <?= lang(
                'Please review the status of the following activities:',
                'Bitte überprüfe den Status der folgenden Aktivitäten:'
            ) ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#why-status"><?= lang('What does it mean?', 'Was bedeutet das?') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['status'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
                $status = $doc['status'];


            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">

                            <?php if ($doc['status'] == 'in progress') { ?>
                                <?= lang(
                                    "The activity has ended, but the status is still <b>in progress</b>. Please confirm if the work has been successfully completed or not or extend the time frame.",
                                    "Die Aktivität ist beendet, aber der Status ist noch <b>in Arbeit</b>. Bitte bestätige, ob die Arbeit erfolgreich abgeschlossen wurde oder nicht, oder verlängere den Zeitraum."
                                )  ?>
                            <?php } else { ?>
                                <?= lang(
                                    "The activity has officially started, but the status is still <b>in preparation</b>. Please change the status or move the time frame.",
                                    "Die Aktivität hat offiziell begonnen, aber der Status ist noch <b>in Vorbereitung</b>. Bitte ändere den Status oder verschiebe den Zeitraum."
                                )  ?>
                            <?php } ?>
                            <br>
                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="form-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                                <label class="required" for="end"><?= lang('Ended at / Extend until', 'Geendet am / Verlängern bis') ?>:</label>
                                <input type="date" class="form-control w-200" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? '') ?>" required>
                                <div>

                                    <div class="custom-radio d-inline-block">
                                        <input type="radio" name="values[status]" id="status-preparation" value="preparation" checked="checked" value="preparation" <?= $status == 'preparation' ? 'checked' : '' ?>>
                                        <label for="status-preparation"><?= lang('In preparation', 'In Vorbereitung') ?></label>
                                    </div>

                                    <div class="custom-radio d-inline">
                                        <input type="radio" name="values[status]" id="status-in-progress-<?= $id ?>" value="in progress" <?= $status == 'in progress' ? 'checked' : '' ?>>
                                        <label for="status-in-progress-<?= $id ?>"><?= lang('In progress', 'In Arbeit') ?></label>
                                    </div>

                                    <div class="custom-radio d-inline">
                                        <input type="radio" name="values[status]" id="status-completed-<?= $id ?>" value="completed" <?= $status == 'completed' ? 'checked' : '' ?>>
                                        <label for="status-completed-<?= $id ?>"><?= lang('Completed', 'Abgeschlossen') ?></label>
                                    </div>

                                    <div class="custom-radio mr-10 d-inline">
                                        <input type="radio" name="values[status]" id="status-aborted-<?= $id ?>" value="aborted" <?= $status == 'aborted' ? 'checked' : '' ?>>
                                        <label for="status-aborted-<?= $id ?>"><?= lang('Aborted', 'Abgebrochen') ?></label>
                                    </div>
                                </div>
                                <button class="btn" type="submit"><?= lang('Submit', 'Bestätigen') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>



    <?php if (!empty($issues['openend'])) { ?>
        <h4 class="mb-0">
            <?= lang(
                'Do you still work on the following activities?',
                'Arbeitest du noch immer an den folgenden Aktivitäten?'
            ) ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#open-end"><?= lang('What does it mean?', 'Was bedeutet das?') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['openend'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">

                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                <input type="hidden" name="values[end-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                <button class="btn small text-success">
                                    <i class="ph ph-check"></i>
                                    <?= lang('Yes, still running', 'Ja, noch laufend') ?>
                                </button>
                            </form>

                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn small text-danger">
                                <i class="ph ph-x"></i>
                                <?= lang('No (Edit)', 'Nein (Bearbeiten)') ?>
                            </a>

                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if ($Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.edit-own')) { ?>

        <?php if (!empty($issues['project-open']) || !empty($issues['project-end'])) {
            $projects = array_merge($issues['project-open'] ?? [], $issues['project-end'] ?? [])
        ?>
            <h4 class="">
                <?= lang(
                    'Please have a look at the following projects:',
                    'Bitte schau dir die folgenden Projekte an:'
                ) ?>
            </h4>

            <table class="table">
                <?php
                if (isset($issues['project-open']))
                    foreach ($issues['project-open'] as $id) {
                        $doc = $osiris->projects->findOne(['_id' => DB::to_ObjectID($id)]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('The project', 'Das Projekt') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('still has the status <q>applied</q>. Is this correct? ', 'hat noch immer den Status <q>beantragt</q>. Ist das noch immer so?') ?>
                            <div class='' id="approve-<?= $id ?>">

                                <form action="<?= ROOTPATH ?>/crud/projects/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                    <input type="hidden" name="values[end-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                    <button class="btn small text-success">
                                        <i class="ph ph-check"></i>
                                        <?= lang('Yes, ask again later', 'Ja, frag später erneut') ?>
                                    </button>
                                </form>

                                <a href="<?= ROOTPATH ?>/projects/edit/<?= $id ?>" class="btn small text-danger">
                                    <i class="ph ph-edit"></i>
                                    <?= lang('No (Edit)', 'Nein (Bearbeiten)') ?>
                                </a>

                            </div>
                        </td>
                    </tr>
                <?php } ?>

                <?php
                if (isset($issues['project-end']))
                    foreach ($issues['project-end'] as $id) {
                        $doc = $osiris->projects->findOne(['_id' => DB::to_ObjectID($id)]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('The project', 'Das Projekt') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('has ended. You can either prolong it or end it:', 'ist zu Ende. Du kannst es entweder verlängern oder als beendet markieren:') ?>
                            <div class='' id="approve-<?= $id ?>">


                                <form action="<?= ROOTPATH ?>/crud/projects/update/<?= $id ?>" method="post" class="form-inline mt-5">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                                    <label class="required" for="end"><?= lang('Ended at / Extend until', 'Geendet am / Verlängern bis') ?>:</label>
                                    <input type="date" class="form-control w-200" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? '') ?>" required>
                                    <div>
                                        <select class="form-control" id="status-<?= $id ?>" name="values[status]" required>
                                            <option value="applied"><?= lang('applied', 'beantragt') ?></option>
                                            <option value="approved" selected><?= lang('approved', 'bewilligt') ?></option>
                                            <option value="rejected"><?= lang('rejected', 'abgelehnt') ?></option>
                                            <option value="finished"><?= lang('finished', 'abgeschlossen') ?></option>
                                        </select>
                                    </div>
                                    <button class="btn ml-10" type="submit"><?= lang('Submit', 'Bestätigen') ?></button>
                                </form>

                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>

        <?php } ?>



        <?php
        if (isset($issues['infrastructure'])) { ?>

            <h4 class="">
                <?= lang(
                    'Please have a look at the following infrastructures:',
                    'Bitte schau dir die folgenden Infrastrukturen an:'
                ) ?>
            </h4>

            <table class="table">
                <?php

                foreach ($issues['infrastructure'] as $id => $timepoint) {
                    $doc = $osiris->infrastructures->findOne(['id' => $id]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('Please update the statistics of ', 'Bitte aktualisiere die Statistiken von ') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('from ', 'von ') ?>
                            <b><?= $timepoint ?></b>
                            <br>
                            <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $id ?>?edit-stats=<?= $timepoint ?>#statistics" target="_blank" rel="noopener noreferrer" class="btn small primary">
                                <i class="ph ph-calendar-plus"></i>
                                <?= lang('Update now', 'Jetzt aktualisieren') ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

        <?php } ?>
    <?php } ?>

    <?php if (isset($issues['nagoya'])) { ?>
        <h4 class="">
            <?= lang(
                'Please review the following Nagoya Protocol submissions:',
                'Bitte überprüfe die folgenden Nagoya-Protokoll-Einreichungen:'
            ) ?>
        </h4>

        <table class="table">
            <?php
            foreach ($issues['nagoya'] as $project_id) {
                $project = $osiris->proposals->findOne(['_id' => DB::to_ObjectID($project_id)]);
            ?>
                <tr id="tr-<?= $project_id ?>">
                    <td>
                        <?= lang('The Nagoya Protocol compliance for project', 'Die Nagoya-Protokoll-Compliance für das Projekt') ?>
                        <b><?= $project['name'] ?></b>
                        <?= lang('requires your input.', 'benötigt deine Eingabe.') ?><br>
                        <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $project_id ?>" class="btn small primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('Provide input now', 'Jetzt Eingabe machen') ?>
                        </a>
                        <a href="<?= ROOTPATH ?>/projects/view/<?= $project_id ?>" class="btn small">
                            <i class="ph ph-arrow-fat-line-right"></i>
                            <?= lang('View project', 'Projekt ansehen') ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>