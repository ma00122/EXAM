<?php

namespace app\middlewares;

use app\utils\ValidationHelper;
use flight\Engine;

/**
 * Middleware d'authentification pour protéger les routes
 * 
 * Exemple d'utilisation dans routes.php:
 * 
 * $router->group('/admin', function($router) {
 *     // Ces routes nécessitent une authentification
 *     $router->get('/dashboard', [AdminController::class, 'dashboard']);
 *     $router->get('/users', [AdminController::class, 'users']);
 * }, [new AuthMiddleware()]);
 */
class AuthMiddleware
{
    /**
     * Vérifie si l'utilisateur est authentifié avant d'accéder à la route
     * 
     * @param Engine $app
     * @return bool
     */
    public function before(Engine $app): bool
    {
        ValidationHelper::startSessionSecure();
        
        if (!ValidationHelper::isLoggedIn()) {
            // Stocker l'URL demandée pour redirection après login
            $_SESSION['redirect_after_login'] = $app->request()->url;
            
            // Rediriger vers la page de connexion
            $app->redirect('/login');
            return false;
        }
        
        // Rendre les données utilisateur disponibles dans toutes les vues
        $app->view()->set('current_user', ValidationHelper::getCurrentUser());
        
        return true;
    }
}
