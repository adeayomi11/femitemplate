<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-heading">
    <div><div class="eyebrow">SUPPORT QUEUE</div><h2><?= e($title) ?></h2><p><?= $user['role'] === 'student' ? 'Review your submitted complaints and open a ticket to see its complete history.' : 'Search and review cases available to your role.' ?></p></div>
    <?php if ($user['role'] === 'student'): ?><a class="btn btn-primary btn-sm" href="?page=new-ticket"><i class="ti ti-plus me-1"></i>New complaint</a><?php endif; ?>
</div>
<section class="card">
    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <h3><?= count($tickets) ?> <?= count($tickets) === 1 ? 'case' : 'cases' ?></h3>
        <form class="filter-row" method="get">
            <input type="hidden" name="page" value="tickets">
            <input class="form-control" type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= $user['role'] === 'student' ? 'Search your complaints' : 'Reference, subject, student…' ?>" aria-label="Search cases">
            <select class="form-select" name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach (ticket_statuses() as $status): ?><option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <?php if ($filters): ?><a class="btn btn-light btn-sm" href="?page=tickets">Clear</a><?php endif; ?>
        </form>
    </div>
    <?php if ($tickets): ?>
        <?php $showStudent = $user['role'] !== 'student'; require __DIR__ . '/ticket-table.php'; ?>
    <?php else: ?>
        <div class="empty-state"><i class="ti ti-search"></i><strong>No matching complaints</strong><span><?= $user['role'] === 'student' ? 'Your submitted complaints will show here.' : 'Try adjusting the filters or check again later.' ?></span></div>
    <?php endif; ?>
</section>
