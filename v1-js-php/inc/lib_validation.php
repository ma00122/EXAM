<?php
// lib_validation.php

function post_trim($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

function normalize_telephone($tel) {
    // enlève espaces (tu peux ajouter +, tirets si tu veux plus tard)
    return preg_replace('/\s+/', '', $tel);
}

function validate_registration_input($input, $pdo = null) {
    // $input = ['nom'=>..., 'prenom'=>..., 'email'=>..., 'password'=>..., 'confirm_password'=>..., 'telephone'=>...]
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
        'telephone' => normalize_telephone(trim($input['telephone'] ?? '')),
    ];

    $password = $input['password'] ?? '';
    $confirm  = $input['confirm_password'] ?? '';

    // --- mêmes règles + mêmes messages ---
    if (mb_strlen($values['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères.";
    }

    if (mb_strlen($values['prenom']) < 2) {
        $errors['prenom'] = "Le prénom doit contenir au moins 2 caractères.";
    }

    if ($values['email'] === '') {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "L'email n'est pas valide (ex: nom@domaine.com).";
    }

    if (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if (strlen($confirm) < 8) {
        $errors['confirm_password'] = "Veuillez confirmer le mot de passe (min 8 caractères).";
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
        if ($errors['password'] === '') {
            $errors['password'] = "Vérifiez le mot de passe et sa confirmation.";
        }
    }

    // Téléphone : 8–15 chiffres, uniquement chiffres
    if (strlen($values['telephone']) < 8 || strlen($values['telephone']) > 15) {
        $errors['telephone'] = "Le téléphone doit contenir entre 8 et 15 chiffres.";
    } elseif (!preg_match('/^[0-9]+$/', $values['telephone'])) {
        $errors['telephone'] = "Le téléphone ne doit contenir que des chiffres.";
    }

    // Vérif email unique (version pro)
    if ($pdo && $errors['email'] === '') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$values['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = "Cet email est déjà utilisé.";
        }
    }

    $ok = true;
    foreach ($errors as $msg) {
        if ($msg !== '') { $ok = false; break; }
    }

    return ['ok' => $ok, 'errors' => $errors, 'values' => $values];
}