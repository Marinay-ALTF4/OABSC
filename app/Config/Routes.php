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
$routes->get('/logout', 'Auth::logout');

// Dashboard
$routes->get('/dashboard', 'Home::index');

// Admin
$routes->get('/admin/login', 'Auth::adminLogin');
$routes->get('/admin/patients', 'Admin::patients');
$routes->get('/admin/patients/list', 'Admin::patientList');
$routes->get('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/edit/(:num)', 'Admin::editUser/$1');
$routes->post('/admin/patients/delete/(:num)', 'Admin::deleteUser/$1');
$routes->post('/admin/patients/restore/(:num)', 'Admin::restoreUser/$1');

// API (Postman)
$routes->get('/api/health', 'Api::health');
$routes->post('/api/register', 'Api::register');
$routes->post('/api/login', 'Api::login');
$routes->get('/api/users', 'Api::users');
