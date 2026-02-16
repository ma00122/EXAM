<?php include __DIR__ . '/../../layout/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-tags"></i> Gestion des catégories</h1>
    <a href="/admin/categories/nouveau" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle catégorie
    </a>
</div>

<?php if (isset($_SESSION['flash'])): ?>
<div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
    <?= $_SESSION['flash']['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
        <div class="text-center py-5">
            <i class="bi bi-folder-x fs-1 text-muted"></i>
            <p class="text-muted mt-3">Aucune catégorie créée</p>
            <a href="/admin/categories/nouveau" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Créer la première catégorie
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Nb objets</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat->id ?></td>
                        <td><strong><?= call_user_func($e, $cat->nom) ?></strong></td>
                        <td><?= call_user_func($e, $cat->description ?? '-') ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= $cat->countObjets() ?> objets</span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($cat->created_at)) ?></td>
                        <td>
                            <a href="/admin/categories/<?= $cat->id ?>/modifier" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="/admin/categories/<?= $cat->id ?>/supprimer" 
                                  class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
