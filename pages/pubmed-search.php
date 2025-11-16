<?php

/**
 * Page to search for activities in PubMed
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/pubmed-search
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1 class="my-0">
    <i class="ph-duotone ph-plus-circle"></i>
    <?= lang('Search in Pubmed', 'Suche in Pubmed') ?>
</h1>

<a href="<?= ROOTPATH ?>/add-activity" class="link mb-10 d-block"><?= lang('Add manually', 'Füge manuell hinzu') ?></a>


<form action="#" class="form-inline w-500 mw-full" onsubmit="searchLiterature(event)">
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
        <label class=" w-100" for="year">Year</label>
        <input type="text" class="form-control" placeholder="" id="year" value="<?= $_GET['year'] ?? CURRENTYEAR ?>">
    </div>
    <div class="form-group mb-0">
        <input type="submit" class="btn secondary ml-auto" value="Search">
    </div>
</form>

<hr>

<p class="text-secondary text-right" id="details"></p>

<div id="results">

    <p>
        Enter your search terms.
    </p>
</div>


<script>
    function searchLiterature(evt) {
        evt.preventDefault()
        $('#results').empty()
        $('#details').empty()

        var authors = $('#authors').val().trim()
        var title = $('#title').val().trim()
        var year = $('#year').val().trim()
        var affiliation = $('#affiliation').val().trim()

        if (authors === "" && title === "" && year === "") {
            $('#results').html(`<tr class='row-danger'><td>Search was empty.</td></tr>`);
            return false
        }

        console.log(authors);

        var term = []
        if (title !== '')
            term.push(`(${title}[title])`)
        if (authors !== '')
            term.push(`(${authors}[author])`)
        if (year !== '')
            term.push(`(${year}[year])`)
        if (affiliation !== '')
            term.push(`(${affiliation}[ad])`)

        term = term.join(' AND ')
        console.log(term);
        searchPubMed(term)

        return false;
    }



    function searchPubMed(term) {
        var url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi'
        var data = {
            db: 'pubmed',
            term: term,
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
                var result = data.esearchresult
                displayPubMed(result.idlist)

                $('#details').html(`
                    ${result.retmax} out of ${result.count} results are shown.
                `)
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