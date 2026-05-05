<?php

/**
 * Spectrum class
 */

class Spectrum
{
    public static function aggregate($spectrum)
    {
        if (empty($spectrum)) return [];
        $spectrum_by_field = [];

        if (!isset($spectrum[0]) || !isset($spectrum[0]['weight'])) {
            // if spectrum is not aggregated, aggregate it by topic id and sum the weights
            foreach ($spectrum as $topic) {
                $field = $topic['field'] ?? 'unknown';
                if (!isset($spectrum_by_field[$field])) {
                    $spectrum_by_field[$field] = [];
                }
                $topic = [
                    'topic' => $topic,
                    'score' => floatval($topic['score'] ?? 1) * 100,
                    'count' => null
                ];
                $spectrum_by_field[$field][] = $topic;
            }
        } else {

        $max_weight = max(array_column($spectrum, 'weight'));
        foreach ($spectrum as $aggr) {
            $field = $aggr['topic']['field'] ?? 'unknown';
            $score =  round($aggr['weight'] * 100 / $max_weight);
            if ($score < 4) continue; // skip very weak topics
            $aggr['score'] = $score; // overwrite weight with normalized score for visualization
            if (!isset($spectrum_by_field[$field])) {
                $spectrum_by_field[$field] = [];
            }
            $spectrum_by_field[$field][] = $aggr;
        }
        }
        return $spectrum_by_field;
    }

    public static function single($id, $name, $score, $title = '', $domain = '', $count = 0)
    {
        return '<span class="spectrum spectrum-' . $domain . '"
        data-id="' . e($id) . '"
        data-score="' . $score . '"
        data-name="' . e($name) . '"
        data-domain="' . e($domain) . '"
        data-count="' . ($count) . '"
        title="' . e($title) . '">
        <div role="progressbar" aria-valuenow="' . $score . '" aria-valuemin="0" aria-valuemax="100" style="--value: ' . $score . '"></div>
        ' . e($name) . '
    </span>';
    }

    public static function render($spectrum, $count = null, $class = '')
    {
        $spectrum_by_field = self::aggregate($spectrum);
?>
        <div class="box <?= $class ?>" id="spectrum">
            <div class="content">
                <?php foreach ($spectrum_by_field as $field => $aggrs) {
                    $domain_id = $aggrs[0]['topic']['domain_id'] ?? 'unknown';
                ?>
                    <h4 class="spectrum-title spectrum-<?= strtolower($domain_id) ?>"><?= lang($field) ?></h4>
                    <?php foreach ($aggrs as $aggr) {
                        $spectrum = $aggr['topic'];
                        echo self::single(
                            $spectrum['id'] ?? null,
                            $spectrum['name'] ?? 'spectrum',
                            $aggr['score'],
                            $spectrum['path'] ?? $spectrum['name'] ?? 'spectrum',
                            $spectrum['domain_id'] ?? 'unknown',
                            $aggr['count']
                        );
                    } ?>
                <?php } ?>
            </div>
                <div class="footer d-flex justify-content-between align-items-center">
            <?php if ($count !== null) { ?>
                    <?php self::hint($count); ?>
            <?php } else { ?>
                    <small><?= lang('These topics are automatically assigned by OpenAlex.', 'Diese Themen werden automatisch von OpenAlex vergeben.') ?></small>
                    <a href="<?= ROOTPATH ?>/spectrum#what-is-spectrum" class="ml-10" style="white-space: nowrap;"><i class="ph ph-question"></i> <?= lang('Learn more', 'Erfahre mehr') ?></a>
                </div>
            <?php } ?>


            <script src="<?= ROOTPATH ?>/js/popover.js"></script>
            <script>
                $(document).ready(function() {
                    spectrumTooltip()
                });
            </script>
        </div>
<?php

    }

    public static function hint($count)
    {
        echo '<small>';
        echo lang(
            'Research Spectrum is based on the analysis of ' . $count . ' publications in OSIRIS.',
            'Das Forschungs-Spektrum basiert auf der Analyse von ' . $count . ' Publikationen in OSIRIS.'
        );
        if ($count <= 10) {
            echo lang(
                'Since there are only a few publications in OSIRIS with an assigned spectrum, the results may be incomplete or biased.',
                'Da es nur wenige Publikationen in OSIRIS mit zugewiesenen Schwerpunkten gibt, können die Ergebnisse unvollständig sein oder verzerrt wirken.'
            );
        }
        echo '</small>';
        echo '<a href="' . ROOTPATH . '/spectrum#what-is-spectrum" class="ml-10" style="white-space: nowrap;"><i class="ph ph-question"></i> ' . lang('Learn more', 'Erfahre mehr') . '</a>';
    }
}
