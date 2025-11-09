<div class="container" style="max-width:1000px;">
  <h1>Nagoya Compliance – Phase 1: Länder & Zeiträume</h1>
  <p class="text-muted">Projekt: <?= htmlspecialchars($project['title'] ?? ('#'.$project['_id'])) ?></p>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="">

    <div class="card mb-3">
      <div class="card-body">
        <label class="mb-2">
          <input type="checkbox" name="enabled" value="1"
            <?= !empty($project['nagoya']['enabled'])?'checked':''; ?>>
          Dieses Projekt collect / obtain / utilize genetische Ressourcen außerhalb Deutschlands.
        </label>
        <p class="small text-muted mb-0">
          Hinweis: „Provider country“ = Land der ursprünglichen Feldsammlung (nicht Lagerort/ Sammlung).
        </p>
      </div>
    </div>

    <div id="entriesWrap" class="<?= empty($project['nagoya']['enabled'])?'d-none':'' ?>">
      <div class="d-flex align-items-center mb-2">
        <h5 class="mb-0">Einträge</h5>
        <button type="button" id="addRow" class="btn btn-sm btn-outline-primary ml-3">+ Zeile</button>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle" id="entriesTable">
          <thead class="thead-light">
            <tr>
              <th style="width:28%">Land</th>
              <th style="width:16%">Von</th>
              <th style="width:16%">Bis</th>
              <th style="width:18%">Quelle</th>
              <th style="width:16%">Ungefähr</th>
              <th style="width:6%"></th>
            </tr>
          </thead>
          <tbody>
          <?php
            $rows = $project['nagoya']['phase1']['entries'] ?? [];
            if (empty($rows)) $rows = [[]]; // one empty row
            foreach ($rows as $row):
          ?>
            <tr>
              <td>
                <select class="form-control" name="country[]">
                  <option value="">– choose country –</option>
                  <?php foreach($countries as $cc=>$name): ?>
                    <option value="<?= $cc ?>" <?= (isset($row['country']) && $row['country']===$cc)?'selected':'' ?>>
                      <?= htmlspecialchars($name) ?> (<?= $cc ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="date" class="form-control" name="from[]" value="<?= htmlspecialchars($row['period']['from'] ?? '') ?>"></td>
              <td><input type="date" class="form-control" name="to[]" value="<?= htmlspecialchars($row['period']['to'] ?? '') ?>"></td>
              <td>
                <select class="form-control" name="source[]">
                  <option value="">– select –</option>
                  <option value="already" <?= (($row['source'] ?? '')==='already')?'selected':'' ?>>already collected</option>
                  <option value="planned" <?= (($row['source'] ?? '')==='planned')?'selected':'' ?>>planned collection</option>
                </select>
              </td>
              <td class="text-center">
                <label class="mb-0">
                  <input type="checkbox" name="approx[]" value="1" <?= !empty($row['period']['approx'])?'checked':''; ?>>
                  Zeitraum ungenau
                </label>
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger removeRow" title="Remove">&times;</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <p class="small text-muted">
        *Wenn „Zeitraum ungenau“ aktiv ist, dürfen Datumsfelder leer sein (werden dann später vom ABS-Team im Kontext bewertet).
      </p>
    </div>

    <div class="d-flex mt-3">
      <a href="/projects/<?= $project['_id'] ?>#tab-nagoya" class="btn btn-light">Abbrechen</a>
      <button class="btn btn-primary ml-auto" type="submit">Speichern</button>
    </div>
  </form>
</div>

<script>
// Minimal JS: toggle block + add/remove rows
const entriesWrap = document.getElementById('entriesWrap');
document.querySelector('input[name="enabled"]').addEventListener('change', e=>{
  entriesWrap.classList.toggle('d-none', !e.target.checked);
});

// Row template (cloned from first row)
document.getElementById('addRow').addEventListener('click', ()=>{
  const tbody = document.querySelector('#entriesTable tbody');
  const tmpl  = tbody.rows[0].cloneNode(true);
  // clear inputs
  tmpl.querySelectorAll('select,input').forEach(el=>{
    if (el.tagName==='SELECT') el.selectedIndex = 0;
    if (el.type==='date') el.value = '';
    if (el.type==='checkbox') el.checked = false;
  });
  tbody.appendChild(tmpl);
});

document.querySelector('#entriesTable').addEventListener('click', (e)=>{
  if (e.target.classList.contains('removeRow')) {
    const tbody = document.querySelector('#entriesTable tbody');
    if (tbody.rows.length > 1) e.target.closest('tr').remove();
  }
});
</script>