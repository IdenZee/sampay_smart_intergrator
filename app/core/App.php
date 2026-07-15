<?php

class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    public function run(): void
    {
        $url        = $_GET['url'] ?? '';
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $this->router->dispatch($url, $httpMethod);
    }

    private function registerRoutes(): void
    {
        $r = $this->router;

        // Auth
        $r->any('',                          'AuthController', 'login');
        $r->any('login',                     'AuthController', 'login');
        $r->get('logout',                    'AuthController', 'logout');
        $r->any('forgot-password',           'AuthController', 'forgotPassword');
        $r->any('reset-password/{token}',    'AuthController', 'resetPassword');

        // Dashboard
        $r->get('dashboard',                 'DashboardController', 'index');

        // Businesses
        $r->get('businesses',                'BusinessesController', 'index');
        $r->any('businesses/create',         'BusinessesController', 'create');
        $r->any('businesses/edit/{id}',      'BusinessesController', 'edit');
        $r->get('businesses/{id}',           'BusinessesController', 'show');
        $r->post('businesses/delete/{id}',   'BusinessesController', 'delete');

        // VSDC Onboarding
        $r->get('vsdc/dashboard/{id}',       'VsdcController', 'dashboard');
        $r->get('vsdc/init/{id}',            'VsdcController', 'initDevice');
        $r->get('vsdc/codes/{id}',           'VsdcController', 'fetchCodes');
        $r->get('vsdc/item-classes/{id}',    'VsdcController', 'fetchItemClasses');
        $r->get('vsdc/register-item/{id}',   'VsdcController', 'registerItem');
        $r->get('vsdc/register-all/{id}',    'VsdcController', 'registerAllItems');

        // Items / Stock
        $r->get('items',                     'ItemsController', 'index');
        $r->any('items/create',              'ItemsController', 'create');
        $r->any('items/edit/{id}',           'ItemsController', 'edit');
        $r->post('items/delete/{id}',        'ItemsController', 'delete');
        $r->get('items/register-vsdc/{id}',  'ItemsController', 'registerVsdc');
        $r->get('items/register-all/{biz}',  'ItemsController', 'registerAllVsdc');

        // Sales
        $r->get('sales',                     'SalesController', 'index');
        $r->get('sales/{id}',                'SalesController', 'show');

        // API Keys
        $r->get('api-keys',                  'ApiKeysController', 'index');
        $r->any('api-keys/create',           'ApiKeysController', 'create');
        $r->post('api-keys/revoke/{id}',     'ApiKeysController', 'revoke');

        // Users
        $r->get('users',                     'UsersController', 'index');
        $r->any('users/create',              'UsersController', 'create');
        $r->any('users/edit/{id}',           'UsersController', 'edit');
        $r->post('users/delete/{id}',        'UsersController', 'delete');
        $r->any('users/reset-password/{id}', 'UsersController', 'resetPassword');

        // Settings
        $r->any('settings',                  'SettingsController', 'index');
        $r->any('settings/vsdc',             'SettingsController', 'vsdc');

        // Audit Log
        $r->get('audit-log',                 'AuditLogController', 'index');

        // Profile
        $r->any('profile',                   'ProfileController', 'edit');

        // REST API - Android POS (API key bearer token auth)
        $r->get('api/v1/ping',               'ApiController', 'ping');
        $r->get('api/v1/items',              'ApiController', 'items');
        $r->post('api/v1/sales',             'ApiController', 'submitSale');
        $r->get('api/v1/sales/{id}',         'ApiController', 'getReceipt');
    }
}
