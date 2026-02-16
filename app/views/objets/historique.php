<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/objets">Objets</a></li>
            <li class="breadcrumb-item"><a href="/objets/<?= $objet->id ?>"><?= call_user_func($e, $objet->titre) ?></a></li>
            <li class="breadcrumb-item active">Historique des propriétaires</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <?php $photo = $objet->getPhotoPrincipale(); ?>
                <?php if ($photo): ?>
                <img src="<?= call_user_func($e, $photo->getUrl()) ?>" class="card-img-top" 
                     alt="<?= call_user_func($e, $objet->titre) ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= call_user_func($e, $objet->titre) ?></h5>
                    <p class="prix-estimatif"><?= $objet->getPrixFormate() ?></p>
                    <a href="/objets/<?= $objet->id ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye"></i> Voir l'objet
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <h2><i class="bi bi-clock-history"></i> Historique des propriétaires</h2>
            <p class="text-muted">
                Cet objet a eu <strong><?= count($historique) ?></strong> propriétaire(s) depuis sa création.
            </p>

            <?php if (empty($historique)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Aucun historique disponible pour cet objet.
            </div>
            <?php else: ?>
            <div class="timeline">
                <?php foreach ($historique as $index => $hist): ?>
                <div class="card mb-3 <?= $index === 0 ? 'border-success' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">
                                    <i class="bi bi-person-circle"></i>
                                    <?= call_user_func($e, ($hist->user->prenom ?? '') . ' ' . ($hist->user->nom ?? '')) ?>
                                    <?php if ($index === 0): ?>
                                    <span class="badge bg-success">Propriétaire actuel</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-envelope"></i> <?= call_user_func($e, $hist->user->email ?? '') ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= $hist->isPremierProprietaire() ? 'bg-primary' : 'bg-info' ?>">
                                    <?= $hist->isPremierProprietaire() ? 'Création' : 'Échange' ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-0 small">
                            <i class="bi bi-calendar"></i> 
                            <strong>Date d'acquisition :</strong> <?= $hist->getDateFormatee() ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
