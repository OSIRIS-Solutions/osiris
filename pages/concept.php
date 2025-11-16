<?php

/**
 * Page to see all concepts
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /concepts
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<h1>
    <i class="ph-duotone ph-lightbulb" aria-hidden="true"></i>
    <?= lang('Concept', 'Konzept') ?>
    <span class="text-secondary">
        <?= $name ?>
    </span>
</h1>

<?php
// $active_users = $osiris->persons->distinct('username', ['is_active' => ['$ne'=>false]]);
// $concepts = $osiris->activities->aggregate(
//     [
//         ['$match' => ['concepts.display_name' => $name]],
//         ['$project' => ['authors' => 1, 'concepts' => 1]],
//         ['$unwind' => '$concepts'],
//         ['$match' => ['concepts.display_name' => $name]],
//         ['$unwind' => '$authors'],
//         ['$match' => ['authors.user' => ['$in' => $active_users]]],
//         [
//             '$group' => [
//                 '_id' => '$authors.user',
//                 'total' => ['$sum' => 1],
//                 'totalScore' => ['$sum' => '$concepts.score'],
//                 'author' => ['$first' => '$authors']
//             ]
//         ],
//         ['$project' => ['score' => ['$divide' => ['$totalScore', '$total']], 'author' => 1, 'total' => 1]],
//         ['$match' => ['score' => ['$gte' => .1]]],
//         ['$sort' => ['author.last' => 1]],
//     ]
// )->toArray();

// dump($concepts, true);
// $concepts = $osiris->activities->aggregate(
//     [
//         ['$match' => ['concepts.display_name' => $name]],
//         ['$project' => ['authors' => 1, 'concepts' => 1]],
//         ['$unwind' => '$concepts'],
//         ['$match' => ['concepts.display_name' => $name]],
//         ['$unwind' => '$authors'],
//         ['$match' => ['authors.user' => ['$ne' => null]]],
//         [
//             '$group' => [
//                 '_id' => '$authors.user',
//                 'total' => ['$sum' => 1],
//                 'totalScore' => ['$sum' => '$concepts.score'],
//                 'author' => ['$first' => '$authors']
//             ]
//         ],
//         ['$match' => ['totalScore' => ['$gte' => 1]]],
//         ['$sort' => ['totalScore' => -1]],
//     ]
// )->toArray();
?>



<div class="d-flex align-items-end">
    <h2 class="m-0">
        <?= lang('Experts for this concept', 'Expert:innen für dieses Konzept') ?>
    </h2>
    <div class="ml-md-auto">
        <label for="add"><?= lang('Add another concept to this graph', 'Füge ein Konzept zum Vergleich hinzu') ?></label>
        <input type="text" class="form-control" list="concept-list" onchange="update(this.value); this.value=''">
    </div>
</div>

<div class="box" id="chart-box">
    <div id="chart"></div>
</div>

<datalist id="concept-list">
    <?php
    $concepts = $osiris->activities->aggregate([
        ['$match' => ['concepts' => ['$exists' => true]]],
        ['$project' => ['concepts' => 1]],
        ['$unwind' => '$concepts'],
        [
            '$group' => [
                '_id' => '$concepts.display_name',
                'level' => ['$first' => '$concepts.level'],
                'wikidata' => ['$first' => '$concepts.wikidata'],
                'score' => ['$sum' => '$concepts.score']
            ]
        ],
        ['$match' => ['score' => ['$gte' => 1]]],
        ['$sort' => ['score' => -1]],
    ]);
    foreach ($concepts as $c) { ?>
        <option><?= $c['_id'] ?></option>
    <?php } ?>
</datalist>

<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" charset="utf-8"></script>

<script>
    const TOPIC = '<?= $name ?>'
    const items = 1

    var data = []
    var authors = new Set();
    var layout = {
        title: false,
        showlegend: false,
        height: 400,
        // width: 200 + (100 * items),
        width: $('#chart-box').width()-20,
        margin: {
            l: 150,
            t: 20,
            r: 50,
            b: 160
        }
    };

    const plot = Plotly.newPlot('chart', data, layout);
    update(TOPIC);

    function update(topic) {
        if (topic.length == 0) return
        $.ajax({
            type: "GET",
            url: ROOTPATH + "/api/dashboard/concept-search",
            data: {
                concept: topic
            },
            dataType: "json",
            success: function(response) {
                console.log(response);
                if (response.count == 0) {
                    toastError(
                        'Concept not found'
                    )
                    return;
                }
                data.push(response.data)
                layout.height = 250 + (20 * data.length)

                // response.data.y.forEach(a => {
                //     authors.add(a)
                // });

                // layout.width = 120 + (20 * authors.size)
                Plotly.update('chart', data, layout);

            },
            error: function(response) {
                console.log(response);
            }
        });
    }
</script>


<div class="mt-20">
    <h2>
        <?= lang('Activities with this concept', 'Aktivitäten mit diesem Konzept') ?>
    </h2>

    <table class="table dataTable responsive" id="result-table">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Activity', 'Aktivität') ?></th>
                <th><?= lang('Score') ?></th>
            </tr>
        </thead>
        <tbody>
        </tbody>

    </table>
</div>


<script>
    var dataTable;
    $(document).ready(function() {
        dataTable = $('#result-table').DataTable({
            "ajax": {
                "url": ROOTPATH + '/api/concept-activities',
                "data": {
                    'concept': TOPIC
                },
                dataSrc: 'data'
            },
            deferRender: true,
            columnDefs: [{
                    targets: 0,
                    data: 'icon'
                },
                {
                    targets: 1,
                    data: 'activity'
                },
                {
                    targets: 2,
                    data: 'score',
                    render: function(data, type, row) {
                        return (100 * data).toFixed(2) + "%";
                    }
                }
            ],
            "order": [
                [2, 'desc'],
                // [0, 'asc']
            ]
        });

    });
</script>