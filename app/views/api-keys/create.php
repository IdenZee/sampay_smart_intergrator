<div class="row justify-content-center">
<div class="col-lg-6">

<?php if ($newKey): ?>
<div class="alert alert-success border-0 shadow-sm mb-4">
    <h6 class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i>API Key Generated</h6>
    <p class="mb-2 small">Copy this key now — it will <strong>never be shown again</strong>.</p>
    <div class="input-group">
        <input type="text" id="rawKey" class="form-control font-monospace bg-dark text-success"
               value="<?= Format::e($newKey) ?>" readonly>
        <button class="btn btn-outline-secondary" onclick="copyKey()" title="Copy">
            <i class="bi bi-clipboard"></i>
        </button>
    </div>
    <div class="mt-3">
        <a href="<?= APP_URL ?>/api-keys" class="btn btn-sm btn-outline-success">
            <i class="bi bi-arrow-left me-1"></i> Back to API Keys
        </a>
    </div>
</div>
<script>
function copyKey() {
    navigator.clipboard.writeText(document.getElementById('rawKey').value)
        .then(() => alert('Copied to clipboard!'));
}
</script>

<?php else: ?>

<div class="content-card">
    <div class="card-header"><i class="bi bi-plus-circle me-2"></i>Generate API Key</div>
    <div class="p-4">
        <form method="POST">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">Business <span class="text-danger">*</span></label>
                <select name="business_id" class="form-select <?= isset($errors['business_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">— Select Business —</option>
                    <?php foreach ($businesses as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= (int)$preselect === (int)$b['id'] ? 'selected' : '' ?>>
                        <?= Format::e($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['business_id'])): ?><div class="invalid-feedback"><?= $errors['business_id'] ?></div><?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium">Key Name <span class="text-danger">*</span></label>
                <input type="text" name="key_name"
                       class="form-control <?= isset($errors['key_name']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($_POST['key_name'] ?? '') ?>"
                       placeholder="e.g. POS Terminal 1 — Lusaka Branch" required>
                <?php if (isset($errors['key_name'])): ?><div class="invalid-feedback"><?= $errors['key_name'] ?></div><?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium">Device Info</label>
                <input type="text" name="device_info" class="form-control"
                       value="<?= Format::e($_POST['device_info'] ?? '') ?>"
                       placeholder="e.g. Android T6, IMEI 35xxxxxxxx (optional)">
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-lightning-charge-fill me-1"></i> Generate Key
            </button>
            <a href="<?= APP_URL ?>/api-keys" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>

<?php endif; ?>

</div>
</div>
