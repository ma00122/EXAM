    </main>

    <!-- Footer -->
    <footer class="mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-shield-check"></i> BNGRC</h5>
                    <p class="text-muted">
                        Bureau National de Gestion des Risques et Catastrophes.<br>
                        Système de gestion des dons et besoins.
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="/villes"><i class="bi bi-geo-alt"></i> Villes</a></li>
                        <li><a href="/besoins"><i class="bi bi-list-check"></i> Besoins</a></li>
                        <li><a href="/dons"><i class="bi bi-gift"></i> Dons</a></li>
                        <li><a href="/simulation"><i class="bi bi-play-circle"></i> Simulation</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Analyse & Récap</h5>
                    <ul class="list-unstyled">
                        <li><a href="/recap"><i class="bi bi-clipboard-data"></i> Récapitulatif</a></li>
                        <li><a href="/besoins/restants"><i class="bi bi-exclamation-triangle"></i> Besoins Restants</a></li>
                        <li><a href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="/configuration"><i class="bi bi-gear"></i> Configuration</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-3 border-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> BNGRC - Projet Final S3 – Février 2026
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <strong>Équipe de développement:</strong><br>
                        <span class="badge bg-primary">SEDRA - ETU004141</span>
                        <span class="badge bg-success">BOLTON - ETU004172</span>
                        <span class="badge bg-info">MAHERY - ETU004546</span>
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
