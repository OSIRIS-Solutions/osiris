<?php

/**
 * Display all documents in the system.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<style>
    #uploadsTable .badge {
        font-weight: 500;
    }

    #uploadsTable td {
        vertical-align: middle;
    }

    #uploadsTable .btn-group .btn {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }
</style>
<h1>
    <i class="ph-duotone ph-files"></i>
    <?= lang("Documents", "Dokumente") ?>
</h1>

<table id="uploadsTable" class="table table-hover align-middle">
    <thead>
        <tr>
            <th><?= lang('File', 'Datei') ?></th>
            <th><?= lang('Linked to', 'Verknüpft mit') ?></th>
            <th class="text-end"><?= lang('Actions', 'Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($documents as $doc):
            $file_url = ROOTPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension'];

            $id = DB::to_ObjectID($doc['id']);
            $icon = null;
            $con = null;

            $vocabs = [
                'activities' => 'activity-document-types',
                'nagoya-permit' => 'nagoya-document-types',
                'proposals' => 'proposal-document-types',
            ];
            if (!isset($vocabs[$doc['type']])) continue;

            switch ($doc['type']) {
                case 'activities':
                    $con = $osiris->activities->findOne(['_id' => $id], ['projection' => ['name' => '$rendered.title', 'type' => 1, 'icon' => '$rendered.icon']]);
                    $icon = $con['icon'];
                    break;
                case 'proposals':
                case 'nagoya-permit':
                    $icon = '<i class="ph ph-tree-structure"></i>';
                    $con = $osiris->proposals->findOne(['_id' => $id], ['projection' => ['name' => 1, 'type' => 1]]);
                    break;
                default:
                    continue 2;
            }

            $label = $Vocabulary->getValue($vocabs[$doc['type']], $doc['name'] ?? '', lang('Other', 'Sonstiges'));

            $uploader = $DB->getNameFromId($doc['uploaded_by']);
            $date = !empty($doc['uploaded']) ? date('d.m.Y', strtotime($doc['uploaded'])) : '';
            $size = number_format((int)($doc['size'] ?? 0), 0, ',', '.');
            $filename = $doc['filename'] ?? '';
            $desc = trim($doc['description'] ?? '');
            $entityType = ucfirst($con['type'] ?? $doc['type']);
            $entityName = $con['name'] ?? lang('Unknown', 'Unbekannt');
        ?>
            <tr>
                <!-- FILE -->
                <td>
                    <div class="d-flex align-items-center gap-10">
                        <div class="pt-5 font-size-18">
                            <i class="ph ph-<?= getFileIcon($doc['extension'] ?? '') ?> text-muted"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-10">
                                <a href="<?= $file_url ?>" class="badge primary" target="_blank" rel="noopener"><?= $label ?>
                                    <i class="ph ph-download ml-5 font-size-16"></i>
                                </a>
                            </div>

                            <?php if ($desc !== ''): ?>
                                <div class="text-muted font-size-12"><?= nl2br(htmlspecialchars($desc)) ?></div>
                            <?php endif; ?>

                            <div class="text-muted font-size-12 mt-5">
                                <?= lang('File name', 'Dateiname') ?>: <?= htmlspecialchars($filename) ?> <br>
                                <?= lang('Uploaded by', 'Hochgeladen von') ?> <?= htmlspecialchars($uploader) ?>
                                <?= lang('on', 'am') ?> <?= htmlspecialchars($date) ?>
                                · <?= htmlspecialchars($size) ?> <?= lang('Bytes', 'Bytes') ?>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- LINKED ENTITY -->
                <td>
                    <div class="d-flex align-items-center gap-10">
                        <div class="pt-5 font-size-18">
                            <?= $icon ?>
                        </div>
                        <div>
                            <div class="text-muted font-size-12"><?= ($entityType) ?></div>
                            <div class="">
                                <a href="<?= ROOTPATH ?>/activities/view/<?= $con['_id'] ?? '' ?>">
                                    <?= get_preview($entityName, 100) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- ACTIONS -->
                <td class="text-end">
                    <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $file_url ?>" target="_blank" rel="noopener">
                            <i class="ph ph-arrow-square-out"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= $file_url ?>" download>
                            <i class="ph ph-download"></i>
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<script>
    $('#uploadsTable').DataTable({
        pageLength: 25,
        order: [
            [0, 'asc']
        ],
    });
</script>