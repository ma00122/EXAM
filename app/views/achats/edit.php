<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/achats" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="/achats/update/<?= $achat['id'] ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ville_id" class="form-label">Ville <span class="text-danger">*</span></label>
                                <select class="form-select" id="ville_id" name="ville_id" required>
                                    <option value="">-- Sélectionner une ville --</option>
                                    <?php foreach ($villes as $ville): ?>
                                        <option value="<?= $ville['id'] ?>" <?= $achat['ville_id'] == $ville['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ville['nom']) ?> (<?= htmlspecialchars($ville['region']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="besoin_id" class="form-label">Besoin <span class="text-danger">*</span></label>
                                <select class="form-select" id="besoin_id" name="besoin_id" required>
                                    <option value="">-- Sélectionner un besoin --</option>
                                    <?php foreach ($besoins as $besoin): ?>
                                        <option value="<?= $besoin['id'] ?>" 
                                                data-prix="<?= $besoin['prix_unitaire'] ?>"
                                                <?= $achat['besoin_id'] == $besoin['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($besoin['ville_nom']) ?> - <?= htmlspecialchars($besoin['produit']) ?> 
                                            (<?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar/unité)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="quantite_achetee" class="form-label">Quantité <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantite_achetee" name="quantite_achetee" 
                                       value="<?= htmlspecialchars($achat['quantite_achetee']) ?>" min="1" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="montant_produit" class="form-label">Montant Produit (Ar) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="montant_produit" name="montant_produit" 
                                       value="<?= htmlspecialchars($achat['montant_produit']) ?>" min="0" step="0.01" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_achat" class="form-label">Date d'achat <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_achat" name="date_achat" 
                                       value="<?= htmlspecialchars($achat['date_achat']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                <select class="form-select" id="statut" name="statut" required>
                                    <option value="en_attente" <?= $achat['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="valide" <?= $achat['statut'] === 'valide' ? 'selected' : '' ?>>Validé</option>
                                    <option value="annule" <?= $achat['statut'] === 'annule' ? 'selected' : '' ?>>Annulé</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($achat['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                            <a href="/achats" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm bg-light">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informations actuelles</h5>
                </div>
                <div class="card-body">
                    <p><strong>Montant HT:</strong> <?= number_format($achat['montant_produit'], 0, ',', ' ') ?> Ar</p>
                    <p><strong>Frais:</strong> <span class="text-warning"><?= number_format($achat['frais'], 0, ',', ' ') ?> Ar</span></p>
                    <p><strong>Total TTC:</strong> <span class="text-success"><?= number_format($achat['montant_total'], 0, ',', ' ') ?> Ar</span></p>
                    <hr>
                    <p><strong>Taux de frais actuel:</strong> <span class="badge bg-warning text-dark"><?= $fraisPourcentage ?>%</span></p>
                    <p class="text-muted small">Note: Les frais seront recalculés lors de la mise à jour.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->render('layout/footer'); ?>
