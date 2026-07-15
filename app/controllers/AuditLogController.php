<?php

class AuditLogController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $log     = new AuditLog();
        $page    = max(1, (int)$this->get('page', 1));
        $perPage = 50;

        // Scope business users to their own business
        $businessId = Auth::businessId();

        $total   = $log->totalCount($businessId);
        $entries = $log->paginate($page, $perPage, $businessId);
        $pages   = (int)ceil($total / $perPage);

        $this->view('audit.index', [
            'pageTitle'  => 'Audit Log',
            'activeMenu' => 'audit-log',
            'entries'    => $entries,
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
        ]);
    }
}
