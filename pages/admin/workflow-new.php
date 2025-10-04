<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>

            <p>
                <?= lang('The ID is used internally to save this workflow and associate activities to it. Therefore, it must be unique and may only contain lowercase letters (a-z), numbers (0-9), and hyphens (-). Spaces and special characters are not allowed.', 'Die ID wird intern verwendet, um diesen Workflow zu speichern und ihm Aktivitäten zuzuordnen. Daher muss sie eindeutig sein und darf nur Kleinbuchstaben (a-z), Zahlen (0-9) und Bindestriche (-) enthalten. Leerzeichen und Sonderzeichen sind nicht zulässig.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="used-ids">
                <?php foreach ($osiris->adminWorkflows->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>


<form action="<?= ROOTPATH ?>/crud/workflows/create" method="post" id="group-form">

    <div class="box padded">
        <h4 class="title">
            <?= lang('New workflow', 'Neuer Workflow') ?>
        </h4>

        <div class="form-group">
            <label for="id" class="required">ID</label>
            <input type="text" class="form-control" name="values[id]" id="id" value="<?= $form['id'] ?? '' ?>" <?= !empty($form) ? 'disabled' : '' ?> oninput="sanitizeID(this, '#used-ids li')" required>

            <small>
                <a href="#unique"><i class="ph ph-info"></i>
                    <?= lang('Important! Must be unique.', 'Wichtig! Die ID muss einzigartig sein.') ?>
                </a>
            </small>
        </div>

        <div class="form-group">
            <label for="name" class="required "><?= lang('Name of the workflow', 'Name des Workflow') ?></label>
            <input type="text" class="form-control" name="values[name]" required value="<?= $form['name'] ?? '' ?>">
        </div>

        <h5><?= lang('Steps', 'Schritte') ?></h5>

        <p class="text-danger">
            <?= lang('Steps can be defined after you have saved the workflow once.', 'Schritte können definiert werden, sobald du den Workflow einmal gespeichert hast.') ?>
        </p>

        <button type="submit" class="btn success" id="submitBtn">
            <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
        </button>

    </div>
</form>


<?php include_once BASEPATH . '/header-editor.php'; ?>
<script>

</script>