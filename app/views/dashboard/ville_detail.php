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

    <!-- Statistiques de la ville -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check fs-1"></i>
                    <h2 class="mt-2"><?= $stats['nombre_besoins'] ?></h2>
                    <p class="mb-0">Besoins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-1"></i>
                    <h2 class="mt-2"><?= number_format($stats['quantite_demandee']) ?></h2>
                    <p class="mb-0">Qté Demandée</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1"></i>
                    <h2 class="mt-2"><?= number_format($stats['quantite_recue']) ?></h2>
                    <p class="mb-0">Qté Reçue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-<?= $stats['pourcentage_couverture'] >= 75 ? 'success' : ($stats['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?> text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <h2 class="mt-2"><?= $stats['pourcentage_couverture'] ?>%</h2>
                    <p class="mb-0">Couverture</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de progression -->
    <div class="card mb-4">
        <div class="card-body">
            <h5><i class="bi bi-bar-chart"></i> Progression de couverture</h5>
            <div class="progress" style="height: 35px;">
                <div class="progress-bar bg-success" 
                     role="progressbar" 
                     style="width: <?= $stats['pourcentage_couverture'] ?>%"
                     aria-valuenow="<?= $stats['pourcentage_couverture'] ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= $stats['pourcentage_couverture'] ?>% couverts (<?= number_format($stats['quantite_recue']) ?> unités)
                </div>
                <?php if ($stats['quantite_restante'] > 0): ?>
                <div class="progress-bar bg-danger" 
                     role="progressbar" 
                     style="width: <?= 100 - $stats['pourcentage_couverture'] ?>%">
                    <?= 100 - $stats['pourcentage_couverture'] ?>% restants (<?= number_format($stats['quantite_restante']) ?> unités)
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Valeurs -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Valeur Totale Demandée</h6>
                    <h3 class="text-primary"><?= number_format($stats['valeur_totale'], 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Valeur Reçue</h6>
                    <h3 class="text-success"><?= number_format($stats['valeur_recue'], 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Valeur Restante</h6>
                    <h3 class="text-danger"><?= number_format($stats['valeur_restante'], 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des besoins -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-list-check"></i> Besoins de <?= htmlspecialchars($ville['nom']) ?>
            <span class="badge bg-dark float-end"><?= count($besoins) ?></span>
        </div>
        <?php if (empty($besoins)): ?>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i>
                Aucun besoin enregistré pour cette ville.
            </div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th class="text-center">Qté Demandée</th>
                        <th class="text-center">Qté Reçue</th>
                        <th class="text-center">Qté Restante</th>
                        <th class="text-end">Prix Unit.</th>
                        <th class="text-end">Valeur Totale</th>
                        <th class="text-center">Couverture</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $besoin): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $besoin['id'] ?></span></td>
                        <td><strong><?= htmlspecialchars($besoin['produit']) ?></strong></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($besoin['type'] ?? 'N/A') ?></span></td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark"><?= number_format($besoin['quantite']) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= number_format($besoin['quantite_recue']) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($besoin['quantite_restante'] > 0): ?>
                            <span class="badge bg-danger"><?= number_format($besoin['quantite_restante']) ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format($besoin['valeur_totale'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px; min-width: 60px;">
                                <div class="progress-bar bg-<?= $besoin['pourcentage_couverture'] >= 75 ? 'success' : ($besoin['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?>" 
                                     role="progressbar" 
                                     style="width: <?= $besoin['pourcentage_couverture'] ?>%">
                                    <?= $besoin['pourcentage_couverture'] ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <small><?= date('d/m/Y', strtotime($besoin['date_saisie'])) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="3">Total</th>
                        <th class="text-center"><?= number_format($stats['quantite_demandee']) ?></th>
                        <th class="text-center"><?= number_format($stats['quantite_recue']) ?></th>
                        <th class="text-center"><?= number_format($stats['quantite_restante']) ?></th>
                        <th></th>
                        <th class="text-end"><?= number_format($stats['valeur_totale'], 0, ',', ' ') ?> Ar</th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
