<?php

final class Nagoya
{
  private const MAP = [
    
    'incomplete' => [
      'color' => 'signal',
      'icon' => 'ph-list-magnifying-glass',
      'title_en' => 'Incomplete',
      'title_de' => 'Unvollständig'
    ],
    'abs-review' => [
      'color' => 'signal',
      'icon' => 'ph-users-three',
      'title_en' => 'Country review pending',
      'title_de' => 'Länderbewertung ausstehend'
    ],
    'not-relevant' => [
      'color' => 'muted',
      'icon' => 'ph-x-circle',
      'title_en' => 'not ABS-relevant',
      'title_de' => 'nicht ABS-relevant'
    ],
    'researcher-input' => [
      'color' => 'signal',
      'icon' => 'ph-user-gear',
      'title_en' => 'Researcher input required',
      'title_de' => 'Eingabe durch Forschende erforderlich'
    ],
    'awaiting-abs-evaluation' => [
      'color' => 'signal',
      'icon' => 'ph-file-search',
      'title_en' => 'Awaiting ABS evaluation',
      'title_de' => 'Wartet auf ABS-Bewertung'
    ],
    'out-of-scope' => [
      'color' => 'muted',
      'icon' => 'ph-circle',
      'title_en' => 'Out of scope',
      'title_de' => 'Außerhalb des Geltungsbereichs'
    ],
    'permits-pending' => [
      'color' => 'signal',
      'icon' => 'ph-handshake',
      'title_en' => 'Permits pending',
      'title_de' => 'Genehmigungen ausstehend'
    ],
    'compliant' => [
      'color' => 'success',
      'icon' => 'ph-check-circle',
      'title_en' => 'Compliant',
      'title_de' => 'Compliant'
    ],
    'unknown' => [
      'color' => 'muted',
      'icon' => 'ph-question',
      'title_en' => 'Unknown',
      'title_de' => 'Unbekannt'
    ],
  ];

  public static function compute(array $project, ?array $nagoya = null): array
  {
    $n = $nagoya ?? ($project['nagoya'] ?? []);
    $enabled   = !empty($n['enabled']);
    $countries = $n['countries'] ?? [];

    if (!$enabled) return self::out('not-relevant', ['disabled']);
    if (empty($countries)) return self::out('incomplete', ['no-countries']);

    // 1) Country-Review offen?
    $anyUnknown = false;
    $anyAbsTrue = false;
    $allAbsFalse = true;
    foreach ($countries as $c) {
      $rev = $c['review'] ?? [];
      $party = $rev['nagoyaParty']    ?? 'unknown';
      $own   = $rev['ownABSMeasures'] ?? 'unknown';
      // if one is yes -> abs=true; if both no -> abs=false; else unknown
      if ($party === 'yes' || $own === 'yes') {
        $anyAbsTrue = true;
        $allAbsFalse = false;
      } elseif ($party === 'no' && $own === 'no') {
        // abs = false
      } else {
        $anyUnknown = true;
        $allAbsFalse = false;
      }
    }
    if ($anyUnknown) return self::out('abs-review', ['open-country-review']);
    if ($allAbsFalse) return self::out('not-relevant', ['all-countries-no-abs']);



    // 3) Noch keine A/B/C-Setzung:
    $scopeComplete  = self::scopeComplete($nagoya);
    $scopeSubmitted = !empty($nagoya['scopeSubmitted']);
    if (!$scopeComplete) {
      // Scope noch unvollständig → Forschende dran
      return self::out('researcher-input', ['scope-incomplete']);
    }
    // Scope ist vollständig, aber noch NICHT eingereicht
    if ($scopeComplete && !$scopeSubmitted) {
      return self::out('researcher-input', ['scope-complete-not-submitted']);
    }

    $label = self::deriveProjectLabel($n);
    // Scope vollständig + eingereicht + A/B/C gesetzt
    if ($scopeComplete && $scopeSubmitted && $label !== null) {
      // A/B → Genehmigungen prüfen
      if ($label === 'C') return self::out('out-of-scope', ['label-C']);
      if ($label === 'A' || $label === 'B') {
        $pending = self::permitsPending($n);
        return $pending ? self::out('permits-pending', ['permits-open'])
          : self::out('compliant', ['permits-complete']);
      }
    }

    // Scope vollständig + eingereicht, aber noch kein A/B/C
    if ($scopeComplete && $scopeSubmitted && $label === null) {
      return self::out('awaiting-abs-evaluation', ['scope-submitted-await-eval']);
    }

    return self::out('researcher-input', ['scope-incomplete']);
  }

  public static function scopeComplete(array $nagoya): bool
  {
    // Erwartete Struktur in Zukunft:
    // $country['scope']['geo'|'temporal'|'material'|'utilization'|'aTK']
    if (empty($nagoya['countries'] ?? [])) return false;
    foreach ($nagoya['countries'] ?? [] as $c) {
      if (!($c['abs'] ?? false)) continue;
      $s = $c['scope'] ?? [];
      foreach ($s['groups'] ?? [] as $g) {
        $g = DB::doc2Arr($g);
        if (!self::scopeGroupComplete($g)) {
          return false;
        }
      }
    }
    return true;
  }
  private static function scopeGroupComplete(array $group): bool
  {
    // Check required fields
    foreach (['geo', 'material', 'utilization'] as $f) {
      if (empty($group[$f] ?? null)) {
        return false;
      }
    }
    if (empty($group['temporal']) && !($group['temporal_ongoing'] ?? false)) {
      return false;
    }
    return true;
  }

  private static function deriveProjectLabel(array $nagoya): ?string
  {
    $hasAbs = false;
    $hasA   = false;
    $hasB   = false;

    if (empty($nagoya['countries'] ?? [])) {
      return null;
    }

    foreach ($nagoya['countries'] ?? [] as $c) {
      if (!($c['abs'] ?? false)) continue;
      $hasAbs = true;
      if (empty($c['scope'] ?? []) || empty($c['scope']['groups'] ?? [])) return null;
      $label = $c['evaluation']['label'] ?? null;
      if (!in_array($label, ['A', 'B', 'C'], true)) {
        return null;
      }
      if ($label === 'A') $hasA = true;
      if ($label === 'B') $hasB = true;
    }
    if (!$hasAbs) {
      return null; // oder 'not-relevant', je nach deiner Logik
    }
    if ($hasA) return 'A';
    if ($hasB) return 'B';
    return 'C'; // es gibt ABS-Länder, aber nur C oder null -> C
  }

  public static function whoIsNext(array $project): string
  {
    $status = $project['nagoya']['status'] ?? '';
    switch ($status) {
      case 'abs-review':
      case 'awaiting-abs-evaluation':
        return 'abs-team';
      case 'researcher-input':
        // Wenn bewilligt, Hinweis „verpflichtend“
        return ($project['status'] ?? null) === 'approved'
          ? 'researcher-required'
          : 'researcher';
      case 'permits-pending':
        return 'both-permits';
      default:
        return 'none';
    }
  }

  /**
   * Write-through projection: normalize nagoya doc, compute status,
   * set projections for fast querying, and stamp metadata.
   */
  public static function writeThrough(array $project, array $nagoya, ?string $userId = null): array
  {
    // Normalize countries: ensure per-country ABS boolean is set consistently
    if (!empty($nagoya['countries']) && is_array($nagoya['countries'])) {
      foreach ($nagoya['countries'] as &$c) {
        $rev = $c['review'] ?? [];
        $party = $rev['nagoyaParty']    ?? 'unknown';
        $own   = $rev['ownABSMeasures'] ?? 'unknown';
        if ($party === 'yes' || $own === 'yes') {
          $c['abs'] = true;
        } elseif ($party === 'no' && $own === 'no') {
          $c['abs'] = false;
        } else {
          $c['abs'] = null;
        }
      }
      unset($c);
    }

    // Projections for fast filters
    $nagoya['countryCodes'] = array_values(array_unique(array_filter(array_map(
      fn($c) => $c['code'] ?? null,
      DB::doc2Arr($nagoya['countries'] ?? [])
    ))));

    // Optional permit projections (future-proof; harmless if absent)
    $counts = self::aggregatePermits($nagoya);
    if ($counts) {
      $nagoya['permitCounts'] = $counts;                 // {total, granted, pending}
      $nagoya['hasPermitsPending'] = ($counts['pending'] ?? 0) > 0;
    } else {
      unset($nagoya['permitCounts'], $nagoya['hasPermitsPending']);
    }

    // Compute canonical status and stamp metadata
    $nagoya['label'] = self::deriveProjectLabel($nagoya);
    $calc = self::compute($project, $nagoya);
    $nagoya['status'] = $calc['status'];
    $nagoya['status_reason'] = $calc['reason'];
    $nagoya['status_updated'] = date('Y-m-d');
    if ($userId) $nagoya['status_updated_by'] = $userId;

    return $nagoya;
  }

  /** Returns true if permits are pending across countries; false if all granted. */
  private static function permitsPending(array $nagoya): bool
  {
    $foundAny = false;
    $allGranted = true;
    foreach ($nagoya['countries'] ?? [] as $c) {
      foreach (($c['abs']['permits'] ?? []) as $p) {
        $foundAny = true;
        if (($p['status'] ?? '') !== 'granted') $allGranted = false;
      }
    }
    // If A/B will require permits but none are recorded yet, treat as pending.
    return (!$foundAny) || (!$allGranted);
  }

  /** Aggregate permit counters (optional). */
  private static function aggregatePermits(array $nagoya): array
  {
    $total = 0;
    $granted = 0;
    $pending = 0;
    foreach ($nagoya['countries'] ?? [] as $c) {
      foreach (($c['abs']['permits'] ?? []) as $p) {
        $total++;
        (($p['status'] ?? '') === 'granted') ? $granted++ : $pending++;
      }
    }
    return $total ? ['total' => $total, 'granted' => $granted, 'pending' => $pending] : [];
  }

  /** Helper to pack status result */
  private static function out(string $status, array $reason): array
  {
    return ['status' => $status, 'reason' => $reason];
  }


  public static function icon(array $project): string
  {
    $n = $project['nagoya'] ?? [];

    // If module not enabled → green check (no ABS context)
    if (($n['enabled'] ?? false) === false) {
      return '<i class="ph ph-check text-success" title="' . htmlspecialchars(lang('Not ABS-relevant', 'Nicht ABS-relevant')) . '"></i>';
    }

    $status = $n['status'] ?? null;
    if (!$status) {
      return '<i class="ph ph-question text-muted" title="' . htmlspecialchars(lang('Unknown', 'Unbekannt')) . '"></i>';
    }

    // Map statuses to icons/colors
    $map = self::MAP[$status] ?? self::MAP['unknown'];
    $clr  = $map['color'] ?? 'muted';
    $icon = $map['icon']  ?? 'ph-question';
    return '<i class="ph ' . $icon . ' ' . $clr . '" title="' . htmlspecialchars(self::statusLabel($status)) . '"></i>';
  }

  /** Returns a badge (<span class="badge ...">...</span>) for the given project/nagoya. */
  public static function badge(array $project, bool $large = true): string
  {
    $n = $project['nagoya'] ?? [];

    if (($n['enabled'] ?? false) === false) {
      return self::makeBadge('muted', 'ph-x-circle', lang('not ABS-relevant', 'nicht ABS-relevant'), $large);
    }

    $status = $n['status'] ?? null;
    if (!$status) {
      return self::makeBadge('muted', 'ph-question', lang('Unknown', 'Unbekannt'), $large);
    }

    // Map statuses to badge color + icon
    $map = self::MAP[$status] ?? self::MAP['unknown'];
    $clr  = $map['color'] ?? 'muted';
    $icon = $map['icon']  ?? 'ph-question';
    $label = self::statusLabel($status);
    return self::makeBadge($clr, $icon, $label, $large);
  }

  /** Color of the status (for badges/icons). */
  public static function statusColor(string $status): string
  {
    $map = self::MAP[$status] ?? self::MAP['unknown'];
    return $map['color'] ?? 'muted';
  }

  /** Human-readable label for a status (for titles/tooltips). */
  public static function statusLabel(string $status): string
  {
    $map = self::MAP[$status] ?? self::MAP['unknown'];
    $label_en = $map['title_en'] ?? 'Unknown';
    $label_de = $map['title_de'] ?? 'Unbekannt';
    return lang($label_en, $label_de);
  }

  /**
   * Country-level Nagoya badge.
   * Shows ABS relevance, A/B/C label and a rough permit state.
   */
  public static function countryBadge(array $country): string
  {
    $abs      = $country['abs'] ?? null; // true/false/null
    $eval     = $country['evaluation'] ?? [];
    $label    = $eval['label'] ?? '';  // 'A','B','C' or ''
    if (!is_string($label)) $label = '';
    $permits  = $eval['permits'] ?? [];

    // Determine permit state
    $hasNeeded    = false;
    $hasRequested = false;
    $hasGranted   = false;

    if (is_array($permits)) {
      foreach ($permits as $p) {
        $status = $p['status'] ?? null;
        if ($status === 'needed') {
          $hasNeeded = true;
        } elseif ($status === 'requested') {
          $hasRequested = true;
        } elseif ($status === 'granted') {
          $hasGranted = true;
        }
      }
    }

    // Base classes / text depending on ABS relevance
    $classes = 'badge small ';
    $icon    = 'ph ph-question';
    $text    = lang('ABS unknown', 'ABS unbekannt');

    if ($abs === true) {
      $classes .= 'primary';
      $icon    = 'ph ph-shield-check';
      $text    = lang('ABS-relevant', 'ABS-relevant');
    } elseif ($abs === false) {
      $classes .= 'muted';
      $icon    = 'ph ph-x-circle';
      $text    = lang('Not ABS-relevant', 'Nicht ABS-relevant');
    }

    // A/B/C sublabel
    $labelHtml = self::ABCbadge($label);
    if ($labelHtml !== '') {
      $classes .= ' d-inline-flex align-items-center';
    }

    // Permit state indicator (only for ABS-relevant)
    $permitHtml = '';
    if ($abs === true) {
      if ($hasNeeded || $hasRequested) {
        $permitHtml = sprintf(
          '<small class="badge warning ml-5">%s</small>',
          htmlspecialchars(lang('permits pending', 'Genehmigungen ausstehend'))
        );
      } elseif ($hasGranted) {
        $permitHtml = sprintf(
          '<small class="badge success ml-5">%s</small>',
          htmlspecialchars(lang('permits granted', 'Genehmigungen erteilt'))
        );
      }
    }

    return sprintf(
      '<span class="%s"><i class="%s"></i> %s%s%s</span>',
      $classes,
      $icon,
      htmlspecialchars($text),
      $labelHtml,
      $permitHtml
    );
  }

  public static function permitStatusBadge(string $status): string
  {
    switch ($status) {
      case 'needed':
        return '<span class="badge warning"><i class="ph ph-shield-exclamation"></i> ' .
          htmlspecialchars(lang('Permit needed', 'Genehmigung erforderlich')) . '</span>';
      case 'requested':
        return '<span class="badge signal"><i class="ph ph-hourglass"></i> ' .
          htmlspecialchars(lang('Permit requested', 'Genehmigung beantragt')) . '</span>';
      case 'granted':
        return '<span class="badge success"><i class="ph ph-shield-check"></i> ' .
          htmlspecialchars(lang('Permit granted', 'Genehmigung erteilt')) . '</span>';
      case 'not-applicable':
        return '<span class="badge muted"><i class="ph ph-x-circle"></i> ' .
          htmlspecialchars(lang('Not applicable', 'Nicht zutreffend')) . '</span>';
      default:
        return '<span class="badge muted"><i class="ph ph-question"></i> ' .
          htmlspecialchars(lang('Unknown status', 'Unbekannter Status')) . '</span>';
    }
  }

  public static function ABCbadge(string $label = ''): string
  {
    switch ($label) {
      case 'A':
        return '<small class="badge danger ml-5" data-toggle="tooltip" data-title="' .
          lang('Project within the scope of the EU ABS Regulation', 'Projekt im Geltungsbereich der EU-ABS-Verordnung') . '">A</small>';
      case 'B':
        return '<small class="badge signal ml-5" data-toggle="tooltip" data-title="' .
          lang('Project out of the scope of the EU ABS Regulation but within the scope of ABS measures in provider countries', 'Projekt außerhalb des Geltungsbereichs der EU-ABS-Verordnung, aber innerhalb des Geltungsbereichs der ABS-Maßnahmen in den Herkunftsländern') . '">B</small>';
      case 'C':
        return '<small class="badge muted ml-5" data-toggle="tooltip" data-title="' .
          lang('Project out of the scope of both, the EU ABS regulation and the ABS measures in provider countries', 'Projekt außerhalb des Geltungsbereichs sowohl der EU-ABS-Verordnung als auch der ABS-Maßnahmen in den Herkunftsländern') . '">C</small>';
      default:
        return '';
    }
  }

  /** Small helper to build a badge HTML string. */
  private static function makeBadge(string $color, string $icon, string $label, bool $large): string
  {
    $sizeCls = $large ? ' large' : '';
    return '<span class="badge' . $sizeCls . ' ' . $color . '"><i class="ph ' . $icon . '"></i> ' . htmlspecialchars($label) . '</span>';
  }
}
