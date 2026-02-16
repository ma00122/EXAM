<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-cart-check"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <div>
            <a href="/achats/create" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Nouvel Achat
            </a>
            <a href="/achats/stats" class="btn btn-outline-info">
                <i class="bi bi-bar-chart"></i> Statistiques
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

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_achats'] ?></h3>
                    <p class="mb-0">Total Achats</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['montant_total'], 0, ',', ' ') ?> Ar</h3>
                    <p class="mb-0">Montant Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['total_frais'], 0, ',', ' ') ?> Ar</h3>
                    <p class="mb-0">Total Frais</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['frais_pourcentage'] ?>%</h3>
                    <p class="mb-0">Taux de Frais</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/achats" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrer par ville</label>
                    <select name="ville_id" class="form-select">
                        <option value="">-- Toutes les villes --</option>
                        <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>" <?= $filterVilleId == $ville['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom']) ?> (<?= htmlspecialchars($ville['region']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filtrer par statut</label>
                    <select name="statut" class="form-select">
                        <option value="">-- Tous les statuts --</option>
                        <option value="en_attente" <?= $filterStatut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="valide" <?= $filterStatut === 'valide' ? 'selected' : '' ?>>Validé</option>
                        <option value="annule" <?= $filterStatut === 'annule' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter"></i> Filtrer
                    </button>
                    <a href="/achats" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des achats -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Montant Produit</th>
                        <th>Frais</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($achats)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Aucun achat trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($achats as $achat): ?>
                            <tr>
                                <td><?= $achat['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($achat['ville_nom']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($achat['region']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($achat['besoin_produit']) ?></td>
                                <td><?= $achat['quantite_achetee'] ?></td>
                                <td><?= number_format($achat['montant_produit'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-warning"><?= number_format($achat['frais'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-success"><strong><?= number_format($achat['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                                <td><?= date('d/m/Y', strtotime($achat['date_achat'])) ?></td>
                                <td>
                                    <?php
                                    $statutBadge = match($achat['statut']) {
                                        'valide' => 'bg-success',
                                        'annule' => 'bg-danger',
                                        default => 'bg-warning text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $statutBadge ?>"><?= ucfirst($achat['statut']) ?></span>
                                </td>
                                <td>
                                    <?php if ($achat['statut'] === 'en_attente'): ?>
                                        <a href="/achats/validate/<?= $achat['id'] ?>" class="btn btn-sm btn-success" 
                                           onclick="return confirm('Valider cet achat ?');" title="Valider">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                        <a href="/achats/cancel/<?= $achat['id'] ?>" class="btn btn-sm btn-warning" 
                                           onclick="return confirm('Annuler cet achat ?');" title="Annuler">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/achats/edit/<?= $achat['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/achats/delete/<?= $achat['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Supprimer cet achat ?');" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->render('layout/footer'); ?>
