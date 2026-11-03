<?php

/** Rendering safeguards shared by the HTML and DOCX report exports. */
class ReportExport
{
    private array $issues = [];
    private string $block = '';

    public function setBlock(int $number, array|ArrayAccess $step): void
    {
        $this->block = lang('Block', 'Baustein') . ' ' . $number . ' (' .
            ($step['title'] ?? $step['name'] ?? $step['type'] ?? '?') . ')';
    }

    public function getIssues(): array
    {
        return $this->issues;
    }

    /** Turn library warnings into catchable failures without printing them. */
    public function checked(callable $render)
    {
        set_error_handler(static function ($severity, $message, $file, $line) {
            if ($severity & (E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE)) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
            // PHPWord also emits deprecations; keep those out of the response.
            return true;
        });
        try {
            return $render();
        } finally {
            restore_error_handler();
        }
    }

    public function issue($container, string $message, string $detail): void
    {
        $notice = $this->plainText($this->block . ': ' . $message);
        $this->issues[] = $notice;
        error_log('Report export [' . $this->block . ']: ' . $detail);
        // Do not send an error message back through the failing HTML parser.
        $container->addText($notice, ['bold' => true, 'color' => 'B00020']);
    }

    public function addHtml($container, $html): void
    {
        $html = (string) ($html ?? '');
        if ($html === '') return;

        try {
            $normalized = $this->normalizeHtml($html);
            $this->checked(static function () use ($container, $normalized) {
                \PhpOffice\PhpWord\Shared\Html::addHtml($container, $normalized, false, false);
            });
        } catch (Throwable $error) {
            $this->issue($container,
                lang('Formatting could not be exported; the content follows as plain text.',
                    'Die Formatierung konnte nicht exportiert werden; der Inhalt folgt als Klartext.'),
                $error->getMessage());
            $container->addText($this->plainText($html));
        }
    }

    private function normalizeHtml(string $html): string
    {
        // Browser HTML allows these tags without a closing slash; PHPWord loads XML.
        $html = preg_replace_callback(
            '~<(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)\b((?:"[^"]*"|\'[^\']*\'|[^\'">])*)>~i',
            static fn($match) => '<' . strtolower($match[1]) . rtrim(rtrim($match[2]), '/') . ' />',
            $html
        );
        // Preserve XML entities and decode HTML-only entities such as &nbsp;.
        $html = preg_replace_callback('/&([a-z][a-z0-9]+);/i', static function ($match) {
            if (in_array($match[1], ['amp', 'lt', 'gt', 'quot', 'apos'], true)) return $match[0];
            return html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }, $html);
        $html = preg_replace('/&(?!#\d+;|#x[0-9a-f]+;|[a-z][a-z0-9]+;)/i', '&amp;', $html);

        $previous = libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument();
            if (!$dom->loadXML('<body>' . $html . '</body>', LIBXML_NONET)) {
                throw new RuntimeException('Invalid HTML/XML in report content.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        return $html;
    }

    private function plainText(string $html): string
    {
        $html = preg_replace('~<br\b[^>]*>|</(?:p|div|li|tr|h[1-6])\s*>~i', "\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Invalid XML characters must not make the fallback corrupt the DOCX.
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
    }
}
