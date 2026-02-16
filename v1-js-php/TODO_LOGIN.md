jmnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnjjjjjjjjjjjjjjjj# 📋 TO-DO LIST - Système de Connexion (Login)

## 🎯 Objectif
Créer un système de connexion sécurisé avec gestion de session

---

## ✅ ÉTAPE 1 : Base de données
- [ ] Vérifier que la table `users` existe avec les colonnes :
  - `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
  - `email` (VARCHAR, UNIQUE)
  - `password_hash` (VARCHAR)
  - `nom`, `prenom`, `telephone`

---

## ✅ ÉTAPE 2 : Créer lib_auth.php (Bibliothèque d'authentification)

### Fichier : `inc/lib_auth.php`

- [ ] **Fonction `login_user($email, $password, $pdo)`**
  - Rechercher l'utilisateur par email
  - Vérifier le mot de passe avec `password_verify()`
  - Retourner `['ok' => true/false, 'error' => '...', 'user' => [...]]`

- [ ] **Fonction `start_session_secure()`**
  - Configurer session sécurisée
  - `session_start()` avec options

- [ ] **Fonction `store_user_session($user)`**
  - Stocker ID utilisateur en session
  - Stocker nom, prénom, email

- [ ] **Fonction `is_logged_in()`**
  - Vérifier si `$_SESSION['user_id']` existe
  - Retourner `true/false`

- [ ] **Fonction `get_current_user()`**
  - Retourner les infos utilisateur depuis `$_SESSION`

- [ ] **Fonction `logout_user()`**
  - Détruire la session
  - Supprimer les cookies

---

## ✅ ÉTAPE 3 : Créer login.php (Page de connexion)

### Fichier : `login.php`

- [ ] **Partie PHP (haut du fichier)**
  - Inclure `inc/db.php` et `inc/lib_auth.php`
  - Initialiser `$error = ''` et `$email_value = ''`
  - Si déjà connecté → Rediriger vers `dashboard.php`
  
- [ ] **Traitement POST**
  - Récupérer `$email` et `$password` de `$_POST`
  - Valider que les champs ne sont pas vides
  - Appeler `login_user($email, $password, $pdo)`
  - Si succès → Démarrer session + Redirection
  - Si échec → Afficher message d'erreur

- [ ] **Partie HTML**
  - Formulaire Bootstrap avec 2 champs :
    - Email
    - Mot de passe
  - Bouton "Se connecter"
  - Lien vers `register.php` ("Pas encore inscrit ?")
  - Afficher erreur si présente

---

## ✅ ÉTAPE 4 : Créer dashboard.php (Page protégée)

### Fichier : `dashboard.php`

- [ ] **Protection de la page**
  - Inclure `inc/lib_auth.php`
  - Vérifier `is_logged_in()`
  - Si non connecté → Rediriger vers `login.php`

- [ ] **Affichage**
  - Message de bienvenue avec nom utilisateur
  - Afficher infos : nom, prénom, email, téléphone
  - Bouton "Se déconnecter" → POST vers `logout.php`

---

## ✅ ÉTAPE 5 : Créer logout.php (Déconnexion)

### Fichier : `logout.php`

- [ ] Inclure `inc/lib_auth.php`
- [ ] Appeler `logout_user()`
- [ ] Rediriger vers `login.php` avec message

---

## ✅ ÉTAPE 6 : Modifier register.php

- [ ] Après inscription réussie → Connecter automatiquement
  - Appeler `store_user_session()` avec les données
  - Rediriger vers `dashboard.php` au lieu de réafficher le formulaire
  
- [ ] Ajouter lien "Déjà inscrit ? Se connecter" vers `login.php`

---

## ✅ ÉTAPE 7 : Créer index.php (Page d'accueil)

### Fichier : `index.php`

- [ ] Vérifier si connecté avec `is_logged_in()`
- [ ] Si connecté → Rediriger vers `dashboard.php`
- [ ] Si non connecté → Afficher :
  - Message de bienvenue
  - Bouton "Se connecter" → `login.php`
  - Bouton "S'inscrire" → `register.php`

---

## ✅ ÉTAPE 8 : Sécurité et améliorations

- [ ] **Protection CSRF**
  - Générer token CSRF dans formulaire login
  - Vérifier token à la soumission

- [ ] **Limitation tentatives**
  - Ajouter compteur échecs de connexion
  - Bloquer après 5 tentatives (5 min)

- [ ] **Remember Me (optionnel)**
  - Checkbox "Se souvenir de moi"
  - Cookie sécurisé avec token

- [ ] **Configuration session**
  - Cookie httponly, secure, samesite
  - Régénérer ID session après login

---

## ✅ ÉTAPE 9 : Tests

- [ ] Test connexion avec email/password corrects
- [ ] Test connexion avec mauvais password
- [ ] Test connexion avec email inexistant
- [ ] Test accès dashboard sans connexion
- [ ] Test déconnexion
- [ ] Test "retour arrière" après déconnexion

---

## 📁 Structure finale des fichiers

```
├── index.php              (Page d'accueil)
├── login.php              (Connexion)
├── register.php           (Inscription - existant)
├── dashboard.php          (Espace membre protégé)
├── logout.php             (Déconnexion)
├── inc/
│   ├── db.php             (Connexion BDD - existant)
│   ├── lib_validation.php (Validation - existant)
│   └── lib_auth.php       (Authentification - NOUVEAU)
└── assets/
    ├── css/
    └── js/
```

---

## 🔐 Checklist Sécurité

- [ ] Utiliser `password_verify()` pour vérifier mots de passe
- [ ] Sessions sécurisées (httponly, secure)
- [ ] Protection contre force brute (limitation tentatives)
- [ ] Redirection après POST (pattern Post-Redirect-Get)
- [ ] Validation côté serveur (toujours)
- [ ] Messages d'erreur génériques ("Email ou mot de passe incorrect")

---

## 🎨 Bonus (Optionnel)

- [ ] Page "Mot de passe oublié"
- [ ] Email de confirmation à l'inscription
- [ ] Vérification email (lien activation)
- [ ] Profil utilisateur éditable
- [ ] Changement de mot de passe
- [ ] Historique des connexions

---

## 🚀 Ordre d'implémentation recommandé

1. **ÉTAPE 2** → Créer bibliothèque `lib_auth.php`
2. **ÉTAPE 3** → Créer page `login.php`
3. **ÉTAPE 4** → Créer `dashboard.php`
4. **ÉTAPE 5** → Créer `logout.php`
5. **ÉTAPE 7** → Créer `index.php`
6. **ÉTAPE 6** → Modifier `register.php`
7. **ÉTAPE 8** → Ajouter sécurité
8. **ÉTAPE 9** → Tester

---

📝 **Note** : Commencer par les fonctions de base, puis ajouter les améliorations de sécurité progressivement.
