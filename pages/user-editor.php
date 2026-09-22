<?php

/**
 * Page to edit user information
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/edit/<username>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$depts = DB::doc2Arr($data['depts'] ?? []);

$ldap_fields = [];

if (strtoupper(USER_MANAGEMENT) == 'LDAP') {
    $ldap_fields = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
    $ldap_fields = DB::doc2Arr($ldap_fields['value'] ?? []);
    // ignore empty values
    $ldap_fields = array_keys(array_filter($ldap_fields));
}

$ldap_msg = '<small class="text-muted">' . lang('people.this_field_is_centrally_managed_by_your_organisation') . '</small>';

$data_fields = $Settings->get('person-data');
if (!is_null($data_fields)) {
    $data_fields = DB::doc2Arr($data_fields);
} else {
    $fields = file_get_contents(BASEPATH . '/data/person-fields.json');
    $fields = json_decode($fields, true);

    $data_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data_fields = array_column($data_fields, 'id');
}

$active = function ($field) use ($data_fields) {
    return in_array($field, $data_fields);
};
?>


<script>
    const selectedOrgIds = JSON.parse('<?= json_encode($depts) ?>');
</script>
<?php include_once BASEPATH . '/header-editor.php'; ?>

<script src="<?= ROOTPATH ?>/js/user-editor.js"></script>

<style>
    .form-control[readonly] {
        background-color: var(--muted-color-very-light);
        cursor: not-allowed;
    }

    .form-control[readonly]:focus {
        background-color: var(--muted-color-very-light);
        cursor: not-allowed;
        box-shadow: none;
    }
</style>

<h1 class="mt-0">
    <i class="ph-duotone ph-student"></i>
    <?= $data['name'] ?>
</h1>

<?php if ($data['is_active'] ?? true) { ?>
    <div class="text-success">
        <?= lang('people.this_user_account_is_active') ?>
    </div>
<?php } else { ?>
    <div class="text-danger">
        <?= lang('people.this_user_account_is_inactive') ?>
    </div>
<?php } ?>


<nav class="pills mt-20 mb-0">
    <a onclick="navigate('personal')" id="btn-personal" class="btn active">
        <i class="ph ph-user" aria-hidden="true"></i>
        <?= lang('people.personal') ?>
    </a>

    <a onclick="navigate('contact')" id="btn-contact" class="btn">
        <i class="ph ph-identification-card" aria-hidden="true"></i>
        <?= lang('people.contact_amp_profile') ?>
    </a>

    <a onclick="navigate('organization')" id="btn-organization" class="btn">
        <i class="ph ph-building" aria-hidden="true"></i>
        <?= lang('common.organization') ?>
    </a>

    <a onclick="navigate('research')" id="btn-research" class="btn">
        <i class="ph ph-flask" aria-hidden="true"></i>
        <?= lang('common.research') ?>
    </a>

    <a onclick="navigate('biography')" id="btn-biography" class="btn">
        <i class="ph ph-book-open-text" aria-hidden="true"></i>
        <?= lang('common.biography') ?>
    </a>

    <?php if ($Settings->featureEnabled('portal')) { ?>
        <a onclick="navigate('portfolio')" id="btn-portfolio" class="btn">
            <i class="ph ph-eye" aria-hidden="true"></i>
            <?= lang('admin.portfolio') ?>
        </a>
    <?php } ?>
    <a onclick="navigate('account')" id="btn-account" class="btn">
        <i class="ph ph-key" aria-hidden="true"></i>
        <?= lang('people.account') ?>
    </a>
    <?php if ($data['username'] == $_SESSION['username'] || $Settings->hasPermission('user.settings')) { ?>
        <a onclick="navigate('preferences')" id="btn-preferences" class="btn">
            <i class="ph ph-gear" aria-hidden="true"></i>
            <?= lang('people.preferences') ?>
        </a>
    <?php } ?>
</nav>

<form action="<?= ROOTPATH ?>/crud/users/update/<?= $data['username'] ?>" method="post">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

    <section id="personal">
        <h2 class="title"><?= lang('people.name_and_personal_information') ?></h2>

        <div class="form-row row-eq-spacing">
            <div class="col-sm-2">
                <label for="academic_title">Title</label>
                <select name="values[academic_title]" id="academic_title" class="form-control" <?= in_array('academic_title', $ldap_fields) ? 'disabled' : '' ?>>
                    <option value="" <?= $data['academic_title'] == '' ? 'selected' : '' ?>></option>
                    <option value="Dr." <?= $data['academic_title'] == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                    <option value="Prof. Dr." <?= $data['academic_title'] == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option>
                    <option value="PD Dr." <?= $data['academic_title'] == 'PD Dr.' ? 'selected' : '' ?>>PD Dr.</option>
                    <option value="Prof." <?= $data['academic_title'] == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                    <option value="PD" <?= $data['academic_title'] == 'PD' ? 'selected' : '' ?>>PD</option>
                    <option value="Dipl.-Ing." <?= $data['academic_title'] == 'Dipl.-Ing.' ? 'selected' : '' ?>>Dipl.-Ing.</option>
                </select>
                <?php if (in_array('title', $ldap_fields)) {
                    echo $ldap_msg;
                } ?>
            </div>
            <div class="col-sm">
                <label for="first"><?= lang('common.name_first') ?></label>
                <input type="text" name="values[first]" id="first" class="form-control" value="<?= $data['first'] ?? '' ?>" <?= in_array('first', $ldap_fields) ? 'disabled' : 'required' ?>>
                <?php if (in_array('first', $ldap_fields)) {
                    echo $ldap_msg;
                } ?>
            </div>
            <div class="col-sm">
                <label for="last"><?= lang('common.name_last') ?></label>
                <input type="text" name="values[last]" id="last" class="form-control" value="<?= $data['last'] ?? '' ?>" <?= in_array('last', $ldap_fields) ? 'disabled' : 'required' ?>>
                <?php if (in_array('last', $ldap_fields)) {
                    echo $ldap_msg;
                } ?>
            </div>
        </div>


        <?php
        if (!isset($data['names'])) {
            $names = [
                $data['formalname'],
                Document::abbreviateAuthor($data['last'], $data['first'], true, ' ')
            ];
        } else {
            $names = $data['names'];
        }
        ?>


        <div class="form-group">
            <label for="names" class=""><?= lang('people.names_for_author_matching') ?></label>

            <div class="box m-0 p-5">
                <?php foreach ($names as $n) { ?>
                    <div class="input-group d-inline-flex w-auto m-5">
                        <input type="text" name="values[names][]" value="<?= $n ?>" required class="form-control">
                        <div class="input-group-append">
                            <a class="btn text-danger" onclick="$(this).closest('.input-group').remove();">×</a>
                        </div>
                    </div>
                <?php } ?>

                <button class="btn secondary m-5" type="button" onclick="addName(event, this);">
                    <i class="ph ph-plus"></i> <?= lang('people.add_name') ?>
                </button>
            </div>
        </div>

        <?php if ($active('gender')) { ?>
            <div class="form-group">
                <span><?= lang('common.gender') ?>:</span><br>
                <?php
                $gender = $data['gender'] ?? 'n';
                ?>

                <div class="custom-radio d-inline-block mr-10">
                    <input type="radio" name="values[gender]" id="gender-m" value="m" <?= $gender == 'm' ? 'checked' : '' ?>>
                    <label for="gender-m"><?= lang('common.gender_male') ?></label>
                </div>
                <div class="custom-radio d-inline-block mr-10">
                    <input type="radio" name="values[gender]" id="gender-f" value="f" <?= $gender == 'f' ? 'checked' : '' ?>>
                    <label for="gender-f"><?= lang('common.gender_female') ?></label>
                </div>
                <div class="custom-radio d-inline-block mr-10">
                    <input type="radio" name="values[gender]" id="gender-d" value="d" <?= $gender == 'd' ? 'checked' : '' ?>>
                    <label for="gender-d"><?= lang('common.gender_non_binary') ?></label>
                </div>
                <div class="custom-radio d-inline-block mr-10">
                    <input type="radio" name="values[gender]" id="gender-n" value="n" <?= $gender == 'n' ? 'checked' : '' ?>>
                    <label for="gender-n"><?= lang('common.gender_not_specified') ?></label>
                </div>

            </div>
        <?php } ?>
    </section>



    <section id="organization" style="display:none;">

        <h2 class="title mb-0">
            <?= lang('people.organisational_information') ?>
        </h2>

        <p>
            <strong><?= lang('common.username') ?>:</strong> <code class="code"><?= $data['username'] ?></code>
            <br>
            <small class="text-muted">
                <?= lang('people.the_username_cannot_be_changed') ?>
            </small>
        </p>

        <!-- internal_id -->
        <?php if ($active('internal_id')) { ?>
            <div class="form-group">
                <label for="internal_id"><?= lang('common.internal_id') ?></label>
                <input type="text" name="values[internal_id]" id="internal_id" class="form-control w-auto" value="<?= $data['internal_id'] ?? '' ?>" <?= in_array('internal_id', $ldap_fields) ? 'disabled' : '' ?>>
                <?php if (in_array('internal_id', $ldap_fields)) {
                    echo $ldap_msg;
                } ?>
            </div>
        <?php } ?>


        <!-- check if there are active custom fields -->
        <?php
        $custom_fields = $osiris->adminFields->find()->toArray();
        if (!empty($custom_fields)) {
            require_once BASEPATH . "/php/Modules.php";
            $Modules = new Modules($data);

            // echo "<h5>" . lang('Institutional fields', 'Institutionelle Felder') . "</h5>";
            foreach ($custom_fields as $field) {
                $key = $field['id'] ?? null;
                if ($active($key)) {
                    echo '<div class="form-group">';
                    $Modules->custom_field($key);
                    echo '</div>';
                }
            }
        } ?>




        <!-- room -->
        <?php if ($active('room')) { ?>
            <div class="form-group">
                <label for="room"><?= lang('common.room') ?></label>
                <input type="text" name="values[room]" id="room" class="form-control w-auto" value="<?= $data['room'] ?? '' ?>" <?= in_array('room', $ldap_fields) ? 'disabled' : '' ?>>
                <?php if (in_array('room', $ldap_fields)) {
                    echo $ldap_msg;
                } ?>
            </div>
        <?php } ?>

        <style>
            #depts .table tr.selected td::before {
                content: '\E182';
                font-family: 'Phosphor';
                font-size: 1em;
                color: var(--primary-color);
            }
        </style>

        <div class="depts mb-20">

            <?php if ($active('position')) { ?>
                <div class="form-group">
                    <label for="position">
                        <h5><?= lang('common.current_position') ?></h5>
                    </label>

                    <?php
                    $staff = $Settings->get('staff');
                    $staffPos = $staff['positions'] ?? [];
                    $staffFree = $staff['free'] ?? true;
                    ?>
                    <?php if ($staffFree) { ?>
                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="position" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="values[position]" id="position" type="text" class="form-control" value="<?= e($data['position'] ?? '') ?>" <?= in_array('position', $ldap_fields) ? 'disabled' : '' ?>>
                                <?php if (in_array('position', $ldap_fields)) {
                                    echo $ldap_msg;
                                } ?>
                            </div>
                            <div class="col-md-6">
                                <label for="position_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="values[position_de]" id="position_de" type="text" class="form-control" value="<?= e($data['position_de'] ?? '') ?>" <?= in_array('position', $ldap_fields) ? 'disabled' : '' ?>>
                                <?php if (in_array('position', $ldap_fields)) {
                                    echo $ldap_msg;
                                } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <!-- select list from predifined pos -->
                        <select name="values[position_both]" id="position" class="form-control">
                            <option value=""> -- <?= lang('common.no_position_selected') ?> --- </option>
                            <?php foreach ($staffPos as $pos) {
                                $en = $pos[0] ?? '-';
                                $de = $pos[1] ?? '-';
                            ?>
                                <option value="<?= $en ?>;;<?= $de ?>" <?= ($data['position'] ?? '') == $en ? 'selected' : '' ?>><?= $en ?> // <?= $de ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>

                </div>
            <?php } ?>

            <h5>
                <?= lang('people.organisational_units') ?>
            </h5>

            <?php
            $units = DB::doc2Arr($data['units'] ?? []);
            ?>

            <a href="<?= ROOTPATH ?>/user/units/<?= $user ?>" target="_blank" rel="noopener noreferrer">
                <i class="ph ph-edit"></i>
                <?= lang('people.edit_units') ?>
            </a>

            <table class="table w-auto mt-10">
                <thead>
                    <tr>
                        <th>
                            <?= lang('common.unit') ?>
                        </th>
                        <th>
                            <?= lang('common.start') ?>
                        </th>
                        <th>
                            <?= lang('common.end') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $dept) {
                        $d = $Groups->getName($dept['unit']);
                    ?>
                        <tr data-id="<?= $dept['id'] ?>">
                            <td><?= $d ?></td>
                            <td><?= $dept['start'] ?? '<em class="text-danger">' . lang('common.unknown') . '</em>' ?></td>
                            <td><?= $dept['end'] ?? '<em class="text-success">' . lang('common.current') . '</em>' ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


        </div>


    </section>

    <?php if ($Settings->featureEnabled('portal')) { ?>

        <section id="portfolio" style="display:none;">
            <h2 class="title"><?= lang('people.public_visibility') ?> (Portfolio)</h2>


            <?php if ($active('hide')) { ?>
                <div class="alert danger">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="hide" value="1" name="values[hide]" <?= ($data['hide'] ?? false) ? 'checked' : '' ?>>
                        <label for="hide"><?= lang('people.hide_profile_in_portfolio') ?></label>
                    </div>
                    <small class="text-danger">
                        <?= lang('people.by_hiding_your_profile_you_prevent_osiris_portfolio_from_displaying_your_pr') ?>
                    </small>
                </div>
            <?php } ?>

            <p class="text-danger">
                <?= lang('people.by_setting_the_image_mail_or_phone_number_to_publicly_visible_you_allow_osi') ?>
            </p>
            <?php if ($active('public_image')) { ?>
                <!-- show profile picture -->
                <div class="custom-checkbox mb-20">
                    <input type="checkbox" id="public_image" value="1" name="values[public_image]" <?= ($data['public_image'] ?? false) ? 'checked' : '' ?>>
                    <label for="public_image"><?= lang('people.show_profile_picture') ?></label>
                </div>
            <?php } ?>

            <?php if ($active('public_other_activities')) { ?>
                <!-- show profile picture -->
                <input type="hidden" name="values[public_other_activities]" value="false">
                <div class="custom-checkbox mb-20">
                    <input type="checkbox" id="public_other_activities" value="true" name="values[public_other_activities]" <?= ($data['public_other_activities'] ?? true) ? 'checked' : '' ?>>
                    <label for="public_other_activities"><?= lang('people.show_other_activities_not_publications_as_a_separate_section_in_the_profile') ?></label>
                </div>
            <?php } ?>

            <?php if ($active('public_teaching')) { ?>
                <!-- show profile picture -->
                <input type="hidden" name="values[public_teaching]" value="false">
                <div class="custom-checkbox mb-20">
                    <input type="checkbox" id="public_teaching" value="true" name="values[public_teaching]" <?= ($data['public_teaching'] ?? true) ? 'checked' : '' ?>>
                    <label for="public_teaching"><?= lang('people.show_teaching_activities_as_a_separate_section_in_the_profile') ?></label>
                </div>
            <?php } ?>

            <?php if ($active('public_email')) { ?>
                <div class="custom-checkbox mb-20">
                    <input type="checkbox" id="public_email" value="1" name="values[public_email]" <?= ($data['public_email'] ?? true) ? 'checked' : '' ?>>
                    <label for="public_email"><?= lang('people.show_email_address') ?></label>
                </div>
            <?php } ?>

            <div class="custom-checkbox mb-20">
                <input type="checkbox" id="public_phone" value="1" name="values[public_phone]" <?= ($data['public_phone'] ?? false) ? 'checked' : '' ?>>
                <label for="public_phone"><?= lang('people.show_telephone_number') ?></label>
            </div>

            <!-- alternative mail -->
            <div class="form-group">
                <label for="mail_alternative"><?= lang('people.alternative_mail') ?></label>
                <input type="text" name="values[mail_alternative]" id="mail_alternative" class="form-control" value="<?= $data['mail_alternative'] ?? '' ?>">
            </div>
            <!-- comment for mail -->
            <div class="form-group">
                <label for="mail_alternative_comment"><?= lang('people.explanation_for_alternative_mail') ?></label>
                <input type="text" name="values[mail_alternative_comment]" id="mail_alternative_comment" class="form-control" value="<?= $data['mail_alternative_comment'] ?? '' ?>">
            </div>

        </section>
    <?php } ?>


    <section id="contact" style="display:none;">
        <h4 class="title"><?= lang('common.contact') ?></h4>
        <div class="form-group">
            <label for="mail">Mail</label>
            <input type="text" name="values[mail]" id="mail" class="form-control need-validation" data-validator="email" value="<?= $data['mail'] ?? '' ?>" <?= in_array('mail', $ldap_fields) ? 'disabled' : '' ?> onblur="validateEmail(this)">
            <?php if (in_array('mail', $ldap_fields)) {
                echo $ldap_msg;
            } ?>
        </div>

        <?php
        $digest = $Settings->get('mail-digest', 'none');
        if ($digest != 'none') {
            // select frequency or opt out
            $user_digest = $data['mail_digest'] ?? 'default';
            if ($user_digest == 'default') {
                $user_digest = $Settings->get('mail-digest', 'none');
            }
        ?>
            <div class="form-group">
                <label for="mail_digest"><?= lang('people.mail_digest') ?></label>
                <select name="values[mail_digest]" id="mail_digest" class="form-control w-auto">
                    <option value="default" <?= $user_digest == 'default' ? 'selected' : '' ?>>--- <?= lang('people.use_default_setting') ?> (<?= ucfirst($digest) ?>)</option>
                    <option value="none" <?= $user_digest == 'none' ? 'selected' : '' ?>><?= lang('people.no_mail_digest') ?></option>
                    <option value="daily" <?= $user_digest == 'daily' ? 'selected' : '' ?>><?= lang('people.daily_mail_digest') ?></option>
                    <option value="weekly" <?= $user_digest == 'weekly' ? 'selected' : '' ?>><?= lang('people.weekly_mail_digest') ?></option>
                    <option value="monthly" <?= $user_digest == 'monthly' ? 'selected' : '' ?>><?= lang('people.monthly_mail_digest') ?></option>
                </select>
                <small class="text-muted">
                    <?= lang('people.you_can_choose_to_receive_a_summary_of_your_activities_by_email_at_regular') ?>
                    <?= lang('people.preferred_language_based_on_interface') ?>
                    <strong><?= strtoupper($data['lang'] ?? 'de') ?></strong>
                </small>
            </div>
        <?php } ?>

        <div class="form-row row-eq-spacing">
            <?php if ($active('telephone')) { ?>
                <div class="col-sm-6">
                    <label for="telephone"><?= lang('common.telephone') ?></label>
                    <input type="tel" name="values[telephone]" id="telephone" class="form-control need-validation" data-validator="telephone" value="<?= $data['telephone'] ?? '' ?>" <?= in_array('telephone', $ldap_fields) ? 'disabled' : '' ?> onblur="validateTelephone(this)">
                    <?php if (in_array('telephone', $ldap_fields)) {
                        echo $ldap_msg;
                    } ?>
                </div>
            <?php } ?>

            <?php if ($active('mobile')) { ?>
                <div class="col-sm-6">
                    <label for="mobile"><?= lang('common.mobile') ?></label>
                    <input type="tel" name="values[mobile]" id="mobile" class="form-control need-validation" data-validator="telephone" value="<?= $data['mobile'] ?? '' ?>" <?= in_array('mobile', $ldap_fields) ? 'disabled' : '' ?> onblur="validateTelephone(this)">
                    <?php if (in_array('mobile', $ldap_fields)) {
                        echo $ldap_msg;
                    } ?>
                </div>
            <?php } ?>

        </div>

        <h4 class="title"><?= lang('people.researcher_ids') ?></h4>

        <div class="form-row row-eq-spacing">
            <div class="col-sm-6">
                <label for="orcid">ORCID</label>
                <?php
                include_once BASEPATH . '/php/Orcid.php';
                $orcid_settings = new Orcid_Settings();
                if (!isset($data['orcid_validated']) || !$data['orcid_validated'] || !$Settings->featureEnabled('orcid')) { ?>
                    <input type="text" name="values[orcid]" id="orcid" class="form-control need-validation" data-validator="orcid" value="<?= $data['orcid'] ?? '' ?>" oninput="validateORCID(this);">
                    <small class="text-danger" id="orcid-wrong" style="display: none;">
                        <?= lang('people.the_orcid_should_be_in_the_format_0000_0000_0000_0000') ?>
                    </small>
                <?php } else { ?>
                    <div class="input-group">
                        <input type="text" name="values[orcid]" id="orcid" class="form-control" value="<?= $data['orcid'] ?? '' ?>" disabled>
                        <div class="input-group-append">
                            <a class="btn text-danger" onclick="disconnectORCID()">×</a>
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= lang('people.orcid_is_already_connected') ?>
                    </small>
                    <br>
                    <script>
                        function disconnectORCID() {
                            if (confirm('<?= lang('people.are_you_sure_you_want_to_disconnect_your_orcid') ?>')) {
                                // /crud/orcid/disconnect
                                fetch('<?= ROOTPATH ?>/crud/orcid/disconnect', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        username: '<?= $data['username'] ?>'
                                    })
                                }).then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert('<?= lang('people.orcid_disconnected_successfully') ?>');
                                            location.reload();
                                        } else {
                                            alert('<?= lang('people.error_disconnecting_orcid') ?>');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('<?= lang('people.error_disconnecting_orcid') ?>');
                                    });
                            }
                        }
                    </script>
                <?php } ?>

                <?php
                if ($data['username'] == $_SESSION['username'] && $Settings->featureEnabled('orcid')) {
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                ?>
                    <small class="text-muted">
                        <?= lang('common.or_auth_page') ?>
                    </small>
                    <br>
                    <a href="<?= $orcid_settings->api_auth_url ?>oauth/authorize?client_id=<?= $orcid_settings->client_id ?>&response_type=code&scope=/authenticate&redirect_uri=<?= $protocol . $_SERVER['HTTP_HOST'] . ROOTPATH ?>/orcid/validate" id="orcid-validation" class="btn">
                        <i class="ph ph-user-circle-check" aria-hidden="true"></i>
                        <?= lang('common.connect_orcid') ?>
                    </a>
                <?php } ?>
            </div>


            <div class="col-sm-6">
                <label for="google_scholar">Google Scholar ID</label>
                <input type="text" name="values[google_scholar]" id="google_scholar" class="form-control need-validation" data-validator="googleScholar" value="<?= $data['google_scholar'] ?? '' ?>" oninput="validateGoogleScholar(this)">
                <small class="text-muted">
                    <?= lang('people.not_the_url_only_the_bold_part_https_scholar_google_com_citations_user_2g1y') ?>
                </small>
                <div class="text-danger" id="google-scholar-wrong" style="display: none;">
                    <?= lang('people.please_enter_a_valid_google_scholar_id') ?>
                </div>
            </div>
        </div>


        <?php if ($active('socials')) { ?>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <p class="text-danger">
                    <?= lang('people.please_note_that_the_following_information_is_optional_if_you_do_not_wish_t') ?>
                </p>
            <?php } ?>

            <h4>
                <?= lang('people.social_media') ?>
            </h4>
            <div id="socials">
                <input type="hidden" name="values[socials]" value="">
                <?php
                $socials = DB::doc2Arr($data['socials'] ?? []);
                foreach ($socials as $t => $url) {
                    $logo = socialLogo($t);
                ?>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="ph <?= $logo ?> mr-5 text-primary"></i>
                                    <?= ucfirst($t) ?>
                                </span>
                            </div>
                            <input type="text" name="values[socials][<?= $t ?>]" class="form-control need-validation" data-validator="social" value="<?= $url ?>" placeholder="<?= lang('common.url') ?>">
                            <div class="input-group-append">
                                <a class="btn text-danger" onclick="$(this).closest('.input-group').remove();">×</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- dropdown to add new social -->
            <div class="dropdown">
                <button class="btn" data-toggle="dropdown" type="button" id="socials-dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-plus"></i>
                    <?= lang('people.add_new_social') ?>
                </button>
                <div class="dropdown-menu" aria-labelledby="socials-dropdown">
                    <?php foreach (['researchgate', 'youtube', 'github', 'linkedin', 'mastodon', 'bluesky', 'instagram', 'facebook', 'X', 'matrix', 'website'] as $s) {
                        if (array_key_exists($s, $socials)) continue;
                        $logo = socialLogo($s);
                    ?>
                        <a class="item py-0" onclick="addSocial(event, '<?= $s ?>', '<?= $logo ?>');">
                            <i class="ph <?= $logo ?> text-primary"></i>
                            <?= ucfirst($s) ?>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <script>
                function addSocial(e, type, logo) {
                    e.preventDefault();
                    // var i = $(btn).closest('form').find('select[name^="values[socials]"]').last().attr('name').match(/\[(\d+)\]/)[1];
                    // get last index
                    var html = `
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="ph ${logo} mr-5 text-primary"></i>
                        ${type.toUpperCase()}
                    </span>
                </div>
                <input type="text" name="values[socials][${type}]" class="form-control need-validation" data-validator="social" value="" placeholder="URL">
                <div class="input-group-append">
                    <a class="btn text-danger" onclick="$(this).closest('.input-group').remove();">×</a>
                </div>
            </div>
        `;
                    $('#socials').append(html);
                }

                // validate social media urls
                $(document).on('blur', '#socials input', function() {
                    validateSocial(this);
                });
            </script>
        <?php } ?>

        <!-- Contact Button -->
        <?php if ($Settings->featureEnabled('contact-button') && !empty($Settings->get('contact-button'))) { ?>
            <h4>
                <?= lang('people.contact_button') ?>
            </h4>
            <?php 
                $contact_button = $data['contact-button'] ?? false;
                $contact_button_type = $data['contact-button-type'] ?? 'mail';
            ?>

            <div class="form-group">
                <div>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="contact-button-true" value="1" name="values[contact-button]" <?= $contact_button ? 'checked' : '' ?>>
                        <label for="contact-button-true"><?= lang('common.enabled_features') ?></label>
                    </div>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="contact-button-false" value="0" name="values[contact-button]" <?= $contact_button ? '' : 'checked' ?>>
                        <label for="contact-button-false"><?= lang('common.disabled_features') ?></label>
                    </div>
                </div>
                <small class="text-muted">
                    <?= lang('people.the_contact_button_will_be_displayed_on_your_osiris_profile_page_you_can_ch') ?>
                </small>
                <div class="form-row row-eq-spacing" style="display: none;" id="contact-button-settings">
                    <div class="col-sm">
                        <label for="contact-button-type"><?= lang('common.contact_type') ?></label>
                        <select id="contact-button-type" name="values[contact-button-type]" class="form-control" onchange="toggleContact(this)">
                            <?php foreach ($Settings->get('contact-button')->getArrayCopy() as $type => $enabled) { 
                                if (!$enabled) continue; ?>
                                <option value="<?= $type ?>" <?= $contact_button_type == $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label for="contact"><?= lang('common.contact') ?></label>
                        <input type="text" name="values[contact]" id="contact-button-input" class="form-control need-validation" data-validator="contact"  value="<?= $data['contact'] ?? '' ?>" oninput="validateContact(this)">
                        <small class="text-muted" id="contact-button-input-help"></small>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        function toggleContactButtonSettings() {
                            if ($('#contact-button-true').is(':checked')) {
                                $('#contact-button-settings').show();
                                toggleContact($('#contact-button-type'));
                            } else {
                                $('#contact-button-settings').hide();
                            }
                        }
                        toggleContactButtonSettings();
                        $('input[name="values[contact-button]"]').change(toggleContactButtonSettings);
                    });

                    function toggleContact(select) {
                        var type = $(select).val();
                        var contactInput = $('#contact-button-input');
                        var contactHelp = $('#contact-button-input-help');
                        if (type === 'mail' || type === 'teams') {
                            contactInput.attr('placeholder', '<?= lang('people.enter_email_address') ?>');
                            contactHelp.text('<?= lang('people.please_add_an_email_address_it_will_be_used_for_the_contact_button') ?>');
                            if (('<?= $data['contact-button-type'] ?? false ?>' === 'mail' || '<?= $data['contact-button-type'] ?? false ?>' === 'teams' ) && '<?= $data['contact'] ?? false ?>') {
                                contactInput.val('<?= $data['contact'] ?? '' ?>');
                            } else if ('<?= $data['mail'] ?? false ?>') {
                                contactInput.val('<?= $data['mail'] ?>');
                            } else {
                                contactInput.val('');
                            }
                        } else if (type === 'slack') {
                            contactInput.attr('placeholder', '<?= lang('people.enter_slack_user_id') ?>');
                            contactHelp.text('<?= lang('people.please_add_a_slack_user_id_in_the_format_u12345678_it_will_be_used_for_the') ?>');
                            if ('<?= $data['contact-button-type'] ?? false ?>' === 'slack' && '<?= $data['contact'] ?? false ?>') {
                                contactInput.val('<?= $data['contact'] ?? '' ?>');
                            } else {
                                contactInput.val('');
                            }
                        } else if (type === 'matrix') {
                            contactInput.attr('placeholder', '<?= lang('people.enter_matrix_id') ?>');
                            contactHelp.text('<?= lang('people.please_add_a_matrix_id_in_the_format_username_server_it_will_be_used_for_th') ?>');
                            if ('<?= $data['contact-button-type'] ?? false ?>' === 'matrix' && '<?= $data['contact'] ?? false ?>') {
                                contactInput.val('<?= $data['contact'] ?? '' ?>');
                            } else if ('<?= $data['socials']['matrix'] ?? false ?>') {
                                matrix = '<?= $data['socials']['matrix'] ?? '' ?>';
                                matrix = '<?= $data['socials']['matrix'] ?? '' ?>';
                                matrixId = matrix.includes('/#/') ? matrix.split('/#/')[1] : matrix;
                                contactInput.val(matrixId);
                            } else {
                                contactInput.val('');
                            }
                        } else if (type === 'other') {
                            contactInput.attr('placeholder', '<?= lang('people.enter_contact_url') ?>');
                            contactHelp.text('<?= lang('people.please_add_a_contact_url_it_will_be_used_for_the_contact_button') ?>');
                            if ('<?= $data['contact-button-type'] ?? false ?>' === 'other' && '<?= $data['contact'] ?? false ?>') {
                                contactInput.val('<?= $data['contact'] ?? '' ?>');
                            } else {
                                contactInput.val('');
                            }
                        }
                        validateContact(contactInput);
                    }

                </script>
            </div>
        <?php } ?>

    </section>



    <section id="account" style="display:none;">
        <h2 class="title">
            <?= lang('people.account_settings') ?>
        </h2>

        <?php if (!($data['is_active'] ?? true)) { ?>
            <h5>
                <?= lang('people.reactivate_inactive_user_account') ?>
            </h5>
            <div class="custom-checkbox mb-10">
                <input type="checkbox" id="is_active" value="1" name="values[is_active]">
                <label for="is_active"><?= lang('people.reactivate') ?></label>
            </div>
        <?php } ?>

        <?php if (
            USER_MANAGEMENT == 'AUTH' &&
            $data['username'] == ($_SESSION['realuser'] ?? $_SESSION['username'])
        ) { ?>

            <h5>
                <?= lang('people.change_password') ?>
            </h5>

            <div class="form-group">
                <label for="old_password"><?= lang('people.old_password') ?></label>
                <input type="password" name="old_password" id="old_password" class="form-control">
            </div>

            <div class="form-row row-eq-spacing">
                <div class="col-sm-6">
                    <label for="password"><?= lang('auth.password_new') ?></label>
                    <input type="password" name="password" id="password" class="form-control need-validation" data-validator="password" oninput="validatePassword(this);">
                    <small id="password-wrong-length">
                        <?= lang('people.the_password_should_be_at_least_8_characters_long') ?>
                    </small>
                    <br>
                    <small id="password-wrong-uppercase">
                        <?= lang('people.the_password_must_contain_at_least_one_uppercase_letter') ?>
                    </small>
                    <br>
                    <small id="password-wrong-lowercase">
                        <?= lang('people.the_password_must_contain_at_least_one_lowercase_letter') ?>
                    </small>
                </div>

                <div class="col-sm-6">
                    <label for="password2"><?= lang('people.repeat_password') ?></label>
                    <input type="password" name="password2" id="password2" class="form-control need-validation" data-validator="password2" oninput="validatePassword2(this)">
                    <br>
                    <small class="text-danger" id="password2-wrong" style="display: none;">
                        <?= lang('people.passwords_do_not_match') ?>
                    </small>
                </div>
            </div>
        <?php } ?>

        <?php if ($Settings->hasPermission('user.roles')) { ?>

            <h5><?= lang('common.roles') ?></h5>
            <!-- ensure that empty roles are saved too -->
            <input type="hidden" name="values[roles][]" value="">
            <?php
            foreach ($Settings->get('roles') as $role) {
                // everyone is user: no setting needed
                if ($role == 'user') continue;

                // check if user has role
                $has_role = in_array($role, DB::doc2Arr($data['roles'] ?? array()));

                $disable = false;
                // only admin can make others admins
                if ($role == 'admin' && !$Settings->hasPermission('admin.give-right')) $disable = true;
            ?>
                <div class="form-group custom-checkbox d-inline-block ml-10 mb-10 <?= $disable ? 'text-muted' : '' ?>">
                    <input type="checkbox" id="role-<?= $role ?>" value="<?= $role ?>" name="values[roles][]" <?= ($has_role) ? 'checked' : '' ?> <?= $disable ? 'onclick="return false;"' : '' ?>>
                    <label for="role-<?= $role ?>"><?= strtoupper($role) ?></label>
                </div>
            <?php } ?>
        <?php } ?>

        <h5>
            <?= lang('people.transfer_the_maintenance_of_your_profile') ?>
        </h5>
        <?php
        if (is_string($data['maintenance'] ?? null)) $data['maintenance'] = [$data['maintenance']];
        $maintenance = DB::doc2Arr($data['maintenance'] ?? []);
        ?>

        <div class="form-group mb-0">
            <input type="hidden" name="values[maintenance]" value="">

            <style>
                #maintenance-list:empty::before {
                    content: "<?= lang('people.this_profile_is_not_shared_with_someone') ?>";
                    color: var(--muted-color);
                    font-style: italic;
                }

                #maintenance-list:not(:empty)::before {
                    content: "<?= lang('people.this_profile_was_shared_with') ?>";
                }
            </style>
            <div class="author-widget">
                <div class="author-list p-10" id="maintenance-list"><?php
                                                                    $module_lst = [];
                                                                    foreach ($maintenance as $u) {
                                                                        $mP = $DB->getPerson($u);
                                                                    ?><div class='author'>
                            <?= e($mP['displayname']) ?>
                            <input type='hidden' name='values[maintenance][]' value='<?= e($u) ?>'>
                            <a onclick='$(this).parent().remove()'>&times;</a>
                        </div><?php } ?></div>
                <div class="footer">
                    <div class="input-group small d-inline-flex w-auto">
                        <select class="form-control" id="maintenance-select">
                            <option value="" disabled selected><?= lang('people.select_a_person_to_share_with') ?></option>
                            <?php
                            $all_users = $osiris->persons->find(['is_active' => ['$ne' => false]], ['sort' => ['last' => 1, 'first' => 1]]);
                            foreach ($all_users as $s) { ?>
                                <option value="<?= e($s['username']) ?>"><?= e("$s[last], $s[first] ($s[username])") ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn small primary" type="button" onclick="addMaintenance();">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function addMaintenance() {
                    const selected = $('#maintenance-select option:selected');
                    const user = selected.val()
                    const name = selected.html()

                    console.log(user);
                    if (user.length === 0) return;
                    // check if already exists
                    if ($('#maintenance-list').find(`input[value="${user}"]`).length > 0) {
                        toastError('<?= lang('people.person_already_exists') ?>');
                        return;
                    }
                    var html = `<div class='author'>${name} <input type='hidden' name='values[maintenance][]' value='${user}'> <a onclick='$(this).parent().remove()'>&times;</a></div>`;
                    $('#maintenance-list').append(html);
                    // add hidden input to form
                    // $('#keyword-list').append(``);
                }
            </script>
            <!-- 
            <select name="values[maintenance]" id="maintenance" class="form-control">
                <option value="">
                    <?= lang('people.profile_is_not_shared_with_someone') ?>
                </option>

                <?php
                $selected = $data['maintenance'] ?? '';
                $all_users = $osiris->persons->find(['is_active' => ['$ne' => false]], ['sort' => ['last' => 1, 'first' => 1]]);
                foreach ($all_users as $s) { ?>
                    <option value="<?= $s['username'] ?>" <?= $selected == $s['username'] ? 'selected' : '' ?>><?= "$s[last], $s[first] ($s[username])" ?></option>
                <?php } ?>
            </select> -->
        </div>

        <p class=" text-danger">
            <i class="ph ph-warning"></i>
            <?= lang('people.warning_this_person_gets_full_access_to_your_osiris_profile_and_can_edit_in') ?>
        </p>

    </section>

    <?php if ($data['username'] == $_SESSION['username'] || $Settings->hasPermission('user.settings')) { ?>

        <section id="preferences" style="display:none;">
            <h2 class="title"><?= lang('people.profile_preferences') ?></h2>


            <h5><?= lang('people.sidebar_favourites') ?></h5>

            <p>
                <?= lang('people.you_can_add_your_favourite_pages_to_the_sidebar_for_quick_access') ?>
            </p>

            <?php
            $favs = DB::doc2Arr($data['sidebar_favorites'] ?? []);
            include_once BASEPATH . '/php/SidebarNav.php';
            $sidebar = new SidebarNav($Settings);
            $options = $sidebar->getFavoritableOptions();
            $options = array_column($options, null, 'id');
            ?>
            <!-- save empty favs as well -->
            <input type="hidden" name="values[sidebar_favorites]" value="">
            <div class="author-widget">
                <div class="author-list p-10" id="sidebar_favorites-list">
                    <?php
                    $module_lst = [];
                    foreach ($favs as $fav) {
                        $label = $options[$fav]['label'] ?? '';
                    ?>
                        <div class='author'>
                            <i class="ph ph-dots-six-vertical text-muted"></i> <?= $label ?>
                            <input type='hidden' name='values[sidebar_favorites][]' value='<?= $fav ?>'>
                            <a onclick='$(this).parent().remove()'>&times;</a>
                        </div>
                    <?php } ?>
                </div>
                <div class="footer">
                    <div class="input-group small d-inline-flex w-auto">
                        <select class="form-control" id="sidebar-select">
                            <option value="" disabled selected><?= lang('people.add_favorite') ?></option>
                            <?php
                            foreach ($options as $option) {
                                $id = $option['id'];
                            ?>
                                <option value="<?= $id ?>"><?= $option['label'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn small primary" type="button" onclick="addSidebarFavorite();">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function addSidebarFavorite() {
                    var sidebar = $('#sidebar-select').val();
                    if (sidebar.length === 0) return;
                    var label = $('#sidebar-select option:selected').text();
                    // check if already exists
                    if ($('#sidebar_favorites-list').find(`input[value="${sidebar}"]`).length > 0) {
                        toastError('<?= lang('people.sidebar_favorite_already_exists') ?>');
                        return;
                    }
                    var html = `<div class='author'><i class="ph ph-dots-six-vertical text-muted"></i> ${label} <input type='hidden' name='values[sidebar_favorites][]' value='${sidebar}'> <a onclick='$(this).parent().remove()'>&times;</a></div>`;
                    $('#sidebar_favorites-list').append(html);
                }
                $(document).ready(function() {
                    var sidebardiv = $("#sidebar_favorites-list");
                    sidebardiv.sortable({});
                });
            </script>


            <h5>
                <?= lang('people.display_of_activities') ?>
                <a href="#" onclick="$('#display_activities-info').toggleClass('hidden'); return false;">
                    <i class="ph ph-info text-muted"></i>
                </a>
            </h5>
            <?php
            $display_activities = $data['display_activities'] ?? 'web';
            ?>
            <p class="hidden" id="display_activities-info">
                <?= lang('people.you_can_choose_how_activities_are_displayed_for_you_when_you_use_osiris') ?>
            </p>


            <div class="form-group">
                <div class="custom-radio">
                    <input type="radio" name="values[display_activities]" id="display_activities-web" value="web" <?= $display_activities == 'web' ? 'checked' : '' ?>>
                    <label for="display_activities-web"><?= lang('people.web_display') ?></label>
                </div>
                <small class="text-muted">
                    <?= lang('people.display_type_optimized_for_the_web') ?>
                </small>
            </div>
            <div class="form-group">
                <div class="custom-radio">
                    <input type="radio" name="values[display_activities]" id="display_activities-print" value="print" <?= $display_activities != 'web' ? 'checked' : '' ?>>
                    <label for="display_activities-print"><?= lang('people.print_display') ?></label>
                </div>
                <small class="text-muted">
                    <?= lang('people.display_type_optimized_for_printing_and_exporting_activities') ?>
                </small>
            </div>


            <?php
            if ($Settings->featureEnabled('coins')) {
            ?>

                <div class="mt-10">
                    <h5>
                        <?= lang('people.coin_visibility') ?>
                        <a href="#" onclick="$('#coins-info').toggleClass('hidden'); return false;">
                            <i class="ph ph-info text-muted"></i>
                        </a>
                    </h5>
                    <?php
                    $show_coins = $data['show_coins'] ?? 'none';
                    ?>

                    <p class="hidden" id="coins-info">
                        <i class="ph ph-coins text-signal"></i>
                        <?= lang('people.coins_are_a_gamification_element_in_osiris_and_represent_points_you_earn_fo') ?>
                    </p>
                    <div class="custom-radio d-inline-block mr-10">
                        <input type="radio" name="values[show_coins]" id="show_coins-true" value="none" <?= $show_coins == 'none' ? 'checked' : '' ?>>
                        <label for="show_coins-true"><?= lang('people.for_nobody') ?></label>
                    </div>
                    <div class="custom-radio d-inline-block mr-10">
                        <input type="radio" name="values[show_coins]" id="show_coins-myself" value="myself" <?= $show_coins == 'myself' ? 'checked' : '' ?>>
                        <label for="show_coins-myself"><?= lang('people.for_myself') ?></label>
                    </div>
                    <div class="custom-radio d-inline-block mr-10">
                        <input type="radio" name="values[show_coins]" id="show_coins-all" value="all" <?= $show_coins == 'all' ? 'checked' : '' ?>>
                        <label for="show_coins-all"><?= lang('people.for_everyone') ?></label>
                    </div>

                </div>
            <?php
            }
            ?>


            <?php
            if ($Settings->featureEnabled('achievements')) {
            ?>
                <div class="mb-20">
                    <h5>
                        <?= lang('people.achievement_visibility') ?>
                        <a href="#" onclick="$('#achievements-info').toggleClass('hidden'); return false;">
                            <i class="ph ph-info text-muted"></i>
                        </a>
                    </h5>
                    <?php
                    $hide_achievements = $data['hide_achievements'] ?? false;
                    ?>
                    <p class="hidden" id="achievements-info">
                        <i class="ph ph-trophy text-signal"></i>
                        <?= lang('people.achievements_are_a_gamification_element_in_osiris_and_represent_badges_you') ?>
                    </p>

                    <div class="custom-radio d-inline-block mr-10">
                        <input type="radio" name="values[hide_achievements]" id="hide_achievements-true" value="true" <?= $hide_achievements ? 'checked' : '' ?>>
                        <label for="hide_achievements-true"><?= lang('people.for_nobody') ?></label>
                    </div>
                    <div class="custom-radio d-inline-block mr-10">
                        <input type="radio" name="values[hide_achievements]" id="hide_achievements-false" value="false" <?= $hide_achievements ? '' : 'checked' ?>>
                        <label for="hide_achievements-false"><?= lang('people.for_everyone') ?></label>
                    </div>
                </div>
            <?php
            }
            ?>
        </section>
    <?php } ?>


    <section id="research" style="display:none">

        <?php if ($active('topics')) { ?>
            <!-- if topics are registered, you can choose them here -->
            <?php $Settings->topicChooser($data['topics'] ?? []) ?>
        <?php } ?>


        <?php if ($active('keywords')) {
            $kw_name = $Settings->get('staff-keyword-name', 'Keywords');
            $all_kw = DB::doc2Arr($Settings->get('staff-keywords', []));
            sort($all_kw);
            $selected_kw = DB::doc2Arr($data['keywords'] ?? []);
        ?>
            <h2 class="title">
                <?= $kw_name ?>
            </h2>

            <div class="author-widget">
                <div class="author-list p-10" id="keyword-list">
                    <?php
                    $module_lst = [];
                    foreach ($selected_kw as $k) { ?>
                        <div class='author'>
                            <?= $k ?>
                            <input type='hidden' name='values[keywords][]' value='<?= $k ?>'>
                            <a onclick='$(this).parent().remove()'>&times;</a>
                        </div>
                    <?php } ?>
                </div>
                <div class="footer">
                    <div class="input-group small d-inline-flex w-auto">
                        <select class="form-control" id="keyword-select">
                            <option value="" disabled selected><?= lang('people.add_kw_name', replace: ['kw_name' => $kw_name]) ?></option>
                            <?php
                            foreach ($all_kw as $kw) {
                                // if (in_array($kw, $selected_kw)) continue;
                            ?>
                                <option><?= $kw ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn small primary" type="button" onclick="addKeyword();">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function addKeyword() {
                    var kw = $('#keyword-select').val();
                    console.log(kw);
                    if (kw.length === 0) return;
                    // check if already exists
                    if ($('#keyword-list').find(`input[value="${kw}"]`).length > 0) {
                        toastError('<?= lang('people.keyword_already_exists') ?>');
                        return;
                    }
                    var html = `<div class='author'>${kw} <input type='hidden' name='values[keywords][]' value='${kw}'> <a onclick='$(this).parent().remove()'>&times;</a></div>`;
                    $('#keyword-list').append(html);
                    // add hidden input to form
                    // $('#keyword-list').append(``);
                }
            </script>

        <?php } ?>



        <?php if ($active('expertise')) { ?>
            <h2 class="title">
                <?= lang('common.expertise') ?>
            </h2>
            <!-- ensure to save empty expertise -->
            <input type="hidden" name="values[expertise]" value="">
            <?php
            $expertise = $data['expertise'] ?? array();
            foreach ($expertise as $n) { ?>
                <div class="input-group d-inline-flex w-auto mr-5 mb-10">
                    <input type="text" name="values[expertise][]" value="<?= $n ?>" list="expertise-list" required class="form-control">
                    <div class="input-group-append">
                        <a class="btn" onclick="$(this).closest('.input-group').remove();">&times;</a>
                    </div>
                </div>
            <?php } ?>

            <button class="btn mb-10" type="button" onclick="addExpertise(event, this);">
                <i class="ph ph-plus"></i>
            </button>
            <datalist id="expertise-list">
                <?php
                foreach ($osiris->persons->distinct('expertise') as $d) { ?>
                    <option><?= $d ?></option>
                <?php } ?>
            </datalist>

            <script>
                function addExpertise(evt, el) {
                    var group = $('<div class="input-group d-inline-flex w-auto mr-5 mb-10"> ')
                    group.append('<input type="text" name="values[expertise][]" value="" list="expertise-list" required class="form-control">')
                    // var input = $()
                    var btn = $('<a class="btn">')
                    btn.on('click', function() {
                        $(this).closest('.input-group').remove();
                    })
                    btn.html('&times;')

                    group.append($('<div class="input-group-append">').append(btn))
                    // $(el).prepend(group);
                    $(group).insertBefore(el);
                }
            </script>


        <?php } ?>




        <?php if ($active('research')) { ?>
            <h2 class="title">
                <?= lang('common.research_interests') ?>
            </h2>

            <!-- ensure to save empty research interests -->
            <input type="hidden" name="values[research]" value="">
            <small class="text-muted">Max. 5</small><br>
            <table class="table">
                <thead>
                    <tr>
                        <th><label for="research" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label></th>
                        <th><label for="research_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="research-interests">
                    <?php
                    $data['research_de'] = $data['research_de'] ?? array();
                    foreach (($data['research'] ?? array()) as $i => $n) {
                        $n_de = $data['research_de'][$i] ?? '';
                    ?>
                        <tr class="research-interest">
                            <td>
                                <input type="text" name="values[research][]" value="<?= $n ?>" list="research-list" required class="form-control">
                            </td>
                            <td>
                                <input type="text" name="values[research_de][]" value="<?= $n_de ?>" list="research-list-de" class="form-control">
                            </td>
                            <td><a class="btn text-danger" onclick="$(this).closest('.research-interest').remove();"><i class="ph ph-trash"></i></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <button class="btn" type="button" onclick="addResearchInterest(event);">
                <i class="ph ph-plus"></i>
            </button>

            <datalist id="research-list">
                <?php
                foreach ($osiris->persons->distinct('research') as $d) { ?>
                    <option><?= $d ?></option>
                <?php } ?>
            </datalist>
            <datalist id="research-list-de">
                <?php
                foreach ($osiris->persons->distinct('research_de') as $d) { ?>
                    <option><?= $d ?></option>
                <?php } ?>
            </datalist>


        <?php } ?>


        <?php if ($active('research_profile')) { ?>
            <h2 class="title"><?= lang('people.research_profile_user_editor') ?></h2>

            <div class="row row-eq-spacing">
                <div class="col-md-6">
                    <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="research_profile-editor-quill"><?= $data['research_profile'] ?? '' ?></div>
                        <textarea name="values[research_profile]" id="research_profile-editor" class="d-none" readonly><?= $data['research_profile'] ?? '' ?></textarea>
                        <script>
                            quillEditor('research_profile-editor');
                        </script>
                    </div>

                </div>
                <div class="col-md-6">
                    <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="research_profile_de-editor-quill"><?= $data['research_profile_de'] ?? '' ?></div>
                        <textarea name="values[research_profile_de]" id="research_profile_de-editor" class="d-none" readonly><?= $data['research_profile_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('research_profile_de-editor');
                        </script>
                    </div>

                </div>
            </div>
        <?php } ?>

    </section>



    <section id="biography" style="display:none">

        <?php if ($active('cv')) { ?>
            <h2 class="title"><?= lang('common.curriculum_vitae') ?></h2>

            <!-- ensure to save empty cv -->
            <input type="hidden" name="values[cv]" value="">

            <button class="btn" type="button" onclick="addCVrow(event, '#cv-list')"><i class="ph ph-plus text-success"></i> <?= lang('common.add_entry') ?></button>
            <br>
            <small class="text-muted float-right"><?= lang('people.sorting_will_be_done_automatically') ?></small>
            <br>
            <div id="cv-list" class="w-800 mw-full">
                <?php
                if (isset($data['cv']) && !empty($data['cv'])) {

                    foreach ($data['cv'] as $i => $con) {
                        // try to fix dates saved in old format (array instead of string with month and year)
                        // if (!isset($con['from']) || is_null($con['from']['year'] ?? 'not set')) {
                        //     $con['from'] = '';
                        // }
                        // if (!is_string($con['from'] ?? null) && isset($con['from']['year'])) {
                        //     $con['from'] = ($con['from']['year'] ?? '') . '-' . str_pad(($con['from']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
                        // }
                        // if (!isset($con['to']) || is_null($con['to']['year'] ?? 'not set')) {
                        //     $con['to'] = '';
                        // }
                        // if (!is_string($con['to'] ?? null) && isset($con['to']['year'])) {
                        //     $con['to'] = ($con['to']['year'] ?? '') . '-' . str_pad(($con['to']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
                        // }

                ?>

                        <div class="alert mb-10">
                            <div class="input-group my-10">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?= lang('common.from') ?>*</span>
                                </div>
                                <input type="month" name="values[cv][<?= $i ?>][from]" id="from-<?= $i ?>" value="<?= $con['from'] ?? '' ?>" class="form-control month-field" placeholder="<?= lang('people.yyyy_mm') ?> *" required>
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?= lang('common.to') ?></span>
                                </div>
                                <input type="month" name="values[cv][<?= $i ?>][to]" id="to-<?= $i ?>" value="<?= $con['to'] ?? '' ?>" class="form-control month-field" placeholder="<?= lang('people.yyyy_mm') ?>">
                            </div>

                            <div class="form-group mb-10">
                                <input name="values[cv][<?= $i ?>][position]" type="text" class="form-control" value="<?= $con['position'] ?? '' ?>" placeholder="Position *" required>
                            </div>
                            <div class="form-group mb-0">
                                <input name="values[cv][<?= $i ?>][affiliation]" type="text" class="form-control" value="<?= $con['affiliation'] ?? '' ?>" placeholder="Affiliation *" list="affiliation-list" required>
                            </div>

                            <small class="text-muted">* <?= lang('people.required') ?></small><br>

                            <!-- checkbox to hide from portfolio -->

                            <?php if ($Settings->featureEnabled('portal')) { ?>
                                <div class="custom-checkbox ml-10">
                                    <input type="checkbox" id="hide-<?= $i ?>" <?= ($con['hide'] ?? false) ? 'checked' : '' ?> name="values[cv][<?= $i ?>][hide]">
                                    <label for="hide-<?= $i ?>">
                                        <?= lang('people.hide_in_portfolio') ?>
                                    </label>
                                </div>
                            <?php } ?>

                            <button class="btn danger my-10" type="button" onclick="$(this).closest('.alert').remove()"><i class="ph ph-trash"></i></button>
                        </div>
                <?php }
                } ?>
            </div>

            <script>
                var i = <?= $i ?? 0 ?>;

                var CURRENTYEAR = <?= CURRENTYEAR ?>;

                function addCVrow(evt, parent) {
                    i++;
                    var el = `
            <div class="alert mb-10">
                    <div class="input-group my-10">
                        <div class="input-group-prepend">
                            <span class="input-group-text">${lang('common.from')}*</span>
                        </div>
                        <input type="month" name="values[cv][${i}][from]" class="form-control" placeholder="<?= lang('people.yyyy_mm') ?> *" required>
                        <div class="input-group-prepend">
                            <span class="input-group-text">${<?= json_encode(lang('common.to'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>}</span>
                        </div>
                        <input type="month" name="values[cv][${i}][to]" class="form-control" placeholder="<?= lang('people.yyyy_mm') ?>">
                    </div>

                    <div class="form-group mb-10">
                        <input name="values[cv][${i}][position]" type="text" class="form-control" placeholder="Position *" required>
                    </div>

                    <div class="form-group mb-0">
                        <input name="values[cv][${i}][affiliation]" type="text" class="form-control" placeholder="Affiliation *" list="affiliation-list" required>
                    </div>

                    <small class="text-muted">* <?= lang('people.required') ?></small><br>

                    <button class="btn danger my-10" type="button" onclick="$(this).closest('.alert').remove()"><i class="ph ph-trash"></i></button>
                </div>
                `;
                    $(parent).prepend(el);
                }

                function areAllMonthValid() {
                    let allValid = true;
                    $('.month-field').each(function() {
                        const value = $(this).val();

                        if (value.length > 0 && !/^\d{4}-\d{2}$/.test(value)) {
                            allValid = false;
                            return false; // break the loop
                        }
                    });
                    return allValid;
                }

                function validateFeedback(item = null, pass = false) {
                    if (item !== null) {
                        $(item).toggleClass('is-invalid', !pass);
                    }
                    $('.btn[type="submit"]').prop('disabled', !areAllMonthValid());
                }

                // when month field is blurred, check if month is correctly formatted, if not, try to fix it, otherwise give an error
                $(document).on('blur', '.month-field', function() {
                    var val = $(this).val();
                    if (val.length == 0) {
                        validateFeedback(this, true);
                        return;
                    }
                    // check if value is in format YYYY-MM
                    if (!/^\d{4}-\d{2}$/.test(val)) {
                        // try to fix common mistakes like YYYY/MM or MM/YYYY
                        var fixed = val.replace('/', '-');
                        if (/^\d{4}-\d{2}$/.test(fixed)) {
                            validateFeedback(this, true);
                            $(this).val(fixed);
                            return;
                        }
                        fixed = val.replace('/', '-').split('-').reverse().join('-');
                        if (/^\d{4}-\d{2}$/.test(fixed)) {
                            validateFeedback(this, true);
                            $(this).val(fixed);
                            return;
                        }
                        validateFeedback(this, false);
                        toastError('<?= lang('people.please_enter_a_valid_month_in_the_format_yyyy_mm') ?>');
                    } else {
                        validateFeedback(this, true);
                    }
                });
            </script>


            <datalist id="affiliation-list">
                <?php
                foreach ($osiris->persons->distinct('cv.affiliation') as $d) { ?>
                    <option><?= $d ?></option>
                <?php } ?>
            </datalist>
        <?php } ?>



        <?php if ($active('biography')) { ?>
            <h2 class="title"><?= lang('common.biography') ?></h2>

            <div class="row row-eq-spacing my-0">
                <div class="col-md-6">
                    <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="biography-editor-quill"><?= $data['biography'] ?? '' ?></div>
                        <textarea name="values[biography]" id="biography-editor" class="d-none" readonly><?= $data['biography'] ?? '' ?></textarea>
                        <script>
                            quillEditor('biography-editor');
                        </script>
                    </div>

                </div>
                <div class="col-md-6">
                    <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="biography_de-editor-quill"><?= $data['biography_de'] ?? '' ?></div>
                        <textarea name="values[biography_de]" id="biography_de-editor" class="d-none" readonly><?= $data['biography_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('biography_de-editor');
                        </script>
                    </div>

                </div>
            </div>
        <?php } ?>



        <?php if ($active('education')) { ?>
            <h2><?= lang('common.education_profile') ?></h2>

            <div class="row row-eq-spacing my-0">
                <div class="col-md-6">
                    <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="education-editor-quill"><?= $data['education'] ?? '' ?></div>
                        <textarea name="values[education]" id="education-editor" class="d-none" readonly><?= $data['education'] ?? '' ?></textarea>
                        <script>
                            quillEditor('education-editor');
                        </script>
                    </div>

                </div>
                <div class="col-md-6">
                    <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                    <div class="form-group mb-0">
                        <div id="education_de-editor-quill"><?= $data['education_de'] ?? '' ?></div>
                        <textarea name="values[education_de]" id="education_de-editor" class="d-none" readonly><?= $data['education_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('education_de-editor');
                        </script>
                    </div>

                </div>
            </div>
        <?php } ?>

    </section>


    <br>
    <button type="submit" class="btn secondary" onclick="return validateUserForm(event);">
        Update
    </button>

</form>
