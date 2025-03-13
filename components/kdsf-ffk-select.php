<?php
$selected_ffk = [];
if (isset($form) && isset($form['kdsf-ffk'])) {
    $selected_ffk = $form['kdsf-ffk'];
}
?>


<style>
    .kdsf-widget {
        display: block;
        width: 100%;
        padding-left: 0.8rem;
        padding-right: 0.8rem;
        background-color: white;
        border: var(--border-width) solid var(--border-color);
        border-radius: var(--border-radius);
        -moz-box-shadow: 0 0.2rem 0 rgba(0, 0, 0, 0.05);
        -webkit-box-shadow: 0 0.2rem 0 rgba(0, 0, 0, 0.05);
        box-shadow: 0 0.2rem 0 rgba(0, 0, 0, 0.05);
    }

    .kdsf-widget label {
        display: block;
        font-weight: bold;
        margin: 0.5rem 0;
    }

    #kdsf-ffk-list,
    .kdsf-item {
        list-style-type: none;
        margin: 1rem 0;
    }

    #kdsf-ffk-list {
        max-height: 20rem;
        overflow-y: auto;
    }

    .kdsf-item {
        padding-left: 2rem;
    }

    .toggle {
        cursor: pointer;
        font-weight: bold;
    }

    .toggle .toggle-icon::before {
        content: "\e3d6";
        color: var(--muted-color);
    }

    .toggle.open .toggle-icon::before {
        content: "\e32c";
    }

    .toggle.selected .toggle-icon::before {
        content: '\E184';
        color: var(--success-color);
    }
    .kdsf-tooltip {
        display: none;
        /* position: relative; */
        background-color: white;
        /* max-width: 40rem; */
        color: var(--text-color);
        z-index: 1;
        font-size: 1.2rem;
        color: var(--muted-color);
    }
</style>
<div class="kdsf-widget">
    <label for="kdsf-ffk">
        KDSF Forschungsfeldklassifikation
    </label>
    <input type="search" id="search" placeholder="Suchen..." class="form-control">
    <ul id="kdsf-ffk-list"></ul>
</div>

<script>
    function createList(items, parentUl) {
        const selected_ffk = <?= json_encode($selected_ffk) ?>;
        items.forEach(item => {
            let li = $('<li class="kdsf-category"></li>');
            if (item.children) {
                let toggle = $('<span class="toggle"><i class="ph toggle-icon"></i> ' + lang(item.labels.en, item.labels.de) + '</span>');
                let subUl = $('<ul class="kdsf-item hidden"></ul>');
                // check if an item within the children is selected
                item.children.forEach(child => {
                    if (selected_ffk.includes(child.id)) {
                        subUl.removeClass('hidden');
                        toggle.addClass('selected');
                        toggle.addClass('open');
                    }
                });
                toggle.click(() => {
                    subUl.toggleClass('hidden')
                    toggle.toggleClass('open');
                });
                li.append(toggle, subUl);
                createList(item.children, subUl);
            } else {
                let note = '';
                if (item.scope_notes) {
                    note = ' title="' + lang(item.scope_notes.en, item.scope_notes.de) + '"';
                }
                let checkbox = $('<input type="checkbox" name="values[kdsf-ffk][]" value="' + item.id + '" id="ffk-' + item.id + '">');
                if (selected_ffk.includes(item.id)) {
                    checkbox.prop('checked', true);
                }
                checkbox.change(() => {
                    // toggle parent class 
                    // find out if any in the list is checked
                    let cat = checkbox.parents('.kdsf-category');
                    let checked = cat.find('input[type="checkbox"]:checked').length;
                    if (checked > 0) {
                        cat.find('.toggle').addClass('selected');
                    } else {
                        cat.find('.toggle').removeClass('selected');
                    }

                });
                let tooltip = $('<i class="ph ph-question text-primary"></i>');
                tooltip.on('click', function() {
                    $(this).next().slideToggle();
                })
                let info = '';
                if (item.scope_notes) {
                    info = '<div class="kdsf-tooltip">' + lang(item.scope_notes.en, item.scope_notes.de) + '</div>';
                }
                li.append(checkbox, ' ' + lang(item.labels.en, item.labels.de) + ' ', tooltip,  info);
            }
            parentUl.append(li);
        });
    }

    $.get(ROOTPATH + '/data/kdsf', data => {
        createList(data, $('#kdsf-ffk-list'));
    });

    $('#search').on('input', function() {
        let term = $(this).val().toLowerCase();
        $('li').each(function() {
            let text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(term));
        });
    });
</script>