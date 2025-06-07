<?php
$user = $_SESSION['username'];
?>


<h1><?= lang('Events') ?></h1>


<!-- modal for adding conference -->
<?php if ($Settings->hasPermission('conferences.edit')) { ?>
   <div class="btn-toolbar">
   <a href="<?=ROOTPATH?>/conferences/new" class="">
        <i class="ph ph-plus"></i>
        <?= lang('Add event', 'Event hinzufügen') ?>
    </a>
   </div>
<?php } ?>


<!-- 
<p class="text-muted mt-0">
    <small> <?= lang('Events were added by users of the OSIRIS system.', 'Events wurden von Nutzenden des OSIRIS-Systems angelegt.') ?></small>
</p> -->

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
                        return `<a href="${rootpath}/conferences/view/${row.id}" class="font-weight-bold">${row.title}</a>
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