<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BNGRC') ?> - Gestion des Catastrophes</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bngrc-primary: #1e3a5f;
            --bngrc-secondary: #2c5282;
            --bngrc-accent: #3b82f6;
            --bngrc-success: #16a34a;
            --bngrc-warning: #f59e0b;
            --bngrc-danger: #dc2626;
            --bngrc-bg: #f5f7fb;
            --bngrc-card: #ffffff;
            --bngrc-text: #0f172a;
            --bngrc-muted: #64748b;
        }

        body {
            background-color: var(--bngrc-bg);
            color: var(--bngrc-text);
        }

        .navbar-brand { font-weight: 700; letter-spacing: 0.3px; }
        .navbar-bngrc {
            background: linear-gradient(135deg, var(--bngrc-primary) 0%, var(--bngrc-secondary) 100%) !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.2);
        }
        .nav-link { font-weight: 500; }
        .nav-link:hover { color: #e2e8f0 !important; }

        .dropdown-menu {
            border: 0;
            box-shadow: 0 20px 40px rgba(2, 8, 23, 0.15);
            border-radius: 14px;
            padding: 0.6rem;
        }
        .dropdown-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--bngrc-muted);
        }
        .dropdown-item {
            border-radius: 10px;
            padding: 0.45rem 0.75rem;
        }
        .dropdown-item:hover {
            background-color: #eef2ff;
        }

        .card-bngrc { transition: transform 0.2s, box-shadow 0.2s; border: 0; border-radius: 16px; }
        .card-bngrc:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(2, 8, 23, 0.12); }
        .badge-statut-disponible { background-color: var(--bngrc-success); }
        .badge-statut-en_cours { background-color: var(--bngrc-warning); color: #111827; }
        .badge-statut-termine { background-color: #64748b; }
        .montant-total { font-size: 1.1em; font-weight: 700; color: var(--bngrc-success); }

        .hero-section {
            background: linear-gradient(135deg, var(--bngrc-primary) 0%, var(--bngrc-secondary) 100%);
            color: white;
            padding: 3rem 0;
        }

        main.app-main {
            min-height: calc(100vh - 260px);
            padding-top: 1.25rem;
            padding-bottom: 2rem;
        }

        footer { background-color: var(--bngrc-primary); color: white; }
        footer a { color: #cbd5f5; text-decoration: none; }
        footer a:hover { color: white; }

        .btn-cta {
            border-radius: 999px;
            font-weight: 600;
        }
        .section-title {
            font-weight: 700;
            color: var(--bngrc-primary);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-bngrc sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-shield-check"></i> BNGRC
                <span class="d-none d-lg-inline-block ms-2 small opacity-75">Gestion des dons & besoins</span>
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
                            <li><h6 class="dropdown-header">Référentiels</h6></li>
                            <li><a class="dropdown-item" href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                            <li><a class="dropdown-item" href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Transactions</h6></li>
                            <li><a class="dropdown-item" href="/dons"><i class="bi bi-gift"></i> Dons Nature</a></li>
                            <li><a class="dropdown-item" href="/dons-argent"><i class="bi bi-cash-stack"></i> Dons Argent</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Suivi</h6></li>
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
                            <li><h6 class="dropdown-header">Tableaux</h6></li>
                            <li><a class="dropdown-item" href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a></li>
                            <li><a class="dropdown-item" href="/besoins/restants"><i class="bi bi-exclamation-triangle"></i> Besoins Restants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Synthèse</h6></li>
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
                        <a class="nav-link btn btn-outline-light btn-sm px-3 btn-cta" href="/recap">
                            <i class="bi bi-bar-chart-fill"></i> Récap Ajax
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="app-main">
