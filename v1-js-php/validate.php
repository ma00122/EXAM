<?php
// validate.php
header('Content-Type: application/json; charset=utf-8');

require_once('inc/db.php');
require_once ('inc/lib_validation.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errors' => ['_global' => 'Méthode non autorisée.'], 'values' => []]);
    exit;
}

try {
    $pdo = getPdo();

    $input = [
        'nom' => post_trim('nom'),
        'prenom' => post_trim('prenom'),
        'email' => post_trim('email'),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'telephone' => post_trim('telephone'),
    ];

    $result = validate_registration_input($input, $pdo);

    // Astuce pro : renvoyer aussi un statut global exploitable
    echo json_encode([
        'ok' => $result['ok'],
        'errors' => $result['errors'],
        'values' => $result['values'], // ex: telephone normalisé sans espaces
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'errors' => ['_global' => 'Erreur serveur lors de la validation.'],
        'values' => []
    ]);
}