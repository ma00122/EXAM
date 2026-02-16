<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BNGRC') ?> - Gestion des Dons</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: bold; }
        .card-stat { transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .prix-estimatif { font-size: 1.2em; font-weight: bold; color: #198754; }
        .hero-section { background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%); color: white; padding: 3rem 0; }
        footer { background-color: #1a365d; color: white; }
        footer a { color: #90cdf4; }
        footer a:hover { color: white; }
        .nav-link.active { background-color: rgba(255,255,255,0.1); border-radius: 5px; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1a365d;">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-building"></i> BNGRC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    
                    <!-- Menu Gestion -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-folder"></i> Gestion
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                            <li><a class="dropdown-item" href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/dons"><i class="bi bi-gift"></i> Dons Nature</a></li>
                            <li><a class="dropdown-item" href="/dons-argent"><i class="bi bi-cash-stack"></i> Dons Argent</a></li>
                        </ul>
                    </li>

                    <!-- Menu Opérations -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-arrow-left-right"></i> Opérations
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/attributions"><i class="bi bi-arrow-left-right"></i> Attributions</a></li>
                            <li><a class="dropdown-item" href="/achats"><i class="bi bi-cart-check"></i> Achats</a></li>
                        </ul>
                    </li>

                    <!-- Simulation -->
                    <li class="nav-item">
                        <a class="nav-link" href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a>
                    </li>

                    <!-- Récapitulatif -->
                    <li class="nav-item">
                        <a class="nav-link" href="/recap"><i class="bi bi-clipboard-data"></i> Récap</a>
                    </li>

                    <!-- Configuration -->
                    <li class="nav-item">
                        <a class="nav-link" href="/configuration"><i class="bi bi-gear"></i> Config</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-calendar3"></i> <?= date('d/m/Y') ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main>
