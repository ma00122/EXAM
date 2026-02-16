<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>
                <i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($ville['nom']) ?>
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-map"></i> Région: <?= htmlspecialchars($ville['region']) ?>
            </p>
        </div>
        <a href="/bngrc/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au Dashboard
        </a>
    </div>

    <!-- Informations de la ville -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-info-circle"></i> Informations de la ville
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-muted">Nom</h6>
                    <h4><?= htmlspecialchars($ville['nom']) ?></h4>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Région</h6>
                    <h4><?= htmlspecialchars($ville['region']) ?></h4>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Population</h6>
                    <h4><?= number_format($ville['population'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Message d'information -->
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i>
        Les statistiques détaillées des besoins pour cette ville seront disponibles après l'intégration du module Besoins.
    </div>

    <!-- Actions -->
    <div class="mt-4">
        <a href="/villes/edit/<?= $ville['id'] ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="/villes" class="btn btn-outline-primary">
            <i class="bi bi-list"></i> Toutes les villes
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
