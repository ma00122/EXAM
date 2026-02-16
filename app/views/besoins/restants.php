<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-clipboard-data"></i> Besoins Restants
            <span class="badge bg-warning text-dark"><?= count($besoins_restants ?? []) ?></span>
        </h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="btnActualiser">
                <i class="bi bi-arrow-clockwise"></i> Actualiser
            </button>
            <a href="/besoins" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Statistiques globales avec Ajax -->
    <div class="row mb-4" id="statsContainer">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6><i class="bi bi-currency-exchange"></i> Montant Total</h6>
                    <h3 id="montantTotal"><?= number_format($stats['montant_total_global'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6><i class="bi bi-check-circle"></i> Montant Satisfait</h6>
                    <h3 id="montantSatisfait"><?= number_format($stats['montant_satisfait_global'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6><i class="bi bi-hourglass-split"></i> Montant Restant</h6>
                    <h3 id="montantRestant"><?= number_format($stats['montant_restant_global'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6><i class="bi bi-percent"></i> Progression</h6>
                    <?php 
                    $progression = ($stats['montant_total_global'] ?? 0) > 0 
                        ? round((($stats['montant_satisfait_global'] ?? 0) / ($stats['montant_total_global'])) * 100, 1) 
                        : 0;
                    ?>
                    <h3 id="progression"><?= $progression ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de progression -->
    <div class="progress mb-4" style="height: 25px;">
        <div class="progress-bar bg-success" id="progressBar" role="progressbar" 
             style="width: <?= $progression ?>%;" 
             aria-valuenow="<?= $progression ?>" aria-valuemin="0" aria-valuemax="100">
            <?= $progression ?>% satisfait
        </div>
    </div>

    <!-- Filtre par ville -->
    <div class="mb-3">
        <form method="get" class="d-flex gap-2 align-items-center">
            <label for="ville_id" class="mb-0">Filtrer par ville :</label>
            <select name="ville_id" id="ville_id" class="form-select" style="width: 240px;">
                <option value="0">Toutes les villes</option>
                <?php if (!empty($villes)): foreach ($villes as $ville): ?>
                    <option value="<?= $ville['id'] ?>" <?= (!empty($selected_ville) && (int)$selected_ville === (int)$ville['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ville['nom']) ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
            <button class="btn btn-primary" type="submit">Filtrer</button>
            <a href="/besoins/restants" class="btn btn-outline-secondary">Réinitialiser</a>
        </form>
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

    <?php if (empty($besoins_restants)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> 
        <strong>Félicitations !</strong> Tous les besoins ont été satisfaits.
        <a href="/besoins" class="alert-link">Voir tous les besoins</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Ville</th>
                    <th>Type</th>
                    <th>Produit</th>
                    <th class="text-center">Qté Demandée</th>
                    <th class="text-center">Qté Reçue</th>
                    <th class="text-center">Qté Restante</th>
                    <th>Prix Unit.</th>
                    <th>Montant Total</th>
                    <th>Montant Satisfait</th>
                    <th>Montant Restant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($besoins_restants as $besoin): ?>
                <?php 
                    $quantite_demandee = $besoin['quantite'] ?? 0;
                    $quantite_recue = $besoin['quantite_satisfaite'] ?? 0;
                    $quantite_restante = $besoin['quantite_restante'] ?? ($quantite_demandee - $quantite_recue);
                    $prix_unitaire = $besoin['prix_unitaire'] ?? 0;
                    $montant_total = $besoin['montant_total'] ?? ($quantite_demandee * $prix_unitaire);
                    $montant_satisfait = $besoin['montant_satisfait'] ?? ($quantite_recue * $prix_unitaire);
                    $montant_restant = $besoin['montant_restant'] ?? ($quantite_restante * $prix_unitaire);
                    $type_nom = $besoin['type_nom'] ?? 'N/A';
                    $ville_nom = $besoin['ville_nom'] ?? 'N/A';
                    
                    // Calcul du pourcentage de satisfaction
                    $pourcentage_satisfaction = $quantite_demandee > 0 
                        ? round(($quantite_recue / $quantite_demandee) * 100, 1) 
                        : 0;
                ?>
                <tr>
                    <td><?= $besoin['id'] ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($ville_nom) ?></span></td>
                    <td>
                        <?php 
                        $badge_class = 'bg-secondary';
                        if ($type_nom === 'nature') $badge_class = 'bg-success';
                        elseif ($type_nom === 'materiaux') $badge_class = 'bg-warning text-dark';
                        elseif ($type_nom === 'argent') $badge_class = 'bg-primary';
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($type_nom) ?></span>
                    </td>
                    <td><?= htmlspecialchars($besoin['produit'] ?? '') ?></td>
                    <td class="text-center">
                        <strong><?= number_format($quantite_demandee, 0, ',', ' ') ?></strong>
                    </td>
                    <td class="text-center text-success">
                        <strong><?= number_format($quantite_recue, 0, ',', ' ') ?></strong>
                        <small class="text-muted">(<?= $pourcentage_satisfaction ?>%)</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark fs-6">
                            <?= number_format($quantite_restante, 0, ',', ' ') ?>
                        </span>
                    </td>
                    <td><?= number_format($prix_unitaire, 0, ',', ' ') ?> Ar</td>
                    <td><?= number_format($montant_total, 0, ',', ' ') ?> Ar</td>
                    <td class="text-success"><?= number_format($montant_satisfait, 0, ',', ' ') ?> Ar</td>
                    <td class="text-danger fw-bold">
                        <?= number_format($montant_restant, 0, ',', ' ') ?> Ar
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="text-end fw-bold">Totaux :</td>
                    <td class="text-center fw-bold">
                        <?php 
                        $total_demande = array_sum(array_column($besoins_restants, 'quantite'));
                        echo number_format($total_demande, 0, ',', ' ');
                        ?>
                    </td>
                    <td class="text-center fw-bold text-success">
                        <?php 
                        $total_recu = array_sum(array_column($besoins_restants, 'quantite_satisfaite'));
                        echo number_format($total_recu, 0, ',', ' ');
                        ?>
                    </td>
                    <td class="text-center fw-bold">
                        <?php 
                        $total_restant_qte = array_sum(array_map(function($b) {
                            return ($b['quantite'] ?? 0) - ($b['quantite_satisfaite'] ?? 0);
                        }, $besoins_restants));
                        echo number_format($total_restant_qte, 0, ',', ' ');
                        ?>
                    </td>
                    <td></td>
                    <td class="fw-bold">
                        <?php 
                        $total_montant = array_sum(array_map(function($b) {
                            return ($b['quantite'] ?? 0) * ($b['prix_unitaire'] ?? 0);
                        }, $besoins_restants));
                        echo number_format($total_montant, 0, ',', ' ') . ' Ar';
                        ?>
                    </td>
                    <td class="fw-bold text-success">
                        <?php 
                        $total_satisfait = array_sum(array_map(function($b) {
                            return ($b['quantite_satisfaite'] ?? 0) * ($b['prix_unitaire'] ?? 0);
                        }, $besoins_restants));
                        echo number_format($total_satisfait, 0, ',', ' ') . ' Ar';
                        ?>
                    </td>
                    <td class="fw-bold text-danger">
                        <?php 
                        $total_restant_montant = array_sum(array_map(function($b) {
                            return (($b['quantite'] ?? 0) - ($b['quantite_satisfaite'] ?? 0)) * ($b['prix_unitaire'] ?? 0);
                        }, $besoins_restants));
                        echo number_format($total_restant_montant, 0, ',', ' ') . ' Ar';
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- Informations sur les frais d'achat -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-info-circle"></i> Configuration des frais d'achat
        </div>
        <div class="card-body">
            <p>
                <strong>Frais d'achat actuel :</strong> 
                <span class="badge bg-primary fs-5" id="fraisPourcentage"><?= $frais_pourcentage ?? 5 ?>%</span>
            </p>
            <p class="text-muted mb-0">
                Ce pourcentage de frais est appliqué sur les achats effectués avec les dons en argent.
                <br>
                Formule : <code>Montant Total = Montant Produit × (1 + <?= $frais_pourcentage ?? 5 ?>%)</code>
            </p>
        </div>
    </div>
</div>

<!-- Script Ajax pour actualiser les montants -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnActualiser = document.getElementById('btnActualiser');
    
    if (btnActualiser) {
        btnActualiser.addEventListener('click', function() {
            actualiserMontants();
        });
    }
    
    function actualiserMontants() {
        // Afficher un indicateur de chargement
        btnActualiser.disabled = true;
        btnActualiser.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Chargement...';
        
        // Appel Ajax vers /recap/data
        fetch('/recap/data')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                // Mise à jour des valeurs
                document.getElementById('montantTotal').textContent = 
                    new Intl.NumberFormat('fr-FR').format(data.total || 0) + ' Ar';
                document.getElementById('montantSatisfait').textContent = 
                    new Intl.NumberFormat('fr-FR').format(data.satisfait || 0) + ' Ar';
                document.getElementById('montantRestant').textContent = 
                    new Intl.NumberFormat('fr-FR').format(data.restant || 0) + ' Ar';
                
                // Calcul et mise à jour de la progression
                const progression = data.total > 0 ? Math.round((data.satisfait / data.total) * 1000) / 10 : 0;
                document.getElementById('progression').textContent = progression + '%';
                
                // Mise à jour de la barre de progression
                const progressBar = document.getElementById('progressBar');
                progressBar.style.width = progression + '%';
                progressBar.setAttribute('aria-valuenow', progression);
                progressBar.textContent = progression + '% satisfait';
                
                // Afficher un message de succès
                showNotification('Données actualisées avec succès !', 'success');
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur lors de l\'actualisation des données.', 'danger');
            })
            .finally(() => {
                // Réactiver le bouton
                btnActualiser.disabled = false;
                btnActualiser.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualiser';
            });
    }
    
    function showNotification(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Supprimer automatiquement après 3 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
