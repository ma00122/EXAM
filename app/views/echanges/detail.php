<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/echanges">Mes échanges</a></li>
            <li class="breadcrumb-item active">Détail de l'échange</li>
        </ol>
    </nav>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-arrow-left-right"></i> Échange #<?= $echange->id ?></h4>
            <span class="badge bg-<?= $echange->getStatutClass() ?> fs-6"><?= $echange->getStatutLabel() ?></span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Date de proposition :</strong></p>
                    <p class="text-muted"><?= date('d/m/Y à H:i', strtotime($echange->date_proposition)) ?></p>
                </div>
                <?php if ($echange->date_reponse): ?>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Date de réponse :</strong></p>
                    <p class="text-muted"><?= date('d/m/Y à H:i', strtotime($echange->date_reponse)) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($echange->message): ?>
            <div class="alert alert-secondary">
                <strong><i class="bi bi-chat-quote"></i> Message :</strong>
                <p class="mb-0 mt-2"><?= nl2br(call_user_func($e, $echange->message)) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Objet demandé -->
        <div class="col-md-5">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box"></i> Objet demandé
                        <?php if ($objetDemande->user_id === $_SESSION['user']['id']): ?>
                        <small>(votre objet)</small>
                        <?php endif; ?>
                    </h5>
                </div>
                <?php $photoDemande = $objetDemande->getPhotoPrincipale(); ?>
                <?php if ($photoDemande): ?>
                <img src="<?= call_user_func($e, $photoDemande->getUrl()) ?>" class="card-img-top" 
                     alt="<?= call_user_func($e, $objetDemande->titre) ?>" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-info mb-2"><?= call_user_func($e, $objetDemande->category->nom ?? '') ?></span>
                    <h5 class="card-title"><?= call_user_func($e, $objetDemande->titre) ?></h5>
                    <p class="prix-estimatif"><?= $objetDemande->getPrixFormate() ?></p>
                    <hr>
                    <p class="mb-0 small">
                        <strong>Propriétaire :</strong><br>
                        <i class="bi bi-person"></i> 
                        <?= call_user_func($e, ($objetDemande->proprietaire->prenom ?? '') . ' ' . ($objetDemande->proprietaire->nom ?? '')) ?>
                    </p>
                </div>
                <div class="card-footer">
                    <a href="/objets/<?= $objetDemande->id ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Voir l'objet
                    </a>
                </div>
            </div>
        </div>

        <!-- Flèche centrale -->
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <i class="bi bi-arrow-left-right text-primary" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">Échange</p>
            </div>
        </div>

        <!-- Objet proposé -->
        <div class="col-md-5">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box"></i> Objet proposé
                        <?php if ($objetPropose->user_id === $_SESSION['user']['id']): ?>
                        <small>(votre objet)</small>
                        <?php endif; ?>
                    </h5>
                </div>
                <?php $photoPropose = $objetPropose->getPhotoPrincipale(); ?>
                <?php if ($photoPropose): ?>
                <img src="<?= call_user_func($e, $photoPropose->getUrl()) ?>" class="card-img-top" 
                     alt="<?= call_user_func($e, $objetPropose->titre) ?>" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-info mb-2"><?= call_user_func($e, $objetPropose->category->nom ?? '') ?></span>
                    <h5 class="card-title"><?= call_user_func($e, $objetPropose->titre) ?></h5>
                    <p class="prix-estimatif"><?= $objetPropose->getPrixFormate() ?></p>
                    <hr>
                    <p class="mb-0 small">
                        <strong>Propriétaire :</strong><br>
                        <i class="bi bi-person"></i> 
                        <?= call_user_func($e, ($objetPropose->proprietaire->prenom ?? '') . ' ' . ($objetPropose->proprietaire->nom ?? '')) ?>
                    </p>
                </div>
                <div class="card-footer">
                    <a href="/objets/<?= $objetPropose->id ?>" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-eye"></i> Voir l'objet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <?php if ($canRespond): ?>
    <div class="card mt-4">
        <div class="card-body text-center">
            <h5>Que souhaitez-vous faire ?</h5>
            <div class="d-flex justify-content-center gap-3 mt-3">
                <form action="/echanges/<?= $echange->id ?>/accepter" method="POST"
                      onsubmit="return confirm('Accepter cet échange ? Les objets changeront de propriétaire.');">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-lg"></i> Accepter l'échange
                    </button>
                </form>
                <form action="/echanges/<?= $echange->id ?>/refuser" method="POST"
                      onsubmit="return confirm('Refuser cet échange ?');">
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-x-lg"></i> Refuser l'échange
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="/echanges" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour aux échanges
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
