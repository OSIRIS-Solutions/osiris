document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-initiative-page]');
    if (!page) return;
    const tabs = Array.from(page.querySelectorAll('[data-initiative-tab]'));
    const sections = Array.from(page.querySelectorAll('[data-initiative-section]'));
    if (!tabs.length) return;
    function showSection() {
        const hash = window.location.hash.slice(1).replace(/^contents-/, '');
        const target = tabs.find(tab => tab.dataset.initiativeTab === hash)
            || tabs.find(tab => tab.classList.contains('active')) || tabs[0];
        tabs.forEach(tab => {
            const active = tab === target;
            tab.classList.toggle('active', active);
            if (active) tab.setAttribute('aria-current', 'page');
            else tab.removeAttribute('aria-current');
        });
        sections.forEach(section => {
            section.hidden = section.dataset.initiativeSection !== target.dataset.initiativeTab;
            if (!section.hidden && window.jQuery?.fn.DataTable) {
                section.querySelectorAll('.initiative-content-table').forEach(table => {
                    if (!jQuery.fn.DataTable.isDataTable(table)) jQuery(table).DataTable({ order: [] });
                    else jQuery(table).DataTable().columns.adjust().responsive?.recalc();
                });
            }
        });
    }
    window.addEventListener('hashchange', showSection);
    showSection();
});
