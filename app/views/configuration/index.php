<?php $this->render('layout/header', ['pageTitle' => $pageTitle]); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-gear"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <a href="/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour Dashboard
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Configuration Frais d'Achat -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-percent"></i> Frais d'Achat</h5>
                </div>
                <div class="card-body">
                    <form action="/configuration/frais" method="POST">
                        <div class="mb-3">
                            <label for="frais_pourcentage" class="form-label">Pourcentage de frais sur achats</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control form-control-lg" 
                                       id="frais_pourcentage" 
                                       name="frais_pourcentage" 
                                       value="<?= htmlspecialchars($fraisPourcentage) ?>"
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Ce pourcentage sera appliqué sur tous les achats effectués avec les dons argent.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                    </form>

                    <!-- Preview calcul frais -->
                    <hr>
                    <h6>Calculateur de frais</h6>
                    <div class="input-group mb-2">
                        <span class="input-group-text">Montant</span>
                        <input type="number" class="form-control" id="calcul_montant" placeholder="Ex: 100000">
                        <span class="input-group-text">Ar</span>
                        <button type="button" class="btn btn-outline-primary" onclick="calculerFrais()">Calculer</button>
                    </div>
                    <div id="resultat_calcul" class="alert alert-info d-none"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Ajouter Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="/configuration/create" method="POST">
                        <div class="mb-3">
                            <label for="cle" class="form-label">Clé</label>
                            <input type="text" class="form-control" id="cle" name="cle" required placeholder="ex: taux_conversion">
                        </div>
                        <div class="mb-3">
                            <label for="valeur" class="form-label">Valeur</label>
                            <input type="text" class="form-control" id="valeur" name="valeur" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optionnelle)</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des configurations -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Toutes les Configurations</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Clé</th>
                        <th>Valeur</th>
                        <th>Description</th>
                        <th>Dernière modification</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($configurations)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune configuration trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($configurations as $config): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($config['cle']) ?></code></td>
                                <td>
                                    <form action="/configuration/update" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="cle" value="<?= htmlspecialchars($config['cle']) ?>">
                                        <input type="text" name="valeur" class="form-control form-control-sm" 
                                               value="<?= htmlspecialchars($config['valeur']) ?>" style="width: 150px;">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><?= htmlspecialchars($config['description'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($config['updated_at'] ?? '-') ?></td>
                                <td>
                                    <?php if (!in_array($config['cle'], ['frais_achat_pourcentage', 'montant_minimum_achat'])): ?>
                                        <a href="/configuration/delete/<?= urlencode($config['cle']) ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette configuration ?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Protégée</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function calculerFrais() {
    const montant = document.getElementById('calcul_montant').value;
    const resultat = document.getElementById('resultat_calcul');
    
    if (!montant || montant <= 0) {
        resultat.classList.add('d-none');
        return;
    }
    
    fetch('/configuration/calcul-frais?montant=' + montant)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultat.classList.remove('d-none');
                resultat.innerHTML = `
                    <strong>Montant HT:</strong> ${data.data.montant_ht.toLocaleString()} Ar<br>
                    <strong>Frais (${data.data.pourcentage_frais}%):</strong> ${data.data.frais.toLocaleString()} Ar<br>
                    <strong>Total TTC:</strong> <span class="text-success">${data.data.montant_total.toLocaleString()} Ar</span>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}
</script>

<?php $this->render('layout/footer'); ?>
