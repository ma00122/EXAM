    </main>

    <!-- Footer -->
    <footer class="mt-5 py-4" style="background-color: #1a365d;">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-building"></i> BNGRC</h5>
                    <p class="text-light opacity-75">
                        Bureau National de Gestion des Risques et Catastrophes<br>
                        Système de gestion des dons et distribution
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Modules</h5>
                    <ul class="list-unstyled">
                        <li><a href="/villes"><i class="bi bi-geo-alt"></i> Gestion des Villes</a></li>
                        <li><a href="/besoins"><i class="bi bi-list-check"></i> Gestion des Besoins</a></li>
                        <li><a href="/dons"><i class="bi bi-gift"></i> Dons Nature</a></li>
                        <li><a href="/dons-argent"><i class="bi bi-cash-stack"></i> Dons Argent</a></li>
                        <li><a href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a></li>
                        <li><a href="/recap"><i class="bi bi-clipboard-data"></i> Récapitulatif</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Projet Final S3</h5>
                    <p class="text-light opacity-75 mb-2">
                        Extension V2 – Achat Don Argent + Simulation + Récap Ajax
                    </p>
                    <p class="mb-0">
                        <strong>Équipe de développement:</strong>
                    </p>
                </div>
            </div>
            <hr class="my-3 border-light opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-light opacity-75">
                        &copy; <?= date('Y') ?> BNGRC - Février 2026 – P18/P5DS
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-primary me-1"><i class="bi bi-person"></i> SEDRA - ETU004141</span>
                    <span class="badge bg-success me-1"><i class="bi bi-person"></i> BOLTON - ETU004172</span>
                    <span class="badge bg-info"><i class="bi bi-person"></i> MAHERY - ETU004546</span>
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
