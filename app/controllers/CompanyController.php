<?php

class CompanyController extends Controller
{
    public function edit(): void
    {
        Auth::requireAdmin();

        $db      = Database::getInstance();
        $company = $db->query("SELECT * FROM company LIMIT 1")->fetch() ?: [];
        $errors  = [];

        if ($this->isPost()) {
            $data = [
                'name'          => $this->post('name', ''),
                'tpin'          => $this->post('tpin', ''),
                'address'       => $this->post('address', ''),
                'city'          => $this->post('city', ''),
                'phone'         => $this->post('phone', ''),
                'email'         => $this->post('email', ''),
                'currency_code' => $this->post('currency_code', 'ZMW'),
            ];

            if (empty($data['name'])) $errors['name'] = 'Company name is required.';
            if (empty($data['tpin'])) $errors['tpin'] = 'TPIN is required.';

            if (empty($errors)) {
                if ($company) {
                    $set  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
                    $db->prepare("UPDATE company SET $set WHERE id = ?")
                       ->execute([...array_values($data), $company['id']]);
                } else {
                    $cols = implode(', ', array_keys($data));
                    $vals = implode(', ', array_fill(0, count($data), '?'));
                    $db->prepare("INSERT INTO company ($cols) VALUES ($vals)")
                       ->execute(array_values($data));
                }
                AuditLog::record(Auth::id(), 'UPDATE', 'company', null, 'Company profile updated');
                Flash::success('Company profile saved.');
                $this->redirect('company');
            }
            $company = $data;
        }

        $this->view('company.edit', [
            'pageTitle'  => 'Company Profile',
            'activeMenu' => 'company',
            'company'    => $company,
            'errors'     => $errors,
        ]);
    }
}
