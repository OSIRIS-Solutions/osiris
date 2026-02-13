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

        <!-- search bar -->
        <!-- <div class="content">
            <input type="text" class="form-control small border-primary" autocomplete="off" placeholder="<?= lang('Search', 'Suche') ?>" oninput="searchNavBar(this.value)">
        </div> -->

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



        <script>
            function searchNavBar(value) {
                var links = $('.sidebar a');
                links.each(function() {
                    if ($(this).text().toLowerCase().includes(value.toLowerCase())) {
                        $(this).css('display', 'flex');
                    } else {
                        $(this).css('display', 'none');
                    }
                });

                // hide empty header
                var titles = $('.sidebar .title');
                titles.each(function() {
                    var nav = $(this).next();
                    var visible = false;
                    nav.children().each(function() {
                        if ($(this).css('display') == 'flex') {
                            visible = true;
                        }
                    });
                    if (visible) {
                        $(this).css('display', 'block');
                    } else {
                        $(this).css('display', 'none');
                    }
                });
            }
        </script>

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
                            <span class="index danger" id="issue-counter"><?= $n_issues ?></span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['approval'])) {
                        $quarter = $notifications['approval']['key'];
                    ?>
                        <a href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>" class="with-icon <?= $pageactive('my-year') ?>">
                            <i class="ph ph-calendar-check" aria-hidden="true"></i>
                            <?= lang('Quarterly approval', 'Quartalsfreigabe') ?>
                            <span class="index danger" id="approval-counter">!</span>
                        </a>
                    <?php } ?>

                    <?php if (isset($notifications['queue'])) {
                        $queue = $notifications['queue']['count'];
                    ?>
                        <a href="<?= ROOTPATH ?>/queue/user" class="with-icon <?= $pageactive('queue/user') ?>">
                            <i class="ph ph-queue" aria-hidden="true"></i>
                            <?= lang('To review', 'Zu überprüfen') ?>
                            <span class="index" id="queue-counter"><?= $queue ?></span>
                        </a>
                    <?php } ?>


                    <?php if ($notifications['reviews'] > 0) { ?>
                        <a href="<?= ROOTPATH ?>/workflow-reviews" class="with-icon <?= $pageactive('workflow-reviews') ?>" id="workflow-reviews-link">
                            <i class="ph ph-highlighter" aria-hidden="true"></i>
                            <?= lang('Reviews', 'Überprüfungen') ?>
                            <span class="index" id="review-counter">0</span>
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
                            <span class="index info" id="message-counter"><?= $n_messages ?></span>
                        </a>
                    <?php } ?>



                    <?php if (isset($notifications['version'])) {
                    ?>
                        <a href="<?= ROOTPATH ?>/new-stuff#version-<?= OSIRIS_VERSION ?>" class="with-icon <?= $pageactive('new-stuff') ?>">
                            <i class="ph ph-bell-ringing" aria-hidden="true"></i>
                            <?= lang('News', 'Neuigkeiten') ?>
                            <span class="index info" id="version-counter">!</span>
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



        <!-- <nav>

            <a href="<?= ROOTPATH ?>/profile/<?= $_SESSION['username'] ?>" class="with-icon <?= $pageactive('profile/' . $_SESSION['username']) ?>">
                <i class="ph ph-user" aria-hidden="true"></i>
                <?= $USER["displayname"] ?? 'User' ?>
            </a>

            <?php if ($Settings->hasPermission('scientist')) { ?>
                <a href="<?= ROOTPATH ?>/my-year" class="with-icon <?= $pageactive('my-year') ?>">
                    <i class="ph ph-calendar" aria-hidden="true"></i>
                    <?= lang('My year', 'Mein Jahr') ?>
                </a>

                <a href="<?= ROOTPATH ?>/my-activities" class="with-icon <?= $pageactive('my-activities') ?>">
                    <i class="ph ph-folder-user" aria-hidden="true"></i>
                    <?= lang('My activities', 'Meine Aktivitäten') ?>
                </a>
            <?php } ?>

                <?php if ($Settings->featureEnabled('calendar', false)) { ?>
                    <a href="<?= ROOTPATH ?>/calendar" class="with-icon <?= $pageactive('calendar') ?>">
                        <i class="ph ph-calendar-dots" aria-hidden="true"></i>
                        <?= lang('Calendar', 'Kalender') ?>
                    </a>
                <?php } ?>

           



            <a href="<?= ROOTPATH ?>/user/logout" class=" with-icon" style="--primary-color:var(--danger-color);--primary-color-20:var(--danger-color-20);">
                <i class="ph ph-sign-out" aria-hidden="true"></i>
                Logout
            </a>


            <?php if ($Settings->featureEnabled('portal-public')) { ?>
                <a href="<?= ROOTPATH ?>/portal/info" class="with-icon <?= $pageactive('portal') ?>" style="--primary-color:var(--muted-color);--primary-color-20:var(--muted-color-20);">
                    <i class="ph ph-globe-hemisphere-west" aria-hidden="true"></i>
                    <?= lang('Go to portal', 'Zum Portal') ?>
                </a>
            <?php } ?>

        </nav> -->

        <div class="title collapse open" onclick="toggleSidebar(this);" id="sidebar-activities">
            <?= lang('Content', 'Inhalte') ?>
        </div>
        <nav>

            <a href="<?= ROOTPATH ?>/activities/search" class="inline-btn  <?= $pageactive('activities') ?>" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                <i class="ph-duotone ph-magnifying-glass-plus"></i>
            </a>
            <a href="<?= ROOTPATH ?>/activities" class="with-icon <?= $pageactive('activities') ?>">
                <i class="ph ph-folders" aria-hidden="true"></i>
                <?= lang('All activities', 'Alle Aktivitäten') ?>
            </a>

            <?php if ($Settings->featureEnabled('projects')) { ?>
                <?php if ($Settings->canProposalsBeCreated()) { ?>
                    <a href="<?= ROOTPATH ?>/proposals/search" class="inline-btn  <?= $pageactive('proposals') ?> mt-10" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                        <i class="ph-duotone ph-magnifying-glass-plus"></i>
                    </a>
                    <a href="<?= ROOTPATH ?>/proposals" class="with-icon <?= $pageactive('proposals') ?>">
                        <i class="ph ph-tree-structure" aria-hidden="true"></i>
                        <?= lang('Proposals', 'Anträge') ?>
                    </a>
                <?php } ?>


                <a href="<?= ROOTPATH ?>/projects/search" class="inline-btn  <?= $pageactive('projects') ?> mt-10" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                    <i class="ph-duotone ph-magnifying-glass-plus"></i>
                </a>
                <a href="<?= ROOTPATH ?>/projects" class="with-icon <?= $pageactive('projects') ?>">
                    <i class="ph ph-tree-structure" aria-hidden="true"></i>
                    <?= lang('Projects', 'Projekte') ?>
                </a>

                <?php if ($Settings->featureEnabled('nagoya') && $Settings->hasPermission('nagoya.view')) { ?>
                    <a href="<?= ROOTPATH ?>/nagoya" class="with-icon <?= $pageactive('nagoya') ?>">
                        <i class="ph ph-scales" aria-hidden="true"></i>
                        <?= lang('Nagoya Dashboard', 'Nagoya-Dashboard') ?>
                    </a>
                <?php } ?>

            <?php } ?>


            <a href="<?= ROOTPATH ?>/journals/search" class="inline-btn  <?= $pageactive('journals') ?> mt-10" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                <i class="ph-duotone ph-magnifying-glass-plus"></i>
            </a>
            <a href="<?= ROOTPATH ?>/journals" class="with-icon <?= $pageactive('journals') ?>">
                <i class="ph ph-stack" aria-hidden="true"></i>
                <?= $Settings->journalLabel() ?>
            </a>

            <?php if ($Settings->featureEnabled('events', true)) { ?>
                <a href="<?= ROOTPATH ?>/conferences/search" class="inline-btn  <?= $pageactive('conferences') ?> mt-10" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                    <i class="ph-duotone ph-magnifying-glass-plus"></i>
                </a>
                <a href="<?= ROOTPATH ?>/conferences" class="with-icon <?= $pageactive('conferences') ?>">
                    <i class="ph ph-calendar-dots" aria-hidden="true"></i>
                    <?= lang('Events') ?>
                </a>
            <?php } ?>

            <?php if ($Settings->featureEnabled('teaching-modules', true)) { ?>
                <a href="<?= ROOTPATH ?>/teaching" class="with-icon <?= $pageactive('teaching') ?>">
                    <i class="ph ph-chalkboard-simple" aria-hidden="true"></i>
                    <?= lang('Teaching modules', 'Lehrmodule') ?>
                </a>
            <?php } ?>

            <?php if ($Settings->featureEnabled('topics')) { ?>
                <a href="<?= ROOTPATH ?>/topics" class="with-icon <?= $pageactive('topics') ?>">
                    <i class="ph ph-puzzle-piece" aria-hidden="true"></i>
                    <?= $Settings->topicLabel() ?>
                </a>
            <?php } ?>

            <?php if ($Settings->featureEnabled('infrastructures')) { ?>
                <a href="<?= ROOTPATH ?>/infrastructures" class="with-icon <?= $pageactive('infrastructures') ?>">
                    <i class="ph ph-cube-transparent" aria-hidden="true"></i>
                    <?= $Settings->infrastructureLabel() ?>
                </a>
            <?php } ?>

            <?php if ($Settings->hasPermission('documents')) { ?>
                <a href="<?= ROOTPATH ?>/documents" class="with-icon <?= $pageactive('documents') ?>">
                    <i class="ph ph-files" aria-hidden="true"></i>
                    <?= lang('Documents', 'Dokumente') ?>
                </a>
            <?php } ?>



            <?php if ($Settings->featureEnabled('concepts')) { ?>
                <a href="<?= ROOTPATH ?>/concepts" class="with-icon <?= $pageactive('concepts') ?>">
                    <i class="ph ph-lightbulb" aria-hidden="true"></i>
                    <?= lang('Concepts', 'Konzepte') ?>
                </a>
            <?php } ?>

        </nav>


        <div class="title collapse open" onclick="toggleSidebar(this);" id="sidebar-users">
            <?= lang('Users', 'Personen') ?>
        </div>

        <nav>
            <?php
            $active =  $pageactive('user/browse');
            if (empty($active) && !str_contains($uri, "profile/" . $_SESSION['username'])) {
                $active = $pageactive('profile');
            }
            ?>

            <a href="<?= ROOTPATH ?>/persons/search" class="inline-btn  <?= $active ?> mt-10" title="<?= lang('Advanced Search', 'Erweiterte Suche') ?>">
                <i class="ph-duotone ph-magnifying-glass-plus"></i>
            </a>
            <a href="<?= ROOTPATH ?>/user/browse" class="with-icon <?= $active ?>">
                <i class="ph ph-users" aria-hidden="true"></i>
                <?= lang('Users', 'Personen') ?>
            </a>
            <a href="<?= ROOTPATH ?>/groups" class="with-icon <?= $pageactive('groups') ?>">
                <i class="ph ph-users-three" aria-hidden="true"></i>
                <?= lang('Organisational Units', 'Einheiten') ?>
            </a>

            <a href="<?= ROOTPATH ?>/organizations" class="with-icon <?= $pageactive('organizations') ?>">
                <i class="ph ph-building-office" aria-hidden="true"></i>
                <?= lang('Organisations', 'Organisationen') ?>
            </a>

            <?php if ($Settings->featureEnabled('guests')) { ?>
                <a href="<?= ROOTPATH ?>/guests" class="with-icon <?= $pageactive('guests') ?>">
                    <i class="ph ph-user-switch" aria-hidden="true"></i>
                    <?= lang('Guests', 'Gäste') ?>
                </a>
            <?php } ?>

        </nav>

        <div class="title collapse open" onclick="toggleSidebar(this);" id="sidebar-tools">
            <?= lang('Analysis', 'Analyse') ?>
        </div>
        <nav>

            <a href="<?= ROOTPATH ?>/dashboard" class="with-icon <?= $pageactive('dashboard') ?>">
                <i class="ph ph-chart-line" aria-hidden="true"></i>
                <?= lang('Dashboard') ?>
            </a>

            <a href="<?= ROOTPATH ?>/visualize" class="with-icon <?= $pageactive('visualize') ?>">
                <i class="ph ph-graph" aria-hidden="true"></i>
                <?= lang('Visualisations', 'Visualisierung') ?>
            </a>

            <a href="<?= ROOTPATH ?>/pivot" class="with-icon <?= $pageactive('pivot') ?>">
                <i class="ph ph-table" aria-hidden="true"></i>
                <?= lang('Pivot table', 'Pivot-Tabelle') ?>
            </a>

            <?php if ($Settings->featureEnabled('trips')) { ?>
                <a href="<?= ROOTPATH ?>/trips" class="with-icon <?= $pageactive('trips') ?>">
                    <i class="ph ph-map-trifold" aria-hidden="true"></i>
                    <?= $Settings->tripLabel() ?>
                </a>
            <?php } ?>

        </nav>


        <div class="title collapse open" onclick="toggleSidebar(this);" id="sidebar-export">
            <?= lang('Export &amp; Import') ?>
        </div>
        <nav>

            <a href="<?= ROOTPATH ?>/download" class="with-icon <?= $pageactive('download') ?>">
                <i class="ph ph-download" aria-hidden="true"></i>
                Export <?= lang('Activities', 'Aktivitäten') ?>
            </a>

            <a href="<?= ROOTPATH ?>/cart" class="with-icon <?= $pageactive('cart') ?>">
                <i class="ph ph-basket" aria-hidden="true"></i>
                <?= lang('Collection', 'Sammlung') ?>
                <?php
                $cart = readCart();
                if (!empty($cart)) { ?>
                    <small class="index" id="cart-counter">
                        <?= count($cart) ?>
                    </small>
                <?php } else { ?>
                    <small class="index hidden" id="cart-counter">
                        0
                    </small>
                <?php } ?>
            </a>
            <a href="<?= ROOTPATH ?>/import" class="with-icon <?= $pageactive('import') ?>">
                <i class="ph ph-upload" aria-hidden="true"></i>
                <?= lang('Import') ?>
            </a>


            <?php if ($Settings->hasPermission('report.queue')) { ?>
                <?php
                $n_queue = $osiris->queue->count(['declined' => ['$ne' => true]]);
                ?>

                <a href="<?= ROOTPATH ?>/queue/editor" class="sidebar-link with-icon sidebar-link-osiris <?= $pageactive('queue/editor') ?>">
                    <i class="ph ph-queue" aria-hidden="true"></i>
                    <?= lang('Queue', 'Warteschlange') ?>
                    <span class="index" id="cart-counter">
                        <?= $n_queue ?>
                    </span>
                </a>
            <?php } ?>


            <?php if ($Settings->hasPermission('report.generate')) { ?>

                <a href="<?= ROOTPATH ?>/reports" class="with-icon <?= $pageactive('reports') ?>">
                    <i class="ph ph-printer" aria-hidden="true"></i>

                    <?= lang('Reports', 'Berichte') ?>
                </a>

                <?php if ($Settings->featureEnabled('ida')) { ?>
                    <a href="<?= ROOTPATH ?>/ida/dashboard" class="with-icon <?= $pageactive('ida') ?>">
                        <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                        <?= lang('IDA-Integration') ?>
                    </a>
                <?php } ?>

            <?php } ?>

        </nav>



        <?php if ($Settings->hasPermission('admin.see') || $Settings->hasPermission('report.templates') || $Settings->hasPermission('user.synchronize')) { ?>
            <div class="title collapse" onclick="toggleSidebar(this);" id="sidebar-admin">
                ADMIN
            </div>
            <nav style="display: none;" id="sidebar-admin-links">
                <?php if ($Settings->hasPermission('admin.see')) { ?>
                    <a href="<?= ROOTPATH ?>/admin/general" class="with-icon <?= $pageactive('admin/general') ?>">
                        <i class="ph ph-gear" aria-hidden="true"></i>
                        <?= lang('Settings', 'Einstellungen') ?>
                    </a>
                    <a href="<?= ROOTPATH ?>/admin" class="with-icon <?= $pageactive('admin') ?>">
                        <i class="ph ph-treasure-chest" aria-hidden="true"></i>
                        <?= lang('Contents', 'Inhalte') ?>
                    </a>
                    <a href="<?= ROOTPATH ?>/admin/roles" class="with-icon <?= $pageactive('admin/roles') ?>">
                        <i class="ph ph-shield-check" aria-hidden="true"></i>
                        <?= lang('Roles &amp; Rights', 'Rollen &amp; Rechte') ?>
                    </a>
                <?php } ?>


                <?php if ($Settings->hasPermission('report.templates')) { ?>
                    <a href="<?= ROOTPATH ?>/admin/reports" class="with-icon <?= $pageactive('admin/reports') ?>">
                        <i class="ph ph-clipboard-text"></i>
                        <?= lang('Report templates', 'Berichtsvorlagen') ?>
                    </a>
                <?php } ?>
                <?php if ($Settings->hasPermission('user.synchronize')) { ?>
                    <a href="<?= ROOTPATH ?>/admin/users" class="with-icon <?= $pageactive('admin/users') ?>">
                        <i class="ph ph-users"></i>
                        <?= lang('User Management', 'Nutzerverwaltung') ?>
                    </a>
                <?php } ?>
            </nav>
        <?php } ?>


    <?php } ?>


</div>