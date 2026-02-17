    </main>

    <!-- Footer -->
    <footer class="mt-5 py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3"><i class="bi bi-shield-check"></i> BNGRC</h5>
                    <p class="text-light opacity-75 mb-3">
                        Bureau National de Gestion des Risques et Catastrophes.<br>
                        Plateforme de gestion des dons, besoins et achats.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">Sécurité</span>
                        <span class="badge bg-success">Transparence</span>
                        <span class="badge bg-info text-dark">Traçabilité</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-uppercase text-light opacity-75 mb-3">Accès rapide</h6>
                    <ul class="list-unstyled d-grid gap-2">
                        <li><a href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                        <li><a href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                        <li><a href="/dons"><i class="bi bi-gift"></i> Dons Nature</a></li>
                        <li><a href="/dons-argent"><i class="bi bi-cash-stack"></i> Dons Argent</a></li>
                        <li><a href="/achats"><i class="bi bi-cart-check"></i> Achats</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-uppercase text-light opacity-75 mb-3">Analyse & Synthèse</h6>
                    <ul class="list-unstyled d-grid gap-2">
                        <li><a href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a></li>
                        <li><a href="/besoins/restants"><i class="bi bi-exclamation-triangle"></i> Besoins Restants</a></li>
                        <li><a href="/recap"><i class="bi bi-clipboard-data"></i> Récapitulatif</a></li>
                        <li><a href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="/configuration"><i class="bi bi-gear"></i> Configuration</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-light opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-light opacity-75">
                        &copy; <?= date('Y') ?> BNGRC - Projet Final S3 – Février 2026
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-light opacity-75">
                        <strong>Équipe:</strong>
                        <span class="badge bg-primary">SEDRA - ETU004141</span>
                        <span class="badge bg-success">BOLTON - ETU004172</span>
                        <span class="badge bg-info text-dark">MAHERY - ETU004546</span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
