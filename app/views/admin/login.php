<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Takalo-Takalo</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-gear-fill"></i> Administration</h4>
                        <small>Takalo-Takalo</small>
                    </div>
                    <div class="card-body">
                        <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?= call_user_func($e, $error) ?></div>
                        <?php endif; ?>

                        <!-- Identifiants par défaut -->
                        <div class="alert alert-info small">
                            <strong>Identifiants par défaut :</strong><br>
                            Email : <code><?= $defaultEmail ?></code><br>
                            Mot de passe : <code><?= $defaultPassword ?></code>
                        </div>

                        <form method="POST" action="/admin/login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= call_user_func($e, $defaultEmail) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           value="<?= call_user_func($e, $defaultPassword) ?>" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Se connecter
                            </button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/" class="text-muted"><i class="bi bi-arrow-left"></i> Retour au site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
