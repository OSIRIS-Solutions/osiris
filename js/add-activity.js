let SETTINGS = {};
const TYPES = {
    "journal-article": "article",
    "magazine-article": "magazine",
    "book-chapter": "chapter",
    publication: "article",
    "doctoral-thesis": "students",
    "master-thesis": "students",
    "bachelor-thesis": "students",
    "guest-scientist": "guests",
    "lecture-internship": "guests",
    "student-internship": "guests",
    reviewer: "review",
    editor: "editorial",
    monograph: "book",
    misc: "misc",
    "edited-book": "book",
};


let SELECTED_CAT = null;
let SELECTED_TYPE = null;
let DOIDATA = null;

function togglePubType(type, callback = () => { }) {
    type = type.trim().toLowerCase().replace(" ", "-");
    // check if type exists:
    if ($(".select-btns").find('.btn[data-subtype="' + type + '"]').length === 0) {
        type = TYPES[type] ?? type;
    }
    console.log(type);

    $("#type").val(type);
    $("#type-description").empty();
    $("#type-examples").empty();

    // select data
    SELECTED_CAT = null;
    SELECTED_TYPE = null;

    $.ajax({
        type: "GET",
        url: ROOTPATH + "/settings/activities",
        data: {
            type: type,
        },
        dataType: "json",
        success: function (response) {
            const data = response.data
            // console.log(response);
            SELECTED_CAT = data.category;
            SELECTED_TYPE = data.type;

            const SELECTED_MODULES = SELECTED_TYPE.modules;

            $("#type").val(SELECTED_CAT.id);
            $("#subtype").val(SELECTED_TYPE.id);

            // add description (always visible)
            var descr = "";
            descr += lang(
                SELECTED_TYPE.description ?? "",
                SELECTED_TYPE.description_de ?? ""
            );
            if (descr != "") descr = "<i class='ph ph-info'></i> " + descr;
            $("#type-description").html(descr);

            var examples = SELECTED_TYPE.example ?? "";
            $("#type-examples").html(examples);

            // show correct subtype buttons
            var form = $("#publication-form");
            form.find(".select-btns").hide();
            form.find('.select-btns[data-type="' + SELECTED_CAT.id + '"]').show();

            $(".select-btns").find(".btn").removeClass("active");
            $(".select-btns")
                .find(
                    '.btn[data-subtype="' +
                    SELECTED_TYPE.id +
                    '"],.btn[data-type="' +
                    SELECTED_CAT.id +
                    '"]'
                )
                .addClass("active");

            $.ajax({
                type: "GET",
                url: ROOTPATH + "/settings/modules",
                data: {
                    id: ID,
                    draft: DRAFT ?? false,
                    modules: SELECTED_MODULES,
                    copy: COPY ?? false,
                    conference: CONFERENCE ?? false,
                    type: SELECTED_TYPE.id,
                },
                dataType: "html",
                success: function (response) {
                    // console.log(response);
                    $("#data-modules").html(response);
                    // if (SELECTED_MODULES.includes('title')) {
                    $(".title-editor").each(function (el) {
                        var element = this;
                        var authordiv = $(".author-list");
                        if (authordiv.length > 0) {
                            authordiv.sortable({});
                        }
                    });
                    // restore form data if not empty
                    if (DOIDATA !== null) {
                        fillForm(DOIDATA)
                    } else if (typeof callback === "function") {
                        callback();
                    }

                    $("#data-modules")
                        .find(":input")
                        .on("change", function () {
                            doubletCheck();
                        });
                },
            });

            form.slideDown();
            return;
        },
    });

    // show form
}

const convertArrayToObject = (array, key) => {
    if (!Array.isArray(array)) return array;
    const initialValue = {};
    return array.reduce((obj, item) => {
        return {
            ...obj,
            [item[key]]: item,
        };
    }, initialValue);
};

function activeButtons(type) {
    $(".select-btns").find(".btn").removeClass("active");

    $("#" + type + "-btn").addClass("active");
    switch (type) {
        case "publication":
            $("#article-btn").addClass("active");
            break;
        case "review":
            $("#review2-btn").addClass("active");
            break;
        case "misc":
            $("#misc-once-btn").addClass("active");
            break;

        case "students":
            $("#students2-btn").addClass("active");
            break;
        case "guests":
            $("#students-btn").addClass("active");
            break;
        case "editorial":
        case "grant-rev":
        case "thesis-rev":
            $("#review-btn").addClass("active");
            break;
        case "misc-once":
        case "misc-annual":
            $("#misc-btn").addClass("active");
            break;
        case "article":
        case "magazine":
        case "book":
        case "chapter":
        case "preprint":
        case "dissertation":
        case "others":
            $("#publication-btn").addClass("active");
            break;
        default:
            break;
    }
}


function affiliationCheck() {
    $('.affiliation-warning').show()
    $('form').each(function () {
        var form = $(this)
        form.find('.author input').each(function () {
            var value = $(this).val().split(';')
            if (value[2] == 1) {
                form.find('.affiliation-warning').hide()
                return false;
            }
        })
    })
}

function addAuthorDiv(lastname, firstname, aoi = false, editor = false, el = null) {
    if (editor) {
        el = $('#editor-list')
    } else {
        el = $('#author-list')
    }
    if (lastname === undefined) lastname = ""
    if (firstname === undefined) firstname = ""
    var author = $('<div class="author">')
        .on('dblclick', function () {
            toggleAffiliation(this)
        })
        .html(lastname + ', ' + firstname);
    var val = 0
    if (aoi) {
        val = 1
        author.addClass('author-aoi')
    }
    val = lastname.trim() + ';' + firstname.trim() + ';' + val

    var classname = editor ? "editors" : "authors";
    author.append('<input type="hidden" name="values[' + classname + '][]" value="' + val + '">')
    author.append('<a onclick="removeAuthor(event, this)">&times;</a>')
    author.appendTo(el)
}

function toggleAffiliation(item) {
    var old = $(item).find('input').val().split(';')
    if ($(item).hasClass('author-aoi')) {
        old[2] = 0
    } else {
        old[2] = 1
    }
    // console.log(old);
    $(item).find('input').val(old.join(';'))
    $(item).toggleClass('author-aoi')
    affiliationCheck();
}

function addAuthor(event, editor = false) {
    if (editor) {
        var el = $('#add-editor')
    } else {
        var el = $('#add-author')
    }
    var data = el.val()
    // console.log(data);
    if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
        event.preventDefault();
        const match = (SCIENTISTS.indexOf(data) != -1)
        var value = data.split(',')
        // console.log(data);
        if (value.length !== 2) {
            toastError('Author name must be formatted like this: Lastname, Firstname')
            return;
        }
        addAuthorDiv(value[0], value[1], match, editor)

        $(el).val('')
        affiliationCheck();
        return false;
    }
}
function removeAuthor(event, el) {
    event.preventDefault();
    $(el).parent().remove()
    affiliationCheck();
}


function verifyForm(event, form) {
    // event.preventDefault()
    form = $(form)
    var correct = true
    var errors = []
    form.find(':input[name]').each(function () {
        //retrieve field name and value from the DOM
        var input = $(this)
        var selector = input
        if (input.attr('id') == 'title') {
            selector = $('.title-editor')
        }
        if ((input.prop('required') && !input.prop('disabled'))) {
            console.log($(this).val());
            if (!$(this).val() || $(this).val().length === 0) {
                selector.removeClass('is-valid').addClass('is-invalid')

                var name = input.attr('name').replace(/^values\[(.+?)\](?:\[\])?$/, '$1');
                // try to get name of the label if available
                if (input.attr('id')){
                    let label = $('label[for="' + input.attr('id') + '"]');
                    if (label.length > 0) {
                        name = label.text().trim();
                    } 
                }
                if (name == 'journal_id') return;
                correct = false;
                errors.push(name)
            } else {
                selector.addClass('is-valid').removeClass('is-invalid');
            }
        }
    });


    // check if authors are defined
    if ($('#author-widget').length > 0) {
        if ($('.author-list').find('.author').length === 0) {
            $('#author-widget').addClass('is-invalid').removeClass('is-valid')
            correct = false
            errors.push("Authors")
        } else {
            $('#author-widget').addClass('is-valid').removeClass('is-invalid')
        }
    }

    // check if topics are defined and topic is required
    if ($('#topic-widget').length > 0 && SELECTED_TYPE['topics-required']) {
        if ($('#topic-widget').find('input:checked').length === 0) {
            $('#topic-widget').addClass('is-invalid').removeClass('is-valid')
            correct = false
            errors.push("Topics")
        }
    }

    if (correct) {
        return true;
    }

    event.preventDefault()

    var msg = lang('The following fields cannot be empty: ', 'Die folgenden Felder dürfen nicht leer sein: ');
    msg += errors.join(', ')
    toastError(msg)
    return false
}

function saveDraft(){
    // no validation needed, just change the route and save
    var form = $('#activity-form')
    form.attr('action', ROOTPATH + '/crud/activities/save-draft')
    form.submit()
}

function loadDrafts(){
    $('#drafts-modal #content').html('<div class="loader show"></div>')
    // open modal
    $('#drafts-modal').addClass('show')
    $.ajax({
        type: "GET",
        url: ROOTPATH + '/activities/drafts',
        data: {
            frame: true
        },
        dataType: "html",
        success: function (response) {
            $('#drafts-modal #content').html(response)
        },
        error: function (response) {
            $('#drafts-modal #content').html('<p>' + lang('Error loading drafts', 'Fehler beim Laden der Entwürfe') + '</p>')
        }
    })
}

function getTeaching(name) {
    // console.log(name);
    const SUGGEST = $('#teaching-suggest')
    SUGGEST.empty()
    var url = ROOTPATH + '/api/teaching'
    var data = {
        search: name,
        limit: 10
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",

        url: url,
        success: function (response) {
            var teaching = [];
            response.data.forEach(j => {

                teaching.push({
                    title: j.title,
                    affiliation: j.affiliation,
                    contact_person: j.contact_person,
                    module: j.module,
                    id: j._id['$oid']
                })
            });
            if (teaching.length === 0) {
                SUGGEST.append('<tr><td colspan="3">' + lang('Module not found in OSIRIS.', 'Modul nicht in OSIRIS gefunden.') + '</tr></td>')
                SUGGEST.append('<tr><td colspan="3"><a href="' + ROOTPATH + '/teaching#add-teaching" class="btn osiris">' + lang('Add new module', 'Neues Modul anlegen') + '</a></tr></td>')
                window.location.replace('#teaching-select')
            } else {
                teaching.forEach((j) => {
                    // console.log(j);
                    var row = $('<tr>')

                    var button = $('<button class="btn" title="select">')
                    button.html('<i class="ph ph-check text-success"></i>')
                    button.on('click', function () {
                        selectTeaching(j);
                    })
                    row.append($('<td class="w-50">').append(button))

                    var data = $('<td>')
                    data.append(`<h5 class="m-0"><span class="highlight-text" >${j.module}</span> ${j.title}</h5>`)
                    data.append(`<span class="float-right">${j.contact_person}</span>`)
                    data.append(`<span class="text-muted">${j.affiliation}</span>`)
                    row.append(data)

                    SUGGEST.append(row)
                })
                if (teaching.length === 1) {
                    selectTeaching(teaching[0])
                    toastSuccess(lang('Module <code class="code">' + teaching[0].title + '</code> selected.', 'Modul <code class="code">' + teaching[0].title + '</code> ausgewählt.'), lang('Module found', 'Modul gefunden'))
                } else {
                    window.location.replace('#teaching-select')
                }
            }


            // console.log(teaching);
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}


function selectTeaching(j) {

    // console.log(j);
    var field = $('#selected-teaching')
    field.empty()
    field.append(`<h5 class="m-0"><span class="highlight-text" >${j.module}</span> ${j.title}</h5>`)
    // field.append(`<span class="float-right">${j.contact_person}</span>`)
    field.append(`<span class="text-muted">${j.affiliation}</span>`)

    $('#module_id').val(j.id['$oid'] ?? j.id)
    $('#module').val(j.module)
    $('#module-title').val(j.title)

    window.location.replace('#')
}



var doubletFound = false;
function doubletCheck() {

    var form = objectifyForm($('#activity-form'))
    // console.log(form);
    if (form['values[start]']) {
        var start = form['values[start]'].split('-')
        form['values[year]'] = start[0]
        form['values[month]'] = start[1]
    }
    if (!form['values[title]'] || !form['values[year]'] || !form['values[month]']) return

    $.ajax({
        type: "GET",
        data: form,
        dataType: "html",
        url: ROOTPATH + "/check-duplicate",
        success: function (data) {
            // console.log(data);
            if (data != 'false') {
                var doublet = $('#doublet-found')
                doublet.show()
                    .find('p').html(data)
                // doublet.find('a')
                // .attr('href', ROOTPATH + '/activities/' + data[])
                $('.loader').removeClass('show')
                if (!doubletFound) {
                    doubletFound = true;
                    // toastWarning(lang('Possible douplicate found.', 'Mögliche Dublette gefunden.'))
                }

            } else {
                $('#doublet-found').hide()
            }
            // toastSuccess(data)
        },
        error: function (response) {
            // console.log(response.responseText)
            toastError(response.responseText)
        }
    })
}


function getPublication(id) {
    id = id.trim()
    $('#id-exists').hide()
    $('.loader').addClass('show')
    if (/(10\.\d{4,5}\/[\S]+[^;,.\s])$/.test(id)) {
        id = id.match(/(10\.\d{4,5}\/[\S]+[^;,.\s])$/)[0]
        if (!UPDATE) {
            checkDuplicateID(id, 'doi')
        } else {
            getDOI(id)
        }
    } else if (/^(\d{7,8})$/.test(id)) {
        if (!UPDATE) {
            checkDuplicateID(id, 'pubmed')
        } else {
            getPubmed(id)
        }
    } else {
        toastError('This is neither DOI nor Pubmed-ID. Sorry.');
        $('.loader').removeClass('show')
        return
    }
}


function checkDuplicateID(id, type = 'doi') {
    $.ajax({
        type: "GET",
        data: { id: id, type: type },
        dataType: "html",
        url: ROOTPATH + "/check-duplicate-id",
        success: function (data) {
            // console.log(data);
            if (data == 'true') {
                // toastError(
                //     lang(
                //         'This DOI/Pubmed-ID already exists in the database!',
                //         'Diese DOI/Pubmed-ID existiert bereits in der Datenbank!'
                //     ))
                $('#id-exists').show()
                    .find('a')
                    .attr('href', ROOTPATH + '/activities/' + type + '/' + id)
                $('.loader').removeClass('show')
            } else {
                if (type == 'doi')
                    getDOI(id);
                if (type == 'pubmed')
                    getPubmed(id);
            }
            // toastSuccess(data)
        },
        error: function (response) {
            // console.log(response.responseText)
            toastError(response.responseText)
        }
    })
}

function getJournal(name) {
    // console.log(name);
    const SUGGEST = $('#journal-suggest')
    SUGGEST.empty()
    var url = ROOTPATH + '/api/journal'
    // https://api.clarivate.com/apis/wos-journals/v1/journals?q=matrix biology
    var data = {
        search: name,
        limit: 10
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",

        url: url,
        success: function (response) {
            var journals = [];
            response.data.forEach(j => {

                journals.push({
                    journal: j.journal,
                    issn: j.issn,
                    abbr: j.abbr,
                    publisher: j.publisher,
                    id: j._id['$oid']
                })
            });
            if (journals.length === 0) {
                SUGGEST.append('<tr><td colspan="3">' + lang('Journal not found in OSIRIS. Starting search in OpenAlex ...', 'Journal nicht in OSIRIS gefunden. Starte Suche im OpenAlex-Katalog ...') + '</tr></td>')
                getJournalAlex(name)
                window.location.replace('#journal-select')
            } else {
                journals.forEach((j) => {
                    // console.log(j);
                    var row = $('<tr>')

                    var button = $('<button class="btn" title="select">')
                    button.html('<i class="ph ph-check text-success"></i>')
                    button.on('click', function () {
                        selectJournal(j);
                    })
                    row.append($('<td class="w-50">').append(button))

                    var data = $('<td>')
                    data.append(`<h5 class="m-0">${j.journal}</h5>`)
                    data.append(`<span class="float-right text-muted">${j.publisher}</span>`)
                    data.append(`<span class="text-muted">ISSN: ${j.issn.join(', ')}</span>`)
                    row.append(data)

                    SUGGEST.append(row)
                })
                if (journals.length === 1) {
                    selectJournal(journals[0])
                    toastSuccess(lang('Journal <code class="code">' + journals[0].journal + '</code> selected.', 'Journal <code class="code">' + journals[0].journal + '</code> ausgewählt.'), lang('Journal found', 'Journal gefunden'))
                } else {
                    window.location.replace('#journal-select')
                }
            }
            var row = $('<tr>')
            var button = $('<button class="btn">')
            button.html(lang('Search in OpenAlex Catalog', 'Suche im OpenAlex-Katalog'))
            button.on('click', function () {
                getJournalAlex(name)
            })
            row.append($('<td colspan="3">').append(button))
            SUGGEST.append(row)
            // window.location.replace('#journal-select')


            // console.log(journals);
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}

function selectJournal(j, n = false) {

    // console.log(j);
    var field = $('#selected-journal')
    if (n) {
        $.ajax({
            type: "POST",
            data: {
                values: j
            },
            dataType: "json",
            url: ROOTPATH + '/crud/journal/create',
            success: function (response) {
                // $('.loader').removeClass('show')
                // console.log(response);
                if (response.msg) {
                    toastWarning(response.msg)
                    selectJournal(response, false)
                    return;
                } else {

                    field.empty()
                    field.append(`<h5 class="m-0">${j.journal}</h5>`)
                    field.append(`<span class="float-right">${j.publisher}</span>`)
                    field.append(`<span class="text-muted">ISSN: ${j.issn.join(', ')}</span>`)


                    $('#journal_id').val(response.id['$oid'])
                    // $('#journal_rev_id').val(response.id['$oid'])
                    $('#journal').val(j.journal)
                    // $('#journal-input').val(j.journal)
                    // $('#issn').val(j.issn.join(' '))
                }
            },
            error: function (response) {
                $('.loader').removeClass('show')
                toastError(response.responseText)
            }
        })
    } else {
        field.empty()
        field.append(`<h5 class="m-0">${j.journal}</h5>`)
        field.append(`<span class="float-right">${j.publisher ?? ''}</span>`)
        field.append(`<span class="text-muted">ISSN: ${j.issn.join(', ')}</span>`)

        $('#journal_id').val(j.id['$oid'] ?? j.id)
        // $('#journal_rev_id').val(j.id['$oid'] ?? j.id)
        $('#journal').val(j.journal)
        // $('#journal-input').val(j.journal)
        // $('#issn').val(j.issn.join(' '))
    }
    window.location.replace('#')
}

function getJournalAlex(name) {
    //1664-462X
    var url = 'https://api.openalex.org/sources'
    const SUGGEST = $('#journal-suggest')
    var data = { mailto: 'juk20@dsmz.de' }
    if (name.match(/\d{4}-?\d{3}[xX\d]/)) {
        // issn search
        url += '/issn:' + name
    } else {
        url += '?search=' + name
        // show only results with ISSN
        data['filter'] = 'has_issn:true'
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",
        url: url,
        success: function (result) {
            // console.log(result);
            var journals = [];

            if (result.results !== undefined) {
                // name search result (many entries)
                result.results.forEach(j => {
                    journals.push({
                        journal: j.display_name,
                        issn: j.issn,
                        abbr: j.medlineta,
                        publisher: j.host_organization_name,
                        openalex: j.id,
                        if: j.summary_stats['2yr_mean_citedness'],
                        oa: j.is_oa
                    })
                });
            } else if (result.display_name !== undefined) {
                // issn search result (only one entry)
                j = result
                journals.push({
                    journal: j.display_name,
                    issn: j.issn,
                    abbr: (j.abbreviated_title ?? '').replace('.', ''),
                    publisher: j.host_organization_name,
                    openalex: j.id,
                    if: j.summary_stats['2yr_mean_citedness'],
                    oa: j.is_oa
                })
            }

            journals.forEach((j) => {
                var row = $('<tr>')

                var button = $('<button class="btn" title="select">')
                button.html('<i class="ph ph-check text-success"></i>')
                button.on('click', function () {
                    selectJournal(j, true);
                })
                row.append($('<td class="w-50">').append(button))

                var data = $('<td>')
                data.append(`<h5 class="m-0">${j.journal}</h5>`)
                data.append(`<span class="float-right">${j.publisher}</span>`)
                data.append(`<span class="text-muted">ISSN: ${j.issn.join(', ')}</span>`)
                row.append(data)

                SUGGEST.append(row)
            })
            if (journals.length === 0) {
                SUGGEST.append('<tr><td>' + lang('Journal not found in OpenAlex. Maybe you want to add a magazine article?', 'Journal nicht in OpenAlex gefunden. Wolltest du vielleicht einen Magazin-Artikel hinzufügen?') + '</tr></td>')
            }

            // console.log(journals);
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}

function getJournalNLM(name) {
    var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi'
    const SUGGEST = $('#journal-suggest')
    // SUGGEST.empty()
    // https://api.clarivate.com/apis/wos-journals/v1/journals?q=matrix biology
    var data = {
        db: 'nlmcatalog',
        term: '("' + name + '"[title]) AND (ncbijournals[filter])',
        retmode: 'json',
        usehistory: 'y'
    }
    if (name.match(/\d{4}-?\d{3}[xX\d]/)) {
        // issn search
        data.term = name + ' AND (ncbijournals[filter])'
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",

        url: url,
        success: function (response) {
            var env = response.esearchresult.webenv
            var key = response.esearchresult.querykey

            var data = {
                retmode: 'json',
                db: 'nlmcatalog',
                query_key: key,
                WebEnv: env
            }
            var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi'

            $.ajax({
                type: "GET",
                data: data,
                dataType: "json",
                url: url,
                success: function (result) {
                    // console.log(result);
                    var journals = [];

                    for (const id in result.result) {
                        if (id == 'uids') continue;
                        const j = result.result[id];
                        // console.log(j);
                        var issn = [];
                        j.issnlist.forEach(item => {
                            issn.push(item.issn)
                        });
                        if (issn.length === 0) continue;
                        var name = j.titlemainlist[0].title
                        journals.push({
                            journal: name.slice(0, name.length - 1),
                            issn: issn,
                            abbr: j.medlineta,
                            publisher: j.publicationinfolist[0].publisher,
                            nlmid: id
                        })
                    }
                    journals.forEach((j) => {
                        var row = $('<tr>')

                        var button = $('<button class="btn" title="select">')
                        button.html('<i class="ph ph-check text-success"></i>')
                        button.on('click', function () {
                            selectJournal(j, true);
                        })
                        row.append($('<td class="w-50">').append(button))

                        var data = $('<td>')
                        data.append(`<h5 class="m-0">${j.journal}</h5>`)
                        data.append(`<span class="float-right">${j.publisher}</span>`)
                        data.append(`<span class="text-muted">ISSN: ${j.issn.join(', ')}</span>`)
                        row.append(data)

                        SUGGEST.append(row)
                    })
                    if (journals.length === 0) {
                        SUGGEST.append('<tr><td>' + lang('Journal not found in NLM. Maybe you want to add a magazine article?', 'Journal nicht in NLM gefunden. Wolltest du vielleicht einen Magazin-Artikel hinzufügen?') + '</tr></td>')
                    }

                    // console.log(journals);
                },
                error: function (response) {
                    toastError(response.responseText)
                    $('.loader').removeClass('show')
                }
            })


        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}

function getPubmed(id) {
    var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi'
    var data = {
        db: 'pubmed',
        id: id,
        retmode: 'json'
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",

        url: url,
        success: function (data) {
            // console.log(data);
            var pmid = data.result.uids[0]
            var pub = data.result[pmid]

            // var date = pub.pubdate
            var doi = ""
            pub.articleids.forEach(el => {
                if (el.idtype == 'doi') doi = el.value
            });

            var date = new Date(pub.sortpubdate)

            var authors = [];
            var editors = [];
            pub.authors.forEach((a, i) => {
                var name = a.name.split(' ', 2)
                var pos = 'middle'
                if (i == 0) pos = 'first'
                else if (i == pub.authors.length - 1) pos = 'last'
                name = {
                    family: name[0],
                    given: name[1].split('').join(' '),
                    position: pos
                }
                if (a.authtype == "Author") {
                    authors.push(name)
                } else if (a.authtype == "Editor") {
                    editors.push(name)
                }
            });


            var pubdata = {
                title: pub.title,
                // first_authors: pub.first_authors,
                authors: authors,
                year: date.getFullYear(),
                month: date.getMonth() + 1,
                day: date.getDate(),
                type: pub.doctype == 'chapter' ? 'book' : pub.pubtype[0],
                journal: pub.fulljournalname,
                // issn: (pub.ISSN ?? []).join(' '),
                issue: pub.issue,
                volume: pub.volume,
                pages: pub.pages,
                doi: doi,
                pubmed: pmid,
                book: pub.booktitle,
                edition: pub.edition,
                publisher: pub.publishername,
                city: pub.publisherlocation,
                editors: editors,
                // open_access: pub.open_access,
                epub: pub.pubstatus == 10,
            }
            toggleForm(pubdata)
            $('.loader').removeClass('show')
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}


function getDOI(doi) {
    url = "https://api.crossref.org/works/v1/" + doi + '?mailto=juk20@dsmz.de'
    $.ajax({
        type: "GET",
        // data: data,
        dataType: "json",
        // cors: true ,
        //   contentType:'application/json',
        //   secure: true,
        //   headers: {
        //     'Access-Control-Allow-Origin': '*',
        //   },
        url: url,
        success: function (data) {
            var pub = data.message
            console.log(pub);
            var date = getPublishingDate(pub)
            var authors = [];
            // var editors = [];
            var first = 1
            if (pub.author === undefined && pub.editor !== undefined) {
                pub.author = pub.editor
            }
            if (pub.author !== undefined) {
                pub.author.forEach((a, i) => {
                    var aoi = false
                    a.affiliation.forEach(e => {
                        // check if AFFILIATION_REGEX matches the affiliation
                        if (AFFILIATION_REGEX.test(e.name)) {
                            aoi = true
                        }
                    })
                    if (a.sequence == "first") {
                        first = i + 1
                    }
                    var pos = a.sequence ?? 'middle';
                    if (i === 0) pos = 'first'
                    else if (i == pub.author.length - 1) pos = 'last'
                    var name = {
                        family: a.family ?? a.name,
                        given: a.given,
                        affiliation: aoi,
                        position: pos
                    }
                    authors.push(name)
                });
            }
            let editors = [];
            if (pub.editor !== undefined) {
                pub.editor.forEach((a, i) => {
                    var aoi = false
                    a.affiliation.forEach(e => {
                        // check if AFFILIATION_REGEX matches the affiliation
                        if (AFFILIATION_REGEX.test(e.name)) {
                            aoi = true
                        }
                    })
                    var name = {
                        family: a.family ?? a.name,
                        given: a.given,
                        affiliation: aoi
                    }
                    editors.push(name)
                });
            }
            var issue = null
            if (pub['journal-issue'] !== undefined) issue = pub['journal-issue'].issue

            var funder = [];
            if (pub.funder !== undefined && pub.funder !== null) {
                pub.funder.forEach(f => {
                    if (f.award) {
                        funder = funder.concat(f.award)
                    }
                });
            }
            // console.log(funder);

            var pages = pub.page
            if (pub.page === undefined || pub.page === null) {
                pages = pub['article-number'] ?? null;
            }

            var abstract = pub.abstract ?? ''
            if (abstract !== '') {
                abstract = abstract.replace(/<[^>]*>?/gm, '')
                // remove line breaks and multiple spaces
                abstract = abstract.replace(/\s\s+/g, ' ')
                // remove leading "abstract"
                abstract = abstract.replace(/^abstract/i, '').trim()
            }

            var pubdata = {
                title: pub.title[0],
                first_authors: first,
                authors: authors,
                editors: editors,
                year: date[0],
                month: date[1],
                day: date[2],
                type: pub.type,
                journal: pub['container-title'][0],
                issn: (pub.ISSN ?? []).join(' '),
                issue: issue,
                volume: pub.volume,
                pages: pages,
                doi: pub.DOI,
                // pubmed: null,
                abstract: abstract,
                edition: pub['edition-number'] ?? null,
                subtitle: pub.subtitle ?? null,
                'pub-language': pub.language ?? null,
                publisher: pub['publisher'] ?? pub['publisher-name'],
                isbn: pub['ISBN'],
                city: pub['publisher-location'],
                // open_access: pub.open_access,
                epub: (pub['published-print'] === undefined && pub['published-online'] === undefined),
                funding: funder.join(',')
            }
            // update form data in case of selecting another type
            DOIDATA = pubdata
            toggleForm(pubdata)
            getOpenAccessStatus(doi)
            $('.loader').removeClass('show')
        },
        error: function (response) {
            // toastError(response.responseText)
            $('.loader').removeClass('show')
            toastWarning('DOI was not found in CrossRef. I am looking in DataCite now.')
            getDataciteDOI(doi)
        }
    })
}


function getOpenAccessStatus(doi) {
    var url = 'https://api.openalex.org/works'
    var data = { mailto: 'juk20@dsmz.de' }
    url += '/https://doi.org/' + doi

    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",
        url: url,
        success: function (pub) {
            fillOpenAccess(pub.open_access.oa_status ?? false)
        }
    })
}

function getOpenAlexDOI(doi) {
    var url = 'https://api.openalex.org/works'
    var data = { mailto: 'juk20@dsmz.de' }
    url += '/https://doi.org/' + doi

    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",
        url: url,
        success: function (pub) {
            // var pub = data
            // console.log(pub);

            var date = pub.publication_date.split('-')


            var authors = [];
            // var editors = [];
            var first = 1
            if (pub.authorships === undefined && pub.editor !== undefined) {
                pub.authorships = pub.editor
            }
            if (pub.authorships !== undefined) {
                pub.authorships.forEach((a, i) => {
                    var aoi = false
                    a.institutions.forEach(e => {
                        // if (e.display_name.includes(AFFILIATION)) {
                        //     aoi = true
                        // }
                        // check if AFFILIATION_REGEX matches the affiliation
                        if (AFFILIATION_REGEX.test(e.display_name)) {
                            aoi = true
                        }
                    })
                    var pos = a.author_position ?? 'middle';
                    if (pos == "first") {
                        first = i + 1
                    } else if (i == pub.authorships.length - 1) pos = 'last'
                    var name = {
                        family: a.author.display_name,
                        given: '',
                        affiliation: aoi,
                        position: pos
                    }
                    authors.push(name)
                });
            }

            var journal = pub.primary_location
            var pages = pub.biblio.first_page
            if (pub.biblio.last_page) pages += '-' + pub.biblio.last_page
            var pubdata = {
                title: pub.title,
                first_authors: first,
                authors: authors,
                year: date[0],
                month: date[1] ?? null,
                day: date[2] ?? null,
                type: pub.type,
                journal: journal.source.display_name,
                issn: (journal.source.issn ?? []).join(' '),
                issue: pub.biblio.issue,
                volume: pub.biblio.volume,
                pages: pages,
                doi: doi,
                // pubmed: null,
                // edition: pub.edition,
                // publisher: pub['publisher'] ?? pub['publisher-name'],
                // isbn: pub['isbn'],
                // city: pub['publisher-location'],
                open_access: pub.open_access.oa_status,
                epub: (journal.version !== 'publishedVersion'),
            }
            toggleForm(pubdata)
            $('.loader').removeClass('show')
        },
        error: function (response) {
            // toastError(response.responseText)
            $('.loader').removeClass('show')
            toastWarning('DOI was not found in CrossRef. I am looking in DataCite now.')
            getDataciteDOI(doi)
        }
    })
}

function getDataciteDOI(doi) {
    url = "https://api.datacite.org/dois/" + doi //+ '?mailto=juk20@dsmz.de'
    $('.loader').addClass('show')

    var dataCiteTypes = {
        'book': 'book',
        'bookchapter': 'chapter',
        'journal': 'article',
        'journalarticle': 'article',
        'conferencepaper': 'article',
        'conferenceproceeding': 'article',
        'dissertation': 'dissertation',
        'preprint': 'preprint',
        'software': 'software',
        'computationalnotebook': 'software',
        'model': 'software',
        'datapaper': 'dataset',
        'dataset': 'dataset',
        'peerreview': 'review',
        'audiovisual': 'misc',
        'collection': 'misc',
        'event': 'misc',
        'image': 'misc',
        'report': 'others',
        'interactiveresource': 'misc',
        'outputmanagementplan': 'misc',
        'physicalobject': 'misc',
        'service': 'misc',
        'sound': 'misc',
        'standard': 'misc',
        'text': 'misc',
        'workflow': 'misc',
        'other': 'misc',
        'presentation': 'lecture',
        'poster': 'poster'
    }

    $.ajax({
        type: "GET",
        // data: data,
        dataType: "json",
        // cors: true ,
        //   contentType:'application/json',
        //   secure: true,
        //   headers: {
        //     'Access-Control-Allow-Origin': '*',
        //   },
        url: url,
        success: function (data) {
            var pub = data.data.attributes
            // console.log(pub);

            var date = ''
            if (pub.dates[0] !== undefined) {
                dateSplit = getDate(pub.dates[0].date.split('-'))
                date = strDate(dateSplit)
            } else {
                dateSplit = [pub.publicationYear, 1, null]
                date = pub.publicationYear + "-01-01"
            }
            // console.log(dateSplit, date);

            var authors = [];
            // var editors = [];
            var first = 1
            pub.creators.forEach((a, i) => {
                var aoi = false
                a.affiliation.forEach(e => {
                    // if (e.includes(AFFILIATION)) {
                    //     aoi = true
                    // }
                    // check if AFFILIATION_REGEX matches the affiliation
                    if (AFFILIATION_REGEX.test(e)) {
                        aoi = true
                    }
                })
                if (a.sequence == "first") {
                    first = i + 1
                }
                var pos = a.sequence ?? 'middle'
                if (i === 0) pos = 'first'
                else if (pub.creator && pub.creator.length && i == pub.creator.length - 1) pos = 'last'
                // console.log(pos);
                var name = {
                    family: a.familyName,
                    given: a.givenName,
                    affiliation: aoi,
                    position: pos
                }
                authors.push(name)
            });
            var type = ''
            if (pub.types.resourceTypeGeneral !== undefined) {
                type = pub.types.resourceTypeGeneral.toLowerCase()
            }
            type = dataCiteTypes[type] ?? type;

            var resType = pub.types.resourceType
            if (resType !== undefined && dataCiteTypes[resType.toLowerCase()] !== undefined) {
                type = dataCiteTypes[resType.toLowerCase()]
            }
            console.info(type);
            let title = pub.titles[0].title
            if (title === undefined) {
                title = ''
            }
            let subtitle = ''
            if (pub.titles.length > 1) {
                subtitle = pub.titles[1].title
            }
            let abstract = pub.descriptions.find(d => d.descriptionType === 'Abstract')
            if (abstract !== undefined) {
                abstract = abstract.description
            }

            var pubdata = {
                type: type,
                title: title,
                subtitle: subtitle,
                abstract: abstract ?? '',
                first_authors: first,
                authors: authors,
                doi: pub.doi,
                link: pub.url,
                date_start: date,
                doctype: pub.types.resourceTypeGeneral,
            }

            if (type == 'software' || type == 'dataset') {
                pubdata['date_start'] = date
                pubdata['software_version'] = pub.version
                pubdata['software_doi'] = pub.doi
                pubdata['software_venue'] = pub.publisher
            } else {
                pubdata['year'] = dateSplit[0] ?? null
                pubdata['month'] = dateSplit[1] ?? null
                pubdata['day'] = dateSplit[2] ?? null
            }

            toggleForm(pubdata)
            $('.loader').removeClass('show')
        },
        error: function (response) {

            toastError('Ressource was not found in DataCite.')
            $('.loader').removeClass('show')
        }
    })
}

function toggleForm(pub) {

    var selectedType = 'misc';

    switch (pub.type.toLowerCase()) {
        case 'journal-article':
            selectedType = 'article';
            break;
        case 'magazine-article':
            selectedType = 'magazine';
            break;
        case 'book-chapter':
        case 'chapter':
            selectedType = 'chapter';
            pub.book = pub.journal;
            delete pub.journal
            break;
        case 'book':
            if (pub.editors !== undefined && pub.editors.length > 0 && pub.authors.length > 0) {
                selectedType = 'chapter';
            } else if (pub.editors !== undefined && pub.editors.length > 0) {
                selectedType = 'editor';
                pub.book = pub.journal;
            } else {
                selectedType = 'book';
                pub.series = pub.journal;
            }
            delete pub.journal
            break;
        case 'software':
        case 'dataset':
        case 'report':
            selectedType = 'software';
            $('#software_type option[value="' + pub.type + '"]').prop("selected", true);
            break;
        // case 'book-chapter':
        //     selectedType = 'chapter';
        //     break;
        case 'posted-content':
            selectedType = 'preprint'
            break
        default:
            selectedType = pub.type;
            break;
    }

    if (UPDATE) {
        $('#publication-form').find('input:not(.hidden)').removeClass('is-valid')
    } else {
        $('#publication-form').find('input:not(.hidden):not([type=radio]):not([type=checkbox])').val('').removeClass('is-valid')
    }

    togglePubType(selectedType, function () { fillForm(pub) })
}

function fillForm(pub) {
    // console.log(pub);

    if (pub.title !== undefined) {
        var title = pub.title.replace(/\s+/g, ' ')
        console.info(title);
        $('#title').val(title).addClass('is-valid')
        // quill.setText(title);
        $('.title-editor .ql-editor').html("<p>" + title + "</p>").addClass('is-valid')
    }

    var elements = [
        'first_authors',
        'year',
        'month',
        'day',
        'journal',
        'issn',
        'issue',
        'volume',
        'pages',
        'doi',
        'pubmed',
        'book',
        'edition',
        'publisher',
        'series',
        'isbn',
        'city',
        'software_venue',
        'software_version',
        'date_start',
        'software_doi',
        'open_access',
        'abstract',
        'subtitle',
        'pub-language',
        'language',
        'doctype',
        'link',
        'editors',
        'funding',
    ]

    elements.forEach(element => {
        if (pub[element] !== undefined && !UPDATE || !isEmpty(pub[element]))
            $('#' + element).val(pub[element]).addClass('is-valid')
        // console.log(element);
        // console.log(pub[element]);
        // console.log($('#' + element));
    });

    if (pub.funding !== undefined){
        // first: check if module projects exists
        if ($('[data-module="projects"]').length > 0){
            $.get(ROOTPATH + '/api/projects-by-funding-number', { number: pub.funding }, function(data){
                if (data.data.length === 0){
                    toastWarning('No project found for funding number ' + pub.funding)
                    return;
                }
               // populate projects dropdown
               data.data.forEach(proj => {
                    addProjectRow(proj._id['$oid'], proj.name);
               })
            })
        }
    }

    if (pub.epub !== undefined && (!UPDATE || !pub.epub || !pub.epub.length))
        $('#epub').attr('checked', pub.epub).addClass('is-valid')


    if (pub.open_access !== undefined) {
        fillOpenAccess(pub.open_access)
    }
    if (pub.journal) {
        // prefer ISSN to look journal up:
        var j_val = pub.journal
        if (pub.issn !== undefined && pub.issn.length !== 0) {
            j_val = pub.issn.split(' ')
            j_val = j_val[0]
        }
        // console.log(j_val);
        $('#journal-search').val(j_val)
        getJournal(j_val)

        $('#journal-field').addClass('is-valid')
    }

    var aff_undef = false
    if ($('#authors').length > 0) {
        $('#authors').find('tr').remove()
        $('#authors').closest('.module').addClass('is-valid')
        pub.authors.forEach(function (d, i) {
            if (d.affiliation === undefined) {
                aff_undef = true
            }
            addAuthorRow({ last: d.family, first: d.given, aoi: d.affiliation ?? false, position: d.position ?? 'middle' })
        })
    }

    // if ($('#editors').length > 0) {
    //     $('#editors').find('tr').remove()
    //     $('#editors').closest('.module').addClass('is-valid')
    //     if (pub.editors !== undefined) {
    //         pub.editors.forEach(function (d, i) {
    //             if (d.affiliation === undefined) {
    //                 aff_undef = true
    //             }
    //             addAuthorRow({ last: d.family, first: d.given, aoi: d.affiliation ?? false, position: 'editor' })
    //         })
    //     }
    // }

    if ($('.author-list').length > 0) {

        $('.author-widget').addClass('is-valid').find('.author').remove()

        if (pub.authors !== undefined) {
            pub.authors.forEach(function (d, i) {
                if (d.affiliation === undefined) {
                    aff_undef = true
                }
                addAuthorDiv(d.family, d.given, d.affiliation ?? false)
            })
        }
        if (pub.editors !== undefined) {
            pub.editors.forEach(function (d, i) {
                if (d.affiliation === undefined) {
                    aff_undef = true
                }
                addAuthorDiv(d.family, d.given, d.affiliation ?? false, true)
            })
        }
        if (aff_undef)
            toastWarning('Not all affiliations could be parsed automatically. Please click on every ' + AFFILIATION + ' author to mark them.')

        affiliationCheck();
    }
    // if (pub.funder.length > 0){
    //     funder
    // }

    toastSuccess(lang('Publication data was successfully loaded.', 'Publikationsdaten wurden erfolgreich geladen.'))

}

function fillOpenAccess(oa) {
    if (oa === false || oa == 'closed') {
        $('#open_access-0').attr('checked', true)
        $('#oa_status option[value=closed]').attr('selected', true)
    } else {
        if (oa === true) oa = 'open'
        // console.log(oa);
        $('#open_access').attr('checked', true)
        $('#oa_status option[value=' + oa + ']').attr('selected', true)
    }
    $('#oa_status').addClass('is-valid')
}

function getPubData(event, form) {
    event.preventDefault();
    if ($('#search-doi').length !== 0) {
        var doi = $('#search-doi').val()
    } else {
        var doi = $('#doi').val()
    }
    getPublication(doi)
}

function getPublishingDate(pub) {
    var date = ["", "", ""];
    // if (pub['issued']){
    //     date = getDate(pub['issued'])
    // } else 
    if (pub['published-print']) {
        date = getDate(pub['published-print'])
    } else if (pub['published']) {
        date = getDate(pub['published'])
    } else if (pub['published-online']) {
        date = getDate(pub['published-online'])
    } else if (pub['issued']) {
        date = getDate(pub['issued'])
    }
    return date
}

function getDate(element) {
    if (element['date-parts'] !== undefined) {
        element = element['date-parts'][0];
    }
    var date = ["", "", ""]
    // 2022-07-06
    if (element[0]) date[0] = element[0]

    if (element[1]) date[1] = element[1] //+= "-" + ("0" + element[1]).slice(-2)
    //else date += "-01"

    if (element[2]) date[2] = element[2] //+= "-" + ("0" + element[2]).slice(-2)
    //else date += "-01"

    // console.log(date);
    // if (element[1]) date = ("0" + element[1]).slice(-2) + "." + date
    // if (element[2]) date = ("0" + element[2]).slice(-2) + "." + date
    return date
}

function selectEvent(id, event, start, end, location) {
    $('#conference_id').val(id)
    $('#conference').val(event)
    $('#location').val(location)
    $('#date_start').val(start)
    $('#date_end').val(end)

    $('#connected-conference').html(lang('Connected to ', 'Verknüpft mit ') + event)

    if ($('#event-select-dropdown').length > 0) {
        $('#event-select-dropdown').removeClass('show')
    }
    toastSuccess(lang('Event "'+event+'" selected.', 'Veranstaltung "'+event+'" ausgewählt.'))
}

function addEvent() {
    var data = {
        title: $('#event-title').val(),
        title_full: $('#event-title_full').val(),
        start: $('#event-start').val(),
        end: $('#event-end').val(),
        location: $('#event-location').val(),
        type: $('#event-type').val(),
        url: $('#event-url').val()
    }

    // check for required
    var valid = true;
    ['title', 'start', 'end', 'location'].forEach(function (d) {
        if (data[d] === '') {
            $('#' + d).addClass('is-invalid')
            valid = false
        }
    });
    if (!valid) {
        toastWarning(lang('Please fill out all required fields.', 'Bitte füllen Sie alle Pflichtfelder aus.'))
        return;
    }


    if ($('#event-attended').is(':checked')) {
        data['participants'] = $('#event-attended').val()
    }

    $.ajax({
        type: "POST",
        data: {
            values: data
        },
        dataType: "json",
        url: ROOTPATH + '/crud/conferences/add',
        success: function (response) {
            console.log(response);
            if (response.id && response.status == 'warning') {
                if (response.msg) toastWarning(response.msg)
                selectEvent(response.id, data.title, data.start, data.end, data.location)
                // close modal
                window.location.hash = '#event-select'
                return;
            } else if (response.msg && response.status == 'error') {
                toastWarning(response.msg)
                return;
            } else {
                toastSuccess(lang('Event added successfully.', 'Veranstaltung erfolgreich hinzugefügt.'))
                selectEvent(response.id, data.title, data.start, data.end, data.location)
                // close modal
                window.location.hash = '#event-select'
            }
            $('.loader').removeClass('show')
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}