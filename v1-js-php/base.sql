CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


┌─────────────────┐
│  FORMULAIRE     │  Utilisateur tape "A" dans le champ Nom
│  register.php   │
└────────┬────────┘
         │ 1. JavaScript détecte l'événement (blur/input)
         │
         ▼
┌─────────────────────────┐
│  validation-ajax.js     │  Collecte les données du formulaire
│  (JavaScript)           │  Crée un objet FormData
└────────┬────────────────┘
         │ 2. Envoi AJAX (fetch)
         │
         ▼
┌─────────────────────────┐
│  validate.php           │  📍 POINT D'ENTRÉE AJAX
│  (Endpoint API JSON)    │
└────────┬────────────────┘
         │ 3. Récupère $_POST
         │
         ▼
┌──────────────────────────────┐
│  lib_validation.php          │  📍 TRAITEMENT VALIDATION
│  validate_registration_input()│  
│  - Nettoie les données       │
│  - Valide chaque champ       │
│  - Vérifie email en base     │
└────────┬─────────────────────┘
         │ 4. Retourne tableau
         │    ['ok'=>false, 'errors'=>[...]]
         ▼
┌─────────────────────────┐
│  validate.php           │  Encode en JSON
│  echo json_encode(...)  │
└────────┬────────────────┘
         │ 5. Réponse JSON
         │
         ▼
┌─────────────────────────┐
│  validation-ajax.js     │  Affiche les erreurs
│  (JavaScript)           │  en rouge sous les champs
└────────┬────────────────┘
         │ 6. Mise à jour visuelle
         ▼
┌─────────────────┐
│  FORMULAIRE     │  Champ "Nom" devient rouge
│  register.php   │  Message: "Le nom doit contenir au moins 2 caractères."
└─────────────────┘















┌─────────────────┐
│  FORMULAIRE     │  Utilisateur clique "S'inscrire"
│  register.php   │
└────────┬────────┘
         │ 1. Soumission POST (method="post" action="register.php")
         │
         ▼
┌─────────────────────────────────┐
│  register.php                   │  📍 POINT D'ENTRÉE POST
│  if ($_SERVER['REQUEST_METHOD'] │
│      === 'POST')                │
└────────┬────────────────────────┘
         │ 2. Ligne 16-23 : Collecte $_POST
         │    $input = ['nom'=>..., 'prenom'=>...]
         │
         ▼
┌──────────────────────────────┐
│  lib_validation.php          │  📍 TRAITEMENT VALIDATION
│  validate_registration_input()│  (MÊME FONCTION que l'AJAX!)
│  - Nettoie                   │
│  - Valide                    │
│  - Vérifie email unique      │
└────────┬─────────────────────┘
         │ 3. Retourne résultat
         │    $res = ['ok'=>true/false, 'errors'=>[...]]
         │
         ▼
┌─────────────────────────────┐
│  register.php               │
│  Ligne 27-33                │
│  $errors = $res['errors']   │
│  $values = $res['values']   │
└────────┬────────────────────┘
         │ 4. Vérification : if ($res['ok'])
         │
         ├─── ❌ Erreurs → Réaffiche formulaire avec erreurs
         │
         └─── ✅ OK → Continue ci-dessous
                │
                ▼
         ┌──────────────────────────────┐
         │  register.php                │  📍 INSERTION EN BASE
         │  Ligne 36-48                 │
         │                              │
         │  1. Hash mot de passe        │
         │  2. INSERT INTO users        │
         │  3. $success = true          │
         └──────────────────────────────┘
                │
                ▼
         ┌──────────────────────────┐
         │  PAGE RECHARGÉE          │  Message vert
         │  "Inscription réussie ✅" │
         └──────────────────────────┘/--