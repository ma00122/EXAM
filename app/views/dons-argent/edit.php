<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/dons-argent" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="/dons-argent/update/<?= $donArgent['id'] ?>" method="POST">
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (Ar) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-lg" id="montant" name="montant" 
                                       value="<?= htmlspecialchars($donArgent['montant']) ?>" min="1" step="1" required>
                                <span class="input-group-text">Ar</span>
                            </div>
                            <?php if ($donArgent['montant_utilise'] > 0): ?>
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    Attention: <?= number_format($donArgent['montant_utilise'], 0, ',', ' ') ?> Ar déjà utilisés.
                                    Le montant ne peut pas être inférieur à cette valeur.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="donateur" class="form-label">Donateur</label>
                            <input type="text" class="form-control" id="donateur" name="donateur" 
                                   value="<?= htmlspecialchars($donArgent['donateur']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="date_saisie" class="form-label">Date du don <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_saisie" name="date_saisie" 
                                   value="<?= htmlspecialchars($donArgent['date_saisie']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($donArgent['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                            <a href="/dons-argent" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Infos actuelles -->
            <div class="card mt-3 bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> État actuel</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Montant total:</td>
                            <td class="text-end"><strong><?= number_format($donArgent['montant'], 0, ',', ' ') ?> Ar</strong></td>
                        </tr>
                        <tr>
                            <td>Montant utilisé:</td>
                            <td class="text-end text-warning"><?= number_format($donArgent['montant_utilise'], 0, ',', ' ') ?> Ar</td>
                        </tr>
                        <tr>
                            <td>Disponible:</td>
                            <td class="text-end text-success"><strong><?= number_format($donArgent['montant_disponible'], 0, ',', ' ') ?> Ar</strong></td>
                        </tr>
                        <tr>
                            <td>Statut:</td>
                            <td class="text-end">
                                <?php
                                $statutBadge = match($donArgent['statut']) {
                                    'disponible' => 'bg-success',
                                    'partiellement_utilise' => 'bg-warning text-dark',
                                    'epuise' => 'bg-secondary',
                                    default => 'bg-info'
                                };
                                ?>
                                <span class="badge <?= $statutBadge ?>"><?= ucfirst(str_replace('_', ' ', $donArgent['statut'])) ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->render('layout/footer'); ?>
