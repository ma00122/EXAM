<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-geo-alt-fill text-primary"></i> Gestion des Villes
                    <span class="badge bg-primary"><?= count($villes) ?></span>
                </h1>
                <a href="/villes/create" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Ajouter une ville
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

            <!-- Barre de recherche -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="/villes" method="GET" class="row g-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($search ?? '') ?>" 
                                       placeholder="Rechercher par nom ou région...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                        </div>
                    </form>
                    <?php if (!empty($search)): ?>
                    <div class="mt-2">
                        <a href="/villes" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Effacer la recherche
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tableau des villes -->
            <?php if (empty($villes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                <?php if (!empty($search)): ?>
                    Aucune ville trouvée pour "<strong><?= htmlspecialchars($search) ?></strong>".
                <?php else: ?>
                    Aucune ville enregistrée. <a href="/villes/create">Ajouter une ville</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-table"></i> Liste des villes
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="80">#ID</th>
                                <th>Nom de la ville</th>
                                <th>Région</th>
                                <th width="200" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($villes as $ville): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $ville['id'] ?></span></td>
                                <td>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong><?= htmlspecialchars($ville['nom']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-map"></i> <?= htmlspecialchars($ville['region']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="/villes/edit/<?= $ville['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                    <a href="/villes/delete/<?= $ville['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette ville ?')">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistiques par région -->
            <?php if (!empty($regions)): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-bar-chart"></i> Statistiques par région
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($regions as $region): ?>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <span class="badge bg-secondary p-2">
                                <i class="bi bi-map"></i> <?= htmlspecialchars($region) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
