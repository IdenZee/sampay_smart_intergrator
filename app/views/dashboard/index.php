<div class="row g-3 mb-4">

    <?php if (!$isBusiness): ?>
    <!-- SamPay Admin stats -->
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Businesses</div>
                    <div class="stat-value"><?= $stats['businesses'] ?></div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-buildings-fill"></i>
                </div>
            </div>
            <a href="<?= APP_URL ?>/businesses" class="small text-muted text-decoration-none">View all →</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Users</div>
                    <div class="stat-value"><?= $stats['users'] ?></div>
                </div>
                <div class="stat-icon" style="background:rgba(240,165,0,.12);color:#f0a500">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <a href="<?= APP_URL ?>/users" class="small text-muted text-decoration-none">Manage →</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Active Items</div>
                    <div class="stat-value"><?= $stats['items'] ?></div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
            <a href="<?= APP_URL ?>/items" class="small text-muted text-decoration-none">Manage items →</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Sales Today</div>
                    <div class="stat-value"><?= $stats['sales_today'] ?></div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <a href="<?= APP_URL ?>/sales" class="small text-muted text-decoration-none">View sales →</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Fiscalised</div>
                    <div class="stat-value text-success"><?= $stats['fiscalised'] ?></div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check-fill"></i>
                </div>
            </div>
            <span class="small text-muted">ZRA signed receipts</span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Pending VSDC</div>
                    <div class="stat-value <?= $stats['pending_vsdc'] > 0 ? 'text-warning' : 'text-success' ?>">
                        <?= $stats['pending_vsdc'] ?>
                    </div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
            </div>
            <a href="<?= APP_URL ?>/items" class="small text-muted text-decoration-none">Register items →</a>
        </div>
    </div>

</div>

<div class="row g-4">

    <!-- Recent Sales -->
    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>Recent Sales</span>
                <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <?php if ($recentSales): ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <?php if (!$isBusiness): ?><th>Business</th><?php endif; ?>
                            <th class="text-end">Amount</th>
                            <th>VSDC</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentSales as $s): ?>
                    <tr>
                        <td class="font-monospace small"><?= Format::e($s['sale_ref']) ?></td>
                        <?php if (!$isBusiness): ?>
                        <td class="small"><?= Format::e($s['business_name']) ?></td>
                        <?php endif; ?>
                        <td class="text-end fw-medium"><?= Format::currency($s['total_amount']) ?></td>
                        <td>
                            <?= $s['is_fiscalised']
                                ? '<span class="badge bg-success">Fiscal</span>'
                                : '<span class="badge bg-warning text-dark">Pending</span>' ?>
                        </td>
                        <td class="small text-muted"><?= Format::timeAgo($s['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-receipt display-6 d-block mb-2 opacity-25"></i>
                <?= $isBusiness
                    ? 'No sales yet. Sales will appear here when the Android POS submits them.'
                    : 'No sales yet. Connect the Android POS app to start receiving sales.' ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-5">
        <div class="content-card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Quick Actions
            </div>
            <div class="p-3 d-grid gap-2">
                <?php if (Auth::isSamPayAdmin()): ?>
                <a href="<?= APP_URL ?>/businesses/create" class="btn btn-outline-primary text-start">
                    <i class="bi bi-plus-lg me-2"></i> Register a Business
                </a>
                <?php endif; ?>
                <?php if (!Auth::isBusinessUser()): ?>
                <a href="<?= APP_URL ?>/items/create" class="btn btn-outline-success text-start">
                    <i class="bi bi-box-seam me-2"></i> Add Stock Item
                </a>
                <?php endif; ?>
                <?php if (Auth::isSamPayAdmin()): ?>
                <a href="<?= APP_URL ?>/api-keys/create" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-phone me-2"></i> Generate Android API Key
                </a>
                <a href="<?= APP_URL ?>/settings" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-gear me-2"></i> System Settings
                </a>
                <?php endif; ?>
                <?php if (Auth::isBusinessAdmin()): ?>
                <a href="<?= APP_URL ?>/users/create" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-person-plus me-2"></i> Add Business User
                </a>
                <a href="<?= APP_URL ?>/businesses/<?= Auth::businessId() ?>" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-buildings me-2"></i> Business Profile
                </a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/sales" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-receipt me-2"></i> View Sales History
                </a>
            </div>
        </div>

        <?php if (Auth::isSamPayAdmin()): ?>
        <div class="content-card">
            <div class="card-header"><i class="bi bi-code-slash me-2"></i>Android API</div>
            <div class="p-3">
                <div class="bg-dark text-light rounded p-3 font-monospace" style="font-size:.73rem;line-height:1.6">
                    <span class="text-success"># Base URL</span><br>
                    <?= rtrim(APP_URL, '/') ?>/api/v1/<br><br>
                    <span class="text-success"># Auth header</span><br>
                    Authorization: Bearer sk_xxx<br><br>
                    <span class="text-success"># Endpoints</span><br>
                    GET  /items<br>
                    POST /sales<br>
                    GET  /sales/{id}
                </div>
                <a href="<?= APP_URL ?>/api-keys" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-key me-1"></i> Manage API Keys
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
