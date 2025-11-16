<?php

/**
 * Page to search for activities online
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/online-search
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1 class="my-0">
    <i class="ph-duotone ph-plus-circle"></i>
    <?= lang('Search online', 'Online-Suche') ?>
</h1>

<a href="<?= ROOTPATH ?>/add-activity" class="link mb-10 d-block"><?= lang('Add manually', 'Füge manuell hinzu') ?></a>

<!-- pills -->

<nav class="pills mb-20">
    <a onclick="navigate('crossref')" id="btn-crossref" class="btn active">
        CrossRef
    </a>
    <a onclick="navigate('pubmed')" id="btn-pubmed" class="btn">
        PubMed
    </a>
    <a onclick="navigate('dnb')" id="btn-dnb" class="btn disabled">
        Deutsche Nationalbibliothek
    </a>
    <a onclick="navigate('openalex')" id="btn-openalex" class="btn disabled">
        OpenAlex
    </a>
    <a onclick="navigate('orcid')" id="btn-orcid" class="btn disabled">
        ORCID
    </a>
    <a onclick="navigate('google')" id="btn-google" class="btn disabled">
        Google Scholar
    </a>
    <a onclick="navigate('matilda')" id="btn-matilda" class="btn disabled">
        Matilda
    </a>
</nav>


<form action="#" class="form-inline w-500 mw-full" onsubmit="searchOnline(event)">
    <div class="form-group">
        <label class=" w-100" for="authors">Author(s)</label>
        <input type="text" class="form-control" placeholder="" id="authors" value="<?= $_GET['authors'] ?? $USER['last'] ?? '' ?>">
    </div>
    <div class="form-group">
        <label class=" w-100" for="affiliation">Affiliation</label>
        <input type="text" class="form-control" placeholder="" id="affiliation" value="<?= $Settings->get('affiliation') ?>">
    </div>
    <div class="form-group">
        <label class=" w-100" for="title">Title</label>
        <input type="text" class="form-control" placeholder="" id="title" value="<?= $_GET['title'] ?? '' ?>">
    </div>
    <div class="form-group">
        <label class=" w-100" for="journal">Journal name</label>
        <input type="text" class="form-control" placeholder="" id="journal" value="<?= $_GET['journal'] ?? '' ?>">
    </div>
    <div class="form-group">
        <label class=" w-100" for="year">Year</label>
        <input type="text" class="form-control" placeholder="" id="year" value="<?= $_GET['year'] ?? CURRENTYEAR ?>">
    </div>
    <div class="form-group mb-0">
        <input type="submit" class="btn secondary ml-auto" value="Search">
    </div>
</form>


<div class="text-secondary text-right" id="details"></div>

<div id="results">

    <p>
        Enter your search terms.
    </p>
</div>

<script>
    let ROOTPATH = '<?= ROOTPATH ?>'
    let SOURCE = 'crossref'

    function navigate(id) {
        if ($('#btn-' + id).hasClass('disabled')) {
            toastError('Not implemented yet.')
            return false
        }
        $('.btn').removeClass('active')
        $('#btn-' + id).addClass('active')
        SOURCE = id
    }

    function searchOnline(event) {
        event.preventDefault()
        $('#results').empty()
        $('#details').empty()

        var data = {
            authors: $('#authors').val().trim(),
            title: $('#title').val().trim(),
            affiliation: $('#affiliation').val().trim(),
            journal: $('#journal').val().trim(),
            year: $('#year').val().trim()
        }

        switch (SOURCE) {
            case 'crossref':
                searchCrossref(data)
                break
            case 'pubmed':
                searchPubmed(data)
                break
            case 'dnb':
                break
            case 'openalex':
                break
            case 'orcid':
                break
            case 'google':
                break
            case 'matilda':
                break
        }
    }

    function crossRefDate(pub) {
        // getting the date of publication from the crossref object
        if (pub['journal-issue'] !== undefined && (
                (pub['journal-issue']['published-online'] !== undefined && pub['journal-issue']['published-online']['date-parts'] !== undefined) ||
                (pub['journal-issue']['published-print'] !== undefined && pub['journal-issue']['published-print']['date-parts'] !== undefined)
            )) {
            pub = pub['journal-issue']
        }
        var dp = ["", "", ""];
        if (pub['published-print']) {
            dp = pub['published-print']
        } else if (pub['published']) {
            dp = pub['published']
        } else if (pub['published-online']) {
            dp = pub['published-online']
        }
        if (dp['date-parts'] !== undefined) {
            dp = dp['date-parts'][0];
        }
        var date = ["1900", "1", "1"]
        if (dp[0]) date[0] = dp[0]
        if (dp[1]) date[1] = dp[1]
        if (dp[2]) date[2] = dp[2]
        date = date.join('-')
        return date
    }

    function searchCrossref(data) {

        if (data.authors === "" && data.title === "") {
            $('#details').html(`<p class='text-danger'>Search was empty.</p>`);
            return false
        }

        $('.loader').addClass('show')

        var query = {}
        if (data.title !== '')
            query['query.title'] = data.title
        if (data.authors !== '')
            query['query.author'] = data.authors
        if (data.affiliation !== '')
            query['query.affiliation'] = data.affiliation
        if (data.journal !== '')
            query['query.container-title'] = data.journal
        if (data.year !== '')
            query['filter'] = `from-pub-date:${data.year}-01-01,until-pub-date:${data.year}-12-31`

        console.log(query);

        $.ajax({
            url: 'https://api.crossref.org/works',
            data: query,
            success: function(result) {
                console.log(result);
                if (result.status === 'failed') {
                    result.message.forEach(msg => {
                        $('#details').append(`<p class="text-danger">${msg.message}</p>`)
                    })
                    $('.loader').removeClass('show')
                    return false
                }
                result = result.message
                let msg = `<p>Found ${result['total-results']} results.</p>`
                $('#details').html(msg)

                result.items.forEach(item => {
                    let authors = item.author.map(a => a.given + ' ' + a.family).join(', ')
                    let title = item.title[0]
                    let journal = item['container-title'][0]
                    let date = crossRefDate(item)
                    let doi = item.DOI
                    let url = item.URL
                    let html = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${title}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">${authors} (${date})</h6>
                            <p class="card-text">${journal}</p>
                            <a href="${url}" class="card-link">Link</a>
                            <a href="${doi}" class="card-link">DOI</a>
                        </div>
                    </div>
                    `
                    $('#results').append(html)
                })

                $('.loader').removeClass('show')
            },
            error: function(err) {
                console.log(err);
                $('#details').html(err.message)
                $('.loader').removeClass('show')
            }
        })
    }



    function searchPubmed(data) {

        if (data.authors === "" && data.title === "") {
            $('#details').html(`<p class='text-danger'>Search was empty.</p>`);
            return false
        }
        $('.loader').addClass('show')
        var term = []
        if (data.title !== '')
            term.push(`(${data.title}[title])`)
        if (data.authors !== '')
            term.push(`(${data.authors}[author])`)
        if (data.year !== '')
            term.push(`(${data.year}[year])`)
        if (data.affiliation !== '')
            term.push(`(${data.affiliation}[ad])`)

        term = term.join(' AND ')
        console.log(term);

        var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi'
        $.ajax({
            type: "GET",
            data: {
                db: 'pubmed',
                term: term,
                retmode: 'json',
                // usehistory: 'y'
            },
            dataType: "json",

            url: url,
            success: function(result) {
                console.log(result);
                var result = result.esearchresult
                displayPubMed(result.idlist)

                $('#details').html(`
                    ${result.retmax} out of ${result.count} results are shown.
                `)
                $('.loader').removeClass('show')
            },
            error: function(response) {
                toastError(response.responseText)
                $('.loader').removeClass('show')
            }
        })
    }


    function displayPubMed(ids) {
        if (ids.length === 0) {
            $('#results').html(`<tr class='row-signal'><td>Nothing found.</td></tr>`);
            return false
        }
        var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi'
        var data = {
            db: 'pubmed',
            id: ids.join(','),
            retmode: 'json',
            // usehistory: 'y'
        }
        $.ajax({
            type: "GET",
            data: data,
            dataType: "json",

            url: url,
            success: function(data) {
                console.log(data);

                var table = $('#results')

                for (const id in data.result) {

                    const item = data.result[id];
                    if (item.uid === undefined) continue;

                    var authors = []
                    if (item.authors !== undefined) {
                        item.authors.forEach(a => {
                            authors.push(a.name);
                        });
                    }

                    var element = $(`<div id="${item.uid}" class="box">`)
                    var content = $('<div class="content">')
                    var link = "pubmed=" + item.uid;
                    if (item.elocationid && item.elocationid.startsWith('doi:')) {
                        link = "doi=" + item.elocationid.replace('doi:', '').trim()
                    }

                    content.append(`
                    <a href="${ROOTPATH}/add-activity?${link}" target='_blank' class="btn secondary float-right"><i class="ph ph-plus"></i></a>
                    `)
                    content.append(
                        `
                        <a class='d-block colorless' target="_blank" href="https://pubmed.ncbi.nlm.nih.gov/${item.uid}/">${item.title}</a>
                        <small class='text-secondary d-block'>${authors.join(', ')}</small>
                        <small class='text-muted'>${item.fulljournalname} (${item.pubdate})</small>
                        `
                    )

                    element.append(content)
                    table.append(element)

                    checkDuplicate(item.uid, item.title)

                }


            },
            error: function(response) {
                toastError(response.responseText)
                $('.loader').removeClass('show')
            }
        })
    }


    function checkDuplicate(id, title) {
        // TODO: add possibility to mark this as duplicate,
        // then add PubMed ID to activity
        $.ajax({
            type: "GET",
            data: {
                title: title,
                pubmed: id
            },
            dataType: "json",
            url: ROOTPATH + '/api/levenshtein',
            success: function(result) {
                console.log(result);
                const element = $('#' + id)
                const content = element.find('.content')
                const btn = content.find('.btn')
                var p = $('<p>')
                element.attr('data-value', result.similarity)

                if (result.similarity > 98) {
                    element.addClass('duplicate')
                    p.addClass('text-danger')

                    p.html(
                        lang('<b>Duplicate</b> of', '<b>Duplikat</b> von') +
                        ` <a href="${ROOTPATH}/activities/view/${result.id}" class="colorless">${result.title}</a>`
                    )
                    btn.remove()
                } else if (result.similarity > 50) {
                    p.addClass('text-signal')
                    p.html(
                        lang('Might be duplicate of ', 'Vielleicht Duplikat von') +
                        ` (<b>${result.similarity}&nbsp;%</b>):</p>
                     <a href="${ROOTPATH}/activities/view/${result.id}" class="colorless">${result.title}</a>`
                    )
                    // p.append('<p class="text-signal">'+lang('This might be a duplicate of the follwing publication', 'Dies könnte ein Duplikat der folgenden Publikation sein'))
                }
                content.append(p)

                $("#results > div").sort(orderByAttr).prependTo("#results")
            },
            error: function(response) {
                toastError(response.responseText)
                $('.loader').removeClass('show')
            }
        })
    }
</script>