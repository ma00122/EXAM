<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-list-check"></i> Besoins Restants</h1>
        <div>
            <a href="/besoins" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Liste complète
            </a>
            <a href="/recap" class="btn btn-primary">
                <i class="bi bi-clipboard-data"></i> Récapitulatif
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['montant_total_global'] ?? 0, 0, ',', ' ') ?></h3>
                    <p class="mb-0">Montant Total (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['montant_satisfait_global'] ?? 0, 0, ',', ' ') ?></h3>
                    <p class="mb-0">Montant Satisfait (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['montant_restant_global'] ?? 0, 0, ',', ' ') ?></h3>
                    <p class="mb-0">Montant Restant (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?= $frais_pourcentage ?>%</h3>
                    <p class="mb-0">Frais d'achat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre par ville -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/besoins/restants" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filtrer par ville</label>
                    <select name="ville_id" class="form-select">
                        <option value="">Toutes les villes</option>
                        <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>" <?= $selected_ville == $ville['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des besoins restants -->
    <div class="card">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Besoins non satisfaits</h5>
            <span class="badge bg-dark"><?= count($besoins_restants) ?> besoin(s)</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($besoins_restants)): ?>
                <div class="text-center py-5 text-success">
                    <i class="bi bi-check-circle display-1"></i>
                    <h4 class="mt-3">Tous les besoins sont satisfaits !</h4>
                </div>
            <?php else: ?>
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Ville</th>
                            <th>Produit</th>
                            <th class="text-center">Qté Demandée</th>
                            <th class="text-center">Qté Reçue</th>
                            <th class="text-center">Qté Restante</th>
                            <th class="text-end">Prix Unit.</th>
                            <th class="text-end">Montant Restant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($besoins_restants as $besoin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($besoin['produit']) ?></td>
                                <td class="text-center"><?= number_format($besoin['quantite_demandee'] ?? $besoin['quantite'], 0, ',', ' ') ?></td>
                                <td class="text-center text-success"><?= number_format($besoin['quantite_recue'] ?? 0, 0, ',', ' ') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= number_format($besoin['quantite_restante'], 0, ',', ' ') ?></span>
                                </td>
                                <td class="text-end"><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-end">
                                    <strong class="text-danger"><?= number_format($besoin['montant_restant'], 0, ',', ' ') ?> Ar</strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="4"><strong>TOTAL</strong></td>
                            <td class="text-center">
                                <strong><?= number_format(array_sum(array_column($besoins_restants, 'quantite_restante')), 0, ',', ' ') ?></strong>
                            </td>
                            <td></td>
                            <td class="text-end">
                                <strong><?= number_format(array_sum(array_column($besoins_restants, 'montant_restant')), 0, ',', ' ') ?> Ar</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
