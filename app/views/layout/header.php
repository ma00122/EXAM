<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Takalo-Takalo') ?> - Échange d'objets</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: bold; }
        .card-objet { transition: transform 0.2s; }
        .card-objet:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .badge-statut-disponible { background-color: #28a745; }
        .badge-statut-en_echange { background-color: #ffc107; color: #000; }
        .badge-statut-echange { background-color: #6c757d; }
        .prix-estimatif { font-size: 1.2em; font-weight: bold; color: #198754; }
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0; }
        footer { background-color: #343a40; color: white; }
        footer a { color: #adb5bd; }
        footer a:hover { color: white; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-arrow-left-right"></i> Takalo-Takalo
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house"></i> Accueil</a>
                    </li>
                    
                    <!-- Menu BNGRC -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> BNGRC
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                            <li><a class="dropdown-item" href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                            <li><a class="dropdown-item" href="/dons"><i class="bi bi-gift"></i> Dons</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a></li>
                            <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/objets"><i class="bi bi-grid"></i> Objets</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/mes-objets"><i class="bi bi-box"></i> Mes objets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/echanges"><i class="bi bi-arrow-left-right"></i> Mes échanges</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Barre de recherche -->
                <form class="d-flex me-3" action="/objets" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Rechercher..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="/mes-objets"><i class="bi bi-box"></i> Mes objets</a></li>
                                <li><a class="dropdown-item" href="/echanges"><i class="bi bi-arrow-left-right"></i> Mes échanges</a></li>
                                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-warning" href="/admin"><i class="bi bi-gear"></i> Administration</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="/logout" method="POST" class="d-inline">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2" href="/register">
                                <i class="bi bi-person-plus"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main>
