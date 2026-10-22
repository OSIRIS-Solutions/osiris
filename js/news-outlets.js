(() => {
    const list = document.getElementById('news-outlets');
    const template = document.getElementById('news-outlet-template');
    const empty = document.getElementById('news-outlets-empty');
    let nextKey = list.children.length;

    function updateControls() {
        const outlets = Array.from(list.children);
        empty.hidden = outlets.length > 0;
        outlets.forEach((outlet, index) => {
            outlet.querySelector('[data-outlet-up]').disabled = index === 0;
            outlet.querySelector('[data-outlet-down]').disabled = index === outlets.length - 1;
        });
    }

    document.getElementById('add-news-outlet').addEventListener('click', () => {
        const key = 'new-' + nextKey++;
        const content = template.content.cloneNode(true);
        content.querySelectorAll('[name], [id], [for]').forEach(element => {
            ['name', 'id', 'for'].forEach(attribute => {
                if (element.hasAttribute(attribute)) {
                    element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__KEY__', key));
                }
            });
        });
        const outlet = content.querySelector('[data-outlet]');
        list.append(content);
        updateControls();
        outlet.querySelector('[data-outlet-name]').focus();
    });

    list.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        const outlet = button.closest('[data-outlet]');
        if (button.hasAttribute('data-outlet-up') && outlet.previousElementSibling) {
            list.insertBefore(outlet, outlet.previousElementSibling);
        } else if (button.hasAttribute('data-outlet-down') && outlet.nextElementSibling) {
            list.insertBefore(outlet.nextElementSibling, outlet);
        } else if (button.hasAttribute('data-outlet-remove')) {
            outlet.remove();
        }
        updateControls();
    });

    list.addEventListener('input', event => {
        if (!event.target.hasAttribute('data-outlet-name')) return;
        const outlet = event.target.closest('[data-outlet]');
        const english = outlet.querySelector('[data-outlet-name="en"]').value;
        const german = outlet.querySelector('[data-outlet-name="de"]').value;
        outlet.querySelector('[data-outlet-title]').textContent =
            outlet.dataset.outletLanguage === 'de' ? (german || english) : english;
    });
    updateControls();
})();
