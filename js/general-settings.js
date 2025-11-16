function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    // hash
    window.location.hash = 'section-' + key;
}

function addValuesRow() {
    $('#possible-positions').append(`
        <tr>
            <td class="w-50">
                <i class="ph ph-dots-six-vertical text-muted handle"></i>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions][]" required>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions_de][]">
            </td>
            <td>
                <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
            </td>
        </tr>
    `);
}

$(document).ready(function () {

    // read hash to navigate
    var hash = window.location.hash;
    if (hash && hash.startsWith('#section-')) {
        hash = hash.replace('#section-', '')
        // check if hash is a valid section
        if ($(`section#${hash}`).length > 0) {
            navigate(hash);
        }
    }

    $('#possible-positions').sortable({
        handle: ".handle",
        // change: function( event, ui ) {}
    });
});
