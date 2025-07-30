<?php

/**
 * Manage person data
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<style>
    h2 i {
        color: var(--primary-color);
    }
</style>
<?php include_once BASEPATH . '/header-editor.php'; ?>

<h1>
    <i class="ph ph-user text-primary"></i>
    <?= lang('Person data', 'Personendaten') ?>
</h1>

<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-gear" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>
    <!-- authentication -->
    <a onclick="navigate('auth')" id="btn-auth" class="btn">
        <i class="ph ph-lock" aria-hidden="true"></i>
        <?php switch (strtoupper(USER_MANAGEMENT)) {
            case 'LDAP':
                echo lang('LDAP settings', 'LDAP-Einstellungen');
                break;
            case 'SSO':
            case 'OAUTH':
                echo lang('SSO settings', 'SSO-Einstellungen');
                break;
            default:
                echo lang('Authentication', 'Authentifizierung');
        } ?>
    </a>
    <!-- data fields -->
    <a onclick="navigate('data-fields')" id="btn-data-fields" class="btn">
        <i class="ph ph-database" aria-hidden="true"></i>
        <?= lang('Data fields', 'Datenfelder') ?>
    </a>
    <!-- positions -->
    <a onclick="navigate('positions')" id="btn-positions" class="btn">
        <i class="ph ph-tree-view" aria-hidden="true"></i>
        <?= lang('Positions', 'Positionen') ?>
    </a>

    <!-- keywords -->
    <a onclick="navigate('keywords')" id="btn-keywords" class="btn">
        <i class="ph ph-tag" aria-hidden="true"></i>
        <?= lang('Keywords', 'Schlagwörter') ?>
    </a>
</nav>

<section id="general">
    <?php
    $persons = $osiris->adminPersons->find();
    ?>
    <form action="<?= ROOTPATH ?>/crud/admin/features" method="post" id="role-form">

        <div class="box padded">
            <h2 class="title">
            <i class="ph ph-gear" aria-hidden="true"></i>
                <?= lang('General settings', 'Allgemeine Einstellungen') ?>
            </h2>
            <div class="form-group">
                <label for="" class="font-weight-bold">
                    <?= lang('Coins', 'Coins') ?>
                </label>
                <?php
                $coins = $Settings->featureEnabled('coins');
                ?>
                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="coins-true" value="1" name="values[coins]" <?= $coins ? 'checked' : '' ?>>
                    <label for="coins-true"><?= lang('enabled', 'aktiviert') ?></label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="coins-false" value="0" name="values[coins]" <?= $coins ? '' : 'checked' ?>>
                    <label for="coins-false"><?= lang('disabled', 'deaktiviert') ?></label>
                </div>

                <small class="d-block text-muted">
                    <?= lang('Coins are not saved anywhere, but are calculated on-demand. If you deactivate coins globally, they will not be calculated at all and will not be shown anywhere.', 'Coins werden nirgendwo gespeichert, sondern on-demand berechnet. Wenn ihr Coins global ausschaltet, werden sie also gar nicht erst berechnet und nirgendwo gezeigt.') ?>
                </small>

            </div>

            <div class="form-group">
                <label for="" class="font-weight-bold">
                    <?= lang('Achievements', 'Errungenschaften') ?>
                </label>
                <?php
                $achievements = $Settings->featureEnabled('achievements');
                ?>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="achievements-true" value="1" name="values[achievements]" <?= $achievements ? 'checked' : '' ?>>
                    <label for="achievements-true"><?= lang('enabled', 'aktiviert') ?></label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="achievements-false" value="0" name="values[achievements]" <?= $achievements ? '' : 'checked' ?>>
                    <label for="achievements-false"><?= lang('disabled', 'deaktiviert') ?></label>
                </div>

            </div>


            <div class="form-group">
                <label for="" class="font-weight-bold">
                    <?= lang('User profile metrics', 'Metriken im Nutzerprofil') ?>
                </label>
                <?php
                $user_metrics = $Settings->featureEnabled('user-metrics');
                ?>

                <div class=" custom-radio d-inline-block ml-10">>
                    <input type="radio" id="user-metrics-true" value="1" name="values[user-metrics]" <?= $user_metrics ? 'checked' : '' ?>>
                    <label for="user-metrics-true"><?= lang('enabled', 'aktiviert') ?></label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="user-metrics-false" value="0" name="values[user-metrics]" <?= $user_metrics ? '' : 'checked' ?>>
                    <label for="user-metrics-false"><?= lang('disabled', 'deaktiviert') ?></label>
                </div>

                <small class="d-block text-muted">
                    <?= lang('If this function is switched off, user metrics (graphs) are only visible on your own profile page.', 'Wenn diese Funktion ausgeschaltet wird, sind Nutzermetriken (Graphen) nur noch auf der eigenen Profilseite sichtbar.') ?>
                </small>

            </div>

            <div class="form-group">
                <label for="" class="font-weight-bold">
                    <?= lang('Profile images', 'Profilbilder der Nutzenden') ?>
                </label>
                <?php
                $db_pictures = $Settings->featureEnabled('db_pictures');
                ?>
                <div class=" custom-radio d-inline-block ml-10">>
                    <input type="radio" id="db_pictures-true" value="1" name="values[db_pictures]" <?= $db_pictures ? 'checked' : '' ?>>
                    <label for="db_pictures-true"><?= lang('Save in database', 'In Datenbank speichern') ?></label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" id="db_pictures-false" value="0" name="values[db_pictures]" <?= $db_pictures ? '' : 'checked' ?>>
                    <label for="db_pictures-false"><?= lang('Save in file system', 'Im Dateisystem speichern') ?></label>
                </div>

                <small class="d-block text-muted">
                    <?= lang(
                        'Saving the profile pictures in the database is recommended if the pictures are maintained exclusively via OSIRIS. If the images are saved in the file system, they can be uploaded more easily (into the folder <code>/img/users</code>) and, for example, updated automatically. However, they must then have the user name as the name and be in JPEG format!',
                        'Die Profilbilder in der Datenbank zu speichern wird empfohlen, wenn die Bilder ausschließlich über OSIRIS gepflegt werden. Wenn die Bilder im Dateisystem gespeichert werden, kann man sie leichter anders hochladen (in den Ordner <code>/img/users</code>) und z.B. automatisch aktualisieren. Sie müssen dann aber den Username als Namen haben und im JPEG-Format sein!'
                    ) ?>
                </small>
            </div>


            <?php if (strtoupper(USER_MANAGEMENT) !== 'AUTH') { ?>
                <div class="form-group">
                    <label for="">
                        <?= lang('LDAP user synchronization', 'LDAP-Nutzersynchronisierung') ?>
                    </label>
                    <?php
                    $sync = $Settings->featureEnabled('ldap-sync');
                    ?>

                    <div class="form-">
                        <label for="ldap-sync-blacklist"><?= lang('Username Blacklist (separated by comma)', 'Username-Blacklist (Komma-getrennt)') ?></label>
                        <textarea class="form-control small" name="general[ldap-sync-blacklist]" id="ldap-sync-blacklist"><?= $Settings->get('ldap-sync-blacklist') ?></textarea>
                    </div>
                    <div class="form-">
                        <label for="ldap-sync-whitelist"><?= lang('Username whitelist (separated by comma)', 'Username-Whitelist (Komma-getrennt)') ?></label>
                        <textarea class="form-control small" name="general[ldap-sync-whitelist]" id="ldap-sync-whitelist"><?= $Settings->get('ldap-sync-whitelist') ?></textarea>
                    </div>

                </div>
            <?php } ?>
            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>

        </div>
    </form>

</section>

<section id="auth" style="display: none;">
    <!-- Settings for different user management styles -->
    <?php
    switch (strtoupper(USER_MANAGEMENT)) {
        case 'LDAP':

            $attributeMappings = [
                'first' => '',
                'last' => '',
                'academic_title' => '',
                'mail' => '',
                'telephone' => '',
                'mobile' => '',
                'position' => '',
                'department' => '',
                'is_active' => '',
                'room' => '',
                'internal_id' => '',
            ];

            $config = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
            $availableLdapFields = DB::doc2Arr($config['value'] ?? []);
            $attributeMappings = array_merge($attributeMappings, $availableLdapFields ?? []);

            $fields = [
                'first' => [
                    'name' => lang('First Name', 'Vorname'),
                    'example' => 'givenname', // Beispiel: "John"
                ],
                'last' => [
                    'name' => lang('Last Name', 'Nachname'),
                    'example' => 'sn', // Beispiel: "Doe"
                ],
                'academic_title' => [
                    'name' => lang('Academic Title', 'Akademischer Titel'),
                    'example' => 'personalTitle', // Beispiel: "Dr."
                ],
                'mail' => [
                    'name' => lang('Email', 'E-Mail'),
                    'example' => 'mail', // Beispiel: "john.doe@example.com"
                ],
                'telephone' => [
                    'name' => lang('Telephone', 'Telefon'),
                    'example' => 'telephonenumber', // Beispiel: "+1 555 123 456"
                ],
                'mobile' => [
                    'name' => lang('Mobile', 'Mobil'),
                    'example' => 'mobile', // Beispiel: "+1 555 987 654"
                ],
                'position' => [
                    'name' => lang('Position', 'Position'),
                    'example' => 'title', // Beispiel: "Software Engineer"
                ],
                'department' => [
                    'name' => lang('Department', 'Abteilung'),
                    'example' => 'department', // Beispiel: "IT Department"
                ], //description
                'is_active' => [
                    'name' => lang('Active', 'Aktiv'),
                    'example' => 'useraccountcontrol', // Beispiel: "512" (Aktiv) oder "514" (Deaktiviert)
                ],
                'room' => [
                    'name' => lang('Room', 'Raum'),
                    'example' => 'physicaldeliveryofficename', // Beispiel: "Room 101"
                ],
                'internal_id' => [
                    'name' => lang('Internal ID', 'Interne ID'),
                    'example' => 'objectsid', // Beispiel: "12345"
                ],
            ];

    ?>
            <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post" class="box primary padded">


                <h2 class="title">
                    <i class="ph ph-lock" aria-hidden="true"></i>
                    <?= lang('LDAP Settings', 'LDAP-Einstellungen') ?>
                </h2>

                <p>
                    <?= lang('Here you can define the attributes that will be automatically synchronized with your LDAP instance.', 'Hier kannst du die Attribute festlegen, die automatisch mit deiner LDAP-Instanz synchronisiert werden sollen.') ?>
                </p>

                <p class="text-danger">
                    <i class="ph ph-warning"></i>
                    <?= lang('Please note that the synchronized attributes cannot be edited within OSIRIS anymore.', 'Bitte beachte, dass die synchronisierten Attribute nicht mehr in OSIRIS bearbeitet werden können.') ?>
                </p>

                <table class="table simple w-auto small mb-10">
                    <thead>
                        <tr>
                            <th><?= lang('Person attribute in OSIRIS', 'Personen-Attribut in OSIRIS') ?></th>
                            <th><?= lang('LDAP variable (leave empty to manage the field in OSIRIS)', 'LDAP-Variable (leer lassen, um das Feld in OSIRIS zu managen)') ?></th>
                            <th><?= lang('Example', 'Beispiel') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attributeMappings as $field => $ldap_field):
                            $f = $fields[$field];
                        ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($f['name'] ?? $field) ?>
                                </td>
                                <td>
                                    <input type="text" name="field[<?= $field ?>]" id="field-<?= $field ?>" value="<?= htmlspecialchars($ldap_field) ?>" class="form-control">
                                </td>
                                <td class="text-muted">
                                    <?= htmlspecialchars($f['example']) ?>
                                    <a onclick="$('#field-<?= $field ?>').val('<?= $f['example'] ?>')"><?= lang('Take', 'Übernehmen') ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn primary"><?= lang('Save &amp; Preview', 'Speichern und Vorschau zeigen') ?></button>

            </form>
        <?php
            break;

        case 'AUTH': ?>

            <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/persons">
                <div class="box primary padded">

                    <h2 class="title">
                        <i class="ph ph-lock" aria-hidden="true"></i>
                        <?= lang('AUTH settings', 'AUTH-Einstellungen') ?>
                    </h2>

                    <input type="hidden" name="general[auth-self-registration]" value="0">
                    <div class="custom-checkbox">
                        <input type="checkbox" name="general[auth-self-registration]" id="auth-self-registration-1" value="1" <?= $Settings->get('auth-self-registration', true) ? 'checked' : '' ?>>
                        <label for="auth-self-registration-1"><?= lang('Allow users to create their own account', 'Erlaube Benutzern, ein eigenes Konto zu erstellen') ?></label>
                    </div>
                    <br>

                    <button class="btn signal">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('Save', 'Speichern') ?>
                    </button>
                </div>

            </form>
        <?php
            break;

        case 'SSO':
        case 'OAUTH':
        ?>
            <?= lang('No further settings available.', 'Keine weiteren Einstellungen verfügbar.') ?>
    <?php
            break;

        default:
            break;
    }
    ?>
</section>


<?php
$fields = file_get_contents(BASEPATH . '/data/person-fields.json');
$fields = json_decode($fields, true);

$custom_fields = $osiris->adminFields->find();

$data = $Settings->get('person-data');
if (!is_null($data)) {
    $data = DB::doc2Arr($data);
} else {
    $data = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data = array_column($data, 'id');
}
?>


<section id="data-fields" style="display: none;">
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/persons">
        <div class="box primary padded">
            <h2 class="title">
                <i class="ph ph-database" aria-hidden="true"></i>
                <?= lang('Data fields', 'Datenfelder') ?>
            </h2>

            <table class="table simple w-auto small mb-10">
                <thead>
                    <tr>
                        <th><?= lang('Active', 'Aktiv') ?></th>
                        <th><?= lang('Field name', 'Feldname') ?></th>
                    </tr>
                </thead>
                <tbody id="data-fields">
                    <?php foreach ($fields as $field) { ?>
                        <tr>
                            <td>
                                <!-- checkbox -->
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="general[person-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                    <label for="field-<?= $field['id'] ?>"></label>
                                </div>
                            </td>
                            <td>
                                <b><?= lang($field['en'], $field['de'] ?? null) ?></b>
                                <?php if (isset($field['description'])) { ?>
                                    <small class="d-block text-muted">
                                        <?= lang($field['description']['en'], $field['description']['de'] ?? null) ?>
                                    </small>
                                <?php } ?>
                            </td>

                        </tr>
                    <?php } ?>
                    <?php if (!empty($custom_fields)) { ?>
                        <tr>
                            <td colspan="2">
                                <h5>
                                    <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
                                </h5>
                            </td>
                        </tr>
                        <?php foreach ($custom_fields as $field) { ?>
                            <tr>
                                <td>
                                    <!-- checkbox -->
                                    <div class="custom-checkbox">
                                        <input type="checkbox" name="general[person-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                        <label for="field-<?= $field['id'] ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($field['name']) ?></b>
                                </td>

                            </tr>
                        <?php } ?>

                    <?php } ?>
                </tbody>
            </table>

            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>

        </div>
    </form>


</section>


<section id="positions" style="display: none;">

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/persons">


        <div class="box primary padded">

            <h2 class="title">
                <i class="ph ph-tree-view" aria-hidden="true"></i>
                <?= lang('Possible Positions', 'Mögliche Positionen') ?>
            </h2>

            <p>
                <?= lang('Define the fields that are used as position for the staff members.', 'Definiere die Felder, die für die Mitarbeitenden verwendet werden.') ?>
            </p>

            <?php
            $staff = $Settings->get('staff');
            $staffPos = $staff['positions'] ?? [];
            $staffFree = $staff['free'] ?? true;
            ?>


            <div class="form-group">
                <div class="custom-radio">
                    <input type="radio" name="staff[free]" id="free-1" value="1" <?= $staffFree ? 'checked' : '' ?>>
                    <label for="free-1"><?= lang('Free text', 'Freitext') ?></label>
                </div>
                <small class="d-block text-muted">
                    <?= lang('If this option is selected, the staff members can enter their own position freely.', 'Wenn diese Option ausgewählt ist, können die Mitarbeitenden ihre Position frei eingeben.') ?>
                </small>
            </div>
            <div class="form-group">
                <div class="custom-radio">
                    <input type="radio" name="staff[free]" id="free-0" value="0" <?= !$staffFree ? 'checked' : '' ?>>
                    <label for="free-0"><?= lang('Defined selection', 'Definierte Liste') ?></label>
                </div>
                <small class="d-block text-muted">
                    <?= lang('If this option is selected, the staff members can only select their position from the list you define below.', 'Wenn diese Option ausgewählt ist, können die Mitarbeitenden ihre Position nur aus der Liste auswählen, die du weiter unten definierst.') ?>
                </small>
            </div>

            <hr>

            <h5>
                <?= lang('Defined list of positions', 'Definierte Liste möglicher Positionen') ?>:
            </h5>
            <small class="text-muted">
                <?=lang('This list will only be used if you select "Defined selection" above.', 'Diese Liste wird nur verwendet, wenn du "Definierte Liste" oben auswählst.')?>
            </small>

            <table class="table simple small my-20">
                <thead>
                    <tr>
                        <th></th>
                        <th>Position (english)</th>
                        <th>Position (deutsch)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="possible-positions">
                    <?php foreach ($staffPos as $value) {
                        if ($value instanceof \MongoDB\BSON\Document) {
                            $value = DB::doc2Arr($value);
                        }
                        // dump type of value
                        if (is_array($value) || is_object($value)) {
                            $de = $value[1] ?? $value[0];
                            $en = $value[0];
                        } else {
                            $en = $value;
                            $de = $value;
                        }
                    ?>
                        <tr>
                            <td class="w-50">
                                <i class="ph ph-dots-six-vertical text-muted handle"></i>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="staff[positions][]" value="<?= $en ?>" required>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="staff[positions_de][]" value="<?= $de ?>">
                            </td>
                            <td>
                                <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
                <tfoot>
                    <tr>
                        <td class="w-50 bg-white"></td>
                        <td colspan="3" class="bg-white">
                            <button class="btn" type="button" onclick="addValuesRow()"><i class="ph ph-plus-circle"></i></button>
                        </td>
                    </tr>
                </tfoot>
            </table>


            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                Save
            </button>
        </div>
    </form>
</section>


<section id="keywords" style="display: none;">
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/persons">
        <div class="box primary padded">
            <h2 class="title">
                <i class="ph ph-tag" aria-hidden="true"></i>
                <?= lang('Keywords', 'Schlagwörter') ?>
            </h2>

            <p>
                <?= lang('Define keywords that the staff members can use. Whether this field is displayed in the user profile or not can be defined in the <q>Data fields</q> tab.', 'Definiere Schlagworte, die für die Mitarbeitenden verwendet werden. Ob dieses Feld im Nutzerprofil angezeigt wird oder nicht, kann im Tab <q>Datenfelder</q> definiert werden.') ?>
            </p>

            <!-- input for name of this keyword -->
            <div class="form-group">
                <label for="keyword-name" class="font-weight-bold">
                    <?= lang('Name of the keyword field', 'Name des Schlagwort-Feldes') ?>
                </label>
                <input type="text" name="general[staff-keyword-name]" id="keyword-name" class="form-control" value="<?= $Settings->get('staff-keyword-name', 'Keywords') ?>">
            </div>

            <?php
            $keywords = DB::doc2Arr($Settings->get('staff-keywords', []));
            ?>
            <div class="form-group">
                <label for="staff-keywords" class="font-weight-bold">
                    <?= lang('Defined list of keywords', 'Definierte Liste von Schlagworten') ?>:
                </label>
                <small class="d-block text-muted">
                    <?= lang('Define a list of keywords that the staff members can use. Each keyword should be seperated by a new line.', 'Definiere eine Liste von Schlagworten, die die Mitarbeitenden verwenden können. Jedes Schlagwort sollte in einer neuen Zeile stehen.') ?>
                </small>
                <textarea name="general[staff-keywords]" id="staff-keywords" class="form-control" rows="10"><?= implode(PHP_EOL, $keywords) ?></textarea>
            </div>

            <button class="btn signal">
                <i class="ph ph-floppy-disk"></i>
                Save
            </button>
        </div>
    </form>
</section>


<script>
    $(document).ready(function() {
        $('#possible-positions').sortable({
            handle: ".handle",
        });
    });


    function addValuesRow() {
        $('#possible-positions').append(`
        <tr>
            <td class="w-50">
                <i class="ph ph-dots-six-vertical text-muted handle"></i>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions][]" required>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions_de][]">
            </td>
            <td>
                <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
            </td>
        </tr>
    `);
    }

    function navigate(key) {
        $('section').hide()
        $('section#' + key).show()

        $('.pills .btn').removeClass('active')
        $('.pills .btn#btn-' + key).addClass('active')

        // hash
        window.location.hash = 'section-' + key;
    }
</script>