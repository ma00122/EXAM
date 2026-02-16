<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-plus-circle text-success"></i> Ajouter une Ville
                </h1>
                <a href="/villes" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <!-- Message d'erreur -->
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-geo-alt"></i> Informations de la ville
                </div>
                <div class="card-body">
                    <form action="/villes/store" method="POST">
                        <!-- Nom de la ville -->
                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                <i class="bi bi-building"></i> Nom de la ville <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nom" 
                                   name="nom" 
                                   value="<?= htmlspecialchars($old['nom'] ?? '') ?>"
                                   placeholder="Ex: Antananarivo, Toamasina..."
                                   required 
                                   minlength="2" 
                                   maxlength="100">
                            <div class="form-text">Le nom doit contenir entre 2 et 100 caractères.</div>
                        </div>

                        <!-- Région -->
                        <div class="mb-3">
                            <label for="region" class="form-label">
                                <i class="bi bi-map"></i> Région <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="region" 
                                   name="region" 
                                   value="<?= htmlspecialchars($old['region'] ?? '') ?>"
                                   placeholder="Ex: Analamanga, Atsinanana..."
                                   list="regions-list"
                                   required 
                                   minlength="2" 
                                   maxlength="100">
                            <datalist id="regions-list">
                                <?php if (!empty($regions)): ?>
                                    <?php foreach ($regions as $region): ?>
                                    <option value="<?= htmlspecialchars($region) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- Régions de Madagascar par défaut -->
                                <option value="Analamanga">
                                <option value="Vakinankaratra">
                                <option value="Itasy">
                                <option value="Bongolava">
                                <option value="Haute Matsiatra">
                                <option value="Amoron'i Mania">
                                <option value="Vatovavy-Fitovinany">
                                <option value="Ihorombe">
                                <option value="Atsimo-Atsinanana">
                                <option value="Atsinanana">
                                <option value="Analanjirofo">
                                <option value="Alaotra-Mangoro">
                                <option value="Boeny">
                                <option value="Sofia">
                                <option value="Betsiboka">
                                <option value="Melaky">
                                <option value="Atsimo-Andrefana">
                                <option value="Androy">
                                <option value="Anosy">
                                <option value="Menabe">
                                <option value="Diana">
                                <option value="Sava">
                            </datalist>
                            <div class="form-text">Sélectionnez une région existante ou entrez une nouvelle.</div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <a href="/villes" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Enregistrer la ville
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aide -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <i class="bi bi-info-circle"></i> Aide
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong>Note :</strong> Les villes sont utilisées pour associer les besoins 
                        dans le système BNGRC. Assurez-vous d'entrer les informations correctement.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
