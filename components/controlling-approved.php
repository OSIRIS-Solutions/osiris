<div class="content">

    <h3 class="title">
        <?= lang('activities.scientist_overview_selected_quarter') ?>
    </h3>

</div>
<table class="table simple small">
    <tbody>
        <?php
        if (isset($_GET['q']) && isset($_GET['y'])) {
            $Y = $_GET['y'];
            $Q =  $_GET['q'];
        } else {
            $Y = CURRENTYEAR;
            $Q = CURRENTQUARTER;
        }
        $yq = $Y . "Q" . $Q;
        $cursor = $osiris->persons->find(
            ['roles' => 'scientist', 'is_active' => ['$ne'=>false]],
            ['sort' => ["approved" => -1, "last" => 1]]
        );
        if (empty($cursor)) {
            echo "<div class='content'>" . lang('activities.no_scientists_found') . "</div>";
        } else foreach ($cursor as $s) {
            $approved = isset($s['approved']) && in_array($yq, DB::doc2Arr($s['approved']));
        ?>
            <tr class="row-<?= $approved ? 'success' : '' ?>">
                <td>
                    <a href="<?= ROOTPATH ?>/my-year/<?= $s['username'] ?>?year=<?= $Y ?>&quarter=<?= $Q ?>">
                        <?= $DB->getNameFromId($s['username']) ?>
                    </a>
                </td>
                <td>
                    <?= bool_icon($approved) ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>