<div class="d-flex justify-content-between align-items-center mb-4">
    <!-- Business filter -->
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

    <div class="d-flex gap-2">
        <?php if ($filterBusiness && Auth::isAdmin()): ?>
        <a href="<?= APP_URL ?>/items/register-all/<?= $filterBusiness ?>"
           class="btn btn-sm btn-outline-warning"
           onclick="return confirm('Register all unregistered items with VSDC?')">
            <i class="bi bi-lightning-charge-fill me-1"></i> Register All with VSDC
        </a>
        <?php endif; ?>
        <?php if (Auth::isAny(['admin', 'manager'])): ?>
        <a href="<?= APP_URL ?>/items/create<?= $filterBusiness ? '?business_id='.$filterBusiness : '' ?>"
           class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Item
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <i class="bi bi-box-seam-fill me-2"></i>
        Items / Stock Catalogue (<?= count($items) ?>)
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Code</th>
                    <th>Business</th>
                    <th>Class</th>
                    <th>Tax</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>VSDC</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td class="fw-medium"><?= Format::e($item['item_name']) ?></td>
                <td class="small font-monospace text-muted"><?= Format::e($item['item_code']) ?></td>
                <td class="small"><?= Format::e($item['business_name']) ?></td>
                <td class="small font-monospace"><?= Format::e($item['item_cls_code']) ?></td>
                <td>
                    <span class="badge bg-light text-dark border"><?= Format::e($item['tax_ty_cd']) ?></span>
                </td>
                <td class="fw-medium"><?= Format::currency($item['selling_price']) ?></td>
                <td class="text-center"><?= number_format($item['stock_qty'], 1) ?></td>
                <td>
                    <?php if ($item['vsdc_registered']): ?>
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> Registered</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if (!$item['vsdc_registered'] && Auth::isAny(['admin','manager'])): ?>
                    <a href="<?= APP_URL ?>/items/register-vsdc/<?= $item['id'] ?>"
                       class="btn btn-xs btn-outline-warning btn-sm"
                       title="Register with VSDC">
                        <i class="bi bi-lightning-charge"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::isAny(['admin','manager'])): ?>
                    <a href="<?= APP_URL ?>/items/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary ms-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <tr><td colspan="9" class="text-center text-muted py-5">
                No items found.
                <?php if (Auth::isAny(['admin','manager'])): ?>
                <a href="<?= APP_URL ?>/items/create">Add your first item.</a>
                <?php endif; ?>
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
