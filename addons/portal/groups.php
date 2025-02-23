<?php
$style = $_GET['style'] ?? 'cards';
?>

<div class="container">

    <h1>
        <i class="ph ph-users-three"></i>
        <?= lang('Organisational Units', 'Organisationseinheiten') ?>
    </h1>

    <div class="d-flex align-items-center mb-10">
        <div class="pills small ml-auto">
            <span class="badge text-muted"><?= lang('Show as', 'Zeige als') ?></span>
            <a class="btn <?= $style == 'cards' ? 'active' : '' ?>" href="?style=cards"><?= lang('Cards', 'Karten') ?></a>
            <a class="btn <?= $style == 'hierarchy' ? 'active' : '' ?>" href="?style=hierarchy"><?= lang('Hierarchy', 'Hierarchie') ?></a>
            <a class="btn <?= $style == 'organigramm' ? 'active' : '' ?>" href="?style=organigramm"><?= lang('Organization Chart', 'Organigramm') ?></a>
        </div>
    </div>



    <?php if ($style == 'cards') { ?>

        <style>
            .badge {
                background: var(--highlight-color);
                color: white;
                opacity: .7;
            }

            .table.cards {
                border: none;
                background: transparent;
                box-shadow: none;
            }

            .table.cards thead {
                display: none;
            }

            .table.cards tbody {
                display: flex;
                flex-grow: column;
                flex-direction: row;
                flex-wrap: wrap;

            }

            .table.cards tr {
                width: 32%;
                margin: 0.5em;
                border: 1px solid var(--border-color);
                border-radius: 0.5em;
                box-shadow: var(--box-shadow);
                background: white;
            }

            .table.cards tr td {
                border: 0;
                box-shadow: none;
                width: 100%;
                height: 100%;
                display: block;
            }
        </style>

        <table class="table cards" id="group-table">
            <thead>
                <th></th>
            </thead>
            <tbody>
                <?php foreach ($Groups->groups as $group) { ?>
                    <tr>
                        <td class="unit" id="<?= $group['id'] ?>" <?= $Groups->cssVar($group['id']) ?>>
                            <span style="display:none">
                                <!-- hidden field for sorting based on level -->
                                <?= $Groups->getLevel($group['id']) ?>
                            </span>
                            <h5 class="title" style="margin: 0;">
                                <span class="badge unit-id"><?= $group['id'] ?></span>
                                <a href="<?= PORTALPATH ?>/group/<?= $group['id'] ?>" class="unit-name">
                                    <?= $group['name'] ?>
                                </a>
                            </h5>
                            <?php if (!empty($group['parent'])) { ?>
                                <small class="unit-parent">
                                    <a href="#<?= $group['parent'] ?>"><?= $Groups->getName($group['parent']) ?></a>
                                </small>
                            <?php } ?>

                            <p class="unit-type text-muted">
                                <?= $group['unit'] ?>
                            </p>

                            <?php if (isset($group['head'])) {
                                $heads = $group['head'];
                                if (is_string($heads)) $heads = [$heads];
                                $heads = array_map([$DB, 'getNameFromId'], $heads);
                            ?>
                                <span class="float-right unit-head">
                                    <i class="ph ph-crown text-signal"></i>
                                    <?= implode('<br>', $heads) ?>
                                </span>
                            <?php
                            } ?>
                            <?php
                            $children = $Groups->getChildren($group['id']);
                            ?>
                            <span class="unit-members">
                                <?= $osiris->persons->count([
                                    'unit' => ['$elemMatch' => ['unit' => $group['id'], 'start' => ['$lte' => date('Y-m-d')], 'end' => ['$gte' => date('Y-m-d')]]],
                                    'is_active' => ['$ne' => false]
                                ]) ?> <?= lang('Members', 'Mitglieder') ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <script>
            var dataTable;
            $(document).ready(function() {
                dataTable = $('#group-table').DataTable({
                    dom: 'frtipP',
                    paging: true,
                    autoWidth: true,
                    pageLength: 12,
                });
            });
        </script>


    <?php } else if ($style == 'hierarchy') {

        echo $Groups->getHierarchy();
    } else if ($style == 'organigramm') { ?>
        <script src="https://d3js.org/d3.v7.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3"></script>
        <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>


        <div class="btn-group float-left">
            <button class="btn small" onclick="chart.layout('left').render().fit()">Left</button>
            <button class="btn small" onclick="chart.layout('top').render().fit()">Top</button>
        </div>

        <!-- <canvas id="canvas" width="900" height="900" style="background:#FFF"></canvas> -->
        <div class="chart-container" style="background-color: #f6f6f6"></div>
        <?php
        $levels = $Groups->groups;

        foreach ($levels as $k => &$value) {
            $res = array();
            if (isset($value['head'])) {
                $heads = $value['head'];
                if (is_string($heads)) $heads = [$heads];
                foreach ($heads as $i => $k) {
                    $res[] = $DB->getNameFromId($k);
                }
            }
            $value['head'] = implode(', ', $res);
        }
        ?>

        <script>
            var tree = JSON.parse('<?= json_encode($Groups->groups) ?>');

            console.log(tree);
            var data = [];
            for (const id in tree) {
                const el = tree[id];
                el.parentId = el.parent
                data.push(el);
            }

            chart = new d3.OrgChart()
                .container('.chart-container')
                .data(data)
                .layout('left')
                .nodeWidth((d) => 250)
                .svgHeight(window.innerHeight - 300)
                .initialZoom(0.7)
                .nodeHeight((d) => 175)
                .childrenMargin((d) => 40)
                .compactMarginBetween((d) => 15)
                .compactMarginPair((d) => 80)
                .nodeContent(function(d, i, arr, state) {
                    return `
            <div style="padding-top:30px;background-color:none;margin-left:1px;height:${
              d.height
            }px;border-radius:2px;overflow:visible">
              <div style="height:${
                d.height - 32
              }px;padding-top:0px;background-color:white;border:1px solid var(--border-color);">

               <div style="margin-right:10px;margin-top:15px;float:right">${
                 d.data.id
               }</div>
               
               <div style="margin-top:-15px;background-color:${d.data.color};height:10px;width:${
                 d.width - 2
               }px;border-radius:1px"></div>

               <div style="padding:20px; padding-top:35px;text-align:center">
                   <div style="color:${d.data.color};font-size:16px;font-weight:bold"> ${
                     d.data.name
                   } </div>
                   <div class="text-muted" style="margin-top:.5rem"> ${
                     d.data.unit
                   } </div>
                   <em>${d.data.head}</em>
               </div> 
              </div>     
      </div>
  `;
                })
                .render();
        </script>

    <?php } ?>
</div>