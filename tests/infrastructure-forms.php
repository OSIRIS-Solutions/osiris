<?php
// Configuration, localization and compatible infrastructure storage.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
require_once BASEPATH . '/php/LocalizedFields.php';
loadIsolated('php/InfrastructureForms.php');
class Html { public function __construct(public readonly string $value) {} }
$osiris->infrastructures = new MemoryCollection();
$config = InfrastructureForms::builder();
$fields = InfrastructureForms::fields($osiris);
check(isset($config->modules['name-loc'], $config->modules['subtitle-loc'], $config->modules['description-loc'], $config->modules['infrastructure-collaborative'], $config->modules['initiatives']), 'Infrastructure fields include localization and the established collaboration widget.');
FormBuilder::validateFields($fields, InfrastructureForms::SYSTEM_FIELDS);
rejects(fn() => FormBuilder::validateFields([], InfrastructureForms::SYSTEM_FIELDS), 'Name, ID and dates remain system fields.');
$input = ['id' => 'ANKH-LAB', 'name' => ['en' => 'Climate lab', 'de' => 'Klimalabor'], 'subtitle' => ['en' => 'Shared facility', 'de' => 'Gemeinsame Einrichtung'], 'description' => ['en' => '<p>Research</p>'], 'start_date' => '2020-01-01', 'end_date' => '', 'statistic_frequency' => 'annual', 'statistic_fields' => ['hours'], 'persons' => [['user' => 'forged']], 'created_by' => 'forged', 'collaborative' => 'no'];
foreach (['type' => 'infrastructure-category', 'infrastructure_type' => 'infrastructure-type', 'access' => 'infrastructure-access'] as $key => $vocab) $input[$key] = (new Vocabulary())->getValues($vocab)[0]['id'];
$values = InfrastructureForms::values($input, $osiris, $Settings);
check($values['name']['de'] === 'Klimalabor' && $values['subtitle']['en'] === 'Shared facility' && $values['description']['en'] === '<p>Research</p>', 'All editable built-in texts retain their language maps.');
check($values['start_date'] === '2020-01-01' && $values['end_date'] === null && $values['statistic_fields'] === ['hours'], 'Dates and statistic keys retain their previous storage formats.');
check(!isset($values['persons'], $values['created_by']), 'Metadata editing cannot overwrite persons or authorship.');
rejects(fn() => InfrastructureForms::values(array_replace($input, ['end_date' => '2019-12-31']), $osiris, $Settings), 'Reversed infrastructure dates are rejected.');
rejects(fn() => InfrastructureForms::values(array_replace($input, ['name' => ['de' => 'Nur Deutsch']]), $osiris, $Settings), 'Required localized names need the base language.');
$edited = InfrastructureForms::values(array_replace($input, ['id' => 'CHANGED']), $osiris, $Settings, $values);
check($edited['id'] === 'ANKH-LAB', 'Existing infrastructure identifiers stay immutable.');
$org = new MongoDB\BSON\ObjectId();
$collaborative = InfrastructureForms::values(array_replace($input, ['collaborative' => 'yes', 'collaborators' => [(string) $org], 'coordinator' => (string) $org]), $osiris, $Settings);
check($collaborative['coordinator_organization'] == $org && !$collaborative['coordinator_institute'] && $collaborative['collaborators'][0] == $org, 'Collaboration partners and the external coordinator retain ObjectID storage.');
ob_start(); InfrastructureForms::render($osiris, $Settings, $values); $formHtml = ob_get_clean();
check(str_contains($formHtml, 'name="values[coordinator]"') && str_contains($formHtml, 'organization-search') && !str_contains($formHtml, 'KDSF-B-'), 'The configured form preserves partner/coordinator selection without KDSF badges.');
$osiris->adminGeneral->insertOne(['key' => 'infrastructure-data', 'value' => ['type', 'notes', 'topics']]);
$legacy = InfrastructureForms::legacyFields($osiris);
check(array_column($legacy, 'id') === ['infrastructure-id', 'infrastructure-date', 'name-loc', 'subtitle-loc', 'infrastructure-category', 'notes', 'topics'], 'Legacy selection and custom fields migrate in the previous form order.');
$format = new Document(false, 'list');
check(str_contains($format->getInfrastructureField($values, 'infrastructure-date'), '2020') && $format->getInfrastructureField($values, 'infrastructure-category') !== '', 'Document renders infrastructure dates and vocabulary labels.');
ob_start(); (new Modules($values))->print_module('subtitle-loc'); $html = ob_get_clean();
check(str_contains($html, 'values[subtitle][en]') && !str_contains($html, '<div class="localized-quill"'), 'Localized subtitles use text inputs rather than rich text.');
check(validateValues(['subtitle' => ['en' => 'Zero emissions', 'de' => 'Null Emissionen']], $DB)['subtitle']['en'] === 'Zero emissions', 'The common normalizer preserves localized subtitles.');
$mapping = json_decode(file_get_contents(BASEPATH . '/data/kdsf-infrastructures.json'), true, 512, JSON_THROW_ON_ERROR);
check($mapping['fields']['name'] === 'KDSF-B-13-2' && $mapping['persons']['fte'] === 'KDSF-B-13-16-D', 'KDSF mappings preserve existing field meanings outside the interface.');
// Execute the migration twice to cover both legacy content and existing translations.
class InfrastructureMigrationCollection extends MemoryCollection
{
    public function updateOne(array $filter, array $update, array $options = [])
    {
        $document = $this->findOne($filter);
        if ($document === null && !empty($options['upsert'])) {
            $this->insertOne(array_replace($filter, $update['$setOnInsert'] ?? [], $update['$set'] ?? []));
            return;
        }
        if ($document === null) return;
        $id = (string) $document['_id'];
        $this->documents[$id] = array_replace($document, $update['$set'] ?? []);
        foreach ($update['$unset'] ?? [] as $key => $unused) unset($this->documents[$id][$key]);
    }
}
$general = new InfrastructureMigrationCollection();
$general->documents = $osiris->adminGeneral->documents;
$osiris->adminGeneral = $general;
$osiris->infrastructures = new InfrastructureMigrationCollection();
$legacyId = new MongoDB\BSON\ObjectId();
$osiris->infrastructures->insertOne(['_id' => $legacyId, 'name' => 'Climate lab', 'name_de' => 'Klimalabor', 'subtitle' => 'English subtitle', 'subtitle_de' => 'Deutscher Untertitel', 'description' => '<p>Original</p>', 'persons' => [['user' => 'reviewer']], 'collaborators' => [$org]]);
$localizedId = new MongoDB\BSON\ObjectId();
$osiris->infrastructures->insertOne(['_id' => $localizedId, 'name' => ['en' => 'Already translated', 'de' => 'Vorhanden', 'fr' => 'Existant'], 'name_de' => 'Old translation']);
ob_start(); loadIsolated('routes/migration/infrastructure-forms.php'); ob_end_clean();
$migrated = $osiris->infrastructures->findOne(['_id' => $legacyId]);
check($migrated['name']['de'] === 'Klimalabor' && $migrated['subtitle']['en'] === 'English subtitle' && $migrated['description']['en'] === '<p>Original</p>' && !isset($migrated['name_de']), 'Migration moves English and German text into language maps and removes companion fields.');
check($migrated['persons'] === [['user' => 'reviewer']] && $migrated['collaborators'] === [$org], 'Migration leaves persons and organization relationships unchanged.');
$translated = $osiris->infrastructures->findOne(['_id' => $localizedId]);
check($translated['name']['de'] === 'Vorhanden' && $translated['name']['fr'] === 'Existant', 'Existing translations take precedence and additional languages survive migration.');
$before = serialize([$osiris->adminGeneral->documents, $osiris->infrastructures->documents]);
ob_start(); loadIsolated('routes/migration/infrastructure-forms.php'); ob_end_clean();
check(serialize([$osiris->adminGeneral->documents, $osiris->infrastructures->documents]) === $before, 'Repeated migration leaves configured fields and localized content unchanged.');
ob_end_clean(); echo $baselineOutput; echo 'Infrastructure form checks passed (' . ($checks - $baselineChecks) . " checks).\n";
