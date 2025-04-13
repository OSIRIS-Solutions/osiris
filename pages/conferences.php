<?php
$user = $_SESSION['username'];
?>


<h1><?= lang('Events') ?></h1>


<!-- modal for adding conference -->
<?php if ($Settings->hasPermission('conferences.edit')) { ?>
    <div class="modal" id="add-conference" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h4 class="title mt-0">
                    <?= lang('Add event', 'Event hinzufügen') ?>
                </h4>

                <form action="<?= ROOTPATH ?>/crud/conferences/add" method="post" id="conference-form">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                    <div class="form-group mb-10">
                        <label for="title" class="required"><?= lang('(Short) Title', 'Kurztitel') ?></label>
                        <input type="text" name="title" required class="form-control">
                    </div>
                    <div class="form-group mb-10">
                        <label for="title"><?= lang('Full Title', 'Kompletter Titel') ?></label>
                        <input type="text" name="title_full" class="form-control">
                    </div>

                    <div class="form-row row-eq-spacing mb-10">
                        <div class="col">
                            <label for="start" class="required"><?= lang('Start date', 'Anfangsdatum') ?></label>
                            <input type="date" name="start" required class="form-control" onchange="$('#conference-end-date').val(this.value)">
                        </div>
                        <div class="col">
                            <label for="end" class="required"><?= lang('End date', 'Enddatum') ?></label>
                            <input type="date" name="end" class="form-control" id="conference-end-date">
                        </div>
                    </div>

                    <div class="form-group mb-10">
                        <label for="location" class="required"><?= lang('Location', 'Ort') ?></label>
                        <input type="text" name="location" required class="form-control">
                    </div>

                    <div class="form-group mb-10">
                        <label for="url"><?= lang('URL', 'URL') ?></label>
                        <input type="url" name="url" class="form-control">
                    </div>

                    <button class="btn mb-10" type="submit"><?= lang('Add event', 'Event hinzufügen') ?></button>
                </form>
            </div>
        </div>
    </div>
    <a href="#add-conference" class="float-md-right btn primary">
        <i class="ph ph-plus"></i>
        <?= lang('Add event', 'Event hinzufügen') ?>
    </a>
<?php } ?>



<p class="text-muted">
    <small> <?= lang('Events were added by users of the OSIRIS system.', 'Events wurden von Nutzenden des OSIRIS-Systems angelegt.') ?></small>
</p>

<?php
// conferences max past 3 month
$conferences = $osiris->conferences->find(
    [],
    // ['start' => ['$gte' => date('Y-m-d', strtotime('-3 month'))]],
    ['sort' => ['start' => -1]]
)->toArray();
?>
<table class="table" id="result-table">
    <thead>
        <tr>
            <th><?= lang('Title', 'Titel') ?></th>
            <th><?= lang('Location', 'Ort') ?></th>
            <th><?= lang('Start', 'Anfang') ?></th>
            <th><?= lang('End', 'Ende') ?></th>
            <th><?= lang('Type', 'Typ') ?></th>
            <th><?= lang('Activities', 'Aktivitäten') ?></th>
            <th><?= lang('URL', 'URL') ?></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>



<script>
    const CARET_DOWN = ' <i class="ph ph-caret-down"></i>';
    var dataTable;
    var rootpath = '<?= ROOTPATH ?>'
    $(document).ready(function() {
        dataTable = $('#result-table').DataTable({
            "ajax": {
                "url": rootpath + '/api/conferences',
                dataSrc: 'data'
            },
            deferRender: true,
            columnDefs: [
                {
                    targets: 0,
                    data: 'title',
                    searchable: true,
                    render: function(data, type, row) {
                        return `<a href="${rootpath}/conferences/${row.id}" class="font-weight-bold">${row.title}</a>
                        <br>
                        ${row.title_full ?? ''}
                        `;
                    }
                },
                {
                    targets: 1,
                    data: 'location',
                    searchable: true,
                },
                {
                    targets: 2,
                    data: 'start',
                    searchable: true,
                    render: function(data, type, row) {
                        // formatted date
                        var date = new Date(data);

                        return `
                        <span class="d-none">${date.getTime()}</span>
                        ${date.toLocaleDateString('de-DE')}
                        `;
                    }
                },
                {
                    targets: 3,
                    data: 'end',
                    searchable: true,
                    render: function(data, type, row) {
                        // formatted date
                        var date = new Date(data);
                        return `
                        <span class="d-none">${date.getTime()}</span>
                        ${date.toLocaleDateString('de-DE')}
                        `;
                    }
                },
                {
                    targets: 4,
                    data: 'type',
                    searchable: true,
                    defaultContent: '',
                },
                {
                    targets: 5,
                    data: 'activities',
                },
                {
                    targets: 6,
                    data: 'url',
                    searchable: true,
                    render: function(data, type, row) {
                        if (!data) {
                            return '';
                        }
                        return `<a href="${data}" target="_blank"><i class="ph ph-link"></i></a>`;
                    }
                },
            ],
            "order": [
                [2, 'desc']
            ],
        });

    });
</script>