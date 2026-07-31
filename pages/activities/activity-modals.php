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


<?php if (
  $Settings->featureEnabled('spectrum')
  && $doc['type'] === 'publication'
  && isset($doc['doi'])
  && array_key_exists('openalex', $doc)
) :
  include_once BASEPATH . '/php/Spectrum.php';
  $Spectrum = new Spectrum();
?>
  <div class="modal" id="spectrum-editor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form action="<?= ROOTPATH ?>/crud/activities/update-spectrum/<?= $id ?>" method="post" class="mb-20">
          <div class="modal-header">
            <h5 class="modal-title">
              <?= lang('Edit research spectrum', 'Forschungsspektrum bearbeiten') ?>
            </h5>
          </div>

          <div class="modal-body">
            <p class="text-muted">
              <?= lang(
                'The selected topics affect the research profiles of the associated researchers.',
                'Die ausgewählten Themen beeinflussen das Forschungsspektrum der zugehörigen Personen.'
              ) ?>
            </p>

            <label for="spectrum-topics">
              <?= lang('Current Spectrum topics', 'Aktuelle Themen des Forschungsspektrums') ?>
            </label>
            <input type="hidden" name="topics" value="">
            <div id="spectrum-editor-list">
              <?php foreach (($openalex['topics'] ?? []) as $topic) : ?>
                <div class="spectrum-topic box">
                  <input type="hidden" name="topics[]" value="<?= $topic['id'] ?>">
                  <div>
                    <div class="popover-title spectrum-<?= $topic['domain_id'] ?> font-size-12"><?= $topic['path'] ?></div>
                    <div class="popover-content">
                      <h5 class="mt-0"><?= $topic['name'] ?></h5>
                      <ul class="horizontal">
                        <li>Score: <?= round(floatval($topic['score'] ?? 1) * 100, 2) ?> %</li>
                        <li><a href="/spectrum/topic/<?= $topic['id'] ?>" target="_blank" rel="noopener noreferrer">Zum Spektrum</a></li>
                        <li>
                          <button type="button" class="btn danger small" onclick="this.closest('.spectrum-topic').remove()">
                            <i class="ph ph-trash"></i>
                            <?= lang('Remove', 'Entfernen') ?>
                          </button>
                        </li>
                      </ul>

                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <h4>
            <?= lang('Add new topics', 'Neue Themen hinzufügen') ?>
          </h4>
          <div id="add-new-spectrum" class="nav-search mb-20">
            <input type="text" id="spectrum-topics" placeholder="<?= lang('Search for topics', 'Suche nach Themen') ?>" class="form-control large" />
            <div class="suggestions on-focus"></div>
          </div>


          <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">
              <?= lang('Cancel', 'Abbrechen') ?>
            </button>

            <button type="submit" class="btn success">
              <i class="ph ph-floppy-disk"></i>
              <?= lang('Save', 'Speichern') ?>
            </button>
          </div>
        </form>

        <?php if (isset($openalex['manual']) && !empty($openalex['automatic_topics'] ?? null)) { ?>
          <hr>
          <form action="<?= ROOTPATH ?>/crud/activities/update-spectrum/<?= $id ?>" method="post" class="mt-20">
            <input type="hidden" name="restore" value="1">
            <button type="submit" class="btn danger small" onclick="return confirm('<?= lang('Are you sure you want to restore the automatic topics? This will remove all manually added topics.', 'Bist du sicher, dass du die automatischen Themen wiederherstellen möchtest? Dadurch werden alle manuell hinzugefügten Themen entfernt.') ?>')">
              <i class="ph ph-arrow-counter-clockwise"></i>
              <?= lang('Restore automatic topics', 'Automatische Themen wiederherstellen') ?>
            </button>
          </form>
        <?php } ?>
        
      </div>
    </div>
  </div>
  <style>
    .suggestions b {
      color: var(--primary-color)
    }

    .suggestions .no-results {
      color: var(--text-muted);
      font-style: italic;
      padding: 10px;
    }
  </style>
  <script>
    $('#spectrum-topics').on('input', function() {
      var query = $(this).val();
      if (query.length < 3) {
        $('#add-new-spectrum .suggestions').html('<div class="no-results"><?= lang("Please enter at least 3 characters to search for topics.", "Bitte gib mindestens 3 Zeichen ein, um nach Themen zu suchen.") ?></div>');
        return;
      }

      $.ajax({
        url: '<?= ROOTPATH ?>/api/openalex/topics',
        method: 'GET',
        data: {
          q: query
        },
        success: function(response) {
          if (response.count === 0) {
            $('#add-new-spectrum .suggestions').html('<div class="no-results"><?= lang("No topics found.", "Keine Themen gefunden.") ?></div>');
            return;
          }

          var suggestions = response.data.map(function(topic) {
            return `<a data-id="${topic.id}" data-domain="${topic.domain_id}"><b>${topic.name}</b><br><small>${topic.path}</small></a>`
          }).join('');
          $('#add-new-spectrum .suggestions').html(suggestions);
        }
      });
    });

    $('#add-new-spectrum .suggestions').on('mousedown', 'a', function() {
      var topicId = $(this).data('id');
      var topicName = $(this).find('b').text();
      var topicPath = $(this).find('small').text();
      var domainId = $(this).data('domain');
      var newTopic = `
        <div class="spectrum-topic box">
          <input type="hidden" name="topics[]" value="${topicId}">
          <div>
            <div class="popover-title spectrum-${domainId} font-size-12">${topicPath}</div>
            <div class="popover-content">
              <h5 class="mt-0">${topicName}</h5>
              <button type="button" class="btn danger small" onclick="this.closest('.spectrum-topic').remove()">
                <i class="ph ph-trash"></i>
                <?= lang('Remove', 'Entfernen') ?>
              </button>
            </div>
          </div>
        </div>
      `;
      $('#spectrum-editor-list').append(newTopic);
    })
  </script>

<?php endif; ?>