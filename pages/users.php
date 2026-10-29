<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-heading">
    <div><div class="eyebrow">TEAM ACCESS</div><h2>Department accounts</h2><p>Create staff logins so each department can view and update only its assigned cases.</p></div>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <section class="card"><div class="card-header"><h3>Create department login</h3></div><div class="card-body">
            <form method="post">
                <?= csrf_field() ?><input type="hidden" name="action" value="create_department_user">
                <div class="mb-3"><label class="form-label" for="full_name">Staff member's full name</label><input class="form-control" id="full_name" name="full_name" required maxlength="150"></div>
                <div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required maxlength="190"></div>
                <div class="mb-3"><label class="form-label" for="department_id">Department</label><select class="form-select" id="department_id" name="department_id" required><option value="">Select a department</option><?php foreach ($departments as $department): ?><option value="<?= e($department['id']) ?>"><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label" for="password">Temporary password</label><input class="form-control" id="password" name="password" type="password" minlength="8" required><div class="form-hint">At least 8 characters. Share credentials with the staff member securely.</div></div>
                <button class="btn btn-primary" type="submit"><i class="ti ti-user-plus me-1"></i>Create department account</button>
            </form>
        </div></section>
        <section class="card mt-4"><div class="card-header"><h3>Manage departments</h3><span class="form-hint">Update the sample list to match the college.</span></div><div class="card-body">
            <form method="post" class="mb-3">
                <?= csrf_field() ?><input type="hidden" name="action" value="save_department">
                <label class="form-label" for="new_department_name">Add a department</label>
                <div class="d-flex gap-2"><input class="form-control" id="new_department_name" name="name" maxlength="120" required placeholder="Department name"><button class="btn btn-primary btn-sm flex-shrink-0" type="submit">Add</button></div>
            </form>
            <div class="d-grid gap-2">
                <?php foreach ($departments as $department): ?>
                    <form method="post" class="d-flex gap-2 align-items-center">
                        <?= csrf_field() ?><input type="hidden" name="action" value="save_department"><input type="hidden" name="department_id" value="<?= e($department['id']) ?>">
                        <input class="form-control form-control-sm" name="name" value="<?= e($department['name']) ?>" maxlength="120" required aria-label="Department name">
                        <button class="btn btn-light btn-sm flex-shrink-0" type="submit" aria-label="Save department name"><i class="ti ti-device-floppy"></i></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div></section>
        <div class="alert alert-info mt-3 small"><i class="ti ti-info-circle me-1"></i>Department accounts can only access cases assigned to their own department.</div>
    </div>
    <div class="col-lg-7">
        <section class="card">
            <div class="card-header"><h3>Active department logins</h3><span class="form-hint"><?= count($departmentUsers) ?> staff account<?= count($departmentUsers) === 1 ? '' : 's' ?></span></div>
            <?php if ($departmentUsers): ?>
                <div class="table-responsive"><table class="table"><thead><tr><th>STAFF MEMBER</th><th>DEPARTMENT</th><th>CREATED</th></tr></thead><tbody>
                    <?php foreach ($departmentUsers as $departmentUser): ?><tr><td><strong><?= e($departmentUser['full_name']) ?></strong><div class="form-hint"><?= e($departmentUser['email']) ?></div></td><td><?= e($departmentUser['department_name']) ?></td><td><?= e(date('M j, Y', strtotime($departmentUser['created_at']))) ?></td></tr><?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?><div class="empty-state"><i class="ti ti-users"></i><strong>No department accounts yet</strong><span>Create the first staff login using the form.</span></div><?php endif; ?>
        </section>
    </div>
</div>
