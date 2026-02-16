<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/mes-objets">Mes objets</a></li>
            <li class="breadcrumb-item active">Objets similaires (±<?= $pourcentage ?>%)</li>
        </ol>
    </nav>

    <div class="card mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">Objet de référence : <?= call_user_func($e, $objetReference->titre) ?></h5>
                    <p class="mb-0 text-muted">
                        Prix estimatif : <strong class="text-success"><?= $objetReference->getPrixFormate() ?></strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-info fs-6">
                        Fourchette : <?= number_format($prixMin, 0, ',', ' ') ?> - <?= number_format($prixMax, 0, ',', ' ') ?> Ar
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-search"></i> Objets dans la fourchette ±<?= $pourcentage ?>%
            <span class="badge bg-primary"><?= count($objets) ?></span>
        </h2>
        <div class="btn-group">
            <a href="/mes-objets/<?= $objetReference->id ?>/prix/10" 
               class="btn <?= $pourcentage === 10 ? 'btn-primary' : 'btn-outline-primary' ?>">±10%</a>
            <a href="/mes-objets/<?= $objetReference->id ?>/prix/20" 
               class="btn <?= $pourcentage === 20 ? 'btn-primary' : 'btn-outline-primary' ?>">±20%</a>
        </div>
    </div>

    <?php if (empty($objets)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Aucun objet trouvé dans cette fourchette de prix.
        <br>Essayez d'élargir la recherche avec ±20%.
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
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
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
                        <!-- Lien pour proposer un échange directement -->
                        <a href="/echanges/proposer/<?= $objet->id ?>?avec=<?= $objetReference->id ?>" 
                           class="btn btn-success btn-sm flex-fill">
                            <i class="bi bi-arrow-left-right"></i> Échanger
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="/mes-objets" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à mes objets
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
