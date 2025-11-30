<?php

if (!$country) {
?>
    <div class="alert danger">
        <?= lang('Country not found for this project.', 'Land für dieses Projekt nicht gefunden.') ?>
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
            'provider'  => '',
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
?>

<h1 class="mb-0">
    <i class="ph-duotone ph-file-text"></i>
    <?= lang('ABS permits for', 'ABS-Genehmigungen für') ?>
    <?= ($DB->getCountry($code, lang('name', 'name_de'))) ?>
</h1>
    <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $id ?>#nagoya">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to all countries', 'Zurück zu allen Ländern') ?>
    </a>

<div class="d-flex align-items-center gap-10 mt-20">
    <b><?= lang('Nagoya status', 'Nagoya-Status') ?>:</b>
    <!-- <?= Nagoya::badge(DB::doc2Arr($project), false) ?> -->
    <?= Nagoya::countryBadge(DB::doc2Arr($country)) ?>
</div>


<div class="row row-eq-spacing my-0">
    <!-- Permits column -->
    <div class="col-md-8 my-0">
        <h2 class="title">
            <i class="ph-duotone ph-file-text"></i>
            <?= lang('Permits for this country', 'Genehmigungen für dieses Land') ?>
        </h2>
        <?php if (!$canEditBasic): ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang(
                    'You can see the permit information for this country. Changes can only be made by the ABS Compliance Team.',
                    'Du kannst die Genehmigungsinformationen für dieses Land einsehen. Änderungen können nur vom ABS-Compliance-Team vorgenommen werden.'
                ) ?>
            </p>
        <?php else: ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang(
                    'Please keep permit information up to date. Use the fields below to edit names, identifiers and status. Document uploads are handled per permit.',
                    'Bitte halte die Genehmigungsinformationen aktuell. Nutze die Felder unten, um Namen, Kennungen und Status zu bearbeiten. Dokumente können pro Genehmigung hochgeladen werden.'
                ) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Shared notes column -->
    <div class="col-md-4 my-0">
        <h2 class="title">
            <i class="ph-duotone ph-chats-circle"></i>
            <?= lang('Shared notes', 'Gemeinsame Notizen') ?>
        </h2>
        <?php if (!$canAddNotes): ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang(
                    'You can see shared notes related to permits for this country. Adding notes is restricted to the ABS Compliance Team.',
                    'Du kannst gemeinsame Notizen zu Genehmigungen für dieses Land einsehen. Das Hinzufügen von Notizen ist auf das ABS-Compliance-Team beschränkt.'
                ) ?>
            </p>
        <?php else: ?>
            <p class="text-muted mb-0 font-size-12">
                <?= lang(
                    'Use the shared notes area to document communication and decisions related to ABS permits for this country.',
                    'Nutze den Bereich für gemeinsame Notizen, um Kommunikation und Entscheidungen zu ABS-Genehmigungen für dieses Land zu dokumentieren.'
                ) ?>
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
                        $validity  = $p['validity'] ?? '';
                        // $provider  = $p['provider'] ?? '';
                        $restricts_transfer = !empty($p['restricts_transfer']);
                        $restriction_details = $p['restriction_details'] ?? '';
                        $benefit_sharing = $p['benefit_sharing'] ?? '';
                        $comment   = $p['comment'] ?? '';
                        $checked   = !empty($p['checked']);
                        $docs      = $docsByPermit[$pid] ?? [];
                    ?>
                        <div class="box padded permit-block" data-permit-id="<?= htmlspecialchars($pid) ?>">
                            <div class="dropdown float-right">
                                <button class="btn link small text-danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                    <i class="ph-duotone ph-trash"></i>
                                    <span class="sr-only"><?= lang('Delete permit', 'Genehmigung löschen') ?></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-1">
                                    <div class="content">
                                        <?= lang('Are you sure you want to delete this permit? This action cannot be undone.', 'Möchten Sie diese Genehmigung wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.') ?>
                                        <button type="button" class="btn danger" onclick="$(this).parent('.permit-block').remove();">
                                            <i class="ph ph-trash"></i>
                                            <?= lang('Yes, delete permit', 'Ja, Genehmigung löschen') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <h3 class="title">
                                <i class="ph-duotone ph-file-text"></i>
                                <?= htmlspecialchars($name) ?>
                            </h3>
                            <input type="hidden"
                                name="permits[<?= htmlspecialchars($pid) ?>][id]"
                                value="<?= htmlspecialchars($pid) ?>">

                            <div class="d-flex justify-content-between align-items-center mb-20">
                                <div>
                                    <?php if ($canEditBasic): ?>
                                        <input
                                            type="text"
                                            class="form-control w-300"
                                            name="permits[<?= htmlspecialchars($pid) ?>][name]"
                                            value="<?= htmlspecialchars($name) ?>"
                                            placeholder="<?= lang('Permit name (e.g. PIC, MAT, ABS permit…)', 'Name der Genehmigung (z.B. PIC, MAT, ABS-Genehmigung…)') ?>">
                                    <?php else: ?>
                                        <strong><?= htmlspecialchars($name ?: lang('Unnamed permit', 'Unbenannte Genehmigung')) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($comment) && !$canEditBasic): ?>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($comment) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="text-right small">
                                    <?php if ($canEditBasic): ?>
                                        <select
                                            name="permits[<?= htmlspecialchars($pid) ?>][status]"
                                            class="form-control">
                                            <option value="" disabled><?= lang('Status', 'Status') ?></option>
                                            <option value="needed" <?= $status === 'needed'   ? 'selected' : '' ?>><?= lang('Needed', 'Erforderlich') ?></option>
                                            <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>><?= lang('Requested', 'Beantragt') ?></option>
                                            <option value="granted" <?= $status === 'granted'  ? 'selected' : '' ?>><?= lang('Granted', 'Erteilt') ?></option>
                                            <option value="not-applicable" <?= $status === 'not-applicable' ? 'selected' : '' ?>><?= lang('Not applicable', 'Nicht zutreffend') ?></option>
                                        </select>
                                    <?php else: ?>
                                        <?php
                                        $statusLabel = '';
                                        $statusClass = 'badge muted';
                                        if ($status === 'needed') {
                                            $statusLabel = lang('Needed', 'Erforderlich');
                                            $statusClass = 'badge signal';
                                        } elseif ($status === 'requested') {
                                            $statusLabel = lang('Requested', 'Beantragt');
                                            $statusClass = 'badge signal';
                                        } elseif ($status === 'granted') {
                                            $statusLabel = lang('Granted', 'Erteilt');
                                            $statusClass = 'badge success';
                                        } elseif ($status === 'not-applicable') {
                                            $statusLabel = lang('Not applicable', 'Nicht zutreffend');
                                            $statusClass = 'badge muted';
                                        } else {
                                            $statusLabel = lang('Unknown', 'Unbekannt');
                                        }
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($status !== 'not-applicable') { ?>
                                <div class="row row-eq-spacing">
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('Permit number', 'Genehmigungsnummer') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= htmlspecialchars($pid) ?>][identifier]"
                                                placeholder="e.g. 12345-ABCD"
                                                value="<?= htmlspecialchars($identifier) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= htmlspecialchars($identifier ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('IRCC number', 'IRCC-Nummer') ?> <small>(Internationally Recognized Certificate of Compliance)</small></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= htmlspecialchars($pid) ?>][ircc]"
                                                placeholder="e.g. IRCC123456"
                                                value="<?= htmlspecialchars($ircc) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= htmlspecialchars($ircc ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row row-eq-spacing">
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('Link to IRCC in the ABS Clearing House', 'Link zum IRCC im ABS Clearing-House') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= htmlspecialchars($pid) ?>][ircc_link]"
                                                placeholder="https://absch.cbd.int/ircc/..."
                                                value="<?= htmlspecialchars($ircc_link) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= htmlspecialchars($ircc_link ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small mb-1"><?= lang('Validity of the permit', 'Gültigkeit der Genehmigung') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= htmlspecialchars($pid) ?>][validity]"
                                                placeholder="e.g. 2024-2029, indefinite…"
                                                value="<?= htmlspecialchars($validity) ?>">
                                        <?php else: ?>
                                            <div class="small"><?= htmlspecialchars($validity ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- does the permit include restrictuons to transfer generic materials to third party?  -->
                                <div class="form-group">
                                    <?php if ($canEditBasic): ?>
                                        <input type="hidden" name="permits[<?= htmlspecialchars($pid) ?>][restricts_transfer]" value="0">
                                        <input
                                            type="checkbox"
                                            name="permits[<?= htmlspecialchars($pid) ?>][restricts_transfer]"
                                            value="1"
                                            onchange="$('#restriction-details-<?= htmlspecialchars($pid) ?>').toggleClass('hidden', !this.checked);"
                                            <?= $restricts_transfer ? 'checked' : '' ?>>
                                        <label class="ml-5"><?= lang('The permit includes restrictions to transfer generic materials to third parties', 'Die Genehmigung enthält Einschränkungen für die Weitergabe generischer Materialien an Dritte') ?></label>
                                    <?php else: ?>
                                        <div class="small">
                                            <?php if ($restricts_transfer) { ?>
                                                <?= lang('The permit includes restrictions to transfer generic materials to third parties', 'Die Genehmigung enthält Einschränkungen für die Weitergabe generischer Materialien an Dritte') ?>
                                            <?php } else { ?>
                                                <?= lang('The permit does not include restrictions to transfer generic materials to third parties', 'Die Genehmigung enthält keine Einschränkungen für die Weitergabe generischer Materialien an Dritte') ?>
                                            <?php } ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- if yes: add comment -->
                                    <div class="form-group mt-2 <?= $restricts_transfer ? '' : 'hidden' ?>" id="restriction-details-<?= htmlspecialchars($pid) ?>">
                                        <label class="small mb-1"><?= lang('Please specify the restrictions', 'Bitte geben Sie die Einschränkungen an') ?></label>
                                        <?php if ($canEditBasic): ?>
                                            <textarea
                                                type="text"
                                                class="form-control"
                                                name="permits[<?= htmlspecialchars($pid) ?>][restriction_details]"><?= htmlspecialchars($restriction_details) ?></textarea>
                                        <?php else: ?>
                                            <div class="small"><?= htmlspecialchars($restriction_details ?: '–') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Main benefit sharing commitments and deadlines -->
                                <div class="form-group">
                                    <?php if ($canEditBasic): ?>
                                        <label class="small mb-1"><?= lang('Main benefit-sharing commitments and deadlines', 'Hauptverpflichtungen und Fristen zur Vorteilsbeteiligung') ?></label>
                                        <textarea
                                            type="text"
                                            class="form-control"
                                            name="permits[<?= htmlspecialchars($pid) ?>][benefit_sharing]"><?= htmlspecialchars($p['benefit_sharing'] ?? '') ?></textarea>
                                    <?php else: ?>
                                        <div class="small"><?= htmlspecialchars($p['benefit_sharing'] ?? '–') ?></div>
                                    <?php endif; ?>
                                </div>

                                <hr>


                                <div class="form-group">
                                    <label class="small mb-1"><?= lang('Comment from ABS team', 'Kommentar vom ABS-Team') ?></label>
                                    <?php if ($canValidateABS): ?>
                                        <textarea
                                            type="text"
                                            class="form-control"
                                            name="permits[<?= htmlspecialchars($pid) ?>][comment]"><?= htmlspecialchars($comment) ?></textarea>
                                    <?php else: ?>
                                        <div class="small"><?= htmlspecialchars($comment ?: '–') ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($canValidateABS): ?>
                                    <div class="mb-5">
                                        <label class="inline-flex align-items-center small">
                                            <input
                                                type="checkbox"
                                                name="permits[<?= htmlspecialchars($pid) ?>][checked]"
                                                value="1"
                                                <?= $checked ? 'checked' : '' ?>>
                                            <span class="ml-5">
                                                <?= lang(
                                                    'ABS team has checked and validated all information for this permit.',
                                                    'ABS-Team hat alle Informationen zu dieser Genehmigung geprüft und validiert.'
                                                ) ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php elseif ($status === 'granted'): ?>
                                    <div class="small text-muted mb-5">
                                        <?php if ($checked): ?>
                                            <span class="badge tiny success">
                                                <i class="ph ph-check"></i> <?= lang('validated by ABS team', 'vom ABS-Team validiert') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge tiny warning">
                                                <i class="ph ph-warning"></i> <?= lang('validation pending', 'Validierung ausstehend') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Documents list -->
                                <div class="mb-5">
                                    <h5 class="mb-5">
                                        <i class="ph-duotone ph-paperclip"></i>
                                        <?= lang('Documents', 'Dokumente') ?>
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
                                                                        <?= lang($doc['name'] ?? 'UNKNOWN', $doc['name_de'] ?? null) ?>
                                                                        <i class="ph ph-download"></i>
                                                                    </strong>
                                                                </a>
                                                                <small class="text-muted">
                                                                    <?= lang('Uploaded by', 'Hochgeladen von') ?>
                                                                    <?= $DB->getNameFromId($doc['uploaded_by']) ?>
                                                                    <?= lang('on', 'am') ?> <?= date('d.m.Y', strtotime($doc['uploaded'])) ?>
                                                                </small>
                                                            </div>
                                                            <?= htmlspecialchars($doc['description'] ?? '') ?><br>
                                                            <small class="text-muted"><?= htmlspecialchars($doc['filename']) ?> (<?= (int)$doc['size'] ?> Bytes)</small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted small mb-5">
                                            <?= lang('No documents uploaded yet for this permit.', 'Für diese Genehmigung wurden noch keine Dokumente hochgeladen.') ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($canUploadDocs): ?>
                                        <a href="#docs-permit-<?= htmlspecialchars($pid) ?>" class="btn small" data-toggle="modal">
                                            <i class="ph ph-upload"></i>
                                            <?= lang('Upload Documents', 'Dokumente hochladen') ?>
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
                        <?= lang('Add permit', 'Genehmigung hinzufügen') ?>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($canEditBasic): ?>
                <div class="mt-15">
                    <button type="submit" class="btn success">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('Save permit information', 'Genehmigungsinformationen speichern') ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Shared notes column -->
    <div class="col-md-4">

        <?php if (!empty($permitNotes)): ?>
            <div class="box padded permit-notes-list mb-10" style="max-height: 60vh; overflow-y:auto;">
                <?php foreach (array_reverse($permitNotes) as $note): ?>
                    <div class="border rounded p-5 mb-5">
                        <div class="d-flex justify-content-between mb-5">
                            <strong><i class="ph-duotone ph-user text-primary"></i> <?= htmlspecialchars($DB->getNameFromId($note['by'] ?? '') ?: ($note['by'] ?? '')) ?></strong>
                            <span class="text-muted"><?= !empty($note['at']) ? format_date($note['at']) : '' ?></span>
                        </div>
                        <div class="">
                            <?= nl2br(htmlspecialchars($note['message'] ?? '')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="box padded text-muted">
                <?= lang('No notes added yet.', 'Noch keine Notizen vorhanden.') ?>
            </div>
        <?php endif; ?>

        <?php if ($canAddNotes): ?>
            <form method="post" action="<?= ROOTPATH ?>/crud/nagoya/add-permit-note/<?= $id ?>" class="box padded">
                <input type="hidden" name="country_id" value="<?= htmlspecialchars($countryId) ?>">
                <div class="form-group">
                    <label class="font-weight-bold small">
                        <?= lang('Add note', 'Notiz hinzufügen') ?>
                    </label>
                    <textarea
                        name="message"
                        rows="3"
                        class="form-control"
                        placeholder="<?= lang('Short note on communication, decisions or next steps…', 'Kurze Notiz zu Kommunikation, Entscheidungen oder nächsten Schritten…') ?>"></textarea>
                </div>
                <button type="submit" class="btn small primary">
                    <i class="ph ph-paper-plane-right"></i>
                    <?= lang('Save note', 'Notiz speichern') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- modals for all permit uploads -->
<?php foreach ($permits as $index => $p):
    $pid = $p['id'] ?? ('permit_' . $index);
?>
    <div class="modal fade" id="docs-permit-<?= htmlspecialchars($pid) ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <h5 class="title">
                    <i class="ph-duotone ph-upload"></i>
                    <?= lang('Upload document for permit', 'Dokument für Genehmigung hochladen') ?>: <q><?= htmlspecialchars($p['name'] ?? '') ?></q>
                </h5>

                <p>
                    <i class="ph-duotone ph-warning text-danger"></i>
                    <?= lang('Please make sure to save your progress on the main permit form before uploading documents, because the upload will reload the page.', 'Bitte stelle sicher, dass du deine Fortschritte im Hauptformular für Genehmigungen gespeichert hast, bevor du Dokumente hochlädst, da der Upload die Seite neu laden wird.') ?>
                </p>

                <form action="<?= ROOTPATH ?>/data/upload"
                    method="post"
                    enctype="multipart/form-data"
                    class="small">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" id="upload-file-<?= htmlspecialchars($pid) ?>" name="file" class="custom-file-input" required>
                            <label for="upload-file-<?= htmlspecialchars($pid) ?>" class="custom-file-label">
                                <?= lang('Choose a file', 'Wähle eine Datei aus') ?>
                            </label>
                        </div>
                    </div>

                    <!-- Basis-Felder für zentrale Upload-Route -->
                    <input type="hidden" name="values[type]" value="nagoya-permit">
                    <input type="hidden" name="values[id]" value="<?= $id ?>">

                    <!-- Dokumenttyp über Vocabulary, z.B. eigenes Nagoya-Vocab -->
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[name]" placeholder="Name" required>
                            <option value="PIC">PIC</option>
                            <option value="MAT">MAT</option>
                            <option value="DD-DECL"><?= lang('Due diligence declaration', 'Sorgfaltserklärung') ?></option>
                            <option value="EMAIL"><?= lang('Email / correspondence', 'E-Mail / Korrespondenz') ?></option>
                            <option value="OTHER"><?= lang('Other', 'Sonstiges') ?></option>
                            <?php
                            // entweder neues Vokabular 'nagoya-document-types'
                            // $vocab = $Vocabulary->getValues('nagoya-document-types');
                            // foreach ($vocab as $v) { echo "<option value=\"{$v['id']}\">" . lang($v['en'], $v['de'] ?? null) . "</option>"; }
                            ?>
                        </select>
                        <label class="required"><?= lang('Document type', 'Dokumenttyp') ?></label>
                    </div>

                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[description]" placeholder="<?= lang('Description', 'Beschreibung') ?>">
                        <label><?= lang('Description', 'Beschreibung') ?></label>
                    </div>

                    <!-- Kontext-Felder für Nagoya -->
                    <input type="hidden" name="values[permit_id]" value="<?= $pid ?>">
                    <input type="hidden" name="values[country_code]" value="<?= htmlspecialchars($code) ?>">

                    <!-- Zurück zur Permit-Seite für dieses Land -->
                    <input type="hidden" name="values[redirect]"
                        value="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $id ?>/<?= urlencode($countryId) ?>">

                    <button class="btn primary" type="submit">
                        <i class="ph ph-upload-simple"></i>
                        <?= lang('Upload document', 'Dokument hochladen') ?>
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
        <?= lang('New permit', 'Neue Genehmigung') ?>
    </h3>
    <input type="hidden" name="permits[**][id]" value="**">
    <div class="d-flex justify-content-between align-items-center mb-20">
        <div>
            <label class="small mb-1"><?= lang('Permit name', 'Name der Genehmigung') ?></label>
            <input type="text" class="form-control" name="permits[**][name]" value="" placeholder="<?= lang('e.g. PIC, MAT, ABS permit…', 'z.B. PIC, MAT, ABS-Genehmigung…') ?>">
        </div>
        <div class="text-right small">
            <label class="small mb-1"><?= lang('Status', 'Status') ?></label>
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
        <?= lang('Please save the permit information to see more options for this permit.', 'Bitte speichere die Genehmigungsinformationen, um weitere Optionen für diese Genehmigung zu sehen.') ?>
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