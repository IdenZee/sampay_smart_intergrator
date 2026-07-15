<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/businesses" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-bold"><?= Format::e($business['name']) ?></h5>
    <?= Format::statusBadge($business['is_active']) ?>
    <?php if (Auth::isSamPayAdmin()): ?>
    <a href="<?= APP_URL ?>/businesses/edit/<?= $business['id'] ?>" class="btn btn-sm btn-outline-primary ms-auto">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <?php endif; ?>
</div>

<div class="row g-4">

    <!-- Info + VSDC -->
    <div class="col-lg-5">
        <div class="content-card mb-4">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Business Details</div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted small" width="130">TPIN</td><td class="fw-medium"><?= Format::e($business['tpin']) ?></td></tr>
                    <tr><td class="text-muted small">Branch Code</td><td><?= Format::e($business['branch_code'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted small">Currency</td><td><?= Format::e($business['currency_code']) ?></td></tr>
                    <tr><td class="text-muted small">Phone</td><td><?= Format::e($business['phone'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted small">Email</td><td><?= Format::e($business['email'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted small">City</td><td><?= Format::e($business['city'] ?? '—') ?></td></tr>
                    <?php if ($business['sampay_business_id']): ?>
                    <tr><td class="text-muted small">SamPay ID</td><td class="small text-muted font-monospace"><?= Format::e($business['sampay_business_id']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header"><i class="bi bi-plug-fill me-2 text-warning"></i>VSDC Status</div>
            <div class="p-3">
                <?php if ($business['vsdc_url']): ?>
                    <div class="mb-2">
                        <?php if ($business['initialized']): ?>
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Initialised
                        </span>
                        <div class="small text-muted mt-1">Since <?= Format::datetime($business['initialized_at']) ?></div>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Not Initialised
                        </span>
                        <?php endif; ?>
                    </div>
                    <table class="table table-sm table-borderless mb-0 small">
                        <tr><td class="text-muted" width="120">VSDC URL</td>
                            <td class="font-monospace text-truncate" style="max-width:200px"><?= Format::e($business['vsdc_url']) ?></td></tr>
                        <tr><td class="text-muted">Device Serial</td><td><?= Format::e($business['device_serial'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">MRC No</td><td><?= Format::e($business['mrc_no'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Tax Office</td><td><?= Format::e($business['tax_office_name'] ?? '—') ?></td></tr>
                    </table>
                    <a href="<?= APP_URL ?>/vsdc/dashboard/<?= $business['id'] ?>" class="btn btn-sm btn-outline-warning mt-2">
                        <i class="bi bi-lightning-charge-fill me-1"></i>
                        <?= $business['initialized'] ? 'Manage VSDC' : 'Set Up VSDC' ?>
                    </a>
                <?php else: ?>
                    <p class="text-muted mb-2 small">No VSDC configured for this business.</p>
                    <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/businesses/edit/<?= $business['id'] ?>" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-plug me-1"></i> Configure VSDC
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats + Items + API Keys -->
    <div class="col-lg-7">
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="stat-card text-center">
                    <div class="stat-value"><?= number_format($business['item_count'] ?? 0) ?></div>
                    <div class="stat-label">Active Items</div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card text-center">
                    <div class="stat-value"><?= number_format($salesCount) ?></div>
                    <div class="stat-label">Total Sales</div>
                </div>
            </div>
        </div>

        <!-- Recent Items -->
        <div class="content-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-seam me-2"></i>Recent Items</span>
                <a href="<?= APP_URL ?>/items?business_id=<?= $business['id'] ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <?php if ($items): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Item</th><th>Code</th><th>Price</th><th>VSDC</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="small"><?= Format::e($item['item_name']) ?></td>
                        <td class="small text-muted font-monospace"><?= Format::e($item['item_code']) ?></td>
                        <td class="small"><?= Format::currency($item['selling_price']) ?></td>
                        <td>
                            <?= $item['vsdc_registered']
                                ? '<span class="badge bg-success-subtle text-success">Registered</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary">Pending</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted small p-3 mb-0">No items yet. <a href="<?= APP_URL ?>/items/create?business_id=<?= $business['id'] ?>">Add an item.</a></p>
            <?php endif; ?>
        </div>

        <!-- API Keys -->
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-phone-fill me-2"></i>Android POS API Keys</span>
                <?php if (Auth::isAny(['admin','manager'])): ?>
                <a href="<?= APP_URL ?>/api-keys/create?business_id=<?= $business['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> New Key
                </a>
                <?php endif; ?>
            </div>
            <?php if ($apiKeys): ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Status</th><th>Last Used</th></tr></thead>
                    <tbody>
                    <?php foreach ($apiKeys as $k): ?>
                    <tr>
                        <td class="small"><?= Format::e($k['key_name']) ?></td>
                        <td><?= Format::statusBadge($k['is_active']) ?></td>
                        <td class="small text-muted"><?= $k['last_used_at'] ? Format::timeAgo($k['last_used_at']) : 'Never' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted small p-3 mb-0">No API keys. Android app won't be able to connect until you create one.</p>
            <?php endif; ?>
        </div>

    </div>
</div>
