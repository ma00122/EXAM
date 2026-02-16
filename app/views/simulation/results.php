<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-clipboard-data-fill text-primary"></i> Résultats de la Simulation
                </h1>
                <div>
                    <a href="/simulation" class="btn btn-info me-2">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                    <a href="/bngrc/dashboard" class="btn btn-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h2 class="text-success"><?= number_format($stats['total_attribue']) ?></h2>
                            <p class="mb-0">Unités attribuées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h2 class="text-warning"><?= number_format($stats['total_restant']) ?></h2>
                            <p class="mb-0">Unités restantes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h2 class="text-info"><?= $stats['nombre_attributions'] ?></h2>
                            <p class="mb-0">Attributions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-<?= $stats['pourcentage_couverture'] >= 75 ? 'success' : ($stats['pourcentage_couverture'] >= 50 ? 'warning' : 'danger') ?> text-white">
                        <div class="card-body">
                            <h2><?= $stats['pourcentage_couverture'] ?>%</h2>
                            <p class="mb-0">Couverture</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résultats par don -->
            <?php if (empty($attributionsByDon)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Aucune attribution effectuée. <a href="/simulation">Exécutez la simulation</a>.
            </div>
            <?php else: ?>
            <?php foreach ($attributionsByDon as $donId => $data): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Don #<?= $donId ?> - <?= htmlspecialchars($data['don']['type_produit']) ?>
                    <span class="badge bg-light text-dark float-end">
                        <?= number_format($data['don']['quantite']) ?> unités
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Attribution #</th>
                                <th>Besoin #</th>
                                <th class="text-center">Quantité attribuée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDon = 0;
                            foreach ($data['attributions'] as $attr): 
                                $totalDon += $attr['quantite_attribuee'];
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $attr['id'] ?></span></td>
                                <td>Besoin #<?= $attr['besoin_id'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($attr['quantite_attribuee']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="2">Total attribué pour ce don</th>
                                <th class="text-center"><?= number_format($totalDon) ?> / <?= number_format($data['don']['quantite']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
