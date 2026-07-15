<div class="row justify-content-center">
<div class="col-xl-9">
<div class="content-card">
    <div class="card-header">
        <i class="bi bi-box-seam-fill me-2"></i>
        <?= $item ? 'Edit Item' : 'New Item' ?>
    </div>
    <div class="p-4">
        <form method="POST">
        <div class="row g-3">

            <!-- Business -->
            <?php if (!empty($businesses)): ?>
            <div class="col-md-6">
                <label class="form-label fw-medium">Business <span class="text-danger">*</span></label>
                <select name="business_id" id="businessSelect"
                        class="form-select <?= isset($errors['business_id']) ? 'is-invalid' : '' ?>"
                        onchange="if(this.value) window.location.href='<?= APP_URL ?>/items/<?= $item ? 'edit/'.$item['id'] : 'create' ?>?business_id='+this.value"
                        required>
                    <option value="">— Select Business —</option>
                    <?php foreach ($businesses as $b): ?>
                    <option value="<?= $b['id'] ?>"
                        <?= (int)($item['business_id'] ?? $preselect) === (int)$b['id'] ? 'selected' : '' ?>>
                        <?= Format::e($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['business_id'])): ?><div class="invalid-feedback"><?= $errors['business_id'] ?></div><?php endif; ?>
                <?php if (empty($itemClasses) && (int)($item['business_id'] ?? $preselect)): ?>
                <div class="form-text">
                    <a href="<?= APP_URL ?>/vsdc/dashboard/<?= (int)($item['business_id'] ?? $preselect) ?>">
                        <i class="bi bi-lightning-charge me-1"></i>Run VSDC setup for this business first
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <!-- Business user: hidden field, business is set automatically -->
            <input type="hidden" name="business_id" value="<?= (int)($item['business_id'] ?? $preselect) ?>">
            <?php endif; ?>

            <!-- Item Code -->
            <div class="col-md-3">
                <label class="form-label fw-medium">Item Code <span class="text-danger">*</span></label>
                <input type="text" name="item_code" class="form-control font-monospace <?= isset($errors['item_code']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($item['item_code'] ?? '') ?>" required placeholder="e.g. FUEL-PMS">
                <?php if (isset($errors['item_code'])): ?><div class="invalid-feedback"><?= $errors['item_code'] ?></div><?php endif; ?>
            </div>

            <!-- Barcode -->
            <div class="col-md-3">
                <label class="form-label fw-medium">Barcode (BCD)</label>
                <input type="text" name="bcd" class="form-control font-monospace"
                       value="<?= Format::e($item['bcd'] ?? '') ?>" placeholder="Optional">
            </div>

            <!-- Item Name -->
            <div class="col-12">
                <label class="form-label fw-medium">Item Name <span class="text-danger">*</span></label>
                <input type="text" name="item_name" class="form-control <?= isset($errors['item_name']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($item['item_name'] ?? '') ?>" required>
                <?php if (isset($errors['item_name'])): ?><div class="invalid-feedback"><?= $errors['item_name'] ?></div><?php endif; ?>
            </div>

            <!-- ZRA Fields -->
            <div class="col-12"><hr class="my-1"><h6 class="fw-semibold text-muted small text-uppercase">ZRA VSDC Fields</h6></div>

            <div class="col-md-4">
                <label class="form-label fw-medium">
                    Item Class Code <span class="text-danger">*</span>
                </label>
                <?php if (!empty($itemClasses)): ?>
                <select name="item_cls_code" id="itemClsCode"
                        class="form-select font-monospace <?= isset($errors['item_cls_code']) ? 'is-invalid' : '' ?>"
                        required>
                    <option value="">— Search class —</option>
                    <?php foreach ($itemClasses as $cls): ?>
                    <option value="<?= Format::e($cls['cls_code']) ?>"
                        <?= ($item['item_cls_code'] ?? '') === $cls['cls_code'] ? 'selected' : '' ?>>
                        <?= Format::e($cls['cls_code']) ?> — <?= Format::e($cls['cls_name']) ?>
                        <?= $cls['tax_ty_cd'] ? '(' . $cls['tax_ty_cd'] . ')' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= count($itemClasses) ?> classes cached from ZRA VSDC.</div>
                <?php else: ?>
                <input type="text" name="item_cls_code" id="itemClsCode"
                       class="form-control font-monospace <?= isset($errors['item_cls_code']) ? 'is-invalid' : '' ?>"
                       value="<?= Format::e($item['item_cls_code'] ?? '') ?>"
                       placeholder="e.g. 27101210" required>
                <div class="form-text text-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Item classes not cached.
                    <?php if ((int)($item['business_id'] ?? $preselect)): ?>
                    <a href="<?= APP_URL ?>/vsdc/dashboard/<?= (int)($item['business_id'] ?? $preselect) ?>">
                        Go to VSDC setup
                    </a> to fetch them.
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (isset($errors['item_cls_code'])): ?><div class="invalid-feedback"><?= $errors['item_cls_code'] ?></div><?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Tax Type <span class="text-danger">*</span></label>
                <select name="tax_ty_cd" class="form-select">
                    <?php foreach ($taxTypes as $code => $label): ?>
                    <option value="<?= $code ?>" <?= ($item['tax_ty_cd'] ?? 'A') === $code ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Origin Code</label>
                <input type="text" name="orgin_natrs_cd" class="form-control font-monospace"
                       value="<?= Format::e($item['orgin_natrs_cd'] ?? 'ZM') ?>" placeholder="ZM">
                <div class="form-text">Country of origin (ISO 2-letter). Default: ZM</div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Quantity Unit</label>
                <select name="qty_unit_cd" class="form-select">
                    <?php foreach ($qtyUnits as $code => $label): ?>
                    <option value="<?= $code ?>" <?= ($item['qty_unit_cd'] ?? 'U') === $code ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Package Unit</label>
                <select name="pkg_unit_cd" class="form-select">
                    <?php foreach ($pkgUnits as $code => $label): ?>
                    <option value="<?= $code ?>" <?= ($item['pkg_unit_cd'] ?? 'NT') === $code ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Batch No</label>
                <input type="text" name="btch_no" class="form-control"
                       value="<?= Format::e($item['btch_no'] ?? '') ?>" placeholder="Optional">
            </div>

            <!-- Pricing & Stock -->
            <div class="col-12"><hr class="my-1"><h6 class="fw-semibold text-muted small text-uppercase">Pricing &amp; Stock</h6></div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Selling Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">ZMW</span>
                    <input type="number" name="selling_price" step="0.01" min="0"
                           class="form-control <?= isset($errors['selling_price']) ? 'is-invalid' : '' ?>"
                           value="<?= number_format((float)($item['selling_price'] ?? 0), 2, '.', '') ?>" required>
                </div>
                <?php if (isset($errors['selling_price'])): ?><div class="text-danger small mt-1"><?= $errors['selling_price'] ?></div><?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Opening Stock Qty</label>
                <input type="number" name="stock_qty" step="0.001" min="0"
                       class="form-control"
                       value="<?= number_format((float)($item['stock_qty'] ?? 0), 3, '.', '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-medium">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= ($item['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !($item['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Description</label>
                <textarea name="description" class="form-control" rows="2"><?= Format::e($item['description'] ?? '') ?></textarea>
            </div>

            <?php if ($item && $item['vsdc_registered']): ?>
            <div class="col-12">
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    This item is registered with ZRA VSDC. Changing the price will require re-registration.
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $item ? 'Save Changes' : 'Create Item' ?>
            </button>
            <a href="<?= APP_URL ?>/items" class="btn btn-outline-secondary">Cancel</a>
        </div>

        </form>
    </div>
</div>
</div>
</div>
