<?php

/**
 * Page for managing users with AUTH user management
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/users
 *
 * @package OSIRIS
 * @since 1.3.7
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$um = strtoupper(USER_MANAGEMENT);
?>

<style>
    .form-row.row-eq-spacing>[class^=col].floating-form {
        padding-left: unset;
    }
</style>
<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-users" aria-hidden="true"></i>
        <?= lang('common.create_new_user') ?>
    </h1>

    <div class="mb-20">
        <span class="badge primary">
            <?= lang('admin.you_are_using') ?>
            <strong><?= $um ?></strong>
            <?= lang('admin.for_authentication') ?>
        </span>
    </div>


    <form action="<?= ROOTPATH ?>/crud/admin/add-user" method="post">

        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input class="form-control" type="text" id="username" name="username" required placeholder="username">
                <label class="required" for="username"><?= lang('common.username_guest_account_add') ?></label>
                <?php if ($um == 'AUTH') { ?>
                    <small class="text-muted">
                        <?= lang('common.please_choose_a_username_without_spaces_or_special_characters') ?>
                    </small>
                <?php } elseif ($um == 'LDAP') { ?>
                    <small class="text-muted">
                        <?= lang('admin.please_make_sure_that_the_username_equals_the_username_in_ldap_case_sensiti') ?>
                    </small>
                <?php } elseif ($um == 'OAUTH') { ?>
                    <small class="text-muted">
                        <?= lang('admin.please_use_the_exact_user_name_from_the_email_address_of_the_user_everythin') ?>
                    </small>
                <?php } ?>
            </div>

            <?php if ($um == 'AUTH') { ?>
                <div class="col floating-form">
                    <input class="form-control" type="password" id="password" name="password" required placeholder="password">
                    <label class="required" for="password">Password</label>
                </div>
            <?php } ?>
        </div>


        <div class="form-row row-eq-spacing">
            <div class="col-sm-2 floating-form">
                <?php
                $title = $data['academic_title'] ?? '';
                ?>
                <select name="values[academic_title]" id="academic_title" class="form-control">
                    <option value="" <?= $title == '' ? 'selected' : '' ?>><?= lang('common.none_guest_account_add') ?></option>
                    <option value="Dr." <?= $title == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                    <option value="Prof. Dr." <?= $title == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option>
                    <option value="PD Dr." <?= $title == 'PD Dr.' ? 'selected' : '' ?>>PD Dr.</option>
                    <option value="Prof." <?= $title == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                    <option value="PD" <?= $title == 'PD' ? 'selected' : '' ?>>PD</option>
                </select>
                <label for="academic_title">Title</label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[first]" id="first" class="form-control" value="<?= $data['first'] ?? '' ?>" required placeholder="first name">
                <label class="required" for="first"><?= lang('common.name_first') ?></label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[last]" id="last" class="form-control" value="<?= $data['last'] ?? '' ?>" required placeholder="last name">
                <label class="required" for="last"><?= lang('common.name_last') ?></label>
            </div>
        </div>


        <h5><?= lang('common.contact') ?></h5>
        <div class="form-row row-eq-spacing">

            <div class="col-sm floating-form">
                <input type="text" name="values[mail]" id="mail" class="form-control" value="<?= $data['mail'] ?? '' ?>" required placeholder="mail">
                <label for="mail" class="required">Mail</label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[telephone]" id="telephone" class="form-control" value="<?= $data['telephone'] ?? '' ?>" placeholder="phone">
                <label for="telephone"><?= lang('common.telephone') ?></label>
            </div>

        </div>


        <div class="form-group">
            <h5><?= lang('common.department') ?></h5>

            <?php
            $tree = $Groups->getHierarchyTree();
            ?>

            <div class="form-group">
                <select name="values[depts][]" id="dept" class="form-control" multiple="multiple" size="5">
                    <option value="">Unknown</option>
                    <?php
                    foreach ($tree as $d => $dept) { ?>
                        <option value="<?= $d ?>" <?= (in_array($d, $data['depts'] ?? [])) == $d ? 'selected' : '' ?>><?= $dept ?></option>
                    <?php } ?>
                </select>

                <script>
                    $(document).ready(function() {
                        $("#dept").selectize();
                    });
                </script>
            </div>
        </div>



        <div class="form-group">
            <span><?= lang('common.gender') ?>:</span>
            <?php
            $gender = $data['gender'] ?? 'n';
            ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-m" value="m" <?= $gender == 'm' ? 'checked' : '' ?>>
                <label for="gender-m"><?= lang('common.gender_male') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-f" value="f" <?= $gender == 'f' ? 'checked' : '' ?>>
                <label for="gender-f"><?= lang('common.gender_female') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-d" value="d" <?= $gender == 'd' ? 'checked' : '' ?>>
                <label for="gender-d"><?= lang('common.gender_non_binary') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-n" value="n" <?= $gender == 'n' ? 'checked' : '' ?>>
                <label for="gender-n"><?= lang('common.gender_not_specified') ?></label>
            </div>

        </div>


        <div>
            <h5><?= lang('common.roles') ?></h5>
            <?php
            $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
            $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

            foreach ($roles as $role) {
                if ($role === 'user') continue;
            ?>
                <div class="form-group custom-checkbox d-inline-block mr-10">
                    <input type="checkbox" id="role-<?= $role ?>" value="1" name="values[roles][<?= $role ?>]" <?= ($data['roles'][$role] ?? false) ? 'checked' : '' ?>>
                    <label for="role-<?= $role ?>"><?= strtoupper($role) ?></label>
                </div>
            <?php
            }
            ?>


        </div>


        <button type="submit" class="btn success">
            <i class="ph ph-user-plus"></i>
            <?= lang('admin.create_user') ?>
        </button>
    </form>

    <br><br>


    <?php if ($um == 'AUTH') {
        $token = $Settings->get('auth-token');
        if (!$Settings->get('auth-self-registration', true)) { ?>
            <div class="alert mb-20">
                <h5 class="title">
                    <?= lang('admin.self_registration_is_disabled') ?>
                </h5>
                <p>
                    <?= lang('admin.currently_self_registration_is_completely_disabled_this_means_that_only_an') ?>
                </p>
                <a href="<?= ROOTPATH ?>/admin/authentication" class="btn">
                    <?= lang('admin.go_to_auth_settings') ?>
                </a>
            </div>
        <?php } elseif (!empty($token)) { ?>
            <div class="box padded">
                <?= lang('admin.to_allow_users_to_register_share_the_following_token_with_them') ?>
                <code id="auth-token" class="code"><?= $token ?></code>
                <button class="btn small ml-5" type="button" onclick="copyToClipboard('<?= $token ?>')" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>">
                    <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                </button>
                <br>
                <!-- or share the link -->
                <?= lang('admin.or_share_the_link') ?>
                <code id="auth-token" class="code"><?= $_SERVER['HTTP_HOST'] ?>/auth/new-user?token=<?= $token ?></code>
                <button class="btn small ml-5" type="button" onclick="copyToClipboard('<?= $_SERVER['HTTP_HOST'] ?>/auth/new-user?token=<?= $token ?>')" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>">
                    <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                </button>
            </div>

            <script>
                function copyToClipboard(text) {
                    navigator.clipboard.writeText(text)
                    toastSuccess('Token copied to clipboard.')
                }
            </script>
        <?php } else { ?>
            <div class="alert mb-20">
                <div class="title">
                    <?= lang('admin.no_auth_token_set') ?>
                </div>
                <p>
                    <?= lang('admin.currently_no_auth_token_is_set_this_means_that_users_can_register_without_a') ?>
                </p>
                <a href="<?= ROOTPATH ?>/admin/authentication" class="btn">
                    <?= lang('admin.go_to_auth_settings') ?>
                </a>
            </div>
        <?php } ?>

        </p>
    <?php } ?>

</div>