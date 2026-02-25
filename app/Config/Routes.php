<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->get('/admin/login', 'Auth::adminLogin');
$routes->post('/login', 'Auth::attemptLogin');
$routes->match(['get','post'], '/register', 'Auth::register');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Home::index');
$routes->get('/admin/patients', 'Admin::patients');
$routes->get('/appointments/book', 'Appointment::book');
$routes->post('/appointments/store', 'Appointment::store');
$routes->get('/appointments/my', 'Appointment::myAppointments');
$routes->post('/appointments/cancel/(:num)', 'Appointment::cancel/$1');
