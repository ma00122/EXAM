<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BNGRC') ?> - Gestion des Catastrophes</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: bold; }
        .card-bngrc { transition: transform 0.2s; }
        .card-bngrc:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .badge-statut-disponible { background-color: #28a745; }
        .badge-statut-en_cours { background-color: #ffc107; color: #000; }
        .badge-statut-termine { background-color: #6c757d; }
        .montant-total { font-size: 1.2em; font-weight: bold; color: #198754; }
        .hero-section { background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; padding: 3rem 0; }
        footer { background-color: #1e3a5f; color: white; }
        footer a { color: #90cdf4; }
        footer a:hover { color: white; }
        .navbar-bngrc { background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%) !important; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-bngrc">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-shield-check"></i> BNGRC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house"></i> Accueil</a>
                    </li>
                    
                    <!-- Menu Gestion -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-database"></i> Gestion
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                            <li><a class="dropdown-item" href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/dons"><i class="bi bi-gift"></i> Dons Nature</a></li>
                            <li><a class="dropdown-item" href="/dons-argent"><i class="bi bi-cash-stack"></i> Dons Argent</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/attributions"><i class="bi bi-arrow-left-right"></i> Attributions</a></li>
                            <li><a class="dropdown-item" href="/achats"><i class="bi bi-cart-check"></i> Achats</a></li>
                        </ul>
                    </li>
                    
                    <!-- Menu Simulation & Recap -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bar-chart"></i> Analyse
                        </a>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header"><i class="bi bi-cpu"></i> Modes de Simulation</h6></li>
                            <li><a class="dropdown-item" href="/simulation?mode=chronologique">📅 Chronologique (FIFO)</a></li>
                            <li><a class="dropdown-item" href="/simulation?mode=croissant">📊 Croissant (petits d'abord)</a></li>
                            <li><a class="dropdown-item" href="/simulation?mode=proportionnel">⚖️ Proportionnel (équitable)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/simulation"><i class="bi bi-play-circle"></i> Simulation (général)</a></li>
                            <li><a class="dropdown-item" href="/besoins/restants"><i class="bi bi-exclamation-triangle"></i> Besoins Restants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/recap"><i class="bi bi-clipboard-data"></i> Récapitulatif</a></li>
                            <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        </ul>
                    </li>
                    
                    <!-- Configuration -->
                    <li class="nav-item">
                        <a class="nav-link" href="/configuration"><i class="bi bi-gear"></i> Configuration</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm px-3" href="/recap">
                            <i class="bi bi-bar-chart-fill"></i> Récap Ajax
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main>
