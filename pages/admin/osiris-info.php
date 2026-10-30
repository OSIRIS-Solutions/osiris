<?php
$data = $osiris->system->find();
$system = [];
foreach ($data as $item) {
    $system[$item->key] = $item->value;
}

?>
<!-- <link rel="stylesheet" href="<?= ROOTPATH ?>/css/phpinfo.css"> -->
<style>
    tbody th {
        background-color: var(--primary-color-10);
        width: 300px;
        font-weight: bold;
    }

    thead th {
        background-color: var(--primary-color-light);
        font-weight: bold;
        color: white;
    }
</style>


<div class="phpinfo-container">
    <h1 class="p">
        <i class="ph-duotone ph-info"></i> <?= lang('OSIRIS Info', 'OSIRIS Info') ?>
    </h1>

    <h2>
        <?= lang('System Information', 'Systeminformationen') ?>
    </h2>

    <table class="table">
        <tbody>
            <tr>
                <th class="e"><?= lang('OSIRIS Version (from Database)', 'OSIRIS-Version (aus der Datenbank)') ?></th>
                <td><?= e($system['version'] ?? '-') ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('OSIRIS Version (from Code)', 'OSIRIS-Version (aus dem Code)') ?></th>
                <td>
                    <?= OSIRIS_VERSION ?> Build: <?= OSIRIS_BUILD ?>
                </td>
            </tr>
            <tr>
                <th class="e"><?= lang('Last Updated', 'Letztes Update') ?></th>
                <td><?= e($system['last_update'] ?? '-') ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('PHP Version', 'PHP-Version') ?></th>
                <td><?= e(PHP_VERSION) ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('Last LDAP Sync', 'Letzte LDAP-Synchronisierung') ?></th>
                <td><?= e($system['ldap-sync'] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>


    <h2><?= lang('Uploads', 'Uploads') ?></h2>

    <table class="table">
        <tbody>
            <?php
            // does /uploads exist?
            $uploadsWritable = is_dir(BASEPATH . '/uploads');
            ?>
            <tr>
                <th class="e"><?= lang('Uploads Directory Exists', 'Uploads-Verzeichnis existiert') ?></th>
                <td>
                    <?php if ($uploadsWritable) { ?>
                        <span class="text-success"><?= lang('Yes', 'Ja') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('No', 'Nein') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php
            // is /uploads writable?
            $uploadsWritable = is_writable(BASEPATH . '/uploads');
            ?>
            <tr>
                <th class="e"><?= lang('Uploads Directory Writable', 'Uploads-Verzeichnis beschreibbar') ?></th>
                <td>
                    <?php if ($uploadsWritable) { ?>
                        <span class="text-success"><?= lang('Yes', 'Ja') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('No', 'Nein') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php
            // if /uploads is writable, check if we can create a test file
            if ($uploadsWritable) {
                $testFile = BASEPATH . '/uploads/test.txt';
                $testFileCreated = false;
                try {
                    file_put_contents($testFile, 'test');
                    $testFileCreated = true;
                } catch (Exception $e) {
                    $testFileCreated = false;
                }
            ?>
                <tr>
                    <th class="e"><?= lang('Uploads Directory Test File Created', 'Testdatei im Uploads-Verzeichnis erstellt') ?></th>
                    <td>
                        <?php if ($testFileCreated) { ?>
                            <span class="text-success"><?= lang('Yes', 'Ja') ?></span>
                            <?php
                            // delete the test file
                            unlink($testFile);
                            ?>
                        <?php } else { ?>
                            <span class="text-danger"><?= lang('No', 'Nein') ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php 
            // check if db pictures are enabled
            $dbPicturesEnabled = $Settings->featureEnabled('db_pictures');
            ?>
            <tr>
                <th class="e"><?= lang('Database Pictures Enabled', 'Datenbank-Bilder aktiviert') ?></th>
                <td>
                    <?php if ($dbPicturesEnabled) { ?>
                        <span class="text-success"><?= lang('Yes, no need to check /img/users', 'Ja, kein Prüfen von /img/users nötig') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('No', 'Nein') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php 
            // if not enabled, check if /img/users is writable
            if (!$dbPicturesEnabled) {
                $imgUsersWritable = is_writable(BASEPATH . '/img/users');
            ?>
                <tr>
                    <th class="e"><?= lang('User Images Directory Writable', 'Benutzerbilder-Verzeichnis beschreibbar') ?></th>
                    <td>
                        <?php if ($imgUsersWritable) { ?>
                            <span class="text-success"><?= lang('Yes', 'Ja') ?></span>
                        <?php } else { ?>
                            <span class="text-danger"><?= lang('No', 'Nein') ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php 
            // check upload limits
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            $postMaxSize = ini_get('post_max_size');
            $smallest = min($uploadMaxFilesize, $postMaxSize);
            ?>
            <tr>
                <th class="e"><?= lang('PHP Upload Limits', 'PHP-Upload-Limits') ?></th>
                <td>
                    <?= $smallest ?>B
                    <small>(<?= lang('upload_max_filesize', 'upload_max_filesize') ?>: <?= e($uploadMaxFilesize) ?>B, <?= lang('post_max_size', 'post_max_size') ?>: <?= e($postMaxSize) ?>B)</small>
                </td>
            </tr>
        </tbody>
    </table>
</div>

