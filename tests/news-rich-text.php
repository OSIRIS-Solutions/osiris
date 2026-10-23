<?php

// Run with: php tests/news-rich-text.php (no database required).
require __DIR__ . '/fixtures/news.php';
$html = '<p>Grüße <strong>Welt</strong> <em>English</em></p><ul><li>Erster Punkt</li></ul><p><a href="https://example.org/news">Mehr erfahren</a></p>';
$clean = sanitize_rich_text($html);
check(str_contains($clean, '<strong>Welt</strong>') && str_contains($clean, '<ul><li>Erster Punkt</li></ul>') && str_contains($clean, 'href="https://example.org/news"'), 'Retain supported formatting, links and Unicode.');
check(sanitize_rich_text($clean) === $clean, 'Sanitization is stable across saving and rendering.');
$unsafe = '<p onclick="alert(1)" style="color:red">Text<script>alert(1)</script><img src=x onerror=alert(1)><svg><script>alert(1)</script></svg></p><a href="java&#115;cript:alert(1)">Link</a><a href="data:text/html,attack">Other link</a>';
$safe = sanitize_rich_text($unsafe);
check(!str_contains($safe, 'onclick') && !str_contains($safe, '<script') && !str_contains($safe, '<img') && !str_contains($safe, '<svg') && !str_contains($safe, 'href='), 'Drop executable markup, attributes, embeds and unsafe link protocols.');
check(str_contains($safe, 'Text') && str_contains($safe, 'Link'), 'Retain readable text when unsupported markup is removed.');
check(render_rich_text('<strong>Literal old text</strong>') === '&lt;strong&gt;Literal old text&lt;/strong&gt;', 'Older plain texts keep literal markup instead of becoming HTML.');
foreach (['<p><br></p>', '<p>&nbsp;</p>', '<script>alert(1)</script>', '<img src=x>'] as $emptyHtml) {
    check(sanitize_rich_text($emptyHtml) === '', 'Normalize empty or unsupported editor contents to an empty value.');
    rejects(fn() => NewsEditorial::apply('ready', ['outlet_id' => $website['id'], 'format' => 'html', 'content' => ['de' => $emptyHtml]], $preparedNews, $reviewConfiguration, 'editor', $now), 'Empty HTML does not satisfy readiness.');
}
$richInput = ['outlet_id' => $website['id'], 'format' => 'html', 'content' => ['de' => $html, 'en' => '<p><b>English</b></p>']];
$richNews = array_merge($preparedNews, NewsEditorial::apply('ready', $richInput, $preparedNews, $reviewConfiguration, 'editor', $now));
$richContribution = NewsEditorial::contributions($richNews)[$website['id']];
check($richContribution['format'] === 'html' && $richContribution['content']['de'] === $clean, 'Store sanitized rich text with both language keys and an explicit format.');
$richReviewNews = $mutate('request_review', $richNews);
$richReview = NewsReview::pending($richReviewNews);
check($richReview['contributions'][0]['format'] === 'html' && NewsReview::matches($richReview, $richReviewNews), 'Review snapshots retain the rich text format and remain comparable.');
$news = $richReviewNews;
$DB = new NewsReviewTestDB();
$Settings = new NewsRequestTestSettings([], $reviewConfiguration);
$_SESSION['username'] = 'author';
ob_start(); include BASEPATH . '/pages/news/review.php'; $preview = ob_get_clean();
check(str_contains($preview, '<strong>Welt</strong>') && !str_contains($preview, '&lt;strong&gt;Welt'), 'Author previews render the actual supported formatting.');
check(str_contains($preview, '&lt;script&gt;Review teaser&lt;/script&gt;'), 'Teasers remain plain text while content supports rich text.');
$legacyConfiguration = $reviewConfiguration;
$legacyConfiguration['outlets'][0]['fields'] = ['title'];
$hidden = NewsEditorial::apply('save', $richInput, $preparedNews, $legacyConfiguration, 'editor', $now);
check($hidden['editorial_contributions'][0]['format'] === 'plain' && $hidden['editorial_contributions'][0]['content'] === $preparedNews['editorial_contributions'][0]['content'], 'Hidden content fields cannot be changed or converted through forged inputs.');
echo "News rich text checks passed.\n";
