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
        <i class="ph-duotone ph-info"></i> <?= lang('common.osiris_info') ?>
    </h1>

    <h2>
        <?= lang('admin.system_information') ?>
    </h2>

    <table class="table">
        <tbody>
            <tr>
                <th class="e"><?= lang('admin.osiris_version_from_database') ?></th>
                <td><?= e($system['version'] ?? '-') ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('admin.osiris_version_from_code') ?></th>
                <td>
                    <?= OSIRIS_VERSION ?> Build: <?= OSIRIS_BUILD ?>
                </td>
            </tr>
            <tr>
                <th class="e"><?= lang('admin.last_updated') ?></th>
                <td><?= e($system['last_update'] ?? '-') ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('admin.php_version') ?></th>
                <td><?= e(PHP_VERSION) ?></td>
            </tr>
            <tr>
                <th class="e"><?= lang('admin.last_ldap_sync') ?></th>
                <td><?= e($system['ldap-sync'] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>


    <h2><?= lang('admin.uploads') ?></h2>

    <table class="table">
        <tbody>
            <?php
            // does /uploads exist?
            $uploadsWritable = is_dir(BASEPATH . '/uploads');
            ?>
            <tr>
                <th class="e"><?= lang('admin.uploads_directory_exists') ?></th>
                <td>
                    <?php if ($uploadsWritable) { ?>
                        <span class="text-success"><?= lang('common.yes') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('common.no') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php
            // is /uploads writable?
            $uploadsWritable = is_writable(BASEPATH . '/uploads');
            ?>
            <tr>
                <th class="e"><?= lang('admin.uploads_directory_writable') ?></th>
                <td>
                    <?php if ($uploadsWritable) { ?>
                        <span class="text-success"><?= lang('common.yes') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('common.no') ?></span>
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
                    <th class="e"><?= lang('admin.uploads_directory_test_file_created') ?></th>
                    <td>
                        <?php if ($testFileCreated) { ?>
                            <span class="text-success"><?= lang('common.yes') ?></span>
                            <?php
                            // delete the test file
                            unlink($testFile);
                            ?>
                        <?php } else { ?>
                            <span class="text-danger"><?= lang('common.no') ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php 
            // check if db pictures are enabled
            $dbPicturesEnabled = $Settings->featureEnabled('db_pictures');
            ?>
            <tr>
                <th class="e"><?= lang('admin.database_pictures_enabled') ?></th>
                <td>
                    <?php if ($dbPicturesEnabled) { ?>
                        <span class="text-success"><?= lang('admin.yes_no_need_to_check_img_users') ?></span>
                    <?php } else { ?>
                        <span class="text-danger"><?= lang('common.no') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php 
            // if not enabled, check if /img/users is writable
            if (!$dbPicturesEnabled) {
                $imgUsersWritable = is_writable(BASEPATH . '/img/users');
            ?>
                <tr>
                    <th class="e"><?= lang('admin.user_images_directory_writable') ?></th>
                    <td>
                        <?php if ($imgUsersWritable) { ?>
                            <span class="text-success"><?= lang('common.yes') ?></span>
                        <?php } else { ?>
                            <span class="text-danger"><?= lang('common.no') ?></span>
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
                <th class="e"><?= lang('admin.php_upload_limits') ?></th>
                <td>
                    <?= $smallest ?>B
                    <small>(<?= lang('admin.upload_max_filesize') ?>: <?= e($uploadMaxFilesize) ?>B, <?= lang('admin.post_max_size') ?>: <?= e($postMaxSize) ?>B)</small>
                </td>
            </tr>
        </tbody>
    </table>
</div>

