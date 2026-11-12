<?php

/**
 * Page for admin settings for initiatives
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/initiatives
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
require_once BASEPATH . '/php/Initiatives.php';
$config = new FormBuilder(
    entityLabel: translate('common.initiatives'),
    redirectUrl: ROOTPATH . '/crud/admin/initiativesFields',
    entity: 'initiatives',
    systemFields: FormBuilder::INITIATIVE_SYSTEM_FIELDS
);
$fields = Initiatives::fields($osiris);
$systemFields = FormBuilder::systemFieldsFor($fields, $config->systemFields);
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-lightbulb"></i>
        <?= lang('Initiatives', 'Initiativen') ?>
    </h1>


    <?php if (!$Settings->featureEnabled('initiatives')) { ?>
        <div class="alert signal">
            <?= lang(
                'The Initiatives feature is not enabled. Please enable it in the <a href="' . ROOTPATH . '/admin/features#initiatives">Features</a> section of the admin panel to use initiatives.',
                'Die Initiativen-Funktion ist nicht aktiviert. Bitte aktiviere sie im Bereich <a href="' . ROOTPATH . '/admin/features#initiatives">"Funktionen"</a> des Admin-Panels, um Initiativen nutzen zu können.'
            ) ?>
        </div>
    <?php } ?>


    <div class="box">
        <div class="content">
            <h2 class="font-size-18"><?= lang('common.data_fields') ?>:</h2>
            <div class="my-10">
                <?php foreach ($fields as $field):
                    $props = $field['props'] ?? [];
                    $fieldType = $field['type'] ?? 'field';
                    $id = $field['id'] ?? '';
                    $module = $config->modules[$id] ?? [];
                    $name = '';
                    $title = '';
                    $icon = match ($fieldType) { 'custom' => 'ph-textbox', 'heading' => 'ph-text-h', 'paragraph' => 'ph-paragraph', 'hr' => 'ph-minus', default => 'ph-database' };
                    if (in_array($fieldType, ['field', 'custom'], true)) {
                        $name = !empty($props['label']) || !empty($props['label_de'])
                            ? lang(($props['label'] ?? '') ?: ($module['label'] ?? $id), $props['label_de'] ?? null)
                            : ($fieldType === 'custom' ? lang($module['label'] ?? $id, $module['label_de'] ?? null) : ($module['label'] ?? $id));
                        $title = $id;
                    } else {
                        $title = strip_tags(lang($props['text'] ?? 'Divider', $props['text_de'] ?? 'Trennlinie'));
                    }
                ?>
                    <span class="badge mb-5" title="<?= e($title) ?>"><i class="ph <?= $icon ?>"></i> <?= e($name) ?>
                        <?php if (!empty($props['required'])): ?><i class="ph ph-asterisk text-danger" aria-label="<?= e(lang('admin.required_fields')) ?>"></i><?php endif; ?>
                        <?php if (isset($systemFields[$id])): ?><i class="ph ph-lock-simple" aria-label="<?= e(lang('System field', 'Systemfeld')) ?>"></i><?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <a class="btn secondary" href="<?= ROOTPATH ?>/admin/initiatives/fields">
                <i class="ph ph-edit"></i> <?= lang('Edit in Form Builder', 'Im Form-Builder bearbeiten') ?>
            </a>
        </div>
    </div>
</div>
