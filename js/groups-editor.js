
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
        suggest.append(`<span >${lang('Please type at least 3 characters', 'Mindestens 3 Zeichen erforderlich')}</span>`)
        return;
    }
    $.get(ROOTPATH+'/api/activities-suggest/' + val+ '?unit='+UNIT, function(data) {
        console.log(data);
        if (data.count == 0) {
            suggest.append(`<span >${lang('Nothing found', 'Nichts gefunden')}</span>`)
            return;
        }
        data.data.forEach(function(d) {
            suggest.append(
                `<a onclick="selectActivity(this)" data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
            )
        })
        suggest.find('a')
            .on('click', function(event) {
                event.preventDefault();
                console.log(this);
                const el = $('<li>')
                    .text($(this).text())
                el.append(`<input type="hidden" name="values[research][${index}][activities][]" value="${$(this).data('id')}">`)
                section.find('ul').append(el);
            })
        // $('#activity-suggest .suggest').html(data);
    })

}





function addResearchrow(evt, parent) {
    i++;
    var el = `
<div class="box padded">
    <div class="row row-eq-spacing my-0">
        <div class="col-md-6">
            <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
            <div class="form-group">
                <input name="values[research][${i}][title]" type="text" class="form-control large" value="" placeholder="Title" required>
            </div>
        </div>
        <div class="col-md-6">
            <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
            <div class="form-group">
                <input name="values[research][${i}][title_de]" type="text" class="form-control large" value="" placeholder="Title">
            </div>
        </div>
    </div>
    ${lang('Please save once to add more information.', 'Bitte speichere einmal, um weitere Informationen hinzuzufügen.')}<br>
    <button class="btn danger" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i> ${lang('Delete', 'Löschen')}</button>
</div>

    `;
    $(parent).append(el);
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