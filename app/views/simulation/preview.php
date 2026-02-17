<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-eye text-info"></i> Prévisualisation
                </h1>
                <div>
                    <a href="/simulation" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <!-- Mode sélectionné -->
            <div class="alert alert-info mb-4">
                <h5 class="mb-1">
                    <i class="bi bi-cpu me-2"></i>Mode: <strong><?= htmlspecialchars($modeLabel ?? 'Chronologique') ?></strong>
                </h5>
                <p class="mb-0">
                    <?php if (($mode ?? 'chronologique') === 'chronologique'): ?>
                        Distribution par ordre de date de saisie (FIFO - premier arrivé, premier servi).
                    <?php elseif (($mode ?? '') === 'croissant'): ?>
                        Priorité aux villes avec les plus petits besoins (satisfaire plus de villes complètement).
                    <?php else: ?>
                        Répartition équitable selon le poids des besoins (formule: part = besoin/total × don).
                    <?php endif; ?>
                </p>
            </div>

            <!-- Statistiques globales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h3><?= number_format($statsGlobales['total_dons'] ?? 0) ?></h3>
                            <p class="mb-0">Unités disponibles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body text-center">
                            <h3><?= number_format($statsGlobales['total_besoins'] ?? 0) ?></h3>
                            <p class="mb-0">Unités demandées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h3><?= number_format($statsGlobales['total_distribue'] ?? 0) ?></h3>
                            <p class="mb-0">Unités distribuées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php $taux = $statsGlobales['taux_satisfaction'] ?? 0; ?>
                    <div class="card bg-<?= $taux >= 75 ? 'success' : ($taux >= 50 ? 'warning' : 'secondary') ?> text-white h-100">
                        <div class="card-body text-center">
                            <h3><?= $taux ?>%</h3>
                            <p class="mb-0">Taux de satisfaction</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résultats par produit -->
            <?php if (empty($resultats)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Aucun don ou besoin disponible pour la simulation.
            </div>
            <?php else: ?>
            
            <?php foreach ($resultats as $produit => $resultat): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-box-seam me-2"></i>
                    <strong><?= htmlspecialchars($produit) ?></strong>
                    <span class="badge bg-light text-dark float-end">
                        Don: <?= $resultat['statistiques']['total_don'] ?? 0 ?> | Besoins: <?= $resultat['statistiques']['total_besoins'] ?? 0 ?>
                    </span>
                </div>
                <div class="card-body">
                    <!-- Détails du calcul -->
                    <?php if (!empty($resultat['details_calcul'])): ?>
                    <div class="bg-light p-3 rounded mb-3">
                        <h6><i class="bi bi-calculator me-2"></i>Détails du calcul:</h6>
                        <pre class="mb-0 small" style="white-space: pre-wrap;"><?php 
                            foreach ($resultat['details_calcul'] as $detail) {
                                echo htmlspecialchars($detail) . "\n";
                            }
                        ?></pre>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tableau des attributions -->
                    <?php if (!empty($resultat['attributions'])): ?>
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Ville</th>
                                <th class="text-center">Besoin</th>
                                <th class="text-center">Attribué</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultat['attributions'] as $attr): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <?= htmlspecialchars($attr['ville_nom'] ?? '') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= $attr['quantite_besoin'] ?? 0 ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= $attr['quantite_attribuee'] ?? 0 ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($attr['complet'])): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Complet</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle"></i> Partiel</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th class="text-center"><?= $resultat['statistiques']['total_besoins'] ?? 0 ?></th>
                                <th class="text-center"><?= $resultat['statistiques']['total_distribue'] ?? 0 ?></th>
                                <th class="text-center">
                                    <?= $resultat['statistiques']['villes_completes'] ?? 0 ?>/<?= count($resultat['attributions']) ?> villes complètes
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">Aucune attribution pour ce produit.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php endif; ?>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <span class="text-muted">Ceci est une prévisualisation. Les données ne sont pas encore enregistrées.</span>
                        </div>
                        <div>
                            <a href="/simulation" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Annuler
                            </a>
                            <?php if (!empty($resultats)): ?>
                            <form action="/simulation/run" method="POST" class="d-inline">
                                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode ?? 'chronologique') ?>">
                                <button type="submit" class="btn btn-success btn-lg"
                                        onclick="return confirm('Valider et enregistrer cette simulation ?')">
                                    <i class="bi bi-check-lg"></i> Valider et Enregistrer
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
