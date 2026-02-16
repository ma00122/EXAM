<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> <?= htmlspecialchars($pageTitle) ?></h1>
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
                    <form action="/attributions/store" method="POST">
                        <div class="mb-3">
                            <label for="don_id" class="form-label">Don <span class="text-danger">*</span></label>
                            <select class="form-select" id="don_id" name="don_id" required>
                                <option value="">-- Sélectionner un don --</option>
                                <?php foreach ($dons as $don): ?>
                                    <?php if ($don['quantite_restante'] > 0): ?>
                                        <option value="<?= $don['id'] ?>" 
                                                data-restant="<?= $don['quantite_restante'] ?>"
                                                <?= ($old['don_id'] ?? '') == $don['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($don['type_produit']) ?> 
                                            - Disponible: <?= $don['quantite_restante'] ?> / <?= $don['quantite'] ?>
                                            (<?= date('d/m/Y', strtotime($don['date_saisie'])) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <span id="don-info" class="text-muted">Sélectionnez un don pour voir la quantité disponible.</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="besoin_id" class="form-label">Besoin <span class="text-danger">*</span></label>
                            <select class="form-select" id="besoin_id" name="besoin_id" required>
                                <option value="">-- Sélectionner un besoin --</option>
                                <?php foreach ($besoins as $besoin): ?>
                                    <option value="<?= $besoin['id'] ?>" <?= ($old['besoin_id'] ?? '') == $besoin['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($besoin['ville_nom']) ?> - <?= htmlspecialchars($besoin['produit']) ?> 
                                        (Qté: <?= $besoin['quantite'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="quantite_attribuee" class="form-label">Quantité à attribuer <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantite_attribuee" name="quantite_attribuee" 
                                   value="<?= htmlspecialchars($old['quantite_attribuee'] ?? 1) ?>" min="1" required>
                            <div id="quantite-warning" class="form-text text-danger d-none">
                                Attention: quantité supérieure au disponible!
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Créer l'attribution
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
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Information</h5>
                </div>
                <div class="card-body">
                    <p>Une attribution lie un <strong>don</strong> à un <strong>besoin</strong>.</p>
                    <p>Seuls les dons ayant une quantité disponible sont affichés.</p>
                    <hr>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lightbulb"></i> Astuce: Utilisez la 
                        <a href="/simulation">simulation automatique</a> 
                        pour attribuer tous les dons d'un coup.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('don_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const info = document.getElementById('don-info');
    const quantiteInput = document.getElementById('quantite_attribuee');
    
    if (selectedOption.value && selectedOption.dataset.restant) {
        const restant = parseInt(selectedOption.dataset.restant);
        info.textContent = 'Quantité disponible: ' + restant;
        info.className = 'text-success';
        quantiteInput.max = restant;
    } else {
        info.textContent = 'Sélectionnez un don pour voir la quantité disponible.';
        info.className = 'text-muted';
        quantiteInput.removeAttribute('max');
    }
});

document.getElementById('quantite_attribuee').addEventListener('input', function() {
    const donSelect = document.getElementById('don_id');
    const selectedOption = donSelect.options[donSelect.selectedIndex];
    const warning = document.getElementById('quantite-warning');
    
    if (selectedOption.value && selectedOption.dataset.restant) {
        const restant = parseInt(selectedOption.dataset.restant);
        if (parseInt(this.value) > restant) {
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    }
});
</script>

<?php $this->render('layout/footer'); ?>
