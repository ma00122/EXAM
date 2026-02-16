<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/mes-objets">Mes objets</a></li>
            <li class="breadcrumb-item active"><?= $action === 'create' ? 'Ajouter' : 'Modifier' ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-<?= $action === 'create' ? 'plus-circle' : 'pencil' ?>"></i>
                        <?= $action === 'create' ? 'Ajouter un objet' : 'Modifier l\'objet' ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Titre -->
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre *</label>
                            <input type="text" class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>" 
                                   id="titre" name="titre" value="<?= call_user_func($e, $values['titre']) ?>" required>
                            <?php if (isset($errors['titre'])): ?>
                            <div class="invalid-feedback"><?= $errors['titre'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Catégorie -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Catégorie *</label>
                            <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                    id="category_id" name="category_id" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" 
                                        <?= $values['category_id'] == $category->id ? 'selected' : '' ?>>
                                    <?= call_user_func($e, $category->nom) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback"><?= $errors['category_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                                      id="description" name="description" rows="5" required><?= call_user_func($e, $values['description']) ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= $errors['description'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Décrivez votre objet en détail (état, marque, dimensions, etc.)</div>
                        </div>

                        <!-- Prix estimatif -->
                        <div class="mb-3">
                            <label for="prix_estimatif" class="form-label">Prix estimatif (Ariary) *</label>
                            <div class="input-group">
                                <input type="text" class="form-control <?= isset($errors['prix_estimatif']) ? 'is-invalid' : '' ?>" 
                                       id="prix_estimatif" name="prix_estimatif" 
                                       value="<?= call_user_func($e, $values['prix_estimatif']) ?>" required>
                                <span class="input-group-text">Ar</span>
                                <?php if (isset($errors['prix_estimatif'])): ?>
                                <div class="invalid-feedback"><?= $errors['prix_estimatif'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Estimez la valeur de votre objet pour faciliter les échanges équitables.</div>
                        </div>

                        <!-- Photos -->
                        <div class="mb-3">
                            <label for="photos" class="form-label">Photos</label>
                            <input type="file" class="form-control" id="photos" name="photos[]" 
                                   accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                            <div class="form-text">
                                Formats acceptés : JPG, PNG, GIF, WebP. Taille max : 5 Mo par image.
                            </div>
                        </div>

                        <!-- Photos existantes (en mode édition) -->
                        <?php if ($action === 'edit' && isset($objet) && !empty($objet->photos)): ?>
                        <div class="mb-3">
                            <label class="form-label">Photos actuelles</label>
                            <div class="row g-2">
                                <?php foreach ($objet->photos as $photo): ?>
                                <div class="col-3" id="photo-<?= $photo->id ?>">
                                    <div class="position-relative">
                                        <img src="<?= call_user_func($e, $photo->getUrl()) ?>" 
                                             class="img-thumbnail" style="width: 100%; height: 100px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                                onclick="deletePhoto(<?= $photo->id ?>)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="/mes-objets" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 
                                <?= $action === 'create' ? 'Ajouter l\'objet' : 'Enregistrer les modifications' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($action === 'edit'): ?>
<script>
function deletePhoto(photoId) {
    if (!confirm('Supprimer cette photo ?')) return;
    
    fetch('/photos/' + photoId + '/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('photo-' + photoId).remove();
        } else {
            alert(data.message || 'Erreur lors de la suppression');
        }
    })
    .catch(() => alert('Erreur lors de la suppression'));
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
