<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription</title>
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center"><h4>Inscription utilisateur</h4></div>
        <div class="card-body">

          <?php if ($success): ?>
            <div class="alert alert-success">Inscription réussie ✅</div>
          <?php endif; ?>

          <form id="registerForm" method="post" action="/register" novalidate>
            <div id="formStatus" class="alert d-none"></div>

            <div class="mb-3">
              <label for="nom" class="form-label">Nom</label>
              <input id="nom" name="nom" class="form-control <?= call_user_func($cls_invalid, $errors, 'nom') ?>" value="<?= call_user_func($e, $values['nom']) ?>">
              <div class="invalid-feedback" id="nomError"><?= call_user_func($e, $errors['nom']) ?></div>
            </div>

            <div class="mb-3">
              <label for="prenom" class="form-label">Prénom</label>
              <input id="prenom" name="prenom" class="form-control <?= call_user_func($cls_invalid, $errors, 'prenom') ?>" value="<?= call_user_func($e, $values['prenom']) ?>">
              <div class="invalid-feedback" id="prenomError"><?= call_user_func($e, $errors['prenom']) ?></div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input id="email" name="email" class="form-control <?= call_user_func($cls_invalid, $errors, 'email') ?>" value="<?= call_user_func($e, $values['email']) ?>">
              <div class="invalid-feedback" id="emailError"><?= call_user_func($e, $errors['email']) ?></div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Mot de passe</label>
              <input id="password" name="password" type="password" class="form-control <?= call_user_func($cls_invalid, $errors, 'password') ?>">
              <div class="invalid-feedback" id="passwordError"><?= call_user_func($e, $errors['password']) ?></div>
            </div>

            <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirmation</label>
              <input id="confirm_password" name="confirm_password" type="password" class="form-control <?= call_user_func($cls_invalid, $errors, 'confirm_password') ?>">
              <div class="invalid-feedback" id="confirmPasswordError"><?= call_user_func($e, $errors['confirm_password']) ?></div>
            </div>

            <div class="mb-3">
              <label for="telephone" class="form-label">Téléphone</label>
              <input id="telephone" name="telephone" class="form-control <?= call_user_func($cls_invalid, $errors, 'telephone') ?>" value="<?= call_user_func($e, $values['telephone']) ?>">
              <div class="invalid-feedback" id="telephoneError"><?= call_user_func($e, $errors['telephone']) ?></div>
            </div>

            <button class="btn btn-primary w-100" type="submit">S'inscrire</button>
          </form>

          <div class="mt-3 text-center">
            <a href="/login">Déjà inscrit ? Se connecter</a>
          </div>

          <script src="/assets/js/validation-ajax.js" defer></script>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
