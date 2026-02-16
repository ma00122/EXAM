<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> <?= htmlspecialchars($pageTitle) ?></h1>
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
                    <form action="/dons-argent/store" method="POST">
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (Ar) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-lg" id="montant" name="montant" 
                                       value="<?= htmlspecialchars($old['montant'] ?? '') ?>" min="1" step="1" required
                                       placeholder="Ex: 150000">
                                <span class="input-group-text">Ar</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="donateur" class="form-label">Donateur</label>
                            <input type="text" class="form-control" id="donateur" name="donateur" 
                                   value="<?= htmlspecialchars($old['donateur'] ?? '') ?>"
                                   placeholder="Nom du donateur (ou laisser vide pour Anonyme)">
                        </div>

                        <div class="mb-3">
                            <label for="date_saisie" class="form-label">Date du don <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_saisie" name="date_saisie" 
                                   value="<?= htmlspecialchars($old['date_saisie'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Informations supplémentaires..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Enregistrer le don
                            </button>
                            <a href="/dons-argent" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exemples suggérés -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Exemples de dons</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setMontant(50000)">50 000 Ar</button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setMontant(100000)">100 000 Ar</button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setMontant(150000)">150 000 Ar</button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setMontant(200000)">200 000 Ar</button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setMontant(500000)">500 000 Ar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setMontant(montant) {
    document.getElementById('montant').value = montant;
}
</script>

<?php $this->render('layout/footer'); ?>
