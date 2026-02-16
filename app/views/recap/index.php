<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-clipboard-data"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="actualiserRecap()">
                <i class="bi bi-arrow-clockwise" id="refresh-icon"></i> Actualiser (Ajax)
            </button>
            <a href="/simulation" class="btn btn-outline-success ms-2">
                <i class="bi bi-play-circle"></i> Simulation
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

    <!-- Timestamp dernière actualisation -->
    <div class="alert alert-info mb-4">
        <i class="bi bi-clock"></i> Dernière actualisation: <strong id="last-update"><?= date('d/m/Y H:i:s') ?></strong>
    </div>

    <!-- Cartes récapitulatives principales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart-check display-4"></i>
                    <h2 class="mt-2" id="montant-total"><?= number_format($recap['montant_total_besoins'], 0, ',', ' ') ?></h2>
                    <p class="mb-0">Montant Total Besoins (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4"></i>
                    <h2 class="mt-2" id="montant-satisfait"><?= number_format($recap['montant_satisfait'], 0, ',', ' ') ?></h2>
                    <p class="mb-0">Montant Satisfait (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle display-4"></i>
                    <h2 class="mt-2" id="montant-restant"><?= number_format($recap['montant_restant'], 0, ',', ' ') ?></h2>
                    <p class="mb-0">Montant Restant (Ar)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de progression -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5>Taux de satisfaction global</h5>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     id="progress-bar"
                     style="width: <?= $recap['taux_satisfaction'] ?>%;" 
                     aria-valuenow="<?= $recap['taux_satisfaction'] ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <span id="taux-satisfaction"><?= $recap['taux_satisfaction'] ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails quantités et montants -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-boxes"></i> Quantités</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Quantité totale demandée:</td>
                            <td class="text-end"><strong id="qty-demandee"><?= number_format($recap['quantite_totale_demandee'], 0, ',', ' ') ?></strong></td>
                        </tr>
                        <tr>
                            <td>Quantité reçue (attributions):</td>
                            <td class="text-end text-success"><strong id="qty-recue"><?= number_format($recap['quantite_totale_recue'], 0, ',', ' ') ?></strong></td>
                        </tr>
                        <tr class="border-top">
                            <td>Quantité restante:</td>
                            <td class="text-end text-danger"><strong id="qty-restante"><?= number_format($recap['quantite_restante'], 0, ',', ' ') ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-currency-exchange"></i> Détail Montants</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Satisfait par dons nature:</td>
                            <td class="text-end"><strong id="montant-nature"><?= number_format($recap['montant_satisfait_nature'], 0, ',', ' ') ?></strong> Ar</td>
                        </tr>
                        <tr>
                            <td>Satisfait par achats:</td>
                            <td class="text-end"><strong id="montant-achats"><?= number_format($recap['montant_satisfait_achats'], 0, ',', ' ') ?></strong> Ar</td>
                        </tr>
                        <tr class="border-top">
                            <td>Total dons argent:</td>
                            <td class="text-end"><strong id="total-argent"><?= number_format($recap['total_dons_argent'], 0, ',', ' ') ?></strong> Ar</td>
                        </tr>
                        <tr>
                            <td>Argent disponible:</td>
                            <td class="text-end text-success"><strong id="argent-dispo"><?= number_format($recap['argent_disponible'], 0, ',', ' ') ?></strong> Ar</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Besoins restants -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Besoins Restants</h5>
            <span class="badge bg-dark" id="count-besoins"><?= count($besoinsRestants) ?> besoin(s)</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ville</th>
                        <th>Produit</th>
                        <th>Qté Demandée</th>
                        <th>Qté Reçue</th>
                        <th>Qté Restante</th>
                        <th>Prix Unitaire</th>
                        <th>Montant Restant</th>
                    </tr>
                </thead>
                <tbody id="besoins-table">
                    <?php if (empty($besoinsRestants)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-success py-4">
                                <i class="bi bi-check-circle"></i> Tous les besoins sont satisfaits !
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($besoinsRestants as $besoin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($besoin['region']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($besoin['produit']) ?></td>
                                <td><?= number_format($besoin['quantite_demandee'], 0, ',', ' ') ?></td>
                                <td class="text-success"><?= number_format($besoin['quantite_recue'], 0, ',', ' ') ?></td>
                                <td class="text-danger"><strong><?= number_format($besoin['quantite_restante'], 0, ',', ' ') ?></strong></td>
                                <td><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-danger"><strong><?= number_format($besoin['montant_restant'], 0, ',', ' ') ?> Ar</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info configuration -->
    <div class="mt-4 text-center">
        <p class="text-muted">
            <i class="bi bi-info-circle"></i> Frais d'achat appliqués: <strong><?= $recap['frais_pourcentage'] ?>%</strong>
            | <a href="/configuration">Configurer</a>
            | <a href="/achats">Voir achats</a>
            | <a href="/dons-argent">Gérer dons argent</a>
        </p>
    </div>
</div>

<script>
/**
 * Fonction pour actualiser les données récap via Ajax
 * Appelle GET /recap/data et met à jour la page
 */
function actualiserRecap() {
    const refreshIcon = document.getElementById('refresh-icon');
    refreshIcon.classList.add('spin');
    
    fetch('/recap/data')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour les montants principaux
            document.getElementById('montant-total').textContent = formatNumber(data.total);
            document.getElementById('montant-satisfait').textContent = formatNumber(data.satisfait);
            document.getElementById('montant-restant').textContent = formatNumber(data.restant);
            
            // Mettre à jour le taux de satisfaction
            const taux = data.total > 0 ? Math.round((data.satisfait / data.total) * 100) : 0;
            document.getElementById('taux-satisfaction').textContent = taux + '%';
            document.getElementById('progress-bar').style.width = taux + '%';
            document.getElementById('progress-bar').setAttribute('aria-valuenow', taux);
            
            // Mettre à jour les quantités
            document.getElementById('qty-demandee').textContent = formatNumber(data.quantite_demandee);
            document.getElementById('qty-recue').textContent = formatNumber(data.quantite_recue);
            document.getElementById('qty-restante').textContent = formatNumber(data.quantite_restante);
            
            // Mettre à jour le timestamp
            document.getElementById('last-update').textContent = new Date().toLocaleString('fr-FR');
            
            // Actualiser les besoins restants (rechargement simple)
            location.reload();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'actualisation des données');
        })
        .finally(() => {
            refreshIcon.classList.remove('spin');
        });
}

/**
 * Formater un nombre avec séparateurs de milliers
 */
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

<?php $this->render('layout/footer'); ?>
