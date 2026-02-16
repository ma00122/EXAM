<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Tableau de bord</h4>
          <form method="post" action="/logout">
            <button type="submit" class="btn btn-sm btn-danger">Se déconnecter</button>
          </form>
        </div>
        <div class="card-body">
          <div class="alert alert-success">
            Bienvenue, <strong><?= call_user_func($e, $user['prenom'] . ' ' . $user['nom']) ?></strong> ! 👋
          </div>

          <h5>Vos informations</h5>
          <table class="table table-bordered">
            <tbody>
              <tr>
                <th>Nom</th>
                <td><?= call_user_func($e, $user['nom']) ?></td>
              </tr>
              <tr>
                <th>Prénom</th>
                <td><?= call_user_func($e, $user['prenom']) ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?= call_user_func($e, $user['email']) ?></td>
              </tr>
              <tr>
                <th>Téléphone</th>
                <td><?= call_user_func($e, $user['telephone']) ?></td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4">
            <p class="text-muted">Vous êtes maintenant connecté. Cette page est protégée et nécessite une authentification.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
