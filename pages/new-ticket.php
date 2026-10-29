<?php
if (!defined('APP_BOOTSTRAPPED')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-heading">
    <div><div class="eyebrow">STUDENT SUPPORT</div><h2>Tell us how we can help</h2><p>Share enough detail for the right team to understand and respond to your request.</p></div>
    <a class="btn btn-light btn-sm" href="?page=tickets"><i class="ti ti-arrow-left me-1"></i>My complaints</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <section class="card"><div class="card-body p-lg-4">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_ticket">
                <div class="mb-4"><label class="form-label" for="category">Topic <span class="text-danger">*</span></label><select class="form-select" id="category" name="category" required><option value="">Select the closest topic</option><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>"><?= e($category) ?></option><?php endforeach; ?></select><div class="form-hint">Choose the topic that best describes your complaint or request.</div></div>
                <div class="mb-4"><label class="form-label" for="subject">Subject <span class="text-danger">*</span></label><input class="form-control" id="subject" name="subject" maxlength="180" required placeholder="A short summary of the issue"></div>
                <div class="mb-4"><label class="form-label" for="description">Describe your issue or request <span class="text-danger">*</span></label><textarea class="form-control" id="description" name="description" minlength="15" maxlength="12000" required placeholder="Include relevant dates, what happened, and what outcome would help."></textarea><div class="form-hint">Please do not include passwords or sensitive personal information.</div></div>
                <div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit"><i class="ti ti-send me-1"></i>Submit complaint</button><a class="btn btn-light" href="?page=tickets">Cancel</a></div>
            </form>
        </div></section>
    </div>
    <div class="col-lg-4">
        <section class="card"><div class="card-body">
            <div class="help-icon mb-3"><i class="ti ti-info-circle"></i></div>
            <h3 class="fs-6 fw-semibold">What happens next?</h3>
            <p class="form-hint lh-lg">Your complaint gets a unique reference number as soon as it is submitted. An administrator reviews it and routes it to the relevant department. You can use the reference to follow status changes and updates.</p>
            <hr>
            <div class="d-flex gap-2 align-items-start"><i class="ti ti-shield-check text-success fs-5"></i><span class="form-hint">Your ticket is visible only to you, support administrators, and the department handling it.</span></div>
        </div></section>
    </div>
</div>
