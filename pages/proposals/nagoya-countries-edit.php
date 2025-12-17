<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = DB::doc2Arr($project['nagoya'] ?? []);
$countries = DB::doc2Arr($nagoya['countries'] ?? []);

$nagoya_perm = $Settings->hasPermission('nagoya.view');
?>


<h1>
    <i class="ph-duotone ph-globe"></i>
    <?= lang('Edit Nagoya Countries', 'Nagoya-Länder bearbeiten') ?>
</h1>

<table class="table">
    <thead>
        <tr>
            <th><?= lang('Country', 'Land') ?></th>
            <th><?= lang('Nagoya Evaluation', 'Nagoya-Bewertung') ?></th>
            <th><?= lang('Scope Overview', 'Übersicht Umfang') ?></th>
            <th><?= lang('ABS Classification', 'ABS-Klassifikation') ?></th>
            <th><?= lang('Actions', 'Aktionen') ?></th>
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
            <th><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></th>
            <td>
                <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
            </td>
            <td class="small text-muted">
                <?= $numGroups ?>
                <?= lang('Sample collection(s)', 'Probensammlung(en)') ?>
                <?php if ($nagoya_perm && !empty($review['comment'])): ?>
                    · <?= htmlspecialchars($review['comment']) ?>
                <?php endif; ?>
                <?php if ($permTotal > 0): ?>
                    <?= $permTotal ?> <?= lang('Permit(s)', 'Genehmigung(en)') ?>
                    <?php if ($permOpen > 0): ?>
                        (<?= $permOpen ?> <?= lang('open', 'offen') ?>)
                    <?php endif; ?>
                    <?php if ($permDocs > 0): ?>
                        · <?= $permDocs ?> <?= lang('document(s)', 'Dokument(e)') ?>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($labelABC): ?>
                    <span class="text-muted ml-5">
                        <?= lang('ABS classification for this country', 'ABS-Klassifikation für dieses Land') ?>: <?= Nagoya::ABCbadge($labelABC) ?>
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
                            <form action="<?= ROOTPATH ?>/crud/nagoya/remove-country/<?= htmlspecialchars($project['_id']) ?>" method="post" class="p-5">
                                <input type="hidden" name="country_id" value="<?= htmlspecialchars($countryId) ?>">
                                <small class="text-muted">
                                    <?= lang('Removing this country will also delete all associated scope groups and permits.', 'Das Entfernen dieses Landes löscht auch alle zugehörigen Umfangsgruppen und Genehmigungen.') ?>
                                </small>
                                <button type="submit" class="btn danger small" onclick="return confirm('<?= lang('Are you sure you want to remove this country from the Nagoya review?', 'Möchten Sie dieses Land wirklich aus der Nagoya-Bewertung entfernen?') ?>');">
                                    <i class="ph ph-trash"></i>
                                    <?= lang('Remove country', 'Land entfernen') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php } else { ?>
                    <span class="text-muted">
                        <?= lang('No actions available', 'Keine Aktionen verfügbar') ?>
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
                        <option value="" disabled selected><?= lang('Please select a country', 'Bitte wähle ein Land aus') ?></option>
                        <?php foreach ($DB->getCountries(lang('name', 'name_de')) as $iso => $name) { ?>
                            <option value="<?= $iso ?>"><?= $name ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="btn success">
                        <i class="ph ph-plus"></i>
                        <?= lang('Add country to Nagoya review', 'Land zur Nagoya-Bewertung hinzufügen') ?>
                    </button>
                </form>
            </td>
        </tr>
    </tfoot>
</table>


<?php if (!$nagoya_perm) { ?>
    <p class="text-muted">
        <?= lang('You can only remove countries from this list if they have not yet been evaluated. Please contact an administrator or compliance officer for further changes.', 'Sie können Länder nur dann aus dieser Liste entfernen, wenn sie noch nicht bewertet wurden. Bitte wenden Sie sich für weitere Änderungen an eine:n Administrator:in oder Compliance-Beauftragte.') ?>
    </p>
<?php } ?>