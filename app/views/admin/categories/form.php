<?php include __DIR__ . '/../../layout/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-<?= isset($category) ? 'pencil' : 'plus-circle' ?>"></i> 
        <?= isset($category) ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?>
    </h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= call_user_func($e, $error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= isset($category) ? '/admin/categories/' . $category->id . '/modifier' : '/admin/categories/nouveau' ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" 
                               value="<?= call_user_func($e, $old['nom'] ?? (isset($category) ? $category->nom : '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                        ><?= call_user_func($e, $old['description'] ?? (isset($category) ? $category->description : '')) ?></textarea>
                        <div class="form-text">Description optionnelle de la catégorie</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> 
                            <?= isset($category) ? 'Enregistrer les modifications' : 'Créer la catégorie' ?>
                        </button>
                        <a href="/admin/categories" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-light">
            <div class="card-body">
                <h5><i class="bi bi-info-circle"></i> Aide</h5>
                <p>Les catégories permettent de classer les objets mis en échange sur la plateforme.</p>
                <ul>
                    <li>Le <strong>nom</strong> de la catégorie doit être unique</li>
                    <li>La <strong>description</strong> est optionnelle mais recommandée</li>
                </ul>
                <?php if (isset($category) && $category->countObjets() > 0): ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Cette catégorie contient <strong><?= $category->countObjets() ?> objet(s)</strong>. 
                    La suppression n'est pas recommandée.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
