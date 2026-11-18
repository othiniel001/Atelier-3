# Mettre la plateforme en ligne (Railway)

Le dépôt contient tout le nécessaire à la racine : `Dockerfile`, `railway.json`, `.dockerignore`.
Faisable entièrement depuis un téléphone.

## 1. Créer le service
1. Aller sur **https://railway.app** → *Login* → **Continue with GitHub**.
2. **New Project** → **Deploy from GitHub repo** → autoriser l'accès au dépôt **Atelier-3**.
3. Dans le service créé : **Settings** → **Source** → *Branch* : choisir la branche qui contient `ecole-frais`
   (`ccr-9d03cefb-ygx5ls`, ou `main` une fois fusionnée).

## 2. Variables (onglet **Variables**)
| Variable  | Valeur | Obligatoire |
|-----------|--------|-------------|
| `APP_KEY` | une clé `base64:...` (voir ci-dessous) | conseillé — sinon les sessions sont perdues à chaque redémarrage |
| `ECOLE_NOM` | ex. `Groupe Scolaire Les Lauriers` | non |
| `ECOLE_DEVISE` | `FCFA` par défaut | non |

Générer une clé : sur un ordinateur `php artisan key:generate --show`, ou demander à Claude.

## 3. Obtenir le lien
**Settings** → **Networking** → **Generate Domain**. Le lien `https://xxxx.up.railway.app` s'ouvre sur
n'importe quel téléphone ou ordinateur. Le premier déploiement prend 3 à 5 minutes.

Au premier démarrage, la base est créée et remplie avec les données de démonstration.
Comptes (mot de passe `password`) : `admin@ecole.test`, `caisse@ecole.test`, `compta@ecole.test`,
`direction@ecole.test`, `parent@ecole.test`.

## 4. (Facultatif) Conserver les données entre deux déploiements
Sans volume, la base SQLite est recréée à chaque déploiement (pratique pour une démo).
Pour la garder : clic droit sur le service → **Attach Volume** → *Mount path* : `/data`.

Autres variables utiles : `SEED_DEMO=false` (pas de données de démo), `SMS_DRIVER=http` + `SMS_URL` + `SMS_TOKEN`
(envoi réel des SMS), `DB_CONNECTION=mysql` + `DB_HOST`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` (base MySQL Railway).

> ⚠️ Avant une vraie mise en service : changer tous les mots de passe des comptes de démonstration.
