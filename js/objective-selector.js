/* Compact, progressively enhanced objective selection. The checkboxes remain the form values. */
(() => {
    document.querySelectorAll('[data-objective-selector]').forEach(widget => {
        if (widget.dataset.objectiveInitialized) return;
        widget.dataset.objectiveInitialized = 'true';
        const picker = widget.querySelector('[data-objective-picker]');
        const trigger = widget.querySelector('[data-objective-toggle]');
        const panel = widget.querySelector('[data-objective-panel]');
        const search = widget.querySelector('[data-objective-search]');
        const selected = widget.querySelector('[data-objective-selected]');
        const emptySelection = widget.querySelector('[data-objective-selection-empty]');
        const count = widget.querySelector('[data-objective-count]');
        const results = widget.querySelector('[data-objective-results]');
        const announcement = widget.querySelector('[data-objective-announcement]');
        const options = [...widget.querySelectorAll('[data-selection-option]')].map(label => ({label, input: label.querySelector('input')}));
        const form = widget.closest('form');
        const text = (key, name, value) => widget.dataset[key].replaceAll(`{{${name}}}`, String(value));
        const announce = (key, name) => { announcement.textContent = text(key, 'name', name); };
        const visibleInputs = () => options.filter(option => !option.label.hidden).map(option => option.input);
        const close = (restoreFocus = false) => {
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) trigger.focus();
        };
        const positionPanel = () => {
            panel.classList.remove('opens-up');
            panel.style.removeProperty('--objective-panel-space');
            const rect = trigger.getBoundingClientRect();
            const below = window.innerHeight - rect.bottom - 12;
            const above = rect.top - 12;
            const upwards = panel.offsetHeight > below && above > below;
            panel.classList.toggle('opens-up', upwards);
            panel.style.setProperty('--objective-panel-space', `${Math.max(120, Math.min(window.innerHeight - 24, upwards ? above : below))}px`);
        };
        const open = () => {
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            positionPanel();
            search.focus();
        };
        const refresh = () => {
            selected.replaceChildren();
            const checked = options.filter(option => option.input.checked);
            checked.forEach(({label, input}) => {
                const card = document.createElement('li');
                card.className = 'objective-selected-card';
                const content = document.createElement('div');
                const parent = document.createElement('small');
                parent.className = 'objective-selected-parent';
                parent.textContent = label.dataset.objectiveParent;
                const name = document.createElement('strong');
                name.textContent = label.dataset.objectiveName;
                content.append(parent, name);
                const warning = label.querySelector('[data-period-warning]');
                if (label.dataset.objectiveState || !warning.hidden) {
                    const state = document.createElement('small');
                    state.className = warning.hidden ? 'text-muted' : 'text-danger';
                    state.textContent = [label.dataset.objectiveState, warning.hidden ? '' : warning.textContent].filter(Boolean).join(' · ');
                    content.append(state);
                }
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn objective-remove';
                remove.setAttribute('aria-label', text('removeLabel', 'name', label.dataset.objectiveName));
                remove.title = remove.getAttribute('aria-label');
                const icon = document.createElement('i');
                icon.className = 'ph ph-x';
                icon.setAttribute('aria-hidden', 'true');
                remove.append(icon);
                remove.addEventListener('click', () => {
                    const index = [...selected.querySelectorAll('button')].indexOf(remove);
                    input.checked = false;
                    input.dispatchEvent(new Event('change', {bubbles: true}));
                    const buttons = [...selected.querySelectorAll('button')];
                    (buttons[Math.min(index, buttons.length - 1)] || trigger).focus();
                });
                card.append(content, remove);
                selected.append(card);
            });
            selected.hidden = checked.length === 0;
            emptySelection.hidden = checked.length > 0;
            count.hidden = checked.length === 0;
            count.textContent = text('countLabel', 'count', checked.length);
            results.textContent = text('resultsLabel', 'count', visibleInputs().length);
            results.hidden = options.length === 0;
            if (!panel.hidden) positionPanel();
        };
        search.disabled = false;
        trigger.hidden = false;
        panel.hidden = true;
        widget.classList.add('objective-selector-ready');
        trigger.addEventListener('click', () => panel.hidden ? open() : close());
        trigger.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown') { event.preventDefault(); open(); }
        });
        picker.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) {
                event.preventDefault(); event.stopPropagation(); close(true);
            }
            if (panel.hidden) return;
            const inputs = visibleInputs();
            const index = inputs.indexOf(event.target);
            if ((event.target === search || index >= 0) && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                const target = event.target === search
                    ? (event.key === 'ArrowDown' ? inputs[0] : inputs.at(-1))
                    : (event.key === 'ArrowDown' ? inputs[index + 1] : inputs[index - 1]);
                (target || search).focus();
            } else if (index >= 0 && ['Home', 'End'].includes(event.key)) {
                event.preventDefault(); (event.key === 'Home' ? inputs[0] : inputs.at(-1)).focus();
            } else if (index >= 0 && event.key === 'Enter') {
                event.preventDefault(); event.target.click();
            } else if (event.target === search && event.key === 'Enter') {
                event.preventDefault();
                if (inputs.length === 1) inputs[0].click();
                else inputs[0]?.focus();
            }
        });
        options.forEach(({label, input}) => label.addEventListener('pointerdown', event => {
            if (event.target === input) return;
            // Keep focus inside the picker until the label's native click toggles its checkbox.
            event.preventDefault();
            input.focus();
        }));
        document.addEventListener('pointerdown', event => { if (!widget.contains(event.target)) close(); });
        picker.addEventListener('focusout', () => requestAnimationFrame(() => {
            if (!picker.contains(document.activeElement)) close();
        }));
        widget.addEventListener('initiative-selection:updated', refresh);
        widget.addEventListener('change', event => {
            const option = options.find(option => option.input === event.target);
            if (option) announce(option.input.checked ? 'addedLabel' : 'removedLabel', option.label.dataset.objectiveName);
        });
        form?.addEventListener('invalid', event => {
            if (!widget.contains(event.target)) return;
            const firstInvalid = [...form.elements].find(control => control.willValidate && !control.validity.valid);
            if (firstInvalid !== event.target) return;
            search.value = '';
            search.dispatchEvent(new Event('input', {bubbles: true}));
            open();
            if (event.target === search) {
                search.setAttribute('aria-invalid', 'true');
                announcement.textContent = search.validationMessage;
            }
        }, true);
        form?.addEventListener('reset', () => { close(); search.removeAttribute('aria-invalid'); announcement.textContent = ''; });
        refresh();
    });
})();
