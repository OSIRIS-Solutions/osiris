<h2 class="title">
    <i class="ph ph-lg ph-coin text-signal"></i>
    Coins
</h2>

<h5>
    <?= lang('activities.what_are_coins') ?>
</h5>
<p class="">
    <?=lang('activities.you_receive_coins_for_research_activities_that_you_enter_in_osiris_your_ins')?>
</p>

<h5>
    <?= lang('activities.how_do_i_get_them') ?>
</h5>

<p>
    <?= lang('activities.very_simple_you_add_scientific_activities_to_osiris_whenever_you_publish_pr', replace: ['affiliation' => $Settings->get('affiliation')]) ?>
</p>

<p>
    <?=lang('activities.in_the_following_table_you_can_see_an_overview_of_the_coins')?>
</p>

<table class="table simple small">
    <?php
        foreach ($Categories->categories as $cat) {
            $color = $cat['color'] ?? 'var(--muted-color)';
            ?>
            <tr>
                <th style="color: <?=$color?>;"><?=lang($cat['name'], $cat['name_de']?? $cat['name'])?></th>
            
                <td></td>
            </tr>
            <?php foreach ($cat['children'] as $type) { ?>
                   <tr><td></td>
                    <td>
                        <b class="key" style="color: <?=$color?>;"><?=lang($type['name'], $type['name_de']?? $type['name'])?></b>
                        <?=$type['coins'] ?? '-'?>
                    </td>
                   </tr>
                <?php } ?>
        <?php }
    ?>
    </tbody>

</table>