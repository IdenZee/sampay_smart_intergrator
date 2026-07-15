<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex align-items-center gap-2">
        <select name="business_id" class="form-select form-select-sm" style="width:220px" onchange="this.form.submit()">
            <option value="">All Businesses</option>
            <?php foreach ($businesses as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $filterBusiness == $b['id'] ? 'selected' : '' ?>>
                <?= Format::e($b['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="<?= APP_URL ?>/api-keys/create<?= $filterBusiness ? '?business_id='.$filterBusiness : '' ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Generate API Key
    </a>
</div>

<div class="content-card">
    <div class="card-header">
        <i class="bi bi-phone-fill me-2"></i>Android POS API Keys (<?= count($keys) ?>)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Business</th>
                    <th>Device Info</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($keys as $k): ?>
            <tr>
                <td class="fw-medium"><?= Format::e($k['key_name']) ?></td>
                <td class="small"><?= Format::e($k['biz_name'] ?? '—') ?></td>
                <td class="small text-muted"><?= Format::e($k['device_info'] ?? '—') ?></td>
                <td><?= $k['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Revoked</span>' ?></td>
                <td class="small text-muted"><?= $k['last_used_at'] ? Format::timeAgo($k['last_used_at']) : 'Never' ?></td>
                <td class="small text-muted"><?= Format::date($k['created_at']) ?></td>
                <td class="text-end">
                    <?php if ($k['is_active']): ?>
                    <form method="POST" action="<?= APP_URL ?>/api-keys/revoke/<?= $k['id'] ?>"
                          onsubmit="return confirm('Revoke this key? The Android device will lose access immediately.')">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-lg"></i> Revoke
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($keys)): ?>
            <tr><td colspan="7" class="text-center text-muted py-5">
                No API keys yet. <a href="<?= APP_URL ?>/api-keys/create">Generate one</a> to connect the Android app.
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-card mt-4">
    <div class="card-header"><i class="bi bi-code-slash me-2"></i>Android App Integration</div>
    <div class="p-4">
        <p class="text-muted small mb-3">The Android POS app connects to this Integrator via these REST endpoints. All requests must include the API key in the <code>Authorization</code> header.</p>
        <div class="bg-dark text-light rounded p-3 font-monospace small">
            <div class="text-success mb-1"># Authentication header</div>
            Authorization: Bearer sk_xxxxxxxxxxxxxxxx<br><br>
            <div class="text-success mb-1"># Base URL</div>
            <?= APP_URL ?>/api/v1/<br><br>
            <div class="text-success mb-1"># Endpoints</div>
            GET  /api/v1/items              → item catalogue<br>
            POST /api/v1/sales              → submit a sale<br>
            GET  /api/v1/sales/{id}         → get receipt<br>
            GET  /api/v1/ping               → connectivity check
        </div>
    </div>
</div>
