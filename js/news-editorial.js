(function () {
    const originalElement = document.getElementById('news-editorial-original');
    const original = originalElement ? JSON.parse(originalElement.textContent) : null;
    const section = document.getElementById('news-editorial');
    if (!section) return;
    const forms = [];
    let submitting = false;
    let pendingSubmission = null;
    let discardConfirmed = false;
    const warning = document.getElementById('news-unsaved-warning');
    const values = form => new URLSearchParams(new FormData(form)).toString();
    function updateDirty() {
        section.querySelectorAll('.news-outlet-card').forEach(card => {
            const badge = card.querySelector('[data-news-unsaved]');
            const dirty = forms.some(item => item.form.closest('.news-outlet-card') === card && values(item.form) !== item.initial);
            if (badge) badge.hidden = !dirty;
        });
    }
    document.querySelectorAll('#news-editorial form').forEach(function (form) {
        const editors = [];
        const contentEditors = new Map();
        form.querySelectorAll('[data-news-content-editor]').forEach(function (element) {
            if (typeof Quill === 'undefined') return;
            const input = document.getElementById(element.dataset.input);
            element.hidden = false;
            const quill = new Quill(element, {
                theme: 'snow',
                bounds: element,
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link', 'clean']] },
                formats: ['bold', 'italic', 'underline', 'list', 'link']
            });
            quill.root.setAttribute('role', 'textbox');
            quill.root.setAttribute('aria-multiline', 'true');
            quill.root.setAttribute('aria-labelledby', input.id + '-label');
            input.hidden = true;
            input.classList.add('d-none');
            document.getElementById(input.id + '-label').addEventListener('click', function () { quill.focus(); });
            const sync = function () {
                input.value = quill.getText().replace(/[\s\u00a0\u200b]/g, '') === '' ? '' : quill.getSemanticHTML();
            };
            sync();
            quill.on('text-change', function () { sync(); updateDirty(); });
            editors.push(sync);
            contentEditors.set(input.id, { quill: quill, sync: sync });
        });
        const copyButton = form.querySelector('[data-news-copy-original]');
        if (copyButton && original) copyButton.addEventListener('click', function () {
            form.querySelectorAll('[data-news-original-field]').forEach(function (input) {
                const value = original[input.dataset.newsOriginalField][input.dataset.newsLanguage] || '';
                const editor = contentEditors.get(input.id);
                if (editor) {
                    editor.quill.history.cutoff();
                    editor.quill.setContents(editor.quill.clipboard.convert({ html: value }), 'user');
                    editor.quill.history.cutoff();
                    editor.sync();
                } else {
                    input.value = value;
                }
            });
            const format = form.querySelector('input[name="editorial[format]"]');
            if (format) format.value = 'html';
            updateDirty();
        });
        if (editors.length) {
            form.querySelector('input[name="editorial[format]"]').value = 'html';
            form.addEventListener('submit', function () { editors.forEach(function (sync) { sync(); }); });
        }
        forms.push({ form: form, initial: values(form) });
        form.addEventListener('input', updateDirty);
        form.addEventListener('change', updateDirty);
        const tabs = Array.from(form.querySelectorAll('[data-news-language-tab]'));
        if (tabs.length) {
            const panels = form.querySelectorAll('[data-news-language-panel]');
            const selectLanguage = function (tab, focus) {
                tabs.forEach(button => {
                    const selected = button === tab;
                    button.setAttribute('aria-selected', String(selected));
                    button.tabIndex = selected ? 0 : -1;
                });
                panels.forEach(panel => { panel.hidden = panel.dataset.newsLanguagePanel !== tab.dataset.newsLanguageTab; });
                if (focus) tab.focus();
            };
            form.querySelector('[role="tablist"]').hidden = false;
            form.querySelectorAll('[data-news-language-heading]').forEach(heading => { heading.hidden = true; });
            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => selectLanguage(tab, false));
                tab.addEventListener('keydown', event => {
                    const target = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : event.key === 'ArrowRight' ? (index + 1) % tabs.length : event.key === 'ArrowLeft' ? (index + tabs.length - 1) % tabs.length : null;
                    if (target === null) return;
                    event.preventDefault();
                    selectLanguage(tabs[target], true);
                });
            });
            selectLanguage(tabs[0], false);
        }
    });
    section.addEventListener('submit', function (event) {
        const otherChanges = forms.some(item => item.form !== event.target && values(item.form) !== item.initial);
        if (otherChanges && !discardConfirmed) {
            event.preventDefault();
            pendingSubmission = { form: event.target, submitter: event.submitter };
            warning.hidden = false;
            warning.scrollIntoView({ block: 'center' });
            warning.querySelector('[data-news-keep-editing]').focus({ preventScroll: true });
            return;
        }
        submitting = true;
    });
    warning.querySelector('[data-news-keep-editing]').addEventListener('click', function () {
        warning.hidden = true;
        const submitter = pendingSubmission && pendingSubmission.submitter;
        pendingSubmission = null;
        if (submitter) submitter.focus();
    });
    warning.querySelector('[data-news-discard-changes]').addEventListener('click', function () {
        if (!pendingSubmission) return;
        const pending = pendingSubmission;
        pendingSubmission = null;
        discardConfirmed = true;
        pending.form.requestSubmit(pending.submitter || undefined);
        discardConfirmed = false;
    });
    window.addEventListener('beforeunload', function (event) {
        if (!submitting && forms.some(item => values(item.form) !== item.initial)) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
