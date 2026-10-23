<?php

require_once __DIR__ . '/NewsEditorial.php';

/** Report selection and downloads are independent of Portfolio output settings. */
class NewsExport
{
    public static function filters(array $input, array $configuration): array
    {
        $result = [];
        foreach (['channel' => 'original', 'from' => '', 'until' => '', 'language' => lang('en', 'de'), 'format' => 'html'] as $key => $default) {
            if (isset($input[$key]) && !is_string($input[$key])) throw new InvalidArgumentException(lang('Invalid report filter.', 'Ungültiger Berichtsfilter.'));
            $result[$key] = trim($input[$key] ?? $default);
        }
        if (!in_array($result['channel'], array_merge(['original'], array_column($configuration['outlets'], 'id')), true)
            || !in_array($result['language'], ['en', 'de'], true) || !in_array($result['format'], ['html', 'docx'], true)) {
            throw new InvalidArgumentException(lang('Invalid report selection.', 'Ungültige Berichtsauswahl.'));
        }
        foreach (['from', 'until'] as $key) {
            $value = $result[$key];
            if ($value === '') continue;
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException(lang('Please enter a valid date.', 'Bitte gib ein gültiges Datum ein.'));
        }
        if ($result['from'] !== '' && $result['until'] !== '' && $result['from'] > $result['until']) {
            throw new InvalidArgumentException(lang('The start date must precede the end date.', 'Das Startdatum muss vor dem Enddatum liegen.'));
        }
        return $result;
    }

    public static function query(array $filters): array
    {
        $date = [];
        if ($filters['from'] !== '') $date['$gte'] = $filters['from'];
        // An exclusive next-day bound also includes news dates with a time component.
        if ($filters['until'] !== '') $date['$lt'] = (new DateTimeImmutable($filters['until']))->modify('+1 day')->format('Y-m-d');
        return $date ? ['date' => $date] : [];
    }

    public static function entries(iterable $documents, array $configuration, array $filters): array
    {
        $entries = [];
        $outlets = array_column($configuration['outlets'], null, 'id');
        $localized = static function ($values) use ($filters): string {
            $localizedValues = [];
            if (is_iterable($values)) foreach ($values as $key => $value) $localizedValues[$key] = $value;
            $values = $localizedValues;
            return (string) (($values[$filters['language']] ?? '') ?: ($values[$filters['language'] === 'de' ? 'en' : 'de'] ?? ''));
        };
        foreach ($documents as $document) {
            $news = DB::doc2Arr($document);
            $date = substr($news['date'] ?? '', 0, 10);
            if (($filters['from'] !== '' && $date < $filters['from']) || ($filters['until'] !== '' && $date > $filters['until'])) continue;
            $entry = ['date' => $date, 'id' => (string) $news['_id'], 'label' => $localized(['en' => $news['title'] ?? '', 'de' => $news['title_de'] ?? ''])];
            if ($filters['channel'] === 'original') {
                foreach (['title', 'teaser', 'content'] as $field) {
                    $value = $localized(['en' => $news[$field] ?? '', 'de' => $news[$field . '_de'] ?? '']);
                    $entry[$field] = $field === 'content' ? render_rich_text($value, 'html') : $value;
                }
            } else {
                $id = $filters['channel'];
                $requests = array_column(NewsEditorial::requests($news), null, 'outlet_id');
                $contributions = NewsEditorial::contributions($news);
                if (NewsEditorial::internalOnly($news) || !isset($requests[$id], $contributions[$id], $outlets[$id])
                    || !NewsEditorial::isReleased($requests[$id])) continue;
                foreach (NewsOutlets::fields($outlets[$id]) as $field) {
                    $value = $localized($contributions[$id][$field] ?? []);
                    $entry[$field] = $field === 'content' ? render_rich_text($value, $contributions[$id]['format'] ?? 'plain') : $value;
                }
            }
            $entries[] = $entry;
        }
        return $entries;
    }

    public static function html(array $entries, string $title, string $language): string
    {
        $escape = static fn($text) => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<!DOCTYPE html><html lang="' . $escape($language) . '"><head><meta charset="UTF-8"><title>' . $escape($title) . '</title></head><body><h1>' . $escape($title) . '</h1>';
        foreach ($entries as $entry) {
            $html .= '<article><p>' . $escape($entry['date']) . '</p>';
            if (!empty($entry['title'])) $html .= '<h2>' . $escape($entry['title']) . '</h2>';
            if (!empty($entry['teaser'])) $html .= '<p>' . nl2br($escape($entry['teaser'])) . '</p>';
            $html .= ($entry['content'] ?? '') . '</article><hr>';
        }
        return $html . '</body></html>';
    }

    public static function word(array $entries, string $title): \PhpOffice\PhpWord\PhpWord
    {
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $word = new \PhpOffice\PhpWord\PhpWord();
        $word->addTitleStyle(1, ['size' => 18, 'bold' => true]);
        $word->addTitleStyle(2, ['size' => 14, 'bold' => true]);
        $section = $word->addSection();
        $section->addTitle($title, 1);
        foreach ($entries as $entry) {
            $section->addText($entry['date']);
            if (!empty($entry['title'])) $section->addTitle($entry['title'], 2);
            if (!empty($entry['teaser'])) $section->addText($entry['teaser']);
            if (!empty($entry['content'])) {
                // PHPWord expects XML-compatible HTML; the sanitizer excludes external resources.
                $html = str_replace(['<br>', '&nbsp;'], ['<br />', '&#160;'], $entry['content']);
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
            }
            $section->addTextBreak();
        }
        return $word;
    }
}
