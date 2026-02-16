<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-eye-fill text-info"></i> Détail du Don #<?= $don['id'] ?>
                </h1>
                <div>
                    <a href="/dons/edit/<?= $don['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <a href="/dons" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Informations du don -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-gift"></i> Informations du don
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th>Produit:</th>
                                    <td><strong><?= htmlspecialchars($don['type_produit']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Quantité totale:</th>
                                    <td><span class="badge bg-primary"><?= number_format($don['quantite']) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Quantité attribuée:</th>
                                    <td><span class="badge bg-info"><?= number_format($quantiteAttribuee) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Quantité restante:</th>
                                    <td>
                                        <?php if ($quantiteRestante > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= number_format($quantiteRestante) ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Épuisé</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de saisie:</th>
                                    <td><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6>Taux d'attribution</h6>
                            <?php 
                            $pourcentage = $don['quantite'] > 0 
                                ? round(($quantiteAttribuee / $don['quantite']) * 100) 
                                : 0;
                            ?>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-info" 
                                     role="progressbar" 
                                     style="width: <?= $pourcentage ?>%"
                                     aria-valuenow="<?= $pourcentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= $pourcentage ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attributions -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-arrow-right-circle"></i> Attributions 
                            <span class="badge bg-light text-dark"><?= count($attributions) ?></span>
                        </div>
                        <?php if (empty($attributions)): ?>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                Ce don n'a pas encore été attribué. 
                                <a href="/simulation">Exécutez la simulation</a> pour attribuer les dons aux besoins.
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Besoin #</th>
                                        <th>Produit</th>
                                        <th>Ville</th>
                                        <th class="text-center">Qté Attribuée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attributions as $attr): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= $attr['besoin_id'] ?></span></td>
                                        <td><?= htmlspecialchars($attr['produit']) ?></td>
                                        <td>
                                            <?php if (!empty($attr['ville_id'])): ?>
                                            <a href="/bngrc/dashboard/ville/<?= $attr['ville_id'] ?>">
                                                Voir la ville
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= number_format($attr['quantite_attribuee']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="3">Total attribué</th>
                                        <th class="text-center"><?= number_format($quantiteAttribuee) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
