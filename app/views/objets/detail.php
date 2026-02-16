<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/objets">Objets</a></li>
            <li class="breadcrumb-item active"><?= call_user_func($e, $objet->titre) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Photos -->
        <div class="col-md-6 mb-4">
            <?php if (!empty($objet->photos)): ?>
            <div id="carouselPhotos" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($objet->photos as $index => $photo): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= call_user_func($e, $photo->getUrl()) ?>" class="d-block w-100 rounded" 
                             alt="Photo <?= $index + 1 ?>" style="max-height: 400px; object-fit: contain; background: #f8f9fa;">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($objet->photos) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
                <?php endif; ?>
            </div>
            <!-- Miniatures -->
            <?php if (count($objet->photos) > 1): ?>
            <div class="d-flex gap-2 mt-3 flex-wrap">
                <?php foreach ($objet->photos as $index => $photo): ?>
                <img src="<?= call_user_func($e, $photo->getUrl()) ?>" 
                     class="rounded cursor-pointer" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                     onclick="document.querySelector('#carouselPhotos').querySelector('.carousel-inner').children[<?= $index ?>].classList.add('active')">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="bg-secondary d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                <i class="bi bi-image text-white" style="font-size: 5rem;"></i>
            </div>
            <?php endif; ?>
        </div>

        <!-- Informations -->
        <div class="col-md-6">
            <span class="badge bg-info mb-2"><?= call_user_func($e, $objet->category->nom ?? 'N/A') ?></span>
            <span class="badge badge-statut-<?= $objet->statut ?>"><?= ucfirst($objet->statut) ?></span>
            
            <h1 class="mb-3"><?= call_user_func($e, $objet->titre) ?></h1>
            
            <p class="prix-estimatif fs-3 mb-3">
                <i class="bi bi-tag"></i> <?= $objet->getPrixFormate() ?>
            </p>

            <div class="card mb-4">
                <div class="card-body">
                    <h5><i class="bi bi-person-circle"></i> Propriétaire</h5>
                    <p class="mb-1">
                        <strong><?= call_user_func($e, ($objet->proprietaire->prenom ?? '') . ' ' . ($objet->proprietaire->nom ?? '')) ?></strong>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-envelope"></i> <?= call_user_func($e, $objet->proprietaire->email ?? '') ?>
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <?php if (isset($_SESSION['user']) && $objet->user_id !== $_SESSION['user']['id'] && $objet->statut === 'disponible'): ?>
            <div class="d-grid gap-2 mb-4">
                <a href="/echanges/proposer/<?= $objet->id ?>" class="btn btn-success btn-lg">
                    <i class="bi bi-arrow-left-right"></i> Proposer un échange
                </a>
            </div>
            <?php elseif (!isset($_SESSION['user'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <a href="/login">Connectez-vous</a> pour proposer un échange.
            </div>
            <?php elseif ($objet->user_id === $_SESSION['user']['id']): ?>
            <div class="alert alert-secondary">
                <i class="bi bi-info-circle"></i> C'est votre objet.
            </div>
            <?php endif; ?>

            <hr>

            <h5><i class="bi bi-text-paragraph"></i> Description</h5>
            <p><?= nl2br(call_user_func($e, $objet->description)) ?></p>

            <hr>

            <p class="text-muted small">
                <i class="bi bi-calendar"></i> Ajouté le <?= date('d/m/Y', strtotime($objet->created_at)) ?>
            </p>

            <!-- Lien vers historique -->
            <a href="/objets/<?= $objet->id ?>/historique" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history"></i> Voir l'historique des propriétaires
            </a>
        </div>
    </div>

    <!-- Historique des propriétaires (résumé) -->
    <?php if (!empty($historique)): ?>
    <hr class="my-5">
    <h3><i class="bi bi-clock-history"></i> Historique des propriétaires</h3>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Propriétaire</th>
                    <th>Date d'acquisition</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historique as $index => $hist): ?>
                <tr>
                    <td><?= count($historique) - $index ?></td>
                    <td>
                        <i class="bi bi-person"></i>
                        <?= call_user_func($e, ($hist->user->prenom ?? '') . ' ' . ($hist->user->nom ?? '')) ?>
                        <?php if ($index === 0): ?>
                        <span class="badge bg-success">Actuel</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $hist->getDateFormatee() ?></td>
                    <td>
                        <?php if ($hist->isPremierProprietaire()): ?>
                        <span class="badge bg-primary">Création</span>
                        <?php else: ?>
                        <span class="badge bg-info">Échange</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
