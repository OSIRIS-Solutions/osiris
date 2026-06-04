<?php

/**
 * ReportTemplate class for managing report templates and export styles
 * Used in report generation and export features
 * Provides default styles and token replacement for exports
 * Integrates with PhpWord for Word document generation
 */

require_once "init.php";

class ReportTemplate extends Settings
{
    private array $style;

    public function __construct()
    {
        // Initialize parent
        parent::__construct();
        // Load export design settings from parent
        $style = parent::get('export-design', []);
        $this->style = DB::doc2Arr($style);
        $this->style = self::mergeReportStyle($this->style);
    }

    public function getStyle(): array
    {
        return $this->style;
    }

    private static function wordColor(?string $color, string $fallback = '000000'): string
    {
        $color = ltrim(trim($color ?? ''), '#');
        return preg_match('/^[a-fA-F0-9]{6}$/', $color) ? strtoupper($color) : $fallback;
    }

    private static function reportStyleDefault(): array
    {
        return [
            'font' => [
                'family' => 'Calibri',
                'size' => 11,
            ],
            'headings' => [
                'h1' => ['size' => 16, 'bold' => true, 'color' => '000000', 'numbered' => true],
                'h2' => ['size' => 14, 'bold' => true, 'color' => '000000', 'numbered' => true],
                'h3' => ['size' => 13, 'bold' => true, 'color' => '000000', 'numbered' => true],
                'h4' => ['size' => 12, 'bold' => true, 'color' => '000000', 'numbered' => false],
            ],
            'table' => [
                'borderSize' => 1,
                'borderColor' => 'CCCCCC',
                'cellMargin' => 80,
            ],
            'page' => [
                'marginTop' => 1200,
                'marginRight' => 1200,
                'marginBottom' => 1200,
                'marginLeft' => 1200,
            ],
            'footer' => [
                'text' => 'Generated with OSIRIS',
                'pageNumbers' => true,
            ],
        ];
    }

    private static function mergeReportStyle(array $style): array
    {
        return array_replace_recursive(self::reportStyleDefault(), $style);
    }

    public function applyReportStyle(\PhpOffice\PhpWord\PhpWord $phpWord, string $usecase = ''): void
    {
        $style = $this->getStyle();
        $phpWord->setDefaultFontName($style['font']['family'] ?? 'Calibri');
        $phpWord->setDefaultFontSize((int)($style['font']['size'] ?? 11));

        $numbered = false;
        foreach (($style['headings'] ?? []) as $heading) {
            if (!empty($heading['numbered'])) {
                $numbered = true;
                break;
            }
        }

        if ($numbered && $usecase !== 'cv') {
            $phpWord->addNumberingStyle('hNum', [
                'type' => 'multilevel',
                'levels' => [
                    ['pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'],
                    ['pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2'],
                    ['pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3'],
                    ['pStyle' => 'Heading4', 'format' => 'decimal', 'text' => '%1.%2.%3.%4'],
                ],
            ]);
        }

        for ($i = 1; $i <= 4; $i++) {
            $cfg = $style['headings']['h' . $i] ?? [];

            $paragraphStyle = [
                'spaceBefore' => (int)($cfg['spaceBefore'] ?? 120),
                'spaceAfter' => (int)($cfg['spaceAfter'] ?? 80),
            ];

            if (!empty($cfg['numbered'])) {
                $paragraphStyle['numStyle'] = 'hNum';
                $paragraphStyle['numLevel'] = $i - 1;
            }

            $phpWord->addTitleStyle(
                $i,
                [
                    'bold' => !empty($cfg['bold']),
                    'size' => (int)($cfg['size'] ?? 12),
                    'color' => self::wordColor($cfg['color'] ?? null),
                ],
                $paragraphStyle
            );
        }

        $borderSize = (int)($style['table']['borderSize'] ?? 6);
        $borderColor = self::wordColor($style['table']['borderColor'] ?? null, 'CCCCCC');

        $tableStyle = [
            'cellMargin' => (int)($style['table']['cellMargin'] ?? 80),
        ];

        if ($borderSize > 0) {
            $tableStyle += [
                'borderSize' => $borderSize,
                'borderColor' => $borderColor,

                'borderTopSize' => $borderSize,
                'borderTopColor' => $borderColor,

                'borderBottomSize' => $borderSize,
                'borderBottomColor' => $borderColor,

                'borderLeftSize' => $borderSize,
                'borderLeftColor' => $borderColor,

                'borderRightSize' => $borderSize,
                'borderRightColor' => $borderColor,

                'insideHSize' => $borderSize,
                'insideHColor' => $borderColor,

                'insideVSize' => $borderSize,
                'insideVColor' => $borderColor,
            ];
        }

        $phpWord->addTableStyle('ReportTable', $tableStyle);
    }

    public function addReportSection(
        \PhpOffice\PhpWord\PhpWord $phpWord
    ): \PhpOffice\PhpWord\Element\Section {
        $style = $this->getStyle();
        return $phpWord->addSection([
            'marginTop' => (int)$style['page']['marginTop'],
            'marginRight' => (int)$style['page']['marginRight'],
            'marginBottom' => (int)$style['page']['marginBottom'],
            'marginLeft' => (int)$style['page']['marginLeft'],
        ]);
    }

    public function addReportFooter(
        \PhpOffice\PhpWord\Element\Section $section,
        string $overrideText = ''
    ): void {
        $style = $this->getStyle();
        $footerSettings = $style['footer'] ?? [];

        $text = trim($overrideText !== '' ? $overrideText : ($footerSettings['text'] ?? ''));
        $showPageNumbers = !empty($footerSettings['pageNumbers']);

        if ($text === '' && !$showPageNumbers) {
            return;
        }

        $footer = $section->addFooter();

        if ($text !== '') {
            $footer->addText($text, ['size' => 9, 'color' => '666666']);
        }

        if ($showPageNumbers) {
            $footer->addPreserveText(
                lang('Page {PAGE} of {NUMPAGES}', 'Seite {PAGE} von {NUMPAGES}'),
                ['size' => 9, 'color' => '666666'],
                ['align' => 'right']
            );
        }
    }
}
