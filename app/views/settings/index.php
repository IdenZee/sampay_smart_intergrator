<ul class="nav nav-tabs mb-4" id="settingsTabs">
    <?php $i = 0; foreach ($groups as $group => $items): $i++; ?>
    <li class="nav-item">
        <a class="nav-link <?= $i === 1 ? 'active' : '' ?>"
           data-bs-toggle="tab" href="#tab-<?= $group ?>"><?= ucfirst($group) ?></a>
    </li>
    <?php endforeach; ?>
    <li class="nav-item ms-auto">
        <a class="nav-link" href="<?= APP_URL ?>/settings/vsdc">
            <i class="bi bi-plug-fill me-1"></i>VSDC Config
        </a>
    </li>
</ul>

<div class="tab-content">
    <?php $i = 0; foreach ($groups as $group => $items): $i++; ?>
    <div class="tab-pane fade <?= $i === 1 ? 'show active' : '' ?>" id="tab-<?= $group ?>">
        <div class="content-card">
            <div class="card-header"><?= ucfirst($group) ?> Settings</div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="group" value="<?= $group ?>">
                    <div class="row g-3">
                    <?php foreach ($items as $s): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-medium"><?= Format::e($s['display_name'] ?? $s['setting_key']) ?></label>
                            <?php if (in_array($s['setting_value'], ['0','1']) && str_ends_with($s['setting_key'], '_enabled')): ?>
                                <select name="<?= $s['setting_key'] ?>" class="form-select">
                                    <option value="1" <?= $s['setting_value'] === '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= $s['setting_value'] === '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            <?php elseif ($s['setting_key'] === 'pricing_mode'): ?>
                                <select name="<?= $s['setting_key'] ?>" class="form-select">
                                    <option value="hq"     <?= $s['setting_value'] === 'hq'     ? 'selected' : '' ?>>HQ (company-wide price)</option>
                                    <option value="branch" <?= $s['setting_value'] === 'branch' ? 'selected' : '' ?>>Branch (per-branch override)</option>
                                </select>
                            <?php else: ?>
                                <input type="text" name="<?= $s['setting_key'] ?>" class="form-control"
                                       value="<?= Format::e($s['setting_value'] ?? '') ?>">
                            <?php endif; ?>
                            <?php if ($s['description']): ?>
                                <div class="form-text"><?= Format::e($s['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">
                        <i class="bi bi-check-lg me-1"></i> Save <?= ucfirst($group) ?> Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
