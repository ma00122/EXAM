# Module MAHERY - BNGRC Extension V2

## Auteur
**ETU004546 - MAHERY**

## Description
Module d'infrastructure, CRUD et gestion des achats pour le projet BNGRC (Bureau National de Gestion des Risques et des Catastrophes).

---

## Tables créées

### 1. Table `configuration`
Gestion des paramètres système.

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| cle | VARCHAR(100) | Clé unique de configuration |
| valeur | VARCHAR(255) | Valeur de la configuration |
| description | TEXT | Description optionnelle |

**Configurations par défaut:**
- `frais_achat_pourcentage`: 5% (frais sur achats)
- `montant_minimum_achat`: 1000 Ar

### 2. Table `achat`
Enregistrement des achats effectués avec les dons argent.

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| ville_id | INT | FK vers ville |
| besoin_id | INT | FK vers besoin |
| don_argent_id | INT | FK vers don_argent (optionnel) |
| montant_produit | DECIMAL | Montant HT |
| frais | DECIMAL | Frais appliqués |
| montant_total | DECIMAL | montant_produit + frais |
| quantite_achetee | INT | Quantité achetée |
| date_achat | DATE | Date de l'achat |
| statut | ENUM | en_attente, valide, annule |

### 3. Table `don_argent`
Gestion des dons en argent.

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| montant | DECIMAL | Montant total du don |
| donateur | VARCHAR | Nom du donateur |
| date_saisie | DATE | Date du don |
| montant_utilise | DECIMAL | Montant déjà utilisé |
| statut | ENUM | disponible, partiellement_utilise, epuise |

---

## Routes créées

### Configuration (`/configuration`)
- `GET /configuration` - Liste des configurations
- `POST /configuration/frais` - Modifier frais d'achat
- `POST /configuration/update` - Modifier une configuration
- `POST /configuration/create` - Créer une configuration
- `GET /configuration/delete/@cle` - Supprimer une configuration
- `GET /configuration/calcul-frais?montant=X` - API calcul preview

### Achats (`/achats`)
- `GET /achats` - Liste des achats (filtrable par ville/statut)
- `GET /achats/create` - Formulaire création
- `POST /achats/store` - Enregistrer achat
- `GET /achats/show/@id` - Détail achat
- `GET /achats/edit/@id` - Formulaire édition
- `POST /achats/update/@id` - Mettre à jour achat
- `GET /achats/validate/@id` - Valider un achat
- `GET /achats/cancel/@id` - Annuler un achat
- `GET /achats/delete/@id` - Supprimer achat
- `GET /achats/stats` - Statistiques par ville

### Récapitulatif (`/recap`)
- `GET /recap` - Page récapitulative globale
- **`GET /recap/data`** - API Ajax JSON (utilisée par Sedra)
- `GET /recap/besoins-restants` - API besoins non satisfaits
- `GET /recap/ville/@id` - Récap par ville

### Dons Argent (`/dons-argent`)
- `GET /dons-argent` - Liste des dons argent
- `GET /dons-argent/create` - Formulaire création
- `POST /dons-argent/store` - Enregistrer don
- `GET /dons-argent/edit/@id` - Formulaire édition
- `POST /dons-argent/update/@id` - Mettre à jour
- `GET /dons-argent/delete/@id` - Supprimer

### Attributions (`/attributions`)
- `GET /attributions` - Liste des attributions
- `GET /attributions/create` - Formulaire création
- `POST /attributions/store` - Enregistrer attribution
- `GET /attributions/edit/@id` - Formulaire édition
- `POST /attributions/update/@id` - Mettre à jour
- `GET /attributions/delete/@id` - Supprimer

---

## API Ajax `/recap/data`

Format de réponse JSON pour Sedra:

```json
{
  "success": true,
  "timestamp": "2026-02-16 12:00:00",
  "total": 1250000,
  "satisfait": 500000,
  "restant": 750000,
  "details": {
    "quantites": {
      "total_demandee": 500,
      "total_recue": 200,
      "restante": 300
    },
    "montants": {
      "total_besoins": 1250000,
      "satisfait_nature": 350000,
      "satisfait_achats": 150000,
      "total_satisfait": 500000,
      "restant": 750000
    },
    "dons": {
      "total_nature": 285,
      "total_argent": 575000,
      "argent_disponible": 425000
    },
    "taux_satisfaction": 40,
    "frais_pourcentage": 5
  }
}
```

---

## Fichiers créés

### Models
- `app/models/Configuration.php` - Gestion configuration
- `app/models/Achat.php` - Gestion achats
- `app/models/DonArgent.php` - Gestion dons argent

### Controllers
- `app/controllers/ConfigurationController.php`
- `app/controllers/AchatController.php`
- `app/controllers/RecapController.php`
- `app/controllers/DonArgentController.php`
- `app/controllers/AttributionController.php`

### Views
- `app/views/configuration/index.php`
- `app/views/achats/index.php`
- `app/views/achats/create.php`
- `app/views/achats/edit.php`
- `app/views/achats/stats.php`
- `app/views/recap/index.php` (avec bouton Actualiser Ajax)
- `app/views/dons-argent/index.php`
- `app/views/dons-argent/create.php`
- `app/views/dons-argent/edit.php`
- `app/views/attributions/index.php`
- `app/views/attributions/create.php`
- `app/views/attributions/edit.php`

### SQL
- `database/configuration.sql` - Script création tables
- `database/install_bngrc.sql` - Script installation complet

---

## Données de test

### Dons Nature
- Riz: 60 unités
- Tole: 50 unités
- Huile: 30 unités
- Clous: 40 unités

### Dons Argent
- 150 000 Ar - Association Solidarité
- 75 000 Ar - Entreprise ABC
- 50 000 Ar - Anonyme
- 200 000 Ar - ONG Entraide
- 100 000 Ar - Particulier

**Total dons argent: 575 000 Ar**

---

## Installation

1. Exécuter le script SQL:
```bash
mysql -u root -p takalo_db < database/install_bngrc.sql
```

2. Vérifier la configuration dans `app/config/config.php`

3. Accéder à l'application via le navigateur

---

## Tests à effectuer

- [ ] CRUD Villes fonctionnel
- [ ] CRUD Dons Nature fonctionnel
- [ ] CRUD Dons Argent fonctionnel
- [ ] CRUD Attributions fonctionnel
- [ ] CRUD Achats fonctionnel
- [ ] Interface modification frais achat
- [ ] Liste achats filtrable par ville
- [ ] Route GET /recap/data retourne JSON correct
- [ ] Page recap affiche montants corrects
- [ ] Bouton Actualiser Ajax fonctionne
- [ ] Frais appliqués correctement sur achats

---

## Intégration avec autres modules

### Pour SEDRA (Simulation)
- Utiliser `GET /recap/data` pour récupérer les données Ajax
- Model `DonArgent` pour gérer les dons argent dans la simulation
- Model `Achat` pour créer les achats lors de la validation
- Model `Configuration` pour récupérer le taux de frais

### Pour BOLTON (Besoins)
- Les besoins sont liés aux achats via `besoin_id`
- Le `prix_unitaire` est bloqué (pas de modification après création)
- Utiliser les statistiques de récap pour les calculs globaux

---

## Auteur
**MAHERY (ETU004546)**
Février 2026
