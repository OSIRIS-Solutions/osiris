<h1>
    <i class="ph-duotone ph-person-simple-circle"></i>
    <?= lang('Accessibility', 'Barrierefreiheit') ?>
</h1>
<p>
    <?= lang('OSIRIS is committed to making our platform accessible to all users. We continuously work to improve the accessibility of our website and applications. If you have any feedback or suggestions on how we can enhance accessibility, please contact us at', 'OSIRIS setzt sich dafür ein, unsere Plattform für alle Benutzer zugänglich zu machen. Wir arbeiten kontinuierlich daran, die Barrierefreiheit unserer Website und Anwendungen zu verbessern. Wenn Sie Feedback oder Vorschläge haben, wie wir die Barrierefreiheit verbessern können, kontaktieren Sie uns bitte unter') ?> <a href="https://osiris-solutions.de/contact" target="_blank">https://osiris-solutions.de/contact</a>.
</p>

<form action="<?= ROOTPATH ?>/set-preferences" method="get" class="box padded">
    <h2 class="title">
        <?= lang('Accessibility Settings', 'Barrierefreiheits-Einstellungen') ?>
    </h2>
    <input type="hidden" name="accessibility[check]">
    <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-contrast" name="accessibility[contrast]" value="high-contrast" <?= !empty($_COOKIE['D3-accessibility-contrast'] ?? '') ? 'checked' : '' ?>>
            <label for="set-contrast"><?= lang('High contrast', 'Erhöhter Kontrast') ?></label><br>
            <small class="text-muted">
                <?= lang('Enhance the contrast of the web page for better readability.', 'Erhöht den Kontrast für bessere Lesbarkeit.') ?>
            </small>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-transitions" name="accessibility[transitions]" value="without-transitions" <?= !empty($_COOKIE['D3-accessibility-transitions'] ?? '') ? 'checked' : '' ?>>
            <label for="set-transitions"><?= lang('Reduce motion', 'Verringerte Bewegung') ?></label><br>
            <small class="text-muted">
                <?= lang('Reduce motion and animations on the page.', 'Verringert Animationen und Bewegungen auf der Seite.') ?>
            </small>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-checkbox">
            <input type="checkbox" id="set-dyslexia" name="accessibility[dyslexia]" value="dyslexia" <?= !empty($_COOKIE['D3-accessibility-dyslexia'] ?? '') ? 'checked' : '' ?>>
            <label for="set-dyslexia"><?= lang('Dyslexia mode', 'Dyslexie-Modus') ?></label><br>
            <small class="text-muted">
                <?= lang('Use a special font to increase readability for users with dyslexia.', 'OSIRIS nutzt eine spezielle Schriftart, die von manchen Menschen mit Dyslexie besser gelesen werden kann.') ?>
            </small>
        </div>
    </div>
    <button class="btn primary"><?= lang('Apply', 'Anwenden') ?></button>
</form>