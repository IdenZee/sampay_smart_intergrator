<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <?php if (Auth::isAdmin()): ?>
    <a href="<?= APP_URL ?>/businesses/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Business
    </a>
    <?php endif; ?>
</div>

<div class="content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-buildings-fill me-2"></i>Registered Businesses (<?= count($businesses) ?>)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Business Name</th>
                    <th>TPIN</th>
                    <th>Branch Code</th>
                    <th>VSDC</th>
                    <th>Items</th>
                    <th>Sales</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($businesses as $b): ?>
            <tr>
                <td>
                    <a href="<?= APP_URL ?>/businesses/<?= $b['id'] ?>" class="fw-semibold text-decoration-none">
                        <?= Format::e($b['name']) ?>
                    </a>
                    <?php if ($b['sampay_business_id']): ?>
                    <div class="small text-muted">SamPay ID: <?= Format::e($b['sampay_business_id']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-muted small"><?= Format::e($b['tpin']) ?></td>
                <td class="small"><?= Format::e($b['branch_code'] ?? '—') ?></td>
                <td>
                    <?php if ($b['initialized']): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Initialised</span>
                    <?php elseif ($b['vsdc_url']): ?>
                        <span class="badge bg-warning text-dark">Not Initialised</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No VSDC</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= number_format($b['item_count']) ?></td>
                <td class="text-center"><?= number_format($b['sale_count']) ?></td>
                <td><?= Format::statusBadge($b['is_active']) ?></td>
                <td class="text-end">
                    <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/businesses/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/businesses/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary ms-1">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($businesses)): ?>
            <tr><td colspan="8" class="text-center text-muted py-5">
                No businesses yet. <a href="<?= APP_URL ?>/businesses/create">Add the first one.</a>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
