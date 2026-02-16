<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-exclamation-triangle text-warning"></i> Besoins Restants</h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="actualiserDonnees()">
                <i class="bi bi-arrow-clockwise" id="refresh-icon"></i> Actualiser (Ajax)
            </button>
            <a href="/besoins" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-list"></i> Tous les besoins
            </a>
            <a href="/recap" class="btn btn-outline-info ms-2">
                <i class="bi bi-clipboard-data"></i> Récap Global
            </a>
        </div>
    </div>

    <!-- Messages flash -->
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

    <!-- Timestamp -->
    <div class="alert alert-info mb-4">
        <i class="bi bi-clock"></i> Dernière actualisation: <strong id="last-update"><?= date('d/m/Y H:i:s') ?></strong>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 id="montant-total"><?= number_format($stats['montant_total_global'] ?? 0, 0, ',', ' ') ?></h4>
                    <p class="mb-0">Montant Total (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 id="montant-satisfait"><?= number_format($stats['montant_satisfait_global'] ?? 0, 0, ',', ' ') ?></h4>
                    <p class="mb-0">Montant Satisfait (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 id="montant-restant"><?= number_format($stats['montant_restant_global'] ?? 0, 0, ',', ' ') ?></h4>
                    <p class="mb-0">Montant Restant (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <?php 
                    $total = $stats['montant_total_global'] ?? 0;
                    $satisfait = $stats['montant_satisfait_global'] ?? 0;
                    $taux = $total > 0 ? round(($satisfait / $total) * 100, 1) : 0;
                    ?>
                    <h4 id="taux-satisfaction"><?= $taux ?>%</h4>
                    <p class="mb-0">Taux de Satisfaction</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre par ville -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/besoins/restants" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Filtrer par ville:</label>
                    <select name="ville_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Toutes les villes --</option>
                        <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>" <?= $selected_ville == $ville['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom']) ?> (<?= htmlspecialchars($ville['region'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Info frais d'achat:</label>
                    <p class="mb-0">
                        <span class="badge bg-secondary">Frais: <?= $frais_pourcentage ?? 5 ?>%</span>
                        <a href="/configuration" class="ms-2">Configurer</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des besoins restants -->
    <div class="card">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Liste des besoins non satisfaits</h5>
            <span class="badge bg-dark" id="count-besoins"><?= count($besoins_restants ?? []) ?> besoin(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ville</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th class="text-end">Qté Demandée</th>
                        <th class="text-end">Qté Reçue</th>
                        <th class="text-end">Qté Restante</th>
                        <th class="text-end">Prix Unitaire</th>
                        <th class="text-end">Montant Restant</th>
                    </tr>
                </thead>
                <tbody id="besoins-table">
                    <?php if (empty($besoins_restants)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-success py-4">
                                <i class="bi bi-check-circle fs-1"></i>
                                <p class="mt-2 mb-0">Tous les besoins sont satisfaits !</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($besoins_restants as $besoin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($besoin['ville_nom'] ?? '') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($besoin['region'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($besoin['produit'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($besoin['type_nom'] ?? '') == 'Nature' ? 'success' : (($besoin['type_nom'] ?? '') == 'Matériaux' ? 'warning' : 'info') ?>">
                                        <?= htmlspecialchars($besoin['type_nom'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= number_format($besoin['quantite'] ?? 0, 0, ',', ' ') ?></td>
                                <td class="text-end text-success"><?= number_format($besoin['quantite_satisfaite'] ?? 0, 0, ',', ' ') ?></td>
                                <td class="text-end text-danger">
                                    <strong><?= number_format($besoin['quantite_restante'] ?? ($besoin['quantite'] - ($besoin['quantite_satisfaite'] ?? 0)), 0, ',', ' ') ?></strong>
                                </td>
                                <td class="text-end"><?= number_format($besoin['prix_unitaire'] ?? 0, 0, ',', ' ') ?> Ar</td>
                                <td class="text-end text-danger">
                                    <strong><?= number_format($besoin['montant_restant'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($besoins_restants)): ?>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="5">TOTAL</th>
                        <th class="text-end">
                            <?= number_format($stats['quantite_totale_restante'] ?? 0, 0, ',', ' ') ?>
                        </th>
                        <th></th>
                        <th class="text-end text-danger">
                            <strong><?= number_format($stats['montant_restant_global'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Liens utiles -->
    <div class="mt-4 text-center">
        <a href="/simulation" class="btn btn-success me-2">
            <i class="bi bi-play-circle"></i> Lancer Simulation
        </a>
        <a href="/achats" class="btn btn-primary me-2">
            <i class="bi bi-cart-check"></i> Voir Achats
        </a>
        <a href="/dons-argent" class="btn btn-info">
            <i class="bi bi-cash-stack"></i> Dons Argent
        </a>
    </div>
</div>

<script>
/**
 * Actualiser les données via Ajax
 */
function actualiserDonnees() {
    const refreshIcon = document.getElementById('refresh-icon');
    refreshIcon.classList.add('spin');
    
    fetch('/recap/data')
        .then(response => response.json())
        .then(data => {
            if (data) {
                // Mettre à jour les montants
                document.getElementById('montant-total').textContent = formatNumber(data.total);
                document.getElementById('montant-satisfait').textContent = formatNumber(data.satisfait);
                document.getElementById('montant-restant').textContent = formatNumber(data.restant);
                
                const total = data.total || 0;
                const satisfait = data.satisfait || 0;
                const taux = total > 0 ? Math.round((satisfait / total) * 1000) / 10 : 0;
                document.getElementById('taux-satisfaction').textContent = taux + '%';
                document.getElementById('last-update').textContent = new Date().toLocaleString('fr-FR');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'actualisation');
        })
        .finally(() => {
            refreshIcon.classList.remove('spin');
        });
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spin {
    animation: spin 1s linear infinite;
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
