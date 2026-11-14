<?php global $osiris; ?>
<div class="data-module col-sm-<?= $width ?>" data-module="infrastructure-collaborative">
        <h5>
            <?= e($label) ?>
        </h5>

        <?php
        $collaborative = $this->form['collaborative'] ?? false;
        ?>
        <div class="form-group">
            <label for="collaborative">
                <?= lang('infrastructures.is_this_a_collaborative_research_infrastructure') ?>

            </label>
            <div>
                <input type="radio" name="values[collaborative]" id="collaborative-yes" value="yes" <?= ($collaborative) ? 'checked' : '' ?>>
                <label for="collaborative-yes">Yes</label>
                <input type="radio" name="values[collaborative]" id="collaborative-no" value="no" <?= (!$collaborative) ? 'checked' : '' ?>>
                <label for="collaborative-no">No</label>
            </div>

            <div id="form-collaborative" style="display: <?= ($collaborative) ? 'block' : 'none' ?>;" class="box padded">

                <?php
                $collab = $this->form['collaborators'] ?? [];
                $institute = $Settings->get('affiliation_details');
                $institute_name = $institute['name'] ?? $institute['id'] ?? 'Your Institute';
                $coordinator_institute = $this->form['coordinator_institute'] ?? empty($this->form['coordinator_organization']);
                $coordinator_organization = $this->form['coordinator_organization'] ?? null;
                ?>
                <div class="form-group my-10">
                    <label for="collaborators">
                        <?= lang('infrastructures.cooperation_partners') ?>

                    </label>
                    <table class="table simple">
                        <thead>
                            <tr>
                                <th><?= lang('common.name') ?></th>
                                <th><?= lang('common.coordinator') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="collaborators">
                            <tr>
                                <td>
                                    <b><?= $institute_name ?></b><br>
                                    <small class="text-muted"><?= lang('infrastructures.this_is_your_institute') ?></small>
                                </td>
                                <td>
                                    <div class="custom-radio">
                                        <input type="radio" name="values[coordinator]" id="coordinator" value="0" <?= ($coordinator_institute) ? 'checked' : '' ?>>
                                        <label for="coordinator" class="empty"></label>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                            <?php
                            $collaborators = $this->form['collaborators'] ?? [];
                            foreach ($collaborators as $org_id) {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (empty($collab)) continue;
                                $is_coord = ($coordinator_organization == $collab['_id']);
                            ?>
                                <tr data-row="<?= $org_id ?>">
                                    <td>
                                        <?= $collab['name'] ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= $collab['location'] ?? null ?>
                                        </small>
                                        <input type="hidden" name="values[collaborators][]" value="<?= $org_id ?>" class="form-control">
                                    </td>
                                    <td>
                                        <div class="custom-radio">
                                            <input type="radio" name="values[coordinator]" id="coordinator-<?= $org_id ?>" value="<?= $org_id ?>" <?= ($is_coord) ? 'checked' : '' ?> required>
                                            <label for="coordinator-<?= $org_id ?>" class="empty"></label>
                                        </div>
                                    </td>
                                    <td><button type="button" class="btn danger remove-collab" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="form-group mt-20 box padded bg-light">
                        <label for="organization-search"><?= lang('infrastructures.add_cooperation_partner') ?></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="organization-search" onkeydown="handleKeyDown(event)" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn" type="button" onclick="getOrganization($('#organization-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                            </div>
                        </div>
                        <p id="search-comment"></p>
                        <table class="table simple">
                            <tbody id="organization-suggest">
                            </tbody>
                        </table>
                        <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                        <p>
                            <?php
                            if ($Settings->hasPermission('organizations.edit')) {
                                echo lang('forms.organization_not_found_add_new', replace:[
                                    'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/organizations/new">' . lang('forms.organization_not_found_add_new_link') . '</a>')
                                ]);
                            } else {
                                echo lang('forms.organization_not_found_contact', replace:[
                                    'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/user/browse?permission=organizations.edit">' . lang('forms.organization_not_found_contact_link') . '</a>')
                                ]);
                            } ?>
                        </p>
                        <script>
                            function handleKeyDown(event) {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    getOrganization($('#organization-search').val());
                                }
                            }
                        </script>
                    </div>

                </div>
            </div>

        </div>
        <script>
            document.getElementById('collaborative-yes').addEventListener('change', function() {
                document.getElementById('form-collaborative').style.display = 'block';
                // enable required for all collaborators
                const inputs = document.querySelectorAll('#collaborators input[type="radio"]');
                inputs.forEach(input => {
                    input.required = true;
                });
            });
            document.getElementById('collaborative-no').addEventListener('change', function() {
                document.getElementById('form-collaborative').style.display = 'none';
                // disable required for all collaborators
                const inputs = document.querySelectorAll('#collaborators input[type="radio"]');
                inputs.forEach(input => {
                    input.required = false;
                });
            });
        </script>

</div>
