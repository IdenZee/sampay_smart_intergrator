<?php

class SettingsController extends Controller
{
    private Setting $model;

    public function __construct()
    {
        $this->model = new Setting();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $groups = $this->model->allGrouped();

        if ($this->isPost()) {
            $group = $this->post('group', 'general');
            $this->model->saveGroup($_POST, $group, Auth::id());
            AuditLog::record(Auth::id(), 'UPDATE', 'settings', null, "Settings group '{$group}' updated");
            Flash::success('Settings saved.');
            $this->redirect('settings');
        }

        $this->view('settings.index', [
            'pageTitle'  => 'Settings',
            'activeMenu' => 'settings',
            'groups'     => $groups,
        ]);
    }

    public function vsdc(): void
    {
        Auth::requireAdmin();
        $db     = Database::getInstance();
        $config = $db->query("SELECT * FROM vsdc_config LIMIT 1")->fetch() ?: [];

        if ($this->isPost()) {
            $data = [
                'label'           => $this->post('label', 'Main VSDC'),
                'vsdc_url'        => rtrim($this->post('vsdc_url', ''), '/'),
                'device_serial'   => $this->post('device_serial', ''),
                'tax_office_name' => $this->post('tax_office_name', ''),
                'mrc_no'          => $this->post('mrc_no', ''),
                'is_active'       => $this->post('is_active') ? 1 : 0,
            ];

            if ($config) {
                $db->prepare("UPDATE vsdc_config SET label=?, vsdc_url=?, device_serial=?,
                              tax_office_name=?, mrc_no=?, is_active=? WHERE id=?")
                   ->execute([...(array_values($data)), $config['id']]);
            } else {
                $db->prepare("INSERT INTO vsdc_config (label, vsdc_url, device_serial, tax_office_name, mrc_no, is_active)
                              VALUES (?, ?, ?, ?, ?, ?)")
                   ->execute(array_values($data));
            }
            AuditLog::record(Auth::id(), 'UPDATE', 'vsdc_config', null, 'VSDC configuration updated');
            Flash::success('VSDC configuration saved.');
            $this->redirect('settings/vsdc');
        }

        $this->view('settings.vsdc', [
            'pageTitle'  => 'VSDC Configuration',
            'activeMenu' => 'settings',
            'config'     => $config,
        ]);
    }
}
