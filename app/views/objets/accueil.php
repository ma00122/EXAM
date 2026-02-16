<?php include __DIR__ . '/../layout/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1><i class="bi bi-arrow-left-right"></i> Bienvenue sur Takalo-Takalo</h1>
        <p class="lead">Échangez vos objets facilement avec d'autres utilisateurs</p>
        <?php if (!isset($_SESSION['user'])): ?>
        <div class="mt-4">
            <a href="/register" class="btn btn-light btn-lg me-2">
                <i class="bi bi-person-plus"></i> S'inscrire
            </a>
            <a href="/objets" class="btn btn-outline-light btn-lg">
                <i class="bi bi-grid"></i> Voir les objets
            </a>
        </div>
        <?php else: ?>
        <div class="mt-4">
            <a href="/mes-objets/nouveau" class="btn btn-light btn-lg me-2">
                <i class="bi bi-plus-circle"></i> Ajouter un objet
            </a>
            <a href="/objets" class="btn btn-outline-light btn-lg">
                <i class="bi bi-grid"></i> Parcourir les objets
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="container py-5">
    <!-- Catégories -->
    <section class="mb-5">
        <h2 class="mb-4"><i class="bi bi-tags"></i> Catégories</h2>
        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="/objets?category=<?= $category->id ?>" class="btn btn-outline-primary w-100">
                    <?= call_user_func($e, $category->nom) ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Derniers objets -->
    <section>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clock-history"></i> Derniers objets ajoutés</h2>
            <a href="/objets" class="btn btn-primary">Voir tous les objets <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if (empty($objets)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Aucun objet disponible pour le moment.
        </div>
        <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($objets as $objet): ?>
            <div class="col">
                <div class="card h-100 card-objet">
                    <?php $photo = $objet->getPhotoPrincipale(); ?>
                    <?php if ($photo): ?>
                    <img src="<?= call_user_func($e, $photo->getUrl()) ?>" class="card-img-top" 
                         alt="<?= call_user_func($e, $objet->titre) ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= call_user_func($e, $objet->titre) ?></h5>
                        <p class="card-text text-muted small">
                            <i class="bi bi-tag"></i> <?= call_user_func($e, $objet->category->nom ?? 'N/A') ?>
                        </p>
                        <p class="prix-estimatif mb-2"><?= $objet->getPrixFormate() ?></p>
                        <p class="card-text small">
                            <i class="bi bi-person"></i> 
                            <?= call_user_func($e, ($objet->proprietaire->prenom ?? '') . ' ' . ($objet->proprietaire->nom ?? '')) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="/objets/<?= $objet->id ?>" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-eye"></i> Voir détail
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
