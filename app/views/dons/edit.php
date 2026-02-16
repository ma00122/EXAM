<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-pencil-square text-warning"></i> Modifier le Don
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
                <div class="card-header bg-warning">
                    <i class="bi bi-pencil"></i> Modifier le don #<?= $don['id'] ?>
                </div>
                <div class="card-body">
                    <form action="/dons/update/<?= $don['id'] ?>" method="POST">
                        <!-- Type de produit -->
                        <div class="mb-3">
                            <label for="type_produit" class="form-label">
                                Type de produit <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="type_produit" 
                                   name="type_produit" 
                                   value="<?= htmlspecialchars($old['type_produit'] ?? $don['type_produit']) ?>"
                                   placeholder="Ex: Riz, Huile, Savon, Sucre..."
                                   required
                                   list="produits-list">
                            <datalist id="produits-list">
                                <?php foreach ($typesProduits as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>">
                                <?php endforeach; ?>
                            </datalist>
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
                                   value="<?= htmlspecialchars($old['quantite'] ?? $don['quantite']) ?>"
                                   min="1"
                                   required>
                            <div class="form-text">
                                La quantité ne peut pas être inférieure à la quantité déjà attribuée.
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
                                   value="<?= htmlspecialchars($old['date_saisie'] ?? $don['date_saisie']) ?>">
                        </div>

                        <!-- Boutons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/dons" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info sur le don actuel -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Informations actuelles
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> #<?= $don['id'] ?></p>
                            <p><strong>Produit:</strong> <?= htmlspecialchars($don['type_produit']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Quantité:</strong> <?= number_format($don['quantite']) ?></p>
                            <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($don['date_saisie'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
