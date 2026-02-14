(function ($) {

  const API_BASE = '/api/command-palette';
  const API_SEARCH = '/api/command-palette/search';
  const MIN_CHARS = 3;
  const DEBOUNCE_MS = 260;

  const state = {
    isOpen: false,

    // Full static payload from /api/command-palette
    staticData: null,

    // Full dynamic payload from /api/command-palette/search?q=
    dynamicData: null,

    // The merged groups currently rendered
    renderedGroups: [],

    // Flat list of currently visible items (for keyboard navigation)
    visibleItems: [],
    selectedIndex: 0,

    // Used to ignore stale search responses
    searchSeq: 0
  };

  // const $cp       = $('.cp');
  // const $backdrop = $('.cp-backdrop');
  // const $panel    = $('.cp-panel');
  // const $input    = $('.cp-input');
  // const $results  = $('.cp-body');

  const $cp = $('.cp');
  const $backdrop = $('.cp-backdrop');
  const $panel = $('.cp-panel');
  const $input = $('#osCpInput');
  const $results = $('#osCpResults');

  if (!$cp.length) return;

  /* ----------------------------
     Reusable helpers
  ---------------------------- */

  function normalize(str) {
    return (str || '').toString().toLowerCase().trim();
  }

  function escapeHtml(str) {
    return String(str || '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  /* Simple debounce with cancel support (no lodash needed). */
  function debounce(fn, wait) {
    let t = null;

    function debounced(...args) {
      if (t) clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    }

    debounced.cancel = function () {
      if (t) clearTimeout(t);
      t = null;
    };

    return debounced;
  }

  function buildSearchText(item) {
    return normalize(
      (item.label || '') + ' ' +
      (item.keywords ? item.keywords.join(' ') : '')
    );
  }

  /* ----------------------------
     Open / Close
  ---------------------------- */

  function openPalette() {
    if (state.isOpen) return;

    state.isOpen = true;
    $cp.addClass('is-open');

    // Load static data fresh on open (can be cached later if you want)
    fetchStatic().then(() => {
      state.dynamicData = null;
      applyFilterAndRender($input.val() || '');
      requestAnimationFrame(() => {
        $input.val('').trigger('focus');
      });
    });
  }

  function closePalette() {
    if (!state.isOpen) return;

    state.isOpen = false;
    $cp.removeClass('is-open');

    // Optional: clear state on close
    // state.dynamicData = null;
    // $input.val('');
  }

  function togglePalette() {
    state.isOpen ? closePalette() : openPalette();
  }

  /* ----------------------------
     API
  ---------------------------- */

  function fetchStatic() {
    return $.getJSON(API_BASE)
      .then(data => {
        state.staticData = data || { groups: [] };
      })
      .catch(() => {
        state.staticData = { groups: [] };
      });
  }

  function fetchDynamic(q, seq) {
    const url = API_SEARCH + '?q=' + encodeURIComponent(q);

    return $.getJSON(url)
      .then(data => {
        // Ignore stale responses
        if (seq !== state.searchSeq) return;

        state.dynamicData = data || { groups: [] };
        applyFilterAndRender(q);
      })
      .catch(() => {
        if (seq !== state.searchSeq) return;
        state.dynamicData = { groups: [] };
        applyFilterAndRender(q);
      });
  }

  /* ----------------------------
     Filtering + merging
  ---------------------------- */

  function filterGroups(payload, q) {
    const query = normalize(q);

    if (!payload || !payload.groups) return [];

    return payload.groups
      .map(group => {
        const items = (group.items || [])
          .filter(item => {
            if (!query) return true;
            return buildSearchText(item).includes(query);
          })
          .sort((a, b) => (b.priority || 0) - (a.priority || 0));

        return { ...group, items };
      })
      .filter(g => g.items.length > 0);
  }

  function mergeGroups(staticGroups, dynamicGroups) {
    // Keep static groups first, then dynamic groups below.
    // If you ever want to merge same-group ids, we can extend this.
    return [...staticGroups, ...dynamicGroups];
  }

  function applyFilterAndRender(q) {
    const query = q || '';
    console.log(state.dynamicData);

    const staticFiltered = filterGroups(state.staticData, query);

    // Dynamic results should only be shown if query length >= MIN_CHARS
    const showDynamic = normalize(query).length >= MIN_CHARS;
    // const dynamicFiltered = showDynamic ? filterGroups(state.dynamicData, query) : [];
    // dynamic data is already filtered server-side, so no need to filter again client-side
    const dynamicFiltered = showDynamic ? (state.dynamicData ? state.dynamicData.groups || [] : []) : [];

    const merged = mergeGroups(staticFiltered, dynamicFiltered);

    // If query is long enough but dynamic is not loaded yet, show a small loading group
    const needsDynamic = showDynamic && state.dynamicData === null;
    const loadingGroup = needsDynamic ? [{
      id: 'loading',
      label: '',
      items: [{
        id: 'loading',
        type: 'info',
        label: 'Searching…',
        icon: 'spinner-gap',
        url: null,
        priority: -9999
      }]
    }] : [];

    state.renderedGroups = needsDynamic ? mergeGroups(staticFiltered, loadingGroup) : merged;

    renderGroups(state.renderedGroups);
  }

  /* ----------------------------
     Rendering
  ---------------------------- */

  function renderGroups(groups) {
    state.visibleItems = [];
    state.selectedIndex = 0;

    if (!groups.length) {
      $results.html(`
        <div class="cp-group">
          <div class="cp-groupTitle">No results</div>
        </div>
      `);
      return;
    }

    let html = '';
    let globalIndex = 0;

    groups.forEach(group => {
      html += `<div class="cp-group">`;

      if (group.label) {
        html += `<div class="cp-groupTitle">${escapeHtml(group.label)}</div>`;
      }

      html += `<ul class="cp-list">`;

      (group.items || []).forEach(item => {
        state.visibleItems.push(item);

        const selected = globalIndex === state.selectedIndex ? ' is-selected' : '';
        const typeClass = item.type ? ` cp-item--${escapeHtml(item.type)}` : '';
        const disabled = (!item.url || item.type === 'info') ? ' is-disabled' : '';

        html += `
          <li class="cp-item${typeClass}${selected}${disabled}" data-index="${globalIndex}">
            <span class="cp-itemIcon">
              <i class="ph ph-${escapeHtml(item.icon || 'circle')}"></i>
            </span>
            <span class="cp-itemMain">
              <span class="cp-itemTitle">${escapeHtml(item.label)}</span>
            </span>
            <span class="cp-tag">${(item.type || '')}</span>
          </li>
        `;

        globalIndex++;
      });

      html += `</ul></div>`;
    });

    $results.html(html);
  }

  /* ----------------------------
     Selection + navigation
  ---------------------------- */

  function moveSelection(delta) {
    const max = state.visibleItems.length - 1;
    if (max < 0) return;

    let next = state.selectedIndex + delta;
    if (next < 0) next = 0;
    if (next > max) next = max;

    state.selectedIndex = next;

    $results.find('.cp-item').removeClass('is-selected');
    $results.find(`[data-index="${next}"]`).addClass('is-selected');

    scrollIntoView();
  }

  function scrollIntoView() {
    const $selected = $results.find(`[data-index="${state.selectedIndex}"]`);
    if (!$selected.length) return;

    const el = $selected.get(0);
    const container = $results.get(0);

    const elTop = el.offsetTop;
    const elBottom = elTop + el.offsetHeight;

    if (elTop < container.scrollTop) {
      container.scrollTop = elTop - 8;
    } else if (elBottom > container.scrollTop + container.clientHeight) {
      container.scrollTop = elBottom - container.clientHeight + 8;
    }
  }

  function activateSelected() {
    const item = state.visibleItems[state.selectedIndex];
    if (!item || !item.url) return;

    window.location.href = item.url;
  }

  /* ----------------------------
     Debounced search trigger
  ---------------------------- */

  const triggerDynamicSearch = debounce(function (q) {
    const query = normalize(q);

    if (query.length < MIN_CHARS) {
      // Reset dynamic results if below threshold
      state.dynamicData = null;
      applyFilterAndRender(q);
      return;
    }

    // Mark as "loading"
    state.dynamicData = null;
    applyFilterAndRender(q);

    // Increment seq to invalidate older requests
    state.searchSeq++;
    const mySeq = state.searchSeq;

    fetchDynamic(query, mySeq);
  }, DEBOUNCE_MS);

  /* ----------------------------
     Events
  ---------------------------- */
document.addEventListener('osiris:command-palette:open', function(){
  openPalette();
});

  document.addEventListener('keydown', function (e) {
    const key = e.key.toLowerCase();

    if ((e.ctrlKey || e.metaKey) && key === 'k') {
      e.preventDefault();
      togglePalette();
      return;
    }

    if (!state.isOpen) return;

    if (key === 'escape') {
      e.preventDefault();
      closePalette();
      return;
    }

    if (key === 'arrowdown') {
      e.preventDefault();
      moveSelection(1);
      return;
    }

    if (key === 'arrowup') {
      e.preventDefault();
      moveSelection(-1);
      return;
    }

    if (key === 'enter') {
      e.preventDefault();
      activateSelected();
      return;
    }
  });

  $input.on('input', function () {
    const q = $(this).val();

    // Always apply local filter instantly (static + whatever dynamic we have)
    applyFilterAndRender(q);

    // Then decide whether we should fetch dynamic results
    triggerDynamicSearch(q);
  });

  $results.on('click', '.cp-item', function () {
    const index = parseInt($(this).attr('data-index'), 10);
    if (!Number.isFinite(index)) return;

    state.selectedIndex = index;
    activateSelected();
  });

  $backdrop.on('click', closePalette);
  $panel.on('click', function (e) { e.stopPropagation(); });

})(jQuery);