<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-play-circle-fill text-info"></i> Simulation d'Attribution
                </h1>
                <div>
                    <a href="/dons" class="btn btn-success me-2">
                        <i class="bi bi-gift"></i> Dons
                    </a>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages flash -->
            <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_dons'] ?></h3>
                            <p class="mb-0">Dons</p>
                            <small><?= number_format($stats['total_dons']) ?> unités</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_besoins'] ?? 0 ?></h3>
                            <p class="mb-0">Besoins</p>
                            <small><?= number_format($stats['total_besoins'] ?? 0) ?> unités</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_attributions'] ?></h3>
                            <p class="mb-0">Attributions</p>
                            <small><?= number_format($stats['total_attribue']) ?> unités attribuées</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-<?= $stats['pourcentage_attribue'] >= 75 ? 'success' : ($stats['pourcentage_attribue'] >= 50 ? 'warning' : 'secondary') ?> text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['pourcentage_attribue'] ?>%</h3>
                            <p class="mb-0">Attribué</p>
                            <small><?= number_format($stats['total_restant']) ?> unités restantes</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions de simulation -->
            <?php $canSimulate = !empty($besoins) && !empty($dons); ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-gear"></i> Actions
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5>Algorithme de simulation</h5>
                            <?php if ($canSimulate): ?>
                            <p class="text-success mb-0">
                                <i class="bi bi-check-circle"></i>
                                <strong><?= count($dons) ?> don(s)</strong> et <strong><?= count($besoins) ?> besoin(s)</strong> prêts pour la simulation.
                            </p>
                            <?php else: ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                La simulation nécessite des <a href="/dons"><strong>dons</strong></a> et des <a href="/besoins"><strong>besoins</strong></a> pour fonctionner.
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Bouton SIMULER (preview Sedra) -->
                            <form action="/simulation/simuler" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-<?= $canSimulate ? 'info' : 'secondary' ?> me-2" 
                                        <?= !$canSimulate ? 'disabled' : '' ?>>
                                    <i class="bi bi-eye"></i> Simuler (Preview)
                                </button>
                            </form>
                            
                            <!-- Bouton exécuter ancien -->
                            <form action="/simulation/run" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-<?= $canSimulate ? 'primary' : 'secondary' ?> me-2" 
                                        <?= !$canSimulate ? 'disabled' : '' ?>
                                        onclick="return confirm('Exécuter la simulation d\'attribution ?')">
                                    <i class="bi bi-play-fill"></i> Exécuter
                                </button>
                            </form>
                            <?php if ($stats['nombre_attributions'] > 0): ?>
                            <form action="/simulation/reset" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-outline-danger"
                                        onclick="return confirm('Réinitialiser toutes les attributions ?')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <!-- Lien vers récap Ajax -->
                            <a href="/recap" class="btn btn-lg btn-outline-primary ms-2">
                                <i class="bi bi-bar-chart"></i> Récap
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des dons -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Dons disponibles
                    <span class="badge bg-light text-dark float-end"><?= count($dons) ?></span>
                </div>
                <?php if (empty($dons)): ?>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucun don enregistré. <a href="/dons/create">Ajouter un don</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dons as $don): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $don['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-box-seam text-success"></i>
                                    <?= htmlspecialchars($don['type_produit']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($don['quantite']) ?></span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Liste des besoins -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-box-seam"></i> Besoins des villes
                    <span class="badge bg-dark float-end"><?= count($besoins) ?></span>
                </div>
                <?php if (empty($besoins)): ?>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucun besoin enregistré. <a href="/besoins/create">Ajouter un besoin</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th>Ville</th>
                                <th class="text-center">Quantité</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besoins as $besoin): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $besoin['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-box text-warning"></i>
                                    <?= htmlspecialchars($besoin['produit']) ?>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= number_format($besoin['quantite']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($besoin['type_nom'] ?? 'N/A') ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Résultats des attributions -->
            <?php if (!empty($attributions)): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-arrow-left-right"></i> Attributions effectuées
                    <span class="badge bg-light text-dark float-end"><?= count($attributions) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Don (Produit)</th>
                                <th>→</th>
                                <th>Besoin (Ville)</th>
                                <th class="text-center">Quantité attribuée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attributions as $attr): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $attr['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-gift text-success"></i>
                                    <a href="/dons/<?= $attr['don_id'] ?>">
                                        <?= htmlspecialchars($attr['type_produit']) ?>
                                    </a>
                                    <small class="text-muted">(Don #<?= $attr['don_id'] ?>)</small>
                                </td>
                                <td><i class="bi bi-arrow-right text-info fs-5"></i></td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong><?= htmlspecialchars($attr['ville_nom'] ?? 'Ville #' . $attr['ville_id']) ?></strong>
                                    <small class="text-muted">(Besoin #<?= $attr['besoin_id'] ?>)</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6"><?= number_format($attr['quantite_attribuee']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Explication de l'algorithme -->
            <div class="card mt-4 border-secondary">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Comment fonctionne l'algorithme ?
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Les <strong>dons</strong> sont triés par date de saisie (du plus ancien au plus récent)</li>
                        <li>Les <strong>besoins</strong> sont également triés par date de création</li>
                        <li>Pour chaque don, l'algorithme recherche les besoins correspondants (<strong>même produit</strong>)</li>
                        <li>La quantité attribuée = <code>min(don disponible, besoin restant)</code></li>
                        <li>L'attribution est enregistrée et les quantités sont mises à jour</li>
                        <li>Le processus continue jusqu'à épuisement du don ou des besoins</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
