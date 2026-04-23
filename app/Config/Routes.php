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

// Notifications
$routes->post('/notifications/mark-all-read', 'Notifications::markAllRead');

// Access Requests
$routes->post('/access-request/send', 'AccessRequest::request');
$routes->post('/access-request/approve', 'AccessRequest::approve');

// Doctor Schedule
$routes->get('/doctor/schedule', 'DoctorSchedule::index');
$routes->post('/doctor/schedule/save', 'DoctorSchedule::save');
$routes->get('/api/doctor/(:num)/schedule', 'DoctorSchedule::getByDoctor/$1');

// Doctor Appointments
$routes->get('/doctor/appointments', 'DoctorAppointments::index');
$routes->post('/doctor/appointments/status', 'DoctorAppointments::updateStatus');

// Client Profile
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/save', 'Profile::save');

// Role Selection
$routes->get('/role-selection', 'RoleSelection::index');
$routes->post('/role-selection/verify', 'RoleSelection::verify');

// Admin
$routes->get('/admin/login', 'Auth::adminLogin');
$routes->get('/admin/patients', 'Admin::patients');
$routes->get('/admin/patients/list', 'Admin::patientList');
$routes->get('/admin/patients/history/(:num)', 'Admin::patientHistory/$1');
$routes->get('/admin/patients/history', 'Admin::patientHistory');
$routes->get('/admin/doctors', 'Admin::doctorList');
$routes->get('/admin/doctors/specialization', 'Admin::doctorSpecialization');
$routes->get('/admin/doctors/schedule', 'Admin::doctorSchedule');
$routes->get('/admin/patients/clients', 'Admin::clientList');
$routes->get('/admin/patients/add', 'Admin::addUser');
$routes->post('/admin/patients/add', 'Admin::addUser');
$routes->get('/admin/patients/add-role', 'Admin::addRole');
$routes->post('/admin/patients/add-role', 'Admin::addRole');
$routes->get('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/delete/(:num)', 'Admin::deleteUser/$1');
$routes->post('/admin/patients/restore/(:num)', 'Admin::restoreUser/$1');
$routes->get('/admin/settings', 'Admin::clinicSettings');
$routes->post('/admin/settings', 'Admin::clinicSettings');

// API (Postman)
$routes->get('/api/health', 'Api::health');
$routes->post('/api/register', 'Api::register');
$routes->get('/api/users', 'Api::users');
$routes->post('/api/login', 'Auth::apiLogin');   // ✅ used by Android app
