<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-box"></i> Mes objets</h1>
        <a href="/mes-objets/nouveau" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un objet
        </a>
    </div>

    <?php if ($success === 'created'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> Objet ajouté avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($success === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> Objet modifié avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($success === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> Objet supprimé avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($objets)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Vous n'avez pas encore ajouté d'objet.
        <a href="/mes-objets/nouveau" class="alert-link">Ajouter votre premier objet</a>
    </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($objets as $objet): ?>
        <div class="col">
            <div class="card h-100">
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
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-info"><?= call_user_func($e, $objet->category->nom ?? 'N/A') ?></span>
                        <span class="badge badge-statut-<?= $objet->statut ?>"><?= ucfirst($objet->statut) ?></span>
                    </div>
                    <h5 class="card-title"><?= call_user_func($e, $objet->titre) ?></h5>
                    <p class="prix-estimatif mb-2"><?= $objet->getPrixFormate() ?></p>
                    <p class="card-text small text-muted">
                        <?= mb_substr($objet->description, 0, 100) ?>...
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <!-- Liens pour filtrage par prix -->
                    <div class="mb-2">
                        <small class="text-muted">Objets similaires :</small>
                        <div class="btn-group btn-group-sm w-100">
                            <a href="/mes-objets/<?= $objet->id ?>/prix/10" class="btn btn-outline-info">
                                <i class="bi bi-search"></i> ±10%
                            </a>
                            <a href="/mes-objets/<?= $objet->id ?>/prix/20" class="btn btn-outline-info">
                                <i class="bi bi-search"></i> ±20%
                            </a>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="/objets/<?= $objet->id ?>" class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <?php if ($objet->statut === 'disponible'): ?>
                        <a href="/mes-objets/<?= $objet->id ?>/modifier" class="btn btn-outline-warning btn-sm flex-fill">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <form action="/mes-objets/<?= $objet->id ?>/supprimer" method="POST" class="flex-fill"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet objet ?');">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
