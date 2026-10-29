<?php

function layout_start($title, $active, $user)
{
    $flash = take_flash();
    $role = $user['role'];
    $nav = array(
        'student' => array(
            array('dashboard', 'Overview', 'ti ti-layout-dashboard'),
            array('new-ticket', 'Create a ticket', 'ti ti-circle-plus'),
            array('tickets', 'My complaints', 'ti ti-inbox'),
            array('track', 'Track a ticket', 'ti ti-search'),
        ),
        'department' => array(
            array('dashboard', 'Overview', 'ti ti-layout-dashboard'),
            array('tickets', 'Department cases', 'ti ti-inbox'),
        ),
        'admin' => array(
            array('dashboard', 'Overview', 'ti ti-layout-dashboard'),
            array('tickets', 'All complaints', 'ti ti-inbox'),
            array('users', 'Department accounts', 'ti ti-users'),
        ),
    );
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
        <link rel="stylesheet" href="dist/assets/fonts/tabler-icons.min.css">
        <link rel="stylesheet" href="dist/assets/css/style.css">
        <link rel="stylesheet" href="assets/css/complaints.css">
    </head>
    <body>
    <div class="app-shell">
        <aside class="app-sidebar">
            <a class="brand" href="?page=dashboard" aria-label="FCWEM Student Support home">
                <span class="brand-mark"><i class="ti ti-leaf" aria-hidden="true"></i></span>
                <span><strong>FCWEM</strong><small>Student support</small></span>
            </a>
            <div class="sidebar-label">WORKSPACE</div>
            <nav class="sidebar-nav" aria-label="Main navigation">
                <?php foreach ($nav[$role] as $item): ?>
                    <a class="<?= $active === $item[0] ? 'active' : '' ?>" href="?page=<?= e($item[0]) ?>" <?= $active === $item[0] ? 'aria-current="page"' : '' ?>>
                        <i class="<?= e($item[2]) ?>" aria-hidden="true"></i><span><?= e($item[1]) ?></span>
                        <?php if ($item[0] === 'new-ticket'): ?><i class="ti ti-arrow-up-right nav-trailing"></i><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-help">
                <span class="help-icon"><i class="ti ti-message-circle-question" aria-hidden="true"></i></span>
                <strong>Need a hand?</strong>
                <p>Submit a support ticket and our team will follow up.</p>
                <?php if ($role === 'student'): ?><a href="?page=new-ticket">Create a ticket <i class="ti ti-arrow-right"></i></a><?php endif; ?>
            </div>
            <div class="sidebar-footer">
                <span class="status-dot"></span>
                <span>Support portal online</span>
            </div>
        </aside>
        <div class="app-main">
            <header class="topbar">
                <div>
                    <span class="topbar-kicker">FEDERAL COLLEGE OF WILDLIFE MANAGEMENT</span>
                    <h1><?= e($title) ?></h1>
                </div>
                <div class="topbar-user">
                    <span class="avatar" aria-hidden="true"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
                    <span class="user-meta"><strong><?= e($user['full_name']) ?></strong><small><?= e(ucfirst($role)) ?><?= !empty($user['department_name']) ? ' · ' . e($user['department_name']) : '' ?></small></span>
                    <form method="post" class="logout-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="logout">
                        <button class="icon-button" type="submit" aria-label="Sign out" title="Sign out"><i class="ti ti-logout"></i></button>
                    </form>
                </div>
            </header>
            <main class="page-content">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
    <?php
}

function layout_end()
{
    ?>
            </main>
            <footer class="app-footer">
                <span>© <?= date('Y') ?> Federal College of Wildlife Management and Eco-tourism</span>
                <span>Student Support Services</span>
            </footer>
        </div>
    </div>
    <script src="dist/assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/complaints.js"></script>
    </body>
    </html>
    <?php
}
