<?php

namespace app\utils;

use app\models\User;

class ValidationHelper
{
    /**
     * Nettoie et trim une valeur POST
     * 
     * @param string $key
     * @return string
     */
    public static function postTrim(string $key): string
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    /**
     * Nettoie et trim une valeur depuis un tableau
     * 
     * @param array $data
     * @param string $key
     * @return string
     */
    public static function trimValue(array $data, string $key): string
    {
        return isset($data[$key]) ? trim($data[$key]) : '';
    }

    /**
     * Normalise un numéro de téléphone (enlève les espaces)
     * 
     * @param string $tel
     * @return string
     */
    public static function normalizeTelephone(string $tel): string
    {
        return preg_replace('/\s+/', '', $tel);
    }

    /**
     * Échappe une valeur pour l'affichage HTML
     * 
     * @param mixed $value
     * @return string
     */
    public static function escape($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Retourne la classe CSS 'is-invalid' si le champ a une erreur
     * 
     * @param array $errors
     * @param string $field
     * @return string
     */
    public static function classInvalid(array $errors, string $field): string
    {
        return ($errors[$field] ?? '') !== '' ? 'is-invalid' : '';
    }

    /**
     * Valide les données d'inscription
     * 
     * @param array $input ['nom', 'prenom', 'email', 'password', 'confirm_password', 'telephone']
     * @return array ['ok' => bool, 'errors' => array, 'values' => array]
     */
    public static function validateRegistrationInput(array $input): array
    {
        $errors = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => '',
            'telephone' => '',
        ];

        $values = [
            'nom' => trim($input['nom'] ?? ''),
            'prenom' => trim($input['prenom'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'telephone' => self::normalizeTelephone(trim($input['telephone'] ?? '')),
        ];

        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        // Validation du nom
        if (mb_strlen($values['nom']) < 2) {
            $errors['nom'] = "Le nom doit contenir au moins 2 caractères.";
        }

        // Validation du prénom
        if (mb_strlen($values['prenom']) < 2) {
            $errors['prenom'] = "Le prénom doit contenir au moins 2 caractères.";
        }

        // Validation de l'email
        if ($values['email'] === '') {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email n'est pas valide (ex: nom@domaine.com).";
        } elseif (User::emailExists($values['email'])) {
            $errors['email'] = "Cet email est déjà utilisé.";
        }

        // Validation du mot de passe
        if (strlen($password) < 8) {
            $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        // Validation de la confirmation
        if (strlen($confirm) < 8) {
            $errors['confirm_password'] = "Veuillez confirmer le mot de passe (min 8 caractères).";
        } elseif ($password !== $confirm) {
            $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
            if ($errors['password'] === '') {
                $errors['password'] = "Vérifiez le mot de passe et sa confirmation.";
            }
        }

        // Validation du téléphone: 8-15 chiffres
        if (strlen($values['telephone']) < 8 || strlen($values['telephone']) > 15) {
            $errors['telephone'] = "Le téléphone doit contenir entre 8 et 15 chiffres.";
        } elseif (!preg_match('/^[0-9]+$/', $values['telephone'])) {
            $errors['telephone'] = "Le téléphone ne doit contenir que des chiffres.";
        }

        // Vérifier si toutes les validations sont OK
        $ok = true;
        foreach ($errors as $msg) {
            if ($msg !== '') {
                $ok = false;
                break;
            }
        }

        return ['ok' => $ok, 'errors' => $errors, 'values' => $values];
    }

    /**
     * Valide les données de connexion
     * 
     * @param array $input ['email', 'password']
     * @return array ['ok' => bool, 'error' => string, 'user' => User|null]
     */
    public static function validateLoginInput(array $input): array
    {
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        // Vérifications de base
        if ($email === '') {
            return ['ok' => false, 'error' => "L'email est obligatoire.", 'user' => null];
        }

        if ($password === '') {
            return ['ok' => false, 'error' => "Le mot de passe est obligatoire.", 'user' => null];
        }

        // Rechercher l'utilisateur
        $user = User::findByEmail($email);

        if ($user === null) {
            return ['ok' => false, 'error' => "Email ou mot de passe incorrect.", 'user' => null];
        }

        // Vérifier le mot de passe
        if (!$user->verifyPassword($password)) {
            return ['ok' => false, 'error' => "Email ou mot de passe incorrect.", 'user' => null];
        }

        return ['ok' => true, 'error' => '', 'user' => $user];
    }

    /**
     * Démarre une session sécurisée
     */
    public static function startSessionSecure(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Stocke les informations utilisateur en session
     * 
     * @param User $user
     */
    public static function storeUserSession(User $user): void
    {
        self::startSessionSecure();
        $_SESSION['user'] = $user->toSessionArray();
        $_SESSION['user_id'] = $user->id;
    }

    /**
     * Vérifie si un utilisateur est connecté
     * 
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        self::startSessionSecure();
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Récupère les données de l'utilisateur connecté
     * 
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        self::startSessionSecure();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public static function logoutUser(): void
    {
        self::startSessionSecure();
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
}
