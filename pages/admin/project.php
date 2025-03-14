<?php if (empty($project)) {
    $route = ROOTPATH . '/admin/project/create';
}

$project['stages']['proposal'] = [
    'name' => 'Proposal',
    'name_de' => 'Antrag',
    'modules' => [
        'abstract',
        'public',
        'internal_number',
        'website',
        'grant_sum',
        'funder',
        'funding_number',
        'grant_sum_proposed',
        'personnel',
        'ressources',
        'contact',
        'purpose',
        'role',
        'coordinator',
        'nagoya',
    ],
    'topics' => true,
    'disabled' => false,
    'portfolio' => true,
    'has_subprojects' => true,
    'inherits' => [
        'status',
        'website',
        'grant_sum',
        'funder',
        'grant_sum_proposed',
        'purpose',
        'role',
        'coordinator',
    ]
];


?>


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

<form action="<?= ROOTPATH ?>/crud/admin/project/update/<?= $project['_id'] ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/project">
    <?php if (isset($type) && $type != 'new') { ?>
        <input type="hidden" name="original_id" value="<?= $type ?>">
    <?php } ?>

    <div class="box">
        <div class="content">
            <h2>
                <?= lang('General', 'Allgemein') ?>
            </h2>

            <div class="row row-eq-spacing">
                <div class="col-sm">
                    <label for="id" class="required">ID</label>
                    <input type="text" class="form-control" name="values[id]" required value="<?= $type == 'new' ? '' : $type ?>" oninput="sanitizeID(this)">
                    <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('Must be unqiue', 'Muss einzigartig sein') ?></a></small>
                </div>
                <div class="col-sm">
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
                <div class="col-sm">
                    <label for="color" class="required "><?= lang('Color', 'Farbe') ?></label>
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

            <div class="custom-checkbox mb-10 danger">
                <input type="checkbox" id="disable" value="true" name="values[disabled]" <?= ($info['disabled'] ?? false) ? 'checked' : '' ?>>
                <label for="disable"><?= lang('Deactivate', 'Deaktivieren') ?></label>
            </div>
            <span class="text-muted">
                <?= lang('Deactivated projects are retained for past activities, but no new ones can be added.', 'Deaktivierte Projektkategorien bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
            </span>
        </div>

    </div>

    <h2>
        Phases
    </h2>

    <?php foreach ($project['phases'] ?? [] as $key => $info) { ?>

        <div class="box">
            <div class="content">

                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="name" class="required ">Name (en)</label>
                        <input type="text" class="form-control" name="values[name]" required value="<?= $project['name'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class="">Name (de)</label>
                        <input type="text" class="form-control" name="values[name_de]" value="<?= $project['name_de'] ?? '' ?>">
                    </div> <div class="col-sm">
                    <label for="color" class="required" ><?= lang('Color', 'Farbe') ?></label>
                   <div class="input-group">
                   <select name="values[color]" class="form-control color-select">
                        <option value="muted">muted</option>
                        <option value="primary">primary</option>
                        <option value="secondary">secondary</option>
                        <option value="success">success</option>
                        <option value="danger">danger</option>
                    </select>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="ph ph-fill ph-circle text-<?= $phase['color'] ?? 'muted' ?>" id="test-icon"></i>
                        </span>
                    </div>
                   </div>
                </div>
            </div>
                </div>


                <?php if ($Settings->featureEnabled('portal')) { ?>
                    <div class="my-20">
                        <input type="hidden" name="values[portfolio]" value="">
                        <div class="custom-checkbox">
                            <input type="checkbox" id="portfolio-question" value="1" name="values[portfolio]" <?= ($info['portfolio'] ?? false) ? 'checked' : '' ?>>
                            <label for="portfolio-question">
                                <?= lang('This type of project should be visible in OSIRIS Portfolio.', 'Diese Art von Projekt sollte in OSIRIS Portfolio sichtbar sein.') ?>
                            </label>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <hr>

            <div class="content">
                <label for="module" class="font-weight-bold"><?= lang('Data fields', 'Datenfelder') ?>:</label>

                <?php
                foreach (Project::FIELDS as $m) {
                ?>
                    <div class="custom-checkbox">
                        <input type="checkbox" id="module-<?= $m ?>" value="1" name="values[stages][<?= $key ?>][modules][<?= $m ?>]" <?= ($info['modules'][$m] ?? false) ? 'checked' : '' ?>>
                        <label for="module-<?= $m ?>">
                            <?= lang($m, $m) ?>
                        </label>
                    </div>
                <?php } ?>

            </div>
            <p>
                <i class="ph ph-info text-signal"></i>
                <?= lang('Please note that name, title, status, and time are always a required part of the form.', 'Zur Information: Name, Titel, Status und Zeitrahmen des Projektes sind immer Teil des Formulars sowie Pflichfelder.') ?>
            </p>
        </div>

        </div>

    <?php } ?>


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

<script>
    $('.color-select').change(function() {
        $(this).next().find('i').removeClass().addClass('ph ph-fill ph-circle text-' + $(this).val());
    });
</script>

<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<script src="<?= ROOTPATH ?>/js/admin-categories.js"></script>