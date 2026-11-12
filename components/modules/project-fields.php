<?php
/** Original project inputs from pages/proposals/edit.php, adapted only to Modules. */
$required = $req || !empty($props['required']);
$floating = in_array($module, ['acronym', 'submission_date', 'approval_date', 'rejection_date', 'purpose', 'internal_number', 'funder', 'funding_program', 'funding_program_select', 'funding_number', 'coordinator', 'project_type', 'grant_sum', 'grant_income', 'grant_subproject', 'grant_sum_proposed', 'grant_income_proposed', 'grant_subproject_proposed', 'comment', 'personnel', 'in-kind', 'institute-role'], true);
$customLabel = (!empty($props['label']) || !empty($props['label_de'])) && $label !== translate($m['label']);
$form = $this->form;
$DB = $this->DB;
$osiris = $DB->db;
$val = function ($key, $default = '') use ($form) {
    $value = $form[$key] ?? $default;
    if ($key === 'funding_number' && (is_array($value) || $value instanceof Traversable)) return e(implode(', ', DB::doc2Arr($value)));
    return is_string($value) ? e($value) : $value;
};
$sel = fn($key, $value) => ($form[$key] ?? null) == $value ? 'selected' : '';
$reqField = fn($field) => $required ? 'required' : '';
if ($module === 'topics' && !$Settings->featureEnabled('topics')) return;
if ($module === 'nagoya' && !$Settings->featureEnabled('nagoya')) return;
?>
<div class="data-module <?= $floating ? 'floating-form' : '' ?> col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
<?php if ($customLabel && !$floating && !in_array($module, ['topics', 'kdsf-ffk'], true)): ?><label class="floating-title <?= $labelClass ?>"><?= e($label) ?></label><?php endif; ?>
<?php switch ($module):
case 'acronym': ?>


                        <input type="text" class="form-control" name="values[acronym]" id="acronym" value="<?= $val('acronym') ?>" maxlength="100" placeholder="Short title" <?= $reqField('acronym') ?>>
                        <label for="acronym" class="<?= $reqField('acronym') ?>"><?= e($label) ?></label>


<?php break;
case 'submission_date': ?>




                    <input type="date" class="form-control large" name="values[submission_date]" id="submission_date" value="<?= $val('submission_date', date('Y-m-d')) ?>" required placeholder=" ">
                    <label for="submission_date" class="required"><?= e($label) ?></label>


<?php break;
case 'approval_date': ?>




                    <input type="date" class="form-control large" name="values[approval_date]" id="approval_date" value="<?= $val('approval_date', date('Y-m-d')) ?>" required placeholder=" ">
                    <label for="approval_date" class="required"><?= e($label) ?></label>


<?php break;
case 'rejection_date': ?>




                    <input type="date" class="form-control large" name="values[rejection_date]" id="rejection_date" value="<?= $val('rejection_date', date('Y-m-d')) ?>" required placeholder=" ">
                    <label for="rejection_date" class="required"><?= e($label) ?></label>


<?php break;
case 'purpose': ?>


                        <select class="form-control" name="values[purpose]" id="purpose" <?= $reqField('purpose') ?> autocomplete="off" placeholder=" ">
                            <?php if (isset($form['_id']) && empty($form['purpose'])): ?><option value="" selected></option><?php endif; ?>
                            <?php
                            $vocab = $Vocabulary->getValues('project-purpose');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= $sel('purpose', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="purpose" class="<?= $reqField('purpose') ?>"><?= e($label) ?></label>


<?php break;
case 'internal_number': ?>


                        <input type="text" class="form-control" name="values[internal_number]" id="internal_number" value="<?= $val('internal_number') ?>" placeholder="1234" <?= $reqField('internal_number') ?>>

                        <label for="internal_number" class="<?= $reqField('internal_number') ?>"><?= e($label) ?></label>


<?php break;
case 'funder': ?>


                        <select class="form-control" name="values[funder]" value="<?= $val('funder') ?>" <?= $reqField('funder') ?> id="funder" placeholder=" ">
                            <?php if (isset($form['_id']) && empty($form['funder'])): ?><option value="" selected></option><?php endif; ?>
                            <?php
                            $vocab = $Vocabulary->getValues('funder');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= $sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funder" class="<?= $reqField('funder') ?>"><?= e($label) ?></label>


<?php break;
case 'funding_program': ?>


                        <input type="text" class="form-control" name="values[funding_program]" value="<?= $val('funding_program') ?>" id="funding_program" placeholder="Funding program">
                        <label for="funding_program" class="<?= $reqField('funding_program') ?>"><?= e($label) ?></label>


<?php break;
case 'funding_program_select': ?>



                        <select class="form-control" name="values[funding_program_select]" id="funding_program_select" <?= $reqField('funding_program_select') ?> placeholder=" ">
                            <?php
                            if ($reqField('funding_program_select') == '' || (isset($form['_id']) && empty($form['funding_program_select']))) { ?>
                                <option value=""><?= lang('projects.select_funding_program') ?></option>
                            <?php }
                            $vocab = $Vocabulary->getValues('funding-program');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= $sel('funding_program_select', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funding_program_select" class="<?= $reqField('funding_program_select') ?>"><?= e($label) ?></label>


<?php break;
case 'funding_number': ?>


                        <input type="text" class="form-control" name="values[funding_number]" value="<?= $val('funding_number') ?>" id="funding_number" placeholder="ABC123">
                        <label for="funding_number" class="<?= $reqField('funding_number') ?>"><?= e($label) ?></label>
                        <small class="text-muted"><?= lang('projects.multiple_seperated_by_comma') ?></small>


<?php break;
case 'coordinator': ?>


                            <input type="text" class="form-control" <?= $reqField('coordinator') ?> name="values[coordinator]" id="coordinator" value="<?= $val('coordinator', isset($form['_id']) ? '' : $Settings->get('affiliation')) ?>" placeholder="Institute of XYZ">
                            <label for="coordinator" class="<?= $reqField('coordinator') ?>"><?= e($label) ?></label>


<?php break;
case 'project_type': ?>


                        <select class="form-control" name="values[project_type]" id="project_type" <?= $reqField('project_type') ?> placeholder=" ">
                            <?php if (isset($form['_id']) && empty($form['project_type'])): ?><option value="" selected></option><?php endif; ?>
                            <?php
                            $vocab = $Vocabulary->getValues('project-type');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= $sel('project_type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="project_type" class="<?= $reqField('project_type') ?>"><?= e($label) ?></label>


<?php break;
case 'grant_sum': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_sum') ?> name="values[grant_sum]" id="grant_sum" value="<?= $val('grant_sum') ?>" placeholder="1234">
                            <label for="grant_sum" class="<?= $reqField('grant_sum') ?>"><?php if ($customLabel): ?><?= e($label) ?><?php else: ?>
                                <?= lang('projects.grant_sum_edit') ?> (<?= lang('common.total') ?>)
                            <?php endif; ?></label>


<?php break;
case 'grant_income': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_income') ?> name="values[grant_income]" id="grant_income" value="<?= $val('grant_income') ?>" placeholder="1234">
                            <label for="grant_income" class="<?= $reqField('grant_income') ?>"><?= e($label) ?></label>


<?php break;
case 'grant_subproject': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_subproject') ?> name="values[grant_subproject]" id="grant_subproject" value="<?= $val('grant_subproject') ?>" placeholder="1234">
                            <label for="grant_subproject" class="<?= $reqField('grant_subproject') ?>"><?= e($label) ?></label>


<?php break;
case 'grant_sum_proposed': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_sum_proposed') ?> name="values[grant_sum_proposed]" id="grant_sum_proposed" value="<?= $val('grant_sum_proposed') ?>" placeholder="112345">
                            <label for="grant_sum_proposed" class="<?= $reqField('grant_sum_proposed') ?>"><?php if ($customLabel): ?><?= e($label) ?><?php else: ?>
                                <?= lang('projects.proposed_grant') ?> (<?= lang('common.total') ?>)
                            <?php endif; ?></label>


<?php break;
case 'grant_income_proposed': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_income_proposed') ?> name="values[grant_income_proposed]" id="grant_income_proposed" value="<?= $val('grant_income_proposed') ?>" placeholder="112345">

                            <label for="grant_income_proposed" class="<?= $reqField('grant_income_proposed') ?>"><?= e($label) ?></label>


<?php break;
case 'grant_subproject_proposed': ?>


                            <input type="text" step="1" class="form-control money-input" <?= $reqField('grant_subproject_proposed') ?> name="values[grant_subproject_proposed]" id="grant_subproject_proposed" value="<?= $val('grant_subproject_proposed') ?>" placeholder="112345">

                            <label for="grant_subproject_proposed" class="<?= $reqField('grant_subproject_proposed') ?>"><?= e($label) ?></label>


<?php break;
case 'public': ?>

                    <div class="form-group ">
                        <input type="hidden" name="values[public]" value="0">
                        <div class="custom-checkbox">
                            <input type="checkbox" id="public-check" <?= $val('public', false) ? 'checked' : '' ?> name="values[public]">
                            <label for="public-check">
                                <?= lang('projects.approval_of_the_internet_presentation_of_the_approved_project') ?>
                            </label>
                        </div>
                    </div>

<?php break;
case 'comment': ?>


                    <textarea name="values[comment]" id="comment" cols="30" rows="5" class="form-control" placeholder="Comment" <?= $reqField('comment') ?>><?= $val('comment') ?></textarea>
                    <label for="comment" class="<?= $reqField('comment') ?>"><?= e($label) ?></label>


<?php break;
case 'funding_organization': ?>
<?php
                    $org_id = $form['funding_organization'] ?? '';
                    if (is_array($org_id) || $org_id instanceof Traversable) $org_id = $org_id['$oid'] ?? '';
                    if (DB::is_ObjectID($org_id)) $org_id = DB::to_ObjectID($org_id);
                ?>
                    <a id="funding" class="box py-5 px-10 my-0 d-block colorless" href="#funding-org-modal">
                        <label for="funding_organization" class="floating-title <?= $reqField('funding_organization') ?>">
                            <?= lang('common.funding_organizations') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[funding_organization]" value="<?= e($org_id) ?>" <?= $reqField('funding_organization') ?> readonly />

                        <div id="funding-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= e(localized($collab['name'])) ?></b>
                                    <br><small class="text-muted"><?= e($collab['location']) ?></small>
                                <?php } else { ?>
                                    <?= lang('error.organization_select_missing') ?>:
                                    <br><small class="text-muted"><?= e($org_id) ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="funding-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="funding-search"><?= lang('projects.search_funding_organization') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="funding-search" onkeydown="selectOrgEvent(event, 'funding')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'funding')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="funding-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="funding-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>

                            </div>
                        </div>
                    </div>



<?php break;
case 'scholarship': ?>
<?php
                    // scholarship is a synonym for funding_organization
                    $org_id = $form['funding_organization'] ?? '';
                    if (is_array($org_id) || $org_id instanceof Traversable) $org_id = $org_id['$oid'] ?? '';
                    if (DB::is_ObjectID($org_id)) $org_id = DB::to_ObjectID($org_id); ?>

                    <a id="scholarship" class="box py-5 px-10 my-0 d-block colorless" href="#scholarship-org-modal">
                        <label for="funding_organization" class="floating-title <?= $reqField('scholarship') ?>">
                            <?= lang('projects.scholarship_institution') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[funding_organization]" value="<?= e($org_id) ?>" <?= $reqField('funding_organization') ?> readonly />

                        <div id="scholarship-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= e(localized($collab['name'])) ?></b>
                                    <br><small class="text-muted"><?= e($collab['location']) ?></small>
                                <?php } else { ?>
                                    <?= lang('projects.no_organization_selected') ?>
                                    <br><small class="text-muted"><?= e($org_id) ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="scholarship-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="scholarship-search"><?= lang('projects.search_scholarship_institutions') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="scholarship-search" onkeydown="selectOrgEvent(event, 'scholarship')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'scholarship')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="scholarship-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="scholarship-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>

                            </div>
                        </div>
                    </div>

<?php break;
case 'university': ?>
<?php
                    $org_id = $form['university'] ?? '';
                    if (is_array($org_id) || $org_id instanceof Traversable) $org_id = $org_id['$oid'] ?? '';
                    if (DB::is_ObjectID($org_id)) $org_id = DB::to_ObjectID($org_id); ?>
                    <a id="university" class="box py-5 px-10 my-0 d-block colorless" href="#university-org-modal">
                        <label for="university" class="floating-title <?= $reqField('university') ?>">
                            <?= lang('projects.partner_university') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[university]" value="<?= e($org_id) ?>" <?= $reqField('university') ?> readonly />

                        <div id="university-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= e(localized($collab['name'])) ?></b>
                                    <br><small class="text-muted"><?= e($collab['location']) ?></small>
                                <?php } else { ?>
                                    <?= lang('error.organization_select_missing') ?>:
                                    <br><small class="text-muted"><?= e($org_id) ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="university-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="university-search"><?= lang('projects.search_for_partner_university') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="university-search" onkeydown="selectOrgEvent(event, 'university')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'university')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="university-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="university-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>

                            </div>
                        </div>
                    </div>

<?php break;
case 'joint_project': ?>

                    <fieldset class="mt-20">
                        <legend class="font-size-14"><?= lang('projects.joint_project') ?></legend>

                        <b>
                            <?= lang('projects.is_this_project_part_of_a_joint_project_with_other_institutions') ?>
                        </b>
                        <div class="custom-radio d-inline-block mr-10 joint-project-input">
                            <input type="radio" id="joint_project_yes" name="values[joint_project]" value="true" <?= $val('joint_project', false) ? 'checked' : '' ?>>
                            <label for="joint_project_yes">
                                <?= lang('common.yes') ?>
                            </label>
                        </div>
                        <div class="custom-radio d-inline-block mr-10 joint-project-input">
                            <input type="radio" id="joint_project_no" name="values[joint_project]" value="false" <?= !$val('joint_project', false) ? 'checked' : '' ?>>
                            <label for="joint_project_no">
                                <?= lang('common.no') ?>
                            </label>
                        </div>

                        <div id="joint-project-yes" style="display: <?= $val('joint_project', false) ? 'block' : 'none' ?>;">
                            <div class="form-group floating-form mt-20">
                                <input type="text" class="form-control" name="values[joint_project_identifier]" id="joint_project_identifier" value="<?= $val('joint_project_identifier') ?>" placeholder="ABC123">
                                <label for="joint_project_identifier" class="">
                                    <?= lang('projects.identifier_of_the_joint_project') ?>
                                </label>
                            </div>
                            <div class="form-group floating-form">
                                <input type="text" class="form-control" name="values[joint_project_title]" id="joint_project_title" value="<?= $val('joint_project_title') ?>" placeholder=" ">
                                <label for="joint_project_title" class="">
                                    <?= lang('projects.title_of_the_joint_project') ?>
                                </label>
                            </div>
                            <div class="form-group">
                                <!-- checkbox -->
                                <input type="hidden" name="values[joint_project_speaker]" value="false">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="joint_project_speaker" <?= $val('joint_project_speaker', false) ? 'checked' : '' ?> name="values[joint_project_speaker]" value="true">
                                    <label for="joint_project_speaker">
                                        <?= lang('projects.speaker_coordinator_consortium_leader_role') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <script>
                        $('.joint-project-input input').on('change', function() {
                            if (this.value === 'true') {
                                $('#joint-project-yes').show();
                            } else {
                                $('#joint-project-yes').hide();
                            }
                        });
                    </script>

<?php break;
case 'research-countries': ?><input type="hidden" name="values[research-countries][]" value="">
<?php
                $countries = $form['research-countries'] ?? [];
            ?>
                <h5>
                    <?= lang('projects.countries_you_will_do_research_on_in') ?>
                </h5>


                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('common.country') ?></th>
                            <th><?= lang('common.research') ?></th>
                            <th><?= lang('common.action') ?></th>
                        </tr>
                    </thead>
                    <tbody id="country-list">
                        <?php foreach ($countries as $country) {
                            if (empty($country) || !isset($country['iso'])) continue;
                            $iso = $country['iso'];
                            $role = $country['role'] ?? 'both';
                        ?>
                            <tr>
                                <td><?= $DB->getCountry($iso, lang('common.field_name_language')) ?></td>
                                <td><?= e($role) ?></td>
                                <td>
                                    <a onclick="$(this).closest('tr').remove()"><?= lang('action.remove') ?></a>
                                    <input type="text" name="values[research-countries][]" value="<?= e($iso) ?>;<?= e($role) ?>" hidden>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="input-group small d-inline-flex w-auto">
                                    <select id="add-research-country" class="form-control">
                                        <option value="" disabled checked><?= lang('common.please_select_a_country') ?></option>
                                        <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                                            <option value="<?= e($iso) ?>"><?= $name ?></option>
                                        <?php } ?>
                                    </select>
                                    <select id="add-research-country-role" class="form-control">
                                        <option value="" disabled checked><?= lang('projects.please_select_a_role') ?></option>
                                        <option value="in"><?= lang('projects.research_in_this_country') ?></option>
                                        <option value="about"><?= lang('projects.research_about_this_country') ?></option>
                                        <option value="both"><?= lang('projects.both') ?></option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn secondary" type="button" onclick="addResearchCountry(event);">
                                            <i class="ph ph-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <script>
                    function addResearchCountry(event) {
                        var el = $('#add-research-country')
                        var data = el.$val()
                        var type = $('#add-research-country-role').$val()
                        if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                            event.preventDefault();
                            if (data) {
                                let tr = $('<tr>')
                                tr.append('<td>' + el.find('option:selected').text() + '</td>')
                                tr.append('<td>' + type + '</td>')
                                tr.append('<td><a onclick="$(this).closest(\'tr\').remove()"><?= lang('action.remove') ?></a><input type="text" name="values[research-countries][]" value="' + data + ';' + type + '" hidden></td>')
                                $('#country-list').append(tr)
                            }
                            $(el).$val('')
                            return false;
                        }
                    }
                </script>

<?php break;
case 'nagoya': ?>


                <?php

                $nagoya = $form['nagoya'] ?? [];
                $countries = $nagoya['countries'] ?? [];
                $enabled = $nagoya['enabled'] ?? false;

                // if the form is not empty, and Nagoya was enabled just refer to the nagoya tab and do not show the fields here
                if (!empty($form) && $enabled && !empty($countries)) { ?>
                    <div class="alert alert-info">
                        <?= lang('projects.nagoya_protocol_settings_can_be_found_in_the_nagoya_protocol_tab_after_savi') ?>
                    </div>
                <?php
                } else {
                ?>

                    <div class="nagoya-container">
                        <label for="nagoya">
                            <?= lang('projects.do_you_plan_to_collect_obtain_or_utilise_genetic_resources_biological_sampl') ?>
                        </label>
                        <div>
                            <input type="radio" name="values[nagoya]" id="nagoya-yes" value="yes" <?= ($enabled) ? 'checked' : '' ?>>
                            <label for="nagoya-yes">Yes</label>
                            <input type="radio" name="values[nagoya]" id="nagoya-no" value="no" <?= (!$enabled) ? 'checked' : '' ?>>
                            <label for="nagoya-no">No</label>
                        </div>

                        <!-- <small class="text-muted">
                            <?= lang('projects.if_you_answer_yes_you_will_be_prompted_to_list_all_countries_from_which_you') ?>
                        </small> -->

                        <div id="ressource-nagoya" style="display: <?= ($enabled) ? 'block' : 'none' ?>;">

                            <b>
                                <?= lang('projects.please_list_all_countries') ?>
                            </b>

                            <div class="author-widget" id="author-widget">
                                <div class="author-list p-10" id="nagoya-countries-list">
                                    <?php
                                    $lang = lang('common.field_name_language');
                                    foreach ($countries as $country) {
                                        $iso = $country['code'] ?? '';
                                    ?>
                                        <div class='author'>
                                            <input type='hidden' name='values[nagoya_countries][]' value='<?= e($iso) ?>'>
                                            <?= $DB->getCountry($iso, $lang) ?>
                                            <a onclick="$(this).closest('.author').remove()">&times;</a>
                                        </div>
                                    <?php } ?>

                                </div>
                                <div class="footer">
                                    <div class="input-group sm d-inline-flex w-auto">
                                        <select id="add-nagoya-country">
                                            <option value="" disabled checked><?= lang('common.please_select_a_country') ?></option>
                                            <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                                                <option value="<?= e($iso) ?>"><?= $name ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn secondary h-full" type="button" onclick="addNagoyaCountry(event);">
                                                <i class="ph ph-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function addNagoyaCountry(event) {
                                        var el = $('#add-nagoya-country')
                                        var data = el.$val()
                                        if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                                            event.preventDefault();
                                            if (data) {
                                                $('#nagoya-countries-list').append('<div class="author"><input type="hidden" name="values[nagoya_countries][]" value="' + data + '">' + el.find('option:selected').text() + '<a onclick="$(this).closest(\'.author\').remove()">&times;</a></div>')
                                            }
                                            $(el).$val('')
                                            return false;
                                        }
                                    }
                                </script>

                            </div>
                        </div>


                        <script>
                            document.getElementById('nagoya-yes').addEventListener('change', function() {
                                document.getElementById('ressource-nagoya').style.display = 'block';
                            });
                            document.getElementById('nagoya-no').addEventListener('change', function() {
                                document.getElementById('ressource-nagoya').style.display = 'none';
                            });
                        </script>
                    </div>
                <?php } ?>

<?php break;
case 'ressources': ?>
<?php
                    $res = $form['ressources'] ?? [];
                    $material = ($res['material'] ?? 'no') === 'yes';
                    $personnel = ($res['personnel'] ?? 'no') === 'yes';
                    $room = ($res['room'] ?? 'no') === 'yes';
                    $other = ($res['other'] ?? 'no') === 'yes';
                ?>
                    <div class="ressources">
                        <div class="form-group">
                            <label for="ressource1">
                                <?= lang('projects.additional_material_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][material]" id="material-yes" value="yes" <?= $material ? 'checked' : '' ?>>
                                <label for="material-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][material]" id="material-no" value="no" <?= $material ? '' : 'checked' ?>>
                                <label for="material-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][material_details]" id="ressource-material" style="display: <?= $material ? 'block' : 'none' ?>;" placeholder="Details"><?= e($res['material_details'] ?? '') ?></textarea>
                            <script>
                                document.getElementById('material-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'block';
                                });
                                document.getElementById('material-no').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'none';
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label for="ressource2">
                                <?= lang('projects.additional_personnel_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-yes" value="yes" <?= $personnel ? 'checked' : '' ?>>
                                <label for="personnel-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-no" value="no" <?= $personnel ? '' : 'checked' ?>>
                                <label for="personnel-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][personnel_details]" id="ressource-personnel" style="display: <?= $personnel ? 'block' : 'none' ?>;" placeholder="Details"><?= e($res['personnel_details'] ?? '') ?></textarea>
                            <script>
                                document.getElementById('personnel-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'block';
                                });
                                document.getElementById('personnel-no').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'none';
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label for="ressource3">
                                <?= lang('projects.additional_room_capacities') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][room]" id="room-yes" value="yes" <?= $room ? 'checked' : '' ?>>
                                <label for="room-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][room]" id="room-no" value="no" <?= $room ? '' : 'checked' ?>>
                                <label for="room-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][room_details]" id="ressource-room" style="display: <?= $room ? 'block' : 'none' ?>;" placeholder="Details"><?= e($res['room_details'] ?? '') ?></textarea>
                            <script>
                                document.getElementById('room-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'block';
                                });
                                document.getElementById('room-no').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'none';
                                });
                            </script>

                        </div>
                        <div class="form-group mb-0">
                            <label for="ressource4">
                                <?= lang('projects.other_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][other]" id="other-yes" value="yes" <?= $other ? 'checked' : '' ?>>
                                <label for="other-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][other]" id="other-no" value="no" <?= $other ? '' : 'checked' ?>>
                                <label for="other-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][other_details]" id="ressource-other" style="display: <?= $other ? 'block' : 'none' ?>;" placeholder="Details"><?= e($res['other_details'] ?? '') ?></textarea>
                            <script>
                                document.getElementById('other-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'block';
                                });
                                document.getElementById('other-no').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'none';
                                });
                            </script>
                        </div>
                    </div>

<?php break;
case 'personnel': ?>

                        <textarea name="values[personnel]" id="personnel" cols="30" rows="2" class="form-control" placeholder="1 Doktorand:in"><?= $val('personnel') ?></textarea>

                        <label for="personnel"><?= e($label) ?></label>
                        <small class="text-muted">
                            <!-- Einstellungen/Verlängerungen in Personenmonaten & Kategorie -->
                            <?= lang('projects.hiring_extensions_in_person_months_category') ?>
                        </small>

<?php break;
case 'in-kind': ?>

                        <textarea name="values[in-kind]" id="in-kind" cols="30" rows="2" class="form-control" placeholder="Antragsteller 5%"><?= $val('in-kind') ?></textarea>

                        <label for="in-kind"><?= e($label) ?></label>
                        <small class="text-muted">
                            <?= lang('projects.informative_details_in_mentioning_the_collaborating_persons_e_g_applicant_1') ?>
                        </small>

<?php break;
case 'institute-role': ?>


                            <select class="form-control" name="values[role]" id="role" <?= $reqField('role') ?> placeholder=" ">
                            <?php if (isset($form['_id']) && empty($form['role'])): ?><option value="" selected></option><?php endif; ?>
                                <?php
                                $vocab = $Vocabulary->getValues('project-institute-role');
                                foreach ($vocab as $v) { ?>
                                    <option value="<?= $v['id'] ?>" <?= $sel('role', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                <?php } ?>
                            </select>
                            <label for="role" class="<?= $reqField('role') ?>"><?php if ($customLabel): ?><?= e($label) ?><?php else: ?>
                                <?= translate('projects.role_of') ?> <?= e($Settings->get('affiliation')) ?>
                            <?php endif; ?></label>


<?php break;
case 'kdsf-ffk':
    echo '<input type="hidden" name="values[kdsf-ffk][]" value="">';
    if ($customLabel) $kdsfLabel = $label;
    include BASEPATH . '/components/kdsf-ffk-select.php';
    break;
case 'topics':
    $Settings->topicChooser(DB::doc2Arr($form['topics'] ?? []), 'values[topics]', $customLabel ? $label : null);
    break;
case 'image':
    include BASEPATH . '/components/modules/project-image.php';
    break;
endswitch; ?>
<?= $this->render_help($help) ?>
</div>
<?php if (in_array($module, ['funding_organization', 'scholarship', 'university'], true)): ?>
<script>
(() => {
    if (!window.osirisOrganizationScript) {
        window.osirisOrganizationScript = new Promise((resolve, reject) => {
            if (typeof getOrganization === 'function') { resolve(); return; }
            const script = document.createElement('script');
            script.src = <?= json_encode(ROOTPATH . '/js/organizations.js?v=' . OSIRIS_BUILD) ?>;
            script.onload = resolve; script.onerror = reject; document.head.append(script);
        });
    }
    window.selectOrgEvent = function (event = null, type = 'scholarship') {
        if (event && event.key !== 'Enter') return;
        if (event) event.preventDefault();
        window.osirisOrganizationScript.then(() => {
            SUGGEST = $('#' + type + '-org-suggest');
            INPUT = $('#' + type + '-search');
            SELECTED = $('#' + type + '-org-value');
            COMMENT = $('#' + type + '-search-comment');
            window.createOrganizationTR = function (org) {
                const id = cleanID(org.id);
                const target = $('#' + type + '-org-value').empty();
                $('<b>').text(org.name).appendTo(target);
                target.append('<br>'); $('<small class="text-muted">').text(org.location || '').appendTo(target);
                $('#' + type + ' input').val(id);
                location.href = '#' + type;
            };
            getOrganization(INPUT.val());
        }).catch(() => toastError(<?= json_encode(lang('Unable to load organization search.', 'Die Organisationssuche konnte nicht geladen werden.')) ?>));
        return false;
    };
})();
</script>
<?php endif; ?>
