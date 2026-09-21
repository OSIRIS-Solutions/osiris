<?php

/**
 * Routing file for admin settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/admin', function () {
    include_once BASEPATH . "/php/init.php";
    $adminPerm = $Settings->hasPermission('admin.see');
    $userSyncPerm = $Settings->hasPermission('user.synchronize');
    $reportPerm = $Settings->hasPermission('report.templates');
    if (!$adminPerm && !$userSyncPerm && !$reportPerm) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $breadcrumb = [
        ['name' => lang('common.settings')],
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/admin.php";
    include BASEPATH . "/footer.php";
}, 'login');


include_once BASEPATH . "/routes/admin.fields.php";


Route::get('/admin/users', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('admin.users')]
    ];
    $page = 'users';
    include BASEPATH . "/header.php";
    if (strtoupper(USER_MANAGEMENT) == 'LDAP') {
        include BASEPATH . "/pages/synchronize-users.php";
    } else {
        include BASEPATH . "/pages/admin/users.php";
    }

    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/guest-account', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('admin.users'), 'path' => '/admin/users'],
        ['name' => lang('people.guest_account'), 'path' => '/admin/guest-account']
    ];
    $page = 'users';
    include BASEPATH . "/header.php";
    if (!strtoupper(USER_MANAGEMENT) == 'LDAP') {
        echo "<div class='alert warning mb-10'>" . lang('admin.guest_accounts_can_only_be_added_when_ldap_user_management_is_enabled') . "</div>";
    } else {
        include BASEPATH . "/pages/admin/guest-account.php";
    }

    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/guest-account/add', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('admin.users'), 'path' => '/admin/users'],
        ['name' => lang('people.guest_account'), 'path' => '/admin/guest-account'],
        ['name' => lang('action.add')]
    ];
    $page = 'users';
    include BASEPATH . "/header.php";
    if (!strtoupper(USER_MANAGEMENT) == 'LDAP') {
        echo "<div class='alert warning mb-10'>" . lang('admin.guest_accounts_can_only_be_added_when_ldap_user_management_is_enabled') . "</div>";
    } else {
        include BASEPATH . "/pages/admin/guest-account-add.php";
    }

    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/roles/distribute', function () {
    include_once BASEPATH . "/php/init.php";
    $page = 'admin/roles';
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.roles'), 'path' => '/admin/roles'],
        ['name' => lang('common.distribute_roles')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/distribute-roles.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/templates', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('admin.activities'), 'path' => "/admin/categories"],
    ];

    $type = null;
    $template = '';
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $type = $_GET['type'];
        $typeArr = $osiris->adminTypes->findOne(['id' => $type]);
        if (!empty($typeArr)) {
            $breadcrumb[] = ['name' => lang($typeArr['name'], $typeArr['name_de']), 'path' => "/admin/types/$type"];

            $templates = $typeArr['template'];
            $template = $templates['print'];
        }
    }
    $breadcrumb[] = ['name' => lang('common.templates')];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/template-builder.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/module-helper', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Modules.php";
    $breadcrumb = [
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => lang('common.new')],
        ['name' => lang('common.data_fields')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/module-helper.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/types/(.*)/fields', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $type = $osiris->adminTypes->findOne(['id' => $id]);
    if (empty($type)) {
        abortwith(404, lang('common.type'), "/admin/categories");
    }
    $name = lang($type['name'], $type['name_de']);

    $t = $type['parent'];
    $parent = $osiris->adminCategories->findOne(['id' => $t]);
    $color = $parent['color'] ?? '#000000';
    $st = $type['id'];
    $submember = $osiris->activities->count(['type' => $t, 'subtype' => $st]);

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang($parent['name'], $parent['name_de']), 'path' => "/admin/categories/" . $t],
        ['name' => $name, 'path' => "/admin/types/" . $id],
        ['name' => lang('common.data_fields')]
    ];

    global $form;
    $form = DB::doc2Arr($type);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/form-builder.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/doi-mappings', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => lang('admin.doi_mappings')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/doi-mappings.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/categories', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/categories.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/categories/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => lang('common.new')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/category.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/categories/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];

    $category = $osiris->adminCategories->findOne(['id' => $id]);
    if (empty($category) && is_numeric($id)) {
        // try if it id is saved as integer
        $category = $osiris->adminCategories->findOne(['id' => intval($id)]);
    }
    if (empty($category)) {
        abortwith(404, lang('common.category'), "/admin/categories");
    }
    $name = lang($category['name'], $category['name_de']);
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => $name]
    ];

    global $form;
    $form = DB::doc2Arr($category);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/category.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/admin/types/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => lang('admin.new_type')]
    ];
    $t = $_GET['parent'] ?? '';
    $st = $t;
    $type = [
        "id" => '',
        "icon" => $type['icon'] ?? 'folder-open',
        "name" => '',
        "name_de" => '',
        "new" => true,
        "modules" => [
            "title",
            "authors",
            "date"
        ],
        "template" => [
            "print" => "{authors} ({year}) {title}.",
            "title" => "{title}",
            "subtitle" => "{authors}, {date}"
        ],
        "coins" => 0,
        "parent" => $t

    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/category-type.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/admin/types/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $user = $_SESSION['username'];

    $type = $osiris->adminTypes->findOne(['id' => $id]);
    if (empty($type)) {
        abortwith(404, lang('common.type'), "/admin/categories");
    }
    $name = lang($type['name'], $type['name_de']);

    $t = $type['parent'];
    $parent = $osiris->adminCategories->findOne(['id' => $t]);
    $color = $parent['color'] ?? '#000000';
    $st = $type['id'];
    $submember = $osiris->activities->count(['type' => $t, 'subtype' => $st]);

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.activities'), 'path' => "/admin/categories"],
        ['name' => lang($parent['name'], $parent['name_de']), 'path' => "/admin/categories/" . $t],
        ['name' => $name]
    ];

    global $form;
    $form = DB::doc2Arr($type);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/category-type.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/settings/activities', function () {
    include_once BASEPATH . "/php/init.php";

    $t = $_GET['type'];
    $type = $osiris->adminTypes->findOne(['id' => $t]);
    if (empty($type)) {
        // try if it is a category with otherwise named children
        $type = $osiris->adminTypes->findOne(['parent' => $t]);
    }
    if (empty($type)) {
        echo return_rest(['error' => lang('admin.type_not_found_please_select_the_correct_type_manually')]);
        die();
    }
    $parent = $osiris->adminCategories->findone(['id' => $type['parent']]);
    echo return_rest([
        'category' => DB::doc2Arr($parent),
        'type' => DB::doc2Arr($type)
    ]);
});


Route::get('/admin/vocabulary', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();
    $vocabularies = $Vocabulary->getVocabularies();

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.vocabulary')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/vocabulary.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/vocabulary/([a-z\-_]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();
    $vocab = $Vocabulary->getVocabulary($id);
    if (empty($vocab)) {
        abortwith(404, lang('common.vocabulary'), "/admin/vocabulary");
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.vocabulary'), 'path' => '/admin/vocabulary'],
        ['name' => lang($vocab['name'], $vocab['name_de'] ?? null)]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/vocabulary-edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/settings/modules', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Modules.php";

    $form = array();
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $mongoid = $DB->to_ObjectID($_GET['id']);
        if (isset($_GET['draft']) && $_GET['draft'] == 'true') {
            $form = $osiris->activitiesDrafts->findOne(['_id' => $mongoid]);
        } else {
            $form = $osiris->activities->findOne(['_id' => $mongoid]);
        }
    }
    $Modules = new Modules($form, $_GET['copy'] ?? false, $_GET['conference'] ?? false);

    if (isset($_GET['type']) && !empty($_GET['type'])) {
        // new in 1.5.1
        $Modules->print_form($_GET['type']);
    } else if (isset($_GET['modules'])) {
        $Modules->print_modules($_GET['modules']);
    }
});



Route::get('/admin/persons', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.persons')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/persons.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/infrastructures', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.infrastructures')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/infrastructures.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/projects', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.projects')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/projects.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/projects/([123])/(.*)', function ($stage, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (DB::is_ObjectID($id)) {
        $project = $osiris->adminProjects->findOne(['_id' => $DB->to_ObjectID($id)]);
    } else {
        $project = $osiris->adminProjects->findOne(['id' => $id]);
    }
    if (empty($project)) {
        $project = array();
    } else {
        $project = DB::doc2Arr($project);
    }
    $type = $project['id'];

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.projects'), 'path' => '/admin/projects'],
        ['name' => $type . ' - ' . $stage . '/2']
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/project.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/projects/new', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $stage = 1;
    $project = array();
    $type = null;

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.projects'), 'path' => '/admin/projects'],
        ['name' => lang('admin.new_project_type') . ' - ' . $stage . '/2']
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/project.php";
    include BASEPATH . "/footer.php";
}, 'login');




Route::get('/admin/resource-hub-image-map', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.resource_hub'), 'path' => '/admin/resource-hub'],
        ['name' => lang('admin.arrange_image_map')],
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/resource-hub-image-map.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/phpinfo', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    function getPhpinfo()
    {
        ob_start();
        phpinfo();
        $data = ob_get_contents();
        ob_end_clean();
        // remove the style and script tags from the output
        $data = preg_replace('#<style[^>]*>.*?</style>#is', '', $data);
        // add "table" class to all tables
        $data = preg_replace('#<table#', '<table class="table"', $data);
        return $data;
    }

    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('admin.php_info')]
    ];
    $phpinfo = getPhpinfo();
    include BASEPATH . "/header.php";
    echo "<link rel='stylesheet' href='" . ROOTPATH . "/css/phpinfo.css'>";
    echo "<div class='phpinfo-container'>$phpinfo</div>";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/osirisinfo', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => lang('common.osiris_info')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/osiris-info.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/(.*)', function ($path) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $breadcrumb = [
        ['name' => lang('common.settings'), 'path' => '/admin'],
        ['name' => ucfirst($path)]
    ];
    if (!file_exists(BASEPATH . "/pages/admin/$path.php")) {
        abortwith(404, lang('common.settings'), "/admin");
    }
    if ($path === 'api-clients') {
        header('Cache-Control: no-store');
    }
    include BASEPATH . "/header.php";
    $affiliation = $Settings->get('affiliation_details');
    include_once BASEPATH . '/header-editor.php';

    echo '<script src="' . ROOTPATH . '/js/general-settings.js"></script>';
    include BASEPATH . "/pages/admin/$path.php";
    include BASEPATH . "/footer.php";
}, 'login');


/**
 * CRUD routes
 */

Route::post('/crud/admin/api-clients/create', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/ApiClient.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
    $description = trim(strip_tags((string) ($_POST['description'] ?? '')));
    $surfaces = ApiClient::filterSurfaces($_POST['surfaces'] ?? []);
    $scopes = ApiClient::filterScopes($_POST['scopes'] ?? []);
    $expiresAt = trim((string) ($_POST['expires_at'] ?? '')) ?: null;
    if ($name === '' || mb_strlen($name) > 200 || empty($surfaces) || empty($scopes)) {
        $_SESSION['msg'] = lang('admin.please_provide_a_name_and_select_at_least_one_api_area_and_permission');
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . '/admin/api-clients');
        die();
    }
    if ($expiresAt !== null) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $expiresAt);
        if ($date === false || $date->format('Y-m-d') !== $expiresAt) {
            $_SESSION['msg'] = lang('admin.the_expiration_date_is_invalid');
            $_SESSION['msg_type'] = 'error';
            header('Location: ' . ROOTPATH . '/admin/api-clients');
            die();
        }
    }

    $clients = new ApiClient($osiris);
    $credentials = $clients->create(
        mb_substr($name, 0, 200),
        mb_substr($description, 0, 1000),
        $surfaces,
        $scopes,
        $expiresAt,
        $_SESSION['username'] ?? ''
    );
    $_SESSION['api_client_credentials'] = $credentials;
    $_SESSION['msg'] = lang('admin.api_client_created_copy_the_secret_now_it_will_not_be_shown_again');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/api-clients');
    die();
}, 'login');

Route::post('/crud/admin/api-clients/update/([a-z0-9_]+)', function ($clientId) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/ApiClient.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $name = trim(strip_tags((string) ($_POST['name'] ?? '')));
    $surfaces = ApiClient::filterSurfaces($_POST['surfaces'] ?? []);
    $scopes = ApiClient::filterScopes($_POST['scopes'] ?? []);
    $expiresAt = trim((string) ($_POST['expires_at'] ?? '')) ?: null;
    if ($name === '' || empty($surfaces) || empty($scopes)) {
        $_SESSION['msg'] = lang('admin.name_api_area_and_permissions_are_required');
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . '/admin/api-clients');
        die();
    }
    if ($expiresAt !== null) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $expiresAt);
        if ($date === false || $date->format('Y-m-d') !== $expiresAt) {
            $_SESSION['msg'] = lang('admin.the_expiration_date_is_invalid');
            $_SESSION['msg_type'] = 'error';
            header('Location: ' . ROOTPATH . '/admin/api-clients');
            die();
        }
    }
    $osiris->apiClients->updateOne(
        ['client_id' => $clientId],
        ['$set' => [
            'name' => mb_substr($name, 0, 200),
            'description' => mb_substr(trim(strip_tags((string) ($_POST['description'] ?? ''))), 0, 1000) ?: null,
            'surfaces' => $surfaces,
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['username'] ?? null,
        ]]
    );
    $_SESSION['msg'] = lang('admin.api_client_updated');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/api-clients');
    die();
}, 'login');

Route::post('/crud/admin/api-clients/toggle/([a-z0-9_]+)', function ($clientId) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $osiris->apiClients->updateOne(
        ['client_id' => $clientId],
        ['$set' => [
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['username'] ?? null,
        ]]
    );
    $_SESSION['msg'] = $enabled
        ? lang('admin.api_client_enabled')
        : lang('admin.api_client_disabled');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/api-clients');
    die();
}, 'login');

Route::post('/crud/admin/api-clients/rotate/([a-z0-9_]+)', function ($clientId) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/ApiClient.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $clients = new ApiClient($osiris);
    $secret = $clients->rotate($clientId);
    if ($secret === null) {
        abortwith(404, lang('admin.api_client'), '/admin/api-clients');
    }
    $_SESSION['api_client_credentials'] = ['client_id' => $clientId, 'secret' => $secret];
    $_SESSION['msg'] = lang('admin.the_api_secret_was_rotated_the_previous_secret_is_no_longer_valid');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/api-clients');
    die();
}, 'login');

Route::post('/crud/admin/api-clients/delete/([a-z0-9_]+)', function ($clientId) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    $osiris->apiClients->deleteOne(['client_id' => $clientId]);
    $_SESSION['msg'] = lang('admin.api_client_deleted');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/api-clients');
    die();
}, 'login');

function redirectFromResourceHubImage(string $message, string $type = 'error'): void
{
    $_SESSION['msg'] = $message;
    $_SESSION['msg_type'] = $type;
    header('Location: ' . ROOTPATH . '/admin/resource-hub#image-map-configuration');
    die;
}

Route::post('/crud/admin/resource-hub/image', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('admin.you_do_not_have_permission_to_manage_the_resource_hub'), '/', lang('navigation.go_back_home'));
    }

    $file = $_FILES['image'] ?? null;
    if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => lang('admin.the_image_is_too_large_a_maximum_of_10_mb_is_allowed'),
            UPLOAD_ERR_PARTIAL => lang('common.the_image_was_only_partially_uploaded'),
            UPLOAD_ERR_NO_TMP_DIR => lang('common.the_temporary_upload_directory_is_missing'),
            UPLOAD_ERR_CANT_WRITE => lang('common.the_image_could_not_be_written_to_disk'),
            UPLOAD_ERR_EXTENSION => lang('common.a_php_extension_stopped_the_upload'),
            default => lang('admin.please_select_an_image_to_upload'),
        };
        redirectFromResourceHubImage($message);
    }

    if ((int) $file['size'] > 10 * 1024 * 1024) {
        redirectFromResourceHubImage(lang('admin.the_image_is_too_large_a_maximum_of_10_mb_is_allowed'));
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $dimensions = @getimagesize($file['tmp_name']);
    if ($dimensions === false || !isset($allowedMimeTypes[$mime])) {
        redirectFromResourceHubImage(lang('common.only_jpeg_png_and_webp_images_are_allowed'));
    }

    [$width, $height] = $dimensions;
    if ($width < 1200 || $height < 600) {
        redirectFromResourceHubImage(lang('admin.the_image_is_too_small_it_must_be_at_least_1200_x_600_pixels'));
    }
    if ($width > 5000 || $height > 3000 || $width * $height > 15000000) {
        redirectFromResourceHubImage(lang('admin.the_image_is_too_large_it_may_be_no_larger_than_5000_x_3000_pixels_or_15_me'));
    }
    if ($width <= $height) {
        redirectFromResourceHubImage(lang('admin.please_use_a_landscape_image'));
    }

    $targetDirectory = BASEPATH . '/uploads/resource-hub';
    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        redirectFromResourceHubImage(lang('common.the_upload_directory_could_not_be_created'));
    }

    $extension = $allowedMimeTypes[$mime];
    $filename = bin2hex(random_bytes(12)) . '.' . $extension;
    $targetPath = $targetDirectory . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        redirectFromResourceHubImage(lang('common.the_image_could_not_be_saved'));
    }

    $settingsDocument = $osiris->adminGeneral->findOne(['key' => 'resource-hub']);
    $settingsValue = DB::doc2Arr($settingsDocument['value'] ?? []);
    $imageMap = DB::doc2Arr($settingsValue['image-map'] ?? []);
    $oldImage = DB::doc2Arr($imageMap['image'] ?? []);

    $image = [
        'file' => 'resource-hub/' . $filename,
        'mime' => $mime,
        'width' => (int) $width,
        'height' => (int) $height,
        'size' => (int) $file['size'],
        'uploaded' => date('Y-m-d H:i:s'),
        'uploaded_by' => $_SESSION['username'] ?? null,
    ];

    try {
        $osiris->adminGeneral->updateOne(
            ['key' => 'resource-hub'],
            ['$set' => ['value.image-map.image' => $image]],
            ['upsert' => true]
        );
    } catch (Throwable $exception) {
        @unlink($targetPath);
        redirectFromResourceHubImage(lang('admin.the_image_configuration_could_not_be_saved'));
    }

    $oldFile = (string) ($oldImage['file'] ?? '');
    if (preg_match('#^resource-hub/[a-f0-9]{24}\.(jpg|png|webp)$#', $oldFile)) {
        $oldPath = BASEPATH . '/uploads/' . $oldFile;
        if ($oldPath !== $targetPath && is_file($oldPath)) @unlink($oldPath);
    }

    redirectFromResourceHubImage(lang('admin.the_background_image_was_uploaded_successfully'), 'success');
}, 'login');

Route::post('/crud/admin/resource-hub/image/delete', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('admin.you_do_not_have_permission_to_manage_the_resource_hub'), '/', lang('navigation.go_back_home'));
    }

    $settingsDocument = $osiris->adminGeneral->findOne(['key' => 'resource-hub']);
    $settingsValue = DB::doc2Arr($settingsDocument['value'] ?? []);
    $imageMap = DB::doc2Arr($settingsValue['image-map'] ?? []);
    $image = DB::doc2Arr($imageMap['image'] ?? []);

    try {
        $osiris->adminGeneral->updateOne(
            ['key' => 'resource-hub'],
            ['$unset' => ['value.image-map.image' => true]]
        );
    } catch (Throwable $exception) {
        redirectFromResourceHubImage(lang('admin.the_image_configuration_could_not_be_removed'));
    }

    $file = (string) ($image['file'] ?? '');
    if (preg_match('#^resource-hub/[a-f0-9]{24}\.(jpg|png|webp)$#', $file)) {
        $path = BASEPATH . '/uploads/' . $file;
        if (is_file($path)) @unlink($path);
    }

    redirectFromResourceHubImage(lang('admin.the_background_image_was_removed'), 'success');
}, 'login');

Route::post('/crud/admin/resource-hub/image-map', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('admin.you_do_not_have_permission_to_manage_the_resource_hub'), '/', lang('navigation.go_back_home'));
    }

    $settingsDocument = $osiris->adminGeneral->findOne(['key' => 'resource-hub']);
    $settingsValue = DB::doc2Arr($settingsDocument['value'] ?? []);
    $cards = DB::doc2Arr($settingsValue['cards'] ?? []);
    $imageMap = DB::doc2Arr($settingsValue['image-map'] ?? []);
    $image = DB::doc2Arr($imageMap['image'] ?? []);

    if (empty($cards) || empty($image['file'])) {
        $_SESSION['msg'] = lang('admin.upload_a_background_image_and_create_at_least_one_card_first');
        $_SESSION['msg_type'] = 'warning';
        header('Location: ' . ROOTPATH . '/admin/resource-hub#image-map-configuration');
        die;
    }

    $validCardIds = [];
    foreach ($cards as $card) {
        $card = DB::doc2Arr($card);
        $cardId = (string) ($card['id'] ?? '');
        if ($cardId !== '') $validCardIds[$cardId] = true;
    }

    $placements = [];
    foreach (DB::doc2Arr($_POST['placements'] ?? []) as $cardId => $placement) {
        if (!isset($validCardIds[$cardId])) continue;
        $placement = DB::doc2Arr($placement);
        if (!isset($placement['x'], $placement['y']) || !is_numeric($placement['x']) || !is_numeric($placement['y'])) continue;

        $x = round((float) $placement['x'], 2);
        $y = round((float) $placement['y'], 2);
        if ($x < 0 || $x > 100 || $y < 0 || $y > 100) continue;

        $placements[$cardId] = ['x' => $x, 'y' => $y];
    }

    try {
        $osiris->adminGeneral->updateOne(
            ['key' => 'resource-hub'],
            ['$set' => ['value.image-map.placements' => $placements]],
            ['upsert' => true]
        );
    } catch (Throwable $exception) {
        $_SESSION['msg'] = lang('admin.the_card_positions_could_not_be_saved');
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . '/admin/resource-hub-image-map');
        die;
    }

    $_SESSION['msg'] = lang('admin.the_card_positions_were_saved');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/admin/resource-hub-image-map');
    die;
}, 'login');

Route::post('/crud/admin/general', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $msg = 'settings-saved';
    if (isset($_POST['general'])) {
        foreach ($_POST['general'] as $key => $value) {
            if ($key == 'auth-self-registration') $value = boolval($value);
            if (str_contains($key, 'keywords') || $key == 'tags') {
                $value = array_map('trim', explode(PHP_EOL, $value));
                $value = array_filter($value);
            }
            if ($key === 'resource-hub' && is_array($value)) {
                $icon = trim((string) ($value['icon'] ?? 'link'));
                $value['icon'] = preg_match('/^[a-z0-9-]+$/', $icon) ? $icon : 'link';

                $description = [];
                foreach (['en', 'de'] as $language) {
                    $text = trim(strip_tags((string) ($value['description'][$language] ?? '')));
                    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
                    $description[$language] = function_exists('mb_substr')
                        ? mb_substr($text, 0, 200)
                        : substr($text, 0, 200);
                }
                $value['description'] = $description;
            }
            if ($key === 'resource-hub' && is_array($value) && !array_key_exists('image-map', $value)) {
                $current = $osiris->adminGeneral->findOne(['key' => 'resource-hub']);
                $currentValue = DB::doc2Arr($current['value'] ?? []);
                if (isset($currentValue['image-map'])) {
                    $imageMap = DB::doc2Arr($currentValue['image-map']);
                    $validCardIds = [];
                    foreach (DB::doc2Arr($value['cards'] ?? []) as $card) {
                        $card = DB::doc2Arr($card);
                        if (!empty($card['id'])) $validCardIds[(string) $card['id']] = true;
                    }
                    $imageMap['placements'] = array_filter(
                        DB::doc2Arr($imageMap['placements'] ?? []),
                        fn($cardId) => isset($validCardIds[$cardId]),
                        ARRAY_FILTER_USE_KEY
                    );
                    $value['image-map'] = $imageMap;
                }
            }
            $osiris->adminGeneral->deleteOne(['key' => $key]);
            $osiris->adminGeneral->insertOne([
                'key' => $key,
                'value' => $value
            ]);
        }
    }

    if (isset($_POST['features'])) {
        $features = $_POST['features'];
        foreach ($features as $feature => $enabled) {
            $osiris->adminFeatures->deleteOne(['feature' => $feature]);
            $r = [
                'feature' => $feature,
                'enabled' => boolval($enabled)
            ];
            $osiris->adminFeatures->insertOne($r);
        }
    }

    if (isset($_POST['mail'])) {

        $osiris->adminGeneral->deleteOne(['key' => 'mail']);
        $osiris->adminGeneral->insertOne([
            'key' => 'mail',
            'value' => $_POST['mail']
        ]);
    }

    if (isset($_POST['footer_links'])) {
        $links = [];
        // join the name, name_de and url into an array
        if (isset($_POST['footer_links']['name']) && is_array($_POST['footer_links']['name'])) {
            $names = $_POST['footer_links']['name'];
            $names_de = $_POST['footer_links']['name_de'] ?? $names;
            $urls = $_POST['footer_links']['url'] ?? [];

            foreach ($names as $i => $name) {
                if (empty($name) || empty($urls[$i])) continue; // skip empty links
                $links[] = [
                    'name' => $name,
                    'name_de' => $names_de[$i] ?? $name,
                    'url' => $urls[$i]
                ];
            }
        }
        $osiris->adminGeneral->deleteOne(['key' => 'footer_links']);
        $osiris->adminGeneral->insertOne([
            'key' => 'footer_links',
            'value' => $links
        ]);
    }

    if (isset($_POST['staff'])) {
        $staff = [];
        if (isset($_POST['staff']['free'])) {
            $staff['free'] = boolval($_POST['staff']['free']);
        }
        if (isset($_POST['staff']['positions']) && !empty($_POST['staff']['positions'])) {
            $en = $_POST['staff']['positions'];
            $de = $_POST['staff']['positions_de'] ?? $en;

            $staff['positions'] = [];
            foreach ($en as $i => $e) {
                $staff['positions'][] = [
                    $e,
                    $de[$i] ?? $e
                ];
            }
        }
        $osiris->adminGeneral->deleteOne(['key' => 'staff']);
        $osiris->adminGeneral->insertOne([
            'key' => 'staff',
            'value' => $staff
        ]);
    }


    if (isset($_FILES["logo"])) {
        $filename = e(basename($_FILES["logo"]["name"]));
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["logo"]["size"];

        if ($_FILES['logo']['error'] != UPLOAD_ERR_OK) {
            $msg = match ($_FILES['logo']['error']) {
                1 => lang('error.file_upload_exceeds_limit'),
                2 => lang('error.file_upload_too_large', replace:['max' => '16 MB']),
                3 => lang('error.file_partially_uploaded'),
                4 => lang('error.no_file_uploaded'),
                6 => lang('error.file_upload_missing_temp'),
                7 => lang('error.file_upload_write_failed'),
                8 => lang('error.file_upload_stopped'),
                default => lang('error.something_went_wrong') . " (" . $_FILES['file']['error'] . ")"
            };
        } else if ($filesize > 2000000) {
            $msg = lang('error.file_too_big_max_2MB');
        } else {
            $val = new MongoDB\BSON\Binary(file_get_contents($_FILES["logo"]["tmp_name"]), MongoDB\BSON\Binary::TYPE_GENERIC);
            // first: delete logo, then: insert new one
            $osiris->adminGeneral->deleteOne(['key' => 'logo']);
            $updateResult = $osiris->adminGeneral->insertOne([
                'key' => 'logo',
                'value' => $val,
                'ext' => $filetype
            ]);
        }
    }
    if ($msg != 'settings-saved') {
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = 'error';
    } else {
        $_SESSION['msg'] = lang('admin.settings_saved_successfully');
        $_SESSION['msg_type'] = 'success';
    }

    if (isset($_POST['redirect'])) {
        header("Location: " . $_POST['redirect']);
        die();
    }

    header("Location: " . ROOTPATH . "/admin");
}, 'login');


Route::post('/crud/admin/roles', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (isset($_POST['values'])) {
        $osiris->adminRights->deleteMany([]);
        $rights = $_POST['values'];
        foreach ($rights as $right => $roles) {
            foreach ($roles as $role => $perm) {
                $r = [
                    'role' => $role,
                    'right' => $right,
                    'value' => boolval($perm)
                ];
                $osiris->adminRights->insertOne($r);
            }
        }
    }
    if (isset($_POST['roles']) && is_array($_POST['roles']) && count($_POST['roles']) > 2) {
        // user, scientist and admin must always be there
        if (!in_array('user', $_POST['roles'])) {
            $_POST['roles'][] = 'user';
        }
        if (!in_array('scientist', $_POST['roles'])) {
            $_POST['roles'][] = 'scientist';
        }
        if (!in_array('admin', $_POST['roles'])) {
            $_POST['roles'][] = 'admin';
        }
        $osiris->adminGeneral->deleteOne(['key' => 'roles']);
        $osiris->adminGeneral->insertOne([
            'key' => 'roles',
            'value' => array_map('strtolower', $_POST['roles'])
        ]);
    }

    $_SESSION['msg'] = lang('admin.settings_saved_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/roles");
}, 'login');


Route::post('/crud/admin/update-user-roles', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $roles = $_POST['roles'] ?? [];
    if (empty($roles) || !is_array($roles)) {
        $_SESSION['msg'] = lang('admin.no_roles_provided');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/admin/roles/distribute");
        die;
    }
    // get all admins to not remove admin role
    $admins = $osiris->persons->find(['roles' => 'admin'])->toArray();
    $admin_users = array_map(fn($a) => $a['username'], $admins);
    foreach ($roles as $user => $r) {
        if (!is_array($r)) $r = [];
        // check if user is admin
        if (in_array($user, $admin_users) && !in_array('admin', $r)) {
            $r[] = 'admin';
        }
        $osiris->persons->updateOne(
            ['username' => $user],
            ['$set' => ['roles' => array_map('strtolower', $r)]]
        );
    }
    $_SESSION['msg'] = lang('admin.roles_updated_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/roles/distribute");
    die;
}, 'login');


// Route::post('/crud/admin/features', function () {
//     include_once BASEPATH . "/php/init.php";
//     if (!$Settings->hasPermission('admin.see')) {
//         abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
//     }


//     if (isset($_POST['values'])) {
//         $features = $_POST['values'];
//         foreach ($features as $feature => $enabled) {
//             $osiris->adminFeatures->deleteOne(['feature' => $feature]);
//             $r = [
//                 'feature' => $feature,
//                 'enabled' => boolval($enabled)
//             ];
//             $osiris->adminFeatures->insertOne($r);
//         }
//     }

//     if (isset($_POST['general'])) {
//         foreach ($_POST['general'] as $key => $value) {
//             if (isset($value['en']) && $value['de'] == '') {
//                 $value['de'] = $value['en'];
//             }
//             $osiris->adminGeneral->deleteOne(['key' => $key]);
//             $osiris->adminGeneral->insertOne([
//                 'key' => $key,
//                 'value' => $value
//             ]);
//         }
//     }

//     $_SESSION['msg'] = lang('Settings saved successfully.', 'Einstellungen erfolgreich gespeichert.');
//     $_SESSION['msg_type'] = 'success';
//     header("Location: " . ROOTPATH . "/admin/general#features");
// }, 'login');


Route::post('/crud/(categories|types)/create', function ($col) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));

    $values = validateValues($_POST['values'], $DB);

    // if (isset($values['upload'])) $values

    if ($col == 'categories') {
        $collection = $osiris->adminCategories;
    } else {
        $collection = $osiris->adminTypes;
        if (!isset($values['parent'])) {
            $_SESSION['msg'] = lang('admin.type_must_have_a_parent_category');
            $_SESSION['msg_type'] = 'error';
            header("Location: " . ROOTPATH . "/types/new");
            die();
        }
    }

    // check if category ID already exists:
    $category_exist = $collection->findOne(['id' => $values['id']]);
    if (!empty($category_exist)) {
        $_SESSION['msg'] = lang('admin.category_id_does_already_exist');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/$col/new");
        die();
    }

    // add fields
    $values['modules'] = [
        "title*",
        "authors*",
        "date*"
    ];

    $insertOneResult  = $collection->insertOne($values);
    // $id = $insertOneResult->getInsertedId();
    $id = $values['id'];

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        $_SESSION['msg'] = lang('admin.category_created_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/(categories|types)/update/([A-Za-z0-9]*)', function ($col, $id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
    $values = validateValues($_POST['values'], $DB);

    // if (isset($values['upload'])) $values

    if ($col == 'categories') {
        $collection = $osiris->adminCategories;
        $key = 'type';
    } else {
        $collection = $osiris->adminTypes;
        $key = 'subtype';
        // types need a categorie a.k.a. parent
        if (!isset($values['parent'])) {
            die("Type must have a parent category.");
        }
    }


    // check if ID has changed
    if (isset($_POST['original_id']) && $_POST['original_id'] != $values['id']) {
        // update all connected activities 
        $osiris->activities->updateMany(
            [$key => $_POST['original_id']],
            ['$set' => [$key => $values['id']]]
        );
        $_POST['redirect'] = ROOTPATH . "/admin/types/" . $values['id'];

        if ($col == 'categories') {
            // update all connected types
            $osiris->adminTypes->updateMany(
                ['parent' => $_POST['original_id']],
                ['$set' => ['parent' => $values['id']]]
            );
            $_POST['redirect'] = ROOTPATH . "/admin/categories/" . $values['id'];
        }
    }

    if ($col == 'types') {
        // check if parent has changed
        if (isset($_POST['original_parent']) && $_POST['original_parent'] != $values['parent']) {
            // update all connected activities 
            $osiris->activities->updateMany(
                ['type' => $_POST['original_parent'], 'subtype' => $values['id']],
                ['$set' => ['type' => $values['parent']]]
            );
        }
        // checkbox default
        $values['disabled'] = $values['disabled'] ?? false;
    }

    // add information on updating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    $mongo_id = $DB->to_ObjectID($id);
    $updateResult = $collection->updateOne(
        ['_id' => $mongo_id],
        ['$set' => $values]
    );

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang('admin.category_updated_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/(categories|types)/delete/(.*)', function ($col, $id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    // select the right collection

    if ($col == 'categories') {
        $collection = $osiris->adminCategories;
        $member = $osiris->activities->count(['type' => $id]);
    } else {
        $collection = $osiris->adminTypes;
        $member = $osiris->activities->count(['subtype' => $id]);
    }

    // check that no activities are connected
    if ($member !== 0) die('Cannot delete as long as activities are connected.');

    // prepare id
    $updateResult = $collection->deleteOne(
        ['id' => $id]
    );
    if ($col == 'categories') {
        $osiris->adminTypes->deleteMany(['parent' => $id]);
    }

    $deletedCount = $updateResult->getDeletedCount();

    // addUserActivity('delete');
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang('admin.category_deleted_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'deleted' => $deletedCount
    ]);
});


Route::post('/crud/(categories|types)/update-order', function ($col) {
    include_once BASEPATH . "/php/init.php";
    // select the right collection
    if ($col == 'categories') {
        $collection = $osiris->adminCategories;
    } else {
        $collection = $osiris->adminTypes;
    }

    foreach ($_POST['order'] as $i => $id) {
        $collection->updateOne(
            ['id' => $id],
            ['$set' => ['order' => $i]]
        );
    }

    $_SESSION['msg'] = lang('common.order_updated');
    $_SESSION['msg_type'] = 'success';
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect']);
        die();
    }
});

Route::post('/crud/admin/activity-fields', function () {
    include_once BASEPATH . "/php/init.php";

    $type = $_POST['activityType'] ?? null;
    if (empty($type)) {
        die("No activity type given.");
    }
    $schema = $_POST['schema'] ?? null;
    if (empty($schema)) {
        die("No schema given.");
    }

    $schema = json_decode($schema, true);
    $fields = $schema['items'];
    if (empty($fields)) {
        die("No fields given.");
    }
    $modules = [];
    foreach ($fields as $field) {
        if ($field['type'] != 'field' && $field['type'] != 'custom') {
            // skip non-field types
            continue;
        }
        $f = $field['id'];
        if (isset($field['overrides']) && isset($field['overrides']['required']) && $field['overrides']['required']) {
            $f .= '*';
        }
        $modules[] = $f;
    }

    $osiris->adminTypes->updateOne(
        ['id' => $type],
        ['$set' => [
            'modules' => $modules,
            'fields' => $fields
        ]]
    );
    // redirect back
    $_SESSION['msg'] = lang('admin.activity_form_has_been_updated');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/types/$type/fields");
    die();
});

// <!-- Test Email Settings by sending a test mail -->
// // /crud/admin/mail-test

Route::post('/crud/admin/mail-test', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/MailSender.php";

    // include_once BASEPATH . "/php/mail.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $to = $_POST['email'];

    $msg = sendMail($to, 'OSIRIS Test Mail', 'This is a test mail from the OSIRIS system. If you received this mail, everything is set up correctly.');
    if ($msg === null) {
        $msg = lang('admin.test_mail_sent_successfully');
    }
    $_SESSION['msg'] = $msg;
    header("Location: " . ROOTPATH . "/admin/mail");
}, 'login');


// crud/admin/add-user

Route::post('/crud/admin/add-user', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if ($osiris->persons->count(['username' => $_POST['username']]) > 0) {
        $_SESSION['msg'] = lang('error.username_already_taken');
        $_SESSION['msg_type'] = 'error';
        include BASEPATH . "/header.php";
        $form = $_POST;
        include BASEPATH . "/pages/admin/users.php";
        include BASEPATH . "/footer.php";
        die;
    }

    $person = $_POST['values'];
    $person['username'] = $_POST['username'];

    $username = $person['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $account = [
        'username' => $username,
        'password' => $password
    ];
    $collection = $osiris->accounts;
    if (isset($_POST['guestaccount'])) {
        $collection = $osiris->guestAccounts;
        $account['valid_until'] = $_POST['valid_until'] ?? null;
        $person['is_guest'] = true;
    }
    // remove existing accounts with same username
    $collection->deleteMany(['username' => $username]);
    $collection->insertOne($account);

    $depts = [];
    if (isset($person['depts']) && is_array($person['depts'])) {
        foreach ($person['depts'] as $d) {
            $depts[] = [
                'unit' => $d,
                'start' => null,
                'end' => null,
                'scientific' => true,
                'id' => uniqid()
            ];
        }
    }
    $person['units'] = $depts;
    unset($person['depts']);

    $person['displayname'] = "$person[first] $person[last]";
    $person['formalname'] = "$person[last], $person[first]";
    $person['first_abbr'] = "";
    foreach (explode(" ", $person['first']) as $name) {
        $person['first_abbr'] .= " " . $name[0] . ".";
    }
    $person['created'] = date('Y-m-d');
    $person['roles'] = array_keys($person['roles'] ?? []);
    if (isset($_POST['guestaccount'])) {
        if (!in_array('guest', $person['roles'])) {
            $person['roles'][] = 'guest';
        }
    }

    $person['new'] = true;
    $person['is_active'] = true;

    $osiris->persons->insertOne($person);
    include_once BASEPATH . "/php/Render.php";
    renderCurrentUnits(['username' => $username]);

    if (isset($_POST['guestaccount'])) {
        $_SESSION['msg'] = lang('admin.guest_account_created_successfully', replace: [
            'rootpath' => ROOTPATH,
            'username' => rawurlencode($username),
            'displayname' => e($person['displayname']),
        ]);
        $_SESSION['msg_type'] = 'success';
        header("Location: " . ROOTPATH . "/admin/guest-account");
    } else {
        $_SESSION['msg'] = lang('admin.user_created_successfully', replace: [
            'rootpath' => ROOTPATH,
            'username' => rawurlencode($username),
            'displayname' => e($person['displayname']),
        ]);
        $_SESSION['msg_type'] = 'success';
        header("Location: " . ROOTPATH . "/admin/users");
    }
}, 'login');


Route::post('/crud/admin/projects/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));

    $values = validateValues($_POST['values'], $DB);

    $collection = $osiris->adminProjects;

    // check if category ID already exists:
    $category_exist = $collection->findOne(['id' => $values['id']]);
    if (!empty($category_exist)) {
        $_SESSION['msg'] = lang('admin.project_id_does_already_exist');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/admin/projects");
        die();
    }

    $insertOneResult  = $collection->insertOne($values);
    // $id = $insertOneResult->getInsertedId();
    $id = $values['id'];

    $_SESSION['msg'] = lang('admin.project_id_successfully_created', replace: ['id' => $id]);
    header("Location: " . ROOTPATH . "/admin/projects/2/$id");
    die();
});

// /crud/admin/guest-account/update
Route::post('/crud/admin/guest-account/update', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    if (!isset($_POST['username'])) die("no username given");
    $valid_until = $_POST['valid_until'] ?? null;
    $osiris->guestAccounts->updateOne(
        ['username' => $_POST['username']],
        ['$set' => [
            'valid_until' => $valid_until
        ]]
    );

    $_SESSION['msg'] = lang('admin.guest_account_updated_successfully', replace: [
        'rootpath' => ROOTPATH,
        'username' => rawurlencode($_POST['username']),
        'displayname' => e($_POST['username']),
    ]);
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/guest-account");
    die();
});
// /crud/admin/guest-account/delete
Route::post('/crud/admin/guest-account/delete', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    if (!isset($_POST['username'])) die("no username given");
    $osiris->guestAccounts->deleteOne(
        ['username' => $_POST['username']]
    );
    // end valid and remove is_guest flag from person and remove guest role
    $osiris->persons->updateOne(
        ['username' => $_POST['username']],
        ['$unset' => ['is_guest' => "", 'valid_until' => ""]],
        ['$pull' => ['roles' => 'guest']]
    );
    $_SESSION['msg'] = lang('admin.guest_account_deleted_successfully', replace: [
        'rootpath' => ROOTPATH,
        'username' => rawurlencode($_POST['username']),
        'displayname' => e($_POST['username']),
    ]);
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/guest-account");
    die();
});

/**
 * crud/admin/guest-account/generate-link
 * Generate a link for changing the passwort 
 * of the guest account that is valid for 24h
 */
Route::post('/crud/admin/guest-account/generate-link', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.synchronize')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }
    if (!isset($_POST['username'])) die("no username given");
    $token = bin2hex(random_bytes(16));
    $osiris->guestAccounts->updateOne(
        ['username' => $_POST['username']],
        ['$set' => ['reset_token' => $token, 'reset_token_valid_until' => date('Y-m-d H:i:s', time() + 24 * 60 * 60)]]
    );
    $link = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . "://" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ROOTPATH . "/reset-guest-password?token=$token";
    $_SESSION['msg'] = lang('admin.password_reset_link_for_user', replace: [
        'rootpath' => ROOTPATH,
        'username' => rawurlencode($_POST['username']),
        'displayname' => e($_POST['username']),
        'link' => e($link),
    ]);
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/admin/guest-account");
    die();
});

Route::post('/crud/admin/projects/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    include_once BASEPATH . "/php/Project.php";
    $Project = new Project();

    $collection = $osiris->adminProjects;
    $mongo_id = $DB->to_ObjectID($id);

    $original = $collection->findOne(['_id' => $mongo_id]);
    if (empty($original)) {
        abortwith(404, lang('admin.project_type'), "/admin/projects");
    }
    $name = lang($original['name'] ?? $original['id'], $original['name_de'] ?? null);

    $stage = $_POST['stage'] ?? 1;

    $values = validateValues($_POST['values'] ?? [], $DB);
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];
    $values['stage'] = $stage;

    if ($stage == 1) {
        // first stage: update basic information
        if ($original['id'] != $values['id']) {
            // update all connected projects with this type 
            $osiris->projects->updateMany(
                ['type' => $original['id']],
                ['$set' => ['type' => $values['id']]]
            );
        }

        $values['disabled'] = boolval($values['disabled'] ?? false);
        $values['notification_changed_email'] = boolval($values['notification_changed_email'] ?? false);
        $values['notification_created_email'] = boolval($values['notification_created_email'] ?? false);

        $updateResult = $collection->updateOne(
            ['_id' => $mongo_id],
            ['$set' => $values]
        );

        if (isset($values['disabled']) && $values['disabled']) {
            $_SESSION['msg'] = lang('admin.deactivated_project_name_successfully_saved', replace: ['name' => $name]);
            header("Location: " . ROOTPATH . "/admin/projects");
            die;
        }

        header("Location: " . ROOTPATH . "/admin/projects/2/$id");
        die;
    } elseif ($stage == 2) {

        if (!isset($_POST['phase'])) {
            // save empty phases
            $values['phases'] = [];
            $updateResult = $collection->updateOne(
                ['_id' => $mongo_id],
                ['$set' => $values]
            );
            $_SESSION['msg'] = lang('admin.project_name_successfully_saved', replace: ['name' => $name]);
            header("Location: " . ROOTPATH . "/admin/projects");
            die;
        }

        $phases = $_POST['phase'];

        $values['phases'] = [];
        foreach ($Project::PHASES as $phase) {
            $phase_id = $phase['id'];
            // if projects are created directly, skip proposal phase
            if ($original['process'] == 'project' && $phase['type'] == 'proposal') {
                continue;
            }
            // check if pahse was not selected, if so create it with empty modules
            if (!isset($phases[$phase_id])) {
                $phases[$phase_id] = [
                    'modules' => [],
                ];
            }
            // add modules to phase
            $modules = [];
            foreach ($phases[$phase_id]['modules'] ?? [] as $m) {
                if (str_ends_with($m, '*')) {
                    $m = substr($m, 0, -1);
                    $modules[] = [
                        'module' => $m,
                        'required' => true
                    ];
                } else {
                    $modules[] = [
                        'module' => $m,
                        'required' => false
                    ];
                }
            }
            // add phase to values
            $values['phases'][] = [
                'id' => $phase_id,
                'name' => $phase['name'],
                'name_de' => $phase['name_de'],
                'color' => $phase['color'] ?? 'muted',
                'modules' => $modules
            ];
        }

        $updateResult = $collection->updateOne(
            ['_id' => $mongo_id],
            ['$set' => $values]
        );


        $_SESSION['msg'] = lang('admin.project_name_successfully_saved', replace: ['name' => $name]);
        header("Location: " . ROOTPATH . "/admin/projects");
        // header("Location: " . ROOTPATH . "/admin/projects/3/$id");
        die;
    }
    dump($values, true);
    die;
});



Route::post('/crud/admin/projects/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    $collection = $osiris->adminProjects;
    $mongo_id = $DB->to_ObjectID($id);

    // check if ID is in use
    $project = $collection->findOne(['_id' => $mongo_id]);
    if (empty($project)) {
        $_SESSION['msg'] = lang('admin.project_id_could_not_be_deleted_as_it_does_not_exist', replace: ['id' => $id]);
        header("Location: " . ROOTPATH . "/admin/projects");
        die();
    }
    $project_id = $project['id'];

    if ($osiris->projects->count(['type' => $project_id]) > 0) {
        $_SESSION['msg'] = lang('admin.project_project_id_could_not_be_deleted_projects_are_still_associated_to_th', replace: ['project_id' => $project_id]);
        header("Location: " . ROOTPATH . "/admin/projects");
        die();
    }

    $deleted = $collection->deleteOne(['_id' => $mongo_id]);
    if ($deleted->getDeletedCount() == 0) {
        $_SESSION['msg'] = lang('admin.project_project_id_could_not_be_deleted', replace: ['project_id' => $project_id]);
        header("Location: " . ROOTPATH . "/admin/projects");
        die();
    }

    $_SESSION['msg'] = lang('admin.project_project_id_successfully_deleted', replace: ['project_id' => $project_id]);
    header("Location: " . ROOTPATH . "/admin/projects");
    die();
});

Route::post('/crud/admin/vocabularies/([a-z\-_]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) {
        abortwith(403, lang('error.admin_no_permission'), "/", lang('navigation.go_back_home'));
    }

    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
    $doc = [
        'id' => $id,
        'values' => $_POST['values']
    ];

    // delete old vocabulary
    $osiris->adminVocabularies->deleteOne(['id' => $id]);
    // insert new vocabulary
    $osiris->adminVocabularies->insertOne($doc);

    $_SESSION['msg'] = lang('admin.vocabulary_id_successfully_saved', replace: ['id' => $id]);

    $red = ROOTPATH . "/admin/vocabulary/$id";
    header("Location: " . $red);
});
