#!/usr/bin/env bash
# Démarrage du conteneur : configuration, base de données, planificateur, Apache.
set -e
cd /var/www/html

# Adresse publique : fournie par Railway (RAILWAY_PUBLIC_DOMAIN) ou Render (RENDER_EXTERNAL_URL).
if [ -z "$APP_URL" ]; then
    if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
        export APP_URL="https://$RAILWAY_PUBLIC_DOMAIN"
    elif [ -n "$RENDER_EXTERNAL_URL" ]; then
        export APP_URL="$RENDER_EXTERNAL_URL"
    fi
fi

# Clé de chiffrement : à définir dans les variables de l'hébergeur pour qu'elle reste stable.
if [ -z "$APP_KEY" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
    echo "AVERTISSEMENT : APP_KEY absente, clé temporaire générée (les sessions seront perdues au redémarrage)."
fi

# Base SQLite (par défaut). Monter un volume sur /data pour la conserver entre les déploiements.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    if [ -z "$DB_DATABASE" ]; then
        if [ -d /data ]; then export DB_DATABASE=/data/database.sqlite; else export DB_DATABASE=/var/www/html/database/database.sqlite; fi
    fi
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE" "$(dirname "$DB_DATABASE")"
fi

php artisan migrate --force --no-interaction

# Données de démonstration au premier démarrage (désactivable avec SEED_DEMO=false).
if [ "${SEED_DEMO:-true}" = "true" ] && [ "$(php artisan tinker --execute 'echo \App\Models\User::count();' 2>/dev/null | tail -n1)" = "0" ]; then
    echo "Base vide : chargement des données de démonstration..."
    php artisan db:seed --force --no-interaction
fi

php artisan optimize
chown -R www-data:www-data storage bootstrap/cache

# Planificateur (rappels SMS quotidiens) en arrière-plan.
su -s /bin/bash www-data -c "php artisan schedule:work" > /proc/1/fd/1 2>&1 &

exec apache2-foreground
