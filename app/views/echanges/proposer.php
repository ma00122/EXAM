<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/objets">Objets</a></li>
            <li class="breadcrumb-item"><a href="/objets/<?= $objetDemande->id ?>"><?= call_user_func($e, $objetDemande->titre) ?></a></li>
            <li class="breadcrumb-item active">Proposer un échange</li>
        </ol>
    </nav>

    <h1 class="mb-4"><i class="bi bi-arrow-left-right"></i> Proposer un échange</h1>

    <div class="row">
        <!-- Objet demandé (côté gauche) -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Objet que vous voulez</h5>
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
                    <p class="text-muted small">
                        <i class="bi bi-person"></i> 
                        <?= call_user_func($e, ($objetDemande->proprietaire->prenom ?? '') . ' ' . ($objetDemande->proprietaire->nom ?? '')) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Flèche centrale -->
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-left-right text-primary" style="font-size: 3rem;"></i>
        </div>

        <!-- Formulaire de proposition (côté droit) -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Votre objet à échanger</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <!-- Sélection de l'objet -->
                        <div class="mb-3">
                            <label class="form-label">Sélectionnez un de vos objets :</label>
                            <?php if (isset($errors['objet_propose_id'])): ?>
                            <div class="alert alert-danger py-2"><?= $errors['objet_propose_id'] ?></div>
                            <?php endif; ?>

                            <div class="list-group">
                                <?php foreach ($mesObjets as $objet): ?>
                                <label class="list-group-item list-group-item-action d-flex align-items-center">
                                    <input type="radio" name="objet_propose_id" value="<?= $objet->id ?>" 
                                           class="form-check-input me-3" required
                                           <?= ($_GET['avec'] ?? '') == $objet->id ? 'checked' : '' ?>>
                                    <?php $photo = $objet->getPhotoPrincipale(); ?>
                                    <?php if ($photo): ?>
                                    <img src="<?= call_user_func($e, $photo->getUrl()) ?>" 
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <strong><?= call_user_func($e, $objet->titre) ?></strong>
                                        <br>
                                        <small class="text-success"><?= $objet->getPrixFormate() ?></small>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Message (optionnel) -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Message (optionnel)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                      placeholder="Ajoutez un message pour accompagner votre proposition..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-send"></i> Envoyer la proposition
                            </button>
                            <a href="/objets/<?= $objetDemande->id ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Note d'information -->
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i>
        <strong>Comment ça marche ?</strong>
        <ul class="mb-0 mt-2">
            <li>Sélectionnez un de vos objets à proposer en échange.</li>
            <li>Le propriétaire de l'objet demandé recevra votre proposition.</li>
            <li>S'il accepte, les deux objets changeront automatiquement de propriétaire.</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
