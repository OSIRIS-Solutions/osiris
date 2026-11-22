(() => {
    const section = document.getElementById('news-outlet-requests');
    if (!section) return;
    const internalOnly = document.getElementById('news-internal-only');
    const changed = document.getElementById('news-publication-choice-changed');
    const outlets = section.querySelectorAll('input[name="news[outlets][]"]');
    section.addEventListener('change', event => {
        if (event.target === internalOnly) {
            if (internalOnly.checked) outlets.forEach(outlet => { if (!outlet.disabled) outlet.checked = false; });
        } else if (event.target.matches('input[name="news[outlets][]"]')) {
            if (event.target.checked && internalOnly) internalOnly.checked = false;
        } else {
            return;
        }
        changed.value = '1';
    });
})();
