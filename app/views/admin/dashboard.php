<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-speedometer2"></i> Tableau de bord</h1>
    <span class="text-muted">Bienvenue, <?= call_user_func($e, $_SESSION['user']['prenom'] ?? 'Admin') ?> !</span>
</div>

<!-- Cartes statistiques -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Utilisateurs inscrits</h6>
                        <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                    </div>
                    <i class="bi bi-people fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Échanges effectués</h6>
                        <h2 class="mb-0"><?= $stats['total_echanges'] ?></h2>
                    </div>
                    <i class="bi bi-arrow-left-right fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Objets en ligne</h6>
                        <h2 class="mb-0"><?= $stats['total_objets'] ?></h2>
                    </div>
                    <i class="bi bi-box fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Catégories</h6>
                        <h2 class="mb-0"><?= $stats['total_categories'] ?></h2>
                    </div>
                    <i class="bi bi-tags fs-1 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liens rapides -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="/admin/categories" class="list-group-item list-group-item-action">
                        <i class="bi bi-tags me-2"></i> Gérer les catégories
                    </a>
                    <a href="/admin/categories/nouveau" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle me-2"></i> Ajouter une catégorie
                    </a>
                    <a href="/admin/statistiques" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i> Voir les statistiques détaillées
                    </a>
                    <a href="/" target="_blank" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Voir le site public
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informations</h5>
            </div>
            <div class="card-body">
                <p><strong>Projet :</strong> Takalo-Takalo</p>
                <p><strong>Description :</strong> Plateforme d'échange d'objets</p>
                <p><strong>Version :</strong> Révision Février 2026 – P18/P5DS</p>
                <hr>
                <p class="mb-0"><strong>Équipe de développement :</strong></p>
                <ul class="mb-0">
                    <li>Membre 1 - ETU001234</li>
                    <li>Membre 2 - ETU001235</li>
                    <li>Membre 3 - ETU001236</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>
