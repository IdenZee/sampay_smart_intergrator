<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $db    = Database::getInstance();
        $bizId = Auth::businessId();

        if ($bizId) {
            // Business-scoped dashboard
            $stats = [
                'items'        => (int)$db->query("SELECT COUNT(*) FROM items WHERE business_id = $bizId AND is_active = 1")->fetchColumn(),
                'sales_today'  => (int)$db->query("SELECT COUNT(*) FROM sales WHERE business_id = $bizId AND DATE(created_at) = CURDATE()")->fetchColumn(),
                'fiscalised'   => (int)$db->query("SELECT COUNT(*) FROM sales WHERE business_id = $bizId AND is_fiscalised = 1")->fetchColumn(),
                'pending_vsdc' => (int)$db->query("SELECT COUNT(*) FROM items WHERE business_id = $bizId AND vsdc_registered = 0 AND is_active = 1")->fetchColumn(),
            ];

            $recentSales = $db->query(
                "SELECT s.sale_ref, s.total_amount, s.is_fiscalised, s.created_at
                 FROM sales s
                 WHERE s.business_id = $bizId
                 ORDER BY s.created_at DESC LIMIT 5"
            )->fetchAll();
        } else {
            // SamPay admin — global view
            $stats = [
                'businesses'   => (int)$db->query("SELECT COUNT(*) FROM businesses WHERE is_active = 1")->fetchColumn(),
                'users'        => (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
                'items'        => (int)$db->query("SELECT COUNT(*) FROM items WHERE is_active = 1")->fetchColumn(),
                'sales_today'  => (int)$db->query("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                'fiscalised'   => (int)$db->query("SELECT COUNT(*) FROM sales WHERE is_fiscalised = 1")->fetchColumn(),
                'pending_vsdc' => (int)$db->query("SELECT COUNT(*) FROM items WHERE vsdc_registered = 0 AND is_active = 1")->fetchColumn(),
            ];

            $recentSales = $db->query(
                "SELECT s.sale_ref, s.total_amount, s.is_fiscalised, s.created_at, b.name AS business_name
                 FROM sales s
                 JOIN businesses b ON b.id = s.business_id
                 ORDER BY s.created_at DESC LIMIT 5"
            )->fetchAll();
        }

        $this->view('dashboard.index', [
            'pageTitle'   => 'Dashboard',
            'activeMenu'  => 'dashboard',
            'stats'       => $stats,
            'recentSales' => $recentSales,
            'isBusiness'  => (bool)$bizId,
        ]);
    }
}
