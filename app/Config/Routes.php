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
