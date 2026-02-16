<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-lg"></i> Ajouter un nouveau besoin
                    </h4>
                </div>
                <div class="card-body">
                    <form action="/besoins/store" method="POST" id="besoinForm">
                        
                        <div class="row">
                            <!-- Ville -->
                            <div class="col-md-6 mb-3">
                                <label for="ville_id" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Ville <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="ville_id" name="ville_id" required>
                                    <option value="">Sélectionner une ville</option>
                                    <?php foreach ($villes as $ville): ?>
                                    <option value="<?= $ville->id ?>">
                                        <?= htmlspecialchars($ville->nom) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner une ville.</div>
                            </div>

                            <!-- Type de besoin -->
                            <div class="col-md-6 mb-3">
                                <label for="type_id" class="form-label">
                                    <i class="bi bi-tag"></i> Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="type_id" name="type_id" required>
                                    <option value="">Sélectionner un type</option>
                                    <?php foreach ($types as $type): ?>
                                    <option value="<?= $type->id ?>">
                                        <?= htmlspecialchars($type->nom_type) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner un type.</div>
                            </div>
                        </div>

                        <!-- Produit -->
                        <div class="mb-3">
                            <label for="produit" class="form-label">
                                <i class="bi bi-box"></i> Produit <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="produit" name="produit" 
                                   required placeholder="Ex: Riz, Tôle, Clou...">
                            <div class="invalid-feedback">Le produit est requis.</div>
                        </div>

                        <div class="row">
                            <!-- Quantité -->
                            <div class="col-md-6 mb-3">
                                <label for="quantite" class="form-label">
                                    <i class="bi bi-123"></i> Quantité <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="quantite" name="quantite" 
                                       min="1" required placeholder="Ex: 100">
                                <div class="invalid-feedback">La quantité doit être supérieure à 0.</div>
                            </div>

                            <!-- Prix unitaire -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_unitaire" class="form-label">
                                    <i class="bi bi-currency-exchange"></i> Prix Unitaire (Ar) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" 
                                       min="0.01" step="0.01" required placeholder="Ex: 2500">
                                <div class="invalid-feedback">Le prix unitaire doit être supérieur à 0.</div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Le prix unitaire ne sera plus modifiable après enregistrement.
                                </small>
                            </div>
                        </div>

                        <!-- Calcul automatique valeur totale -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-calculator"></i> Valeur Totale Estimée
                                    </h5>
                                    <p class="card-text display-6 text-success" id="valeur_totale">
                                        0,00 Ar
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
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcul automatique de la valeur totale
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantite');
    const prixInput = document.getElementById('prix_unitaire');
    const valeurTotaleDisplay = document.getElementById('valeur_totale');

    function updateValeurTotale() {
        const quantite = parseFloat(quantiteInput.value) || 0;
        const prix = parseFloat(prixInput.value) || 0;
        const total = quantite * prix;
        
        valeurTotaleDisplay.textContent = new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(total) + ' Ar';
    }

    quantiteInput.addEventListener('input', updateValeurTotale);
    prixInput.addEventListener('input', updateValeurTotale);

    // Validation du formulaire
    const form = document.getElementById('besoinForm');
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
