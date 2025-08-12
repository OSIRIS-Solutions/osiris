
function sanitizeID(element, idlist = '#IDLIST li') {
    // read input value and make sure its lowercase
    const val = element.value.toLowerCase();
    $(element).val(val)

    var list = []

    // get existing IDs from list
    if ( typeof idlist === 'string' ) {
        list = $(idlist).map(function (i, v) {
            return $(this).text().trim();
        }).toArray();
    } else {
        list = idlist
    }
    // check if selected ID is in list
    if (val == '') {
        $(element).addClass('is-invalid').removeClass('is-valid')
        toastError(lang('ID cannot be empty.', 'ID darf nicht leer sein.'))
        $('#submitBtn').attr('disabled', true)
    } else if (list.includes(val)) {
        // give negative feedback to user and disable submit button
        $(element).addClass('is-invalid').removeClass('is-valid')
        if (val == 'new') {
            toastError(lang('NEW is a reserved keyword.', 'NEW ist ein reserviertes Schlüsselwort.'))
        } else {
            toastError(lang('ID does already exist.', 'ID existiert bereits.'))
        }
        $('#submitBtn').attr('disabled', true)
    } else {
        // give positive feedback to user and enable submit button
        $(element).addClass('is-valid').removeClass('is-invalid')
        $('#submitBtn').attr('disabled', false)
    }
}

function addModule() {

    var el = $('.author-list')
    var val = $('.module-input').val()
    console.log(val);
    if (val === undefined || val === null || val == '') return;

    // check if value already exists
    let values = el.find('input[type=hidden]')
    let exists = false;
    values.each(function () {
        if ($(this).val() === val) {
            exists = true;
            return false; // break the loop
        }
    });
    if (exists) {
        toastError(lang('Module already exists.', 'Modul existiert bereits.'));
        return;
    }
    
    var author = $('<div class="author" ondblclick="toggleRequired(this)">')
        .html(val);
    author.append('<input type="hidden" name="values[modules][]" value="' + val + '">')
    author.append('<a onclick="$(this).parent().remove()">&times;</a>')
    author.appendTo(el)
}

// function addModule(type, subtype) {

//     var el = $('#type-' + type).find('#subtype-' + subtype).find('.author-widget')
//     var val = el.find('.module-input').val()
//     if (val === undefined || val === null) return;
//     console.log(val);
//     var author = $('<div class="author" ondblclick="toggleRequired(this)">')
//         .html(val);
//     author.append('<input type="hidden" name="activities[' + type + '][children][' + subtype + '][modules][]" value="' + val + '">')
//     author.append('<a onclick="$(this).parent().remove()">&times;</a>')
//     author.appendTo(el.find('.author-list'))
// }

function toggleRequired(el) {
    const element = $(el)
    const input = element.find('input')
    if (element.hasClass('required')) {
        input.val(input.val().replace('*', ''))
    } else {
        input.val(input.val() + '*')
    }
    element.toggleClass('required')
}
var authordiv = $('.author-list')
if (authordiv.length > 0) {
    authordiv.sortable({});
}