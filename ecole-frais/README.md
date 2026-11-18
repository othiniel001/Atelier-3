# Gestion des frais scolaires (`ecole-frais`)

Application Laravel 13 de gestion des frais d'une école : inscriptions, frais par classe et par tranches,
encaissement à la caisse, validation comptable, reçus PDF avec QR code de vérification, suivi des arriérés,
rappels SMS aux parents, espace parent et tableau de bord de la direction.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # ou configurer MySQL dans .env
php artisan migrate --seed
php artisan serve
```

Comptes de démonstration (mot de passe `password`) :

| Rôle       | E-mail                 | Accueil                         |
|------------|------------------------|---------------------------------|
| Admin      | admin@ecole.test       | Élèves / administration         |
| Caissier   | caisse@ecole.test      | Encaissement, reçus             |
| Comptable  | compta@ecole.test      | Validation des paiements, arriérés |
| Direction  | direction@ecole.test   | Tableau de bord                 |
| Parent     | parent@ecole.test      | Espace parent                   |

Rappels SMS quotidiens : `php artisan schedule:work` (ou un cron `* * * * * php artisan schedule:run`).
Lancement manuel : `php artisan frais:envoyer-rappels --simulation`.

Tests : `php artisan test`.

## Fonctionnement (étapes 1 → 11)

| # | Étape | Où |
|---|-------|----|
| 1 | Rôles (admin, caissier, comptable, parent, direction), connexion, middleware `role:` | `Role`, `User`, `Auth/ConnexionController`, `VerifierRole` |
| 2 | Journal d'audit automatique des créations / modifications / suppressions + connexions, validations, rejets | `Journal`, `JournalObserver`, `Admin/JournalController` |
| 3 | Années scolaires (une seule active), sections, classes | `Admin/*` |
| 4 | Élèves et parents (avec compte « espace parent » optionnel) | `Admin/EleveController`, `Admin/ParentEleveController` |
| 5 | Types de frais, montant par classe/année, échéances (tranches) dont la somme = montant | `Admin/FraisController`, `FraisRequest` |
| 6 | La caisse enregistre le paiement (statut *en attente*), la comptabilité **valide** ou **rejette** | `PaiementService`, `Caisse/PaiementController`, `Comptabilite/ValidationController` |
| 7 | Reçu émis à l'encaissement : numéro `REC-AAAA-000001`, PDF (dompdf), QR code vers une page publique de vérification ; reçu annulé si rejet | `RecuService`, `VerificationRecuController` |
| 8 | Soldes et arriérés (+ export CSV) | `SoldeService`, `Comptabilite/ArriereController` |
| 9 | SMS : confirmation de validation, information de rejet, rappel N jours avant échéance (un seul par élève et par tranche) | `SmsService`, `EnvoyerRappels` |
| 10 | Espace parent : soldes, tranches, historique, téléchargement des reçus (uniquement ses enfants) | `Parent/EspaceParentController` |
| 11 | Tableau de bord : attendu, encaissé, taux de recouvrement, arriérés, par classe, par mode, par mois | `StatistiqueService`, `Direction/TableauDeBordController` |

### Règles de calcul (`SoldeService`)

- **Payé** = somme des paiements *non rejetés* (en attente + validés) — un paiement en attente bloque donc
  déjà le montant, ce qui évite d'encaisser deux fois la même somme.
- **Reste** = montant du frais − payé. Un encaissement ne peut pas dépasser le reste.
- **Exigible** à une date = somme des tranches dont la date limite est dépassée
  (frais sans tranche : tout le montant dès la rentrée).
- **Arriéré** = exigible − payé (si positif).

Les montants sont stockés en entiers (unité de la devise, `ECOLE_DEVISE`, FCFA par défaut).

### Passerelle SMS

`SMS_DRIVER=log` écrit les SMS dans `storage/logs/laravel.log`. Avec `SMS_DRIVER=http`, l'application envoie
`POST SMS_URL` en JSON `{to, from, message}` avec l'en-tête `Authorization: Bearer SMS_TOKEN` ; adaptez
`SmsService::transmettre()` au format de votre fournisseur. Chaque envoi est historisé dans `notifications_sms`.
