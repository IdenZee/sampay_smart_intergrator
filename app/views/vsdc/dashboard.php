<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/businesses/<?= $business['id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-bold"><?= Format::e($business['name']) ?> — VSDC Setup</h5>
        <div class="small text-muted">TPIN: <?= Format::e($business['tpin']) ?> &nbsp;·&nbsp; <?= Format::e($business['vsdc_url'] ?? 'No VSDC URL') ?></div>
    </div>
</div>

<!-- Progress bar -->
<?php
$step1 = (bool)$business['initialized'];
$step2 = $business['last_std_codes'] ? true : false;
$step3 = count($classes) > 0;
$step4 = $totalItems > 0 && $unregistered === 0;
$done  = array_sum([$step1, $step2, $step3, $step4]);
?>
<div class="content-card mb-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold">Onboarding Progress</span>
        <span class="small text-muted"><?= $done ?> / 4 steps complete</span>
    </div>
    <div class="progress" style="height:8px">
        <div class="progress-bar bg-success" style="width:<?= ($done / 4) * 100 ?>%"></div>
    </div>
    <?php if ($done === 4): ?>
    <div class="alert alert-success mb-0 mt-3 py-2 small">
        <i class="bi bi-check-circle-fill me-2"></i>
        Business is fully onboarded and ready for the Android POS app.
    </div>
    <?php endif; ?>
</div>

<div class="row g-4">

<!-- Steps column -->
<div class="col-lg-7">

    <!-- Step 1 -->
    <div class="content-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <span class="badge <?= $step1 ? 'bg-success' : 'bg-secondary' ?> me-2">Step 1</span>
                Initialise VSDC Device
            </span>
            <?= $step1
                ? '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Done</span>'
                : '<span class="badge bg-warning text-dark">Required</span>' ?>
        </div>
        <div class="p-3">
            <p class="small text-muted mb-3">
                Registers this device with ZRA VSDC. Device serial and TPIN must be correct.
                Standard codes and item classes are fetched automatically on success.
            </p>
            <?php if ($step1): ?>
                <div class="small text-muted">
                    <i class="bi bi-clock me-1"></i>Initialised <?= Format::datetime($business['initialized_at']) ?>
                </div>
                <?php if (Auth::isAdmin()): ?>
                <a href="<?= APP_URL ?>/vsdc/init/<?= $business['id'] ?>"
                   class="btn btn-sm btn-outline-secondary mt-2"
                   onclick="return confirm('Re-initialise? Only do this if VSDC device details changed.')">
                    <i class="bi bi-arrow-repeat me-1"></i> Re-initialise
                </a>
                <?php endif; ?>
            <?php elseif ($business['vsdc_url']): ?>
                <?php if (Auth::isAdmin()): ?>
                <a href="<?= APP_URL ?>/vsdc/init/<?= $business['id'] ?>"
                   class="btn btn-warning"
                   onclick="return confirm('Initialise this device with ZRA VSDC?')">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Initialise Device
                </a>
                <?php else: ?>
                <span class="small text-muted">Admin access required.</span>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    No VSDC URL configured.
                    <a href="<?= APP_URL ?>/businesses/edit/<?= $business['id'] ?>">Edit business</a> to add it.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 2 -->
    <div class="content-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <span class="badge <?= $step2 ? 'bg-success' : 'bg-secondary' ?> me-2">Step 2</span>
                Fetch Standard Codes
            </span>
            <?= $step2
                ? '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Done</span>'
                : '<span class="badge bg-secondary">Pending</span>' ?>
        </div>
        <div class="p-3">
            <p class="small text-muted mb-3">
                Downloads ZRA reference codes (payment types, tax types, currency codes, etc.)
                and caches them for use during sales.
            </p>
            <?php if ($step2): ?>
                <div class="small text-muted">
                    <i class="bi bi-clock me-1"></i>Last fetched <?= Format::datetime($business['last_std_codes']) ?>
                </div>
            <?php endif; ?>
            <?php if ($step1 && Auth::isAdmin()): ?>
            <a href="<?= APP_URL ?>/vsdc/codes/<?= $business['id'] ?>"
               class="btn btn-sm <?= $step2 ? 'btn-outline-secondary' : 'btn-primary' ?> mt-2">
                <i class="bi bi-arrow-repeat me-1"></i>
                <?= $step2 ? 'Refresh Codes' : 'Fetch Codes' ?>
            </a>
            <?php elseif (!$step1): ?>
                <span class="small text-muted">Complete Step 1 first.</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 3 -->
    <div class="content-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <span class="badge <?= $step3 ? 'bg-success' : 'bg-secondary' ?> me-2">Step 3</span>
                Fetch Item Class List
            </span>
            <?= $step3
                ? '<span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>' . count($classes) . ' classes</span>'
                : '<span class="badge bg-secondary">Pending</span>' ?>
        </div>
        <div class="p-3">
            <p class="small text-muted mb-3">
                Downloads ZRA commodity classification codes (HS codes). These are required
                when registering items and will appear as a searchable dropdown on the item form.
            </p>
            <?php if ($step1 && Auth::isAdmin()): ?>
            <a href="<?= APP_URL ?>/vsdc/item-classes/<?= $business['id'] ?>"
               class="btn btn-sm <?= $step3 ? 'btn-outline-secondary' : 'btn-primary' ?> me-2">
                <i class="bi bi-arrow-repeat me-1"></i>
                <?= $step3 ? 'Refresh Classes' : 'Fetch Item Classes' ?>
            </a>
            <?php elseif (!$step1): ?>
                <span class="small text-muted">Complete Step 1 first.</span>
            <?php endif; ?>
            <?php if ($step3): ?>
            <a href="<?= APP_URL ?>/items/create?business_id=<?= $business['id'] ?>"
               class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 4 -->
    <div class="content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <span class="badge <?= $step4 ? 'bg-success' : 'bg-secondary' ?> me-2">Step 4</span>
                Register Items with ZRA VSDC
            </span>
            <span class="badge <?= $unregistered === 0 && $totalItems > 0 ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= $registered ?> / <?= $totalItems ?> registered
            </span>
        </div>
        <div class="p-3">
            <p class="small text-muted mb-3">
                Each item in the catalogue must be registered with ZRA VSDC before it can appear
                on a fiscalised receipt. New items or price changes require re-registration.
            </p>

            <?php if ($unregistered > 0 && Auth::isAdmin()): ?>
            <a href="<?= APP_URL ?>/vsdc/register-all/<?= $business['id'] ?>"
               class="btn btn-warning me-2"
               onclick="return confirm('Register all <?= $unregistered ?> pending item(s) with ZRA VSDC?')">
                <i class="bi bi-lightning-charge-fill me-1"></i>
                Register All (<?= $unregistered ?> pending)
            </a>
            <?php elseif ($totalItems === 0): ?>
            <a href="<?= APP_URL ?>/items/create?business_id=<?= $business['id'] ?>"
               class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add First Item
            </a>
            <?php else: ?>
            <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i>All items registered.</span>
            <?php endif; ?>

            <?php if (!empty($pendingItems)): ?>
            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Item</th><th>Code</th><th>Class</th><th>Price</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($pendingItems as $item): ?>
                    <tr>
                        <td class="small fw-medium"><?= Format::e($item['item_name']) ?></td>
                        <td class="small font-monospace text-muted"><?= Format::e($item['item_code']) ?></td>
                        <td class="small font-monospace"><?= Format::e($item['item_cls_code']) ?></td>
                        <td class="small"><?= Format::currency($item['selling_price']) ?></td>
                        <td>
                            <?php if (Auth::isAny(['admin','manager'])): ?>
                            <a href="<?= APP_URL ?>/vsdc/register-item/<?= $item['id'] ?>"
                               class="btn btn-xs btn-outline-warning btn-sm">
                                <i class="bi bi-lightning-charge"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Right: item classes reference -->
<div class="col-lg-5">

    <div class="content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-columns-reverse me-2"></i>Cached Item Classes</span>
            <span class="badge bg-secondary"><?= count($classes) ?></span>
        </div>
        <?php if ($classes): ?>
        <div style="max-height:520px;overflow-y:auto">
            <table class="table table-sm table-hover mb-0">
                <thead class="sticky-top bg-white">
                    <tr><th>Code</th><th>Name</th><th>Tax</th></tr>
                </thead>
                <tbody>
                <?php foreach ($classes as $c): ?>
                <tr>
                    <td class="font-monospace small fw-bold"><?= Format::e($c['cls_code']) ?></td>
                    <td class="small"><?= Format::e($c['cls_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= Format::e($c['tax_ty_cd'] ?? '—') ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-4 text-center text-muted">
            <i class="bi bi-list-columns display-6 d-block mb-2 opacity-25"></i>
            No item classes yet.<br>
            <span class="small">Complete Steps 1–3 to fetch them from ZRA VSDC.</span>
        </div>
        <?php endif; ?>
    </div>

</div>

</div>
