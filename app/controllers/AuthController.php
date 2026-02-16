<?php
namespace app\controllers;

use flight\Engine;
use Flight;

/**
 * Controller AuthController - Gestion de l'authentification (placeholder)
 */
class AuthController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
        Flight::redirect('/besoins');
    }

    public function validate(): void
    {
        Flight::json(['success' => true]);
    }

    public function login(): void
    {
        Flight::redirect('/besoins');
    }

    public function dashboard(): void
    {
        Flight::redirect('/besoins');
    }

    public function logout(): void
    {
        session_destroy();
        Flight::redirect('/besoins');
    }
}
