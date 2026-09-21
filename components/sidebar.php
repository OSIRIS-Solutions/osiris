<?php
$cart = readCart();
?>
<div class="sidebar-menu">

    <!-- Sidebar links and titles -->
    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) { ?>

        <div class="spacer h-50"></div>
        <a href="<?= ROOTPATH ?>/user/login" class="cta with-icon <?= $pageactive('add-activity') ?>">
            <i class="ph ph-sign-in mr-10" aria-hidden="true"></i>
            <?= lang('header.login') ?>
        </a>

        <?php if (strtoupper(USER_MANAGEMENT) === 'AUTH' && $Settings->get('auth-self-registration', true)) { ?>
            <a href="<?= ROOTPATH ?>/auth/new-user" class="with-icon <?= $pageactive('auth/new-user') ?>">
                <i class="ph ph-user-plus" aria-hidden="true"></i>
                <?= lang('common.register') ?>
            </a>
        <?php } ?>

        <?php if ($Settings->featureEnabled('portal-public')) { ?>
            <a href="<?= ROOTPATH ?>/portal/info" class="with-icon <?= $pageactive('portal') ?>">
                <i class="ph ph-globe-hemisphere-west" aria-hidden="true"></i>
                <?= lang('navigation.go_to_portal') ?>
            </a>
        <?php } ?>

    <?php } else { ?>

        <div class="my-profile">
            <div class="my-profile-head">

                <a href="<?= ROOTPATH ?>/profile/<?= $_SESSION['username'] ?>" class="my-profile-avatar">
                    <?= $Settings->printProfilePicture($_SESSION['username'], 'my-profile-picture') ?>
                    <div class="my-profile-name">
                        <strong><?= $USER["displayname"] ?? $_SESSION['username'] ?></strong>
                        <br>
                        <small class="text-muted" style="font-size: 0.8rem; font-weight: normal;">
                            @<?= $_SESSION['username'] ?>
                        </small>
                    </div>
                </a>
                <a href="#" onclick="$('.my-profile-links').slideToggle();" title="<?= lang('navigation.profile_options') ?>">
                    <i class="ph ph-dots-three ph-2x"></i>
                </a>
            </div>
            <div class="my-profile-links" style="display: none;">
                <?php
                $realusername = $_SESSION['realuser'] ?? $_SESSION['username'];
                $maintain = $osiris->persons->find(['maintenance' => $realusername, 'username' => ['$exists' => true]], ['projection' => ['displayname' => 1, 'username' => 1]])->toArray();
                if (!empty($maintain)) { ?>
                    <div class="dropdown modal-sm">
                        <a href="#" class="" data-toggle="dropdown" id="switch-user" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-user-switch"></i>
                            <span><?= lang('navigation.switch_users') ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-center w-250" aria-labelledby="switch-user">
                            <!-- <h6 class="header text-primary"><?= lang('navigation.switch_users') ?></h6> -->

                            <form action="<?= ROOTPATH ?>/switch-user" method="post" class="p-10" id="switch-user-form">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-primary text-primary"><i class="ph ph-user"></i></span>
                                    </div>

                                    <select name="OSIRIS-SELECT-MAINTENANCE-USER" id="osiris-select-maintenance-user" class="form-control border-primary bg-white" onchange="$(this).closest('form').submit()">
                                        <option value="" disabled>
                                            <?= lang('navigation.switch_user') ?>
                                        </option>
                                        <option value="<?= $realusername ?>"><?= $DB->getNameFromId($realusername) ?></option>
                                        <?php
                                        foreach ($maintain as $d) { ?>
                                            <option value="<?= $d['username'] ?>" <?= $d['username'] ==  $_SESSION['username'] ? 'selected' : '' ?>><?= $DB->getNameFromId($d['username']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($Settings->hasPermission('scientist')) { ?>
                    <a href="<?= ROOTPATH ?>/my-year" class="<?= $pageactive('my-year') ?>">
                        <i class="ph ph-calendar" aria-hidden="true"></i>
                        <?= lang('common.my_year_docs') ?>
                    </a>

                    <a href="<?= ROOTPATH ?>/my-activities" class="<?= $pageactive('my-activities') ?>">
                        <i class="ph ph-folder-user" aria-hidden="true"></i>
                        <?= lang('activities.my_activities') ?>
                    </a>
                <?php } ?>

                <a href="<?= ROOTPATH ?>/user/edit/<?= $_SESSION['username'] ?>">
                    <i class="ph ph-gear" aria-hidden="true"></i>
                    <?= lang('navigation.user_settings') ?>
                </a>

                <a href="<?= ROOTPATH ?>/user/logout" class="mt-10" style="--primary-color:var(--danger-color);">
                    <i class="ph ph-sign-out" aria-hidden="true"></i>
                    <?= lang('navigation.logout') ?>
                </a>

            </div>
        </div>

        <div class="my-profile-spacer"></div>

        <nav id="sidebar-add">
            <a href="<?= ROOTPATH ?>/add-activity" class="cta with-icon <?= $pageactive('add-activity') ?>">
                <i class="ph ph-plus-circle mr-10" aria-hidden="true"></i>
                <?= lang('dashboard.add_activity') ?>
            </a>

            <div id="sidebar-add-navigation">

                <?php if ($Settings->featureEnabled('projects') && $Settings->hasPermission('projects.add')) { ?>
                    <?php if ($Settings->canProposalsBeCreated()) { ?>
                        <a href="<?= ROOTPATH ?>/proposals/new" class="">
                            <i class="ph ph-tree-structure"></i>
                            <?= lang('dashboard.add_project_proposal') ?>
                        </a>
                    <?php } else if ($Settings->canProjectsBeCreated()) { ?>
                        <a href="<?= ROOTPATH ?>/projects/new" class="">
                            <i class="ph ph-tree-structure"></i>
                            <?= lang('dashboard.add_project') ?>
                        </a>
                    <?php } ?>
                <?php } ?>
                <?php if ($Settings->hasPermission('conferences.edit') && $Settings->featureEnabled('events', true)) { ?>
                    <a href="<?= ROOTPATH ?>/conferences/new">
                        <i class="ph ph-calendar-plus"></i>
                        <?= lang('action.add_event') ?>
                    </a>
                <?php } ?>
                <?php if ($Settings->featureEnabled('infrastructures') && $Settings->hasPermission('infrastructures.edit')) {
                    $header_infras = $osiris->infrastructures->find([
                        'statistic_frequency' => 'irregularly',
                        'persons' => [
                            '$elemMatch' => [
                                'user' => $_SESSION['username'],
                                'reporter' => true
                            ]
                        ],
                        'start_date' => ['$lte' => CURRENTYEAR . '-12-31'],
                        '$or' => [
                            ['end_date' => null],
                            ['end_date' => ['$gte' => CURRENTYEAR . '-01-01']]
                        ],
                    ]);
                    foreach ($header_infras as $inf) {
                ?>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $inf['_id'] ?>?edit-stats=<?= date('Y-m-d') ?>">
                            <i class="ph ph-cube-transparent"></i>
                            <?= lang('common.statistics_for') . $inf['name'] ?>
                        </a>
                <?php
                    }
                } ?>
            </div>
        </nav>


        <?php
        $notifications = $DB->notifications();
        $n_notifications = $_SESSION['has_notifications'] ?? false;
        $has_notifications = $n_notifications > 0;

        $notifications['reviews'] = 0;
        if ($Settings->featureEnabled('quality-workflow', false)) {
            $notifications['reviews'] = $osiris->adminWorkflows->count(['steps.role' => ['$in' => $Settings->roles]]) > 0;
            if ($notifications['reviews'] > 0) {
                $has_notifications = true;
            }
        }
        ?>
        <div class="my-tasks tasks-<?= $has_notifications ? '1' : '0' ?>">

            <div class="title collapse open" onclick="toggleSidebar(this);" id="sidebar-tasks">
                <?= lang('dashboard.my_tasks') ?>
            </div>

            <nav>
                <?php
                if ($has_notifications) {
                    if (isset($notifications['activity'])) {
                        $n_issues = $notifications['activity']['count'];
                ?>
                        <a href="<?= ROOTPATH ?>/issues" class="with-icon <?= $pageactive('issues') ?>">
                            <i class="ph ph-bell" aria-hidden="true"></i>
                            <?= lang('dashboard.issues') ?>
                            <span class="sidebar-index danger" id="issue-counter"><?= $n_issues ?></span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['approval'])) {
                        $quarter = $notifications['approval']['key'];
                    ?>
                        <a href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>" class="with-icon <?= $pageactive('my-year') ?>">
                            <i class="ph ph-calendar-check" aria-hidden="true"></i>
                            <?= lang('dashboard.quarterly_approval') ?>
                            <span class="sidebar-index danger" id="approval-counter">!</span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['queue'])) {
                        $queue = $notifications['queue']['count'];
                    ?>
                        <a href="<?= ROOTPATH ?>/queue/user" class="with-icon <?= $pageactive('queue/user') ?>">
                            <i class="ph ph-queue" aria-hidden="true"></i>
                            <?= lang('dashboard.to_review') ?>
                            <span class="sidebar-index" id="queue-counter"><?= $queue ?></span>
                        </a>
                    <?php } ?>


                    <?php if ($notifications['reviews'] > 0) { ?>
                        <a href="<?= ROOTPATH ?>/workflow-reviews" class="with-icon <?= $pageactive('workflow-reviews') ?>" id="workflow-reviews-link">
                            <i class="ph ph-highlighter" aria-hidden="true"></i>
                            <?= lang('dashboard.reviews') ?>
                            <span class="sidebar-index review-counter">0</span>
                        </a>

                        <script>
                            // highlight if there are reviews to be done
                            $(document).ready(function() {
                                $.getJSON('<?= ROOTPATH ?>/api/workflow-reviews/count', function(data) {
                                    if (data.count > 0) {
                                        $('.review-counter').text(data.count);
                                    }
                                });
                            });
                        </script>
                    <?php } ?>

                    <?php if (isset($notifications['messages'])) {
                        $n_messages = count($notifications['messages']);
                    ?>
                        <a href="<?= ROOTPATH ?>/messages" class="with-icon <?= $pageactive('messages') ?>">
                            <i class="ph ph-envelope" aria-hidden="true"></i>
                            <?= lang('common.messages') ?>
                            <span class="sidebar-index info" id="message-counter"><?= $n_messages ?></span>
                        </a>
                    <?php } ?>


                <?php } else { ?>
                    <div class="no-tasks">
                        <i class="ph ph-coffee" aria-hidden="true"></i>
                        <span><?= lang('navigation.you_have_no_pending_tasks_great_job') ?></span>
                    </div>
                <?php } ?>

            </nav>
        </div>


        <?php
        include_once BASEPATH . '/php/SidebarNav.php';
        $Sidebar = new SidebarNav($Settings);
        echo $Sidebar->render();
        ?>


    <?php } ?>


</div>