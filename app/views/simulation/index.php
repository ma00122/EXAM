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
                    <a href="/bngrc/dashboard" class="btn btn-primary">
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
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_dons'] ?></h3>
                            <p class="mb-0">Dons</p>
                            <small><?= number_format($stats['total_dons']) ?> unités</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_attributions'] ?></h3>
                            <p class="mb-0">Attributions</p>
                            <small><?= number_format($stats['total_attribue']) ?> unités attribuées</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
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
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-gear"></i> Actions
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5>Algorithme de simulation</h5>
                            <p class="text-muted mb-0">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                La simulation d'attribution nécessite le <strong>module Besoins</strong> pour fonctionner.
                                Ce module permet de définir les besoins des villes auxquels les dons seront attribués.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <form action="/simulation/run" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-secondary me-2" disabled>
                                    <i class="bi bi-play-fill"></i> Exécuter la simulation
                                </button>
                            </form>
                            <?php if ($stats['nombre_attributions'] > 0): ?>
                            <form action="/simulation/reset" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-outline-danger"
                                        onclick="return confirm('Réinitialiser toutes les attributions ?')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                                </button>
                            </form>
                            <?php endif; ?>
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
                                <th>Don</th>
                                <th>→</th>
                                <th>Besoin</th>
                                <th class="text-center">Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attributions as $attr): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $attr['id'] ?></span></td>
                                <td>
                                    <a href="/dons/<?= $attr['don_id'] ?>">
                                        Don #<?= $attr['don_id'] ?> - <?= htmlspecialchars($attr['type_produit']) ?>
                                    </a>
                                </td>
                                <td><i class="bi bi-arrow-right text-info"></i></td>
                                <td>Besoin #<?= $attr['besoin_id'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($attr['quantite_attribuee']) ?></span>
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
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Module Besoins requis</strong> - La simulation nécessite la définition des besoins par ville pour fonctionner.
                    </div>
                    <ol class="mb-0">
                        <li>Les dons sont triés par date de saisie (du plus ancien au plus récent)</li>
                        <li>Les besoins sont également triés par date de saisie</li>
                        <li>Pour chaque don, l'algorithme recherche les besoins correspondants (même produit)</li>
                        <li>La quantité attribuée = minimum(don disponible, besoin restant)</li>
                        <li>L'attribution est enregistrée et les quantités sont mises à jour</li>
                        <li>Le processus continue jusqu'à épuisement du don ou des besoins</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
