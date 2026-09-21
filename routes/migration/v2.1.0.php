<?php
// 

/* Create an index if it doesn't already exist. */
if (!function_exists('ensureIndex')) {
    function ensureIndex($collection, array $keys, array $options = [])
    {
        try {
            if (empty($options['name'])) {
                $parts = [];
                foreach ($keys as $k => $v) $parts[] = $k . '_' . $v;
                $options['name'] = 'cp_' . implode('__', $parts);
            }

            $name = $collection->createIndex($keys, $options);

            echo '<li class="migration-ok">✓ ';
            echo e($collection->getCollectionName()) . ' → <code>' . e($name) . '</code>';
            echo '</li>';

            return true;
        } catch (Throwable $e) {
            echo '<li class="migration-error">✗ ';
            echo e($collection->getCollectionName()) . ' → ' . e($e->getMessage());
            echo '</li>';

            return false;
        }
    }
}

echo '<div class="migration-card">';
echo '<h3>' . lang('common.creating_or_confirming_indexes_for_fast_journal_table') . '</h3>';
echo '<p class="migration-muted">' . lang('common.osiris_is_creating_or_confirming_the_indexes_required_for_fast_journal_tabl') . '</p>';
echo '<ul class="migration-index-list">';
ensureIndex($osiris->activities, ['journal_id' => 1]);
echo '</ul>';
echo '</div>';
