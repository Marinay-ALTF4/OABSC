<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');        // visiting http://localhost/OABSC/ shows login
$routes->get('/dashboard', 'Home::index');
$routes->get('/admin/patients', 'Admin::patients');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin');
$routes->match(['get','post'], '/register', 'Auth::register');
$routes->get('/logout', 'Auth::logout');
