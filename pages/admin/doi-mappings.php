<?php
$mappings = $Settings->getDOImappings();
$types = $osiris->adminTypes->find([], ['sort' => ['parent' => 1, 'order' => 1]])->toArray();
$type_options = [];
foreach ($types as $type) {
    $parent = $osiris->adminCategories->findOne(['id' => $type['parent']]);
    $name = lang($type['name'], $type['name_de'] ?? null);
    if ($parent) {
        $name = lang($parent['name'], $parent['name_de'] ?? null) . " > " . $name;
    }
    $type_options[$type['id']] = $name;
}

$fields = [
    "crossref" => [
        "book-chapter",
        "book-part",
        "book-section",
        "book-series",
        "book-set",
        "book-track",
        "book",
        "component",
        "dataset",
        "dissertation",
        "edited-book",
        "journal-article",
        "journal-issue",
        "journal-volume",
        "journal",
        "monograph",
        "peer-review",
        "posted-content",
        "proceedings-article",
        "proceedings",
        "reference-book",
        "reference-entry",
        "report-series",
        "report",
        "standard-series",
        "standard",
        "other",
    ],
    // DataCite
    "datacite" => [
        'audiovisual',
        'book',
        'bookchapter',
        'collection',
        'computationalnotebook',
        'conferencepaper',
        'conferenceproceeding',
        'datapaper',
        'dataset',
        'dissertation',
        'event',
        'image',
        'interactiveresource',
        'journal',
        'journalarticle',
        'model',
        'outputmanagementplan',
        'peerreview',
        'physicalobject',
        'poster',
        'preprint',
        'presentation',
        'report',
        'service',
        'software',
        'sound',
        'standard',
        'text',
        'workflow',
        'other',
    ]
];
?>

<!-- 10.34669/WI.WS/23 -->


<h1>
    <i class="ph-duotone ph-link-simple" aria-hidden="true"></i>
    <?= lang('DOI Mappings', 'DOI Zuordnungen') ?>
</h1>

<!-- apply default mapping to all none -->
<div class="input-group mb-20 w-auto">
    <select id="default-mapping-select" class="form-control w-200 flex-reset">
        <option value=""><?= lang('Select type', 'Typ auswählen') ?></option>
        <?php
        foreach ($type_options as $type_id => $type_name) {
        ?>
            <option value="<?= e($type_id) ?>"><?= e($type_name) ?></option>
        <?php } ?>
    </select>
    <div class="input-group-append">
        <button type="button" class="btn" id="apply-default-mapping">
            <i class="ph ph-magic-wand"></i>
            <?= lang('Apply default mapping to all "None"', 'Standardzuordnung auf alle "Keine" anwenden') ?>
        </button>
    </div>
</div>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/doi-mappings">
    <input type="hidden" name="action" value="update-doi-mappings">

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Field', 'Feld') ?></th>
                <th><?= lang('Mapping', 'Zuordnung') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($fields as $schema => $field_types) { ?>
                <tr>
                    <th colspan="2" class="text-primary">
                        <i class="ph ph-globe mr-5"></i>
                        <strong><?= strtoupper($schema) ?></strong>
                    </th>
                </tr>
                <?php

                foreach ($field_types as $field) {
                    $key = $schema . '.' . $field;
                ?>
                    <tr>
                        <td>
                            <?= e($field) ?>
                        </td>
                        <td>
                            <select name="general[doi_mappings][<?= $key ?>]" class="form-control">
                                <option value=""><?= lang('None', 'Keine') ?></option>
                                <?php
                                foreach ($type_options as $type_id => $type_name) {
                                    $selected = (isset($mappings[$key]) && $mappings[$key] === $type_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= e($type_id) ?>" <?= $selected ?>><?= e($type_name) ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php
                }
                ?>
            <?php } ?>

        </tbody>
    </table>

    <button type="submit" class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('Save', 'Speichern') ?>
    </button>
</form>

<script>
    $(document).ready(function() {
        $('#apply-default-mapping').on('click', function() {
            var selectedType = $('#default-mapping-select').val();
            if (selectedType === '') {
                toastError('<?= lang('Please select a type to apply.', 'Bitte wählen Sie einen Typ zum Anwenden aus.') ?>');
                return;
            }

            $('select[name^="general[doi_mappings]"]').each(function() {
                if ($(this).val() === '') {
                    $(this).val(selectedType);
                }
            });
        });
    });
</script>