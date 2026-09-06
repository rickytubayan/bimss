<?php
class App {
    private $router;

    public function __construct() {
        Session::start();
        $this->router = new Router();
        $this->loadRoutes();
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'];
        $this->router->dispatch($method, $url);
    }

    private function loadRoutes() {
        $r = $this->router;

        // Public routes
        $r->group('', ['CSRFMiddleware'], function ($r) {
            $r->get('', 'Public\HomeController@index');
            $r->get('/', 'Public\HomeController@index');
            $r->get('home', 'Public\HomeController@index');
            $r->get('api/language/set', 'Public\LanguageController@set');

            $r->group('auth', [], function ($r) {
                $r->get('login', 'Public\AuthController@showLogin');
                $r->post('login', 'Public\AuthController@login');
                $r->get('otp', 'Public\AuthController@showOTP');
                $r->post('otp', 'Public\AuthController@verifyOTP');
                $r->get('register', 'Public\AuthController@showRegister');
                $r->post('register', 'Public\AuthController@register');
                $r->get('logout', 'Public\AuthController@logout');
            });

            $r->group('public', ['AuthMiddleware'], function ($r) {
                $r->get('dashboard', 'Public\HomeController@dashboard');
                $r->get('profile', 'Public\ProfileController@index');
                $r->get('profile/qr', 'Public\ProfileController@qrCode');
                $r->get('documents', 'Public\DocumentRequestController@index');
                $r->post('documents/request', 'Public\DocumentRequestController@request');
                $r->get('documents/track/{tracking_code}', 'Public\DocumentRequestController@track');
                $r->get('appointments', 'Public\AppointmentController@index');
                $r->post('appointments/book', 'Public\AppointmentController@book');
                $r->get('complaints', 'Public\ComplaintController@index');
                $r->post('complaints/submit', 'Public\ComplaintController@submit');
                $r->get('blotter', 'Public\BlotterController@index');
                $r->post('blotter/submit', 'Public\BlotterController@submit');
                $r->get('bulletin', 'Public\BulletinController@index');
                $r->get('map', 'Public\MapController@index');
                $r->get('transparency', 'Public\TransparencyController@index');
            });
        });

        // Admin routes
        $r->group('admin', ['AuthMiddleware', 'RBACMiddleware'], function ($r) {
            $r->get('', 'Admin\DashboardController@index');
            $r->get('dashboard', 'Admin\DashboardController@index');

            $r->get('residents', 'Admin\ResidentController@index');
            $r->get('residents/create', 'Admin\ResidentController@create');
            $r->post('residents/store', 'Admin\ResidentController@store');
            $r->get('residents/{id}', 'Admin\ResidentController@show');
            $r->get('residents/{id}/edit', 'Admin\ResidentController@edit');
            $r->post('residents/{id}/update', 'Admin\ResidentController@update');
            $r->post('residents/{id}/delete', 'Admin\ResidentController@delete');

            $r->get('households', 'Admin\HouseholdController@index');
            $r->get('households/create', 'Admin\HouseholdController@create');
            $r->post('households/store', 'Admin\HouseholdController@store');
            $r->get('households/{id}', 'Admin\HouseholdController@show');
            $r->get('households/{id}/edit', 'Admin\HouseholdController@edit');
            $r->post('households/{id}/update', 'Admin\HouseholdController@update');
            $r->post('households/{id}/delete', 'Admin\HouseholdController@delete');

            $r->get('clearances', 'Admin\ClearanceController@index');
            $r->get('clearances/{id}', 'Admin\ClearanceController@show');
            $r->post('clearances/process/{id}', 'Admin\ClearanceController@process');
            $r->post('clearances/sign/{id}', 'Admin\ClearanceController@sign');
            $r->post('clearances/release/{id}', 'Admin\ClearanceController@release');

            $r->get('certificates', 'Admin\CertificateController@index');
            $r->get('certificates/create', 'Admin\CertificateController@create');
            $r->post('certificates/store', 'Admin\CertificateController@store');
            $r->post('certificates/sign/{id}', 'Admin\CertificateController@sign');

            $r->get('appointments', 'Admin\AppointmentController@index');
            $r->get('appointments/{id}', 'Admin\AppointmentController@show');
            $r->post('appointments/{id}/complete', 'Admin\AppointmentController@complete');
            $r->post('appointments/{id}/cancel', 'Admin\AppointmentController@cancel');
            $r->get('appointments/slots', 'Admin\AppointmentController@slots');
            $r->post('appointments/slots/store', 'Admin\AppointmentController@storeSlot');

            $r->get('finance', 'Admin\FinanceController@index');
            $r->get('finance/income', 'Admin\FinanceController@income');
            $r->post('finance/income/store', 'Admin\FinanceController@storeIncome');
            $r->get('finance/expenses', 'Admin\FinanceController@expenses');
            $r->post('finance/expenses/store', 'Admin\FinanceController@storeExpense');
            $r->get('finance/receipts', 'Admin\FinanceController@receipts');
            $r->post('finance/receipts/generate', 'Admin\FinanceController@generateReceipt');

            $r->get('budget', 'Admin\BudgetController@index');
            $r->get('budget/create', 'Admin\BudgetController@create');
            $r->post('budget/store', 'Admin\BudgetController@store');
            $r->get('budget/{id}', 'Admin\BudgetController@show');

            $r->get('tax', 'Admin\TaxController@index');
            $r->get('tax/create', 'Admin\TaxController@create');
            $r->post('tax/store', 'Admin\TaxController@store');
            $r->post('tax/payment/{id}', 'Admin\TaxController@recordPayment');

            $r->get('health', 'Admin\HealthController@index');
            $r->get('health/maternal', 'Admin\HealthController@maternal');
            $r->post('health/maternal/store', 'Admin\HealthController@storeMaternal');
            $r->get('health/immunization', 'Admin\HealthController@immunization');
            $r->post('health/immunization/store', 'Admin\HealthController@storeImmunization');
            $r->get('health/growth', 'Admin\HealthController@growth');
            $r->post('health/growth/store', 'Admin\HealthController@storeGrowth');
            $r->get('health/surveillance', 'Admin\HealthController@surveillance');
            $r->post('health/surveillance/store', 'Admin\HealthController@storeSurveillance');

            $r->get('seniors', 'Admin\SeniorPWDController@index');
            $r->post('seniors/store', 'Admin\SeniorPWDController@store');

            $r->get('blotter', 'Admin\BlotterController@index');
            $r->post('blotter/store', 'Admin\BlotterController@store');
            $r->get('blotter/{id}', 'Admin\BlotterController@show');
            $r->post('blotter/{id}/update', 'Admin\BlotterController@update');

            $r->get('lupon', 'Admin\KPController@index');
            $r->get('lupon/cases', 'Admin\KPController@cases');
            $r->get('lupon/cases/create', 'Admin\KPController@createCase');
            $r->post('lupon/cases/store', 'Admin\KPController@storeCase');
            $r->get('lupon/cases/{id}', 'Admin\KPController@showCase');
            $r->post('lupon/cases/{id}/hearing', 'Admin\KPController@addHearing');
            $r->post('lupon/cases/{id}/settle', 'Admin\KPController@settle');
            $r->post('lupon/cases/{id}/cfa', 'Admin\KPController@issueCFA');

            $r->get('tanod', 'Admin\TanodController@index');
            $r->get('tanod/schedule', 'Admin\TanodController@schedule');
            $r->post('tanod/schedule/store', 'Admin\TanodController@storeSchedule');
            $r->get('tanod/cctv', 'Admin\TanodController@cctv');

            $r->get('drrm', 'Admin\DRRMController@index');
            $r->get('drrm/events', 'Admin\DRRMController@events');
            $r->post('drrm/events/store', 'Admin\DRRMController@storeEvent');
            $r->get('drrm/events/{id}', 'Admin\DRRMController@showEvent');
            $r->get('drrm/rdana/{event_id}', 'Admin\DRRMController@rdana');
            $r->post('drrm/rdana/store', 'Admin\DRRMController@storeRDANA');
            $r->get('drrm/hazard-map', 'Admin\DRRMController@hazardMap');
            $r->get('drrm/relief', 'Admin\DRRMController@relief');

            $r->get('evacuation', 'Admin\EvacuationController@index');
            $r->post('evacuation/store', 'Admin\EvacuationController@store');
            $r->get('evacuation/{id}', 'Admin\EvacuationController@show');
            $r->post('evacuation/{id}/checkin', 'Admin\EvacuationController@checkIn');
            $r->post('evacuation/{id}/checkout', 'Admin\EvacuationController@checkOut');

            $r->get('assets', 'Admin\AssetController@index');
            $r->post('assets/store', 'Admin\AssetController@store');
            $r->get('assets/{id}', 'Admin\AssetController@show');
            $r->post('assets/{id}/maintenance', 'Admin\AssetController@addMaintenance');

            $r->get('bookings', 'Admin\BookingController@index');
            $r->post('bookings/store', 'Admin\BookingController@store');
            $r->post('bookings/confirm/{id}', 'Admin\BookingController@confirm');
            $r->post('bookings/cancel/{id}', 'Admin\BookingController@cancel');

            $r->get('livelihood', 'Admin\LivelihoodController@index');
            $r->get('livelihood/jobs', 'Admin\LivelihoodController@jobs');
            $r->post('livelihood/jobs/store', 'Admin\LivelihoodController@storeJob');
            $r->get('livelihood/farmers', 'Admin\LivelihoodController@farmers');
            $r->post('livelihood/farmers/store', 'Admin\LivelihoodController@storeFarmer');

            $r->get('compliance', 'Admin\ComplianceController@index');
            $r->get('compliance/transparency', 'Admin\ComplianceController@transparency');
            $r->post('compliance/transparency/upload', 'Admin\ComplianceController@uploadDocument');

            $r->get('reports', 'Admin\ReportController@index');
            $r->get('reports/annual', 'Admin\ReportController@annual');
            $r->get('reports/soba', 'Admin\ReportController@soba');
            $r->get('reports/budget', 'Admin\ReportController@budget');

            $r->get('notifications', 'Admin\NotificationController@index');
            $r->post('notifications/mark-read/{id}', 'Admin\NotificationController@markRead');
            $r->post('notifications/broadcast', 'Admin\NotificationController@broadcast');
            $r->get('emergency/send', 'Admin\NotificationController@emergencyForm');
            $r->post('emergency/send', 'Admin\NotificationController@sendEmergency');

            $r->get('settings', 'Admin\SettingsController@index');
            $r->post('settings/update', 'Admin\SettingsController@update');
            $r->get('settings/users', 'Admin\SettingsController@users');
            $r->get('settings/users/create', 'Admin\SettingsController@createUser');
            $r->post('settings/users/store', 'Admin\SettingsController@storeUser');

            $r->get('bulletins', 'Admin\BulletinController@index');
            $r->get('bulletins/create', 'Admin\BulletinController@create');
            $r->post('bulletins/store', 'Admin\BulletinController@store');
            $r->post('bulletins/{id}/delete', 'Admin\BulletinController@delete');
        });
    }
}
