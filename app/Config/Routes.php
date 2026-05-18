<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Auth
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin');
$routes->get('/login/verify-mfa', 'Auth::verifyMfa');
$routes->post('/login/verify-mfa', 'Auth::verifyMfa');
$routes->post('/login/resend-mfa', 'Auth::resendMfaCode');
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
$routes->get('/register/verify', 'Auth::verifyRegistration');
$routes->post('/register/verify', 'Auth::verifyRegistration');
$routes->post('/register/resend-code', 'Auth::resendVerificationCode');
$routes->get('/register/reset', 'Auth::resetRegistration');
$routes->get('/logout', 'Auth::logout');

// Dashboard
$routes->get('/dashboard', 'Home::index');

// Settings
$routes->get('/settings', 'Settings::index');
$routes->post('/settings/update', 'Settings::update');

// Secretary
$routes->get('/secretary/appointments', 'Secretary::appointments');
$routes->get('/secretary/queue', 'Secretary::queue');
$routes->get('/secretary/records', 'Secretary::records');
$routes->get('/secretary/register', 'Secretary::register');
$routes->post('/secretary/register', 'Secretary::register');
$routes->get('/secretary/schedules', 'Secretary::schedules');
$routes->get('/secretary/approvals', 'Secretary::approvals');
$routes->post('/secretary/update-status', 'Secretary::updateStatus');

// Client Appointments
$routes->get('/appointments/new', 'Appointments::new');
$routes->post('/appointments', 'Appointments::create');
$routes->get('/appointments/my', 'Appointments::my');
$routes->post('/appointments/cancel/(:num)', 'Appointments::cancel/$1');

// Notifications
$routes->post('/notifications/mark-all-read', 'Notifications::markAllRead');
$routes->post('/notifications/delete/(:num)', 'Notifications::deleteOne/$1');
$routes->get('/notifications/fetch', 'Notifications::fetch');

// Access Requests
$routes->post('/access-request/send', 'AccessRequest::request');
$routes->post('/access-request/approve', 'AccessRequest::approve');

// Doctor Schedule
$routes->get('/doctor/schedule', 'DoctorSchedule::index');
$routes->post('/doctor/schedule/save', 'DoctorSchedule::save');
$routes->get('/api/doctor/(:num)/schedule', 'DoctorSchedule::getByDoctor/$1');

// Doctor Appointments
$routes->get('/doctor/records', 'DoctorAppointments::records');
$routes->get('/doctor/records/(:num)', 'DoctorAppointments::records/$1');
$routes->get('/doctor/notes', 'DoctorAppointments::notes');
$routes->post('/doctor/notes', 'DoctorAppointments::saveNote');
$routes->get('/doctor/prescriptions', 'DoctorAppointments::prescriptions');
$routes->post('/doctor/prescriptions', 'DoctorAppointments::savePrescription');
$routes->get('/doctor/queue', 'DoctorAppointments::queue');
$routes->get('/doctor/appointments', 'DoctorAppointments::index');
$routes->post('/doctor/appointments/status', 'DoctorAppointments::updateStatus');

// Client Profile
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/save', 'Profile::save');

// Admin
$routes->get('/admin/login', 'Auth::adminLogin');
// Permission assign — outside filter (admin-only check is inside controller)
$routes->post('/admin/permissions/add',    'AdminPermissions::addPermission');
$routes->post('/admin/permissions/assign', 'AdminPermissions::assignPermission');
$routes->group('admin', ['filter' => 'permission'], function($routes) {
    $routes->get('appointments',                'Admin::appointments');
    $routes->post('appointments/update-status', 'Admin::updateAppointmentStatus');
    $routes->post('appointments/archive/(:num)',  'Admin::archiveAppointment/$1');
    $routes->post('appointments/restore/(:num)',  'Admin::restoreAppointment/$1');
    $routes->post('appointments/delete/(:num)',   'Admin::deleteArchivedAppointment/$1');
    $routes->get('audit-log',                   'AuditLog::index');
    $routes->get('audit-reports',               'AuditReport::index');
    $routes->get('audit-reports/export',        'AuditReport::exportCsv');
    $routes->get('reports',                     'AuditReport::index');
    $routes->get('access-requests',             'Admin::accessRequests');
    $routes->get('permissions',                 'AdminPermissions::index');
    $routes->post('permissions/add',            'AdminPermissions::addPermission');
    $routes->post('permissions/assign',         'AdminPermissions::assignPermission');
    $routes->get('patients',                    'Admin::patients');
    $routes->get('patients/list',               'Admin::patientList');
    $routes->get('patients/history/(:num)',     'Admin::patientHistory/$1');
    $routes->get('patients/history',            'Admin::patientHistory');
    $routes->get('doctors',                     'Admin::doctorList');
    $routes->get('doctors/specialization',      'Admin::doctorSpecialization');
    $routes->get('doctors/schedule',            'Admin::doctorSchedule');
    $routes->get('doctor-schedules',            'Admin::doctorSchedule');
    $routes->get('patients/clients',            'Admin::clientList');
    $routes->get('patients/add',                'Admin::addUser');
    $routes->post('patients/add',               'Admin::addUser');
    $routes->get('patients/add-role',           'Admin::addRole');
    $routes->post('patients/add-role',          'Admin::addRole');
    $routes->get('patients/edit/(:num)',        'Admin::editUser/$1');
    $routes->post('patients/edit/(:num)',       'Admin::editUser/$1');
    $routes->post('patients/delete/(:num)',     'Admin::deleteUser/$1');
    $routes->post('patients/restore/(:num)',    'Admin::restoreUser/$1');
    $routes->get('settings',                    'Settings::index');
    $routes->post('settings',                   'Settings::update');
    $routes->get('announcements',               'Home::index');
});

// API (Flutter Mobile App)
$routes->get('/api/health', 'Api::health');
$routes->post('/api/login', 'Api::login');
$routes->post('/api/register', 'Api::register');
$routes->get('/api/users', 'Api::users');
$routes->get('/api/patients', 'Api::patients');
$routes->get('/api/appointments', 'Api::appointments');
$routes->post('/api/appointments', 'Api::createAppointment');
$routes->get('/api/dashboard', 'Api::dashboard');
$routes->get('/api/doctors', 'Api::doctors');
$routes->get('/api/profile', 'Api::profile');
$routes->post('/api/profile/update', 'Api::updateProfile');
$routes->get('/api/notifications', 'Api::notifications');
$routes->post('/api/admin/users/add', 'Api::addUser');
$routes->post('/api/admin/roles/add', 'Api::addRole');
$routes->post('/api/doctor/schedule/save', 'Api::saveDoctorSchedule');
$routes->post('/api/doctor/(:any)/schedule/save', 'Api::saveDoctorSchedule/$1');
$routes->post('/api/appointments/cancel', 'Api::cancelAppointment');
$routes->get('/api/notes', 'Api::getNotes');
$routes->post('/api/notes', 'Api::saveNote');
$routes->delete('/api/notes/(:num)', 'Api::deleteNote/$1');
$routes->get('/api/prescriptions', 'Api::getPrescriptions');
$routes->post('/api/prescriptions', 'Api::savePrescription');
$routes->delete('/api/prescriptions/(:num)', 'Api::deletePrescription/$1');
$routes->post('/api/appointments/update-status', 'Api::updateAppointmentStatus');
