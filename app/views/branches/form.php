<div class="row justify-content-center">
<div class="col-lg-7">
<div class="content-card">
    <div class="card-header">
        <i class="bi bi-building me-2"></i>
        <?= $branch ? 'Edit Branch' : 'New Branch' ?>
    </div>
    <div class="p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Branch Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($branch['name'] ?? $_POST['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Branch Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control text-uppercase <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($branch['code'] ?? $_POST['code'] ?? '') ?>"
                           placeholder="e.g. LUS01" maxlength="20" required>
                    <?php if (isset($errors['code'])): ?>
                        <div class="invalid-feedback"><?= $errors['code'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-medium">Address</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= Format::e($branch['address'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">City</label>
                    <input type="text" name="city" class="form-control"
                           value="<?= Format::e($branch['city'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= Format::e($branch['phone'] ?? '') ?>">
                </div>
                <div class="col-12 d-flex gap-4 pt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_hq" id="is_hq" value="1"
                               <?= ($branch['is_hq'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_hq">This is the HQ branch</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               <?= ($branch['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> <?= $branch ? 'Save Changes' : 'Create Branch' ?>
                </button>
                <a href="<?= APP_URL ?>/branches" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
