<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <?php if (Auth::isAdmin()): ?>
    <a href="<?= APP_URL ?>/branches/create" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Branch
    </a>
    <?php endif; ?>
</div>

<div class="content-card">
    <div class="card-header">
        <i class="bi bi-building me-2"></i>All Branches (<?= count($branches) ?>)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th><th>Code</th><th>City</th><th>Phone</th>
                    <th>HQ</th><th>Status</th>
                    <?php if (Auth::isAdmin()): ?><th class="text-end">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($branches as $b): ?>
                <tr>
                    <td class="fw-medium"><?= Format::e($b['name']) ?></td>
                    <td><code><?= Format::e($b['code']) ?></code></td>
                    <td><?= Format::e($b['city']) ?></td>
                    <td><?= Format::e($b['phone']) ?></td>
                    <td><?= $b['is_hq'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '' ?></td>
                    <td><?= Format::statusBadge($b['is_active']) ?></td>
                    <?php if (Auth::isAdmin()): ?>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/branches/edit/<?= $b['id'] ?>"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= APP_URL ?>/branches/delete/<?= $b['id'] ?>"
                              class="d-inline"
                              onsubmit="return confirm('Deactivate this branch?')">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($branches)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No branches yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
