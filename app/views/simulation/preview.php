<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-eye-fill text-info"></i> Preview Simulation
                </h1>
                <div>
                    <a href="/simulation" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <!-- Messages d'erreurs de simulation -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-exclamation-triangle"></i> Avertissements</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Statistiques de preview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= number_format($totalAttribue) ?></h3>
                            <p class="mb-0">Unités attribuées</p>
                            <small>(dons nature)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= number_format($totalAchete) ?></h3>
                            <p class="mb-0">Unités achetées</p>
                            <small>(dons argent)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3><?= number_format($totalFrais) ?> Ar</h3>
                            <p class="mb-0">Frais d'achat</p>
                            <small>(<?= $fraisPourcentage ?>%)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3><?= number_format($argentRestant) ?> Ar</h3>
                            <p class="mb-0">Argent restant</p>
                            <small>non utilisé</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attributions de dons nature -->
            <?php if (!empty($previewAttributions)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Attributions de dons nature (<?= count($previewAttributions) ?>)
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Don</th>
                                <th>Produit</th>
                                <th>Ville</th>
                                <th>Besoin</th>
                                <th>Quantité attribuée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewAttributions as $attr): ?>
                            <tr>
                                <td>#<?= $attr['don_id'] ?></td>
                                <td><?= htmlspecialchars($attr['don_produit']) ?></td>
                                <td><?= htmlspecialchars($attr['ville_nom']) ?></td>
                                <td><?= htmlspecialchars($attr['besoin_produit']) ?></td>
                                <td><strong><?= number_format($attr['quantite_attribuee']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Achats avec dons argent -->
            <?php if (!empty($previewAchats)): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-cart-check"></i> Achats avec dons argent (<?= count($previewAchats) ?>)
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Montant produit</th>
                                <th>Frais (<?= $fraisPourcentage ?>%)</th>
                                <th>Total</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewAchats as $achat): ?>
                            <tr>
                                <td><?= htmlspecialchars($achat['ville_nom']) ?></td>
                                <td><?= htmlspecialchars($achat['besoin_produit']) ?></td>
                                <td><?= number_format($achat['quantite_achetee']) ?></td>
                                <td><?= number_format($achat['montant_produit']) ?> Ar</td>
                                <td><?= number_format($achat['frais']) ?> Ar</td>
                                <td><strong><?= number_format($achat['montant_total']) ?> Ar</strong></td>
                                <td>
                                    <?php if ($achat['type'] === 'complet'): ?>
                                    <span class="badge bg-success">Complet</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark">Partiel</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="3"><strong>TOTAL</strong></td>
                                <td><strong><?= number_format(array_sum(array_column($previewAchats, 'montant_produit'))) ?> Ar</strong></td>
                                <td><strong><?= number_format($totalFrais) ?> Ar</strong></td>
                                <td><strong><?= number_format($totalMontantAchats) ?> Ar</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Aucun résultat -->
            <?php if (empty($previewAttributions) && empty($previewAchats)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Aucune attribution ni achat possible avec les dons et besoins actuels.
            </div>
            <?php endif; ?>

            <!-- Boutons d'action -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Cette preview n'a pas modifié la base de données. Cliquez sur "Valider" pour enregistrer les changements.
                            </p>
                        </div>
                        <div>
                            <a href="/simulation" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <?php if (!empty($previewAttributions) || !empty($previewAchats)): ?>
                            <form action="/simulation/valider" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Valider et Enregistrer
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
