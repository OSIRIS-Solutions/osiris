<?php
include_once BASEPATH . '/php/LDAPInterface.php';

if (isset($_POST['field'])) {
    $fields = array_filter($_POST['field'] ?? []);

    // Speichern der aktualisierten Daten
    $osiris->adminGeneral->updateOne(
        ['key' => 'ldap_mappings'],
        ['$set' => ['value' => $fields]],
        ['upsert' => true]
    );
} else {
    $fields = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
    $fields = DB::doc2Arr($fields['value'] ?? []);
}
// look in LDAP for those fields
$ldap_fields = array_filter($fields);

if (empty($ldap_fields)) {
    echo '<div class="alert warning">';
    echo lang(
        'No LDAP attributes have been configured for synchronization. Please configure them first in the <a href="' . ROOTPATH . '/admin/persons#section-auth">LDAP settings</a>.',
        'Es wurden keine LDAP-Attribute für die Synchronisation konfiguriert. Bitte konfiguriere diese zuerst in den <a href="' . ROOTPATH . '/admin/persons#section-auth">LDAP-Einstellungen</a>.'
    );
    echo '</div>';
    exit;
}

$filter = "(|";
foreach ($ldap_fields as $field) {
    $filter .= "($field=*)";
}
$filter .= ")";

$LDAP = new LDAPInterface();
$result = $LDAP->fetchUsers('(cn=*)', array_values($ldap_fields));
if (is_string($result)) {
    echo $result;
    return;
}
?>

<div class="alert success">
    <?= lang('The attributes have been saved.', 'Die Attribute wurden gespeichert.') ?>
</div>

<h1>
    <i class="ph-duotone ph-arrow-clockwise"></i>
    <?= lang('Synchronized attributes from LDAP', 'Synchronisierte Attribute aus LDAP') ?>
</h1>

<p>
    <?= lang('The following attributes are synchronized from LDAP to OSIRIS every time a user synchronization is performed.', 'Die folgenden Attribute werden von LDAP nach OSIRIS synchronisiert, jedes Mal wenn eine Nutzer-Synchronisation durchgeführt wird.') ?>
</p>

<style>
    th code {
        font-size: smaller;
        color: var(--muted-color);
        text-transform: none;
        font-weight: normal
    }
</style>

<table class="table">
    <thead>
        <tr>
            <th>
                User
                <br>
                <code>samaccountname</code>
            </th>
            <?php foreach ($ldap_fields as $osiris_field => $ldap_key) {
            ?>
                <th>
                    <?= htmlspecialchars($osiris_field) ?>
                    <br>
                    <code><?= htmlspecialchars($ldap_key) ?></code>
                </th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $entry) {
            $user = '';
            if (isset($entry['samaccountname'])) {
                $user = $entry['samaccountname'][0];
            } else if (isset($entry['uid'])) {
                $user = $entry['uid'][0];
            }
            if (empty($user)) continue;
        ?>
            <tr>
                <td><?= $user ?></td>
                <?php foreach ($ldap_fields as $osiris_field => $lf) { ?>
                    <td>
                        <?php if (isset($entry[$lf])) {
                            // if field = department, check if department exists in OSIRIS
                            if ($osiris_field == 'department') {
                                $dept = $Groups->findGroup($entry[$lf][0]);
                                if (!$dept) { ?>
                                    <i class="ph-duotone ph-warning text-danger" title="Department not found in OSIRIS"></i>
                            <?php
                                }
                            }
                            ?>
                            <?= $entry[$lf][0] ?>
                        <?php } else { ?>
                            <span class="text-danger">not found</span>
                        <?php } ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>

<p>
    <?= lang(
        'You are now ready to synchronize attributes from LDAP to OSIRIS by <a href="https://wiki.osiris-app.de/technical/user-management/ldap/#synchronisation-der-nutzerattribute" target="_blank">setting up a CRON-Job</a> or you can do it manually:',
        'Du bist nun bereit, die Attribute von LDAP nach OSIRIS zu synchronisieren, indem du einen <a href="https://wiki.osiris-app.de/technical/user-management/ldap/#synchronisation-der-nutzerattribute" target="_blank">CRON-Job</a> einrichtest oder es manuell tust:'
    ) ?>
</p>

<form action="<?= ROOTPATH ?>/synchronize-attributes-now" method="post">
    <button class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('Synchronize now', 'Jetzt synchronisieren') ?>
    </button>
</form>