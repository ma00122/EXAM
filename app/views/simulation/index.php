<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-play-circle-fill text-info"></i> Simulation d'Attribution
                </h1>
                <div>
                    <a href="/dons" class="btn btn-success me-2">
                        <i class="bi bi-gift"></i> Dons
                    </a>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages flash -->
            <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_dons'] ?></h3>
                            <p class="mb-0">Dons</p>
                            <small><?= number_format($stats['total_dons']) ?> unités</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_besoins'] ?? 0 ?></h3>
                            <p class="mb-0">Besoins</p>
                            <small><?= number_format($stats['total_besoins'] ?? 0) ?> unités</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['nombre_attributions'] ?></h3>
                            <p class="mb-0">Attributions</p>
                            <small><?= number_format($stats['total_attribue']) ?> unités attribuées</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-<?= $stats['pourcentage_attribue'] >= 75 ? 'success' : ($stats['pourcentage_attribue'] >= 50 ? 'warning' : 'secondary') ?> text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['pourcentage_attribue'] ?>%</h3>
                            <p class="mb-0">Attribué</p>
                            <small><?= number_format($stats['total_restant']) ?> unités restantes</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- CHOIX DU MODE DE SIMULATION V3 -->
            <!-- ============================================== -->
            <?php $canSimulate = !empty($besoins) && !empty($dons); ?>
            <?php $selectedMode = $_GET['mode'] ?? 'chronologique'; ?>
            
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-cpu"></i> <strong>Choix du Mode de Simulation</strong>
                </div>
                <div class="card-body">
                    <!-- Menu déroulant de sélection du mode -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="modeSelect" class="form-label fw-bold">
                                <i class="bi bi-sliders me-2"></i>Sélectionner le mode de simulation :
                            </label>
                            <select class="form-select form-select-lg" id="modeSelect" name="mode">
                                <option value="chronologique" <?= $selectedMode === 'chronologique' ? 'selected' : '' ?>>
                                    📅 Mode 1: Chronologique (FIFO - par date)
                                </option>
                                <option value="croissant" <?= $selectedMode === 'croissant' ? 'selected' : '' ?>>
                                    📊 Mode 2: Croissant (petits besoins d'abord)
                                </option>
                                <option value="proportionnel" <?= $selectedMode === 'proportionnel' ? 'selected' : '' ?>>
                                    ⚖️ Mode 3: Proportionnel (répartition équitable)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <!-- Explication du mode sélectionné -->
                            <div id="modeExplication" class="alert alert-info h-100 mb-0">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i><span id="modeTitre">Mode Chronologique (FIFO)</span></h6>
                                <p class="mb-1" id="modeDesc">Distribution par ordre de date de saisie. Les besoins enregistrés en premier sont satisfaits en premier.</p>
                                <hr class="my-2">
                                <p class="mb-0 small" id="modeExemple"><strong>Exemple RIZ (Don=100):</strong> Tana(30)→Toamasina(50)→Mahajanga(20)→Fianara(0)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cartes visuelles des 3 modes -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card h-100 mode-card <?= $selectedMode === 'chronologique' ? 'border-primary bg-light' : '' ?>" 
                                 data-mode="chronologique" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="fs-1 mb-2">📅</div>
                                    <h5 class="card-title">Chronologique</h5>
                                    <p class="card-text small text-muted">Premier arrivé, premier servi (FIFO)</p>
                                    <span class="badge bg-secondary">Tana=30✓ Toamasina=50✓</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 mode-card <?= $selectedMode === 'croissant' ? 'border-primary bg-light' : '' ?>" 
                                 data-mode="croissant" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="fs-1 mb-2">📊</div>
                                    <h5 class="card-title">Croissant</h5>
                                    <p class="card-text small text-muted">Petits besoins satisfaits d'abord</p>
                                    <span class="badge bg-success">3 villes complètes</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 mode-card <?= $selectedMode === 'proportionnel' ? 'border-primary bg-light' : '' ?>" 
                                 data-mode="proportionnel" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="fs-1 mb-2">⚖️</div>
                                    <h5 class="card-title">Proportionnel</h5>
                                    <p class="card-text small text-muted">Répartition équitable selon le poids</p>
                                    <span class="badge bg-warning text-dark">4 villes partielles</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions de simulation -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gear"></i> Actions
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5>Algorithme de simulation</h5>
                            <?php if ($canSimulate): ?>
                            <p class="text-success mb-0">
                                <i class="bi bi-check-circle"></i>
                                <strong><?= count($dons) ?> don(s)</strong> et <strong><?= count($besoins) ?> besoin(s)</strong> prêts pour la simulation.
                            </p>
                            <?php else: ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                La simulation nécessite des <a href="/dons"><strong>dons</strong></a> et des <a href="/besoins"><strong>besoins</strong></a> pour fonctionner.
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <!-- Bouton PRÉVISUALISER avec mode -->
                            <form action="/simulation/preview" method="POST" class="d-inline" id="formPreview">
                                <input type="hidden" name="mode" id="hiddenModePreview" value="<?= htmlspecialchars($selectedMode) ?>">
                                <button type="submit" class="btn btn-lg btn-<?= $canSimulate ? 'info' : 'secondary' ?>" 
                                        <?= !$canSimulate ? 'disabled' : '' ?>>
                                    <i class="bi bi-eye"></i> Prévisualiser
                                </button>
                            </form>
                            
                            <!-- Bouton EXÉCUTER avec mode -->
                            <form action="/simulation/run" method="POST" class="d-inline" id="formRun">
                                <input type="hidden" name="mode" id="hiddenModeRun" value="<?= htmlspecialchars($selectedMode) ?>">
                                <button type="submit" class="btn btn-lg btn-<?= $canSimulate ? 'primary' : 'secondary' ?>" 
                                        <?= !$canSimulate ? 'disabled' : '' ?>
                                        onclick="return confirm('Exécuter la simulation en mode ' + document.getElementById('modeSelect').value + ' ?')">
                                    <i class="bi bi-play-fill"></i> Exécuter
                                </button>
                            </form>
                            
                            <?php if ($stats['nombre_attributions'] > 0): ?>
                            <form action="/simulation/reset" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-lg btn-outline-danger"
                                        onclick="return confirm('Réinitialiser toutes les attributions ?')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <a href="/recap" class="btn btn-lg btn-outline-primary ms-2">
                                <i class="bi bi-bar-chart"></i> Récap
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des dons -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-gift"></i> Dons disponibles
                    <span class="badge bg-light text-dark float-end"><?= count($dons) ?></span>
                </div>
                <?php if (empty($dons)): ?>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucun don enregistré. <a href="/dons/create">Ajouter un don</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dons as $don): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $don['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-box-seam text-success"></i>
                                    <?= htmlspecialchars($don['type_produit']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= number_format($don['quantite']) ?></span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($don['date_saisie'])) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Liste des besoins -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-box-seam"></i> Besoins des villes
                    <span class="badge bg-dark float-end"><?= count($besoins) ?></span>
                </div>
                <?php if (empty($besoins)): ?>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucun besoin enregistré. <a href="/besoins/create">Ajouter un besoin</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th>Ville</th>
                                <th class="text-center">Quantité</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besoins as $besoin): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $besoin['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-box text-warning"></i>
                                    <?= htmlspecialchars($besoin['produit']) ?>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <?= htmlspecialchars($besoin['ville_nom'] ?? 'N/A') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= number_format($besoin['quantite']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($besoin['type_nom'] ?? 'N/A') ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Résultats des attributions -->
            <?php if (!empty($attributions)): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-arrow-left-right"></i> Attributions effectuées
                    <span class="badge bg-light text-dark float-end"><?= count($attributions) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Don (Produit)</th>
                                <th>→</th>
                                <th>Besoin (Ville)</th>
                                <th class="text-center">Quantité attribuée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attributions as $attr): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $attr['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-gift text-success"></i>
                                    <a href="/dons/<?= $attr['don_id'] ?>">
                                        <?= htmlspecialchars($attr['type_produit']) ?>
                                    </a>
                                    <small class="text-muted">(Don #<?= $attr['don_id'] ?>)</small>
                                </td>
                                <td><i class="bi bi-arrow-right text-info fs-5"></i></td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong><?= htmlspecialchars($attr['ville_nom'] ?? 'Ville #' . $attr['ville_id']) ?></strong>
                                    <small class="text-muted">(Besoin #<?= $attr['besoin_id'] ?>)</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6"><?= number_format($attr['quantite_attribuee']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Explication de l'algorithme -->
            <div class="card mt-4 border-secondary">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Comment fonctionne l'algorithme ?
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li><strong>Mode Chronologique:</strong> Les dons et besoins sont triés par date de saisie (FIFO)</li>
                        <li><strong>Mode Croissant:</strong> Les besoins les plus petits sont satisfaits en premier</li>
                        <li><strong>Mode Proportionnel:</strong> Chaque ville reçoit une part proportionnelle à son besoin</li>
                        <li>La quantité attribuée = <code>min(don disponible, besoin restant)</code></li>
                        <li>L'attribution est enregistrée et les quantités sont mises à jour</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script de synchronisation mode -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeSelect = document.getElementById('modeSelect');
    const modeCards = document.querySelectorAll('.mode-card');
    const hiddenModePreview = document.getElementById('hiddenModePreview');
    const hiddenModeRun = document.getElementById('hiddenModeRun');
    
    // Explications des modes
    const modes = {
        chronologique: {
            titre: 'Mode Chronologique (FIFO)',
            desc: 'Distribution par ordre de date de saisie. Les besoins enregistrés en premier sont satisfaits en premier.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Tana=30✓, Toamasina=50✓, Mahajanga=20/40⚠️, Fianara=0/20❌'
        },
        croissant: {
            titre: 'Mode Croissant (Petits d\'abord)',
            desc: 'Priorité aux villes avec les plus petits besoins. Permet de satisfaire complètement plus de villes.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Fianara=20✓, Tana=30✓, Mahajanga=40✓, Toamasina=10/50⚠️'
        },
        proportionnel: {
            titre: 'Mode Proportionnel (Équitable)',
            desc: 'Répartition selon le poids des besoins. Formule: part = (besoin_ville / total) × don. Arrondi vers le bas.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Tana=21, Toamasina=36, Mahajanga=29, Fianara=14 (tous partiels)'
        }
    };
    
    function updateExplication(mode) {
        const m = modes[mode];
        document.getElementById('modeTitre').textContent = m.titre;
        document.getElementById('modeDesc').textContent = m.desc;
        document.getElementById('modeExemple').innerHTML = m.exemple;
    }
    
    function selectMode(mode) {
        // Mettre à jour le select
        modeSelect.value = mode;
        
        // Mettre à jour les champs cachés
        if (hiddenModePreview) hiddenModePreview.value = mode;
        if (hiddenModeRun) hiddenModeRun.value = mode;
        
        // Mettre à jour les cartes visuellement
        modeCards.forEach(card => {
            if (card.dataset.mode === mode) {
                card.classList.add('border-primary', 'bg-light');
            } else {
                card.classList.remove('border-primary', 'bg-light');
            }
        });
        
        // Mettre à jour l'explication
        updateExplication(mode);
        
        // Mettre à jour l'URL sans recharger
        const url = new URL(window.location);
        url.searchParams.set('mode', mode);
        window.history.replaceState({}, '', url);
    }
    
    // Événement sur le menu déroulant
    modeSelect.addEventListener('change', function() {
        selectMode(this.value);
    });
    
    // Événement sur les cartes
    modeCards.forEach(card => {
        card.addEventListener('click', function() {
            selectMode(this.dataset.mode);
        });
    });
    
    // Initialiser avec le mode actuel
    updateExplication(modeSelect.value);
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
