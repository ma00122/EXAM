<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/attributions" class="btn btn-outline-secondary">
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
                    <form action="/attributions/update/<?= $attribution['id'] ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Don</label>
                            <input type="text" class="form-control" readonly
                                   value="<?= htmlspecialchars($attribution['type_produit']) ?> (Qté: <?= $attribution['don_quantite'] ?>)">
                            <div class="form-text text-muted">Le don ne peut pas être modifié après création.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Besoin</label>
                            <input type="text" class="form-control" readonly
                                   value="<?= htmlspecialchars($attribution['besoin_produit'] ?? 'N/A') ?> - Ville #<?= $attribution['ville_id'] ?>">
                            <div class="form-text text-muted">Le besoin ne peut pas être modifié après création.</div>
                        </div>

                        <div class="mb-3">
                            <label for="quantite_attribuee" class="form-label">Quantité attribuée <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantite_attribuee" name="quantite_attribuee" 
                                   value="<?= htmlspecialchars($attribution['quantite_attribuee']) ?>" min="1" required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                            <a href="/attributions" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm bg-light">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Détails</h5>
                </div>
                <div class="card-body">
                    <p><strong>ID Attribution:</strong> #<?= $attribution['id'] ?></p>
                    <p><strong>Don ID:</strong> #<?= $attribution['don_id'] ?></p>
                    <p><strong>Besoin ID:</strong> #<?= $attribution['besoin_id'] ?></p>
                    <p><strong>Créée le:</strong> <?= date('d/m/Y H:i', strtotime($attribution['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->render('layout/footer'); ?>
