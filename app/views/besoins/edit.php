<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil"></i> Modifier le besoin #<?= $besoin['id'] ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="/besoins/update/<?= $besoin['id'] ?>" method="POST" id="besoinEditForm">
                        
                        <div class="row">
                            <!-- Ville (non modifiable) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt"></i> Ville
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?>" disabled readonly>
                                <small class="text-muted">Non modifiable</small>
                            </div>

                            <!-- Type de besoin (non modifiable) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-tag"></i> Type
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($besoin['type_nom'] ?? 'N/A') ?>" disabled readonly>
                                <small class="text-muted">Non modifiable</small>
                            </div>
                        </div>

                        <!-- Produit (non modifiable) -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-box"></i> Produit
                            </label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($besoin['produit'] ?? '') ?>" disabled readonly>
                            <small class="text-muted">Non modifiable</small>
                        </div>

                        <div class="row">
                            <!-- Quantité (modifiable) -->
                            <div class="col-md-6 mb-3">
                                <label for="quantite" class="form-label">
                                    <i class="bi bi-123"></i> Quantité <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="quantite" name="quantite" 
                                       min="1" required value="<?= $besoin['quantite'] ?>">
                                <div class="invalid-feedback">La quantité doit être supérieure à 0.</div>
                            </div>

                            <!-- Prix unitaire (non modifiable après insertion) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-currency-exchange"></i> Prix Unitaire (Ar)
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?= number_format($besoin['prix_unitaire'], 2, ',', ' ') ?> Ar" 
                                       disabled readonly>
                                <small class="text-warning">
                                    <i class="bi bi-lock"></i> Le prix unitaire n'est pas modifiable après insertion.
                                </small>
                            </div>
                        </div>

                        <!-- Calcul automatique valeur totale -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-calculator"></i> Valeur Totale
                                    </h5>
                                    <p class="card-text display-6 text-success" id="valeur_totale">
                                        <?= number_format($besoin['quantite'] * $besoin['prix_unitaire'], 2, ',', ' ') ?> Ar
                                    </p>
                                    <small class="text-muted">valeur_totale = quantité × prix_unitaire</small>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <a href="/besoins" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <div>
                                <a href="/besoins/delete/<?= $besoin['id'] ?>" 
                                   class="btn btn-danger me-2"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?');">
                                    <i class="bi bi-trash"></i> Supprimer
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Mettre à jour
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantite');
    const valeurTotaleDisplay = document.getElementById('valeur_totale');
    const prixUnitaire = <?= $besoin['prix_unitaire'] ?>;

    function updateValeurTotale() {
        const quantite = parseFloat(quantiteInput.value) || 0;
        const total = quantite * prixUnitaire;
        
        valeurTotaleDisplay.textContent = new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(total) + ' Ar';
    }

    quantiteInput.addEventListener('input', updateValeurTotale);

    const form = document.getElementById('besoinEditForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
