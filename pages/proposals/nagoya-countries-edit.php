<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = DB::doc2Arr($project['nagoya'] ?? []);
$countries = DB::doc2Arr($nagoya['countries'] ?? []);

$nagoya_perm = $Settings->hasPermission('nagoya.view');
?>


<h1>
    <i class="ph-duotone ph-globe"></i>
    <?= lang('projects.edit_nagoya_countries') ?>
</h1>

<table class="table">
    <thead>
        <tr>
            <th><?= lang('common.country') ?></th>
            <th><?= lang('common.nagoya_evaluation') ?></th>
            <th><?= lang('projects.scope_overview_nagoya_countries_edit') ?></th>
            <th><?= lang('projects.abs_classification') ?></th>
            <th><?= lang('common.actions') ?></th>
        </tr>
    </thead>

    <?php foreach ($countries as $c):
        $review     = $c['review'] ?? [];
        $abs        = $c['abs'] ?? null;
        $scope      = DB::doc2Arr($c['scope']['groups'] ?? []);
        $numGroups  = count($scope);
        $eval       = DB::doc2Arr($c['evaluation'] ?? []);
        $permits    = DB::doc2Arr($eval['permits'] ?? []);
        $permTotal  = count($permits);
        $permOpen   = 0;
        $permDocs   = 0;
        foreach ($permits as $perm) {
            if (in_array($perm['status'] ?? '', ['needed', 'requested'])) {
                $permOpen++;
            }
            $permDocs += count(DB::doc2Arr($perm['docs'] ?? []));
        }
        $labelABC = $eval['label'] ?? null;
        $countryId = $c['id'] ?? null;
    ?>
        <tr>
            <th><?= $DB->getCountry($c['code'], lang('common.field_name_language')) ?></th>
            <td>
                <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
            </td>
            <td class="small text-muted">
                <?= $numGroups ?>
                <?= lang('common.sample_collection_s') ?>
                <?php if ($nagoya_perm && !empty($review['comment'])): ?>
                    · <?= e($review['comment']) ?>
                <?php endif; ?>
                <?php if ($permTotal > 0): ?>
                    <?= $permTotal ?> <?= lang('common.permit_s') ?>
                    <?php if ($permOpen > 0): ?>
                        (<?= $permOpen ?> <?= lang('common.open') ?>)
                    <?php endif; ?>
                    <?php if ($permDocs > 0): ?>
                        · <?= $permDocs ?> <?= lang('common.document_s') ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($labelABC): ?>
                    <span class="text-muted ml-5">
                        <?= lang('common.abs_classification_for_this_country') ?>: <?= Nagoya::ABCbadge($labelABC) ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($nagoya_perm || $abs === null) { ?>
                    <div class="dropdown">
                        <button class="btn link" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-dots-three-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-1">
                            <form action="<?= ROOTPATH ?>/crud/nagoya/remove-country/<?= e($project['_id']) ?>" method="post" class="p-5">
                                <input type="hidden" name="country_id" value="<?= e($countryId) ?>">
                                <small class="text-muted">
                                    <?= lang('projects.removing_this_country_will_also_delete_all_associated_scope_groups_and_perm') ?>
                                </small>
                                <button type="submit" class="btn danger small" onclick="return confirm('<?= lang('projects.are_you_sure_you_want_to_remove_this_country_from_the_nagoya_review') ?>');">
                                    <i class="ph ph-trash"></i>
                                    <?= lang('projects.remove_country') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php } else { ?>
                    <span class="text-muted">
                        <?= lang('projects.no_actions_available') ?>
                    </span>
                <?php } ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <tfoot>
        <tr>
            <td colspan="5">
                <form action="<?= ROOTPATH ?>/crud/nagoya/add-country/<?= $project['_id'] ?>" method="post">
                    <select id="add-nagoya-country" name="countryCode" class="form-control d-inline-block w-auto mr-10">
                        <option value="" disabled selected><?= lang('common.please_select_a_country') ?></option>
                        <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                            <option value="<?= $iso ?>"><?= $name ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="btn success">
                        <i class="ph ph-plus"></i>
                        <?= lang('projects.add_country_to_nagoya_review') ?>
                    </button>
                </form>
            </td>
        </tr>
    </tfoot>
</table>


<?php if (!$nagoya_perm) { ?>
    <p class="text-muted">
        <?= lang('projects.you_can_only_remove_countries_from_this_list_if_they_have_not_yet_been_eval') ?>
    </p>
<?php } ?>