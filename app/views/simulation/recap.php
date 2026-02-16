<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-bar-chart-fill text-info"></i> Récapitulatif des Besoins
                </h1>
                <div>
                    <button type="button" class="btn btn-primary" id="btn-actualiser">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser
                    </button>
                    <a href="/simulation" class="btn btn-secondary ms-2">
                        <i class="bi bi-arrow-left"></i> Simulation
                    </a>
                </div>
            </div>

            <!-- Cartes statistiques (mises à jour via Ajax) -->
            <div class="row mb-4" id="recap-cards">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 id="total-besoins">-</h3>
                            <p class="mb-0">Quantité totale demandée</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 id="total-satisfait">-</h3>
                            <p class="mb-0">Quantité reçue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 id="total-restant">-</h3>
                            <p class="mb-0">Quantité restante</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cartes montants -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h4 id="montant-total" class="text-primary">-</h4>
                            <p class="mb-0">Montant total besoins</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h4 id="montant-satisfait" class="text-success">-</h4>
                            <p class="mb-0">Montant satisfait</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h4 id="montant-restant" class="text-danger">-</h4>
                            <p class="mb-0">Montant restant</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicateur de chargement -->
            <div class="text-center d-none" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <!-- Message d'erreur -->
            <div class="alert alert-danger d-none" id="error-message"></div>

            <!-- Dernière mise à jour -->
            <p class="text-muted text-center">
                <small>Dernière mise à jour : <span id="last-update">-</span></small>
            </p>

        </div>
    </div>
</div>

<script>
/**
 * Script Ajax pour actualiser le récap sans recharger la page
 * Projet BNGRC - Module Sedra
 */
document.addEventListener('DOMContentLoaded', function() {
    const btnActualiser = document.getElementById('btn-actualiser');
    const loadingSpinner = document.getElementById('loading-spinner');
    const errorMessage = document.getElementById('error-message');

    // Charger les données au démarrage
    actualiserRecap();

    // Bouton Actualiser
    btnActualiser.addEventListener('click', function() {
        actualiserRecap();
    });

    /**
     * Fetch Ajax pour recharger les montants sans recharger la page
     */
    function actualiserRecap() {
        // Afficher spinner
        loadingSpinner.classList.remove('d-none');
        errorMessage.classList.add('d-none');
        btnActualiser.disabled = true;

        fetch('/recap/data')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Mettre à jour les quantités
                    document.getElementById('total-besoins').textContent = formatNumber(data.total);
                    document.getElementById('total-satisfait').textContent = formatNumber(data.satisfait);
                    document.getElementById('total-restant').textContent = formatNumber(data.restant);

                    // Mettre à jour les montants
                    document.getElementById('montant-total').textContent = formatMoney(data.montant_total);
                    document.getElementById('montant-satisfait').textContent = formatMoney(data.montant_satisfait);
                    document.getElementById('montant-restant').textContent = formatMoney(data.montant_restant);

                    // Mettre à jour l'heure
                    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('fr-FR');
                } else {
                    showError('Erreur lors du chargement des données');
                }
            })
            .catch(error => {
                showError('Erreur de connexion : ' + error.message);
            })
            .finally(() => {
                loadingSpinner.classList.add('d-none');
                btnActualiser.disabled = false;
            });
    }

    /**
     * Formater un nombre
     */
    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }

    /**
     * Formater un montant en Ariary
     */
    function formatMoney(amount) {
        return new Intl.NumberFormat('fr-FR').format(amount) + ' Ar';
    }

    /**
     * Afficher un message d'erreur
     */
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
