<?php 

/**
 * Admin page for managing footer contents such as legal notice, privacy policy and custom links
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/footer
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<style>
    #custom-footer .ql-editor p,
    #custom-footer .ql-editor ol,
    #custom-footer .ql-editor ul,
    #custom-footer .ql-editor pre,
    #custom-footer .ql-editor blockquote,
    #custom-footer .ql-editor h1,
    #custom-footer .ql-editor h2,
    #custom-footer .ql-editor h3,
    #custom-footer .ql-editor h4,
    #custom-footer .ql-editor h5,
    #custom-footer .ql-editor h6 {
        margin-bottom: 1rem;
    }
</style>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <div class="container w-800 mw-full" id="custom-footer">
        <h1>
            <i class="ph-duotone ph-scales"></i>
            <?= lang('admin.footer_contents') ?>
        </h1>
        <p>
            <?= lang('admin.you_can_add_custom_link_to_the_footer_of_your_osiris_installation_and_manag') ?>
        </p>

        <h2><?= lang('admin.legal_notice') ?></h2>
        <?php
        $impress = $Settings->get('impress');
        if (empty($impress)) {
            $impress = file_get_contents(BASEPATH . '/pages/impressum.html');
        }
        ?>
        <div class="form-group">
            <div>
                <div class="form-group title-editor" id="impress-quill"><?= $impress ?></div>
                <textarea class="form-control hidden" name="general[impress]" id="impress"><?= e($impress) ?></textarea>
            </div>

            <script>
                quillEditor('impress');
            </script>
        </div>
        <h2><?= lang('admin.privacy_policy') ?></h2>
        <?php
        $privacy = $Settings->get('privacy');
        if (empty($privacy)) {
            $privacy = file_get_contents(BASEPATH . '/pages/privacy.html');
        }
        ?>
        <div class="form-group">
            <div>
                <div class="form-group title-editor" id="privacy-quill"><?= $privacy ?></div>
                <textarea class="form-control hidden" name="general[privacy]" id="privacy"><?= e($privacy) ?></textarea>
            </div>
            <script>
                quillEditor('privacy');
            </script>
        </div>

        <h3><?= lang('common.links') ?></h3>
        <p>
            <?= lang('admin.you_can_add_links_to_external_resources_that_are_relevant_for_your_users_th') ?>
        </p>

        <?php
        $links = $Settings->get('footer_links', []);
        ?>
        <!-- make sure empty links are saved too -->
        <input type="hidden" name="footer_links" value="">
        <table class="table mb-20" id="footer-links-table">
            <thead>
                <tr>
                    <th><?= lang('admin.title_en') ?></th>
                    <th><?= lang('admin.title_de') ?></th>
                    <th><?= lang('admin.link_url_complete') ?></th>
                    <th><?= lang('common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                    <tr>
                        <td>
                            <input type="text" class="form-control" name="footer_links[name][]" value="<?= e($link['name'] ?? '') ?>" placeholder="<?= lang('admin.link_name_en') ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="footer_links[name_de][]" value="<?= e($link['name_de'] ?? '') ?>" placeholder="<?= lang('admin.link_name_de') ?>">
                        </td>
                        <td>
                            <input type="url" class="form-control" name="footer_links[url][]" value="<?= e($link['url'] ?? '') ?>" placeholder="<?= lang('admin.link_url_complete') ?>">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash" title="<?= lang('action.delete') ?>"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="button" class="btn btn-primary" onclick="addLink()"><?= lang('admin.add_new_link') ?></button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <script>
            function addLink() {
                const tbody = $('#footer-links-table tbody');
                const newRow = `
                            <tr>
                                <td><input type="text" class="form-control" name="footer_links[name][]" placeholder="<?= lang('admin.link_name_en') ?>"></td>
                                <td><input type="text" class="form-control" name="footer_links[name_de][]" placeholder="<?= lang('admin.link_name_de') ?>"></td>
                                <td><input type="url" class="form-control" name="footer_links[url][]" placeholder="<?= lang('admin.link_url_complete') ?>"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash" title="<?= lang('action.delete') ?>"></i></button></td>
                            </tr>`;
                tbody.append(newRow);
            }
        </script>

        <div class="bottom-buttons">
            <button class="btn success large">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('action.save') ?>
            </button>
        </div>
    </div>
</form>