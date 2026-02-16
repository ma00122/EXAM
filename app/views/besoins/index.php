<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-list-check"></i> Liste des Besoins
            <span class="badge bg-primary"><?= count($besoins) ?></span>
        </h2>
        <a href="/besoins/create" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Ajouter un besoin
        </a>
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

    <?php if (empty($besoins)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Aucun besoin trouvé.
        <a href="/besoins/create" class="alert-link">Ajouter un besoin</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Valeur Totale</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($besoins as $besoin): ?>
                <?php 
                    $quantite = $besoin['quantite'] ?? 0;
                    $prix_unitaire = $besoin['prix_unitaire'] ?? 0;
                    $valeur_totale = $quantite * $prix_unitaire;
                    $type_nom = $besoin['type_nom'] ?? 'N/A';
                ?>
                <tr>
                    <td><?= $besoin['id'] ?></td>
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
                    <td><strong><?= number_format($quantite, 0, ',', ' ') ?></strong></td>
                    <td><?= number_format($prix_unitaire, 2, ',', ' ') ?> Ar</td>
                    <td class="text-success fw-bold">
                        <?= number_format($valeur_totale, 2, ',', ' ') ?> Ar
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/besoins/edit/<?= $besoin['id'] ?>" class="btn btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/besoins/delete/<?= $besoin['id'] ?>" 
                               class="btn btn-outline-danger" 
                               title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total Général :</td>
                    <td class="text-success fw-bold">
                        <?php 
                        $total_general = 0;
                        foreach ($besoins as $besoin) {
                            $total_general += ($besoin['quantite'] ?? 0) * ($besoin['prix_unitaire'] ?? 0);
                        }
                        echo number_format($total_general, 2, ',', ' ') . ' Ar';
                        ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
