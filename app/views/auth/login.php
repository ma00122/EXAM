<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion</title>
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header text-center"><h4>Connexion</h4></div>
        <div class="card-body">

          <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-info">Vous avez été déconnecté avec succès.</div>
          <?php endif; ?>

          <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= call_user_func($e, $error) ?></div>
          <?php endif; ?>

          <form method="post" action="/login">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input id="email" name="email" type="email" class="form-control" 
                     value="<?= call_user_func($e, $emailValue) ?>" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Mot de passe</label>
              <input id="password" name="password" type="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Se connecter</button>
          </form>

          <div class="mt-3 text-center">
            <a href="/register">Pas encore inscrit ? S'inscrire</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
