

$(document).ready(function () {
    // scroll to active sidebar menu if available
    if ($('.sidebar-menu a.active').length !== 0) {
        $('.sidebar').animate({
            scrollTop: $(".sidebar-menu a.active").offset().top - 200
        }, 100);
    }

    // Hide decorative Phosphor icons from screen readers
    $('i.ph, i.ph-duotone').each(function () {
        const $icon = $(this);

        // Skip if explicitly labeled
        if ($icon.attr('aria-label') || $icon.attr('aria-labelledby')) {
            return;
        }

        $icon.attr({
            'aria-hidden': 'true',
            'role': 'presentation'
        });
    });
})

function initQuill(element, controls = 'basic') {
    let toolbar = [
        ['italic', 'underline'],
        [{ script: 'super' }, { script: 'sub' }]
    ];
    let formats = ['italic', 'underline', 'script'];
    if (controls === 'full') {
        toolbar = [
            ['bold', 'italic', 'underline'],
            // [{ header: [1, 2, 3, false] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ script: 'super' }, { script: 'sub' }],
            ['link', 'image'],
            ['clean']
        ]
        formats = ['italic', 'underline', 'bold', 'script', 'link', 'image', 'list', 'header']
    }
    var quill = new Quill(element, {
        modules: {
            toolbar: toolbar
        },
        formats: formats,
        placeholder: '',
        theme: 'snow'
    });

    quill.on('text-change', function () {
        var str = ''
        $(element).find('.ql-editor p').each(function (i, el) {
            var el = $(el)
            if (el.html() == '<br>') return;
            var html = el.html()
            if (str != '') str += "<br>"
            str += html
        })
        $(element).next().val(str)
    });

    if (controls === 'scientific') {
        // add additional symbol toolbar for greek letters
        var additional = $('<span class="ql-formats">')
        var symbols = ['α', 'β', 'π', 'Δ']
        symbols.forEach(symbol => {
            var btn = $('<button type="button" class="ql-symbol additional">')
            btn.html(symbol)
            btn.on('click', function () {
                // $('.symbols').click(function(){
                quill.focus();
                var symbol = $(this).html();
                var caretPosition = quill.getSelection(true);
                quill.insertText(caretPosition, symbol);
                // });
            })
            additional.append(btn)
        });
        $(element).parent().find('.ql-toolbar').append(additional)
    }

    return quill;

}

function quillEditor(selector, mode = 'snow') {
    const maxImageSize = 1024 * 1024; // 1 MB
    const editor = document.getElementById(selector + '-quill');
    if (!editor) return null;

    const compact = mode === 'compact';
    let compactWrapper = null;
    let compactToggle = null;

    if (compact) {
        compactWrapper = document.createElement('div');
        compactWrapper.className = 'quill-compact';
        editor.parentNode.insertBefore(compactWrapper, editor);
        compactWrapper.appendChild(editor);

        compactToggle = document.createElement('button');
        compactToggle.type = 'button';
        compactToggle.className = 'quill-compact-toggle';
        compactToggle.setAttribute('aria-label', lang('common.formatting'));
        compactToggle.setAttribute('title', lang('common.formatting'));
        compactToggle.setAttribute('aria-expanded', 'false');
        compactToggle.setAttribute('aria-haspopup', 'true');
        compactToggle.innerHTML = '<i class="ph ph-text-aa" aria-hidden="true"></i>';
        compactWrapper.insertBefore(compactToggle, editor);
    }

    const quill = new Quill('#' + selector + '-quill', {
        modules: {
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ script: 'sub' }, { script: 'super' }],
                    ['link', 'image'],
                    ['clean']
                ],
                handlers: {
                    image: function () {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.click();
                        input.onchange = () => {
                            const file = input.files[0];
                            if (!file) return;
                            if (file.size > maxImageSize) {
                                toastError(lang('common.the_selected_image_is_too_large_maximum_size_is_1_mb'));
                                return;
                            }
                            const reader = new FileReader();
                            reader.onload = e => {
                                const range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', e.target.result);
                                quill.setSelection(range.index + 1);
                            };
                            reader.readAsDataURL(file);
                        };
                    }
                }

            }
        },
        formats: ['italic', 'bold', 'underline', 'script', 'link', 'image', 'list', 'header'],
        placeholder: lang('common.start_typing_here'),
        theme: 'snow',
    });

    if (compact) initCompactQuill(quill, compactWrapper, compactToggle);

    quill.on('text-change', (delta, oldDelta, source) => {
        const input = document.getElementById(selector);
        input.value = quill.getSemanticHTML();
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    return quill;
}

function closeCompactQuillToolbar(wrapper) {
    if (!wrapper) return;

    wrapper.classList.remove('is-toolbar-open', 'is-toolbar-pinned');
    const toggle = wrapper.querySelector('.quill-compact-toggle');
    const toolbar = wrapper.querySelector('.ql-toolbar');
    toggle?.setAttribute('aria-expanded', 'false');
    toolbar?.setAttribute('aria-hidden', 'true');
    toolbar?.style.removeProperty('top');
    toolbar?.style.removeProperty('right');
    toolbar?.style.removeProperty('left');
}

function openCompactQuillToolbar(wrapper, toolbar, toggle, pinned = false) {
    document.querySelectorAll('.quill-compact.is-toolbar-open').forEach(openWrapper => {
        if (openWrapper !== wrapper) closeCompactQuillToolbar(openWrapper);
    });

    wrapper.classList.add('is-toolbar-open');
    wrapper.classList.toggle('is-toolbar-pinned', pinned);
    toggle.setAttribute('aria-expanded', 'true');
    toolbar.setAttribute('aria-hidden', 'false');

    if (pinned) {
        toolbar.style.removeProperty('top');
        toolbar.style.removeProperty('right');
        toolbar.style.removeProperty('left');
    }
}

function positionCompactQuillToolbar(quill, wrapper, toolbar, range) {
    const bounds = quill.getBounds(range.index, range.length);
    if (!bounds) return;

    const editorBounds = quill.container.getBoundingClientRect();
    const wrapperBounds = wrapper.getBoundingClientRect();
    const editorLeft = editorBounds.left - wrapperBounds.left;
    const editorTop = editorBounds.top - wrapperBounds.top;
    const gap = 8;

    let left = editorLeft + bounds.left + bounds.width / 2 - toolbar.offsetWidth / 2;
    left = Math.max(4, Math.min(left, wrapper.clientWidth - toolbar.offsetWidth - 4));

    let top = editorTop + bounds.top - toolbar.offsetHeight - gap;
    if (top < 0) top = editorTop + bounds.bottom + gap;

    toolbar.style.top = `${top}px`;
    toolbar.style.right = 'auto';
    toolbar.style.left = `${left}px`;
}

function initCompactQuill(quill, wrapper, toggle) {
    if (!quill || !wrapper || !toggle) return;

    const toolbar = quill.getModule('toolbar')?.container;
    if (!toolbar) return;

    toolbar.id = `${quill.container.id}-toolbar`;
    toolbar.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-controls', toolbar.id);
    let lastRange = { index: 0, length: 0 };

    quill.on('selection-change', (range, oldRange, source) => {
        if (range) lastRange = range;

        if (range?.length > 0 && source === Quill.sources.USER) {
            openCompactQuillToolbar(wrapper, toolbar, toggle);
            positionCompactQuillToolbar(quill, wrapper, toolbar, range);
        } else if (!wrapper.classList.contains('is-toolbar-pinned')) {
            closeCompactQuillToolbar(wrapper);
        }
    });

    toggle.addEventListener('mousedown', event => event.preventDefault());
    toggle.addEventListener('click', () => {
        const pinned = wrapper.classList.contains('is-toolbar-pinned');
        if (pinned) {
            closeCompactQuillToolbar(wrapper);
            return;
        }

        openCompactQuillToolbar(wrapper, toolbar, toggle, true);
        quill.focus({ preventScroll: true });
        quill.setSelection(lastRange.index, lastRange.length, Quill.sources.API);
    });

    document.addEventListener('click', event => {
        if (!wrapper.contains(event.target)) closeCompactQuillToolbar(wrapper);
    });

    wrapper.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        closeCompactQuillToolbar(wrapper);
        toggle.focus();
    });
}

function initLocalizedQuill(editor) {
    if (!editor || editor.dataset.initialized === 'true') return;

    const input = editor.nextElementSibling;
    if (!input || !input.id) return;

    editor.localizedQuill = quillEditor(input.id, 'compact');
    editor.dataset.initialized = 'true';
}

function localizedInputHasValue(input) {
    if (!input) return false;

    const value = input.value.trim();
    const editorContainer = input.previousElementSibling;
    const isRichText = editorContainer?.classList.contains('localized-quill')
        || Boolean(editorContainer?.querySelector('.localized-quill'));
    if (!isRichText) return value !== '';

    const content = document.createElement('div');
    content.innerHTML = value;
    const text = (content.textContent || '').replace(/\u00a0/g, ' ').trim();
    return text !== '' || content.querySelector('img, video, iframe') !== null;
}

function localizedGroupFields(field) {
    if (!field) return [];

    const group = field.dataset.localizedGroup;
    if (!group) return [field];

    const scope = field.closest('form') || document;
    return Array.from(scope.querySelectorAll('.localized-field[data-localized-group]'))
        .filter(item => item.dataset.localizedGroup === group);
}

function updateLocalizedLanguageStatus(input) {
    const panel = input.closest('.localized-language-panel');
    const field = panel?.closest('.localized-field');
    if (!panel || !field) return;

    const language = panel.dataset.language;
    const fields = localizedGroupFields(field);
    const hasValue = fields.some(item => {
        const languagePanel = Array.from(item.querySelectorAll('.localized-language-panel'))
            .find(candidate => candidate.dataset.language === language);
        return localizedInputHasValue(languagePanel?.querySelector('.localized-value'));
    });

    fields.forEach(item => {
        item.querySelectorAll('.localized-language-tab').forEach(tab => {
            if (tab.dataset.language !== language) return;
            tab.classList.toggle('has-value', hasValue);
            tab.classList.toggle('is-empty', !hasValue);
        });
    });
}

function selectLocalizedLanguage(tab, focusField = true) {
    const field = tab.closest('.localized-field');
    if (!field) return;

    const language = tab.dataset.language;
    const fields = localizedGroupFields(field);
    let activePanel = null;

    fields.forEach(item => {
        item.querySelectorAll('.localized-language-tab').forEach(languageTab => {
            const active = languageTab.dataset.language === language;
            languageTab.classList.toggle('active', active);
            languageTab.setAttribute('aria-selected', active ? 'true' : 'false');
            languageTab.tabIndex = active ? 0 : -1;
        });
        item.querySelectorAll('.localized-language-indicator [data-language]').forEach(indicator => {
            indicator.hidden = indicator.dataset.language !== language;
        });

        let itemActivePanel = null;
        item.querySelectorAll('.localized-language-panel').forEach(panel => {
            const active = panel.dataset.language === language;
            panel.hidden = !active;
            if (!active) panel.querySelectorAll('.quill-compact').forEach(closeCompactQuillToolbar);
            if (active) itemActivePanel = panel;
        });

        itemActivePanel?.querySelectorAll('.localized-quill').forEach(initLocalizedQuill);
        const fieldInput = itemActivePanel?.querySelector('.localized-value');
        const label = item.querySelector('.localized-field-heading > label');
        if (label && fieldInput) label.htmlFor = fieldInput.id;
        if (item === field) activePanel = itemActivePanel;
    });

    if (!focusField || !activePanel) return;

    const input = activePanel.querySelector('.ql-editor, .localized-value:not(.d-none)');
    input?.focus();
}

function prepareLocalizedFieldsForSubmit(form) {
    form.querySelectorAll('.localized-field').forEach(field => {
        field.querySelectorAll('.localized-language-panel').forEach(panel => {
            const input = panel.querySelector('.localized-value');
            if (!input) return;

            const isBaseLanguage = panel.dataset.language === field.dataset.baseLanguage;
            input.disabled = !isBaseLanguage && !localizedInputHasValue(input);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.localized-quill').forEach(editor => {
        if (!editor.closest('[hidden]')) initLocalizedQuill(editor);
    });
    document.querySelectorAll('.localized-language-panel .localized-value')
        .forEach(updateLocalizedLanguageStatus);
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => prepareLocalizedFieldsForSubmit(form));
    });
});

document.addEventListener('input', event => {
    if (event.target.matches('.localized-language-panel .localized-value')) {
        updateLocalizedLanguageStatus(event.target);
    }
});

document.addEventListener('keydown', event => {
    const tab = event.target.closest('.localized-language-tab');
    if (!tab || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

    const tabs = Array.from(tab.closest('.localized-language-tabs').querySelectorAll('.localized-language-tab'));
    let index = tabs.indexOf(tab);
    if (event.key === 'Home') index = 0;
    else if (event.key === 'End') index = tabs.length - 1;
    else index = (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;

    event.preventDefault();
    selectLocalizedLanguage(tabs[index], false);
    tabs[index].focus();
});

document.addEventListener('invalid', event => {
    const panel = event.target.closest('.localized-language-panel');
    if (!panel?.hidden) return;

    const field = panel.closest('.localized-field');
    const tab = localizedGroupFields(field)
        .flatMap(item => Array.from(item.querySelectorAll('.localized-language-tab')))
        .find(item => item.dataset.language === panel.dataset.language);
    if (tab) selectLocalizedLanguage(tab, false);
}, true);


function readHash() {
    var hash = window.location.hash.substr(1);
    // console.log(hash);
    if (hash === undefined || hash == "") return {}
    return hash.split('&').reduce(function (res, item) {
        var parts = item.split('=');
        res[parts[0]] = parts[1];
        return res;
    }, {});
}

function writeHash(data) {
    var hash = readHash()
    for (const key in data) {
        if (data[key] === null)
            delete hash[key]
        else
            hash[key] = data[key];
    }
    hash = Object.entries(hash)
    var arr = hash.map(function (a) {
        return a[0] + "=" + a[1]
    })
    window.location.hash = arr.join("&")
}

$('input[name=activity]').on('change', function () {
    $('input[name=activity]').removeClass('primary')
    $(this).addClass('primary')

})

function toastError(msg = "", title = null) {
    if (title === null) title = lang('common.error')
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "danger",
        hasDismissButton: true,
        timeShown: 10000
    })
    // also log to console
    console.error(msg)
}
function toastSuccess(msg = "", title = null) {
    if (title === null) title = lang('common.success')
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "success",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function toastWarning(msg = "", title = null) {
    if (title === null) title = lang('common.warning')
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "signal",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function toastInfo(msg = "", title = null) {
    if (title === null) title = lang('common.info_727698de')
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "primary",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function getCookie(cname) {
    let decodedCookie = decodeURIComponent(document.cookie);
    if (cname === null) {
        return decodedCookie
    }
    let name = cname + "=";
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
function lang(en, de = null, replace = {}) {
    if (de !== null && typeof de === 'object') {
        replace = de;
        de = null;
    }
    if (de === null) {
        let result = window.OSIRIS_JS_TRANSLATIONS?.[en] ?? en;
        Object.entries(replace).forEach(([key, value]) => {
            result = result.replaceAll(`{{${key}}}`, String(value));
        });
        return result;
    }
    var language = document.documentElement.lang || getCookie('osiris-language');
    if (language === undefined) return de;
    if (language == "en") return en;
    if (language == "de") return de;
    return de;
}

function objectifyForm(formArray) {
    //serialize data function
    var returnArray = {};
    for (var i = 0; i < formArray.length; i++) {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}

function isEmpty(value) {
    switch (typeof (value)) {
        case "string": return (value.length === 0);
        case "number":
        case "boolean": return false;
        case "undefined": return true;
        case "object": return !value ? true : false; // handling for null.
        default: return !value ? true : false
    }
}

function resetInput(el) {
    $(el).addClass('hidden')
    var el = $(el).prev()
    var old = el.attr("data-value").trim()
    el.val(old)
    el.removeClass("is-valid")
}



$('#edit-form').on('submit', function (event) {
    event.preventDefault()
    var values = {}
    $('#edit-form [data-value]').each(function (i, el) {
        var el = $(el)
        var name = el.attr('name')
        var old = el.attr("data-value").trim()
        if (old != el.val().trim()) {
            values[name] = el.val()
        }
    })
    if (Object.entries(values).length === 0) {
        toastError("Nothing to change. Only highlighted fields will be submitted to the database.")
        return
    }
    $('#edit-form input[type="hidden"]').each(function (i, el) {
        var el = $(el)
        var name = el.attr('name')
        values[name] = el.val()
    })
    values['comment'] = $('#editor-comment').val()
    // console.log(values);
    $.ajax({
        type: "POST",
        data: values,
        dataType: "html",
        url: ROOTPATH + "/update",
        success: function (data) {
            // console.log(data);
            toastSuccess(data)
            location.reload()
        },
        error: function (response) {
            // console.log(response.responseText)
            toastError(response.responseText)
        }
    })
})


$('.highlight-badge').on("mouseenter", function () {
    var row = this.innerHTML;
    $("#row-" + row).addClass('table-primary')
})
    .on("mouseleave", function () {
        var row = this.innerHTML;
        $("#row-" + row).removeClass('table-primary')
    })


function tableToCSV() {

    // Variable to store the final csv data
    var csv_data = [];

    // Get each row data
    var rows = document.getElementsByTagName('tr');
    for (var i = 0; i < rows.length; i++) {

        // Get each column data
        var cols = rows[i].querySelectorAll('td,th');

        // Stores each csv row data
        var csvrow = [];
        for (var j = 0; j < cols.length; j++) {

            // Get the text data of each cell of
            // a row and push it to csvrow
            csvrow.push(cols[j].innerHTML);
        }

        // Combine each column value with comma
        csv_data.push(csvrow.join(";"));
    }
    // combine each row data with new line character
    csv_data = csv_data.join('\n');

    downloadCSVFile(csv_data);
}

function downloadCSVFile(csv_data) {

    // Create CSV file object and feed our
    // csv_data into it
    CSVFile = new Blob([csv_data], { type: "text/csv" });

    // Create to temporary link to initiate
    // download process
    var temp_link = document.createElement('a');

    // Download csv file
    temp_link.download = "itool.csv";
    var url = window.URL.createObjectURL(CSVFile);
    temp_link.href = url;

    // This link should not be displayed
    temp_link.style.display = "none";
    document.body.appendChild(temp_link);

    // Automatically click the link to trigger download
    temp_link.click();
    document.body.removeChild(temp_link);
}



function strDate(date) {
    var res = date[0];

    if (date[1] != '') res += "-" + ("0" + date[1]).slice(-2)
    else res += "-01"

    if (date[2] != '') res += "-" + ("0" + date[2]).slice(-2)
    else res += "-01"

    return res
}


function todo() {
    osirisJS.initStickyAlert({
        content: lang('common.sorry_but_this_button_does_not_work_yet'),
        title: '<i class="ph ph-smiley-sad ph-3x text-signal"></i>',
        alertType: "",
        hasDismissButton: true
    })
}

function loadModal(path, data = {}) {
    $.ajax({
        type: "GET",
        dataType: "html",
        data: data,
        url: ROOTPATH + '/' + path,
        success: function (response) {
            $('#modal-content').html(response)
            $('#the-modal').addClass('show')


            if ($('#the-modal .title-editor').length !== 0) {
                var quill = new Quill('#the-modal .title-editor', {
                    modules: {
                        toolbar: [
                            ['italic', 'underline']
                        ]
                    },
                    formats: ['italic', 'underline'],
                    placeholder: '',
                    theme: 'snow'
                });
                quill.on('text-change', function (delta, oldDelta, source) {
                    var delta = quill.getContents()
                    // console.log(delta);
                    var str = ""
                    delta.ops.forEach(el => {
                        if (el.attributes !== undefined) {
                            if (el.attributes.bold) str += "<b>";
                            if (el.attributes.italic) str += "<i>";
                            if (el.attributes.underline) str += "<u>";
                        }
                        str += el.insert;
                        if (el.attributes !== undefined) {
                            if (el.attributes.underline) str += "</u>";
                            if (el.attributes.italic) str += "</i>";
                            if (el.attributes.bold) str += "</b>";
                        }
                    });
                    $('#the-modal #title').val(str)
                });
            }
        },
        error: function (response) {
            // console.log(response);
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}

// function toggleEditForm(collection, id) {
//     loadModal('form/' + collection + '/' + id);

// }


function filter_results(input) {
    var table = $('#result-table')
    if (table.length == 0) return;
    var rows = table.find('tbody > tr')
    if (input.length == 0) {
        rows.show();
        return
    }
    rows.hide()
    var data = input.split(" ");
    $.each(data, function (i, v) {
        // workaround: ignore button content (unbreakable)
        rows.find('td:not(.unbreakable)').filter(":contains('" + v + "')").parent().show();
    });
}


function updateCart(add = true) {
    var cart = $('#cart-counter')
    var counter = cart.html()
    if (add) {
        counter++;
    } else {
        counter--;
    }
    cart.html(counter)
    if (counter == 0) {
        cart.addClass('hidden')
    } else {
        cart.removeClass('hidden')
    }
}

function addToCart(el, id) {//.addClass('animate__flip')
    // document.cookie = "username=John Doe; expires=Thu, 18 Dec 2013 12:00:00 UTC"; 
    var fav = osirisJS.readCookie('osiris-cart')
    var action;
    if (fav) {
        var favlist = fav.split(',')
        // console.log(favlist);
        const index = favlist.indexOf(id);
        if (index > -1) {
            favlist.splice(index, 1);
            action = "remove";
            updateCart(false)
            toastInfo(lang('common.item_removed_from_your_collection'))
        } else {
            if (favlist.length > 30) {
                toastError(lang('common.you_can_have_no_more_than_30_items_in_your_collection'))
                return;
            }
            favlist.push(id)
            action = "add";
            toastInfo(lang('common.item_added_to_collection', { rootpath: ROOTPATH }))
            updateCart(true)
        }
        fav = favlist.join(',')
    } else {
        fav = id
        action = "add";
        updateCart(true)
        toastInfo(lang('common.item_added_to_collection', { rootpath: ROOTPATH }))
    }
    osirisJS.createCookie('osiris-cart', fav, 30)
    if (el === null) {
        location.reload()
    } else {
        if (action == "add") {
            $(el).find('i').addClass('text-success').removeClass('ph').addClass('ph-duotone')
        } else {
            $(el).find('i').removeClass('text-success').removeClass('ph-duotone').addClass('ph')
        }
        console.log(action);
    }

}



function orderByAttr(a, b) {
    a = $(a).attr('data-value')
    b = $(b).attr('data-value')
    if (a === undefined) return -1
    if (b === undefined) return 1
    return a.localeCompare(b)
}

function dump(el) {
    console.log(el);
}


function copyTextToClipboard(text) {
    // check if navigator.clipboard is available
    if (!navigator.clipboard) {
        toastError(lang('common.this_browser_does_not_support_copying_to_clipboard'));
        return;
    }
    navigator.clipboard.writeText(text)
    toastSuccess(lang('common.query_copied_to_clipboard'))
}

function copyToClipboard(selector) {
    // check if navigator.clipboard is available
    if (!navigator.clipboard) {
        toastError(lang('common.this_browser_does_not_support_copying_to_clipboard'));
        return;
    }
    var text = $(selector).text()
    navigator.clipboard.writeText(text)
    toastSuccess(lang('common.query_copied_to_clipboard'))
}
