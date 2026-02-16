<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-gift-fill text-success"></i> Gestion des Dons
                    <span class="badge bg-success"><?= count($dons) ?></span>
                </h1>
                <div>
                    <a href="/simulation" class="btn btn-info me-2">
                        <i class="bi bi-play-circle"></i> Simulation
                    </a>
                    <a href="/bngrc/dashboard" class="btn btn-primary me-2">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="/dons/create" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Ajouter un don
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

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="/dons" method="GET" class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Filtrer par produit</label>
                            <select name="produit" class="form-select">
                                <option value="">-- Tous les produits --</option>
                                <?php foreach ($typesProduits as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" 
                                        <?= $filterProduit === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Filtrer
                            </button>
                            <?php if (!empty($filterProduit)): ?>
                            <a href="/dons" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Effacer
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tableau des dons -->
            <?php if (empty($dons)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                <?php if (!empty($filterProduit)): ?>
                    Aucun don trouvé pour "<strong><?= htmlspecialchars($filterProduit) ?></strong>".
                <?php else: ?>
                    Aucun don enregistré. <a href="/dons/create">Ajouter un don</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-table"></i> Liste des dons
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="60">#ID</th>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-center">Attribuée</th>
                                <th class="text-center">Restante</th>
                                <th>Date saisie</th>
                                <th width="200" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dons as $don): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $don['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-box-seam text-success"></i>
                                    <strong><?= htmlspecialchars($don['type_produit']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= number_format($don['quantite']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($don['quantite_attribuee'] > 0): ?>
                                    <span class="badge bg-info"><?= number_format($don['quantite_attribuee']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($don['quantite_restante'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= number_format($don['quantite_restante']) ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Épuisé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y', strtotime($don['date_saisie'])) ?>
                                </td>
                                <td class="text-center">
                                    <a href="/dons/<?= $don['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/dons/edit/<?= $don['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/dons/delete/<?= $don['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce don ?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-center">
                                    <?= number_format(array_sum(array_column($dons, 'quantite'))) ?>
                                </th>
                                <th class="text-center">
                                    <?= number_format(array_sum(array_column($dons, 'quantite_attribuee'))) ?>
                                </th>
                                <th class="text-center">
                                    <?= number_format(array_sum(array_column($dons, 'quantite_restante'))) ?>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Aide -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="bi bi-info-circle"></i> Information</h5>
                    <p class="mb-0">
                        Les dons sont attribués aux besoins correspondants via la <a href="/simulation">simulation</a>.
                        Une fois attribués, les dons ne peuvent être supprimés sans réinitialiser la simulation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
