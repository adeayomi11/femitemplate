<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}

$isStudent = $user['role'] === 'student';
$isAdmin = $user['role'] === 'admin';
?>
<section class="card welcome-card mb-4">
    <div class="card-body">
        <div class="eyebrow text-white-50"><?= $isStudent ? 'STUDENT SUPPORT' : ($isAdmin ? 'PORTAL ADMINISTRATION' : 'DEPARTMENT WORKSPACE') ?></div>
        <h2><?= $isStudent ? 'A better way to get support.' : ($isAdmin ? 'Support operations at a glance.' : 'Your department case queue.') ?></h2>
        <p>
            <?php if ($isStudent): ?>Tell us what you need, get a unique ticket reference, and follow each update from your dashboard.<?php elseif ($isAdmin): ?>Review incoming complaints, route each case to the appropriate department, and keep support moving.<?php else: ?>Review the cases assigned to your department and keep students informed with clear status updates.<?php endif; ?>
        </p>
        <?php if ($isStudent): ?><a class="btn" href="?page=new-ticket">Submit a complaint <i class="ti ti-arrow-up-right ms-1"></i></a><?php elseif ($isAdmin): ?><a class="btn" href="?page=tickets">Review all complaints <i class="ti ti-arrow-up-right ms-1"></i></a><?php else: ?><a class="btn" href="?page=tickets">Open department cases <i class="ti ti-arrow-up-right ms-1"></i></a><?php endif; ?>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <article class="card stat-card"><div class="stat-icon"><i class="ti ti-inbox"></i></div><div class="card-body"><div class="stat-label"><?= $isStudent ? 'Total complaints submitted' : 'Cases in your workspace' ?></div><div class="stat-value"><?= (int) ($stats['total'] ?? 0) ?></div><div class="stat-footnote"><?= $isStudent ? 'All your support requests' : 'Total tickets visible to you' ?></div></div></article>
    </div>
    <div class="col-sm-6 col-xl-4">
        <article class="card stat-card"><div class="stat-icon"><i class="ti ti-hourglass"></i></div><div class="card-body"><div class="stat-label">Open cases</div><div class="stat-value"><?= (int) ($stats['open_count'] ?? 0) ?></div><div class="stat-footnote">Submitted, under review, or in progress</div></div></article>
    </div>
    <div class="col-sm-6 col-xl-4">
        <article class="card stat-card"><div class="stat-icon"><i class="ti ti-circle-check"></i></div><div class="card-body"><div class="stat-label">Resolved</div><div class="stat-value"><?= (int) ($stats['resolved_count'] ?? 0) ?></div><div class="stat-footnote">Cases marked as resolved</div></div></article>
    </div>
</div>

<section class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div><h3><?= $isStudent ? 'Your recent complaints' : 'Recently updated cases' ?></h3><span class="form-hint">The latest activity in your support workspace</span></div>
        <a class="small fw-semibold text-decoration-none" href="?page=tickets">View all <i class="ti ti-arrow-right"></i></a>
    </div>
    <?php if ($tickets): ?>
        <?php $showStudent = !$isStudent; require __DIR__ . '/ticket-table.php'; ?>
    <?php else: ?>
        <div class="empty-state"><i class="ti ti-inbox"></i><strong>No complaints yet</strong><span><?= $isStudent ? 'When you submit a ticket, it will appear here.' : 'There are no cases assigned to this workspace yet.' ?></span></div>
    <?php endif; ?>
</section>
