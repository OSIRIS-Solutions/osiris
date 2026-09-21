<style>
    .bottom-buttons {
        z-index: 90;
    }
    .collapse-panel {
        margin-bottom: 2rem;
    }

    .collapse-content {
        background-color: white;
    }

    .collapse-header {
        cursor: pointer;
        font-size: 1.8rem;
        font-weight: bold;
    }

    .collapse-header,
    .collapse-panel[open] .collapse-header {
        color: var(--primary-color);
        border-color: var(--primary-color);
        background-color: var(--primary-color-20);
    }

    .collapse-header:hover,
    .collapse-panel[open] .collapse-header:hover {
        background-color: var(--primary-color-30);
    }

    .collapse-panel.project-panel {
        --primary-color: #1c7d72;
        --primary-color-dark: #157065;
        --primary-color-20: #1c7d7233;
        --primary-color-30: #1c7d723d;
    }

    .collapse-panel.infrastructure-panel {
        --primary-color: #d48646;
        --primary-color-dark: #b36a36;
        --primary-color-20: #d4864633;
        --primary-color-30: #d486463d;
    }

    .collapse-panel.activity-panel {
        --primary-color: #9f4371;
        --primary-color-dark: #7a345a;
        --primary-color-20: #9f437133;
        --primary-color-30: #9f43713d;
    }


    .suggestions {
        color: #464646;
        /* position: absolute; */
        margin: 10px auto;
        top: 100%;
        left: 0;
        height: 19.2rem;
        overflow: auto;
        bottom: -3px;
        width: 100%;
        box-sizing: border-box;
        min-width: 12rem;
        background-color: white;
        border: var(--border-width) solid #afafaf;
        /* visibility: hidden; */
        /* opacity: 0; */
        z-index: 100;
        -webkit-transition: opacity 0.4s linear;
        transition: opacity 0.4s linear;
        border-radius: var(--border-radius);
    }

    .suggestions a {
        display: block;
        padding: 0.5rem;
        border-bottom: var(--border-width) solid #afafaf;
        color: #464646;
        text-decoration: none;
        width: 100%;
    }

    .suggestions a:hover {
        background-color: #f0f0f0;
    }
</style>


<div class="container" style="margin-bottom: 6rem;max-width: 80rem;">
    <a href="<?= ROOTPATH ?>/activities/view/<?= $id ?>" class="">
        <i class="ph ph-arrow-left"></i>
        <?= lang('activities.back_to_activity') ?>
    </a>

    <h1>
        <i class="ph-duotone ph-link" aria-hidden="true"></i>
        <?= lang('activities.edit_connections') ?>
    </h1>

    <form action="<?= ROOTPATH ?>/crud/activities/connections/<?= $id ?>" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/edit-connections/" . $id ?>">

        <?php if ($Settings->featureEnabled('projects')) { ?>
            <details class="collapse-panel project-panel" open>
                <summary class="collapse-header">
                    <?= lang('common.projects') ?>
                </summary>
                <div class="collapse-content">
                    <?php
                    $full_permission = $Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.connect');
                    $filter = [];
                    if (!$full_permission) {
                        // make sure to include currently selected projects
                        $filter = ['$or' => [['persons.user' => $_SESSION['username']], ['_id' => ['$in' => $activity['projects'] ?? []]]]];
                    }
                    $project_list = $osiris->projects->find($filter, [
                        'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
                        'sort' => ['name' => 1]
                    ])->toArray();
                    ?>
                    <div class="d-flex gap-10 mb-20">
                        <select id="project-select" class="form-control" placeholder="<?= lang('forms.project_select') ?>">
                            <option value=""><?= lang('forms.project_select') ?></option>
                            <?php
                            foreach ($project_list as $s) { ?>
                                <option value="<?= $s['_id'] ?>"><?= isset($s['acronym']) ? $s['acronym'] . ' – ' : '' ?><?= $s['name'] ?> <?= lang($s['title'], $s['title_de'] ?? null) ?> <?= isset($s['internal_number']) ? ('(ID ' . $s['internal_number'] . ')') : '' ?></option>
                            <?php } ?>
                        </select>
                        <button class="btn primary" type="button" onclick="addProjectRow()"><i class="ph ph-plus-circle"></i> <?= lang('action.add') ?></button>
                    </div>
                    <!-- make sure that empty projects are also submitted -->
                    <input type="hidden" name="projects[]" value="">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('common.connected_projects') ?>:</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="project-list"><?php
                                                    foreach ($projects as $p) { ?>
                                <tr id="project-<?= $p['_id'] ?>">
                                    <td class="w-full">
                                        <input type="hidden" name="projects[]" value="<?= $p['_id'] ?>">
                                        <b><?= isset($p['acronym']) ? $p['acronym'] . ' – ' : '' ?><?= $p['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $p['title'] ?? '' ?>
                                        </span>
                                    </td>
                                    <td class="w-50">
                                        <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>



                    <?php if ($full_permission) { ?>
                        <p class="text-muted font-size-12 mb-0">
                            <i class="ph ph-info"></i>
                            <?= lang('common.note_only_projects_are_shown_here_you_cannot_connect_proposals') ?>
                        </p>
                    <?php } else { ?>
                        <p class="text-muted font-size-12 mb-0">
                            <i class="ph ph-info"></i>
                            <?= lang('common.note_only_your_own_projects_are_shown_here_you_cannot_connect_proposals') ?>
                        </p>
                    <?php } ?>

                    <script>
                        let projectSelect = $("#project-select").selectize();

                        function addProjectRow() {
                            const row = $('<tr>')
                            const projectId = $('#project-select').val();
                            const projectName = $('#project-select option:selected').text();
                            if (!projectId) {
                                alert('<?= lang('forms.project_select') ?>');
                                return;
                            }
                            // check if project already exists
                            if ($('#project-list').find(`#project-${projectId}`).length > 0) {
                                toastError('<?= lang('common.this_project_is_already_connected') ?>');
                                return;
                            }
                            row.append(`<td class="w-full">
                                <input type="hidden" name="projects[]" value="${projectId}">
                                <b>${projectName}</b>
                                </td>
                                `);
                            row.append(`<td class="w-50">
                                <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                </td>`);
                            row.attr('id', `project-${projectId}`);
                            $('#project-list').append(row)
                            // reset select
                            projectSelect[0].selectize.clear();
                        }
                    </script>
                </div>
            </details>
        <?php } ?>



        <?php if ($Settings->featureEnabled('infrastructures')) { ?>
            <details class="collapse-panel infrastructure-panel" open>
                <summary class="collapse-header">
                    <?= $Settings->infrastructureLabel() ?>
                </summary>
                <div class="collapse-content">
                    <?php
                    $filter = [];
                    $all_infrastructures = $osiris->infrastructures->find(
                        $filter,
                        ['sort' => ['end_date' => -1, 'start_date' => 1], 'projection' => ['id' => 1, 'name' => 1]]
                    )->toArray();
                    ?>
                    <div class="d-flex gap-10 mb-20">
                        <select id="infrastructure-select" class="form-control" placeholder="<?= lang('forms.select_infrastructure') ?>">
                            <option value=""><?= lang('forms.select_infrastructure') ?></option>
                            <?php
                            foreach ($all_infrastructures as $s) { ?>
                                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                            <?php } ?>
                        </select>
                        <button class="btn primary" type="button" onclick="addInfrastructureRow()"><i class="ph ph-plus-circle"></i> <?= lang('action.add') ?></button>
                    </div>

                    <!-- make sure that empty infrastructures are also submitted -->
                    <input type="hidden" name="infrastructures[]" value="">

                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= lang('common.connected') ?> <?= $Settings->infrastructureLabel() ?>:</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="infrastructure-list"><?php
                                                        foreach ($infrastructures as $infra) { ?>
                                <tr>
                                    <td class="w-full">
                                        <input type="hidden" name="infrastructures[]" value="<?= $infra['id'] ?>">
                                        <b><?= $infra['name'] ?></b>
                                        <br>
                                        <span class="text-muted">
                                            <?= $infra['subtitle'] ?? '' ?>
                                        </span>
                                    </td>

                                    <td class="w-50">
                                        <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <script>
                    let infraSelect = $("#infrastructure-select").selectize();

                    function addInfrastructureRow() {
                        const row = $('<tr>')
                        const infraId = $('#infrastructure-select').val();
                        const infraName = $('#infrastructure-select option:selected').text();
                        if (!infraId) {
                            alert('<?= lang('forms.select_infrastructure') ?>');
                            return;
                        }
                        // check if infrastructure already exists
                        if ($('#infrastructure-list').find(`input[value="${infraId}"]`).length > 0) {
                            toastError('<?= lang('common.this_infrastructure_is_already_connected') ?>');
                            return;
                        }
                        row.append(`<td class="w-full">
                            <input type="hidden" name="infrastructures[]" value="${infraId}">
                            <b>${infraName}</b>
                            </td>
                            `);
                        row.append(`<td class="w-50">
                            <button class="btn link text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                        </td>`);
                        $('#infrastructure-list').append(row)
                        // reset select
                        infraSelect[0].selectize.clear();
                    }
                </script>

            </details>
        <?php } ?>



        <details class="collapse-panel activity-panel" open>
            <summary class="collapse-header">
                <?= lang('common.activities') ?>
            </summary>
            <div class="collapse-content">

                <a class="btn primary mb-20" href="#connect-activities">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('activities.connect_activity') ?>
                </a>

                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('common.connected_activities') ?>:</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="activity-list">
                        <?php foreach ($connected_activities as $con) { ?>
                            <?php
                            // check if activity is target or source
                            $reverse = ($con['target_id'] == $id);
                            $activity = $osiris->activities->findOne(['_id' => $reverse ? $con['source_id'] : $con['target_id']], ['projection' => [
                                'rendered' => 1,
                            ]]);
                            $conLabel = $Format->getRelationshipLabel($con['relationship'], $reverse);
                            ?>
                            <tr id="activity-<?= $activity['_id'] ?>">
                                <td>
                                    <h5 class="m-0">
                                        <?= lang($conLabel['en'], $conLabel['de']) ?>
                                    </h5>
                                    <div><?= $activity['rendered']['web'] ?? '' ?></div>
                                </td>
                                <td class="w-50">
                                    <button type="button" class="btn link text-danger" data-toggle="tooltip" data-title="<?= lang('common.disconnect_activity') ?>" onclick="removeActivity('<?= $con['_id'] ?>')">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </details>

        <!-- News cannot be added here, the activity has to be connected through the news section -->
        <?php if ($Settings->featureEnabled('news', true)) { ?>
            <p class="text-muted font-size-12">
                <i class="ph ph-info"></i>
                <?= lang('activities.note_news_cannot_be_connected_here_to_connect_news_please_go_to_the_news_se') ?>
            </p>
        <?php } ?>
        

        <div class="bottom-buttons">
            <button id="save-button" type="submit" class="btn large success">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('action.save') ?>
            </button>
            <a id="cancel-button" type="button" class="btn large light ml-5" href="<?= ROOTPATH ?>/activities/view/<?= $id ?>#edit-activities">
                <i class="ph ph-x"></i>
                <?= lang('action.cancel') ?>
            </a>
        </div>

    </form>



    <div class="modal" id="connect-activities" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title">
                    <?= lang('common.connect_activities') ?>
                </h5>
                <input type="hidden" name="source_id" value="<?= $id ?>">
                <!-- relationship type -->
                <div class="form-group">
                    <label for="relationship-type"><?= lang('common.relationship_type') ?></label>
                    <div class="form-group">
                        <div class="input-group">
                            <select name="relationship" id="relationship-type" class="form-control">
                                <?php
                                $relationships = $Format->getRelationships();
                                foreach ($relationships as $rel) {
                                    $key = $rel['id'];
                                    $label = lang($rel['label']['en'], $rel['label']['de'] ?? null);
                                    $rev = lang($rel['reverse_label']['en'], $rel['reverse_label']['de'] ?? null);
                                ?>
                                    <option data-label="<?= $label ?>" data-reverse-label="<?= $rev ?>" value="<?= $key ?>"><?= $label ?></option>
                                <?php } ?>
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <div class="custom-switch">
                                        <input type="checkbox" id="swap-relationship-dir" name="reverse" value="1" onchange="swapRelationshipDirection()">
                                        <label for="swap-relationship-dir">
                                            <?= lang('common.swap_direction') ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- input field with suggesting activities -->
                <div class="form-group" id="activity-suggest">
                    <label for="activity-suggested"><?= lang('common.select_an_activity_to_connect') ?></label>
                    <input type="text" name="activity-suggested" id="activity-suggested" class="form-control" placeholder="<?= lang('common.start_typing_to_search_for_activities') ?>">

                    <div class="form-group font-size-12">
                        <div class="custom-radio d-inline-block mr-20">
                            <input type="radio" name="activity-search-limit" id="activity-suggest-author" value="user" checked="checked">
                            <label for="activity-suggest-author"><?= lang('common.only_show_my_activities') ?></label>
                        </div>
                        <div class="custom-radio d-inline-block mr-20">
                            <input type="radio" name="activity-search-limit" id="activity-suggest-unit" value="unit">
                            <label for="activity-suggest-unit"><?= lang('common.show_activities_from_my_unit_s') ?></label>
                        </div>
                        <div class="custom-radio d-inline-block mr-20">
                            <input type="radio" name="activity-search-limit" id="activity-suggest-all" value="all">
                            <label for="activity-suggest-all"><?= lang('common.show_all_activities') ?></label>
                        </div>
                    </div>
                    <div class="suggestions on-focus"></div>
                </div>
                <input type="hidden" name="target_id" id="activity-selected" value="">

                <button type="button" class="btn primary" onclick="connectActivity()">
                    <?= lang('common.connect') ?>
                </button>

            </div>
        </div>
    </div>

    <script>
        $('#activity-suggested, #activity-suggest-author, #activity-suggest-unit, #activity-suggest-all').on('input', function() {

            const val = $('#activity-suggested').val();
            if (val.length < 3) return;
            let filter = ''
            let limit = $('input[name="activity-search-limit"]:checked').val();
            if (limit == 'user') {
                filter = '?user=<?= urlencode($_SESSION['username']) ?>';
            } else if (limit == 'unit') {
                filter = '?unit=<?= implode(',', $user_units) ?>';
            } else {
                filter = '';
            }
            $.get('<?= ROOTPATH ?>/api/activities-suggest/' + val + filter, function(data) {
                $('#activity-suggest .suggestions').empty();
                console.log(data);
                data.data.forEach(function(d) {
                    if (d.id.toString() == '<?= $id ?>') return; // prevent selecting itself
                    $('#activity-suggest .suggestions').append(
                        `<a data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                    )
                })

                // $('#activity-suggest .suggest').html(data);
            })
        })
        $('#activity-suggest .suggestions').on('click', 'a', function() {
            const activity_id = $(this).data('id');
            const activity_text = $(this).text();
            $('#activity-selected').val(activity_id);
            $('#activity-suggested').val(activity_text);
            $('#activity-suggest .suggestions').empty();
        })

        function swapRelationshipDirection() {
            // swap all the labels
            const select = $('#relationship-type');
            const isReverse = $('#swap-relationship-dir').is(':checked');
            select.find('option').each(function() {
                const label = $(this).data(isReverse ? 'reverse-label' : 'label');
                $(this).text(label);
            });
        }

        function connectActivity() {
            const target_id = $('#activity-selected').val();
            const relationship = $('#relationship-type').val();
            const reverse = $('#swap-relationship-dir').is(':checked');
            if (!target_id) {
                alert('<?= lang('activities.please_select_an_activity_to_connect') ?>');
                return;
            }
            console.log({
                target_id: target_id,
                source_id: '<?= $id ?>',
                relationship: relationship,
                reverse: reverse
            });
            // submit the form via ajax
            $.ajax({
                type: 'POST',
                url: '<?= ROOTPATH ?>/crud/activities/connect',
                data: {
                    target_id: target_id,
                    source_id: '<?= $id ?>',
                    relationship: relationship,
                    reverse: reverse ? 1 : 0
                },
                dataType: 'json',
                success: function(data) {
                    if (data.inserted > 0) {
                        toastSuccess('<?= lang('activities.activity_connected_successfully') ?>');
                        // add the new connection to the list of connected activities
                        // add connection to the list of connected activities
                        const label = $('#relationship-type option:selected').text();
                        $('#activity-list').append(`<tr>
                    <td>
                    <h5 class="m-0">
                    ${label}
                        </h5>
                        <div>${$('#activity-suggested').val()}</div>
                        </td>
                        <td class="w-50">
                        <button type="button" class="btn link text-danger" data-toggle="tooltip" data-title="<?= lang('common.disconnect_activity') ?>">
                        <i class="ph ph-trash"></i>
                        </button>
                        </td>
                        </tr>`);
                        // clear the input field and hidden field
                        $('#activity-selected').val('');
                        $('#activity-suggested').val('');

                        // hide modal
                        location.hash = '#close';
                    } else {
                        console.log(data);
                        if (data.message) {
                            toastError(data.message);
                            return;
                        }
                        toastError('<?= lang('activities.an_error_occurred_while_connecting_the_activity') ?>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                    toastError('<?= lang('activities.an_error_occurred_while_connecting_the_activity') ?>');
                }
            });


        }

        function removeActivity(connection_id) {
            if (!confirm('<?= lang('activities.are_you_sure_you_want_to_disconnect_this_activity') ?>')) {
                return;
            }
            // submit the form via ajax
            $.ajax({
                type: 'POST',
                url: '<?= ROOTPATH ?>/crud/activities/disconnect',
                data: {
                    connection_id: connection_id
                },
                dataType: 'json',
                success: function(data) {
                    if (data.deleted > 0) {
                        toastSuccess('<?= lang('activities.activity_disconnected_successfully') ?>');
                        // remove the connection from the list of connected activities
                        $(`#activity-${connection_id}`).remove();
                    } else {
                        console.log(data);
                        toastError('<?= lang('activities.an_error_occurred_while_disconnecting_the_activity') ?>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                    toastError('<?= lang('activities.an_error_occurred_while_disconnecting_the_activity') ?>');
                }
            });
        }
    </script>
</div>