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
    <?= lang(
        "Migration of Activity Files to Uploads Collection",
        "Migration von Aktivitätsdateien in die Uploads-Sammlung"
    ) ?>
</h2>


<div class="alert info">
    <h4 class="title">
        <?= lang("Note", "Hinweis") ?>
    </h4>
    <?=lang('It is unfortunately not possible to determine who originally uploaded the migrated documents and when this happened. Wherever possible, we add the author of the activity as the uploader and use the creation date of the file as the upload date. In all other cases, the username "migration" is used as the uploader and today\'s date as the upload date.', 'Es ist leider nicht möglich, bei den migrierten Dokumenten zu sagen, wer sie ursprünglich hochgeladen hat und wann dies geschehen ist. Wo immer möglich fügen wir die Verfasser:in der Aktivität als Hochladende hinzu und verwenden das Erstellungsdatum der Datei als Hochladedatum. In allen anderen Fällen wird der Benutzername "migration" als Hochladender verwendet und das heutige Datum als Hochladedatum.')?>
    <br>
    <?=lang('It is also not possible to estimate the type of document for each migrated file. All documents are therefore only annotated as "file". This can be manually adjusted in the activities if necessary.', 'Außerdem ist es auch nicht möglich, abzuschätzen, um welche Art von Dokument es sich jeweils handelt. Alle Dokumente werden deshalb nur als "Datei" annotiert. Dies kann ggf. manuell in den Aktivitäten angepasst werden.')?>
</div>


<p>
    <?= lang(
        $DRY_RUN ? "Dry run mode: no changes will be made. To perform actual migration, set Dry Run to false." : "Actual migration mode: changes will be made.",
        $DRY_RUN ? "Trockenlaufmodus: Es werden keine Änderungen vorgenommen. Um die tatsächliche Migration durchzuführen, setzen Sie Dry Run auf false." : "Tatsächlicher Migrationsmodus: Es werden Änderungen vorgenommen."
    ) ?>
</p>

<form action="#" method="get">
    <div class="box padded" style="max-width: 40rem;">
        <h4 class="title">
            <i class="ph ph-gear"></i>
            <?= lang("Migration Settings", "Migrations-Einstellungen") ?>
        </h4>
        <div class="form-group">
            <label for="dry_run"><?= lang('Run Settings', 'Migrationseinstellungen') ?></label>
            <select id="dry_run" name="dry_run" class="form-control">
                <option value="1" <?= $DRY_RUN ? 'selected' : '' ?>><?= lang("Dry Run (no changes)", "Trockenlauf (keine Änderungen)") ?></option>
                <option value="0" <?= !$DRY_RUN ? 'selected' : '' ?>><?= lang("Perform Migration", "Migration durchführen") ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="delete_source"><?= lang("Handle Source Files", "Umgang mit Quelldateien") ?></label>
            <select id="delete_source" name="delete_source" class="form-control">
                <option value="0" <?= !$DELETE_SOURCE ? 'selected' : '' ?>><?= lang("Keep source files", "Quelldateien behalten") ?></option>
                <option value="1" <?= $DELETE_SOURCE ? 'selected' : '' ?>><?= lang("Delete source files after migration", "Quelldateien nach der Migration löschen") ?></option>
            </select>
        </div>
        <div class="form-group">
            <label for="verify_hash"><?= lang("Verify File Hash", "Datei-Hash überprüfen") ?></label>
            <select id="verify_hash" name="verify_hash" class="form-control">
                <option value="0" <?= !$VERIFY_HASH ? 'selected' : '' ?>><?= lang("Disabled (faster)", "Deaktiviert (schneller)") ?></option>
                <option value="1" <?= $VERIFY_HASH ? 'selected' : '' ?>><?= lang("Enabled (safer, slower)", "Aktiviert (sicherer, langsamer)") ?></option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn primary">
                <?= lang("Run Migration", "Migration ausführen") ?>
            </button>
        </div>
    </div>

</form>


<div class="row row-eq-spacing">
    <div class="col order-last">

        <h2>
            <?= lang("Migration Logs", "Migrationsprotokolle") ?>
        </h2>
        <div class="box" id="migration-logs" style="max-height: 40rem; overflow: auto; background: #f9f9f9; padding: 1rem; border: 1px solid #ccc;">
            <pre><?php

                    $filter = [
                        'files' => ['$exists' => true, '$ne' => []],
                    ];

                    // Use a cursor so it doesn't load everything into memory
                    $cursor = $activitiesCol->find($filter, [
                        'projection' => ['files' => 1],
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
            <?= lang("Summary", "Zusammenfassung") ?>
        </h2>

        <?php if ($DRY_RUN) { ?>
            <p><?= lang(
                    "Dry run completed. No changes were made. Review the logs above. To perform the actual migration, set Dry Run to false and run again.",
                    "Der Trockenlauf ist abgeschlossen. Es wurden keine Änderungen vorgenommen. Überprüfen Sie die obigen Protokolle. Um die tatsächliche Migration durchzuführen, setzen Sie Dry Run auf false und führen Sie sie erneut aus."
                ) ?></p>
        <?php } ?>

        <table class="table small">
            <tr>
                <th><?= lang("Activities Processed", "Verarbeitete Aktivitäten") ?></th>
                <td><?= $summary['activities_processed'] ?></td>
            </tr>
            <tr>
                <th><?= lang("Files Found", "Gefundene Dateien") ?></th>
                <td><?= $summary['files_found'] ?></td>
            </tr>
            <tr>
                <th><?= lang("Files Migrated", "Migrierte Dateien") ?></th>
                <td><?= $summary['files_migrated'] ?></td>
            </tr>
            <tr>
                <th><?= lang("Files Skipped", "Übersprungene Dateien") ?></th>
                <td><?= $summary['files_skipped'] ?></td>
            </tr>
            <tr>
                <th><?= lang("Missing Source Files", "Fehlende Quelldateien") ?></th>
                <td><?= $summary['missing_files'] ?></td>
            </tr>
            <tr>
                <th><?= lang("Errors", "Fehler") ?></th>
                <td><?= $summary['errors'] ?></td>
            </tr>
        </table>


    </div>
</div>