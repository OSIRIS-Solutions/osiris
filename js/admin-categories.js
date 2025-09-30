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