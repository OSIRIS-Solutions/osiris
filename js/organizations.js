let SUGGEST;
let INPUT;
let SELECTED;
let COMMENT;

$(document).ready(function () {
    SUGGEST = $('#organization-suggest')
    INPUT = $('#organization-search')
    SELECTED = $('#collaborators')
    COMMENT = $('#search-comment')
})

function getOrganization(name, ror = false) {
    console.info('getOrganization')
    console.log(name);
    SUGGEST.empty()
    COMMENT.empty()
    name = name.trim()
    // check if name is empty
    if (name === '') {
        toastError('Please provide a name')
        return
    }
    // check if name is a valid ROR ID
    if (name.startsWith('https://ror.org/')) {
        getRORid(name)
        return;
    }
    // check if name is a valid ROR ID ^0[a-z|0-9]{6}[0-9]{2}$
    if (name.match(/^0[a-z|0-9]{6}[0-9]{2}$/)) {
        getRORid(name)
        return;
    }

    if (ror) {
        searchROR(name);
        return;
    }

    // check if organisation is in the database, else search in ROR
    var url = ROOTPATH + '/api/organizations'
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
            console.log(response);
            var organizations = response.data

            if (organizations.length === 0) {
                COMMENT.html(lang('No results found in our database. Start search in ROR…', 'Keine Ergebnisse in unserer Datenbank gefunden. Starte jetzt die Suche in ROR…'))

                searchROR(name)
                return
            } else {
                suggestOrganization(organizations, false)
            }
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}


function suggestOrganization(data, create = false) {
    console.info('suggestOrganization')

    if (data.length === 0) {
        COMMENT.html(lang('No results found', 'Keine Ergebnisse gefunden'))
    } else {
        data.forEach((org) => {
            console.log(org);
            var row = $('<tr>')

            var button = $('<button type="button" class="btn" title="select">')
            button.html('<i class="ph ph-check text-success"></i>')
            button.on('click', function () {
                selectOrganization(org, create);
            })
            row.append($('<td class="w-50">').append(button))

            var td = $('<td>')
            td.append(`<h5 class="m-0">${org.name}</h5>`)
            td.append(`<span class="text-muted">${org.location}</span>`)
            row.append(td)

            SUGGEST.append(row)
        })
    }
    let lastrow = $('<tr>')
    let rorbtn = $('<button type="button" class="btn">')
    rorbtn.html(lang('Search in ROR', 'Suche in ROR'))
    rorbtn.on('click', function () {
        getOrganization(INPUT.val(), true);
    })
    lastrow.append($('<td colspan="3">').append(rorbtn))
    SUGGEST.append(lastrow)
}

function cleanID(id) {
    console.info('cleanID')
    if (id['$oid']) {
        return id['$oid']
    }
    return id
}

function createOrganizationTR(org) {
    console.info('createOrganizationTR')
    var id = cleanID(org.id)
    var row = $('<tr>')
    var td = $('<td>')
    td.append(`${org.name} <br><small class="text-muted">${org.location}</small>`)
    td.append(`<input type="hidden" name="values[collaborators][]" value="${id}">`)
    row.append(td)
    row.append($('<td>').append(`<div class="custom-radio">
                                        <input type="radio" required name="values[coordinator]" id="coordinator-${id}" value="${id}">
                                        <label for="coordinator-${id}" class="empty"></label>
                                    </div>`))

    td = $('<td>')
    var deletebtn = $('<button type="button" class="btn danger" title="remove">')
    deletebtn.html('<i class="ph ph-trash"></i>')
    deletebtn.on('click', function () {
        $(this).closest('tr').remove()
    })
    td.append(deletebtn)
    row.append(td)

    SELECTED.append(row)
}

function selectOrganization(org, create = false, callback = null) {
    if (callback === null) {
        callback = createOrganizationTR
    }
    console.log(org);
    console.info('selectOrganization')
    if (create) {
        $.ajax({
            type: "POST",
            data: {
                values: org
            },
            dataType: "json",
            url: ROOTPATH + '/crud/organization/create',
            success: function (response) {
                // $('.loader').removeClass('show')
                // console.log(response);
                if (response.msg) {
                    toastWarning(response.msg)
                    selectOrganization(response, false)
                    return;
                } else {
                    // random id
                    callback(org)
                    toastSuccess(lang('Organization added', 'Organisation angelegt'))
                }
                SUGGEST.empty()
                INPUT.val('')
            },
            error: function (response) {
                $('.loader').removeClass('show')
                toastError(response.responseText)
            }
        })
    } else {
        callback(org)
        toastSuccess(lang('Organization connected', 'Organisation verknüpft'))

        SUGGEST.empty()
        INPUT.val('')
    }
    window.location.replace('#close-modal')
}

function getRORid(ror, msg = true) {
    console.info('getRORid')
    if (!ror) {
        toastError('Please provide a ROR ID')
        return
    }
    var url = 'https://api.ror.org/organizations/' + ror.trim()
    $.ajax({
        type: "GET",
        url: url,

        success: function (response) {
            console.log(response);
            if (response.errors) {
                toastError(', '.join(response.errors))
                return
            }
            selectOrganization(response, true)
            $('#organizations-ror-id').val('')
            if (msg)
                toastSuccess(lang('Organization added', 'Organisation hinzugefügt'))
        },
        error: function (response) {
            var errors = response.responseJSON.errors
            if (errors) {
                toastError(errors.join(', '))
            } else {
                toastError(response.responseText)
            }
            $('.loader').removeClass('show')
        }
    })
}

function searchROR(name) {
    console.info('searchROR')
    console.log(name);
    SUGGEST.empty()
    name = name.trim()
    // check if name is empty
    if (name === '') {
        toastError('Please provide a name')
        return
    }
    // check if name is a valid ROR ID
    if (name.startsWith('https://ror.org/')) {
        getRORid(name)
        return;
    }
    // check if name is a valid ROR ID ^0[a-z|0-9]{6}[0-9]{2}$
    if (name.match(/^0[a-z|0-9]{6}[0-9]{2}$/)) {
        getRORid(name)
        return;
    }

    var url = 'https://api.ror.org/organizations'
    var data = {
        affiliation: name
    }
    $.ajax({
        type: "GET",
        data: data,
        dataType: "json",

        url: url,
        success: function (response) {
            console.log(response);
            let organizations = response.items.map(item => {
                let o = item.organization
                let address = o.addresses[0] ?? {}
                return {
                    id: o.id,
                    name: o.name,
                    location: `${address.city}, ${o.country.country_name}`,
                    ror_id: o.id,
                    country: o.country.country_code,
                    types: o.types,
                    type: o.types[0],
                    lat: address.lat ?? null,
                    lng: address.lng ?? null,
                    url: o.links[0] ?? null,
                    chosen: item.chosen,
                }
            }
            )
            suggestOrganization(organizations, true)
        },
        error: function (response) {
            toastError(response.responseText)
            $('.loader').removeClass('show')
        }
    })
}


