<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — <?= $pageTitle ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-bg: #1a1a2e;
            --sidebar-width: 250px;
            --accent: #f0a500;
        }
        body { background: #f4f6fb; font-size: 0.9rem; }

        /* ── Sidebar ─────────────────────────────────────────────────── */
        #sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand .brand-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .sidebar-brand .brand-name span { color: var(--accent); }
        .sidebar-brand .brand-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nav-section {
            padding: 0.75rem 1rem 0.25rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.3);
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 0.55rem 1.5rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all .15s;
            font-size: 0.875rem;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(240,165,0,0.12);
            border-left: 3px solid var(--accent);
        }
        #sidebar .nav-link i { font-size: 1rem; width: 18px; }

        /* ── Main content ────────────────────────────────────────────── */
        #main { margin-left: var(--sidebar-width); }

        /* ── Topbar ──────────────────────────────────────────────────── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar .page-title { font-size: 1.05rem; font-weight: 600; color: #1a1a2e; }
        .topbar .user-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #495057;
        }
        .user-avatar {
            width: 32px; height: 32px;
            background: var(--sidebar-bg);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 600;
        }

        /* ── Cards / stats ───────────────────────────────────────────── */
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            border: 1px solid #e9ecef;
        }
        .stat-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; }
        .stat-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }

        .content-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        .content-card .card-header {
            background: none;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: #1a1a2e;
        }

        /* ── Table ───────────────────────────────────────────────────── */
        .table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>

<!-- ══ Sidebar ═══════════════════════════════════════════════════════════ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="bi bi-lightning-charge-fill me-1"></i>SamPay<span> Integrator</span></div>
        <div class="brand-sub">ZRA Smart Invoice Gateway</div>
    </div>

    <ul class="nav flex-column mt-2">

        <li><a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/dashboard">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a></li>

        <?php if (Auth::isSamPayAdmin()): ?>
        <!-- ── SamPay Admin sidebar ── -->
        <div class="nav-section">Businesses</div>
        <li><a class="nav-link <?= $activeMenu === 'businesses' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/businesses">
            <i class="bi bi-buildings-fill"></i> All Businesses
        </a></li>

        <div class="nav-section">Stock &amp; Sales</div>
        <li><a class="nav-link <?= $activeMenu === 'items' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/items">
            <i class="bi bi-box-seam-fill"></i> Items / Stock
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'sales' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/sales">
            <i class="bi bi-receipt"></i> Sales / Invoices
        </a></li>

        <div class="nav-section">Android POS</div>
        <li><a class="nav-link <?= $activeMenu === 'api-keys' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/api-keys">
            <i class="bi bi-phone-fill"></i> API Keys
        </a></li>

        <div class="nav-section">System</div>
        <li><a class="nav-link <?= $activeMenu === 'users' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/users">
            <i class="bi bi-people-fill"></i> Users
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'settings' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/settings">
            <i class="bi bi-gear-fill"></i> Settings
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'audit-log' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/audit-log">
            <i class="bi bi-journal-text"></i> Audit Log
        </a></li>

        <?php elseif (Auth::isBusinessAdmin()): ?>
        <!-- ── Business Admin sidebar ── -->
        <div class="nav-section">My Business</div>
        <li><a class="nav-link <?= $activeMenu === 'my-business' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/businesses/<?= Auth::businessId() ?>">
            <i class="bi bi-buildings-fill"></i> Business Profile
        </a></li>

        <div class="nav-section">Stock &amp; Sales</div>
        <li><a class="nav-link <?= $activeMenu === 'items' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/items">
            <i class="bi bi-box-seam-fill"></i> Items / Stock
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'sales' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/sales">
            <i class="bi bi-receipt"></i> Sales / Invoices
        </a></li>

        <div class="nav-section">System</div>
        <li><a class="nav-link <?= $activeMenu === 'users' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/users">
            <i class="bi bi-people-fill"></i> Users
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'audit-log' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/audit-log">
            <i class="bi bi-journal-text"></i> Audit Log
        </a></li>

        <?php else: ?>
        <!-- ── Business User (view-only) sidebar ── -->
        <div class="nav-section">Stock &amp; Sales</div>
        <li><a class="nav-link <?= $activeMenu === 'items' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/items">
            <i class="bi bi-box-seam-fill"></i> Items / Stock
        </a></li>
        <li><a class="nav-link <?= $activeMenu === 'sales' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/sales">
            <i class="bi bi-receipt"></i> Sales / Invoices
        </a></li>
        <?php endif; ?>

    </ul>

    <!-- Sidebar footer -->
    <div style="position:absolute; bottom:0; width:100%; padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,.08);">
        <a href="<?= APP_URL ?>/profile" class="d-flex align-items-center gap-2 text-decoration-none mb-2">
            <div class="user-avatar">
                <?= strtoupper(substr(Auth::user()['name'], 0, 2)) ?>
            </div>
            <div>
                <div style="color:#fff;font-size:.8rem;font-weight:600; line-height:1.2;">
                    <?= Format::e(Auth::user()['name']) ?>
                </div>
                <div style="color:rgba(255,255,255,.4);font-size:.7rem;">
                    <?= ucfirst(Auth::role()) ?>
                </div>
            </div>
        </a>
        <a href="<?= APP_URL ?>/logout" class="nav-link text-danger" style="padding:.35rem 0;">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</nav>

<!-- ══ Main ══════════════════════════════════════════════════════════════ -->
<div id="main">
    <!-- Topbar -->
    <div class="topbar">
        <div class="page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div class="user-chip">
            <i class="bi bi-bell text-muted"></i>
            <div class="user-avatar ms-2">
                <?= strtoupper(substr(Auth::user()['name'], 0, 2)) ?>
            </div>
            <span><?= Format::e(Auth::user()['name']) ?></span>
        </div>
    </div>

    <!-- Flash message -->
    <div class="px-4 pt-3">
        <?= Flash::render() ?>
    </div>

    <!-- Page content -->
    <div class="p-4">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
