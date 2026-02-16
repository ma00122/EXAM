<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-arrow-left-right"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <div>
            <a href="/attributions/create" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Nouvelle Attribution
            </a>
            <a href="/simulation" class="btn btn-outline-primary">
                <i class="bi bi-play-circle"></i> Simulation Auto
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

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_attributions'] ?></h3>
                    <p class="mb-0">Total Attributions</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['quantite_totale'], 0, ',', ' ') ?></h3>
                    <p class="mb-0">Quantité Totale Attribuée</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre par ville -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/attributions" class="row g-3">
                <div class="col-md-6">
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
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter"></i> Filtrer
                    </button>
                    <a href="/attributions" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des attributions -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Don</th>
                        <th>Besoin</th>
                        <th>Ville</th>
                        <th>Qté Attribuée</th>
                        <th>Date Don</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attributions)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucune attribution trouvée. 
                                <a href="/simulation">Lancez une simulation</a> pour créer des attributions automatiques.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attributions as $attr): ?>
                            <tr>
                                <td><?= $attr['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($attr['type_produit']) ?></strong>
                                    <br><small class="text-muted">Qté don: <?= $attr['don_quantite'] ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($attr['besoin_produit']) ?></strong>
                                    <br><small class="text-muted">Demandé: <?= $attr['besoin_quantite'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($attr['ville_nom'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-success"><?= number_format($attr['quantite_attribuee'], 0, ',', ' ') ?></span></td>
                                <td><?= date('d/m/Y', strtotime($attr['don_date'])) ?></td>
                                <td>
                                    <a href="/attributions/edit/<?= $attr['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/attributions/delete/<?= $attr['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Supprimer cette attribution ?');" title="Supprimer">
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
