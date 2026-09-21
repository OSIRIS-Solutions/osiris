<?php

$pagetitle = lang('common.guests');
$filter = [];
if (!$Settings->hasPermission('guests.view')) {
    $filter = [
        'supervisor.user' => $_SESSION['username']
    ];
    $pagetitle = lang('guests.my_guests');
}

?>



<h1>
    <i class="ph-duotone ph-user-switch"></i>
    <?= $pagetitle ?>
</h1>

<?php if ($Settings->hasPermission('guests.add')) { ?>
    <a href="<?= ROOTPATH ?>/guests/new" class="btn link px-0 mb-10">
        <i class="ph ph-plus"></i>
        <?= lang('guests.add_new_guest') ?>
    </a>
<?php } ?>

<?php
$guest_forms = $Settings->featureEnabled('guest-forms');
?>

<br>


<table class="table mt-10" id="guest-table">
    <thead>
        <tr>
            <th>ID</th>
            <th><?= lang('guests.name_of_guest') ?></th>
            <th><?= lang('common.affiliation') ?></th>
            <th>Status</th>
            <th><?= lang('guests.time_of_stay') ?></th>
            <th><?= lang('guests.supervisor') ?></th>
            <?php if ($guest_forms) { ?>
                <th><?= lang('guests.complete') ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 0;
        foreach ($osiris->guests->find($filter) as $entry) {
            $i++;
        ?>
            <tr>
                <td>
                    <a href="<?= ROOTPATH ?>/guests/view/<?= $entry['id'] ?>">#<?= $entry['id'] ?></a>
                </td>
                <td>
                    <?= $entry['guest']['academic_title'] ?? '' ?>
                    <?= $entry['guest']['first'] ?? '' ?>
                    <?= $entry['guest']['last'] ?? '' ?>
                </td>
                <td>
                    <?= $entry['affiliation']['name'] ?? '' ?>
                </td>
                <td>
                    <?php
                    $now = new DateTime();
                    $status = '';
                    if ($entry['cancelled'] ?? false) $status = 'cancelled';
                    else if (getDateTime($entry['start']) <= $now && getDateTime($entry['end']) >= $now) $status = 'current';
                    else if (getDateTime($entry['start']) > $now) $status = 'future';
                    else if (getDateTime($entry['end']) < $now) $status = 'past';
                    echo $status;
                    ?>
                </td>
                <td>
                    <span class="hidden">
                        <?= format_date($entry['start'], "Y-m-d") ?>
                        <?= format_date($entry['end'] ?? $entry['start'], "Y-m-d") ?>
                    </span>

                    <!-- <?php if ($status == 'cancelled') { ?>
                        <span class="badge danger"><?= lang('guests.cancelled') ?></span>
                    <?php } else {
                                // check if date is current
                                if ($status == 'current') {
                                    echo "<i class='ph ph-check text-success'></i> ";
                                }
                    ?> -->
                    <?= fromToDate($entry['start'], $entry['end'] ?? null) ?>
                <?php } ?>
                </td>
                <td>
                    <?= $entry['supervisor']['name'] ?>
                </td>
                <?php if ($guest_forms) { ?>
                    <td>
                        <?php
                        $finished = ($entry['legal']['general'] ?? false);
                        echo bool_icon($finished);
                        ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        <?php if ($i == 0) { ?>
            <tr>
                <td colspan="3"><?= lang('guests.no_data_found') ?></td>
            </tr>
        <?php } ?>


    </tbody>
</table>


<script>
    $.extend($.fn.dataTableExt.oSort, {
        "status-pre": function(a) {
            let c = $.inArray(a, [
                'current',
                'future',
                'past',
                'cancelled',
            ]);
            console.log(a);
            return c;
        }
    });
    dataTable = $('#guest-table').DataTable({
        columnDefs: [{
                targets: 3,
                // data: 'icon'
                // type: 'status',
                "render": function(data, type, full, meta) {
                    switch (data) {
                        case 'current':
                            return "<span class='badge success'>" + <?= json_encode(lang('guests.current'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + "</span>";
                        case 'future':
                            return "<span class='badge signal'>" + <?= json_encode(lang('projects.future'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + "</span>";
                        case 'past':
                            return "<span class='badge muted'>" + <?= json_encode(lang('guests.past'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + "</span>";
                        case 'cancelled':
                            return "<span class='badge danger'>" + <?= json_encode(lang('guests.cancelled_list'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + "</span>";
                        default:
                            return '';
                    }
                    // return `<a href="${ROOTPATH}/activities/view/${data}"><i class="ph ph-arrow-fat-line-right"></a>`;
                },
            },
            // {
            //     targets: 1,
            //     // data: 'activity'
            // },

        ],
        "order": [
            [3, 'asc'],
            // [4, 'desc'],
        ]
    });
</script>