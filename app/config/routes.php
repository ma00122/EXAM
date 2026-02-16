<?php

use app\controllers\AdminController;
use app\controllers\AuthController;
use app\controllers\EchangeController;
use app\controllers\ObjetController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */



$router->get('/', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->accueil();
});

$router->get('/objets', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->liste();
});

$router->get('/objets/@id', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->detail((int) $id);
});

$router->get('/objets/@id/historique', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->historique((int) $id);
});

$router->get('/register', function() use ($app) {
    $controller = new AuthController($app);
    $controller->register();
});

$router->post('/register', function() use ($app) {
    $controller = new AuthController($app);
    $controller->register();
});

$router->post('/validate', function() use ($app) {
    $controller = new AuthController($app);
    $controller->validate();
});

$router->get('/login', function() use ($app) {
    $controller = new AuthController($app);
    $controller->login();
});

$router->post('/login', function() use ($app) {
    $controller = new AuthController($app);
    $controller->login();
});

$router->get('/dashboard', function() use ($app) {
    $controller = new AuthController($app);
    $controller->dashboard();
});

$router->post('/logout', function() use ($app) {
    $controller = new AuthController($app);
    $controller->logout();
});

$router->get('/mes-objets', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->mesObjets();
});

$router->get('/mes-objets/nouveau', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->create();
});

$router->post('/mes-objets/nouveau', function() use ($app) {
    $controller = new ObjetController($app);
    $controller->create();
});

$router->get('/mes-objets/@id/modifier', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->edit((int) $id);
});

$router->post('/mes-objets/@id/modifier', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->edit((int) $id);
});

$router->post('/mes-objets/@id/supprimer', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->delete((int) $id);
});

$router->post('/photos/@id/supprimer', function($id) use ($app) {
    $controller = new ObjetController($app);
    $controller->deletePhoto((int) $id);
});

$router->get('/mes-objets/@id/prix/@pourcentage', function($id, $pourcentage) use ($app) {
    $controller = new ObjetController($app);
    $controller->parPrix((int) $id, (int) $pourcentage);
});

$router->get('/echanges', function() use ($app) {
    $controller = new EchangeController($app);
    $controller->index();
});

$router->get('/echanges/proposer/@objetId', function($objetId) use ($app) {
    $controller = new EchangeController($app);
    $controller->proposer((int) $objetId);
});

$router->post('/echanges/proposer/@objetId', function($objetId) use ($app) {
    $controller = new EchangeController($app);
    $controller->proposer((int) $objetId);
});

$router->get('/echanges/@id', function($id) use ($app) {
    $controller = new EchangeController($app);
    $controller->detail((int) $id);
});

$router->post('/echanges/@id/accepter', function($id) use ($app) {
    $controller = new EchangeController($app);
    $controller->accepter((int) $id);
});

$router->post('/echanges/@id/refuser', function($id) use ($app) {
    $controller = new EchangeController($app);
    $controller->refuser((int) $id);
});

$router->get('/admin/login', function() use ($app) {
    $controller = new AdminController($app);
    $controller->login();
});

$router->post('/admin/login', function() use ($app) {
    $controller = new AdminController($app);
    $controller->login();
});

$router->get('/admin', function() use ($app) {
    $controller = new AdminController($app);
    $controller->dashboard();
});

$router->get('/admin/statistiques', function() use ($app) {
    $controller = new AdminController($app);
    $controller->statistiques();
});

$router->get('/admin/categories', function() use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesIndex();
});

$router->get('/admin/categories/nouveau', function() use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesCreate();
});

$router->post('/admin/categories/nouveau', function() use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesCreate();
});

$router->get('/admin/categories/@id/modifier', function($id) use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesEdit((int) $id);
});

$router->post('/admin/categories/@id/modifier', function($id) use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesEdit((int) $id);
});

$router->post('/admin/categories/@id/supprimer', function($id) use ($app) {
    $controller = new AdminController($app);
    $controller->categoriesDelete((int) $id);
});
