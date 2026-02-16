    </main>

    <!-- Footer -->
    <footer class="mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-arrow-left-right"></i> Takalo-Takalo</h5>
                    <p class="text-muted">
                        Plateforme d'échange d'objets entre particuliers.<br>
                        Échangez vos objets facilement et en toute sécurité.
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled">
                        <li><a href="/objets"><i class="bi bi-grid"></i> Parcourir les objets</a></li>
                        <li><a href="/register"><i class="bi bi-person-plus"></i> S'inscrire</a></li>
                        <li><a href="/login"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Catégories populaires</h5>
                    <ul class="list-unstyled">
                        <li><a href="/objets?category=1"><i class="bi bi-tag"></i> Vêtements</a></li>
                        <li><a href="/objets?category=2"><i class="bi bi-book"></i> Livres</a></li>
                        <li><a href="/objets?category=4"><i class="bi bi-phone"></i> Électronique</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-3 border-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> Takalo-Takalo - Révision Février 2026 – P18/P5DS
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <strong>Équipe de développement:</strong><br>
                        <span class="badge bg-primary">Membre 1 - ETU001234</span>
                        <span class="badge bg-success">Membre 2 - ETU001235</span>
                        <span class="badge bg-info">Membre 3 - ETU001236</span>
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
