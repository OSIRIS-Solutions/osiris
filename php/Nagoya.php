<?php

final class Nagoya
{
  // Canonical status values
  public const S_NOT_RELEVANT   = 'not-relevant';
  public const S_INCOMPLETE     = 'incomplete';
  public const S_AWAIT_REVIEW   = 'awaiting-review';
  public const S_ABS_RELEVANT   = 'abs-relevant';       // Phase 1 result
  public const S_IN_SCOPE_EU    = 'in-scope-eu';        // A
  public const S_IN_SCOPE_NAT   = 'in-scope-national';  // B
  public const S_OUT_OF_SCOPE   = 'out-of-scope';       // C
  public const S_PERMITS_PEND   = 'permits-pending';
  public const S_COMPLIANT      = 'compliant';

  /**
   * Compute canonical status from current nagoya subdoc.
   * This function is the single source of truth for status transitions.
   */
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
      if ($party === 'unknown' && $own === 'unknown') $anyUnknown = true;

      $abs = $c['abs'] ?? ($party === 'yes' || $own === 'yes');
      if ($abs) {
        $anyAbsTrue = true;
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
    $map = [
      'compliant'         => ['ph-check-circle',        'text-success'],
      'permits-pending'   => ['ph-clock-countdown',     'text-signal'],
      'in-scope-eu'       => ['ph-seal-check',          'text-primary'],
      'in-scope-national' => ['ph-seal-check',          'text-primary'],
      'abs-relevant'      => ['ph-warning-circle',      'text-danger'],
      'awaiting-review'   => ['ph-hourglass-medium',    'text-signal'],
      'incomplete'        => ['ph-list-magnifying-glass', 'text-signal'],
      'out-of-scope'      => ['ph-circle',              'text-secondary'],
      'not-relevant'      => ['ph-x-circle',            'text-muted'],
      'abs-review'       => ['ph-users-three',         'text-signal'],
      'awaiting-abs-evaluation' => ['ph-file-search',   'text-signal'],
      'researcher-input' => ['ph-user-gear',           'text-signal'],
      'researcher-required' => ['ph-user-gear',         'text-danger'],
      'both-permits'     => ['ph-handshake',           'text-signal'],
    ];

    [$icon, $cls] = $map[$status] ?? ['ph-question', 'text-muted'];
    return '<i class="ph ' . $icon . ' ' . $cls . '" title="' . htmlspecialchars(self::statusLabel($status)) . '"></i>';
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
    $map = [
      'compliant'         => ['success',  'ph-check-circle',        lang('Compliant', 'Compliant')],
      'permits-pending'   => ['signal',   'ph-clock-countdown',     lang('Permits pending', 'Genehmigungen ausstehend')],
      'in-scope-eu'       => ['primary',  'ph-seal-check',          lang('In scope (EU)', 'Im Geltungsbereich (EU)')],
      'in-scope-national' => ['primary',  'ph-seal-check',          lang('In scope (national)', 'Im Geltungsbereich (national)')],
      'abs-relevant'      => ['danger',   'ph-warning-circle',      lang('ABS-relevant', 'ABS-relevant')],
      'awaiting-review'   => ['signal',   'ph-hourglass-medium',    lang('Awaiting review', 'Wartet auf Prüfung')],
      'incomplete'        => ['signal',   'ph-list-magnifying-glass', lang('Incomplete', 'Unvollständig')],
      'out-of-scope'      => ['secondary', 'ph-circle',              lang('Out of scope', 'Außerhalb des Geltungsbereichs')],
      'not-relevant'      => ['muted',    'ph-x-circle',            lang('not ABS-relevant', 'nicht ABS-relevant')],
      'abs-review'       => ['signal',  'ph-users-three',         lang('Country review pending', 'Länderbewertung ausstehend')],
      'awaiting-abs-evaluation' => ['signal', 'ph-file-search',   lang('Awaiting ABS evaluation', 'Wartet auf ABS-Bewertung')],
      'researcher-input' => ['signal', 'ph-user-gear',           lang('Researcher input required', 'Eingabe durch Forschende erforderlich')],
      'researcher-required' => ['danger', 'ph-user-gear',         lang('Researcher input required', 'Eingabe durch Forschende erforderlich')],
      'both-permits'     => ['signal',  'ph-handshake',           lang('Permits pending', 'Genehmigungen ausstehend')],
    ];

    [$clr, $icon, $label] = $map[$status] ?? ['muted', 'ph-question', lang('Unknown', 'Unbekannt')];
    return self::makeBadge($clr, $icon, $label, $large);
  }

  /** Human-readable label for a status (for titles/tooltips). */
  public static function statusLabel(string $status): string
  {
    $labels = [
      'compliant'         => lang('Compliant', 'Compliant'),
      'permits-pending'   => lang('Permits pending', 'Genehmigungen ausstehend'),
      'in-scope-eu'       => lang('In scope (EU)', 'Im Geltungsbereich (EU)'),
      'in-scope-national' => lang('In scope (national)', 'Im Geltungsbereich (national)'),
      'abs-relevant'      => lang('ABS-relevant', 'ABS-relevant'),
      'awaiting-review'   => lang('Awaiting review', 'Wartet auf Prüfung'),
      'incomplete'        => lang('Incomplete', 'Unvollständig'),
      'out-of-scope'      => lang('Out of scope', 'Außerhalb des Geltungsbereichs'),
      'not-relevant'      => lang('not ABS-relevant', 'nicht ABS-relevant'),
      'abs-review'       => lang('Country review pending', 'Länderbewertung ausstehend'),
      'awaiting-abs-evaluation' => lang('Awaiting ABS evaluation', 'Wartet auf ABS-Bewertung'),
      'researcher-input' => lang('Researcher input required', 'Eingabe durch Forschende erforderlich'),
      'researcher-required' => lang('Researcher input required', 'Eingabe durch Forschende erforderlich'),
      'both-permits'     => lang('Permits pending', 'Genehmigungen ausstehend'),
    ];
    return $labels[$status] ?? lang('Unknown', 'Unbekannt');
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
