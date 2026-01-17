'use strict';

let SUGGEST;
let INPUT;
let SELECTED;
let COMMENT;
let USE_RADIO = true;
let DATAFIELD = 'collaborators'

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
    console.log(create);
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
    if (!create) {
        let rorbtn = $('<button type="button" class="btn">')
        rorbtn.html(lang('Search in ROR', 'Suche in ROR'))
        rorbtn.on('click', function () {
            getOrganization(INPUT.val(), true);
        })
        lastrow.append($('<td colspan="3">').append(rorbtn))
    } else {
        let createbtn = $('<a href="#add-organization" class="btn">')
        createbtn.html(lang('Create new organization', 'Neue Organisation anlegen'))
        lastrow.append($('<td colspan="3">').append(createbtn))
    }
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
    td.append(`<input type="hidden" name="values[${DATAFIELD}][]" value="${id}">`)
    row.append(td)
    if (USE_RADIO) {
        row.append($('<td>').append(`<div class="custom-radio">
                                        <input type="radio" required name="values[coordinator]" id="coordinator-${id}" value="${id}">
                                        <label for="coordinator-${id}" class="empty"></label>
                                    </div>`))
    }

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
            url: ROOTPATH + '/crud/organizations/create',
            success: function (response) {
                // $('.loader').removeClass('show')
                // console.log(response);
                if (response.msg) {
                    toastWarning(response.msg)
                    selectOrganization(response, false, callback)
                    return;
                } else {
                    // random id
                    callback(response)
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
    var url = 'https://api.ror.org/v2/organizations/' + ror.trim()
    $.ajax({
        type: "GET",
        url: url,

        success: function (response) {
            console.log(response);
            if (response.errors) {
                toastError(', '.join(response.errors))
                return
            }
            var org = translateROR(response)
            selectOrganization({
                name: org.name,
                location: `${org.location}`,
                ror: org.ror,
                country: org.country,
                types: org.types,
                type: org.type,
                lat: org.lat ?? null,
                lng: org.lng ?? null,
                url: org.url ?? null,
                chosen: true,
            }, true)
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

    var url = 'https://api.ror.org/v2/organizations'
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
                return translateROR(item.organization);
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



function translateROR(o) {
    console.log(o);
    let name = ""
    let synonyms = []
    o.names.forEach(n => {
        if (n.types.includes("ror_display")) {
            name = n.value
        } else {
            synonyms.push(n.value)
        }
    })
    if (name == "") {
        name = o.names[0].value ?? o.id
    }
    let location = o.locations[0] ?? {}
    let location_name = null;
    if (location && location.geonames_details) {
        location = location.geonames_details
        location_name = location.name ?? '';
        if (location.country_name) {
            location_name += ', ' + location.country_name
        }
    }
    let org = {
        ror: o.id,
        name: name,
        location: location_name,
        country: location.country_code ?? null,
        lat: location.lat ?? null,
        lng: location.lng ?? null,
        type: o.types[0],
        types: o.types,
        url: o.links[0] ?? null,
        synonyms: synonyms,
        chosen: false
    }
    return org
}



function addOrganization() {
    var data = {
        name: $('#org-name').val(),
        type: $('#org-type').val(),
        location: $('#org-location').val(),
        country: $('#org-country').val(),
        lat: $('#org-lat').val(),
        lng: $('#org-lng').val(),
    }

    // check for required
    var valid = true;
    ['name', 'type', 'country'].forEach(function (d) {
        if (data[d] === '') {
            $('#org-' + d).addClass('is-invalid')
            valid = false
        }
    });
    if (!valid) {
        toastWarning(lang('Please fill out all required fields.', 'Bitte füllen Sie alle Pflichtfelder aus.'))
        return;
    }

    selectOrganization(data, true);
}


function getCoordinates(locationId = '#location', countryId = '#country', latId = '#lat', lngId = '#lng') {
    let loc = $(locationId).val();
    console.log(loc);

    if (!loc || loc.length === 0) {
        toastError(lang("Please provide a location first.", "Bitte geben Sie zuerst einen Standort ein."));
        return;
    }
    let url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(loc) + '&accept-language=' + lang('en', 'de');
    if ($(countryId).val() && $(countryId).val().length > 0) {
        url += '&countrycodes=' + $(countryId).val().toUpperCase();
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data && data.length > 0) {
                const place = data[0];
                $(latId).val(place.lat);
                $(lngId).val(place.lon);
                const name = place.display_name;

                toastSuccess(lang("Location coordinates updated based on <b>" + name + "</b>.", "Standort-Koordinaten aktualisiert basierend auf <b>" + name + "</b>."));
            } else {
                toastError(lang("Location not found. Please refine your search.", "Standort nicht gefunden. Bitte verfeinern Sie Ihre Suche."));
            }
        })
        .catch(error => {
            console.error('Error fetching location data:', error);
            toastError(lang("An error occurred while fetching location data.", "Beim Abrufen der Standortdaten ist ein Fehler aufgetreten."));
        });

}
