# ANRDI

Site institutionnel PHP de l'ANRDI.

## Apercu

Le projet fournit :

- le site public
- l'authentification classique
- l'authentification OAuth (`Google`, `Microsoft`, `GitHub`, `X`)
- l'espace membre
- l'envoi d'emails transactionnels

Le projet est base sur PHP 8.1+ et Composer.

## Structure

```text
httpdocs/
├── assets/
├── errors/
├── includes/
├── membre/
├── oauth/
├── pages/
├── src/
├── templates/
├── composer.json
└── index.php
```

## Prerequis

- PHP `8.1` ou superieur
- Composer
- MySQL ou MariaDB
- Serveur web Apache ou Nginx
- HTTPS actif en production

## Installation

```bash
git clone https://github.com/TON-USER/TON-REPO.git
cd TON-REPO
composer install --no-dev --optimize-autoloader
```

## Configuration

Le site charge sa configuration sensible depuis :

```text
src/Core/Internal/System/Runtime/Config/Env/.secure/config.php
```

Ce fichier ne doit jamais etre versionne.

Le bootstrap principal est dans :

```text
includes/bootstrap.php
```

Exemple minimal de structure attendue pour `config.php` :

```php
<?php

return [
    'app' => [
        'env' => 'production',
        'debug' => false,
        'timezone' => 'Europe/Paris',
        'secret' => 'CHANGE_ME',
    ],
    'domains' => [
        'main' => 'https://anrdi.fr',
        'pro' => 'https://pro.anrdi.fr',
        'admin' => 'https://admin.anrdi.fr',
        'cdn' => 'https://cdn.anrdi.fr',
        'api' => 'https://api.anrdi.fr',
        'membres' => 'https://membres.anrdi.fr',
        'status' => 'https://status.anrdi.fr',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'database_name',
        'user' => 'database_user',
        'password' => 'database_password',
        'charset' => 'utf8mb4',
        'prefix' => 'anrdi_',
    ],
    'mail' => [
        'enabled' => true,
        'from_address' => 'noreply@example.com',
        'from_name' => 'ANRDI',
        'host' => 'smtp.example.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'smtp_user',
        'password' => 'smtp_password',
        'reply_to' => 'contact@example.com',
        'contact_address' => 'contact@example.com',
    ],
    'session' => [
        'name' => 'ANRDI_SESS',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
        'lifetime' => 3600,
    ],
    'security' => [
        'csrf_token_length' => 64,
        'csrf_expiry' => 3600,
        'login_max_attempts' => 5,
        'login_lockout' => 900,
        'honeypot_field' => 'website_url',
    ],
    'oauth' => [
        'google' => [
            'enabled' => false,
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'https://anrdi.fr/oauth/google/callback.php',
        ],
        'microsoft' => [
            'enabled' => false,
            'tenant_id' => 'common',
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'https://anrdi.fr/oauth/microsoft/callback.php',
        ],
        'github' => [
            'enabled' => false,
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'https://anrdi.fr/oauth/github/callback.php',
        ],
        'x' => [
            'enabled' => false,
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'https://anrdi.fr/oauth/x/callback.php',
        ],
    ],
    'logo' => [
        'header' => '/assets/img/logo-header.png',
        'footer' => '/assets/img/logo-footer.png',
        'favicon' => '/assets/img/favicon.png',
        'og' => '/assets/img/og-image.png',
    ],
    'uploads' => [
        'path' => __DIR__ . '/../../../../../../uploads/secure/',
    ],
    'api' => [
        'version' => 'v1',
        'rate_limit' => 60,
        'cors_origins' => [],
    ],
    'pro_forbidden_domains' => [],
];
```

## Securite

Ne jamais envoyer sur GitHub :

- `config.php`
- les mots de passe et secrets OAuth
- les dumps SQL
- les logs
- `vendor/`
- les uploads utilisateur

Le fichier [`.gitignore`](./.gitignore) bloque deja les elements sensibles les plus courants.

Si un secret a ete publie par erreur, il faut le revoquer et le regenerer.

## Deploiement

Exemple de deploiement minimal :

```bash
composer install --no-dev --optimize-autoloader
```

Verifier ensuite :

- les droits du fichier de configuration prive
- la connexion base de donnees
- la configuration SMTP
- les URLs de callback OAuth
- le bon fonctionnement en HTTPS

## Mise sur GitHub

Initialisation locale :

```bash
git init
git branch -M main
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/TON-USER/TON-REPO.git
git push -u origin main
```

Avant le premier `push`, verifier :

```bash
git status
```

## Licence

Usage interne ANRDI sauf mention contraire.
