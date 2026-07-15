<div class="content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text me-2"></i>Audit Log (<?= number_format($total) ?> entries)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>Time</th><th>User</th><th>Action</th>
                    <th>Module</th><th>Description</th><th>IP</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $e): ?>
                <tr>
                    <td class="text-muted small text-nowrap"><?= Format::datetime($e['created_at']) ?></td>
                    <td class="small"><?= Format::e($e['user_name'] ?? 'System') ?></td>
                    <td>
                        <?php
                        $badge = match($e['action']) {
                            'LOGIN'        => 'bg-success',
                            'LOGOUT'       => 'bg-secondary',
                            'CREATE'       => 'bg-primary',
                            'UPDATE'       => 'bg-warning text-dark',
                            'DELETE'       => 'bg-danger',
                            'LOGIN_FAILED' => 'bg-danger',
                            default        => 'bg-info text-dark',
                        };
                        ?>
                        <span class="badge <?= $badge ?>"><?= $e['action'] ?></span>
                    </td>
                    <td class="small text-muted"><?= Format::e($e['module'] ?? '') ?></td>
                    <td class="small"><?= Format::e($e['description'] ?? '') ?></td>
                    <td class="small text-muted"><?= Format::e($e['ip_address'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($entries)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No audit entries yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="p-3 d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= APP_URL ?>/audit-log?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
