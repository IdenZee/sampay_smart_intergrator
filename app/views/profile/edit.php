<?php if ($mustChange): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    You must change your password before continuing.
</div>
<?php endif; ?>

<div class="row g-4">

    <?php if (!$mustChange): ?>
    <!-- Profile details -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header"><i class="bi bi-person-fill me-2"></i>Profile Details</div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="profile">
                    <div class="mb-3">
                        <label class="form-label fw-medium">First Name</label>
                        <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                               value="<?= Format::e($user['first_name']) ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Last Name</label>
                        <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                               value="<?= Format::e($user['last_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" class="form-control" value="<?= Format::e($user['email']) ?>" readonly>
                        <div class="form-text">Email cannot be changed here.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= Format::e($user['phone'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Change password -->
    <div class="col-lg-<?= $mustChange ? '6 mx-auto' : '6' ?>">
        <div class="content-card">
            <div class="card-header"><i class="bi bi-shield-lock-fill me-2"></i>Change Password</div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Current Password</label>
                        <input type="password" name="current_password"
                               class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                               required autofocus>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">New Password</label>
                        <input type="password" name="new_password"
                               class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                               placeholder="Min. 8 characters" required minlength="8">
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Confirm New Password</label>
                        <input type="password" name="password_confirm"
                               class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                               required>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i> Update Password
                    </button>
                    <?php if (!$mustChange): ?>
                    <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline-secondary ms-2">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

</div>
