<style>
    table td {
        width: 40%;
    }
</style>

<form action="<?= ROOTPATH ?>/respolve-doublets" method="post">
    <input type="hidden" name="id1" value="<?= $id1 ?>">
    <input type="hidden" name="id2" value="<?= $id2 ?>">
    <table class="table">
        <tbody>
            <?php foreach ($html as $module => $vals) {
                $same = $vals[0] === $vals[1];
                if ($same) { ?>
                    <tr class="text-muted">
                        <th class="key"><?= $Modules->get_name($module) ?>:</th>
                        <td colspan="2">
                            <?= $vals[0] ?>
                            <input type="hidden" value="1" name="<?= $module ?>">
                        </td>
                    </tr>
                <?php
                } else {
                ?>
                    <tr>
                        <th class="key"><?= $Modules->get_name($module) ?>:</th>
                        <td>
                            <div class="custom-radio">
                                <input type="radio" id="cb-<?= $module ?>-1" value="1" name="<?= $module ?>" checked>
                                <label for="cb-<?= $module ?>-1">
                                    <?= $vals[0] ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="custom-radio">
                                <input type="radio" id="cb-<?= $module ?>-2" value="2" name="<?= $module ?>">
                                <label for="cb-<?= $module ?>-2">
                                    <?= $vals[1] ?>
                                </label>
                            </div>
                        </td>
                    </tr>
            <?php
                }
            } ?>

        </tbody>
    </table>

    <p>
        <button class="btn secondary">
            <i class="ph ph-arrows-merge"></i>
            <?= lang('import.merge') ?>
        </button>
    </p>

</form>