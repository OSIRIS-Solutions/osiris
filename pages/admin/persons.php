<?php

/**
 * Manage person data
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
    <i class="ph-duotone ph-user"></i>
    <?= lang('admin.person_data') ?>
</h1>


<?php
$persons = $osiris->adminPersons->find();
?>

<div class="row row-eq-spacing">
    <div class="col-md-9">

        <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
            <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/persons">

            <div class="box padded">
                <h2 class="title" id="general">
                    <i class="ph-duotone ph-gear" aria-hidden="true"></i>
                    <?= lang('common.general_settings') ?>
                </h2>
                <div class="form-group">
                    <label for="" class="font-weight-bold">
                        <?= lang('admin.coins') ?>
                    </label>
                    <?php
                    $coins = $Settings->featureEnabled('coins');
                    ?>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="coins-true" value="1" name="features[coins]" <?= $coins ? 'checked' : '' ?>>
                        <label for="coins-true"><?= lang('common.enabled_features') ?></label>
                    </div>

                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="coins-false" value="0" name="features[coins]" <?= $coins ? '' : 'checked' ?>>
                        <label for="coins-false"><?= lang('common.disabled_features') ?></label>
                    </div>

                    <small class="d-block text-muted">
                        <?= lang('admin.coins_are_not_saved_anywhere_but_are_calculated_on_demand_if_you_deactivate') ?>
                    </small>

                </div>

                <div class="form-group">
                    <label for="" class="font-weight-bold">
                        <?= lang('common.achievements') ?>
                    </label>
                    <?php
                    $achievements = $Settings->featureEnabled('achievements');
                    ?>

                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="achievements-true" value="1" name="features[achievements]" <?= $achievements ? 'checked' : '' ?>>
                        <label for="achievements-true"><?= lang('common.enabled_features') ?></label>
                    </div>

                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="achievements-false" value="0" name="features[achievements]" <?= $achievements ? '' : 'checked' ?>>
                        <label for="achievements-false"><?= lang('common.disabled_features') ?></label>
                    </div>

                </div>


                <div class="form-group">
                    <label for="" class="font-weight-bold">
                        <?= lang('admin.user_profile_metrics') ?>
                    </label>
                    <?php
                    $user_metrics = $Settings->featureEnabled('user-metrics');
                    ?>

                    <div class=" custom-radio d-inline-block ml-10">>
                        <input type="radio" id="user-metrics-true" value="1" name="features[user-metrics]" <?= $user_metrics ? 'checked' : '' ?>>
                        <label for="user-metrics-true"><?= lang('common.enabled_features') ?></label>
                    </div>

                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="user-metrics-false" value="0" name="features[user-metrics]" <?= $user_metrics ? '' : 'checked' ?>>
                        <label for="user-metrics-false"><?= lang('common.disabled_features') ?></label>
                    </div>

                    <small class="d-block text-muted">
                        <?= lang('admin.if_this_function_is_switched_off_user_metrics_graphs_are_only_visible_on_yo') ?>
                    </small>

                </div>

                <div class="form-group">
                    <label for="" class="font-weight-bold">
                        <?= lang('admin.profile_images') ?>
                    </label>
                    <?php
                    $db_pictures = $Settings->featureEnabled('db_pictures');
                    ?>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="db_pictures-true" value="1" name="features[db_pictures]" <?= $db_pictures ? 'checked' : '' ?>>
                        <label for="db_pictures-true"><?= lang('admin.save_in_database') ?></label>
                    </div>

                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="db_pictures-false" value="0" name="features[db_pictures]" <?= $db_pictures ? '' : 'checked' ?>>
                        <label for="db_pictures-false"><?= lang('admin.save_in_file_system') ?></label>
                    </div>

                    <small class="d-block text-muted">
                        <?= lang('admin.saving_the_profile_pictures_in_the_database_is_recommended_if_the_pictures') ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="" class="font-weight-bold">
                        <?= lang('admin.contact_button_in_user_profiles') ?>
                    </label>
                    <?php
                    $contactButton = $Settings->featureEnabled('contact-button');
                    ?>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="contact-button-true" value="1" name="features[contact-button]" <?= $contactButton ? 'checked' : '' ?>>
                        <label for="contact-button-true"><?= lang('common.enabled_features') ?></label>
                    </div>
                    <div class="custom-radio d-inline-block ml-10">
                        <input type="radio" id="contact-button-false" value="0" name="features[contact-button]" <?= $contactButton ? '' : 'checked' ?>>
                        <label for="contact-button-false"><?= lang('common.disabled_features') ?></label>
                    </div>
                    <small class="d-block text-muted">
                        <?= lang('admin.if_this_function_is_activated_a_contact_button_will_be_displayed_in_the_use') ?>
                    </small>
                </div>
                <script>
                    $(document).ready(function() {
                        if ($('#contact-button-true').is(':checked')) {
                            $('#contact-button-allowed').show();
                        } else {
                            $('#contact-button-allowed').hide();
                        }

                        $('#contact-button-true').on('change', function() {
                            $('#contact-button-allowed').show();
                        });
                        $('#contact-button-false').on('change', function() {
                            $('#contact-button-allowed').hide();
                        });
                    });
                </script>
                <?php
                $standardContactTypes = ['mail' => "0", 'slack' => "0", 'teams' => "0", 'matrix' => "0", 'other' => "0"];
                $contactSettings = $Settings->get('contact-button')->getArrayCopy();
                $contactTypes = array_merge($standardContactTypes, $contactSettings ?? []);
                ?>
                <div class="form-group" style="display: none;" id="contact-button-allowed">
                    <table class="table simple w-full small mb-10">
                        <thead>
                            <tr>
                                <th><?= lang('common.active') ?></th>
                                <th><?= lang('common.contact_type') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($contactTypes as $type => $enabled) {
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="general[contact-button][<?= $type ?>]" id="contact-button-<?= $type ?>" value="1" <?= $enabled ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <?= ucfirst($type) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                


                <?php if (strtoupper(USER_MANAGEMENT) !== 'AUTH') { ?>
                    <div class="form-group">
                        <label for="">
                            <?= lang('admin.ldap_user_synchronization') ?>
                        </label>
                        <?php
                        $sync = $Settings->featureEnabled('ldap-sync');
                        ?>

                        <div class="form-">
                            <label for="ldap-sync-blacklist"><?= lang('admin.username_blacklist_separated_by_comma') ?></label>
                            <textarea class="form-control small" name="general[ldap-sync-blacklist]" id="ldap-sync-blacklist"><?= $Settings->get('ldap-sync-blacklist') ?></textarea>
                        </div>
                        <div class="form-">
                            <label for="ldap-sync-whitelist"><?= lang('admin.username_whitelist_separated_by_comma') ?></label>
                            <textarea class="form-control small" name="general[ldap-sync-whitelist]" id="ldap-sync-whitelist"><?= $Settings->get('ldap-sync-whitelist') ?></textarea>
                        </div>

                    </div>
                <?php } ?>

            </div>



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


            <div class="box padded">
                <h2 class="title" id="datafields">
                    <i class="ph-duotone ph-database" aria-hidden="true"></i>
                    <?= lang('common.data_fields') ?>
                </h2>

                <table class="table simple w-auto small mb-10">
                    <thead>
                        <tr>
                            <th><?= lang('common.active') ?></th>
                            <th><?= lang('common.field_name') ?></th>
                        </tr>
                    </thead>
                    <tbody id="data-fields">
                        <?php foreach ($fields as $field) { ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="general[person-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
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
                                        <?= lang('common.custom_fields') ?>
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
                                        <b><?= e(lang($field['name'], $field['name_de'] ?? null)) ?></b>
                                        <code class="code mx-10"><?= e($field['format']) ?></code>
                                        <a href="<?= ROOTPATH ?>/admin/fields/<?= $field['id'] ?>">
                                            <i class="ph ph-pencil" title="<?= lang('action.edit') ?>"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } ?>
                    </tbody>
                </table>

            </div>






            <div class="box padded">

                <h2 class="title" id="positions">
                    <i class="ph-duotone ph-tree-view" aria-hidden="true"></i>
                    <?= lang('admin.possible_positions') ?>
                </h2>

                <p>
                    <?= lang('admin.define_the_fields_that_are_used_as_position_for_the_staff_members') ?>
                </p>

                <?php
                $staff = $Settings->get('staff');
                $staffPos = $staff['positions'] ?? [];
                $staffFree = $staff['free'] ?? true;
                ?>


                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="staff[free]" id="free-1" value="1" <?= $staffFree ? 'checked' : '' ?>>
                        <label for="free-1"><?= lang('admin.free_text') ?></label>
                    </div>
                    <small class="d-block text-muted">
                        <?= lang('admin.if_this_option_is_selected_the_staff_members_can_enter_their_own_position_f') ?>
                    </small>
                </div>
                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="staff[free]" id="free-0" value="0" <?= !$staffFree ? 'checked' : '' ?>>
                        <label for="free-0"><?= lang('admin.defined_selection') ?></label>
                    </div>
                    <small class="d-block text-muted">
                        <?= lang('admin.if_this_option_is_selected_the_staff_members_can_only_select_their_position') ?>
                    </small>
                </div>

                <hr>

                <h5>
                    <?= lang('admin.defined_list_of_positions') ?>:
                </h5>
                <small class="text-muted">
                    <?= lang('admin.this_list_will_only_be_used_if_you_select_defined_selection_above') ?>
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

            </div>


            <div class="box padded">
                <h2 class="title" id="keywords">
                    <i class="ph-duotone ph-tag" aria-hidden="true"></i>
                    <?= lang('admin.keywords_persons') ?>
                </h2>

                <p>
                    <?= lang('admin.define_keywords_that_the_staff_members_can_use_whether_this_field_is_displa') ?>
                </p>

                <!-- input for name of this keyword -->
                <div class="form-group">
                    <label for="keyword-name" class="font-weight-bold">
                        <?= lang('admin.name_of_the_keyword_field') ?>
                    </label>
                    <input type="text" name="general[staff-keyword-name]" id="keyword-name" class="form-control" value="<?= $Settings->get('staff-keyword-name', 'Keywords') ?>">
                </div>

                <?php
                $keywords = DB::doc2Arr($Settings->get('staff-keywords', []));
                ?>
                <div class="form-group">
                    <label for="staff-keywords" class="font-weight-bold">
                        <?= lang('admin.defined_list_of_keywords') ?>:
                    </label>
                    <small class="d-block text-muted">
                        <?= lang('admin.define_a_list_of_keywords_that_the_staff_members_can_use_each_keyword_shoul') ?>
                    </small>
                    <textarea name="general[staff-keywords]" id="staff-keywords" class="form-control" rows="10"><?= implode(PHP_EOL, $keywords) ?></textarea>
                </div>
            </div>

            <div class="bottom-buttons">

                <button class="btn success large">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('action.save') ?>
                </button>

                <a class="btn light large" href="<?= ROOTPATH ?>/admin/persons">
                    <i class="ph ph-x"></i>
                    <?= lang('action.cancel') ?>
                </a>

            </div>
        </form>
    </div>


    <div class="col-md-3 d-none d-md-block">
        <nav class="on-this-page-nav">
            <div class="content">
                <div class="title"><?= lang('admin.features') ?></div>

                <a href="#general"><?= lang('common.general_settings') ?></a>
                <a href="#datafields"><?= lang('common.data_fields') ?></a>
                <a href="#positions"><?= lang('admin.possible_positions') ?></a>
                <a href="#keywords"><?= lang('admin.keywords_persons') ?></a>
            </div>
        </nav>
    </div>

</div>


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
</script>
