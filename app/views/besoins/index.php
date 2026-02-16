<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar avec filtres -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
                </div>
                <div class="card-body">
                    <form action="/besoins" method="GET">
                        <!-- Filtre par ville -->
                        <div class="mb-3">
                            <label for="ville_id" class="form-label">Ville</label>
                            <select class="form-select" id="ville_id" name="ville_id">
                                <option value="">Toutes les villes</option>
                                <?php foreach ($villes as $ville): ?>
                                <option value="<?= $ville->id ?>" 
                                        <?= ($selectedVille ?? null) == $ville->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ville->nom) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtre par type -->
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type</label>
                            <select class="form-select" id="type_id" name="type_id">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $type): ?>
                                <option value="<?= $type->id ?>" 
                                        <?= ($selectedType ?? null) == $type->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type->nom_type) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrer
                        </button>

                        <?php if ($selectedVille || $selectedType): ?>
                        <a href="/besoins" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-x-circle"></i> Effacer les filtres
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des besoins -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-list-check"></i> Liste des Besoins
                    <span class="badge bg-primary"><?= count($besoins) ?></span>
                </h2>
                <a href="/besoins/create" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Ajouter un besoin
                </a>
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

            <?php if (empty($besoins)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Aucun besoin trouvé.
                <a href="/besoins/create" class="alert-link">Ajouter un besoin</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ville</th>
                            <th>Type</th>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Valeur Totale</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($besoins as $besoin): ?>
                        <?php 
                            // Calcul valeur totale
                            $quantite = is_array($besoin) ? $besoin['quantite'] : $besoin->quantite;
                            $prix_unitaire = is_array($besoin) ? $besoin['prix_unitaire'] : $besoin->prix_unitaire;
                            $valeur_totale = $quantite * $prix_unitaire;
                            $id = is_array($besoin) ? $besoin['id'] : $besoin->id;
                            $ville_nom = is_array($besoin) ? ($besoin['ville_nom'] ?? 'N/A') : ($besoin->ville_nom ?? 'N/A');
                            $type_nom = is_array($besoin) ? ($besoin['type_nom'] ?? 'N/A') : ($besoin->type_nom ?? 'N/A');
                            $produit = is_array($besoin) ? $besoin['produit'] : $besoin->produit;
                        ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td>
                                <span class="badge bg-info"><?= htmlspecialchars($ville_nom) ?></span>
                            </td>
                            <td>
                                <?php 
                                $badge_class = 'bg-secondary';
                                if ($type_nom === 'nature') $badge_class = 'bg-success';
                                elseif ($type_nom === 'materiaux') $badge_class = 'bg-warning text-dark';
                                elseif ($type_nom === 'argent') $badge_class = 'bg-primary';
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($type_nom) ?></span>
                            </td>
                            <td><?= htmlspecialchars($produit) ?></td>
                            <td><strong><?= number_format($quantite, 0, ',', ' ') ?></strong></td>
                            <td><?= number_format($prix_unitaire, 2, ',', ' ') ?> Ar</td>
                            <td class="text-success fw-bold">
                                <?= number_format($valeur_totale, 2, ',', ' ') ?> Ar
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/besoins/edit/<?= $id ?>" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/besoins/delete/<?= $id ?>" 
                                       class="btn btn-outline-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Général :</td>
                            <td class="text-success fw-bold">
                                <?php 
                                $total_general = 0;
                                foreach ($besoins as $besoin) {
                                    $q = is_array($besoin) ? $besoin['quantite'] : $besoin->quantite;
                                    $p = is_array($besoin) ? $besoin['prix_unitaire'] : $besoin->prix_unitaire;
                                    $total_general += $q * $p;
                                }
                                echo number_format($total_general, 2, ',', ' ') . ' Ar';
                                ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
