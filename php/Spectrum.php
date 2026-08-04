<?php

/**
 * Spectrum class
 */

class Spectrum
{

    public static function retrieve($osiris, $entity, $id)
    {
        $spectrum_filter = [
            'type' => 'publication',
            'openalex.topics' => ['$exists' => true, '$ne' => []]
        ];
        switch ($entity) {
            case 'groups':
            case 'group':
            case 'units':
            case 'unit':
                $spectrum_filter['units'] = $id;
                break;
            case 'persons':
            case 'person':
                $spectrum_filter['rendered.users'] = $id;
                break;
            case 'topics':
            case 'topic':
                $spectrum_filter['topics'] = $id;
                break;
            default:
                return [];
        }

        $count_spectrum = $osiris->activities->count($spectrum_filter);
        if ($count_spectrum === 0) {
            return [];
        }
        $spectrum = $osiris->activities->aggregate([
            ['$match' => $spectrum_filter],
            // total number of matched activities
            ['$unwind' => '$openalex.topics'],
            // group by topic id
            ['$group' => [
                '_id' => '$openalex.topics.id',
                'count' => ['$sum' => 1],
                'sumScore' => ['$sum' => '$openalex.topics.score'],
                'topic' => ['$first' => '$openalex.topics'],
                'total' => ['$first' => $count_spectrum]
            ]],
            // compute averages + share
            ['$addFields' => [
                'avg_score' => ['$divide' => ['$sumScore', '$count']],
                'share' => ['$divide' => ['$count', $count_spectrum]],
                // optional combined weight (tweakable)
                'weight' => ['$multiply' => [
                    ['$divide' => ['$count', $count_spectrum]],
                    ['$divide' => ['$sumScore', $count_spectrum]]
                ]]
            ]],
            // filter noise
            ['$match' => ['share' => ['$gte' => 0.05]]],
            ['$sort' => ['weight' => -1]],
            ['$limit' => 25]
        ])->toArray();

        return $spectrum;
    }

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

    public static function single($id, $name, $score, $title = '', $domain = '', $count = 0, $filter = null)
    {
        return '<span class="spectrum spectrum-' . $domain . '"
        data-id="' . e($id) . '"
        data-score="' . $score . '"
        data-name="' . e($name) . '"
        data-domain="' . e($domain) . '"
        data-count="' . ($count) . '"
        data-filter="' . e($filter) . '"
        title="' . e($title) . '">
        <div role="progressbar" aria-valuenow="' . $score . '" aria-valuemin="0" aria-valuemax="100" style="--value: ' . $score . '"></div>
        ' . e($name) . '
    </span>';
    }

    public static function render($spectrum, $count = null, $class = '', $filter = null)
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
                            $aggr['count'],
                            $filter
                        );
                    } ?>
                <?php } ?>
            </div>
            <div class="footer d-flex justify-content-between align-items-center">
                <?php if ($count !== null) { ?>
                    <?php self::hint($count); ?>
                <?php } elseif (isset($spectrum['manual'])) { ?>
                    <small><?= lang('These topics were manually adjusted.', 'Diese Themen wurden manuell angepasst.') ?></small>
                <?php } else { ?>
                    <small><?= lang('These topics are automatically assigned by OpenAlex.', 'Diese Themen werden automatisch von OpenAlex vergeben.') ?></small>
                    <a href="<?= ROOTPATH ?>/spectrum#what-is-spectrum" class="ml-10" style="white-space: nowrap;"><i class="ph ph-question"></i> <?= lang('Learn more', 'Erfahre mehr') ?></a>
                <?php } ?>
            </div>


            <script src="<?= ROOTPATH ?>/js/popover.js"></script>
            <script>
                $(document).ready(function() {
                    spectrumTooltip();
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
                ' Since there are only a few publications in OSIRIS with an assigned spectrum, the results may be incomplete or biased.',
                ' Da es nur wenige Publikationen in OSIRIS mit zugewiesenen Schwerpunkten gibt, können die Ergebnisse unvollständig sein oder verzerrt wirken.'
            );
        }
        echo '</small>';
        echo '<a href="' . ROOTPATH . '/spectrum#what-is-spectrum" class="ml-10" style="white-space: nowrap;"><i class="ph ph-question"></i> ' . lang('Learn more', 'Erfahre mehr') . '</a>';
    }
}
