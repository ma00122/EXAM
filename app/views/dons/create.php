<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-plus-circle text-success"></i> Ajouter un Don
                </h1>
                <a href="/dons" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <!-- Message d'erreur -->
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Nouveau don
                </div>
                <div class="card-body">
                    <form action="/dons/store" method="POST">
                        <!-- Type de produit -->
                        <div class="mb-3">
                            <label for="type_produit" class="form-label">
                                Type de produit <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="type_produit" 
                                   name="type_produit" 
                                   value="<?= htmlspecialchars($old['type_produit'] ?? '') ?>"
                                   placeholder="Ex: Riz, Huile, Savon, Sucre..."
                                   required
                                   list="produits-list"
                                   autofocus>
                            <datalist id="produits-list">
                                <?php foreach ($typesProduits as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="form-text">
                                Entrez le nom du produit ou sélectionnez un produit existant.
                            </div>
                        </div>

                        <!-- Quantité -->
                        <div class="mb-3">
                            <label for="quantite" class="form-label">
                                Quantité <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantite" 
                                   name="quantite" 
                                   value="<?= htmlspecialchars($old['quantite'] ?? '') ?>"
                                   placeholder="Ex: 100"
                                   min="1"
                                   required>
                            <div class="form-text">
                                Quantité totale du don (doit être supérieure à 0).
                            </div>
                        </div>

                        <!-- Date de saisie -->
                        <div class="mb-3">
                            <label for="date_saisie" class="form-label">
                                Date de saisie
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_saisie" 
                                   name="date_saisie" 
                                   value="<?= htmlspecialchars($old['date_saisie'] ?? date('Y-m-d')) ?>">
                            <div class="form-text">
                                Date à laquelle le don a été reçu (par défaut: aujourd'hui).
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/dons" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Enregistrer le don
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Note d'information -->
            <div class="card mt-4 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-info-circle"></i> À propos des dons
                    </h6>
                    <p class="card-text mb-0">
                        Les dons seront automatiquement attribués aux besoins correspondants 
                        lors de l'exécution de la <a href="/simulation">simulation</a>.
                        L'algorithme attribue les dons aux besoins ayant le même type de produit,
                        en respectant l'ordre chronologique (FIFO).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
