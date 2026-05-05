<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="modal" id="edit-files" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <a href="#close-modal" class="close" role="button" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </a>
      <h5 class="title">
        <?= lang('Upload and edit files', 'Dateien hochladen und bearbeiten') ?>
      </h5>

      <table class="table" id="files-table">
        <tbody>
          <?php foreach ($files as $file) {
            $file_url = ROOTPATH . '/uploads/' . $file['_id'] . '.' . $file['extension'];
          ?>
            <tr>
              <td class="font-size-18 text-center text-muted" style="width: 50px;">
                <i class='ph ph-file ph-<?= getFileIcon($file['extension'] ?? '') ?>'></i>
              </td>
              <td>
                <div class="float-right">
                  <div class="dropdown">
                    <button class="btn link" data-toggle="dropdown" type="button" id="edit-doc-<?= $file['_id'] ?>" aria-haspopup="true" aria-expanded="false">
                      <i class="ph ph-edit text-primary"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="edit-doc-<?= $file['_id'] ?>">
                      <div class="content">
                        <form action="<?= ROOTPATH ?>/data/document/update" method="post">
                          <div class="form-group floating-form">
                            <select class="form-control" name="name" placeholder="Name" required>
                              <?php
                              $vocab = $Vocabulary->getValues('activity-document-types');
                              foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= ($file['name'] == $v['id'] ? 'selected' : '') ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                              <?php } ?>
                            </select>
                            <label for="name" class="required"><?= lang('Document type', 'Dokumenttyp') ?></label>
                          </div>
                          <div class="form-group">
                            <label for="description"><?= lang('Description', 'Beschreibung') ?></label>
                            <textarea class="form-control" name="description" placeholder="<?= lang('Description', 'Beschreibung') ?>"><?= $file['description'] ?? '' ?></textarea>
                          </div>
                          <input type="hidden" name="id" value="<?= $file['_id'] ?>">
                          <button class="btn btn-block primary" type="submit"><?= lang('Save changes', 'Änderungen speichern') ?></button>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="dropdown">
                    <button class="btn link" data-toggle="dropdown" type="button" id="delete-doc-<?= $file['_id'] ?>" aria-haspopup="true" aria-expanded="false">
                      <i class="ph ph-trash text-danger"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="delete-doc-<?= $file['_id'] ?>">
                      <div class="content">
                        <form action="<?= ROOTPATH ?>/data/delete" method="post">
                          <span class="text-danger"><?= lang('Do you want to delete this document?', 'Möchtest du dieses Dokument wirklich löschen?') ?></span>
                          <input type="hidden" name="id" value="<?= $file['_id'] ?>">
                          <button class="btn btn-block danger" type="submit"><?= lang('Delete', 'Löschen') ?></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <h6 class="m-0">
                  <a href="<?= $file_url ?>" target="_blank" rel="noopener">
                    <?= $Vocabulary->getValue('activity-document-types', $file['name'] ?? '', lang('Other', 'Sonstiges')); ?>
                    <i class="ph ph-download"></i>
                  </a>
                </h6>
                <?= $file['description'] ?? '' ?>
                <br>
                <div class="font-size-12 text-muted d-flex align-items-center justify-content-between">
                  <div>
                    <?= $file['filename'] ?> (<?= $file['size'] ?> Bytes)
                    <br>
                    <?= lang('Uploaded by', 'Hochgeladen von') ?> <?= $DB->getNameFromId($file['uploaded_by']) ?>
                    <?= lang('on', 'am') ?> <?= date('d.m.Y', strtotime($file['uploaded'])) ?>
                  </div>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>

      <form action="<?= ROOTPATH ?>/data/upload" method="post" enctype="multipart/form-data" class="box padded">
        <h5 class="title font-size-16">
          <?= lang('Upload document', 'Dokument hochladen') ?>
        </h5>
        <div class="form-group">
          <div class="custom-file">
            <input type="file" id="upload-file" name="file" class="custom-file-input" maxsize="16777216" required>
            <label for="upload-file" class="custom-file-label"><?= lang('Choose a file', 'Wähle eine Datei aus') ?></label>
            <br><small class="text-danger">Max. 16 MB.</small>
          </div>
        </div>
        <input type="hidden" name="values[type]" value="activities">
        <input type="hidden" name="values[id]" value="<?= $id ?>">
        <div class="form-group floating-form">
          <select class="form-control" name="values[name]" placeholder="Name" required>
            <?php
            $vocab = $Vocabulary->getValues('activity-document-types');
            foreach ($vocab as $v) { ?>
              <option value="<?= $v['id'] ?>"><?= lang($v['en'], $v['de'] ?? null) ?></option>
            <?php } ?>
          </select>
          <label for="name" class="required"><?= lang('Document type', 'Dokumenttyp') ?></label>
        </div>
        <div class="form-group floating-form">
          <input type="text" class="form-control" name="values[description]" placeholder="<?= lang('Description', 'Beschreibung') ?>" value="">
          <label for="description"><?= lang('Description', 'Beschreibung') ?></label>
        </div>
        <button class="btn primary" type="submit"><?= lang('Upload', 'Hochladen') ?></button>
      </form>

      <script>
        var uploadField = document.getElementById("upload-file");

        uploadField.onchange = function() {
          if (this.files[0].size > 16777216) {
            toastError(lang("File is too large! Max. 16MB is supported!", "Die Datei ist zu groß! Max. 16MB werden unterstützt."));
            this.value = "";
          };
        };
      </script>

      <div class="text-right mt-20">
        <a href="#close-modal" class="btn mr-5" role="button"><?= lang('Close', 'Schließen') ?></a>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="edit-tags" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
        <span aria-hidden="true">&times;</span>
      </a>
      <h5 class="title">
        <?= lang('Connect ' . $tagLabel, $tagLabel . ' verknüpfen') ?>
      </h5>
      <p>
        <?= lang('Currently connected ', 'Zurzeit ausgewählte ') . $tagLabel ?>:
        <?php
        $tags = $doc['tags'] ?? [];
        if (count($tags)) {
          echo $Settings->printTags($tags, 'all-activities');
        } else {
          echo lang('No ' . $tagLabel . ' assigned yet.', 'Noch keine ' . $tagLabel . ' vergeben.');
        }
        ?>
      </p>

      <?php if ($Settings->hasPermission('activities.tags')) { ?>
        <form action="<?= ROOTPATH ?>/crud/activities/update-tags/<?= $id ?>" method="post">
          <?php
          $Settings->tagChooser($doc['tags'] ?? []);
          ?>

          <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
          </button>
        </form>
      <?php } ?>
    </div>
  </div>
</div>