<?php if (empty($project)) { 
    $route = ROOTPATH . '/admin/project/create';

} ?>


<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>
            <p>
                <?= lang('Each project type must have a unique ID with which it is linked to an activity.', 'Jeder Projekttyp muss eine einzigartige ID haben, mit der er zu einer Aktivität verknüpft wird.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminProjects->distinct('id') as $k) { ?>
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

<h1>
    <?= lang('Project Settings', 'Projekt-Einstellungen') ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/admin/project/update/<?=$project['_id']?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/project">
    <?php if (isset($type) && $type != 'new') { ?>
        <input type="hidden" name="original_id" value="<?= $type ?>">
    <?php } ?>

    <div class="box">
        <div class="content">
            <div class="row row-eq-spacing">
                <div class="col-sm" >
                    <label for="id" class="required">ID</label>
                    <input type="text" class="form-control" name="values[id]" required value="<?= $type == 'new' ? '' : $type ?>" oninput="sanitizeID(this)">
                    <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('Must be unqiue', 'Muss einzigartig sein') ?></a></small>
                </div>
                <div class="col-sm" >
                    <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>

                    <div class="input-group">
                        <input type="text" class="form-control" name="values[icon]" required value="<?= $project['icon'] ?? 'placeholder' ?>" onchange="iconTest(this.value)">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="ph ph-<?= $project['icon'] ?? 'placeholder' ?>" id="test-icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm" >
                    <label for="color" class="required "><?=lang('Color', 'Farbe')?></label>
                    <input type="color" class="form-control" name="values[color]" required value="<?= $project['color'] ?? '' ?>">
                </div>
            </div>


            <div class="row row-eq-spacing">
                <div class="col-sm">
                    <label for="name" class="required ">Name (en)</label>
                    <input type="text" class="form-control" name="values[name]" required value="<?= $project['name'] ?? '' ?>">
                </div>
                <div class="col-sm">
                    <label for="name_de" class="">Name (de)</label>
                    <input type="text" class="form-control" name="values[name_de]" value="<?= $project['name_de'] ?? '' ?>">
                </div>
            </div>
        </div>

        <hr>
        <div class="content">

            <?php if ($Settings->featureEnabled('portal')) { ?>
                <div class="mb-20">
                    <input type="hidden" name="values[portfolio]" value="">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="portfolio-question" value="1" name="values[portfolio]" <?= ($project['portfolio'] ?? false) ? 'checked' : '' ?>>
                        <label for="portfolio-question">
                            <?= lang('This type of project should be visible in OSIRIS Portfolio.', 'Diese Art von Projekt sollte in OSIRIS Portfolio sichtbar sein.') ?>
                        </label>
                    </div>
                </div>
            <?php } ?>
            <div class="custom-checkbox mb-10 danger">
                <input type="checkbox" id="disable" value="true" name="values[disabled]" <?= ($project['disabled'] ?? false) ? 'checked' : '' ?>>
                <label for="disable"><?= lang('Deactivate', 'Deaktivieren') ?></label>
            </div>
            <span class="text-muted">
                <?= lang('Deactivated projects are retained for past activities, but no new ones can be added.', 'Deaktivierte Projektkategorien bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
            </span>

        </div>

        <hr>
        <div class="content">
            <label for="module" class="font-weight-bold"><?= lang('Data fields', 'Datenfelder') ?>:</label>
            <div class="author-widget">
                <div class="author-list p-10">
                    <?php
                    $module_lst = [];
                    foreach ($project['modules'] ?? array() as $module) {
                        $req = '';
                        $name = trim($module);
                        if (str_ends_with($name, '*')) {
                            $name = str_replace('*', '', $name);
                            $module = $name . "*";
                            $req = 'required';
                        }
                        $module_lst[] = $name;
                    ?>
                        <div class='author <?= $req ?>' ondblclick="toggleRequired(this)">
                            <?= $name ?>
                            <input type='hidden' name='values[modules][]' value='<?= $module ?>'>
                            <a onclick='$(this).parent().remove()'>&times;</a>
                        </div>
                    <?php } ?>

                </div>
                <div class=" footer">
                    <div class="input-group sm d-inline-flex w-auto">
                        <select class="module-input form-control">
                            <option value="" disabled selected><?= lang('Add module ...', 'Füge Module hinzu ...') ?></option>
                            <?php
                            // read custom modules first
                            $custom_modules = $osiris->adminFields->distinct('id');
                            if (!empty($custom_modules)) {
                                foreach ($custom_modules as $m) {
                                    if (in_array($m, $module_lst)) continue;
                            ?>
                                    <option><?= $m ?></option>
                                <?php } ?>
                                <option disabled>---</option>
                            <?php
                            }
                            foreach (Project::FIELDS as $m) {
                                if (in_array($m, $module_lst)) continue;
                            ?>
                                <option><?= $m ?></option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn secondary h-full" type="button" onclick="addModule();">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <p>
                <i class="ph ph-info text-signal"></i>
                <?=lang('Please note that name, title, status, and time are always a required part of the form.', 'Zur Information: Name, Titel, Status und Zeitrahmen des Projektes sind immer Teil des Formulars sowie Pflichfelder.')?>
            </p>
        </div>
       
    </div>
    <button class="btn success" id="submitBtn"><?= lang('Save', 'Speichern') ?></button>
</form>


<?php if (!empty($form)) { ?>

    <?php if ($member == 0) { ?>
        <div class="alert danger mt-20">
            <form action="<?= ROOTPATH ?>/crud/types/delete/<?= $id ?>" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/categories/<?= $project['parent'] ?>">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                <span class="ml-20"><?= lang('Warning! Cannot be undone.', 'Warnung, kann nicht rückgängig gemacht werden!') ?></span>
            </form>
        </div>
    <?php } else { ?>

        <div class="alert danger mt-20">
            <?= lang("Can\'t delete type: $member activities associated.", "Kann Typ nicht löschen: $member Aktivitäten zugeordnet.") ?><br>
            <a href='<?= ROOTPATH ?>/activities/search#{"$and":[{"type":"<?= $id ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('View activities', 'Aktivitäten zeigen') ?>
            </a>

        </div>
    <?php } ?>


<?php } ?>


<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<script src="<?= ROOTPATH ?>/js/admin-categories.js"></script>