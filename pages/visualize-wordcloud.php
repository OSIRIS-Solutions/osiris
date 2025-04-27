<?php

/**
 * Page to visualize coauthor network
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /visualize/wordcloud
 *
 * @package OSIRIS
 * @since 1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$users = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);

$scientist = $_GET['scientist'] ?? $_SESSION['username'];
$selectedUser = $osiris->persons->findone(['username' => $scientist]);
?>


<h1>
    <i class="ph ph-graph" aria-hidden="true"></i>
    <?= lang('Word cloud') ?>
</h1>


<form action="" method="get" class="w-400 mw-full">
    <div class="input-group">
        <select name="scientist" id="scientist-select" class="form-control">
            <?php foreach ($users as $u) { ?>
                <option value="<?= $u['username'] ?>" <?= $u['username'] == $scientist ? 'selected' : '' ?>><?= $u['formalname'] ?></option>
            <?php } ?>
        </select>
        <div class="input-group-append">
            <button class="btn secondary" type="submit">Select</button>
        </div>
    </div>
</form>

<a class="link" href="<?= ROOTPATH ?>/profile/<?= $scientist ?>"><i class="ph ph-student"></i>
    <?= lang('View scientist page of ', 'Zeige die Übersichtsseite von ') ?>
    <?= $selectedUser['displayname'] ?>
</a>

<div id="my_dataviz"></div>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script>



<script>
    const USER = '<?= $scientist ?>'

    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/wordcloud",
        data: {
            user: USER
        },
        dataType: "json",
        success: function(response) {
            var dat = response.data //
            var max = 120;
            var highest = Object.values(dat)[0]
            var factor = max/highest

            myWords = Object.keys(dat).map(function(key) {
                return {
                    text: key,
                    size: (dat[key] * factor) + 10
                };
            });
            console.log(myWords);
            myWords = myWords.slice(0, 200)

            // set the dimensions and margins of the graph
            var margin = {
                    top: 10,
                    right: 10,
                    bottom: 10,
                    left: 10
                },
                width = 800 - margin.left - margin.right,
                height = 450 - margin.top - margin.bottom,
                colors = [
                    '#f78104',
                    '#faab36',
                    '#e95709',
                    '#008083',
                    '#249ea0',
                    '#005f60',
                    // '#63a308',
                    // '#ECAF00',
                ];
            // Constructs a new cloud layout instance. It run an algorithm to find the position of words that suits your requirements
            // Wordcloud features that are different from one word to the other must be here
            var layout = d3.layout.cloud()
                .size([800, 500])
                .words(myWords)
                .padding(1)
                .rotate(function() {
                    return ~~(Math.random() * 2) * 90;
                })
                .font("Impact")
                .fontSize(function(d) {
                    return d.size;
                })
                .on("end", draw);

            layout.start();

            function draw(words) {
                d3.select("#my_dataviz").append("svg")
                    .attr("width", layout.size()[0])
                    .attr("height", layout.size()[1])
                    .append("g")
                    .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
                    .selectAll("text")
                    .data(words)
                    .enter().append("text")
                    .style("font-size", function(d) {
                        return d.size + "px";
                    })
                    .style("font-family", "Impact")
                    .attr("text-anchor", "middle")
                    .attr("fill", (d)=> colors[Math.floor(Math.random() * colors.length)])
                    .attr("transform", function(d) {
                        return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
                    })
                    .text(function(d) {
                        return d.text;
                    });
            }

        },
        error: function(response) {
            console.log(response);
        }
    });
</script>