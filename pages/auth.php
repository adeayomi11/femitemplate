<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}

$flash = take_flash();
$isSetup = $page === 'setup';
$isRegister = $page === 'register';
$authTitle = $isSetup ? 'Set up your portal' : ($isRegister ? 'Create your student account' : 'Welcome back');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($authTitle) ?> · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="dist/assets/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="dist/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/complaints.css">
</head>
<body class="auth-page">
<div class="auth-layout">
    <aside class="auth-aside">
        <a class="auth-brand auth-brand-light" href="index.php"><span class="brand-mark"><i class="ti ti-leaf" aria-hidden="true"></i></span><span><strong>FCWEM</strong><small>Student support portal</small></span></a>
        <div class="auth-aside-copy">
            <span class="auth-pill"><i class="ti ti-sparkles" aria-hidden="true"></i> HERE TO HELP</span>
            <h2>Every concern deserves<br>a clear path forward.</h2>
            <p>A welcoming, reliable way to connect students with the people who can help across our college community.</p>
            <div class="auth-benefits">
                <div><span class="benefit-icon"><i class="ti ti-send" aria-hidden="true"></i></span><span><strong>Simple to get started</strong><small>Share your concern in just a few steps.</small></span></div>
                <div><span class="benefit-icon"><i class="ti ti-route" aria-hidden="true"></i></span><span><strong>Sent to the right team</strong><small>Complaints reach the relevant department.</small></span></div>
                <div><span class="benefit-icon"><i class="ti ti-eye" aria-hidden="true"></i></span><span><strong>Always in the loop</strong><small>Keep an eye on progress with your reference.</small></span></div>
            </div>
        </div>
        <div class="auth-aside-footer"><span class="status-dot"></span> A supportive college community <span class="aside-footer-divider">·</span> <?= date('Y') ?></div>
        <span class="auth-orbit auth-orbit-one" aria-hidden="true"></span>
        <span class="auth-orbit auth-orbit-two" aria-hidden="true"></span>
    </aside>
    <main class="auth-main">
    <section class="auth-card">
        <div class="auth-mobile-brand"><span class="brand-mark"><i class="ti ti-leaf" aria-hidden="true"></i></span><span><strong>FCWEM</strong><small>Student support portal</small></span></div>
        <div class="eyebrow"><?= $isSetup ? 'ONE-TIME SETUP' : 'FEDERAL COLLEGE OF WILDLIFE MANAGEMENT' ?></div>
        <h1><?= e($authTitle) ?></h1>
        <p>
            <?php if ($isSetup): ?>
                Create the first portal administrator. The application has automatically configured its MySQL database.
            <?php elseif ($isRegister): ?>
                Register with your student details to submit complaints and follow their progress.
            <?php else: ?>
                Sign in to manage support requests or submit a complaint to the right team.
            <?php endif; ?>
        </p>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($isSetup): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="setup_admin">
                <div class="mb-3"><label class="form-label" for="full_name">Administrator full name</label><div class="input-icon"><i class="ti ti-user" aria-hidden="true"></i><input class="form-control" id="full_name" name="full_name" autocomplete="name" placeholder="Your full name" required maxlength="150"></div></div>
                <div class="mb-3"><label class="form-label" for="email">Email address</label><div class="input-icon"><i class="ti ti-mail" aria-hidden="true"></i><input class="form-control" id="email" name="email" type="email" autocomplete="email" placeholder="name@college.edu" required maxlength="190"></div></div>
                <div class="mb-3"><label class="form-label" for="password">Create password</label><div class="input-icon"><i class="ti ti-lock" aria-hidden="true"></i><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="10" placeholder="At least 10 characters" required></div><div class="form-hint">Use at least 10 characters for the administrator account.</div></div>
                <button class="btn btn-primary w-100 mt-2" type="submit">Create administrator account <i class="ti ti-arrow-right ms-1"></i></button>
            </form>
        <?php elseif ($isRegister): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="register_student">
                <div class="mb-3"><label class="form-label" for="full_name">Full name</label><div class="input-icon"><i class="ti ti-user" aria-hidden="true"></i><input class="form-control" id="full_name" name="full_name" autocomplete="name" placeholder="Your full name" required maxlength="150"></div></div>
                <div class="mb-3"><label class="form-label" for="email">College email address</label><div class="input-icon"><i class="ti ti-mail" aria-hidden="true"></i><input class="form-control" id="email" name="email" type="email" autocomplete="email" placeholder="name@college.edu" required maxlength="190"></div></div>
                <div class="mb-3"><label class="form-label" for="matric_number">Matric number</label><div class="input-icon"><i class="ti ti-id" aria-hidden="true"></i><input class="form-control" id="matric_number" name="matric_number" placeholder="Your matric number" required maxlength="50"></div></div>
                <div class="mb-3"><label class="form-label" for="department_id">Department</label><select class="form-select" id="department_id" name="department_id" required><option value="">Select your department</option><?php foreach ($departments as $department): ?><option value="<?= e($department['id']) ?>"><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label" for="password">Password</label><div class="input-icon"><i class="ti ti-lock" aria-hidden="true"></i><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required></div><div class="form-hint">Use at least 8 characters.</div></div>
                <button class="btn btn-primary w-100 mt-2" type="submit">Create student account <i class="ti ti-arrow-right ms-1"></i></button>
            </form>
        <?php else: ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="login">
                <div class="mb-3"><label class="form-label" for="email">Email address</label><div class="input-icon"><i class="ti ti-mail" aria-hidden="true"></i><input class="form-control" id="email" name="email" type="email" autocomplete="username" placeholder="name@college.edu" required maxlength="190"></div></div>
                <div class="mb-3"><label class="form-label" for="password">Password</label><div class="input-icon"><i class="ti ti-lock" aria-hidden="true"></i><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required></div></div>
                <button class="btn btn-primary w-100 mt-2" type="submit">Sign in <i class="ti ti-arrow-right ms-1"></i></button>
            </form>
        <?php endif; ?>

        <?php if (!$isSetup): ?>
        <div class="border-top mt-4 pt-3 text-center">
            <?php if (!$isSetup && !$isRegister): ?><span class="text-muted small">New student?</span> <a class="small fw-semibold" href="?page=register">Create an account</a><br><?php endif; ?>
            <?php if (!$isSetup && !$isRegister): ?><a class="small d-inline-block mt-2" href="?page=track">Track a ticket without signing in</a><?php endif; ?>
            <?php if ($isRegister): ?><span class="text-muted small">Already registered?</span> <a class="small fw-semibold" href="?page=login">Sign in</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
    <div class="auth-footer">© <?= date('Y') ?> Federal College of Wildlife Management and Eco-tourism · Student Support Services</div>
    </main>
</div>
<script src="dist/assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>
