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
            $image = DB::doc2Arr($news['image'] ?? []);
            if (!empty($image['data'])) {
                $data = $image['data'];
                $base64 = $data instanceof \MongoDB\BSON\Binary ? $data->getData() : (is_string($data) ? $data : '');
                $bytes = base64_decode($base64, true);
                $info = $bytes === false ? false : @getimagesizefromstring($bytes);
                if ($info) {
                    $source = rich_text_image_source('data:' . $info['mime'] . ';base64,' . $base64, 2 * 1024 * 1024);
                    if ($source !== '') $entry['image'] = $source;
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
            if (!empty($entry['image'])) $html .= '<p><img src="' . $escape($entry['image']) . '" alt="' . $escape($entry['label'] ?? '') . '" style="max-width:100%;height:auto"></p>';
            $html .= self::content($entry['content'] ?? '') . '</article><hr>';
        }
        return $html . '</body></html>';
    }

    /** Keep links clickable, including URLs entered as plain text, and embed only validated images. */
    private static function content(string $html, bool $word = false): string
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . sanitize_rich_text($html) . '</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//body//text()[not(ancestor::a)]') as $node) {
            if (!preg_match_all('~(?:https?://|www\.)[^\s<>]+~u', $node->nodeValue, $matches, PREG_OFFSET_CAPTURE)) continue;
            $fragment = $document->createDocumentFragment();
            $offset = 0;
            foreach ($matches[0] as [$match, $position]) {
                $url = rtrim($match, '.,;:!?)');
                $fragment->appendChild($document->createTextNode(substr($node->nodeValue, $offset, $position - $offset)));
                $link = $document->createElement('a');
                $link->setAttribute('href', str_starts_with($url, 'www.') ? 'https://' . $url : $url);
                $link->appendChild($document->createTextNode($url));
                $fragment->appendChild($link);
                $offset = $position + strlen($url);
            }
            $fragment->appendChild($document->createTextNode(substr($node->nodeValue, $offset)));
            $node->parentNode->replaceChild($fragment, $node);
        }
        foreach ($document->getElementsByTagName('a') as $link) {
            $link->setAttribute('style', 'color:#0563C1;text-decoration:underline');
        }
        foreach ($document->getElementsByTagName('img') as $image) {
            $image->setAttribute('style', 'max-width:100%;height:auto');
            if ($word) {
                // Own temporary files survive until the Word writer has embedded their bytes.
                $bytes = base64_decode(explode(',', $image->getAttribute('src'), 2)[1], true);
                $info = getimagesizefromstring($bytes);
                $file = tempnam(sys_get_temp_dir(), 'news-image-');
                if ($file === false || file_put_contents($file, $bytes) === false) throw new RuntimeException('Could not prepare export image.');
                register_shutdown_function(static function () use ($file) { if (is_file($file)) unlink($file); });
                $image->setAttribute('src', $file);
                $scale = min(1, 600 / $info[0], 800 / $info[1]);
                $image->setAttribute('width', (string) round($info[0] * $scale));
                $image->setAttribute('height', (string) round($info[1] * $scale));
                $image->removeAttribute('style');
            }
        }
        $result = '';
        foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $child) $result .= $document->saveXML($child);
        return $result;
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
            if (!empty($entry['image'])) {
                $bytes = base64_decode(explode(',', $entry['image'], 2)[1], true);
                $info = getimagesizefromstring($bytes);
                $scale = min(1, 450 / $info[0], 600 / $info[1]);
                $section->addImage($bytes, ['width' => $info[0] * $scale, 'height' => $info[1] * $scale]);
            }
            if (!empty($entry['content'])) {
                $html = self::content($entry['content'], true);
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
            }
            $section->addTextBreak();
        }
        return $word;
    }
}
