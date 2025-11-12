

var SCIENTISTS;
$(document).ready(function () {
    var scientists = $('#scientist-list option').map(function (index, item) {
        return item.value
    })
    SCIENTISTS = Object.values(scientists)

    // scroll to active sidebar menu if available
    if ($('.sidebar-menu a.active').length !== 0) {
        $('.sidebar').animate({
            scrollTop: $(".sidebar-menu a.active").offset().top - 200
        }, 100);
    }
})

function initQuill(element) {

    var quill = new Quill(element, {
        modules: {
            toolbar: [
                ['italic', 'underline'],
                [{ script: 'super' }, { script: 'sub' }]
            ]
        },
        formats: ['italic', 'underline', 'script'],
        placeholder: '',
        theme: 'snow' // or 'bubble'
    });

    quill.on('text-change', function () {
        var str = $(element).find('.ql-editor p').html()
        $(element).next().val(str)
        // TODO: add doubletCheck() with underscore
    });

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

function quillEditor(selector) {
    const quill = new Quill('#' + selector + '-quill', {
        modules: {
            toolbar: [
                [{
                    header: [1, 2, 3, false]
                }],
                ['bold', 'italic', 'underline'],
                [{
                    'list': 'ordered'
                },
                {
                    'list': 'bullet'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }],
                ['link', 'image'],
                ['clean']
            ],
        },
        formats: ['italic', 'bold', 'underline', 'script', 'link', 'image', 'list', 'header'],
        placeholder: lang('Start typing here ...', 'Hier tippen ...'),
        theme: 'snow', // or 'bubble' 
    });
    quill.on('text-change', (delta, oldDelta, source) => {
        document.getElementById(selector).value = quill.getSemanticHTML();
    });
}


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
    if (title === null) title = lang("Error", "Fehler")
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "danger",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function toastSuccess(msg = "", title = null) {
    if (title === null) title = lang("Success", "Erfolg")
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "success",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function toastWarning(msg = "", title = null) {
    if (title === null) title = lang("Warning", "Achtung")
    osirisJS.initStickyAlert({
        content: msg,
        title: title,
        alertType: "signal",
        hasDismissButton: true,
        timeShown: 10000
    })
}
function toastInfo(msg = "", title = null) {
    if (title === null) title = lang("Info", "Information")
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
function lang(en, de = null) {
    if (de === null) return en;
    var language = getCookie('osiris-language');
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
        content: lang('Sorry, but this button does not work yet.', 'Sorry, aber der Knopf funktioniert noch nicht.'),
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
                    theme: 'snow' // or 'bubble'
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
    if (fav) {
        var favlist = fav.split(',')
        // console.log(favlist);
        const index = favlist.indexOf(id);
        if (index > -1) {
            favlist.splice(index, 1);
            console.info("remove");
            updateCart(false)
        } else {
            if (favlist.length > 30) {
                toastError(lang('You can have no more than 30 items in your cart.', 'Du kannst nicht mehr als 30 Aktivitäten in deinem Einkaufswagen haben.'))
                return;
            }
            favlist.push(id)
            console.info("add");
            updateCart(true)
        }
        fav = favlist.join(',')
    } else {
        fav = id
        console.info("add");
        updateCart(true)
    }
    osirisJS.createCookie('osiris-cart', fav, 30)
    if (el === null) {
        location.reload()
    } else {
        $(el).find('i').toggleClass('ph ph-fill').toggleClass('ph').toggleClass('text-success')
    }
    // setTimeout(function () {
    //     $(el).find('i').removeClass('animate__flip')
    //     // animate__headShake
    // }, 1000)
}



function orderByAttr(a, b) {
    a = $(a).attr('data-value')
    b = $(b).attr('data-value')
    if (a === undefined) return -1
    if (b === undefined) return 1
    return a.localeCompare(b)
}
