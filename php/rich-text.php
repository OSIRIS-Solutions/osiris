<?php

/** Validate embedded raster images without fetching remote URLs or reading local paths. */
function rich_text_image_source(string $source, int $limit = 512000): string
{
    if (strlen($source) > (int) ceil($limit * 4 / 3) + 64 ||
        !preg_match('~^data:(image/(?:png|jpeg|gif));base64,([A-Za-z0-9+/=]+)$~D', $source, $match)) return '';
    $bytes = base64_decode($match[2], true);
    if ($bytes === false || strlen($bytes) > $limit) return '';
    $image = @getimagesizefromstring($bytes);
    if (!$image || $image['mime'] !== $match[1] || $image[0] * $image[1] > 16000000) return '';
    return 'data:' . $image['mime'] . ';base64,' . base64_encode($bytes);
}

/** Safe formatting and embedded raster images; external resources are excluded. */
function sanitize_rich_text(string $html): string
{
    if ($html === '') return '';
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>', LIBXML_NONET);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    $render = static function (DOMNode $node) use (&$render): string {
        if ($node instanceof DOMText) return htmlspecialchars($node->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (!$node instanceof DOMElement) return '';
        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) return '';
        if ($tag === 'img') {
            $src = rich_text_image_source($node->getAttribute('src'));
            return $src === '' ? '' : '<img src="' . $src . '" alt="' . htmlspecialchars($node->getAttribute('alt'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
        }
        $children = '';
        foreach ($node->childNodes as $child) $children .= $render($child);
        if (!in_array($tag, ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'a', 'ol', 'ul', 'li', 'blockquote', 'h1', 'h2', 'h3'], true)) return $children;
        if ($tag === 'br') return '<br>';
        $attributes = '';
        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if (preg_match('~^(https?://|mailto:|/(?!/)|\#)~i', $href) && !preg_match('/[\x00-\x20\x7f]/', $href)) {
                $attributes = ' href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" rel="noopener noreferrer"';
            }
        }
        return '<' . $tag . $attributes . '>' . $children . '</' . $tag . '>';
    };
    $result = '';
    foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $node) $result .= $render($node);
    return rich_text_has_text($result, 'html') || str_contains($result, '<img ') ? $result : '';
}

function rich_text_has_text(string $value, string $format = 'plain'): bool
{
    if ($format === 'html') $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return preg_replace('/[\s\x{00a0}\x{200b}]+/u', '', $value) !== '';
}

function render_rich_text(string $value, string $format = 'plain'): string
{
    return $format === 'html' ? sanitize_rich_text($value) : nl2br(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
