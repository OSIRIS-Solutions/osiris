<?php

final class Nagoya
{
  private const MAP = [
    'compliant' => [
      'color' => 'success',
      'icon' => 'ph-check-circle',
      'title_en' => 'Compliant', 
      'title_de' => 'Compliant'
    ],
    'permits-pending' => [
      'color' => 'signal',
      'icon' => 'ph-clock-countdown',
      'title_en' => 'Permits pending', 
      'title_de' => 'Genehmigungen ausstehend'
    ],
    'in-scope-eu' => [
      'color' => 'primary',
      'icon' => 'ph-seal-check',
      'title_en' => 'In scope (EU)', 
      'title_de' => 'Im Geltungsbereich (EU)'
    ],
    'in-scope-national' => [
      'color' => 'primary',
      'icon' => 'ph-seal-check',
      'title_en' => 'In scope (national)', 
      'title_de' => 'Im Geltungsbereich (national)'
    ],
    'abs-relevant' => [
      'color' => 'danger',
      'icon' => 'ph-warning-circle',
      'title_en' => 'ABS-relevant', 
      'title_de' => 'ABS-relevant'
    ],
    'awaiting-review' => [
      'color' => 'signal',
      'icon' => 'ph-hourglass-medium',
      'title_en' => 'Awaiting review', 
      'title_de' => 'Wartet auf Prüfung'
    ],
    'incomplete' => [
      'color' => 'signal',
      'icon' => 'ph-list-magnifying-glass',
      'title_en' => 'Incomplete', 
      'title_de' => 'Unvollständig'
    ],
    'out-of-scope' => [
      'color' => 'secondary',
      'icon' => 'ph-circle',
      'title_en' => 'Out of scope', 
      'title_de' => 'Außerhalb des Geltungsbereichs'
    ],
    'not-relevant' => [
      'color' => 'muted',
      'icon' => 'ph-x-circle',
      'title_en' => 'not ABS-relevant', 
      'title_de' => 'nicht ABS-relevant'
    ],
    'abs-review' => [
      'color' => 'signal',
      'icon' => 'ph-users-three',
      'title_en' => 'Country review pending', 
      'title_de' => 'Länderbewertung ausstehend'
    ],
    'awaiting-abs-evaluation' => [
      'color' => 'signal',
      'icon' => 'ph-file-search',
      'title_en' => 'Awaiting ABS evaluation', 
      'title_de' => 'Wartet auf ABS-Bewertung'
    ],
    'researcher-input' => [
      'color' => 'signal',
      'icon' => 'ph-user-gear',
      'title_en' => 'Researcher input required', 
      'title_de' => 'Eingabe durch Forschende erforderlich'
    ],
    'researcher-required' => [
      'color' => 'danger',
      'icon' => 'ph-user-gear',
      'title_en' => 'Researcher input required', 
      'title_de' => 'Eingabe durch Forschende erforderlich'
    ],
    'both-permits' => [
      'color' => 'signal',
      'icon' => 'ph-handshake',
      'title_en' => 'Permits pending', 
      'title_de' => 'Genehmigungen ausstehend'
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
    $label     = $n['label'] ?? null; // 'A'|'B'|'C' wenn ABS-Evaluation erfolgt

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

    // 2) Scope-Aggregat (für alle Länder mit abs=true)
    $scopeOk = self::scopeComplete($n); // siehe Helper unten

    // 3) Falls A/B/C bereits gesetzt → Permits/Compliant-Logik
    if ($label === 'C') return self::out('out-of-scope', ['label-C']);
    if ($label === 'A' || $label === 'B') {
      $pending = self::permitsPending($n);
      return $pending ? self::out('permits-pending', ['permits-open'])
        : self::out('compliant', ['permits-complete']);
    }

    // 4) Noch keine A/B/C-Setzung:
    if (!$scopeOk) return self::out('researcher-input', ['scope-incomplete']);
    return self::out('awaiting-abs-evaluation', ['scope-complete-await-eval']);
  }

  private static function scopeComplete(array $nagoya): bool
  {
    // Erwartete Struktur in Zukunft:
    // $country['scope']['geo'|'temporal'|'material'|'utilization'|'aTK']
    foreach ($nagoya['countries'] ?? [] as $c) {
      if (!($c['abs'] ?? false)) continue;
      $s = $c['scope'] ?? [];
      $ok = !empty($s['geo']) && !empty($s['temporal']) && !empty($s['material']) && !empty($s['utilization']);
      if (!$ok) return false;
    }
    return true;
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
      $nagoya['countries'] ?? []
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

  public static function countryBadge(array $country, bool $large = false): string
  {
    $abs = $country['abs'] ?? null;
    if ($abs === true) {
      return self::makeBadge('success', 'ph-check-circle', lang('ABS-relevant', 'ABS-relevant'), $large);
    } elseif ($abs === false) {
      return self::makeBadge('danger', 'ph-x-circle', lang('Not relevant', 'Nicht relevant'), $large);
    } else {
      return self::makeBadge('muted', 'ph-question', lang('Unknown', 'Unbekannt'), $large);
    }
  }

  /** Small helper to build a badge HTML string. */
  private static function makeBadge(string $color, string $icon, string $label, bool $large): string
  {
    $sizeCls = $large ? ' large' : '';
    return '<span class="badge' . $sizeCls . ' ' . $color . '"><i class="ph ' . $icon . '"></i> ' . htmlspecialchars($label) . '</span>';
  }
}
