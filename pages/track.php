<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}
$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track a ticket · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="dist/assets/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="dist/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/complaints.css">
</head>
<body class="auth-page">
<div class="auth-layout auth-layout-wide">
    <aside class="auth-aside">
        <a class="auth-brand auth-brand-light" href="index.php"><span class="brand-mark"><i class="ti ti-leaf" aria-hidden="true"></i></span><span><strong>FCWEM</strong><small>Student support portal</small></span></a>
        <div class="auth-aside-copy">
            <span class="auth-pill"><i class="ti ti-route" aria-hidden="true"></i> YOUR SUPPORT JOURNEY</span>
            <h2>Know where things<br>stand, every step.</h2>
            <p>Your reference number connects you to the latest progress on your complaint.</p>
            <div class="auth-benefits">
                <div><span class="benefit-icon"><i class="ti ti-hash" aria-hidden="true"></i></span><span><strong>Find your ticket</strong><small>Enter the reference shared after submission.</small></span></div>
                <div><span class="benefit-icon"><i class="ti ti-history" aria-hidden="true"></i></span><span><strong>See recent updates</strong><small>Follow status changes and responses.</small></span></div>
            </div>
        </div>
        <div class="auth-aside-footer"><span class="status-dot"></span> A supportive college community <span class="aside-footer-divider">·</span> <?= date('Y') ?></div>
        <span class="auth-orbit auth-orbit-one" aria-hidden="true"></span>
        <span class="auth-orbit auth-orbit-two" aria-hidden="true"></span>
    </aside>
    <main class="auth-main">
    <section class="auth-card">
        <div class="auth-mobile-brand"><span class="brand-mark"><i class="ti ti-leaf" aria-hidden="true"></i></span><span><strong>FCWEM</strong><small>Student support portal</small></span></div>
        <div class="eyebrow">TICKET STATUS</div>
        <h1>Track your complaint</h1>
        <p>Enter the reference number you received when submitting your ticket. You can also track a case without signing in.</p>
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
        <form class="track-panel mb-4" method="get">
            <input type="hidden" name="page" value="track">
            <label class="form-label" for="reference">Ticket reference number</label>
            <div class="d-flex flex-wrap gap-2"><input class="form-control flex-grow-1" id="reference" name="reference" value="<?= e($reference) ?>" placeholder="e.g. FCW-2026-A1B2C3D4E5F60718293A4B5C" required><button class="btn btn-primary" type="submit"><i class="ti ti-search me-1"></i>Track ticket</button></div>
        </form>

        <?php if ($reference !== '' && !$ticket): ?>
            <div class="alert alert-warning">We could not find a ticket with reference <strong><?= e($reference) ?></strong>. Check the number and try again.</div>
        <?php elseif ($ticket): ?>
            <section class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><h3><?= e($ticket['subject']) ?></h3><span class="badge-status status-<?= e(strtolower(str_replace(' ', '-', $ticket['status']))) ?>"><?= e($ticket['status']) ?></span></div>
                <div class="ticket-summary">
                    <div class="summary-item"><span>Reference</span><strong class="ticket-reference"><?= e($ticket['reference_number']) ?></strong></div>
                    <div class="summary-item"><span>Topic</span><strong><?= e($ticket['category']) ?></strong></div>
                    <div class="summary-item"><span>Department</span><strong><?= !empty($ticket['department_name']) ? e($ticket['department_name']) : 'Not assigned yet' ?></strong></div>
                </div>
                <div class="card-body border-top"><div class="form-label text-uppercase">Request details</div><p class="small lh-lg mb-0" style="white-space:pre-line"><?= e($ticket['description']) ?></p></div>
            </section>
            <section class="card">
                <div class="card-header"><h3>Latest updates</h3></div>
                <div class="card-body">
                    <?php if ($updates): ?><div class="timeline"><?php foreach ($updates as $update): ?><div class="timeline-item"><span class="timeline-dot"></span><strong><?= e($update['status']) ?> <span class="text-muted fw-normal">· <?= e($update['author_name']) ?></span></strong><p><?= e($update['message']) ?></p><time><?= e(date('M j, Y \a\t g:i a', strtotime($update['created_at']))) ?></time></div><?php endforeach; ?></div><?php else: ?><div class="empty-state py-3"><span>No updates are available yet.</span></div><?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        <div class="border-top mt-4 pt-3 text-center"><a class="small fw-semibold" href="?page=login">Sign in to your account</a></div>
    </section>
    <div class="auth-footer">© <?= date('Y') ?> Federal College of Wildlife Management and Eco-tourism</div>
    </main>
</div>
<script src="dist/assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>
