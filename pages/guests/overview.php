

<script src="<?=ROOTPATH?>/js/frappe-gantt.es.js"></script>
<link rel="stylesheet" href="<?=ROOTPATH?>/css/frappe-gantt.css">

<h1>
    <i class="ph-duotone ph-users-rectangle"></i>
    <?=lang('Guest Overview', 'Übersicht über anstehende Gäste')?>
</h1>

<svg id="gantt"></svg>

<?php
    $guests = $osiris->guests->find();
    $tasks = [];
    foreach ($guests as $guest) {
        // dump($guest, true);
        $details = $guest['guest'];
        $name = $details['last'] . ', ' . $details['first'];
        $start = DB::getDate($guest['start']);
        $end = DB::getDate($guest['end']);
        $tasks[] = [
            'id' => $guest['id'],
            'name' => $name,
            'start' => $start,
            'end' => $end,
            // 'progress' => 20,
            'custom_class' => 'guest'
        ];
    }

?>


<script>
    var tasks = <?= json_encode($tasks) ?>;
var gantt = new Gantt("#gantt", tasks, {
    header_height: 50,
    column_width: 30,
    step: 24,
    view_modes: [ 'Week', 'Month'],
    bar_height: 20,
    bar_corner_radius: 3,
    arrow_curve: 5,
    padding: 18,
    view_mode: 'Day',
    date_format: 'YYYY-MM-DD',
    language: lang('en', 'de'), // or 'es', 'it', 'ru', 'ptBr', 'fr', 'tr', 'zh', 'de', 'hu'
    popup: null,
});
gantt.change_view_mode('Week')
</script>