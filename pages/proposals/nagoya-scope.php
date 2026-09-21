<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = $project['nagoya'] ?? [];

$countries = DB::doc2Arr($nagoya['countries'] ?? []);
$in_scope = [];
$out_of_scope = [];
foreach ($countries as $country) {
    if ($country['abs'] ?? false) {
        $in_scope[] = $country;
    } else {
        $out_of_scope[] = $country;
    }
}

// predefined options for material & utilization
$materialOptions = [
    'Soil',
    'Water',
    'Sediment',
    'Plant-Tissue',
    'Animal-Tissue',
    'Microbial-Isolate',
    'Enrichment-Culture',
    'DNA-Extract',
    'RNA-Extract',
];

$utilizationOptions = [
    'Sequencing',
    'Metagenomics',
    'Metabarcoding',
    'Genome-Assembly',
    'Annotation',
    'Cultivation',
    'Phenotypic-Screening',
    'Metabolomics',
    'Proteomics',
    'Transcriptomics',
];
?>
<script>
    window.nagoyaMaterialOptions = <?= json_encode(array_values($materialOptions)) ?>;
    window.nagoyaUtilizationOptions = <?= json_encode(array_values($utilizationOptions)) ?>;
</script>

<style>
    .box .header {
        cursor: pointer;
        background-color: var(--primary-color-20);
    }

    .box .header small {
        margin-left: auto;
    }

    .box .header h2 {
        margin: 0;
        padding: 1rem;
    }

    .box .header::before,
    .scope-group .title::before {
        content: '\E13A';
        font-family: 'Phosphor';
        margin-right: 0.5rem;
        transition: transform 0.2s ease;
        position: relative;
        display: inline-block;
    }

    .box .header::before {
        font-size: 2.8rem;
        color: var(--primary-color);
    }

    .box .header.open::before,
    .scope-group .title.open::before {
        transform: rotate(90deg);
    }

    .scope-group {
        /* border rounded p-10 mb-15 */
        border: var(--border-width) solid var(--primary-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1rem;
        margin-bottom: 1.5rem;
        background-color: white;
    }

    .scope-group .title {
        margin: 0;
        color: white;
        background-color: var(--primary-color);
        padding: .5rem 1rem;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1.6rem;
    }
</style>

<h1 class="mb-3"><?= lang('projects.nagoya_compliance_scope_analysis') ?></h1>
<h2 class="subtitle">
    <a href="<?= ROOTPATH ?>/proposals/view/<?= $id ?>">
        <i class="ph ph-arrow-left"></i>
        <?= ($project['name'] ?? '') ?>
    </a>
</h2>

<div class="mb-20">
    <b><?= lang('common.current_status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>
</div>

<?php if ($nagoya['scopeSubmitted'] ?? false) { ?>
    <!-- thank you -->
    <div class="alert success">
        <div class="title">
            <?= lang('projects.thank_you') ?>
        </div>
        <strong>
            <?= lang('projects.the_scope_information_has_been_submitted_for_abs_review_you_will_be_notifie') ?>
        </strong>
    </div>
<?php } ?>


<form method="post" action="<?= ROOTPATH ?>/crud/nagoya/add-abs-scope/<?= $id ?>">

    <?php foreach ($in_scope as $country):
        $cid   = $country['id'];
        $scope = $country['scope'] ?? [];

        // Backwards compatibility: if no groups exist, map flat fields into one group
        $groups = $scope['groups'] ?? [];
        if (empty($groups)) {
            $groups = [[
                'geo'             => $scope['geo']            ?? '',
                'temporal_from'   => $scope['temporal_from']  ?? '',
                'temporal_to'     => $scope['temporal_to']    ?? '',
                'temporal_ongoing' => $scope['temporal_ongoing'] ?? null,
                'material'        => $scope['material']       ?? [],
                'utilization'     => $scope['utilization']    ?? [],
            ]];
        }
    ?>
        <div class="box">
            <div class="header" onclick="$(this).toggleClass('open').next('.content').toggleClass('hidden');">
                <h2>
                    <i class="ph-duotone ph-globe-stand"></i>
                    <?= $DB->getCountry($country['code'], lang('common.field_name_language')) ?>
                </h2>
                <small class="code badge primary"><?= e($cid) ?></small>
            </div>
            <div class="content">
                <?php if (!empty($country['review']['comment'] ?? null)) { ?>
                    <p class="text-primary">
                        <i class="ph ph-chat-circle-text"></i>
                        <b>
                            <?= lang('projects.abs_review_comment') ?>:
                        </b>
                        <q><?= nl2br(e($country['review']['comment'])) ?></q>
                    </p>
                <?php } ?>


                <input type="hidden" name="country_id[]" value="<?= e($cid) ?>">

                <!-- Scope groups for this country -->
                <div class="scope-groups" data-country="<?= e($cid) ?>" data-next-index="<?= count($groups) ?>">
                    <?php foreach ($groups as $gi => $g):
                        // normalize material/utilization
                        $matSelected  = DB::doc2Arr($g['material']    ?? []);
                        $utilSelected = DB::doc2Arr($g['utilization'] ?? []);
                        if (!is_array($matSelected)) {
                            $matSelected = array_filter(array_map('trim', explode(',', (string)$matSelected)));
                        }
                        if (!is_array($utilSelected)) {
                            $utilSelected = array_filter(array_map('trim', explode(',', (string)$utilSelected)));
                        }
                        $atkUsed = !empty($scope['atk_used']); // country-level
                    ?>
                        <div class="scope-group" data-index="<?= $gi ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="title" onclick="$(this).toggleClass('open').parent().next('.scope-group-fields').toggleClass('hidden');">
                                    <?= lang('common.sample_collection') ?> <?= $gi + 1 ?>
                                </h3>
                                <?php if ($gi > 0): ?>
                                    <button type="button"
                                        class="btn small text-danger remove-scope-group"
                                        data-country="<?= e($cid) ?>">
                                        <i class="ph ph-trash"></i>
                                        <?= lang('projects.remove_sample_collection') ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="scope-group-fields mt-10">
                                <!-- Geographical scope -->
                                <div class="form-group">
                                    <label class="font-weight-bold required">
                                        <?= lang('common.geographical_scope') ?>
                                        <div class="btn small hidden link float-right" onclick="copyFromAbove(this, '<?= e($cid) ?>', <?= $gi ?>, 'geo');">
                                            <i class="ph ph-copy"></i>
                                            <?= lang('projects.copy_from_above') ?>
                                        </div>
                                    </label>
                                    <small class="d-block text-muted mb-5">
                                        <?= lang('projects.please_describe_regions_provinces_or_specific_locations_where_samples_were') ?>
                                    </small>
                                    <textarea
                                        name="scope[<?= e($cid) ?>][groups][<?= $gi ?>][geo]"
                                        rows="3"
                                        class="form-control geo"><?= e($g['geo'] ?? '') ?></textarea>
                                </div>

                                <!-- Temporal scope -->
                                <div class="form-group">
                                    <label class="font-weight-bold required">
                                        <?= lang('common.temporal_scope') ?>
                                        <div class="btn small hidden link float-right" onclick="copyFromAbove(this, '<?= e($cid) ?>', <?= $gi ?>, 'temporal');">
                                            <i class="ph ph-copy"></i>
                                            <?= lang('projects.copy_from_above') ?>
                                        </div>
                                    </label>
                                    <small class="d-block text-muted mb-5">
                                        <?= lang('projects.for_example_2018_2020_march_2023_multiple_field_trips_between_2019_and_2022') ?>
                                    </small>
                                    <div class="d-flex flex-wrap gap-10 align-items-end">
                                        <div class="mr-10">
                                            <input
                                                type="text"
                                                name="scope[<?= e($cid) ?>][groups][<?= $gi ?>][temporal]"
                                                class="form-control temporal"
                                                placeholder="e.g. 2018–2020"
                                                value="<?= e($g['temporal'] ?? '') ?>">
                                        </div>
                                        <div>
                                            <label class="inline-flex align-items-center">
                                                <input
                                                    type="checkbox"
                                                    name="scope[<?= e($cid) ?>][groups][<?= $gi ?>][temporal_ongoing]"
                                                    value="1" class="temporal_ongoing"
                                                    <?= !empty($g['temporal_ongoing']) ? 'checked' : '' ?>>
                                                <span class="ml-5">
                                                    <?= lang('projects.ongoing_still_collecting_samples') ?>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Material scope -->
                                <div class="form-group">
                                    <label class="font-weight-bold required">
                                        <?= lang('common.material_scope') ?>
                                        <div class="btn small hidden link float-right" onclick="copyFromAbove(this, '<?= e($cid) ?>', <?= $gi ?>, 'material');">
                                            <i class="ph ph-copy"></i>
                                            <?= lang('projects.copy_from_above') ?>
                                        </div>
                                    </label>
                                    <small class="d-block text-muted mb-5">
                                        <?= lang('projects.which_types_of_samples_are_concerned_please_select_one_or_more_options_if_s') ?>
                                    </small>
                                    <select
                                        name="scope[<?= e($cid) ?>][groups][<?= $gi ?>][material][]"
                                        class="form-control scope-multi-select material"
                                        multiple>
                                        <?php foreach ($materialOptions as $opt): ?>
                                            <option value="<?= e($opt) ?>"
                                                <?= in_array($opt, $matSelected, true) ? 'selected' : '' ?>>
                                                <?= e($opt) ?>
                                            </option>
                                        <?php endforeach; ?>

                                        <?php
                                        foreach ($matSelected as $val) {
                                            if (!in_array($val, $materialOptions, true)) {
                                        ?>
                                                <option value="<?= e($val) ?>" selected>
                                                    <?= e($val) ?>
                                                </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Utilization scope -->
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold required">
                                        <?= lang('projects.utilization_scope') ?>
                                        <div class="btn small hidden link float-right" onclick="copyFromAbove(this, '<?= e($cid) ?>', <?= $gi ?>, 'utilization');">
                                            <i class="ph ph-copy"></i>
                                            <?= lang('projects.copy_from_above') ?>
                                        </div>
                                    </label>
                                    <small class="d-block text-muted mb-5">
                                        <?= lang('projects.how_will_the_genetic_resources_be_used_please_select_one_or_more_options_fo') ?>
                                    </small>
                                    <select
                                        name="scope[<?= e($cid) ?>][groups][<?= $gi ?>][utilization][]"
                                        class="form-control scope-multi-select utilization"
                                        multiple>
                                        <?php foreach ($utilizationOptions as $opt): ?>
                                            <option value="<?= e($opt) ?>"
                                                <?= in_array($opt, $utilSelected, true) ? 'selected' : '' ?>>
                                                <?= e($opt) ?>
                                            </option>
                                        <?php endforeach; ?>

                                        <?php
                                        foreach ($utilSelected as $val) {
                                            if (!in_array($val, $utilizationOptions, true)) {
                                        ?>
                                                <option value="<?= e($val) ?>" selected>
                                                    <?= e($val) ?>
                                                </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add scope block button -->
                <button type="button"
                    class="btn primary add-scope-group"
                    data-country="<?= e($cid) ?>">
                    <i class="ph ph-plus"></i>
                    <?= lang('projects.add_sample_collection') ?>
                </button>

                <hr class="my-15">

                <!-- aTK (country-level) -->
                <div class="form-group">
                    <label class="font-weight-bold">
                        <?= lang('common.associated_traditional_knowledge_atk') ?>
                    </label>
                    <small class="d-block text-muted mb-5">
                        <?= lang('projects.associated_traditional_knowledge_atk_refers_to_knowledge_innovation_practic') ?>
                    </small>
                    <div class="mb-5">
                        <label class="inline-flex align-items-center">
                            <input
                                type="checkbox"
                                name="scope[<?= e($cid) ?>][atk_used]"
                                value="1"
                                onchange="$('#atk_details_<?= e($cid) ?>').toggleClass('hidden', !this.checked);"
                                <?= !empty($scope['atk_used']) ? 'checked' : '' ?>>
                            <span class="ml-5">
                                <?= lang('projects.traditional_knowledge_is_involved_for_this_country') ?>
                            </span>
                        </label>
                    </div>
                    <textarea
                        id="atk_details_<?= e($cid) ?>"
                        name="scope[<?= e($cid) ?>][atk_details]"
                        rows="2"
                        class="form-control <?= empty($scope['atk_used']) ? 'hidden' : '' ?>"
                        placeholder="<?= lang('projects.please_describe_source_communities_or_agreements_if_applicable') ?>"><?= e($scope['atk_details'] ?? '') ?></textarea>
                </div>

                <!-- Optional notes (country-level) -->
                <div class="form-group">
                    <label class="font-weight-bold"><?= lang('projects.additional_notes_optional') ?></label>
                    <small class="d-block text-muted mb-5">
                        <?= lang('projects.please_indicate_any_other_relevant_information_on_abs_including_explanation') ?>
                    </small>
                    <textarea
                        name="scope[<?= e($cid) ?>][notes]"
                        rows="2"
                        class="form-control"><?= e($scope['notes'] ?? '') ?></textarea>
                </div>
            </div>

        </div>
    <?php endforeach; ?>

    <div class="mt-20">
        <button type="submit" name="action" value="save" class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

        <button type="submit" name="action" value="submit" class="btn success">
            <i class="ph ph-paper-plane-tilt"></i>
            <?= lang('common.submit_scope_for_abs_review') ?>
        </button>
    </div>
</form>

<?php if (!empty($out_of_scope)) { ?>

    <hr class="my-20">

    <h5><?= lang('projects.countries_without_abs') ?>:</h5>
    <?php foreach ($out_of_scope as $country): ?>
        <span class="badge bg-white border mr-5 mb-5">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= $DB->getCountry($country['code'], lang('common.field_name_language')) ?>
        </span>
    <?php endforeach; ?>

    <p class="text-muted font-size-12">
        <?= lang('projects.countries_listed_here_have_been_marked_as_out_of_scope_for_abs_compliance_n') ?>
    </p>

<?php } ?>


<?php include_once BASEPATH . '/header-editor.php'; ?>

<script>
    $(function() {
        function initScopeSelectize($ctx) {
            $ctx.find('select.scope-multi-select').each(function() {
                if (this.selectize) return; // already initialized
                $(this).selectize({
                    plugins: ['remove_button'],
                    create: true,
                    persist: false,
                    maxItems: null,
                    closeAfterSelect: false,
                    selectOnTab: true
                });
            });
        }

        // Initiale Selectize-Initialisierung
        initScopeSelectize($(document));

        // copy from above
        window.copyFromAbove = function(btn, countryId, groupIndex, fieldType) {
            var $btn = $(btn);
            var $formGroup = $btn.closest('.form-group');
            var $currentGroup = $btn.closest('.scope-group');
            var $prevGroup = $currentGroup.prev('.scope-group');
            if ($prevGroup.length === 0) return; // no previous group
           // find field in formGroup and get equivalent field from previous group
            var $currentField, $prevField;
            if (fieldType === 'geo') {
                $currentField = $formGroup.find('textarea.geo');
                $prevField = $prevGroup.find('textarea.geo');
            } else if (fieldType === 'temporal') {
                $currentField = $formGroup.find('input.temporal');
                $prevField = $prevGroup.find('input.temporal');
                var $currentOngoing = $formGroup.find('input.temporal_ongoing');
                var $prevOngoing = $prevGroup.find('input.temporal_ongoing');
            } else if (fieldType === 'material') {
                $currentField = $formGroup.find('select.material')[0].selectize;
                $prevField = $prevGroup.find('select.material')[0].selectize;
            } else if (fieldType === 'utilization') {
                $currentField = $formGroup.find('select.utilization')[0].selectize;
                $prevField = $prevGroup.find('select.utilization')[0].selectize;
            } else {
                return; // unknown field type
            }

            // copy value(s)
            if (fieldType === 'material' || fieldType === 'utilization') {
                var values = $prevField.getValue();
                $currentField.clear();
                $currentField.setValue(values);
            } else if (fieldType === 'temporal') {
                $currentField.val($prevField.val());
                // also copy ongoing checkbox
                var ongoingChecked = $prevOngoing.is(':checked');
                $currentOngoing.prop('checked', ongoingChecked);
            } else {
                $currentField.val($prevField.val());
            }
        };

        // Neuen Scope-Block hinzufügen
        $('.add-scope-group').on('click', function() {
            var cid = $(this).data('country');
            var $wrap = $('.scope-groups[data-country="' + cid + '"]');
            var next = parseInt($wrap.data('next-index') || 0, 10);

            var $last = $wrap.find('.scope-group').last();
            var $clone = $last.clone();
            $clone.find('.btn.small.hidden').removeClass('hidden');
            // Index hochzählen
            $clone.attr('data-index', next);
            $clone.find('strong').first().text('<?= lang('projects.scope_block') ?> ' + (next + 1));

            // alte Selectize-Controls entfernen
            $clone.find('.selectize-control').remove();
            var $selects = $clone.find('select.scope-multi-select');

            // Namen und Inhalte der Inputs/Selects zurücksetzen
            $clone.find('[name]').each(function() {
                this.name = this.name.replace(/\[groups]\[\d+]/, '[groups][' + next + ']');
            });
            $clone.find('textarea').val('');
            $clone.find('input[type="text"]').val('');
            $clone.find('input[type="checkbox"]').prop('checked', false);

            // Select-Optionen frisch aus den Masterlisten befüllen
            $selects.each(function() {
                var $sel = $(this);
                var name = $sel.attr('name') || '';

                var isMaterial = name.indexOf('[material]') !== -1;
                var optionsSource = isMaterial ? (window.nagoyaMaterialOptions || []) :
                    (window.nagoyaUtilizationOptions || []);

                $sel.empty(); // alle alten <option> raus

                optionsSource.forEach(function(val) {
                    $('<option>')
                        .val(val)
                        .text(val)
                        .appendTo($sel);
                });
            });

            // neuen Block einhängen
            $wrap.append($clone);
            $wrap.data('next-index', next + 1);

            // Selectize für neue Selects initialisieren
            initScopeSelectize($clone);
        });

        // Scope-Block entfernen (mindestens einen behalten)
        $(document).on('click', '.remove-scope-group', function() {
            var cid = $(this).data('country');
            var $wrap = $('.scope-groups[data-country="' + cid + '"]');
            var $groups = $wrap.find('.scope-group');
            if ($groups.length <= 1) return;

            $(this).closest('.scope-group').remove();

            // Optional: neu durchnummerieren
            $wrap.find('.scope-group').each(function(idx) {
                $(this).attr('data-index', idx);
                $(this).find('strong').first().text('<?= lang('projects.scope_block') ?> ' + (idx + 1));
                $(this).find('[name]').each(function() {
                    this.name = this.name.replace(/\[groups]\[\d+]/, '[groups][' + idx + ']');
                });
            });
            $wrap.data('next-index', $wrap.find('.scope-group').length);
        });
    });
</script>