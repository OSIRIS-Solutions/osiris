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
    echo lang('people.no_ldap_attributes_have_been_configured_for_synchronization_please_configur', replace: ['rootpath' => ROOTPATH]);
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
    <?= lang('people.the_attributes_have_been_saved') ?>
</div>

<h1>
    <i class="ph-duotone ph-arrow-clockwise"></i>
    <?= lang('people.synchronized_attributes_from_ldap') ?>
</h1>

<p>
    <?= lang('people.the_following_attributes_are_synchronized_from_ldap_to_osiris_every_time_a') ?>
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
                    <?= e($osiris_field) ?>
                    <br>
                    <code><?= e($ldap_key) ?></code>
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
    <?= lang('people.you_are_now_ready_to_synchronize_attributes_from_ldap_to_osiris_by_setting') ?>
</p>

<form action="<?= ROOTPATH ?>/synchronize-attributes-now" method="post">
    <button class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('people.synchronize_now') ?>
    </button>
</form>