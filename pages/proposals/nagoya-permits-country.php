<?php
include_once BASEPATH . '/php/Vocabulary.php';
$Vocabulary = new Vocabulary();
if (!$country) {
?>
    <div class="alert danger">
        <?= lang('projects.country_not_found_for_this_project') ?>
    </div>
<?php
    return;
}

$code       = $country['code'] ?? '';
$countryId  = $country['id'] ?? '';
$evaluation = $country['evaluation'] ?? [];
$permits    = $evaluation['permits'] ?? [];
if (empty($permits)) {
    $permits = [
        [
            'id'        => uniqid('permit_'),
            'name'      => '',
            'status'    => '',
            'identifier' => '',
            'ircc'      => '',
            'ircc_link' => '',
            'declared'  => false,
            'validity'  => '',
            'comment'   => '',
            'checked'   => false,
            'docs'      => []
        ]
    ];
}
$edit_perm = true;
// shared notes (projektweit für Permits)
$permitNotes = DB::doc2Arr($nagoya['permitNotes'] ?? []);

// permissions
$isAbsTeam      = $Settings->hasPermission('nagoya.view'); // ABS-intern
$canEditBasic   = $edit_perm || $isAbsTeam;                // Forschende + ABS
$canValidateABS = $isAbsTeam;                              // Checkbox "validated" nur ABS
$canAddNotes    = $edit_perm || $isAbsTeam;
$canUploadDocs  = $edit_perm || $isAbsTeam;

// documents
$docsByPermit = [];
$cursor = $osiris->uploads->find([
    'type'        => 'nagoya-permit',
    'id'          => $id,          // Proposal-ID
    'country_code' => $code,        // ISO-Code des Landes
]);

foreach ($cursor as $doc) {
    $pid = $doc['permit_id'] ?? null;
    if (!$pid) continue;
    $docsByPermit[$pid][] = $doc;
}

$countryLabel = $evaluation['label'] ?? '';
?>

<h1 class="mb-0">
    <i class="ph-duotone ph-file-text"></i>
    <?= lang('projects.abs_permits_for') ?>
    <?= ($DB->getCountry($code, lang('common.field_name_language'))) ?>
</h1>
<a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $id ?>#nagoya">
    <i class="ph ph-arrow-left"></i>
    <?= lang('projects.back_to_all_countries') ?>
</a>

<div class="d-flex align-items-center gap-10 mt-20">
    <b><?= lang('common.nagoya_status') ?>:</b>
    <!-- <?= Nagoya::badge(DB::doc2Arr($project), false) ?> -->
    <?= Nagoya::countryBadge(DB::doc2Arr($country)) ?>
</div>


<div class="row row-eq-spacing my-0">
    <!-- Permits column -->
    <div class="col-md-8 my-0">
        <h2 class="title">
            <i class="ph-duotone ph-file-text"></i>
            <?= lang('projects.permits_for_this_country') ?>
        </h2>
        <?php if (!$canEditBasic): ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang('projects.you_can_see_the_permit_information_for_this_country_changes_can_only_be_mad') ?>
            </p>
        <?php else: ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang('projects.please_keep_permit_information_up_to_date_use_the_fields_below_to_edit_name') ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Shared notes column -->
    <div class="col-md-4 my-0">
        <h2 class="title">
            <i class="ph-duotone ph-chats-circle"></i>
            <?= lang('common.shared_notes') ?>
        </h2>
        <?php if (!$canAddNotes): ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang('projects.you_can_see_shared_notes_related_to_permits_for_this_country_adding_notes_i') ?>
            </p>
        <?php else: ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang('projects.use_the_shared_notes_area_to_document_communication_and_decisions_related_t') ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="row row-eq-spacing mt-0">
    <!-- Permits column -->
    <div class="col-md-8">
        <form method="post" action="<?= ROOTPATH ?>/crud/nagoya/update-permits/<?= $id ?>?country=<?= urlencode($countryId) ?>">
            <div class="">
                <div id="permit-list">
                    <?php foreach ($permits as $index => $p):
                        $pid       = $p['id'] ?? ('permit_' . $index);
                        $name      = $p['name'] ?? '';
                        $status    = $p['status'] ?? '';
                        $identifier = $p['identifier'] ?? '';
                        $ircc      = $p['ircc'] ?? '';
                        $ircc_link = $p['ircc_link'] ?? '';
                        $declared  = !empty($p['declared']);
                        $validity  = $p['validity'] ?? '';
                        // $provider  = $p['provider'] ?? '';
                        $restricts_transfer = !empty($p['restricts_transfer']);
                        $restriction_details = $p['restriction_details'] ?? '';
                        $benefit_sharing = $p['benefit_sharing'] ?? '';
                        $comment   = $p['comment'] ?? '';
                        $checked   = !empty($p['checked']);
                        $docs      = $docsByPermit[$pid] ?? [];
                    ?>
                        <div class="box padded permit-block" data-permit-id="<?= e($pid) ?>" id="permit-<?= e($pid) ?>">
                            <div class="dropdown float-right">
                                <button class="btn link small text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                    <i class="ph-duotone ph-trash"></i>
                                    <span class="sr-only"><?= lang('projects.delete_permit') ?></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-1">
                                    <div class="content">
                                        <?= lang('projects.are_you_sure_you_want_to_delete_this_permit_this_action_cannot_be_undone') ?>
                                        <button type="button" class="btn danger" onclick="$(this).parent('.permit-block').remove();">
                                            <i class="ph ph-trash"></i>
                                            <?= lang('projects.yes_delete_permit') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <h3 class="title">
                                <i class="ph-duotone ph-file-text"></i>
                                <?= e($name) ?>
                            </h3>
                            <input type="hidden"
                                name="permits[<?= e($pid) ?>][id]"
                                value="<?= e($pid) ?>">

                            <div class="d-flex justify-content-between align-items-center mb-20">
                                <div>
                                    <?php if ($canEditBasic): ?>
                                        <input
                                            type="text"
                                            class="form-control w-300"
                                            name="permits[<?= e($pid) ?>][name]"
                                            value="<?= e($name) ?>"
                                            placeholder="<?= lang('projects.permit_name_e_g_pic_mat_abs_permit') ?>">
                                    <?php else: ?>
                                        <strong><?= e($name ?: lang('common.unnamed_permit')) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($comment) && !$canEditBasic): ?>
                                        <div class="small text-muted">
                                            <?= e($comment) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="text-right small">
                                    <label class="mb-1 font-weight-bold">
                                        <?= lang('common.status') ?>
                                    </label>
                                    <?php if ($canEditBasic): ?>
                                        <select
                                            name="permits[<?= e($pid) ?>][status]"
                                            class="form-control d-inline-block w-auto">
                                            <option value="" disabled><?= lang('common.status') ?></option>
                                            <option value="needed" <?= $status === 'needed'   ? 'selected' : '' ?>><?= lang('common.needed') ?></option>
                                            <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>><?= lang('common.requested') ?></option>
                                            <option value="granted" <?= $status === 'granted'  ? 'selected' : '' ?>><?= lang('common.granted') ?></option>
                                            <option value="not-applicable" <?= $status === 'not-applicable' ? 'selected' : '' ?>><?= lang('common.not_applicable') ?></option>
                                        </select>
                                    <?php else: ?>
                                        <?= Nagoya::permitStatusBadge($status) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($status !== 'not-applicable') { ?>
                                <div class="row row-eq-spacing">
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('projects.permit_number') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= e($pid) ?>][identifier]"
                                                placeholder="e.g. 12345-ABCD"
                                                value="<?= e($identifier) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= e($identifier ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('projects.ircc_number') ?> <small>(Internationally Recognized Certificate of Compliance)</small></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= e($pid) ?>][ircc]"
                                                placeholder="e.g. IRCC123456"
                                                value="<?= e($ircc) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= e($ircc ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row row-eq-spacing">
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('projects.link_to_ircc_in_the_abs_clearing_house') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= e($pid) ?>][ircc_link]"
                                                placeholder="https://absch.cbd.int/ircc/..."
                                                value="<?= e($ircc_link) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= e($ircc_link ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('projects.validity_of_the_permit') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= e($pid) ?>][validity]"
                                                placeholder="e.g. 2024-2029, indefinite…"
                                                value="<?= e($validity) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= e($validity ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- does the permit include restrictuons to transfer generic materials to third party?  -->
                                <div class="form-group">
                                    <?php if ($canEditBasic): ?>
                                        <input type="hidden" name="permits[<?= e($pid) ?>][restricts_transfer]" value="0">
                                        <input
                                            type="checkbox"
                                            name="permits[<?= e($pid) ?>][restricts_transfer]"
                                            value="1"
                                            onchange="$('#restriction-details-<?= e($pid) ?>').toggleClass('hidden', !this.checked);"
                                            <?= $restricts_transfer ? 'checked' : '' ?>>
                                        <label class="ml-5"><?= lang('projects.the_permit_includes_restrictions_to_transfer_generic_materials_to_third_par') ?></label>
                                    <?php else: ?>
                                        <div class="small">
                                            <?php if ($restricts_transfer) { ?>
                                                <?= lang('projects.the_permit_includes_restrictions_to_transfer_generic_materials_to_third_par') ?>
                                            <?php } else { ?>
                                                <?= lang('projects.the_permit_does_not_include_restrictions_to_transfer_generic_materials_to_t') ?>
                                            <?php } ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- if yes: add comment -->
                                    <div class="form-group mt-2 <?= $restricts_transfer ? '' : 'hidden' ?>" id="restriction-details-<?= e($pid) ?>">
                                        <label class="small mb-1"><?= lang('projects.please_specify_the_restrictions') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <textarea
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= e($pid) ?>][restriction_details]"><?= e($restriction_details) ?></textarea>
                                        <?php else: ?>
                                            <div class="small"><?= e($restriction_details ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Main benefit sharing commitments and deadlines -->
                                <div class="form-group">
                                    <?php if ($canEditBasic): ?>
                                        <label class="small mb-1"><?= lang('projects.main_benefit_sharing_commitments_and_deadlines') ?></label>
                                        <textarea
                                            type="text"
                                            class="form-control"
                                            name="permits[<?= e($pid) ?>][benefit_sharing]"><?= e($p['benefit_sharing'] ?? '') ?></textarea>
                                    <?php else: ?>
                                        <div class="small"><?= e($p['benefit_sharing'] ?? '–') ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- only if label is A: declare? Please upload -->

                                <?php if ($countryLabel === 'A') { ?>
                                    <div class="form-group">
                                        <?= lang('projects.have_you_submitted_the_due_diligence_declaration_for_this_permit_to_the_abs') ?>
                                        <a href="https://nagoyaprotocol-hub.de/my-obligations/#obligation-2" target="_blank" rel="noopener noreferrer"><i class="ph ph-info"></i></a>
                                        <?php if ($canEditBasic): ?>
                                            <input type="hidden" name="permits[<?= e($pid) ?>][declared]" value="0">
                                            <div class="mt-5">
                                                <input
                                                    type="checkbox"
                                                    name="permits[<?= e($pid) ?>][declared]"
                                                    value="1"
                                                    id="declared-<?= e($pid) ?>"
                                                    <?= $declared ? 'checked' : '' ?>>
                                                <label class="ml-5" for="declared-<?= e($pid) ?>"><?= lang('projects.yes_i_have_submitted_the_declaration') ?></label>
                                            </div>
                                            <small class="text-muted">
                                                <?= lang('projects.if_you_have_submitted_the_declaration_please_upload_a_copy_of_the_confirmat') ?>
                                            </small>
                                        <?php else: ?>
                                            <div class="small">
                                                <?php if ($declared) { ?>
                                                    <?= lang('projects.yes_the_declaration_has_been_submitted') ?>
                                                <?php } else { ?>
                                                    <?= lang('projects.no_the_declaration_has_not_yet_been_submitted') ?>
                                            </div>
                                        <?php } ?>
                                    <?php endif; ?>
                                    </div>


                                <?php } ?>


                                <hr>


                                <div class="form-group">
                                    <label class="small mb-1"><?= lang('projects.comment_from_abs_team') ?></label>
                                    <?php if ($canValidateABS): ?>
                                        <textarea
                                            type="text"
                                            class="form-control"
                                            name="permits[<?= e($pid) ?>][comment]"><?= e($comment) ?></textarea>
                                    <?php else: ?>
                                        <div class="small"><?= e($comment ?: '–') ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($canValidateABS): ?>
                                    <div class="mb-5">
                                        <label class="inline-flex align-items-center small">
                                            <input
                                                type="checkbox"
                                                name="permits[<?= e($pid) ?>][checked]"
                                                value="1"
                                                <?= $checked ? 'checked' : '' ?>>
                                            <span class="ml-5">
                                                <?= lang('projects.abs_team_has_checked_and_validated_all_information_for_this_permit') ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php elseif ($status === 'granted'): ?>
                                    <div class="small text-muted mb-5">
                                        <?php if ($checked): ?>
                                            <span class="badge tiny success">
                                                <i class="ph ph-check"></i> <?= lang('projects.validated_by_abs_team') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge tiny warning">
                                                <i class="ph ph-warning"></i> <?= lang('projects.validation_pending') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Documents list -->
                                <div class="mb-5">
                                    <h5 class="mb-5">
                                        <i class="ph-duotone ph-paperclip"></i>
                                        <?= lang('common.documents') ?>
                                    </h5>
                                    <?php if (!empty($docs)): ?>
                                        <table class="table table-sm mb-5">
                                            <tbody>
                                                <?php foreach ($docs as $doc):
                                                    $file_url = ROOTPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension'];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <a href="<?= $file_url ?>" target="_blank">
                                                                    <strong>
                                                                        <?= $Vocabulary->getValue('nagoya-document-types', $doc['name'] ?? '-') ?>
                                                                        <i class="ph ph-download"></i>
                                                                    </strong>
                                                                </a>
                                                                <small class="text-muted">
                                                                    <?= lang('common.uploaded_by') ?>
                                                                    <?= $DB->getNameFromId($doc['uploaded_by']) ?>
                                                                    <?= lang('common.on') ?> <?= date('d.m.Y', strtotime($doc['uploaded'])) ?>
                                                                </small>
                                                            </div>
                                                            <?= e($doc['description'] ?? '') ?><br>
                                                            <small class="text-muted"><?= e($doc['filename']) ?> (<?= (int)$doc['size'] ?> Bytes)</small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted small mb-5">
                                            <?= lang('projects.no_documents_uploaded_yet_for_this_permit') ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($canUploadDocs): ?>
                                        <a href="#docs-permit-<?= e($pid) ?>" class="btn small" data-toggle="modal">
                                            <i class="ph ph-upload"></i>
                                            <?= lang('projects.upload_documents') ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>

                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($canEditBasic): ?>
                    <button type="button" class="btn small outline" id="add-permit">
                        <i class="ph ph-plus"></i>
                        <?= lang('common.add_permit') ?>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($canEditBasic): ?>
                <div class="mt-15">
                    <button type="submit" class="btn success">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('projects.save_permit_information') ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Shared notes column -->
    <div class="col-md-4">

        <?php if (!empty($permitNotes)): ?>
            <div class="box permit-notes-list mb-10" style="max-height: 60vh; overflow-y:auto;">
                <table class="table simple small">

                    <?php foreach (array_reverse($permitNotes) as $note): ?>
                        <tr>
                            <td>
                                <div class="d-flex justify-content-between mb-5">
                                    <strong><i class="ph-duotone ph-user text-primary"></i> <?= e($DB->getNameFromId($note['by'] ?? '') ?: ($note['by'] ?? '')) ?></strong>
                                    <span class="text-muted"><?= !empty($note['at']) ? format_date($note['at']) : '' ?></span>
                                </div>
                                <div class="">
                                    <?= nl2br(e($note['message'] ?? '')) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <div class="box padded text-muted">
                <?= lang('common.no_notes_added_yet') ?>
            </div>
        <?php endif; ?>

        <?php if ($canAddNotes): ?>
            <form method="post" action="<?= ROOTPATH ?>/crud/nagoya/add-permit-note/<?= $id ?>" class="box padded">
                <div class="form-group">
                    <label class="font-weight-bold small">
                        <?= lang('common.add_note') ?>
                    </label>
                    <textarea
                        name="message"
                        rows="3"
                        class="form-control"
                        placeholder="<?= lang('common.short_note_on_communication_decisions_or_next_steps') ?>"></textarea>
                </div>
                <button type="submit" class="btn small primary">
                    <i class="ph ph-paper-plane-right"></i>
                    <?= lang('common.save_note') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- modals for all permit uploads -->
<?php foreach ($permits as $index => $p):
    $pid = $p['id'] ?? ('permit_' . $index);
?>
    <div class="modal fade" id="docs-permit-<?= e($pid) ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <h5 class="title">
                    <i class="ph-duotone ph-upload"></i>
                    <?= lang('projects.upload_document_for_permit') ?>: <q><?= e($p['name'] ?? '') ?></q>
                </h5>

                <p>
                    <i class="ph-duotone ph-warning text-danger"></i>
                    <?= lang('projects.please_make_sure_to_save_your_progress_on_the_main_permit_form_before_uploa') ?>
                </p>

                <form action="<?= ROOTPATH ?>/data/upload"
                    method="post"
                    enctype="multipart/form-data"
                    class="small">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" id="upload-file-<?= e($pid) ?>" name="file" class="custom-file-input" required>
                            <label for="upload-file-<?= e($pid) ?>" class="custom-file-label">
                                <?= lang('common.choose_a_file') ?>
                            </label>
                        </div>
                    </div>

                    <!-- Basis-Felder für zentrale Upload-Route -->
                    <input type="hidden" name="values[type]" value="nagoya-permit">
                    <input type="hidden" name="values[id]" value="<?= $id ?>">

                    <!-- Dokumenttyp über Vocabulary, z.B. eigenes Nagoya-Vocab -->
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[name]" placeholder="Name" required>
                            <?php
                            $vocab = $Vocabulary->getValues('nagoya-document-types');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>"><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label class="required"><?= lang('common.doc_type') ?></label>
                    </div>

                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[description]" placeholder="<?= lang('common.description') ?>">
                        <label><?= lang('common.description') ?></label>
                    </div>

                    <!-- Kontext-Felder für Nagoya -->
                    <input type="hidden" name="values[permit_id]" value="<?= $pid ?>">
                    <input type="hidden" name="values[country_code]" value="<?= e($code) ?>">

                    <!-- Zurück zur Permit-Seite für dieses Land -->
                    <input type="hidden" name="values[redirect]"
                        value="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $id ?>/<?= urlencode($countryId) ?>">

                    <button class="btn primary" type="submit">
                        <i class="ph ph-upload-simple"></i>
                        <?= lang('common.upload_document') ?>
                    </button>
                </form>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>


<!-- template for new permit block -->
<div class="box padded permit-block hidden" data-permit-id="**" id="template">
    <h3 class="title">
        <i class="ph-duotone ph-file-text"></i>
        <?= lang('projects.new_permit') ?>
    </h3>
    <input type="hidden" name="permits[**][id]" value="**">
    <div class="d-flex justify-content-between align-items-center mb-20">
        <div>
            <label class="small mb-1"><?= lang('projects.permit_name') ?></label>
            <input type="text" class="form-control" name="permits[**][name]" value="" placeholder="<?= lang('projects.e_g_pic_mat_abs_permit') ?>">
        </div>
        <div class="text-right small">
            <label class="small mb-1"><?= lang('common.status') ?></label>
            <select name="permits[**][status]" class="form-control">
                <option value="" disabled="">Status</option>
                <option value="needed" selected>Erforderlich</option>
                <option value="requested">Beantragt</option>
                <option value="granted">Erteilt</option>
                <option value="not-applicable">Nicht zutreffend</option>
            </select>
        </div>
    </div>
    <small class="text-muted">
        <i class="ph ph-info"></i>
        <?= lang('projects.please_save_the_permit_information_to_see_more_options_for_this_permit') ?>
    </small>
</div>

<script>
    // simple JS to add a new empty permit block
    // comments in English
    $(function() {
        $('#add-permit').on('click', function() {
            var $list = $('#permit-list');
            var newId = 'permit_' + Date.now();
            var $template = $('#template');
            var $clone = $template.clone();
            $clone.removeAttr('id');
            $clone.removeClass('hidden');
            $clone.attr('data-permit-id', newId);
            $clone.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace('**', newId);
                    $(this).attr('name', name);
                }
                if ($(this).attr('type') === 'hidden' && $(this).attr('name').includes('[id]')) {
                    $(this).val(newId);
                }
            });
            $list.append($clone);
        });
    });
</script>