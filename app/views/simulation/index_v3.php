<?php
/**
 * Vue Simulation V3 - Multi-Mode + Reset Global
 * @author ETU004546 - MAHERY | ETU004141 - SEDRA
 */
$pageTitle = $pageTitle ?? 'Simulation V3';
?>

<?php ob_start(); ?>

<style>
    .mode-card {
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .mode-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }
    .mode-card.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    .mode-card .mode-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .preview-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    .attribution-table th {
        background-color: #343a40;
        color: white;
    }
    .details-calcul {
        background: #2d3436;
        color: #00b894;
        font-family: monospace;
        font-size: 0.85rem;
        padding: 15px;
        border-radius: 5px;
        max-height: 300px;
        overflow-y: auto;
    }
    .stat-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }
    .stat-box.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-box.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .produit-section {
        border-left: 4px solid #0d6efd;
        padding-left: 15px;
        margin-bottom: 20px;
    }
    .btn-reset {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        border: none;
    }
    .btn-reset:hover {
        background: linear-gradient(135deg, #f45c43 0%, #eb3349 100%);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="bi bi-cpu me-2"></i>Simulation V3 - Multi-Mode
            </h1>
            <p class="text-muted mb-0">Testez différentes stratégies de distribution des dons</p>
        </div>
        <div>
            <button type="button" class="btn btn-danger btn-reset btn-lg" id="btnResetGlobal">
                <i class="bi bi-arrow-counterclockwise me-2"></i>RESET GLOBAL
            </button>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques actuelles -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <h3 class="mb-0"><?= count($dons ?? []) ?></h3>
                <small>Dons disponibles</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box success">
                <h3 class="mb-0"><?= count($besoins ?? []) ?></h3>
                <small>Besoins non satisfaits</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box warning">
                <h3 class="mb-0"><?= count($attributions ?? []) ?></h3>
                <small>Attributions existantes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h3 class="mb-0"><?= $fraisPourcentage ?? 5 ?>%</h3>
                <small>Frais d'achat</small>
            </div>
        </div>
    </div>

    <!-- Choix du mode de simulation -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-gear me-2"></i>1. Choisir le mode de simulation</h5>
        </div>
        <div class="card-body">
            <!-- Menu déroulant de sélection rapide -->
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <label for="modeSelect" class="form-label fw-bold">
                        <i class="bi bi-list-ul me-2"></i>Sélectionner le mode de simulation :
                    </label>
                    <select class="form-select form-select-lg" id="modeSelect">
                        <option value="chronologique">📅 Mode 1: Chronologique (FIFO - par date)</option>
                        <option value="croissant">📊 Mode 2: Croissant (petits besoins d'abord)</option>
                        <option value="proportionnel">⚖️ Mode 3: Proportionnel (selon le poids)</option>
                    </select>
                </div>
            </div>

            <!-- Explication du mode sélectionné -->
            <div class="alert alert-info mb-4" id="modeExplication">
                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Mode Chronologique (FIFO)</h6>
                <p class="mb-0">Distribution par ordre de date de saisie. Les besoins enregistrés en premier sont satisfaits en premier.</p>
                <hr>
                <p class="mb-0 small"><strong>Exemple RIZ (Don=100):</strong> Tana(30)→Toamasina(50)→Mahajanga(40)→Fianara(20)<br>
                <strong>Résultat:</strong> Tana=30✓, Toamasina=50✓, Mahajanga=20/40⚠️, Fianara=0/20❌</p>
            </div>

            <div class="row">
                <!-- Mode Chronologique -->
                <div class="col-md-4">
                    <div class="mode-card card h-100 text-center p-3 selected" data-mode="chronologique">
                        <div class="mode-icon text-primary">📅</div>
                        <h5>Mode Chronologique</h5>
                        <p class="text-muted small mb-0">
                            Distribution par ordre de date de saisie (FIFO).<br>
                            Premier arrivé, premier servi.
                        </p>
                        <div class="mt-2">
                            <span class="badge bg-secondary">Tana=30✓ Toamasina=50✓</span>
                        </div>
                    </div>
                </div>
                <!-- Mode Croissant -->
                <div class="col-md-4">
                    <div class="mode-card card h-100 text-center p-3" data-mode="croissant">
                        <div class="mode-icon text-success">📊</div>
                        <h5>Mode Croissant</h5>
                        <p class="text-muted small mb-0">
                            Priorité aux plus petits besoins.<br>
                            Satisfait plus de villes complètement.
                        </p>
                        <div class="mt-2">
                            <span class="badge bg-success">3 villes complètes</span>
                        </div>
                    </div>
                </div>
                <!-- Mode Proportionnel -->
                <div class="col-md-4">
                    <div class="mode-card card h-100 text-center p-3" data-mode="proportionnel">
                        <div class="mode-icon text-warning">⚖️</div>
                        <h5>Mode Proportionnel</h5>
                        <p class="text-muted small mb-0">
                            Répartition selon le poids des besoins.<br>
                            Plus équitable pour tous.
                        </p>
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">4 villes partielles</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" id="selectedMode" value="chronologique">
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-secondary btn-lg me-2" id="btnPreview">
                    <i class="bi bi-eye me-2"></i>PRÉVISUALISER
                </button>
                <button type="button" class="btn btn-primary btn-lg" id="btnSimuler">
                    <i class="bi bi-play-fill me-2"></i>SIMULER
                </button>
                <button type="button" class="btn btn-success btn-lg ms-2" id="btnValider" disabled>
                    <i class="bi bi-check-circle me-2"></i>VALIDER
                </button>
            </div>
        </div>
    </div>

    <!-- Données disponibles -->
    <div class="row mb-4">
        <!-- Dons par produit -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-gift me-2"></i>Dons disponibles par produit</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($donsParProduit)): ?>
                        <table class="table table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-end">Quantité totale</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donsParProduit as $produit => $donsP): ?>
                                    <?php $totalProduit = array_sum(array_column($donsP, 'quantite')); ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($produit) ?></strong></td>
                                        <td class="text-end"><?= number_format($totalProduit) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Aucun don disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Besoins par produit -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Besoins non satisfaits par produit</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($besoinsParProduit)): ?>
                        <table class="table table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-end">Quantité totale</th>
                                    <th class="text-end">Nb villes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($besoinsParProduit as $produit => $besoinsP): ?>
                                    <?php 
                                    $totalProduit = array_sum(array_map(function($b) {
                                        return $b['quantite'] - ($b['quantite_satisfaite'] ?? 0);
                                    }, $besoinsP));
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($produit) ?></strong></td>
                                        <td class="text-end"><?= number_format($totalProduit) ?></td>
                                        <td class="text-end"><?= count($besoinsP) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Tous les besoins sont satisfaits</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone de prévisualisation -->
    <div id="previewSection" class="preview-section" style="display: none;">
        <h4><i class="bi bi-eye me-2"></i>Prévisualisation de la simulation</h4>
        <hr>
        
        <!-- Statistiques de la simulation -->
        <div class="row mb-4" id="previewStats">
            <!-- Rempli par JavaScript -->
        </div>
        
        <!-- Détails des attributions -->
        <div id="previewAttributions">
            <!-- Rempli par JavaScript -->
        </div>
        
        <!-- Détails du calcul -->
        <div class="mt-4">
            <h5><i class="bi bi-terminal me-2"></i>Détails du calcul</h5>
            <div id="detailsCalcul" class="details-calcul">
                <!-- Rempli par JavaScript -->
            </div>
        </div>
        
        <!-- Boutons d'action -->
        <div class="text-center mt-4">
            <button type="button" class="btn btn-success btn-lg me-3" id="btnValider">
                <i class="bi bi-check-circle me-2"></i>VALIDER ET ENREGISTRER
            </button>
            <button type="button" class="btn btn-secondary btn-lg" id="btnAnnuler">
                <i class="bi bi-x-circle me-2"></i>Annuler
            </button>
        </div>
    </div>
</div>

<!-- Modal de confirmation Reset -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmation Reset Global</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Cette action va :</p>
                <ul>
                    <li>Supprimer <strong>toutes les attributions</strong></li>
                    <li>Supprimer <strong>tous les achats</strong></li>
                    <li>Restaurer les quantités initiales des <strong>dons</strong></li>
                    <li>Restaurer les quantités initiales des <strong>besoins</strong></li>
                    <li>Remettre les dons argent à leur état initial</li>
                </ul>
                <p class="text-danger fw-bold">Êtes-vous sûr de vouloir continuer ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReset">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Confirmer Reset
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedMode = 'chronologique';
    let previewData = null;
    
    // Explications des modes avec exemples
    const modeExplications = {
        chronologique: {
            titre: 'Mode Chronologique (FIFO)',
            desc: 'Distribution par ordre de date de saisie. Les besoins enregistrés en premier sont satisfaits en premier.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Tana(30)→Toamasina(50)→Mahajanga(40)→Fianara(20)<br><strong>Résultat:</strong> Tana=30✓, Toamasina=50✓, Mahajanga=20/40⚠️, Fianara=0/20❌'
        },
        croissant: {
            titre: 'Mode Croissant (Petits besoins d\'abord)',
            desc: 'Priorité aux villes ayant les plus petits besoins. Permet de satisfaire complètement plus de villes.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Fianara(20)→Tana(30)→Mahajanga(40)→Toamasina(50)<br><strong>Résultat:</strong> Fianara=20✓, Tana=30✓, Mahajanga=40✓, Toamasina=10/50⚠️'
        },
        proportionnel: {
            titre: 'Mode Proportionnel (Équitable)',
            desc: 'Répartition selon le poids des besoins. Formule: part = (besoin_ville / total_besoins) × don. Arrondi vers le bas.',
            exemple: '<strong>Exemple RIZ (Don=100):</strong> Total=140 → Tana=21 (30/140×100), Toamasina=36, Mahajanga=29, Fianara=14<br><strong>Résultat:</strong> Distribution équitable à toutes les villes'
        }
    };
    
    // Fonction pour mettre à jour l'explication
    function updateExplication(mode) {
        const exp = modeExplications[mode];
        document.getElementById('modeExplication').innerHTML = `
            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>${exp.titre}</h6>
            <p class="mb-0">${exp.desc}</p>
            <hr>
            <p class="mb-0 small">${exp.exemple}</p>
        `;
    }
    
    // Fonction pour sélectionner un mode
    function selectMode(mode) {
        selectedMode = mode;
        document.getElementById('selectedMode').value = mode;
        document.getElementById('modeSelect').value = mode;
        
        // Mettre à jour les cartes visuellement
        document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
        const card = document.querySelector(`.mode-card[data-mode="${mode}"]`);
        if (card) card.classList.add('selected');
        
        // Mettre à jour l'explication
        updateExplication(mode);
        
        // Désactiver le bouton valider
        document.getElementById('btnValider').disabled = true;
    }
    
    // Événement sur le menu déroulant
    document.getElementById('modeSelect').addEventListener('change', function() {
        selectMode(this.value);
    });
    
    // Événement sur les cartes
    document.querySelectorAll('.mode-card').forEach(card => {
        card.addEventListener('click', function() {
            selectMode(this.dataset.mode);
        });
    });
    
    // Sélectionner le premier mode par défaut
    selectMode('chronologique');
    
    // Bouton Simuler
    document.getElementById('btnSimuler').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Simulation en cours...';
        
        try {
            const response = await fetch('/simulation/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `mode=${selectedMode}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                previewData = data;
                afficherPreview(data);
                document.getElementById('previewSection').style.display = 'block';
                document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur de communication: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill me-2"></i>SIMULER';
        }
    });
    
    // Afficher la prévisualisation
    function afficherPreview(data) {
        const statsHtml = `
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 class="mb-0">${data.statistiques_globales?.total_dons || 0}</h4>
                    <small>Total dons</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box success">
                    <h4 class="mb-0">${data.statistiques_globales?.total_distribue || 0}</h4>
                    <small>À distribuer</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box warning">
                    <h4 class="mb-0">${data.statistiques_globales?.villes_satisfaites || 0}</h4>
                    <small>Villes satisfaites</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 class="mb-0">${data.statistiques_globales?.taux_satisfaction || 0}%</h4>
                    <small>Taux satisfaction</small>
                </div>
            </div>
        `;
        document.getElementById('previewStats').innerHTML = statsHtml;
        
        // Afficher les attributions par produit
        let attribHtml = '';
        for (const [produit, result] of Object.entries(data.resultats || {})) {
            attribHtml += `
                <div class="produit-section">
                    <h5><i class="bi bi-box me-2"></i>${produit}</h5>
                    <table class="table table-sm table-bordered attribution-table">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th class="text-end">Besoin</th>
                                <th class="text-end">Attribué</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            for (const attr of result.attributions || []) {
                const statusBadge = attr.complet 
                    ? '<span class="badge bg-success">Complet</span>'
                    : '<span class="badge bg-warning">Partiel</span>';
                    
                attribHtml += `
                    <tr>
                        <td>${attr.ville_nom}</td>
                        <td class="text-end">${attr.quantite_besoin}</td>
                        <td class="text-end fw-bold">${attr.quantite_attribuee}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            }
            
            attribHtml += `
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong>${result.statistiques?.total_besoins || 0}</strong></td>
                                <td class="text-end"><strong>${result.statistiques?.total_distribue || 0}</strong></td>
                                <td class="text-center">
                                    <span class="badge bg-info">${result.statistiques?.taux_satisfaction || 0}%</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            // Détails calcul
            const detailsDiv = document.getElementById('detailsCalcul');
            detailsDiv.innerHTML = '';
            for (const [produit, result] of Object.entries(data.resultats || {})) {
                if (result.details_calcul) {
                    detailsDiv.innerHTML += `<div class="mb-3"><strong>=== ${produit} ===</strong></div>`;
                    result.details_calcul.forEach(line => {
                        detailsDiv.innerHTML += line + '<br>';
                    });
                    detailsDiv.innerHTML += '<hr>';
                }
            }
        }
        
        document.getElementById('previewAttributions').innerHTML = attribHtml || '<p class="text-muted">Aucune attribution possible</p>';
    }
    
    // Bouton Valider
    document.getElementById('btnValider').addEventListener('click', async function() {
        if (!confirm('Valider et enregistrer cette simulation ?')) return;
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';
        
        try {
            const response = await fetch('/simulation/valider-mode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `mode=${selectedMode}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>VALIDER ET ENREGISTRER';
        }
    });
    
    // Bouton Annuler
    document.getElementById('btnAnnuler').addEventListener('click', function() {
        document.getElementById('previewSection').style.display = 'none';
        previewData = null;
    });
    
    // Bouton Reset Global
    document.getElementById('btnResetGlobal').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('resetModal'));
        modal.show();
    });
    
    // Confirmer Reset
    document.getElementById('btnConfirmReset').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reset en cours...';
        
        try {
            const response = await fetch('/reset/global', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        } catch (error) {
            alert('Erreur: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-2"></i>Confirmer Reset';
            bootstrap.Modal.getInstance(document.getElementById('resetModal')).hide();
        }
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout/base.php'; ?>
