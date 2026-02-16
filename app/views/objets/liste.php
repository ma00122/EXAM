<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar avec filtres -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
                </div>
                <div class="card-body">
                    <form action="/objets" method="GET">
                        <!-- Recherche -->
                        <div class="mb-3">
                            <label for="q" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="q" name="q" 
                                   value="<?= call_user_func($e, $search ?? '') ?>" 
                                   placeholder="Titre, description...">
                        </div>

                        <!-- Catégorie -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" 
                                        <?= ($selectedCategory ?? null) === $category->id ? 'selected' : '' ?>>
                                    <?= call_user_func($e, $category->nom) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Rechercher
                        </button>

                        <?php if ($search || $selectedCategory): ?>
                        <a href="/objets" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-x-circle"></i> Effacer les filtres
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Catégories rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-tags"></i> Catégories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($categories as $category): ?>
                    <a href="/objets?category=<?= $category->id ?>" 
                       class="list-group-item list-group-item-action <?= ($selectedCategory ?? null) === $category->id ? 'active' : '' ?>">
                        <?= call_user_func($e, $category->nom) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Liste des objets -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-grid"></i> Objets disponibles
                    <span class="badge bg-primary"><?= count($objets) ?></span>
                </h2>
            </div>

            <?php if ($search): ?>
            <div class="alert alert-info">
                <i class="bi bi-search"></i> Résultats pour : <strong><?= call_user_func($e, $search) ?></strong>
            </div>
            <?php endif; ?>

            <?php if (empty($objets)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Aucun objet trouvé.
                <?php if ($search || $selectedCategory): ?>
                <a href="/objets">Voir tous les objets</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($objets as $objet): ?>
                <div class="col">
                    <div class="card h-100 card-objet">
                        <?php $photo = $objet->getPhotoPrincipale(); ?>
                        <?php if ($photo): ?>
                        <img src="<?= call_user_func($e, $photo->getUrl()) ?>" class="card-img-top" 
                             alt="<?= call_user_func($e, $objet->titre) ?>" style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 180px;">
                            <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge bg-info mb-2"><?= call_user_func($e, $objet->category->nom ?? 'N/A') ?></span>
                            <h5 class="card-title"><?= call_user_func($e, $objet->titre) ?></h5>
                            <p class="prix-estimatif mb-2"><?= $objet->getPrixFormate() ?></p>
                            <p class="card-text small text-muted">
                                <i class="bi bi-person"></i> 
                                <?= call_user_func($e, ($objet->proprietaire->prenom ?? '') . ' ' . ($objet->proprietaire->nom ?? '')) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-2">
                                <a href="/objets/<?= $objet->id ?>" class="btn btn-primary btn-sm flex-fill">
                                    <i class="bi bi-eye"></i> Détail
                                </a>
                                <?php if (isset($_SESSION['user'])): ?>
                                <a href="/echanges/proposer/<?= $objet->id ?>" class="btn btn-success btn-sm flex-fill">
                                    <i class="bi bi-arrow-left-right"></i> Échanger
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
