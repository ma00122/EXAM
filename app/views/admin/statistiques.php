<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Statistiques</h1>
</div>

<!-- Statistiques principales -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card primary h-100">
            <div class="card-body text-center">
                <i class="bi bi-people fs-1 text-primary mb-3"></i>
                <h2 class="display-4"><?= $stats['total_users'] ?></h2>
                <h5 class="text-muted">Utilisateurs inscrits</h5>
                <hr>
                <p class="mb-0">
                    <span class="badge bg-secondary"><?= $stats['total_users_reguliers'] ?> utilisateurs</span>
                    <span class="badge bg-warning text-dark"><?= $stats['total_admins'] ?> administrateurs</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card success h-100">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right fs-1 text-success mb-3"></i>
                <h2 class="display-4"><?= $stats['total_echanges_acceptes'] ?></h2>
                <h5 class="text-muted">Échanges effectués</h5>
                <hr>
                <p class="mb-0 text-success">
                    <i class="bi bi-check-circle"></i> Échanges validés et complétés
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card stat-card warning h-100">
            <div class="card-body text-center">
                <i class="bi bi-box fs-1 text-warning mb-3"></i>
                <h2 class="display-4"><?= $stats['total_objets'] ?></h2>
                <h5 class="text-muted">Objets en ligne</h5>
                <hr>
                <p class="mb-0">
                    Répartis dans <strong><?= $stats['total_categories'] ?></strong> catégories
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Résumé -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-table"></i> Résumé des données</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Indicateur</th>
                    <th>Valeur</th>
                    <th>Détail</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="bi bi-people text-primary"></i> Nombre total d'utilisateurs</td>
                    <td><strong><?= $stats['total_users'] ?></strong></td>
                    <td><?= $stats['total_users_reguliers'] ?> utilisateurs + <?= $stats['total_admins'] ?> admins</td>
                </tr>
                <tr>
                    <td><i class="bi bi-arrow-left-right text-success"></i> Nombre d'échanges effectués</td>
                    <td><strong><?= $stats['total_echanges_acceptes'] ?></strong></td>
                    <td>Échanges acceptés et complétés</td>
                </tr>
                <tr>
                    <td><i class="bi bi-box text-warning"></i> Nombre d'objets</td>
                    <td><strong><?= $stats['total_objets'] ?></strong></td>
                    <td>Objets mis en ligne par les utilisateurs</td>
                </tr>
                <tr>
                    <td><i class="bi bi-tags text-info"></i> Nombre de catégories</td>
                    <td><strong><?= $stats['total_categories'] ?></strong></td>
                    <td>Catégories d'objets disponibles</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>
