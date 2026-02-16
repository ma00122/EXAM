<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-speedometer2 text-primary"></i> Dashboard BNGRC
            <small class="text-muted fs-6">Suivi des Dons</small>
        </h1>
        <div>
            <a href="/dons" class="btn btn-success me-2">
                <i class="bi bi-gift"></i> Dons
            </a>
            <a href="/villes" class="btn btn-primary me-2">
                <i class="bi bi-geo-alt"></i> Villes
            </a>
            <a href="/simulation" class="btn btn-info">
                <i class="bi bi-play-circle"></i> Simulation
            </a>
        </div>
    </div>

    <!-- Cartes statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-geo-alt fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_villes'] ?></h2>
                    <p class="mb-0">Villes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gift fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_dons'] ?></h2>
                    <p class="mb-0">Dons</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['nombre_attributions'] ?></h2>
                    <p class="mb-0">Attributions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-<?= $statsGlobales['pourcentage_attribue'] >= 75 ? 'success' : ($statsGlobales['pourcentage_attribue'] >= 50 ? 'warning' : 'danger') ?> text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <h2 class="mt-2"><?= $statsGlobales['pourcentage_attribue'] ?>%</h2>
                    <p class="mb-0">Dons Attribués</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé quantités dons -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Total Dons
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-success"><?= number_format($statsGlobales['total_dons']) ?></h1>
                    <p class="text-muted">Unités totales données</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-check-circle"></i> Dons Attribués
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-info"><?= number_format($statsGlobales['dons_attribues']) ?></h1>
                    <p class="text-muted">Unités attribuées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-hourglass-split"></i> Dons Disponibles
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-warning"><?= number_format($statsGlobales['dons_restants']) ?></h1>
                    <p class="text-muted">Unités encore disponibles</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de progression globale -->
    <div class="card mb-4">
        <div class="card-body">
            <h5><i class="bi bi-bar-chart"></i> Progression d'attribution des dons</h5>
            <div class="progress" style="height: 40px;">
                <div class="progress-bar bg-info" 
                     role="progressbar" 
                     style="width: <?= $statsGlobales['pourcentage_attribue'] ?>%"
                     aria-valuenow="<?= $statsGlobales['pourcentage_attribue'] ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= $statsGlobales['pourcentage_attribue'] ?>% des dons attribués
                </div>
                <div class="progress-bar bg-warning" 
                     role="progressbar" 
                     style="width: <?= 100 - $statsGlobales['pourcentage_attribue'] ?>%">
                    <?= 100 - $statsGlobales['pourcentage_attribue'] ?>% disponibles
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tableau par ville -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-geo-alt"></i> Liste des Villes
                </div>
                <?php if (empty($statsParVille)): ?>
                <div class="card-body">
                    <div class="alert alert-info mb-0">Aucune ville enregistrée. <a href="/villes/create">Ajouter une ville</a></div>
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Ville</th>
                                <th>Région</th>
                                <th class="text-center">Population</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsParVille as $stat): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($stat['ville_nom']) ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($stat['ville_region']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= number_format($stat['population']) ?></span>
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
                <div class="card-header bg-success text-white">
                    <i class="bi bi-box-seam"></i> Dons par Produit
                </div>
                <?php if (empty($statsParProduit)): ?>
                <div class="card-body">
                    <div class="alert alert-info mb-0">Aucun don enregistré. <a href="/dons/create">Ajouter un don</a></div>
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Nb Dons</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-center">Attribué</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsParProduit as $stat): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-box text-success"></i>
                                    <strong><?= htmlspecialchars($stat['produit']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= $stat['nombre_dons'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($stat['quantite_totale']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($stat['quantite_attribuee']) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px; min-width: 80px;">
                                        <div class="progress-bar bg-<?= $stat['pourcentage_attribue'] >= 75 ? 'success' : ($stat['pourcentage_attribue'] >= 50 ? 'warning' : 'info') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $stat['pourcentage_attribue'] ?>%">
                                            <?= $stat['pourcentage_attribue'] ?>%
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

    <!-- Liste des dons -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-list-check"></i> Détail des Dons
            <span class="badge bg-light text-dark float-end"><?= count($dons) ?></span>
        </div>
        <?php if (empty($dons)): ?>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                Aucun don enregistré. <a href="/dons/create">Ajouter un don</a>
            </div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Attribué</th>
                        <th class="text-center">Disponible</th>
                        <th class="text-center">% Attribué</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dons as $don): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $don['id'] ?></span></td>
                        <td><strong><?= htmlspecialchars($don['type_produit']) ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= number_format($don['quantite']) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= number_format($don['quantite_attribuee']) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($don['quantite_restante'] > 0): ?>
                            <span class="badge bg-warning text-dark"><?= number_format($don['quantite_restante']) ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px; min-width: 60px;">
                                <div class="progress-bar bg-<?= $don['pourcentage_attribue'] >= 75 ? 'success' : ($don['pourcentage_attribue'] >= 50 ? 'warning' : 'info') ?>" 
                                     role="progressbar" 
                                     style="width: <?= $don['pourcentage_attribue'] ?>%">
                                    <?= $don['pourcentage_attribue'] ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <small><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></small>
                        </td>
                        <td>
                            <a href="/dons/<?= $don['id'] ?>" class="btn btn-sm btn-outline-primary" title="Voir">
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

<?php include __DIR__ . '/../layout/footer.php'; ?>
