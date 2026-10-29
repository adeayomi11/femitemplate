<div class="table-responsive">
    <table class="table">
        <thead><tr><th>REFERENCE</th><th>COMPLAINT</th><?php if (!empty($showStudent)): ?><th>STUDENT</th><?php endif; ?><th>DEPARTMENT</th><th>STATUS</th><th>UPDATED</th><th></th></tr></thead>
        <tbody>
        <?php
        if (!defined('APP_BOOTSTRAPPED')) {
            http_response_code(404);
            exit;
        }
        ?>
        <?php foreach ($tickets as $item): ?>
            <tr>
                <td><a href="?page=ticket&amp;id=<?= e($item['id']) ?>"><?= e($item['reference_number']) ?></a></td>
                <td><a href="?page=ticket&amp;id=<?= e($item['id']) ?>"><?= e($item['subject']) ?></a><div class="form-hint"><?= e($item['category']) ?></div></td>
                <?php if (!empty($showStudent)): ?><td><?= e($item['student_name']) ?><?php if (!empty($item['matric_number'])): ?><div class="form-hint"><?= e($item['matric_number']) ?></div><?php endif; ?></td><?php endif; ?>
                <td><?= !empty($item['department_name']) ? e($item['department_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                <td><span class="badge-status status-<?= e(strtolower(str_replace(' ', '-', $item['status']))) ?>"><?= e($item['status']) ?></span></td>
                <td><?= e(date('M j, Y', strtotime($item['updated_at'] ?? $item['created_at']))) ?></td>
                <td class="text-end"><a aria-label="View ticket" href="?page=ticket&amp;id=<?= e($item['id']) ?>"><i class="ti ti-chevron-right"></i></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
