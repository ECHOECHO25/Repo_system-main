<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
$routes->options('api', 'Home::options');
$routes->options('api/(:any)', 'Home::options');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Auth
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/logout', 'AuthController::logout');
    $routes->get('auth/me', 'AuthController::me');

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/publications-summary', 'DashboardController::publicationsSummary');
    $routes->get('dashboard/faculty-metrics', 'DashboardController::facultyMetrics');
    $routes->get('dashboard/activity', 'DashboardController::activity');
    
    // Publications
    $routes->get('publications', 'PublicationsController::index');
    $routes->get('publications/(:num)', 'PublicationsController::show/$1');
    $routes->get('publications/recent', 'PublicationsController::recent');
    $routes->get('publications/by-year/(:num)', 'PublicationsController::byYear/$1');
    $routes->post('publications/bulk-import', 'PublicationsController::bulkImport');
    $routes->post('publications', 'PublicationsController::create');
    $routes->put('publications/(:num)', 'PublicationsController::update/$1');
    $routes->delete('publications/(:num)', 'PublicationsController::delete/$1');
    
    // Faculty
    $routes->get('faculty', 'FacultyController::index');
    $routes->get('faculty/(:num)', 'FacultyController::show/$1');
    $routes->get('faculty/top-citations', 'FacultyController::topByCitations');
    $routes->get('faculty/top-hindex', 'FacultyController::topByHIndex');
    $routes->post('faculty', 'FacultyController::create');
    $routes->put('faculty/(:num)', 'FacultyController::update/$1');
    $routes->delete('faculty/(:num)', 'FacultyController::delete/$1');

    // Audit logs
    $routes->get('audit-logs', 'AuditLogsController::index');
    $routes->post('audit-logs/record', 'AuditLogsController::record');

    // Users
    $routes->post('users', 'UsersController::create');

    // Acknowledgements
    $routes->get('acknowledgements', 'AcknowledgementsController::index');
    $routes->get('acknowledgements/(:num)', 'AcknowledgementsController::show/$1');
    $routes->post('acknowledgements', 'AcknowledgementsController::create');
    $routes->put('acknowledgements/(:num)', 'AcknowledgementsController::update/$1');
    $routes->delete('acknowledgements/(:num)', 'AcknowledgementsController::delete/$1');
});
