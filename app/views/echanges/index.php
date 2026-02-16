<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4"><i class="bi bi-arrow-left-right"></i> Mes échanges</h1>

    <?php if ($success === 'proposed'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> Votre proposition d'échange a été envoyée !
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($success === 'accepted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> L'échange a été accepté ! Les objets ont changé de propriétaire.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($success === 'refused'): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle"></i> L'échange a été refusé.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle"></i> <?= call_user_func($e, $error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Onglets -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $tab === 'recus' ? 'active' : '' ?>" href="/echanges?tab=recus">
                <i class="bi bi-inbox"></i> Propositions reçues
                <?php if (count($echangesRecusEnAttente) > 0): ?>
                <span class="badge bg-danger"><?= count($echangesRecusEnAttente) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $tab === 'envoyes' ? 'active' : '' ?>" href="/echanges?tab=envoyes">
                <i class="bi bi-send"></i> Propositions envoyées
                <span class="badge bg-secondary"><?= count($echangesEnvoyes) ?></span>
            </a>
        </li>
    </ul>

    <!-- Contenu des onglets -->
    <?php if ($tab === 'recus'): ?>
    <!-- Propositions reçues -->
    <?php if (empty($echangesRecus)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Vous n'avez reçu aucune proposition d'échange.
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($echangesRecus as $echange): ?>
        <div class="col-12">
            <div class="card <?= $echange->statut === 'en_attente' ? 'border-warning' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?= $echange->getStatutClass() ?>"><?= $echange->getStatutLabel() ?></span>
                        <span class="ms-2 text-muted small">
                            <i class="bi bi-calendar"></i> 
                            <?= date('d/m/Y H:i', strtotime($echange->date_proposition)) ?>
                        </span>
                    </div>
                    <div>
                        De : <strong><?= call_user_func($e, ($echange->demandeur->prenom ?? '') . ' ' . ($echange->demandeur->nom ?? '')) ?></strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h6 class="text-muted mb-2">Mon objet demandé :</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box me-2 fs-4"></i>
                                <div>
                                    <strong><?= call_user_func($e, $echange->objet_demande->titre ?? '') ?></strong>
                                    <br>
                                    <span class="text-success"><?= number_format($echange->objet_demande->prix_estimatif ?? 0, 0, ',', ' ') ?> Ar</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <i class="bi bi-arrow-left-right fs-2 text-primary"></i>
                        </div>
                        <div class="col-md-5">
                            <h6 class="text-muted mb-2">Objet proposé en échange :</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box me-2 fs-4"></i>
                                <div>
                                    <strong><?= call_user_func($e, $echange->objet_propose->titre ?? '') ?></strong>
                                    <br>
                                    <span class="text-success"><?= number_format($echange->objet_propose->prix_estimatif ?? 0, 0, ',', ' ') ?> Ar</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($echange->message): ?>
                    <hr>
                    <p class="mb-0"><i class="bi bi-chat-quote"></i> <em>"<?= call_user_func($e, $echange->message) ?>"</em></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/echanges/<?= $echange->id ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Voir détails
                        </a>
                        <?php if ($echange->statut === 'en_attente'): ?>
                        <div class="btn-group">
                            <form action="/echanges/<?= $echange->id ?>/accepter" method="POST" class="d-inline"
                                  onsubmit="return confirm('Accepter cet échange ? Les objets changeront de propriétaire.');">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-check-lg"></i> Accepter
                                </button>
                            </form>
                            <form action="/echanges/<?= $echange->id ?>/refuser" method="POST" class="d-inline ms-2"
                                  onsubmit="return confirm('Refuser cet échange ?');">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-lg"></i> Refuser
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Propositions envoyées -->
    <?php if (empty($echangesEnvoyes)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Vous n'avez envoyé aucune proposition d'échange.
        <a href="/objets" class="alert-link">Parcourir les objets disponibles</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($echangesEnvoyes as $echange): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?= $echange->getStatutClass() ?>"><?= $echange->getStatutLabel() ?></span>
                        <span class="ms-2 text-muted small">
                            <i class="bi bi-calendar"></i> 
                            <?= date('d/m/Y H:i', strtotime($echange->date_proposition)) ?>
                        </span>
                    </div>
                    <div>
                        À : <strong><?= call_user_func($e, ($echange->proprietaire->prenom ?? '') . ' ' . ($echange->proprietaire->nom ?? '')) ?></strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h6 class="text-muted mb-2">Mon objet proposé :</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box me-2 fs-4"></i>
                                <div>
                                    <strong><?= call_user_func($e, $echange->objet_propose->titre ?? '') ?></strong>
                                    <br>
                                    <span class="text-success"><?= number_format($echange->objet_propose->prix_estimatif ?? 0, 0, ',', ' ') ?> Ar</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <i class="bi bi-arrow-left-right fs-2 text-primary"></i>
                        </div>
                        <div class="col-md-5">
                            <h6 class="text-muted mb-2">Objet demandé :</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box me-2 fs-4"></i>
                                <div>
                                    <strong><?= call_user_func($e, $echange->objet_demande->titre ?? '') ?></strong>
                                    <br>
                                    <span class="text-success"><?= number_format($echange->objet_demande->prix_estimatif ?? 0, 0, ',', ' ') ?> Ar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="/echanges/<?= $echange->id ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Voir détails
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
