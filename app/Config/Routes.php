<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Auth
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin');
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
$routes->get('/register/verify', 'Auth::verifyRegistration');
$routes->post('/register/verify', 'Auth::verifyRegistration');
$routes->post('/register/resend-code', 'Auth::resendVerificationCode');
$routes->get('/register/reset', 'Auth::resetRegistration');
$routes->get('/logout', 'Auth::logout');

// Dashboard
$routes->get('/dashboard', 'Home::index');

// Client Appointments
$routes->get('/appointments/new', 'Appointments::new');
$routes->post('/appointments', 'Appointments::create');
$routes->get('/appointments/my', 'Appointments::my');

// Admin
$routes->get('/admin/login', 'Auth::adminLogin');
$routes->get('/admin/patients', 'Admin::patients');
$routes->get('/admin/patients/list', 'Admin::patientList');
$routes->get('/admin/patients/add', 'Admin::addUser');
$routes->post('/admin/patients/add', 'Admin::addUser');
$routes->get('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/delete/(:num)', 'Admin::deleteUser/$1');
$routes->post('/admin/patients/restore/(:num)', 'Admin::restoreUser/$1');

// API (Postman)
$routes->get('/api/health', 'Api::health');
$routes->post('/api/register', 'Api::register');
$routes->get('/api/users', 'Api::users');
$routes->post('/api/login', 'Auth::apiLogin');   // ✅ used by Android app
