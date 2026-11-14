<?php

/** Shipped defaults shared by installation, migration and form fallbacks. */
function setupDefinition(string $name): array
{
    return json_decode(file_get_contents(__DIR__ . '/' . $name . '.json'), true, 512, JSON_THROW_ON_ERROR);
}

/** Only fill missing configurations; never replace institutional settings. */
function setupDefaults($database, array $models = ['projects', 'events', 'deadlines', 'initiatives']): void
{
    foreach ($models as $model) {
        if ($model === 'projects') {
            if ($database->adminProjects->findOne([]) !== null) continue;
            foreach (setupDefinition($model) as $definition) $database->adminProjects->insertOne($definition);
            continue;
        }
        $definition = setupDefinition($model);
        $database->adminGeneral->updateOne(
            ['id' => $definition['id']],
            ['$setOnInsert' => $definition],
            ['upsert' => true]
        );
    }
}
