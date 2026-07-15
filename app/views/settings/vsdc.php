<div class="row justify-content-center">
<div class="col-lg-7">
<div class="content-card">
    <div class="card-header"><i class="bi bi-plug-fill me-2"></i>VSDC Configuration</div>
    <div class="p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Label</label>
                    <input type="text" name="label" class="form-control"
                           value="<?= Format::e($config['label'] ?? 'Main VSDC') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">VSDC URL <span class="text-danger">*</span></label>
                    <input type="url" name="vsdc_url" class="form-control"
                           value="<?= Format::e($config['vsdc_url'] ?? '') ?>"
                           placeholder="http://your-vsdc-server:8081/vsdc" required>
                    <div class="form-text">Sandbox: http://etheedenprojects.com:8081/sandboxvsdc</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Device Serial</label>
                    <input type="text" name="device_serial" class="form-control"
                           value="<?= Format::e($config['device_serial'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">MRC No</label>
                    <input type="text" name="mrc_no" class="form-control"
                           value="<?= Format::e($config['mrc_no'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Tax Office Name</label>
                    <input type="text" name="tax_office_name" class="form-control"
                           value="<?= Format::e($config['tax_office_name'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               <?= ($config['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium">VSDC Active</label>
                    </div>
                </div>
                <?php if (!empty($config['initialized'])): ?>
                <div class="col-12">
                    <div class="alert alert-success py-2 mb-0 small">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Device initialized on <?= Format::datetime($config['initialized_at']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save VSDC Config
                </button>
                <a href="<?= APP_URL ?>/settings" class="btn btn-outline-secondary">Back to Settings</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
