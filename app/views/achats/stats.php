<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-bar-chart"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/achats" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour aux achats
        </a>
    </div>

    <!-- Statistiques par statut -->
    <div class="row mb-4">
        <?php foreach ($countByStatut as $stat): ?>
            <?php
            $bgClass = match($stat['statut'] ?? 'en_attente') {
                'valide' => 'bg-success',
                'annule' => 'bg-danger',
                default => 'bg-warning'
            };
            ?>
            <div class="col-md-4">
                <div class="card <?= $bgClass ?> text-white">
                    <div class="card-body text-center">
                        <h2><?= $stat['nombre'] ?? 0 ?></h2>
                        <p class="mb-0">Achats <?= ucfirst($stat['statut'] ?? 'En attente') ?></p>
                        <small><?= number_format($stat['total'] ?? 0, 0, ',', ' ') ?> Ar</small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Statistiques par ville -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Achats par Ville (Validés uniquement)</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Nb Achats</th>
                        <th>Montant Produits</th>
                        <th>Total Frais</th>
                        <th>Total Général</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($statsByVille)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune statistique disponible.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $totalGeneral = 0;
                        $totalFrais = 0;
                        foreach ($statsByVille as $stat): 
                            $totalGeneral += $stat['total_general'];
                            $totalFrais += $stat['total_frais'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($stat['ville_nom']) ?></strong></td>
                                <td><?= htmlspecialchars($stat['region'] ?? '') ?></td>
                                <td><span class="badge bg-primary"><?= $stat['nombre_achats'] ?></span></td>
                                <td><?= number_format($stat['total_montant_produit'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-warning"><?= number_format($stat['total_frais'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-success"><strong><?= number_format($stat['total_general'], 0, ',', ' ') ?> Ar</strong></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-dark">
                            <td colspan="4"><strong>TOTAL</strong></td>
                            <td class="text-warning"><strong><?= number_format($totalFrais, 0, ',', ' ') ?> Ar</strong></td>
                            <td class="text-success"><strong><?= number_format($totalGeneral, 0, ',', ' ') ?> Ar</strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center">
        <p class="text-muted">
            <i class="bi bi-info-circle"></i> Taux de frais appliqué: <strong><?= $fraisPourcentage ?>%</strong>
            <a href="/configuration" class="ms-2">Modifier</a>
        </p>
    </div>
</div>

<?php $this->render('layout/footer'); ?>
