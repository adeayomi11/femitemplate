<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}
$ticket = $ticket;
?>
<div class="page-heading">
    <div><div class="eyebrow">COMPLAINT DETAILS</div><h2><?= e($ticket['subject']) ?></h2><p>Submitted <?= e(date('F j, Y \a\t g:i a', strtotime($ticket['created_at']))) ?></p></div>
    <a class="btn btn-light btn-sm" href="?page=tickets"><i class="ti ti-arrow-left me-1"></i>Back to cases</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <section class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><h3>Ticket information</h3><span class="badge-status status-<?= e(strtolower(str_replace(' ', '-', $ticket['status']))) ?>"><?= e($ticket['status']) ?></span></div>
            <div class="ticket-summary">
                <div class="summary-item"><span>Reference number</span><strong class="ticket-reference"><?= e($ticket['reference_number']) ?></strong></div>
                <div class="summary-item"><span>Topic</span><strong><?= e($ticket['category']) ?></strong></div>
                <div class="summary-item"><span>Department</span><strong><?= !empty($ticket['department_name']) ? e($ticket['department_name']) : 'Not assigned yet' ?></strong></div>
                <?php if ($user['role'] !== 'student'): ?><div class="summary-item"><span>Student</span><strong><?= e($ticket['student_name']) ?></strong></div><div class="summary-item"><span>Matric number</span><strong><?= e($ticket['matric_number'] ?: 'Not provided') ?></strong></div><div class="summary-item"><span>Email</span><strong><?= e($ticket['student_email']) ?></strong></div><?php endif; ?>
            </div>
            <div class="card-body border-top"><div class="form-label text-uppercase">Issue details</div><p class="small lh-lg mb-0" style="white-space:pre-line"><?= e($ticket['description']) ?></p></div>
        </section>
        <section class="card">
            <div class="card-header"><h3>Updates and activity</h3></div>
            <div class="card-body">
                <?php if ($updates): ?><div class="timeline"><?php foreach ($updates as $update): ?><div class="timeline-item"><span class="timeline-dot"></span><strong><?= e($update['status']) ?> <span class="text-muted fw-normal">· <?= e($update['author_name']) ?></span></strong><p><?= e($update['message']) ?></p><time><?= e(date('M j, Y \a\t g:i a', strtotime($update['created_at']))) ?></time></div><?php endforeach; ?></div><?php else: ?><div class="empty-state py-3"><span>No updates have been recorded.</span></div><?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-lg-4">
        <?php if ($user['role'] === 'admin'): ?>
            <section class="card mb-4"><div class="card-header"><h3>Route to department</h3></div><div class="card-body">
                <p class="form-hint mb-3">Assign this complaint to the team best placed to help.</p>
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="assign_ticket"><input type="hidden" name="ticket_id" value="<?= e($ticket['id']) ?>">
                    <label class="form-label" for="department_id">Department</label>
                    <select class="form-select mb-3" id="department_id" name="department_id" required><option value="">Select a department</option><?php foreach ($departments as $department): ?><option value="<?= e($department['id']) ?>" <?= (int) $ticket['department_id'] === (int) $department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option><?php endforeach; ?></select>
                    <button class="btn btn-primary w-100" type="submit"><i class="ti ti-route me-1"></i>Save routing</button>
                </form>
            </div></section>
        <?php elseif ($user['role'] === 'department'): ?>
            <section class="card"><div class="card-header"><h3>Update case</h3></div><div class="card-body">
                <p class="form-hint mb-3">The student will see this status and message in their ticket history.</p>
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="department_update"><input type="hidden" name="ticket_id" value="<?= e($ticket['id']) ?>">
                    <div class="mb-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status" required><?php foreach (array('Under Review', 'In Progress', 'Resolved', 'Closed') as $status): ?><option value="<?= e($status) ?>" <?= $ticket['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label" for="message">Update for the student</label><textarea class="form-control" id="message" name="message" required maxlength="5000" placeholder="Explain what has been done or what happens next."></textarea></div>
                    <button class="btn btn-primary w-100" type="submit"><i class="ti ti-device-floppy me-1"></i>Save update</button>
                </form>
            </div></section>
        <?php else: ?>
            <section class="card"><div class="card-body">
                <div class="help-icon mb-3"><i class="ti ti-bell-ringing"></i></div><h3 class="fs-6 fw-semibold">Stay up to date</h3>
                <p class="form-hint lh-lg mb-0">Your complaint status and replies from the college team appear in the activity timeline. Keep your reference number <strong><?= e($ticket['reference_number']) ?></strong> to track this case later.</p>
            </div></section>
        <?php endif; ?>
    </div>
</div>
