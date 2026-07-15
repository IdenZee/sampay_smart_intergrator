<div class="row justify-content-center">
<div class="col-lg-8">
<div class="content-card">
    <div class="card-header">
        <i class="bi bi-person-fill me-2"></i>
        <?= $user ? 'Edit User' : 'New User' ?>
    </div>
    <div class="p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name"
                           class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($user['first_name'] ?? '') ?>" required>
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name"
                           class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($user['last_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email"
                           class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($user['email'] ?? '') ?>"
                           <?= $user ? 'readonly' : '' ?> required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= Format::e($user['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Employee ID</label>
                    <input type="text" name="employee_id" class="form-control"
                           value="<?= Format::e($user['employee_id'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Select role --</option>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"
                            <?= ($user['role_id'] ?? 0) == $role['id'] ? 'selected' : '' ?>>
                            <?= Format::e($role['display_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$user): ?>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Initial Password <span class="text-danger">*</span></label>
                    <input type="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Min. 8 characters" required minlength="8">
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                    <div class="form-text">User will be prompted to change this on first login.</div>
                </div>
                <?php endif; ?>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> <?= $user ? 'Save Changes' : 'Create User' ?>
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
