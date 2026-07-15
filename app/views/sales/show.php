<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold font-monospace"><?= Format::e($sale['sale_ref']) ?></h5>
    <?php if ($sale['is_fiscalised']): ?>
        <span class="badge bg-success fs-6 px-3"><i class="bi bi-shield-check me-1"></i> Fiscalised</span>
    <?php elseif ($sale['vsdc_error']): ?>
        <span class="badge bg-danger fs-6 px-3">VSDC Failed</span>
    <?php else: ?>
        <span class="badge bg-warning text-dark fs-6 px-3">Pending VSDC</span>
    <?php endif; ?>
</div>

<div class="row g-4">
<div class="col-lg-5">

    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-info-circle me-2"></i>Sale Details</div>
        <div class="p-3">
            <table class="table table-sm table-borderless mb-0">
                <tr><td class="text-muted small" width="130">Business</td><td class="fw-medium"><?= Format::e($sale['business_name']) ?></td></tr>
                <tr><td class="text-muted small">Date</td><td><?= Format::date($sale['sale_date']) ?></td></tr>
                <tr><td class="text-muted small">Customer</td><td><?= Format::e($sale['customer_name']) ?></td></tr>
                <tr><td class="text-muted small">TPIN</td><td class="font-monospace small"><?= Format::e($sale['customer_tpin']) ?></td></tr>
                <tr><td class="text-muted small">Payment</td><td><?= Format::e($sale['payment_method']) ?></td></tr>
                <tr><td class="text-muted small">Source</td><td><span class="badge bg-primary"><?= $sale['source'] ?></span></td></tr>
            </table>
        </div>
    </div>

    <?php if ($sale['is_fiscalised']): ?>
    <div class="content-card">
        <div class="card-header text-success"><i class="bi bi-shield-check-fill me-2"></i>ZRA Fiscal Data</div>
        <div class="p-3">
            <table class="table table-sm table-borderless mb-0 small">
                <tr><td class="text-muted" width="110">Receipt No</td><td class="font-monospace fw-bold"><?= Format::e($sale['vsdc_rcpt_no']) ?></td></tr>
                <tr><td class="text-muted">Receipt Date</td><td class="font-monospace"><?= Format::e($sale['vsdc_rcpt_dt']) ?></td></tr>
                <tr><td class="text-muted">Internal Data</td>
                    <td class="font-monospace text-break" style="font-size:.7rem"><?= Format::e($sale['vsdc_intrl_data']) ?></td></tr>
                <tr><td class="text-muted">Receipt Sign</td>
                    <td class="font-monospace text-break" style="font-size:.7rem"><?= Format::e($sale['vsdc_rcpt_sign']) ?></td></tr>
            </table>
        </div>
    </div>
    <?php elseif ($sale['vsdc_error']): ?>
    <div class="alert alert-danger small">
        <strong>VSDC Error:</strong> <?= Format::e($sale['vsdc_error']) ?>
    </div>
    <?php endif; ?>

</div>
<div class="col-lg-7">

    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-list-ul me-2"></i>Line Items</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Tax</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $li): ?>
                <tr>
                    <td>
                        <div class="fw-medium"><?= Format::e($li['item_name']) ?></div>
                        <div class="small text-muted font-monospace"><?= Format::e($li['item_code']) ?></div>
                    </td>
                    <td class="text-center"><?= number_format($li['qty'], 2) ?></td>
                    <td class="text-end"><?= Format::currency($li['unit_price']) ?></td>
                    <td class="text-end text-muted small"><?= Format::currency($li['tax_amount']) ?></td>
                    <td class="text-end fw-medium"><?= Format::currency($li['total_amount']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-semibold">Subtotal</td>
                        <td></td>
                        <td class="text-end"><?= Format::currency($sale['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end text-muted small">VAT</td>
                        <td></td>
                        <td class="text-end text-muted small"><?= Format::currency($sale['tax_amount']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">TOTAL</td>
                        <td></td>
                        <td class="text-end fw-bold fs-5"><?= Format::currency($sale['total_amount']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
</div>
