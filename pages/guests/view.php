<?php

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

require_once BASEPATH . "/vendor/autoload.php";
?>


<h1>
    <?= lang('Guest ', 'Gast') ?>
    <?php if (!empty($form['guest']) && !empty($form['guest']['last'])) { ?>
        <span class="text-osiris">
            <?= $form['guest']['academic_title'] ?? '-' ?>
            <?= $form['guest']['first'] ?? '-' ?>
            <?= $form['guest']['last'] ?? '-' ?>
        </span>
    <?php } else { ?>
        #<?= $id ?>
    <?php } ?>
</h1>

<?php if ($form['cancelled'] ?? false) { ?>
    <div class="alert danger mb-20">
        <h4 class="title">
            <?= lang('This guest has been cancelled', 'Dieser Gast wurde abgesagt') ?>
        </h4>
        <p>
            <?= lang('Cancelled by', 'Abgesagt durch') ?>
            <a href="<?= ROOTPATH ?>/profile/<?= $form['cancelled_by'] ?? '' ?>">
                <?= $DB->getNameFromId($form['cancelled_by'] ?? '') ?>
            </a>
            <?= lang('on', 'am') ?>
            <?= format_date($form['cancelled_date' ?? '']) ?>.
        </p>
    </div>
<?php } ?>

<div class="d-flex">

    <div class="mr-20 badge bg-white">
        <small><?= lang('Responsible', 'Verantwortlich') ?>: </small>
        <br />
        <b><a href="<?= ROOTPATH ?>/profile/<?= $form['supervisor']['user'] ?? '' ?>">
                <?= $form['supervisor']['name'] ?? '-' ?>
            </a></b>
    </div>
    <div class="mr-20 badge bg-white">
        <small><?= lang('Time frame of the stay', 'Dauer des Aufenthalts') ?>: </small>
        <br />
        <b><?= fromToDate($form['start'], $form['end'] ?? null) ?></b>
    </div>

    <div class="mr-20 badge bg-white">
        <small><?= lang('Cancel guest', 'Gast absagen') ?> </small>
        <br />
        <?php if ($form['cancelled'] ?? false) { ?>
            <form action="<?= ROOTPATH ?>/guests/cancel/<?= $id ?>" method="post">
                <input type="hidden" name="cancel" value="0">
                <button class="btn success small" type="submit">
                    <i class="ph ph-calendar-check"></i> <?= lang('Revoke', 'Zurückziehen') ?>
                </button>
            </form>
        <?php } else { ?>
            <form action="<?= ROOTPATH ?>/guests/cancel/<?= $id ?>" method="post">
                <input type="hidden" name="cancel" value="1">
                <button class="btn danger small" type="submit">
                    <i class="ph ph-calendar-x"></i> <?= lang('Cancel', 'Absagen') ?>
                </button>
            </form>
        <?php } ?>
    </div>

    <!-- Add possibility to prolong period -->
    <div class="mr-20 badge bg-white">
        <small><?= lang('Prolong stay', 'Aufenthalt verlängern') ?> </small>
        <br />
        <!--  dropdown -->
        <div class="dropdown">
            <button class="btn secondary small dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-calendar-plus"></i> <?= lang('Prolong', 'Verlängern') ?>
            </button>
            <div class="dropdown-menu p-10" aria-labelledby="dropdownMenuButton">
                <form action="<?= ROOTPATH ?>/guests/update/<?= $id ?>" method="post">
                <div class="form-group">
                <label for="end"><?=lang('End date', 'Neues End-Datum')?></label>
                    <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($form['end'] ?? null) ?>" required>
                </div>
                    <button class="btn secondary small" type="submit">
                        <i class="ph ph-calendar-plus"></i> <?= lang('Prolong', 'Verlängern') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>


</div>


<div class="row row-eq-spacing mt-0">

    <div class="col-md-6">
        <?php if ($Settings->featureEnabled('guest-forms')) {


            $guest_server = $Settings->get('guest-forms-server');
            $url = $guest_server . "/g/" . $id;
            $options = new QROptions([]);

            try {
                $qr = (new QRCode($options))->render($url);
            } catch (Throwable $e) {
                exit($e->getMessage());
            }
        ?>


            <?php if ($form['legal']['general'] ?? false) { ?>
                <div class="alert success my-20">
                    <h4 class="title">
                        Status
                    </h4>
                    Der Gast ist angemeldet und hat alle Belehrungen zur Kenntnis genommen.
                </div>
            <?php } else { ?>
                <div class="box danger">
                    <div class="content">
                        <h4 class="title">
                            Status
                        </h4>
                        <p class="text-danger">
                            Der Nutzer ist noch nicht vollständig angelegt. Bitte lassen Sie das folgende Formular ausfüllen, um den Vorgang abzuschließen:
                        </p>
                        <img src="<?= $qr ?>" alt="<?= $id ?>" class="d-block">
                        <a href="<?= $url ?>" target="_blank" rel="noopener noreferrer" class="link">
                            <?= $url ?>
                        </a>

                        <form action="<?= ROOTPATH ?>/guests/synchronize/<?= $id ?>" method="post">
                            <button class="btn danger" type="submit">
                                <?= lang('Refresh', 'Aktualisieren') ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php } ?>

        <?php } ?>



        <div class="box">

            <div class="content">
                <h4 class="title mb-0">
                    Formulardaten
                </h4>
            </div>
            <table class="table simple">

                <tr>
                    <th class="w-300"><?= lang('Title', 'Titel') ?></th>
                    <td>
                        <?= $form['guest']['academic_title'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('First name', 'Vorname') ?></th>
                    <td>
                        <?= $form['guest']['first'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Last name', 'Nachname') ?></th>
                    <td>
                        <?= $form['guest']['last'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Date of Birth', 'Geburtstag') ?></th>
                    <td>
                        <?= $form['guest']['birthday'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Telephone', 'Telefon') ?></th>
                    <td>
                        <?= $form['guest']['phone'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('E-Mail', 'E-Mail') ?></th>
                    <td>
                        <?= $form['guest']['mail'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Accomodation during stay', 'Unterkunftsadresse während des Aufenthalts') ?></th>
                    <td>
                        <?= $form['guest']['accomodation'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Company / University', 'Firma / Universität / Schule') ?></th>
                    <td>
                        <?= $form['affiliation']['name'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Address', 'Anschrift') ?></th>
                    <td>
                        <?= $form['affiliation']['address'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Country', 'Land') ?></th>
                    <td>
                        <?= $form['affiliation']['country'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Time frame of the stay', 'Dauer des Aufenthalts') ?></th>
                    <td>
                        <?= format_date($form['start'] ?? null) ?>
                        <?= lang('to', 'bis') ?>
                        <?= format_date($form['end'] ?? null) ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Responsible Scientist', 'Verantwortliche/r Wissenschaftler/in') ?></th>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= $form['supervisor']['user'] ?? '' ?>">
                            <?= $form['supervisor']['name'] ?? '-' ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Title', 'Titel') ?></th>
                    <td>
                        <?= $form['title'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Purpose of stay', 'Zweck des Aufenthalts') ?></th>
                    <td>
                        <?= $form['category'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('The visit is financed by', 'Die Finanzierung erfolgt') ?></th>
                    <td>
                        <?= $form['payment'] ?? '-' ?>
                        <?php if (isset($form['payment_comment'])) { ?>
                            <br>
                            <small>Begründung: <q><?= $form['payment_comment'] ?></q></small>
                        <?php } ?>
                        
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('General Agreement', 'Generelle Zustimmung') ?></th>
                    <td>
                        <?= bool_icon($form['legal']['general'] ?? false) ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Legal instructions for data protection', 'Rechtsbelehrung zum Datenschutz') ?></th>
                    <td>
                        <?= bool_icon($form['legal']['data_security'] ?? false) ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Legal instructions for data security', 'Rechtsbelehrung zur Datensicherheit') ?></th>
                    <td>
                        <?= bool_icon($form['legal']['data_protection'] ?? false) ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-300"><?= lang('Safety-Instructions for Short-Time Guests', 'Sicherheitsbelehrung für Kurzzeitgäste') ?></th>
                    <td>
                        <?= bool_icon($form['legal']['safety_instruction'] ?? false) ?>
                    </td>
                </tr>


            </table>

            <div class="content">
                <a href="<?= ROOTPATH ?>/guests/edit/<?= $id ?>" class="btn danger"><?= lang('Edit information', 'Formular bearbeiten') ?></a>
            </div>
        </div>


    </div>


    <?php if ($Settings->hasPermission('guests.see.documents') || $Settings->hasPermission('guests.edit.documents')) { ?>

        
    <?php if ($Settings->hasPermission('guests.edit.documents')) { ?>

    <div class="modal" id="connect-user" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h4 class="title"><?= lang('Connect user', 'Nutzer verknüpfen') ?></h4>

                <form action="<?= ROOTPATH ?>/guests/update/<?= $id ?>" method="post">

                    <div class="form-group">
                        <label class="element-author" for="username">
                            <?= lang('Select a person', 'Wähle eine Person') ?>
                        </label>
                        <select class="form-control" id="username" name="values[username]" autocomplete="off">
                            <option value=""><?= lang('-- No user --', '-- Kein Nutzer --') ?></option>
                            <?php
                            $persons = $osiris->persons->find(['username' => ['$ne' => null], 'last' => ['$ne' => '']], ['sort' => ['last' => 1]]);
                            foreach ($persons as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['username'] ?? '') ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn secondary"><?= lang('Connect', 'Verknüpfen') ?></button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="col-md-6">

        <div class="box box-signal">
            <div class="content">
                <h4 class="title mb-0">
                    Verknüpfter Nutzer
                </h4>
                <?php if (empty($form['username'] ?? false)) { ?>
                    <p>
                        Es ist zurzeit kein Nutzer verknüpft.
                    </p>
                <?php } else {
                    $username = strval($form['username']);
                    $userArr = $DB->getPerson($username);

                ?>
                    <div class="d-flex align-items-center my-20">

                        <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                        <div class="">

                            <h5 class="my-0">
                                <?= $userArr['first'] ?>
                                <?= $userArr['last'] ?>
                            </h5>
                            User:
                            <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="badge"><?= $username ?></a>

                        </div>
                    </div>
                <?php } ?>

                
    <?php if ($Settings->hasPermission('guests.edit.documents')) { ?>
                <a class="btn secondary" href="#connect-user">Nutzer verknüpfen</a>
                <?php } ?>
            </div>
        </div>


        <?php
        $files = $form['files'] ?? array();
        ?>
        
    <?php if ($Settings->hasPermission('guests.edit.documents')) { ?>
        <!-- modal to upload documents -->
        <div class="modal" id="upload-document" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h4 class="title"><?= lang('Upload document', 'Dokument hochladen') ?></h4>

                    <form action="<?= ROOTPATH ?>/guests/upload-files/<?= $id ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                        <div class="custom-file mb-20" id="file-input-div" >
                            <input type="file" id="file-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                            <label for="file-input"><?= lang('Append a file', 'Hänge eine Datei an') ?></label>
                            <br><small class="text-danger">Max. 16 MB.</small>
                        </div>
                        <button class="btn secondary">
                            <i class="ph ph-upload"></i>
                            Upload
                        </button>
                    </form>

                    <script>
                        var uploadField = document.getElementById("file-input");

                        uploadField.onchange = function() {
                            if (this.files[0].size > 16777216) {
                                toastError(lang("File is too large! Max. 16MB is supported!", "Die Datei ist zu groß! Max. 16MB werden unterstützt."));
                                this.value = "";
                            };
                        };
                    </script>
                </div>
            </div>
        </div>
        <?php } ?>


        <div class="box">
            <div class="content">
                <h4 class="title mb-0">
                    Hinterlegte Dokumente
                </h4>

                <table class="table simple w-auto">
                    <?php if (!empty($files)) : ?>
                        <?php foreach ($files as $file) : ?>
                            <tr>
                                <td><?= $file['filename'] ?></td>
                                <td><?= $file['filetype'] ?></td>
                                <td>
                                    <a href="<?= $file['filepath'] ?>"><i class="ph ph-download"></i></a>
                                </td>
                                <td>
                                    <form action="<?= ROOTPATH ?>/guests/upload-files/<?= $id ?>" method="post">
                                        <input type="hidden" name="delete" value="<?= $file['filename'] ?>">

                                        <button class="btn link" type="submit">
                                            <i class="ph ph-trash text-danger"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><?= lang('No files uploaded', 'Noch keine Dateien hochgeladen') ?></td>
                        </tr>
                    <?php endif; ?>
                </table>

                
    <?php if ($Settings->hasPermission('guests.edit.documents')) { ?>
                <a class="btn secondary" href="#upload-document">Dokument hochladen</a>
                <?php } ?>
            </div>
        </div>



        <?php if ($Settings->hasPermission('guests.edit.documents')) { ?>
        <!-- modal for chip registration -->
        <div class="modal" id="chip-registration" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h4 class="title"><?= lang('Chip registration', 'Chip hinterlegen') ?></h4>

                    <?php
                    $chip = $form['chip'] ?? '';
                    ?>

                    <form action="<?= ROOTPATH ?>/guests/update/<?= $id ?>" method="post">
                        <div class="form-group">
                            <label class="element-author" for="chip">
                                <?= lang('Chip number', 'Chipnummer') ?>
                            </label>
                            <input type="text" class="form-control" id="chip" name="values[chip][number]" autocomplete="off" value="<?= $chip['number'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="element-author" for="registered">
                                <?= lang('Registered at', 'Registriert am') ?>
                            </label>
                            <input type="date" class="form-control" id="registered" name="values[chip][start]" autocomplete="off" value="<?= $chip['start'] ?? date('Y-m-d') ?>" required>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="element-author" for="registered">
                                <?= lang('Returned at', 'Zurückgegeben am') ?>
                            </label>
                            <input type="date" class="form-control" id="registered" name="values[chip][end]" autocomplete="off" value="<?= $chip['end'] ?? '' ?>">
                        </div>
                        <button type="submit" class="btn secondary"><?= lang('Register', 'Registrieren') ?></button>
                    </form>
                </div>
            </div>
        </div>

        <?php } ?>
        <div class="box">
            <div class="content">
                <h4 class="title mb-0">
                    Zugangschip
                </h4>
                <?php if (isset($chip) && !empty($chip)) { ?>
                    <ul class="list">
                        <li>
                            <b>Chipnummer:</b> <?= $chip['number'] ?>
                        </li>
                        <li>
                            <b>Registriert am:</b> <?= format_date($chip['start']) ?>
                        </li>
                        <li>
                            <b>Zurückgegeben am:</b> <?= !empty($chip['end'])? format_date($chip['end'] ) : '-' ?>
                        </li>
                    </ul>
                <?php } else { ?>
                    <p>
                        Die Person hat keinen Zugangschip
                    </p>
                <?php } ?>

                <a class="btn secondary" href="#chip-registration">Chip hinterlegen</a>

            </div>
        </div>
    </div>
    
    <?php } ?>

</div>



<?php if (isset($_GET['verbose'])) {
    dump($form, true);
} ?>