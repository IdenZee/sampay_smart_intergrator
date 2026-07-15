<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <?php if (Auth::isAny(['admin','manager'])): ?>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-sm btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> New User
    </a>
    <?php endif; ?>
</div>

<div class="content-card">
    <div class="card-header">
        <i class="bi bi-people-fill me-2"></i>All Users (<?= count($users) ?>)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Role</th>
                    <th>Last Login</th><th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:50%;background:#1a1a2e;color:#fff;
                                        display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:600;">
                                <?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?>
                            </div>
                            <?= Format::e($u['first_name'].' '.$u['last_name']) ?>
                        </div>
                    </td>
                    <td><?= Format::e($u['email']) ?></td>
                    <td><?= Format::roleBadge($u['role_name']) ?></td>
                    <td class="text-muted small">
                        <?= $u['last_login'] ? Format::timeAgo($u['last_login']) : 'Never' ?>
                    </td>
                    <td><?= Format::statusBadge($u['is_active']) ?></td>
                    <td class="text-end">
                        <?php if (Auth::isAny(['admin','manager'])): ?>
                        <a href="<?= APP_URL ?>/users/edit/<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= APP_URL ?>/users/reset-password/<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-warning" title="Reset Password">
                            <i class="bi bi-key"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (Auth::isAdmin() && $u['id'] !== Auth::id()): ?>
                        <form method="POST" action="<?= APP_URL ?>/users/delete/<?= $u['id'] ?>"
                              class="d-inline" onsubmit="return confirm('Deactivate this user?')">
                            <button class="btn btn-sm btn-outline-danger" title="Deactivate">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No users yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
