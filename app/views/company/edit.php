<div class="row justify-content-center">
<div class="col-lg-8">
<div class="content-card">
    <div class="card-header"><i class="bi bi-buildings-fill me-2"></i>Company Profile</div>
    <div class="p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($company['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">TPIN <span class="text-danger">*</span></label>
                    <input type="text" name="tpin" class="form-control <?= isset($errors['tpin']) ? 'is-invalid' : '' ?>"
                           value="<?= Format::e($company['tpin'] ?? '') ?>" required>
                    <?php if (isset($errors['tpin'])): ?><div class="invalid-feedback"><?= $errors['tpin'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-medium">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= Format::e($company['address'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">City</label>
                    <input type="text" name="city" class="form-control" value="<?= Format::e($company['city'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= Format::e($company['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= Format::e($company['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Currency</label>
                    <select name="currency_code" class="form-select">
                        <option value="ZMW" <?= ($company['currency_code'] ?? 'ZMW') === 'ZMW' ? 'selected' : '' ?>>ZMW — Zambian Kwacha</option>
                        <option value="USD" <?= ($company['currency_code'] ?? '') === 'USD' ? 'selected' : '' ?>>USD — US Dollar</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Company Profile
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
