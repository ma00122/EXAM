<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> <?= htmlspecialchars($pageTitle) ?></h1>
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
                    <form action="/achats/store" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ville_id" class="form-label">Ville <span class="text-danger">*</span></label>
                                <select class="form-select" id="ville_id" name="ville_id" required>
                                    <option value="">-- Sélectionner une ville --</option>
                                    <?php foreach ($villes as $ville): ?>
                                        <option value="<?= $ville['id'] ?>" <?= ($old['ville_id'] ?? '') == $ville['id'] ? 'selected' : '' ?>>
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
                                                <?= ($old['besoin_id'] ?? '') == $besoin['id'] ? 'selected' : '' ?>>
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
                                       value="<?= htmlspecialchars($old['quantite_achetee'] ?? 1) ?>" min="1" required
                                       onchange="calculerMontant()">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="montant_produit" class="form-label">Montant Produit (Ar) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="montant_produit" name="montant_produit" 
                                       value="<?= htmlspecialchars($old['montant_produit'] ?? '') ?>" min="0" step="0.01" required
                                       onchange="calculerPreview()">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_achat" class="form-label">Date d'achat <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_achat" name="date_achat" 
                                       value="<?= htmlspecialchars($old['date_achat'] ?? date('Y-m-d')) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Enregistrer l'achat
                            </button>
                            <a href="/achats" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Preview calcul -->
            <div class="card shadow-sm bg-light">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Calcul Preview</h5>
                </div>
                <div class="card-body">
                    <p><strong>Taux de frais:</strong> <span class="badge bg-warning text-dark"><?= $fraisPourcentage ?>%</span></p>
                    <hr>
                    <div id="preview_calcul">
                        <p class="text-muted">Entrez un montant pour voir le calcul...</p>
                    </div>
                </div>
            </div>

            <!-- Dons argent disponibles -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Dons Argent Disponibles</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($donsArgent)): ?>
                        <p class="text-muted">Aucun don argent disponible.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($donsArgent as $don): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($don['donateur']) ?></span>
                                    <span class="badge bg-success"><?= number_format($don['montant_disponible'], 0, ',', ' ') ?> Ar</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const fraisPourcentage = <?= $fraisPourcentage ?>;

function calculerMontant() {
    const besoinSelect = document.getElementById('besoin_id');
    const quantite = parseInt(document.getElementById('quantite_achetee').value) || 0;
    const selectedOption = besoinSelect.options[besoinSelect.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.prix) {
        const prixUnitaire = parseFloat(selectedOption.dataset.prix);
        const montant = quantite * prixUnitaire;
        document.getElementById('montant_produit').value = montant;
        calculerPreview();
    }
}

function calculerPreview() {
    const montant = parseFloat(document.getElementById('montant_produit').value) || 0;
    const preview = document.getElementById('preview_calcul');
    
    if (montant <= 0) {
        preview.innerHTML = '<p class="text-muted">Entrez un montant pour voir le calcul...</p>';
        return;
    }
    
    const frais = montant * (fraisPourcentage / 100);
    const total = montant + frais;
    
    preview.innerHTML = `
        <p><strong>Montant HT:</strong><br>${montant.toLocaleString()} Ar</p>
        <p><strong>Frais (${fraisPourcentage}%):</strong><br><span class="text-warning">${frais.toLocaleString()} Ar</span></p>
        <hr>
        <p class="h5"><strong>Total TTC:</strong><br><span class="text-success">${total.toLocaleString()} Ar</span></p>
    `;
}

// Initialiser le calcul au chargement
document.getElementById('besoin_id').addEventListener('change', calculerMontant);
</script>

<?php $this->render('layout/footer'); ?>
