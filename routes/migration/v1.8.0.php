
<h3>
    <?=lang('Migrating to Version 1.8.0', 'Migriere zur Version 1.8.0')?>
</h3>

<p>
    In dieser Version nehmen wir einige Änderungen an der Datenbankstruktur vor, um neue Funktionen zu unterstützen und die Datenkonsistenz zu verbessern. Lass uns zuerst die Finanzdaten bei Projektanträgen umwandeln, damit sie geplante und ausgegebene Beträge getrennt erfassen können.
</p>
<?php
$projects = $osiris->proposals->find(['grant_years' => ['$exists' => true]])->toArray();
$N_ = count($projects);
$updated = 0;
foreach ($projects as $project) {
    $grant_years = DB::doc2Arr($project['grant_years'] ?? array());
    if (is_array($grant_years) && isset($grant_years[0]['year'])) {
        // already migrated
        continue;
    }
    $new_grant_years = array();
    foreach ($grant_years as $year => $amount) {
        $new_grant_years[] = [
            'year' => $year,
            'planned' => $amount,
            'spent' => $amount
        ];
    }
    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => [
            'grant_years' => $new_grant_years,
        ]]
    );
    $updated++;
}
if ($N_ == 0) {
    echo "<p>" . lang(
        "No proposals found with grant_years field. No changes made.",
        "Keine Anträge mit Drittmittel-Einnahmen gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>" . lang(
        "Transformed grant_years field for " . $updated . " out of " . $N_ . " proposals.",
        "Drittmittel-Einnahmen für " . $updated . " von " . $N_ . " Anträgen umgewandelt."
    ) . "</p>";
}

?>
<p>
    Als nächstes vergeben wir das neue Recht, Finanzstatistiken für Anträge einzusehen, an die Administratorrolle.
</p>
<?php
// give admin rights to see financial statistics to role 'admin'
$osiris->adminRights->insertOne([
    "role" => "admin",
    "right" => "proposals.finance",
    "value" => true
]);
?>

<p>
    Eine weitere Änderung betrifft die Dokumente, die zu Aktivitäten hochgeladen wurden. Wir haben die Art und Weise verbessert, wie diese Dateien gespeichert und verwaltet werden und haben dadurch neue Funktionen ermöglicht. Da diese Migration eine größere Änderung ist und einige Einstellungen benötigt, haben wir einen separaten Migrationsprozess dafür erstellt. Bitte klicke auf den untenstehenden Link, um die Dateimigration durchzuführen. 
    <br>
    Keine Sorge, wir starten erst mal mit einem Trockenlauf, damit du sehen kannst, welche Änderungen vorgenommen werden, ohne dass tatsächlich etwas verändert wird. 
</p>

<p>
    <a href="<?=ROOTPATH?>/migrate/files" class="btn primary">
        <?=lang('Start File Migration', 'Dateimigration starten')?>
    </a>
</p>

<?php
// give admin rights to see financial statistics to role 'admin'
$osiris->adminRights->insertOne([
    "role" => "user",
    "right" => "teaching.edit",
    "value" => true
]);
?>
