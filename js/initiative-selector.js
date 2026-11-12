/* Date-aware initiative and objective selection; no requests on date changes. */
(() => {
    document.querySelectorAll('[data-initiative-selector]').forEach(widget => {
        if (widget.dataset.initialized) return;
        widget.dataset.initialized = 'true';
        const form = widget.closest('form');
        if (!form) return;
        const activity = form.id === 'activity-form';
        const controls = widget.querySelector('[data-selection-controls]');
        const toggle = controls.querySelector('[data-show-all]');
        const search = controls.querySelector('[data-selection-search]');
        const status = controls.querySelector('[data-selection-status]');
        const empty = controls.querySelector('[data-selection-empty]');
        const options = [...widget.querySelectorAll('[data-selection-option]')].map(label => ({
            label, input: label.querySelector('input'), bounds: JSON.parse(label.dataset.period)
        }));
        const editing = form.getAttribute('action')?.includes('/crud/activities/update/');
        const preserved = new Set(editing ? options.filter(option => option.input.defaultChecked).map(option => option.input.value) : []);
        const hint = status.textContent;
        let showAll = false;
        if (toggle) toggle.hidden = !activity || options.length === 0;

        const value = key => form.elements.namedItem(`values[${key}]`)?.value ?? '';
        const period = () => {
            if (form.elements.namedItem('values[start]')) {
                const start = value('start');
                if (!/^\d{4}-\d{2}-\d{2}$/.test(start)) return null;
                return [start, value('end') && value('end') >= start ? value('end') : start];
            }
            const year = Number(value('year'));
            const month = Number(value('month'));
            const day = Number(value('day'));
            if (!Number.isInteger(year) || year < 1900 || year > 2155 || month < 0 || month > 12 || day < 0 || day > 31 || (day && !month)) return null;
            if (!Number.isInteger(month) || !Number.isInteger(day)) return null;
            const lastDay = month ? new Date(year, month, 0).getDate() : 31;
            if (day > lastDay) return null;
            const pad = number => String(number).padStart(2, '0');
            const start = `${year}-${pad(month || 1)}-${pad(day || 1)}`;
            return [start, day ? start : `${year}-${pad(month || 12)}-${pad(lastDay)}`];
        };
        const update = () => {
            const dates = activity ? period() : null;
            const query = showAll ? (search?.value ?? '').trim().toLocaleLowerCase() : '';
            options.forEach(({ label, input, bounds }) => {
                const matches = !activity || (dates && !bounds.archived
                    && (!bounds.start || !bounds.end || bounds.start <= bounds.end)
                    && (!bounds.start || bounds.start <= dates[1]) && (!bounds.end || bounds.end >= dates[0]));
                const allowed = matches || preserved.has(input.value) || Boolean(toggle);
                label.hidden = !input.checked && (!(showAll || matches) || !label.dataset.searchText.toLocaleLowerCase().includes(query));
                if (activity) label.classList.toggle('inactive', !matches);
                label.querySelector('[data-period-warning]').hidden = !activity || matches || !input.checked;
                input.setCustomValidity(input.checked && !allowed ? controls.dataset.invalid : '');
                input.required = false;
            });
            widget.querySelectorAll('[data-objective-group]').forEach(group => {
                group.hidden = ![...group.querySelectorAll('[data-selection-option]')].some(label => !label.hidden);
            });
            const visible = options.filter(option => !option.label.hidden);
            const needsSelection = widget.dataset.required === 'true' && !options.some(option => option.input.checked);
            if (needsSelection && visible.length) visible[0].input.required = true;
            empty.disabled = !needsSelection || visible.length > 0;
            empty.hidden = empty.disabled;
            status.hidden = !activity && options.length > 0;
            empty.options[0].textContent = !options.length ? controls.dataset.noEntries : controls.dataset.noMatches;
            status.textContent = !options.length ? controls.dataset.noEntries
                : (activity && !dates && !showAll ? controls.dataset.chooseDate : (!visible.length ? controls.dataset.noMatches : hint));
        };
        toggle?.addEventListener('click', () => {
            showAll = !showAll;
            toggle.setAttribute('aria-expanded', String(showAll));
            toggle.textContent = showAll ? toggle.dataset.hideLabel : toggle.dataset.showLabel;
            search.hidden = !showAll;
            if (!showAll) search.value = '';
            update();
            if (showAll) search.focus();
        });
        search?.addEventListener('input', update);
        widget.addEventListener('change', update);
        const onDateChange = event => {
            if (/^values\[(year|month|day|start|end)\]$/.test(event.target.name)) update();
        };
        form.addEventListener('input', onDateChange);
        form.addEventListener('change', onDateChange);
        form.addEventListener('reset', () => setTimeout(() => {
            showAll = false;
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = toggle.dataset.showLabel;
                search.hidden = true;
                search.value = '';
            }
            update();
        }, 0));
        update();
    });
})();
