<?php

/** Rows for the configured project/proposal phase inside the surrounding table. */
foreach ($details as $detail):
    if ($detail['type'] === 'hr') continue;
    $cls = $detail['type'] === 'heading' ? 'project-detail-section' : '';
?>
    <tr class="<?= $cls ?>">
        <td>
            <?php if ($detail['type'] === 'heading'): ?>
                <strong><?= e($detail['value']) ?></strong>
            <?php elseif ($detail['type'] === 'paragraph'): ?>
                <?= nl2br(e($detail['value'])) ?>
            <?php else: ?>
                <span class="key"><?= e($detail['label']) ?></span>
                <?= $detail['value'] === '' || $detail['value'] === null ? '-' : $detail['value'] ?>
                <?php if ($detail['id'] === 'image' && !$Project->isProposal && $edit_perm): ?>
                    <br><a href="#edit-image" data-toggle="modal"><i class="ph ph-image"></i> <?= lang('action.edit') ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>