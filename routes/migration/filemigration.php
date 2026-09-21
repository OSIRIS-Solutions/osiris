<?php

/**
 * Migration: activities.files[]  --> uploads collection (+ move files)
 * 
 * Safety rules:
 * - Copy first, verify, then (optionally) delete source.
 * - Idempotent: can be re-run safely.
 * - Comments are in English.
 */

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

$activitiesCol = $osiris->activities;               // adjust
$uploadsCol    = $osiris->uploads;                  // adjust
$UPLOADS_DIR = BASEPATH . '/uploads';            // absolute path to your uploads folder
$DEFAULT_NAME = 'file';

// ==== CONFIG ====
$DRY_RUN = $_GET['dry_run'] ?? true;                                // start with true!
$DELETE_SOURCE = $_GET['delete_source'] ?? false;                         // set true only after successful test
$VERIFY_HASH = $_GET['verify_hash'] ?? false;                           // true = safer, but slower for large PDFs

$summary = [
    'activities_processed' => 0,
    'files_found' => 0,
    'files_migrated' => 0,
    'files_skipped' => 0,
    'missing_files' => 0,
    'errors' => 0,
]; // collect summary info
?>

<h2>
    <i class="ph ph-files"></i>
    <?= lang('admin.migration_of_activity_files_to_uploads_collection') ?>
</h2>


<div class="alert info">
    <h4 class="title">
        <?= lang('admin.note') ?>
    </h4>
    <?=lang('admin.it_is_unfortunately_not_possible_to_determine_who_originally_uploaded_the_m')?>
    <br>
    <?=lang('admin.it_is_also_not_possible_to_estimate_the_type_of_document_for_each_migrated')?>
</div>


<p>
    <?= lang($DRY_RUN ? 'admin.dry_run_mode_description' : 'admin.actual_migration_mode_description') ?>
</p>

<form action="#" method="get">
    <div class="box padded" style="max-width: 40rem;">
        <h4 class="title">
            <i class="ph ph-gear"></i>
            <?= lang('admin.migration_settings') ?>
        </h4>
        <div class="form-group">
            <label for="dry_run"><?= lang('admin.run_settings') ?></label>
            <select id="dry_run" name="dry_run" class="form-control">
                <option value="1" <?= $DRY_RUN ? 'selected' : '' ?>><?= lang('admin.dry_run_no_changes') ?></option>
                <option value="0" <?= !$DRY_RUN ? 'selected' : '' ?>><?= lang('admin.perform_migration') ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="delete_source"><?= lang('admin.handle_source_files') ?></label>
            <select id="delete_source" name="delete_source" class="form-control">
                <option value="0" <?= !$DELETE_SOURCE ? 'selected' : '' ?>><?= lang('admin.keep_source_files') ?></option>
                <option value="1" <?= $DELETE_SOURCE ? 'selected' : '' ?>><?= lang('admin.delete_source_files_after_migration') ?></option>
            </select>
        </div>
        <div class="form-group">
            <label for="verify_hash"><?= lang('admin.verify_file_hash') ?></label>
            <select id="verify_hash" name="verify_hash" class="form-control">
                <option value="0" <?= !$VERIFY_HASH ? 'selected' : '' ?>><?= lang('admin.disabled_faster') ?></option>
                <option value="1" <?= $VERIFY_HASH ? 'selected' : '' ?>><?= lang('admin.enabled_safer_slower') ?></option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn primary">
                <?= lang('admin.run_migration') ?>
            </button>
        </div>
    </div>

</form>


<div class="row row-eq-spacing">
    <div class="col order-last">

        <h2>
            <?= lang('admin.migration_logs') ?>
        </h2>
        <div class="box" id="migration-logs" style="max-height: 40rem; overflow: auto; background: #f9f9f9; padding: 1rem; border: 1px solid #ccc;">
            <pre><?php

                    $filter = [
                        'files' => ['$exists' => true, '$ne' => []],
                    ];

                    // Use a cursor so it doesn't load everything into memory
                    $cursor = $activitiesCol->find($filter, [
                        'projection' => ['files' => 1, 'created_by' => 1],
                    ]);

                    $finfo = new finfo(FILEINFO_MIME_TYPE);

                    function logLine($msg)
                    {
                        echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
                    }

                    /**
                     * Convert a web-ish filepath (/uploads/...) to absolute filesystem path.
                     */
                    function toAbsolutePath(string $uploadsDir, string $filepath): string
                    {
                        // Normalize leading slash
                        if (str_starts_with($filepath, '/')) {
                            $filepath = substr($filepath, 1);
                        }
                        // Now e.g. "uploads/<id>/<file>" or just "uploads/..."
                        // If the stored path already starts with "uploads/", keep it.
                        return rtrim($uploadsDir, '/') . '/' . preg_replace('#^uploads/#', '', $filepath);
                    }

                    /**
                     * Ensure directory exists.
                     */
                    function ensureDir(string $dir, bool $dryRun): void
                    {
                        if (is_dir($dir)) return;
                        if ($dryRun) return;
                        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                            throw new RuntimeException("Failed to create directory: $dir");
                        }
                    }

                    /**
                     * Compute extension from filename; fallback from mimetype if missing.
                     */
                    function guessExtension(string $filename, string $mime): string
                    {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (!empty($ext)) return $ext;

                        // Minimal fallback mapping
                        $map = [
                            'application/pdf' => 'pdf',
                            'image/png' => 'png',
                            'image/jpeg' => 'jpg',
                            'text/plain' => 'txt',
                        ];
                        return $map[$mime] ?? '';
                    }

                    logLine("Starting migration of activity files to uploads collection...");

                    foreach ($cursor as $activity) {
                        $summary['activities_processed']++;
                        $activityId = (string)$activity->_id;
                        $files = $activity->files ?? [];

                        if (empty($files)) continue;

                        logLine("Activity $activityId: " . count($files) . " file(s)");

                        $newUploadIds = [];

                        foreach ($files as $file) {
                            $summary['files_found']++;

                            $origFilename = $file['filename'] ?? null;
                            $origFilepath = $file['filepath'] ?? null;

                            if (!$origFilename || !$origFilepath) {
                                $summary['files_skipped']++;
                                logLine("  - SKIP: missing filename/filepath");
                                continue;
                            }

                            $srcAbs = toAbsolutePath($UPLOADS_DIR, $origFilepath);

                            if (!is_file($srcAbs)) {
                                $summary['missing_files']++;
                                logLine("  - ERROR: source not found: $srcAbs");
                                continue;
                            }

                            $size = filesize($srcAbs);
                            if ($size === false) {
                                $summary['errors']++;
                                logLine("  - ERROR: cannot read filesize: $srcAbs");
                                continue;
                            }

                            $mime = $finfo->file($srcAbs) ?: 'application/octet-stream';
                            $ext  = guessExtension($origFilename, $mime);
                            $today = date('Y-m-d');

                            // Idempotency check: already migrated?
                            // We try to find an uploads doc that points to same activity + same original filename + same size.
                            $existing = $uploadsCol->findOne([
                                'type' => 'activities',
                                'id' => $activityId,
                                'filename' => $origFilename,
                                'size' => (int)$size,
                            ]);

                            if ($existing) {
                                $summary['files_skipped']++;
                                $uploadId = (string)$existing->_id;
                                $targetAbs = rtrim($UPLOADS_DIR, '/') . '/' . $uploadId . ($ext ? '.' . $ext : '');

                                // If target file exists and matches size, we assume OK.
                                if (is_file($targetAbs) && filesize($targetAbs) === $size) {
                                    logLine("  - OK (already migrated): $origFilename -> $uploadId");
                                    $newUploadIds[] = $uploadId;
                                    continue;
                                }
                                // If doc exists but file missing/mismatch, we’ll re-copy below into that id.
                                logLine("  - WARN: uploads doc exists but target file missing/mismatch, will re-copy: $uploadId");
                                $uploadObjectId = $existing->_id;
                            } else {
                                $uploadObjectId = new ObjectId();
                                $uploadId = (string)$uploadObjectId;
                            }


                            $targetAbs = rtrim($UPLOADS_DIR, '/') . '/' . (string)$uploadObjectId . ($ext ? '.' . $ext : '');

                            // Create uploads document if missing
                            if (!$existing) {
                                // read file creation date if possible
                                $fileCreated = $today;
                                $fileMTime = filemtime($srcAbs);
                                if ($fileMTime !== false) {
                                    // to ISODate
                                    $fileCreated = new UTCDateTime($fileMTime * 1000);
                                    $fileCreated = date('Y-m-d', $fileMTime);
                                }
                                $doc = [
                                    '_id' => $uploadObjectId,
                                    'filename' => $origFilename,
                                    'mimetype' => $mime,
                                    'extension' => $ext,
                                    'size' => (int)$size,
                                    'uploaded' => $fileCreated,
                                    'uploaded_by' => $activity['created_by'] ?? 'migration',
                                    'type' => 'activities',
                                    'id' => $activityId,
                                    'name' => $DEFAULT_NAME,
                                    'description' => '',
                                ];

                                if ($DRY_RUN) {
                                    logLine("  - DRY: would insert uploads doc $uploadId for $origFilename");
                                    $summary['files_migrated']++;
                                } else {
                                    $uploadsCol->insertOne($doc);
                                    logLine("  - Inserted uploads doc: $uploadId");
                                }
                            }

                            // Copy to temp file first, then rename (atomic on same filesystem)
                            $tmpTarget = $targetAbs . '.tmp';

                            if ($DRY_RUN) {
                                logLine("  - DRY: would copy $srcAbs -> $targetAbs");
                                $newUploadIds[] = (string)$uploadObjectId;
                                continue;
                            }

                            // If a previous tmp exists, remove it.
                            if (is_file($tmpTarget)) {
                                unlink($tmpTarget);
                            }

                            if (!copy($srcAbs, $tmpTarget)) {
                                logLine("  - ERROR: copy failed to tmp: $tmpTarget");
                                $summary['errors']++;
                                continue;
                            }

                            // Verify size (and optionally hash)
                            $tmpSize = filesize($tmpTarget);
                            if ($tmpSize !== $size) {
                                unlink($tmpTarget);
                                logLine("  - ERROR: size mismatch after copy (src=$size, tmp=$tmpSize)");
                                $summary['errors']++;
                                continue;
                            }

                            if ($VERIFY_HASH) {
                                $srcHash = sha1_file($srcAbs);
                                $tmpHash = sha1_file($tmpTarget);
                                if (!$srcHash || !$tmpHash || $srcHash !== $tmpHash) {
                                    unlink($tmpTarget);
                                    logLine("  - ERROR: hash mismatch after copy");
                                    $summary['errors']++;
                                    continue;
                                }
                            }

                            // Move tmp into final target
                            if (is_file($targetAbs)) {
                                // If target exists already and matches, just drop tmp
                                if (filesize($targetAbs) === $size) {
                                    unlink($tmpTarget);
                                } else {
                                    // Keep a backup just in case
                                    rename($targetAbs, $targetAbs . '.bak.' . time());
                                    rename($tmpTarget, $targetAbs);
                                }
                            } else {
                                rename($tmpTarget, $targetAbs);
                            }
                            $summary['files_migrated']++;

                            // Optionally delete the original
                            if ($DELETE_SOURCE) {
                                if (!unlink($srcAbs)) {
                                    logLine("  - WARN: could not delete source (but migration copy is done): $srcAbs");
                                }
                            }

                            logLine("  - Migrated: $origFilename -> $uploadId");
                            $newUploadIds[] = (string)$uploadObjectId;
                        }

                        // After all files: update activity to remove legacy files field (or keep as backup)
                        if (!$DRY_RUN) {
                            // Option A (recommended): keep old data as files_legacy for audit, then unset files
                            $activitiesCol->updateOne(
                                ['_id' => $activity->_id],
                                [
                                    '$set' => ['files_legacy' => $files],
                                    '$unset' => ['files' => ''],
                                ]
                            );
                            logLine("  - Updated activity: moved files -> files_legacy and unset files");
                        } else {
                            logLine("  - DRY: would move activity.files -> files_legacy and unset files");
                        }
                    }

                    logLine("Done.");

                    ?></pre>
        </div>

    </div>
    <div class="col col-md-3 order-first">

        <h2>
            <?= lang('admin.summary') ?>
        </h2>

        <?php if ($DRY_RUN) { ?>
            <p><?= lang('admin.dry_run_completed_no_changes_were_made_review_the_logs_above_to_perform_the') ?></p>
        <?php } ?>

        <table class="table small">
            <tr>
                <th><?= lang('admin.activities_processed') ?></th>
                <td><?= $summary['activities_processed'] ?></td>
            </tr>
            <tr>
                <th><?= lang('admin.files_found') ?></th>
                <td><?= $summary['files_found'] ?></td>
            </tr>
            <tr>
                <th><?= lang('admin.files_migrated') ?></th>
                <td><?= $summary['files_migrated'] ?></td>
            </tr>
            <tr>
                <th><?= lang('admin.files_skipped') ?></th>
                <td><?= $summary['files_skipped'] ?></td>
            </tr>
            <tr>
                <th><?= lang('admin.missing_source_files') ?></th>
                <td><?= $summary['missing_files'] ?></td>
            </tr>
            <tr>
                <th><?= lang('admin.errors') ?></th>
                <td><?= $summary['errors'] ?></td>
            </tr>
        </table>


    </div>
</div>
