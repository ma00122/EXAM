<?php include __DIR__ . '/../layout/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1><i class="bi bi-shield-check"></i> BNGRC - Gestion des Catastrophes</h1>
        <p class="lead">Bureau National de Gestion des Risques et Catastrophes</p>
        <p>Système de gestion des dons, besoins et attributions pour les villes sinistrées</p>
        <div class="mt-4">
            <a href="/simulation" class="btn btn-light btn-lg me-2">
                <i class="bi bi-play-circle"></i> Simulation
            </a>
            <a href="/recap" class="btn btn-outline-light btn-lg">
                <i class="bi bi-clipboard-data"></i> Récapitulatif
            </a>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Statistiques rapides -->
    <section class="mb-5">
        <h2 class="mb-4"><i class="bi bi-bar-chart-fill"></i> Aperçu Général</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-geo-alt fs-1 mb-2"></i>
                        <h3>Villes</h3>
                        <p class="mb-0">Gérer les zones sinistrées</p>
                        <a href="/villes" class="btn btn-light btn-sm mt-3">Accéder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-list-check fs-1 mb-2"></i>
                        <h3>Besoins</h3>
                        <p class="mb-0">Besoins des villes</p>
                        <a href="/besoins" class="btn btn-dark btn-sm mt-3">Accéder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-gift fs-1 mb-2"></i>
                        <h3>Dons</h3>
                        <p class="mb-0">Dons nature & argent</p>
                        <a href="/dons" class="btn btn-light btn-sm mt-3">Nature</a>
                        <a href="/dons-argent" class="btn btn-outline-light btn-sm mt-3">Argent</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-left-right fs-1 mb-2"></i>
                        <h3>Attributions</h3>
                        <p class="mb-0">Distribution des dons</p>
                        <a href="/attributions" class="btn btn-light btn-sm mt-3">Accéder</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions principales -->
    <section class="mb-5">
        <h2 class="mb-4"><i class="bi bi-lightning-charge"></i> Actions Rapides</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-primary h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-play-circle"></i> Simulation
                    </div>
                    <div class="card-body">
                        <p>Simuler l'attribution des dons aux besoins des villes. Preview avant validation.</p>
                        <a href="/simulation" class="btn btn-primary">
                            <i class="bi bi-play-fill"></i> Lancer simulation
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success h-100">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cart-check"></i> Achats
                    </div>
                    <div class="card-body">
                        <p>Gérer les achats effectués avec les dons en argent. Frais inclus.</p>
                        <a href="/achats" class="btn btn-success">
                            <i class="bi bi-cart-plus"></i> Voir les achats
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info h-100">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-clipboard-data"></i> Récapitulatif
                    </div>
                    <div class="card-body">
                        <p>Vue globale des besoins, dons et taux de satisfaction. Ajax temps réel.</p>
                        <a href="/recap" class="btn btn-info text-white">
                            <i class="bi bi-bar-chart"></i> Voir récap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Configuration -->
    <section>
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-gear"></i> Configuration Système
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-0">Configurer les paramètres système : pourcentage de frais d'achat, montants minimums, etc.</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/configuration" class="btn btn-secondary">
                            <i class="bi bi-gear-fill"></i> Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
