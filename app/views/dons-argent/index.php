<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-cash-stack"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/dons-argent/create" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un Don Argent
        </a>
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
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['nombre_dons'] ?></h3>
                    <p class="mb-0">Total Dons</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['total_montant'], 0, ',', ' ') ?></h3>
                    <p class="mb-0">Montant Total (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['total_disponible'], 0, ',', ' ') ?></h3>
                    <p class="mb-0">Disponible (Ar)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['total_utilise'], 0, ',', ' ') ?></h3>
                    <p class="mb-0">Utilisé (Ar)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des dons argent -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Donateur</th>
                        <th>Montant</th>
                        <th>Utilisé</th>
                        <th>Disponible</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Notes</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($donsArgent)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucun don argent enregistré.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donsArgent as $don): ?>
                            <tr>
                                <td><?= $don['id'] ?></td>
                                <td><strong><?= htmlspecialchars($don['donateur']) ?></strong></td>
                                <td><?= number_format($don['montant'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-warning"><?= number_format($don['montant_utilise'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-success"><strong><?= number_format($don['montant_disponible'], 0, ',', ' ') ?> Ar</strong></td>
                                <td>
                                    <?php
                                    $statutBadge = match($don['statut']) {
                                        'disponible' => 'bg-success',
                                        'partiellement_utilise' => 'bg-warning text-dark',
                                        'epuise' => 'bg-secondary',
                                        default => 'bg-info'
                                    };
                                    $statutLabel = match($don['statut']) {
                                        'disponible' => 'Disponible',
                                        'partiellement_utilise' => 'Partiel',
                                        'epuise' => 'Épuisé',
                                        default => $don['statut']
                                    };
                                    ?>
                                    <span class="badge <?= $statutBadge ?>"><?= $statutLabel ?></span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></td>
                                <td><?= htmlspecialchars($don['notes'] ?? '-') ?></td>
                                <td>
                                    <a href="/dons-argent/edit/<?= $don['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($don['montant_utilise'] == 0): ?>
                                        <a href="/dons-argent/delete/<?= $don['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Supprimer ce don argent ?');" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
