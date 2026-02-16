<?php

use app\controllers\AdminController;
use app\controllers\AuthController;
use app\controllers\EchangeController;
use app\controllers\ObjetController;
use app\controllers\BesoinController;
use app\controllers\VilleController;
use flight\Engine;
use flight\net\Router;

/** 22
 * @var Router $router 
 * @var Engine $app
 */



$router->get('/', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->accueil();
});

/* ===================== ROUTES BESOINS ===================== */

// GET /besoins - Liste de tous les besoins
$router->get('/besoins', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->index();
});

// GET /besoins/create - Formulaire de création
$router->get('/besoins/create', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->create();
});

// POST /besoins/store - Enregistrer un nouveau besoin
$router->post('/besoins/store', function() use ($app) {
    $controller = new BesoinController($app);
    $controller->store();
});

// GET /besoins/edit/@id - Formulaire de modification
$router->get('/besoins/edit/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->edit((int) $id);
});

// POST /besoins/update/@id - Mettre à jour un besoin
$router->post('/besoins/update/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->update((int) $id);
});

// GET /besoins/delete/@id - Supprimer un besoin
$router->get('/besoins/delete/@id', function($id) use ($app) {
    $controller = new BesoinController($app);
    $controller->delete((int) $id);
});
