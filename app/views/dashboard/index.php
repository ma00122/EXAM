<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-speedometer2 text-primary"></i> Dashboard BNGRC
            <small class="text-muted fs-6">Suivi des Besoins et Dons</small>
        </h1>
        <div>
            <a href="/dons" class="btn btn-success me-2">
                <i class="bi bi-gift"></i> Dons
            </a>
            <a href="/simulation" class="btn btn-info">
                <i class="bi bi-play-circle"></i> Simulation
            </a>
        </div>
    </div>

    <!-- Cartes statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-geo-alt fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_villes'] ?></h2>
                    <p class="mb-0">Villes</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_besoins'] ?></h2>
                    <p class="mb-0">Besoins</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gift fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_dons'] ?></h2>
                    <p class="mb-0">Dons</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_attributions'] ?></h2>
                    <p class="mb-0">Attributions</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-<?= $statsGlobales['pourcentage_couverture'] >= 75 ? 'success' : ($statsGlobales['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?> text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['pourcentage_couverture'] ?>%</h2>
                    <p class="mb-0">Couverture</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-currency-exchange fs-1"></i>
                    <h2 class="mt-2"><?= number_format($statsGlobales['valeur_totale'], 0, ',', ' ') ?></h2>
                    <p class="mb-0">Ar Total</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé quantités -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-clipboard-data"></i> Quantités Demandées
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-warning"><?= number_format($statsGlobales['quantite_demandee']) ?></h1>
                    <p class="text-muted">Unités totales demandées</p>
                    <hr>
                    <p class="mb-0">
                        Valeur: <strong><?= number_format($statsGlobales['valeur_totale'], 0, ',', ' ') ?> Ar</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-check-circle"></i> Quantités Reçues
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-success"><?= number_format($statsGlobales['quantite_recue']) ?></h1>
                    <p class="text-muted">Unités attribuées aux besoins</p>
                    <hr>
                    <p class="mb-0">
                        Valeur: <strong><?= number_format($statsGlobales['valeur_recue'], 0, ',', ' ') ?> Ar</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i> Quantités Restantes
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-danger"><?= number_format($statsGlobales['quantite_restante']) ?></h1>
                    <p class="text-muted">Unités encore à couvrir</p>
                    <hr>
                    <p class="mb-0">
                        Valeur: <strong><?= number_format($statsGlobales['valeur_restante'], 0, ',', ' ') ?> Ar</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de progression globale -->
    <div class="card mb-4">
        <div class="card-body">
            <h5><i class="bi bi-bar-chart"></i> Progression globale de couverture</h5>
            <div class="progress" style="height: 40px;">
                <div class="progress-bar bg-success" 
                     role="progressbar" 
                     style="width: <?= $statsGlobales['pourcentage_couverture'] ?>%"
                     aria-valuenow="<?= $statsGlobales['pourcentage_couverture'] ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= $statsGlobales['pourcentage_couverture'] ?>% des besoins couverts
                </div>
                <div class="progress-bar bg-danger" 
                     role="progressbar" 
                     style="width: <?= 100 - $statsGlobales['pourcentage_couverture'] ?>%">
                    <?= 100 - $statsGlobales['pourcentage_couverture'] ?>% restants
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tableau par ville -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-geo-alt"></i> Situation par Ville
                    <small class="float-end">(triée par priorité)</small>
                </div>
                <?php if (empty($statsParVille)): ?>
                <div class="card-body">
                    <div class="alert alert-info mb-0">Aucune donnée disponible.</div>
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Ville</th>
                                <th class="text-center">Demandée</th>
                                <th class="text-center">Reçue</th>
                                <th class="text-center">Restante</th>
                                <th class="text-center">Couverture</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsParVille as $stat): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($stat['ville_nom']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($stat['ville_region']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= number_format($stat['quantite_demandee']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($stat['quantite_recue']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($stat['quantite_restante'] > 0): ?>
                                    <span class="badge bg-danger"><?= number_format($stat['quantite_restante']) ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px; min-width: 80px;">
                                        <div class="progress-bar bg-<?= $stat['pourcentage_couverture'] >= 75 ? 'success' : ($stat['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $stat['pourcentage_couverture'] ?>%">
                                            <?= $stat['pourcentage_couverture'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="/bngrc/dashboard/ville/<?= $stat['ville_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tableau par produit -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-box-seam"></i> Situation par Produit
                </div>
                <?php if (empty($statsParProduit)): ?>
                <div class="card-body">
                    <div class="alert alert-info mb-0">Aucune donnée disponible.</div>
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Besoins</th>
                                <th class="text-center">Demandée</th>
                                <th class="text-center">Reçue</th>
                                <th class="text-center">Couverture</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsParProduit as $stat): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-box text-info"></i>
                                    <strong><?= htmlspecialchars($stat['produit']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= $stat['nombre_besoins'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= number_format($stat['quantite_demandee']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($stat['quantite_recue']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px; min-width: 80px;">
                                        <div class="progress-bar bg-<?= $stat['pourcentage_couverture'] >= 75 ? 'success' : ($stat['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $stat['pourcentage_couverture'] ?>%">
                                            <?= $stat['pourcentage_couverture'] ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Liste des besoins -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-list-check"></i> Détail des Besoins
            <span class="badge bg-dark float-end"><?= count($besoins) ?></span>
        </div>
        <?php if (empty($besoins)): ?>
        <div class="card-body">
            <div class="alert alert-warning mb-0">
                Aucun besoin enregistré. <a href="/besoins/create">Ajouter un besoin</a>
            </div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th class="text-center">Qté Demandée</th>
                        <th class="text-center">Qté Reçue</th>
                        <th class="text-center">Qté Restante</th>
                        <th class="text-end">Valeur Totale</th>
                        <th class="text-center">Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins as $besoin): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $besoin['id'] ?></span></td>
                        <td>
                            <a href="/bngrc/dashboard/ville/<?= $besoin['ville_id'] ?>">
                                <?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?>
                            </a>
                        </td>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
