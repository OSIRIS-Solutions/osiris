<?php

/**
 * Page for admin dashboard for role settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.2.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$view = $_GET['view'] ?? 'new';

$json = file_get_contents(BASEPATH . "/roles.json");
$role_groups = json_decode($json, true, 512, JSON_NUMERIC_CHECK);

$req = $osiris->adminGeneral->findOne(['key' => 'roles']);
$roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

// if user and scientist are not in the roles, add them
if (!in_array('user', $roles)) {
    $roles[] = 'user';
}
if (!in_array('scientist', $roles)) {
    $roles[] = 'scientist';
}
// sort admin last
$roles = array_diff($roles, ['admin']);
$roles = array_merge($roles, ['admin']);

$rights = [];
foreach ($osiris->adminRights->find([]) as $row) {
    $rights[$row['right']][$row['role']] = $row['value'];
}
?>

<!-- change view -->
<div class="btn-group float-right">
    <a class="btn small <?= $view === 'new' ? 'active' : '' ?>" href="?view=new">
        <i class="ph ph-newspaper" aria-hidden="true"></i>
        <?= lang('New View', 'Neue Ansicht') ?>
    </a>
    <a class="btn small <?= $view === 'legacy' ? 'active' : '' ?>" href="?view=legacy">
        <i class="ph ph-archive" aria-hidden="true"></i>
        <?= lang('Legacy View', 'Legacy Ansicht') ?>
    </a>
</div>

<style>
    .table td.description {
        color: var(--muted-color);
        padding-top: 0;
        padding-left: 2rem;
        padding-right: 2rem;
    }

    .with-description td {
        border-bottom: 0;
    }
</style>
<!-- modal to add and remove roles -->
<div class="modal" id="role-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Edit Roles', 'Rollen bearbeiten') ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= ROOTPATH ?>/crud/admin/roles" method="post">
                    <table class="table simple w-auto">
                        <thead>
                            <th><?= lang('Role', 'Rolle') ?></th>
                            <th><?= lang('Action', 'Aktion') ?></th>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role) { ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="roles[]" value="<?= $role ?>">
                                        <?= strtoupper($role) ?>
                                    </td>
                                    <td>
                                        <?php if (!in_array($role, ['user', 'scientist', 'admin', 'editor'])) { ?>
                                            <button class="btn danger" role="button" onclick="$(this).closest('tr').remove()">
                                                <i class="ph ph-x"></i>
                                                <?= lang('Remove', 'Entfernen') ?>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn disabled" role="button" disabled>
                                                <i class="ph ph-x"></i>
                                                <?= lang('Remove', 'Entfernen') ?>
                                            </button>
                                        <?php } ?>

                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="<?= lang('Role', 'Rolle') ?>" id="newrole">
                            <div class="input-group-append">
                                <button class="btn success" type="button" onclick="addRole()">
                                    <i class="ph ph-plus"></i>
                                    <?= lang('Add', 'Hinzufügen') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button class="btn success">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('Save', 'Speichern') ?>
                    </button>
                </form>
                <script>
                    function addRole() {
                        var role = $('#newrole').val();
                        if (role) {
                            $('#role-modal tbody').append(`<tr>
                                <td>
                                    <input type="hidden" name="roles[]" value="${role.toLowerCase()}">
                                    ${role.toUpperCase()}
                                </td>
                                <td>
                                    <button class="btn danger" role="button" onclick="$(this).closest('tr').remove()">
                                        <i class="ph ph-x"></i>
                                        <?= lang('Remove', 'Entfernen') ?>
                                    </button>
                                </td>
                            </tr>`);
                            $('#newrole').val('');
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</div>

<h1>
    <?= lang('Roles &amp; Rights', 'Rollen &amp; Rechte') ?>
</h1>

<div class="mb-5">
    <a href="<?=ROOTPATH?>/admin/roles/distribute">
        <i class="ph ph-user-gear" aria-hidden="true"></i>
        <?= lang('Distribute roles', 'Rollen verteilen') ?>
    </a>
</div>

<style>
    .description {
        color: var(--muted-color);
        padding-top: 0;
        padding-left: 2.5rem;
        font-size: small;
        margin: .5rem 0 0;
    }

    .collapse-header {
        font-weight: bold;
    }

    .collapse-header i {
        margin-right: 0.5rem;
        color: var(--primary-color);
    }

    .collapse-group {
        display: none;
    }

    .collapse-group.active {
        display: block;
    }
</style>


<?php if ($view != 'legacy') { ?>

    <!-- search -->
    <div class="form-group with-icon">
        <input type="text" class="form-control" placeholder="<?= lang('Search', 'Suchen') ?>" id="search-role" onkeyup="filterRoles()">
        <i class="ph ph-x" onclick="$('#search-role').val('').trigger('keyup')"></i>
    </div>

    <form action="<?= ROOTPATH ?>/crud/admin/roles" method="post" id="role-form">

        <div class="tabs">
            <?php foreach ($roles as $role) { ?>
                <button class="btn" type="button" onclick="selectRole('<?= $role ?>')" id="btn-<?= $role ?>" <?= $role === 'user' ? 'class="active"' : '' ?>>
                    <?= strtoupper($role) ?>
                </button>
            <?php } ?>

            <a class="btn float-right" href="#role-modal">
                <i class="ph ph-edit" aria-hidden="true"></i>
                <?= lang('Roles', 'Rollen') ?>
            </a>
        </div>
        <?php foreach ($roles as $role) { ?>
            <div class="collapse-group" id="role-<?= $role ?>">
                <?php foreach ($role_groups as $group) {
                ?>
                    <details class="collapse-panel">
                        <summary class="collapse-header">
                            <i class="ph ph-<?= $group['icon'] ?? 'gear' ?>"></i>
                            <?= lang($group['en'], $group['de']) ?>
                        </summary>
                        <div class="collapse-content">
                            <table class="table simple">
                                <?php foreach ($group['fields'] as $field) {
                                    $right = $field['id'];
                                    $values = $rights[$right] ?? array();
                                    $role = $role ?? 'user'; // Default to user if not set
                                    $val = $values[$role] ?? false;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="custom-checkbox">
                                                <input id="role-<?= $right ?>-<?= $role ?>" type="checkbox" <?= $val ? 'checked' : '' ?> onchange="$(this).next().val(Number(this.checked))">
                                                <input type="hidden" name="values[<?= $right ?>][<?= $role ?>]" value="<?= $val ? 1 : 0 ?>">
                                                <label for="role-<?= $right ?>-<?= $role ?>">
                                                    <?= lang($field['en'], $field['de']) ?>
                                                </label>
                                                <?php if (isset($field['comment_en'])) { ?>
                                                    <p class="description"><?= lang($field['comment_en'], $field['comment_de']) ?></p>
                                                <?php } ?>

                                                <code class="code font-size-12 text-muted hidden"><?= $right ?></code>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </details>
                <?php } ?>
            </div>
        <?php } ?>

        <button class="btn success mt-20">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>

    <script>
        function selectRole(role) {
            // Remove active class from all tabs
            $('.tabs button').removeClass('active');
            // Add active class to the clicked tab
            $('#btn-' + role).addClass('active');
            // Hide all role groups
            $('.collapse-group').removeClass('active');
            // Show the selected role group
            const group = $('#role-' + role);
            if (group.hasClass('active')) {
                group.removeClass('active');
            } else {
                group.addClass('active');
            }
        }

        function filterRoles() {
            const searchTerm = $('#search-role').val().toLowerCase();
            console.log(searchTerm);
            // Remove previous highlights
            $('.collapse-content').each(function() {
                $(this).find('.highlight-search').each(function() {
                    const parent = $(this).parent();
                    $(this).replaceWith($(this).text());
                    parent[0].normalize && parent[0].normalize();
                });
            });

            if (searchTerm.length < 2) {
                // reset all panels if search term is less than 2 characters
                $('.collapse-panel').show();
                return;
            }


            $('.collapse-group').each(function() {
                const group = $(this);
                group.find('.collapse-content').each(function() {
                    const content = $(this);
                    let matched = false;

                    if (searchTerm && content.text().toLowerCase().includes(searchTerm)) {
                        // Highlight matches in all text nodes inside .collapse-content
                        content.find('*').addBack().contents().filter(function() {
                            return this.nodeType === 3 && this.nodeValue.toLowerCase().includes(searchTerm);
                        }).each(function() {
                            const regex = new RegExp('(' + searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                            $(this).replaceWith(this.nodeValue.replace(regex, '<span class="highlight-search">$1</span>'));
                        });
                        matched = true;
                        content.closest('.collapse-panel').show();
                    } else {
                        content.closest('.collapse-panel').hide();
                    }
                });
            });
        }

        // Add highlight style
        $(document).ready(function() {
            if (!$('style#highlight-search-style').length) {
                $('<style id="highlight-search-style">.highlight-search { background: yellow; color: black; }</style>').appendTo('head');
            }
        });
        $(document).ready(function() {
            // Set the first tab as active
            $('.tabs button').first().addClass('active');
            // Show the first role group
            $('.collapse-group').first().addClass('active');
        });
    </script>

<?php } else { ?>

    <form action="<?= ROOTPATH ?>/crud/admin/roles" method="post" id="role-form">

        <style>
            .table.sticky-head thead {
                position: sticky;
                top: 0;
                background: white;
                z-index: 1;
                top: 6rem;
            }
        </style>

        <table class="table my-20 sticky-head">

            <thead>
                <th>
                    <a class="btn small" href="#role-modal">
                        <i class="ph ph-edit" aria-hidden="true"></i>
                        <?= lang('Roles', 'Rollen') ?>
                    </a>
                </th>
                <?php foreach ($roles as $role) { ?>
                    <th>
                        <input type="hidden" readonly name="roles[]" value="<?= $role ?>">
                        <?= strtoupper($role) ?>
                    </th>
                <?php } ?>
            </thead>
            <tbody>
                <?php foreach ($role_groups as $group) {
                ?>
                    <tr>
                        <th colspan="<?= count($roles) + 1 ?>">
                            <?= lang($group['en'], $group['de']) ?>
                        </th>
                    </tr>
                    <?php foreach ($group['fields'] as $field) {
                        $right = $field['id'];
                        $values = $rights[$right] ?? array();
                    ?>
                        <tr>
                            <td class="pl-20">
                                <?= lang($field['en'], $field['de']) ?>
                                <code class="code font-size-12 text-muted"><?= $right ?></code>
                            </td>
                            <?php foreach ($roles as $role) {
                                $val = $values[$role] ?? false;
                            ?>
                                <td>
                                    <input type="checkbox" <?= $val ? 'checked' : '' ?> onchange="$(this).next().val(Number(this.checked))">
                                    <input type="hidden" name="values[<?= $right ?>][<?= $role ?>]" value="<?= $val ? 1 : 0 ?>">
                                </td>
                            <?php } ?>

                        </tr>
                    <?php } ?>

                <?php } ?>
            </tbody>

        </table>

        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            Save
        </button>


    </form>

<?php } ?>