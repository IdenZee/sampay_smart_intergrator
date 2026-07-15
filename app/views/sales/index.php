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
    <div class="text-muted small">
        <?= number_format($total) ?> sale<?= $total !== 1 ? 's' : '' ?>
        &nbsp;·&nbsp;
        <?= $fiscalised ?> fiscalised
    </div>
</div>

<div class="content-card">
    <div class="card-header"><i class="bi bi-receipt me-2"></i>Sales / Invoices</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Business</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Source</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Tax</th>
                    <th>VSDC</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
            <tr>
                <td class="font-monospace small fw-medium"><?= Format::e($s['sale_ref']) ?></td>
                <td class="small"><?= Format::e($s['business_name']) ?></td>
                <td class="small text-muted"><?= Format::date($s['sale_date']) ?></td>
                <td class="small"><?= Format::e($s['customer_name']) ?></td>
                <td>
                    <?php $src = $s['source'] ?? 'android'; ?>
                    <span class="badge bg-<?= $src === 'android' ? 'primary' : ($src === 'web' ? 'info text-dark' : 'secondary') ?>">
                        <?= $src ?>
                    </span>
                </td>
                <td class="text-end fw-medium"><?= Format::currency($s['total_amount']) ?></td>
                <td class="text-end small text-muted"><?= Format::currency($s['tax_amount']) ?></td>
                <td>
                    <?php if ($s['is_fiscalised']): ?>
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> Fiscal</span>
                    <?php elseif ($s['vsdc_error']): ?>
                        <span class="badge bg-danger" title="<?= Format::e($s['vsdc_error']) ?>">Failed</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= APP_URL ?>/sales/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($sales)): ?>
            <tr><td colspan="9" class="text-center text-muted py-5">
                No sales yet. Sales submitted via the Android POS app will appear here.
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="p-3 d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $filterBusiness ? '&business_id='.$filterBusiness : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
