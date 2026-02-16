<div class="sidebar-menu">

    <!-- Sidebar links and titles -->
    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) { ?>

        <a href="<?= ROOTPATH ?>/" class="cta with-icon <?= $pageactive('add-activity') ?>">
            <i class="ph ph-sign-in mr-10" aria-hidden="true"></i>
            <?= lang('Log in', 'Anmelden') ?>
        </a>

        <?php if (strtoupper(USER_MANAGEMENT) === 'AUTH' && $Settings->get('auth-self-registration', true)) { ?>
            <a href="<?= ROOTPATH ?>/auth/new-user" class="with-icon <?= $pageactive('auth/new-user') ?>">
                <i class="ph ph-user-plus" aria-hidden="true"></i>
                <?= lang('Register', 'Registrieren') ?>
            </a>
        <?php } ?>

        <?php if ($Settings->featureEnabled('portal-public')) { ?>
            <a href="<?= ROOTPATH ?>/portal/info" class="with-icon <?= $pageactive('portal') ?>">
                <i class="ph ph-globe-hemisphere-west" aria-hidden="true"></i>
                <?= lang('Go to portal', 'Zum Portal') ?>
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
                <a href="#" onclick="$('.my-profile-links').slideToggle();" title="<?= lang('Profile options', 'Profiloptionen') ?>">
                    <i class="ph ph-dots-three ph-2x"></i>
                </a>
            </div>
            <div class="my-profile-links" style="display: none;">

                <?php if ($Settings->hasPermission('scientist')) { ?>
                    <a href="<?= ROOTPATH ?>/my-year" class="<?= $pageactive('my-year') ?>" data-toggle="tooltip" data-title="<?= lang('My year', 'Mein Jahr') ?>">
                        <i class="ph ph-calendar" aria-hidden="true"></i>
                    </a>

                    <a href="<?= ROOTPATH ?>/my-activities" class="<?= $pageactive('my-activities') ?>" data-toggle="tooltip" data-title="<?= lang('My activities', 'Meine Aktivitäten') ?>">
                        <i class="ph ph-folder-user" aria-hidden="true"></i>
                    </a>
                <?php } ?>

                <a href="<?= ROOTPATH ?>/user/edit/<?= $_SESSION['username'] ?>" data-toggle="tooltip" data-title="<?= lang('Settings', 'Einstellungen') ?>">
                    <i class="ph ph-gear" aria-hidden="true"></i>
                </a>

                <a href="<?= ROOTPATH ?>/user/logout" class="" style="--primary-color:var(--danger-color);" data-toggle="tooltip" data-title="<?= lang('Logout', 'Abmelden') ?>">
                    <i class="ph ph-sign-out" aria-hidden="true"></i>
                </a>

            </div>
        </div>

        <div class="my-profile-spacer"></div>

        <nav id="sidebar-add">
            <a href="<?= ROOTPATH ?>/add-activity" class="cta with-icon <?= $pageactive('add-activity') ?>">
                <i class="ph ph-plus-circle mr-10" aria-hidden="true"></i>
                <?= lang('Add activity', 'Aktivität hinzuf.') ?>
            </a>

            <div id="sidebar-add-navigation">

                <?php if ($Settings->featureEnabled('projects') && $Settings->hasPermission('projects.add')) { ?>
                    <?php if ($Settings->canProposalsBeCreated()) { ?>
                        <a href="<?= ROOTPATH ?>/proposals/new" class="">
                            <i class="ph ph-tree-structure"></i>
                            <?= lang('Add project proposal', 'Projektantrag hinzuf.') ?>
                        </a>
                    <?php } else if ($Settings->canProjectsBeCreated()) { ?>
                        <a href="<?= ROOTPATH ?>/projects/new" class="">
                            <i class="ph ph-tree-structure"></i>
                            <?= lang('Add project', 'Projekt hinzufügen') ?>
                        </a>
                    <?php } ?>
                <?php } ?>
                <?php if ($Settings->hasPermission('conferences.edit') && $Settings->featureEnabled('events', true)) { ?>
                    <a href="<?= ROOTPATH ?>/conferences/new">
                        <i class="ph ph-calendar-plus"></i>
                        <?= lang('Add event', 'Event hinzufügen') ?>
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
                            <?= lang('Statistics for ', 'Statistik für ') . $inf['name'] ?>
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
                <?= lang('My tasks', 'Meine Aufgaben') ?>
            </div>

            <nav>
                <?php
                if ($has_notifications) {
                    if (isset($notifications['activity'])) {
                        $n_issues = $notifications['activity']['count'];
                ?>
                        <a href="<?= ROOTPATH ?>/issues" class="with-icon <?= $pageactive('issues') ?>">
                            <i class="ph ph-bell" aria-hidden="true"></i>
                            <?= lang('Issues', 'Hinweise') ?>
                            <span class="sidebar-index danger" id="issue-counter"><?= $n_issues ?></span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['approval'])) {
                        $quarter = $notifications['approval']['key'];
                    ?>
                        <a href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>" class="with-icon <?= $pageactive('my-year') ?>">
                            <i class="ph ph-calendar-check" aria-hidden="true"></i>
                            <?= lang('Quarterly approval', 'Quartalsfreigabe') ?>
                            <span class="sidebar-index danger" id="approval-counter">!</span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['queue'])) {
                        $queue = $notifications['queue']['count'];
                    ?>
                        <a href="<?= ROOTPATH ?>/queue/user" class="with-icon <?= $pageactive('queue/user') ?>">
                            <i class="ph ph-queue" aria-hidden="true"></i>
                            <?= lang('To review', 'Zu überprüfen') ?>
                            <span class="sidebar-index" id="queue-counter"><?= $queue ?></span>
                        </a>
                    <?php } ?>


                    <?php if ($notifications['reviews'] > 0) { ?>
                        <a href="<?= ROOTPATH ?>/workflow-reviews" class="with-icon <?= $pageactive('workflow-reviews') ?>" id="workflow-reviews-link">
                            <i class="ph ph-highlighter" aria-hidden="true"></i>
                            <?= lang('Reviews', 'Überprüfungen') ?>
                            <span class="sidebar-index" id="review-counter">0</span>
                        </a>

                        <script>
                            // highlight if there are reviews to be done
                            $(document).ready(function() {
                                $.getJSON('<?= ROOTPATH ?>/api/workflow-reviews/count', function(data) {
                                    if (data.count > 0) {
                                        $('#review-counter').text(data.count);
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
                            <?= lang('Messages', 'Nachrichten') ?>
                            <span class="sidebar-index info" id="message-counter"><?= $n_messages ?></span>
                        </a>
                    <?php } ?>



                    <?php if (isset($notifications['version'])) {
                    ?>
                        <a href="<?= ROOTPATH ?>/new-stuff#version-<?= OSIRIS_VERSION ?>" class="with-icon <?= $pageactive('new-stuff') ?>">
                            <i class="ph ph-bell-ringing" aria-hidden="true"></i>
                            <?= lang('News', 'Neuigkeiten') ?>
                            <span class="sidebar-index info" id="version-counter">!</span>
                        </a>
                    <?php } ?>


                <?php } else { ?>
                    <div class="no-tasks">
                        <i class="ph ph-coffee" aria-hidden="true"></i>
                        <span><?= lang('You have no pending tasks. Great job!', 'Du hast keine offenen Aufgaben. Großartig!') ?></span>
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