<div class="row justify-content-center">
<div class="col-xl-9">

<!-- Business Details -->
<div class="content-card mb-4">
    <div class="card-header">
        <i class="bi bi-buildings-fill me-2"></i>
        <?= $business ? 'Edit Business' : 'Register New Business' ?>
    </div>
    <div class="p-4">
        <form method="POST" id="bizForm">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-medium">Business Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($business['name'] ?? '') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">TPIN <span class="text-danger">*</span></label>
                <input type="text" name="tpin" class="form-control <?= isset($errors['tpin']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($business['tpin'] ?? '') ?>" required maxlength="20">
                <?php if (isset($errors['tpin'])): ?><div class="invalid-feedback"><?= $errors['tpin'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">ZRA Branch Code</label>
                <input type="text" name="branch_code" class="form-control"
                       value="<?= Format::e($business['branch_code'] ?? '') ?>"
                       placeholder="e.g. 001">
                <div class="form-text">Assigned by ZRA for Smart Invoice.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Currency</label>
                <select name="currency_code" class="form-select">
                    <option value="ZMW" <?= ($business['currency_code'] ?? 'ZMW') === 'ZMW' ? 'selected' : '' ?>>ZMW — Zambian Kwacha</option>
                    <option value="USD" <?= ($business['currency_code'] ?? '') === 'USD' ? 'selected' : '' ?>>USD — US Dollar</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">SamPay Business ID</label>
                <input type="text" name="sampay_business_id" class="form-control"
                       value="<?= Format::e($business['sampay_business_id'] ?? '') ?>"
                       placeholder="External ref (optional)">
                <div class="form-text">Links to existing SamPay platform account.</div>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-medium">Address</label>
                <input type="text" name="address" class="form-control" value="<?= Format::e($business['address'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">City</label>
                <input type="text" name="city" class="form-control" value="<?= Format::e($business['city'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= Format::e($business['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Email <span class="text-danger"><?= isset($errors['email']) ? '*' : '' ?></span></label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($business['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>
            <?php if ($business): ?>
            <div class="col-md-4">
                <label class="form-label fw-medium">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= ($business['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !($business['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$business): ?>
        <hr class="my-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-person-gear me-2 text-primary"></i>Business Administrator Account</h6>
        <p class="text-muted small mb-3">
            A login account will be auto-created for this business. A temporary password is generated and shown once — the user must change it on first login.
        </p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                <input type="text" name="admin_first_name" class="form-control <?= isset($errors['admin_first_name']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($_POST['admin_first_name'] ?? '') ?>" required>
                <?php if (isset($errors['admin_first_name'])): ?><div class="invalid-feedback"><?= $errors['admin_first_name'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="admin_last_name" class="form-control <?= isset($errors['admin_last_name']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($_POST['admin_last_name'] ?? '') ?>" required>
                <?php if (isset($errors['admin_last_name'])): ?><div class="invalid-feedback"><?= $errors['admin_last_name'] ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Login Email <span class="text-danger">*</span></label>
                <input type="email" name="admin_email" class="form-control <?= isset($errors['admin_email']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($_POST['admin_email'] ?? '') ?>" required>
                <?php if (isset($errors['admin_email'])): ?><div class="invalid-feedback"><?= $errors['admin_email'] ?></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <hr class="my-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-plug-fill me-2 text-warning"></i>VSDC Configuration</h6>

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">VSDC URL</label>
                <input type="url" name="vsdc_url" class="form-control"
                       value="<?= Format::e($business['vsdc_url'] ?? '') ?>"
                       placeholder="http://your-vsdc-server:8081/vsdc">
                <div class="form-text">
                    Sandbox: <code>http://etheedenprojects.com:8081/sandboxvsdc</code> — leave blank to configure later.
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Device Serial</label>
                <input type="text" name="device_serial" class="form-control"
                       value="<?= Format::e($business['device_serial'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">MRC No</label>
                <input type="text" name="mrc_no" class="form-control"
                       value="<?= Format::e($business['mrc_no'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Tax Office Name</label>
                <input type="text" name="tax_office_name" class="form-control"
                       value="<?= Format::e($business['tax_office_name'] ?? '') ?>">
            </div>
            <?php if ($business && ($business['vsdc_url'] ?? '')): ?>
            <div class="col-md-4">
                <label class="form-label fw-medium">VSDC Active</label>
                <select name="vsdc_active" class="form-select">
                    <option value="1" <?= ($business['vsdc_active'] ?? 1) ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= !($business['vsdc_active'] ?? 1) ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $business ? 'Save Changes' : 'Register Business' ?>
            </button>
            <a href="<?= APP_URL ?>/businesses" class="btn btn-outline-secondary">Cancel</a>
        </div>

        </form>
    </div>
</div>

</div>
</div>
