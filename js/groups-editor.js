
$(document).ready(function () {

    // read hash to navigate
    var hash = window.location.hash;
    if (hash && hash.includes('#section-')) {
        navigate(hash.replace('#section-', ''));
    }
});

function navigate(key){
    $('section').hide()
    $('section#' + key).show()
    if (key == 'personnel' || key == 'settings') {
        $('section#'+key+'-2').show()
    }

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    // hash
    window.location.hash = 'section-' + key;

}


function addHead(t, st) {
    var sel = $(`.author-widget .head-input`);
    var val = sel.val();
    if (val == null) return;
    var text = sel.find(`option[value='${val}']`).text();
    var el = `<div class='author'>${text}<input type='hidden' name='values[head][]' value='${val}'><a onclick='$(this).parent().remove()'>&times;</a></div>`;
    $(`.author-list`).append(el);
}

function searchActivities(index) {
    const section = $('#activities-' + index)
    const val = section.find('input[type=text]').val()
    const suggest = section.find('.suggestions');
    suggest.empty().show();
    // prevent enter from submitting form
    $(section).closest('form').on('keypress', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
        }
    })
    if (val.length < 3) {
        suggest.append(`<span >${lang('common.please_type_at_least_3_characters')}</span>`)
        return;
    }
    $.get(ROOTPATH+'/api/activities-suggest/' + val+ '?unit='+UNIT, function(data) {
        console.log(data);
        if (data.count == 0) {
            suggest.append(`<span >${lang('common.nothing_found')}</span>`)
            return;
        }
        data.data.forEach(function(d) {
            suggest.append(
                `<a  data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
            )
        })
        suggest.find('a')
            .on('click', function(event) {
                event.preventDefault();
                const tr = $('<tr>')
                tr.append('<td><span class="handle"><i class="ph ph-dots-six-vertical"></i></span></td>')
                const el = $('<td>')
                    .text($(this).text())
                el.append(`<input type="hidden" name="values[research][${index}][activities][]" value="${$(this).data('id')}">`)
                tr.append(el);
                tr.append('<td><button class="btn link text-danger small" type="button" onclick="$(this).closest(\'tr\').remove()"><i class="ph ph-trash"></i></button></td>')
                console.log(tr);
                section.find('.activity-list').append(tr);
            })
        // $('#activity-suggest .suggest').html(data);
    })

}

let newResearchRowCount = 0;

function addResearchrow(evt) {
    evt.preventDefault();

    const template = document.getElementById('research-row-template');
    const list = document.getElementById('research-list');
    if (!template || !list) return;

    const index = `new-${Date.now().toString(36)}-${newResearchRowCount++}`;
    const instance = document.createElement('template');
    instance.innerHTML = template.innerHTML.split('__INDEX__').join(index).trim();

    const row = instance.content.firstElementChild;
    if (!row) return;
    list.appendChild(row);

    row.querySelectorAll('.localized-quill').forEach(editor => {
        if (!editor.closest('[hidden]')) initLocalizedQuill(editor);
    });
    row.querySelectorAll('.localized-language-panel .localized-value')
        .forEach(updateLocalizedLanguageStatus);
    $(row).find('.activity-list').sortable({ handle: '.handle' });

    const titleField = row.querySelector('.localized-field[data-localized-field$="-title"]');
    const basePanel = Array.from(titleField?.querySelectorAll('.localized-language-panel') || [])
        .find(panel => panel.dataset.language === titleField?.dataset.baseLanguage);
    const titleInput = basePanel?.querySelector('.localized-value');
    const heading = row.querySelector('.header q');

    titleInput?.addEventListener('input', () => {
        if (heading) heading.textContent = titleInput.value.trim() || lang('common.research_interest');
    });
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    titleInput?.focus({ preventScroll: true });
}

// function toggleVisibility() {
//     var hide = $('#hide-check').prop('checked');
//     if (hide) {
//         $('#research').hide();
//     } else {
//         $('#research').show();
//     }
// }


function deptSelect(val) {
    if (val === '') {
        $('#color-row').hide()
        return;
    }
    var opt = $('#parent').find('[value=' + val + ']')
    console.log(opt.attr('data-level'));
    if (opt.attr('data-level') != '0') {
        $('#color-row').hide()
    } else {
        $('#color-row').show()
    }
}
