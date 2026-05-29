<?php
/**
 * ANRDI — Gestion de l'authentification
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }

class Auth
{
    const ROLE_MEMBER      = 'member';
    const ROLE_PRO         = 'professional';
    const ROLE_ADMIN       = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';

    // ═══════════════════════════════════════════════════
    //  SESSION
    // ═══════════════════════════════════════════════════

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::validateFingerprint();
            return;
        }
        session_start();
        if (empty($_SESSION['_initialized'])) {
            session_regenerate_id(true);
            $_SESSION['_initialized'] = true;
            $_SESSION['_ip']          = Security::getClientIp();
            $_SESSION['_ua_hash']     = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $_SESSION['_created']     = time();
            $_SESSION['_last']        = time();
        } else {
            self::validateFingerprint();
        }
    }

    private static function validateFingerprint(): void
    {
        if (empty($_SESSION['_ip']) || empty($_SESSION['_ua_hash'])) return;

        $loggedIn = !empty($_SESSION['_logged_in']) && !empty($_SESSION['_uid']);
        $ipOk = !$loggedIn || ($_SESSION['_ip'] === Security::getClientIp());
        $uaOk = ($_SESSION['_ua_hash'] === hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? ''));

        if (!$ipOk || !$uaOk) {
            error_log('[ANRDI AUTH] Session hijacking détecté — IP: ' . Security::getClientIp());
            self::forceLogout('/login.php?error=session_invalid');
        }

        // Expiration de session
        $lifetime = (int) ini_get('session.gc_maxlifetime') ?: 3600;
        if (isset($_SESSION['_last']) && (time() - $_SESSION['_last']) > $lifetime) {
            self::forceLogout('/login.php?error=session_expired');
        }
        $_SESSION['_last'] = time();
    }

    private static function forceLogout(string $redirect): never
    {
        self::logout();
        header('Location: ' . $redirect);
        exit;
    }

    // ═══════════════════════════════════════════════════
    //  CONNEXION
    // ═══════════════════════════════════════════════════

    public static function login(string $email, string $password): array
    {
        $email = Security::sanitizeEmail($email);
        if (!Security::validateEmail($email)) {
            return ['success' => false, 'error' => 'Adresse email invalide.'];
        }

        $stmt = Database::query(
            'SELECT id, email, password_hash, role, is_active, is_verified,
                    failed_attempts, locked_until
             FROM ' . DB_PREFIX . 'users
             WHERE email = ? LIMIT 1',
            [$email]
        );
        $user = $stmt->fetch();

        if (!$user) {
            // Timing constant (anti-énumération)
            password_verify('dummy_timing_attack_prevention', '$argon2id$v=19$m=65536,t=4,p=2$dummy');
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }

        // Compte verrouillé ?
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $min = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'error' => "Compte temporairement verrouillé. Réessayez dans {$min} min."];
        }

        if (!Security::verifyPassword($password, $user['password_hash'])) {
            self::incrementFailed((int)$user['id'], (int)$user['failed_attempts']);
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }

        if (!$user['is_active'])   return ['success' => false, 'error' => 'Compte désactivé. Contactez l\'administration.'];
        if (!$user['is_verified']) return ['success' => false, 'error' => 'Vérifiez votre email pour activer votre compte.'];

        // Réinitialiser les tentatives
        Database::query(
            'UPDATE ' . DB_PREFIX . 'users
             SET failed_attempts = 0, locked_until = NULL, last_login = NOW(), last_login_ip = ?
             WHERE id = ?',
            [Security::encrypt(Security::getClientIp()), $user['id']]
        );

        self::setUserSession($user);
        return ['success' => true, 'role' => $user['role']];
    }

    private static function incrementFailed(int $userId, int $current): void
    {
        $next      = $current + 1;
        $lockUntil = $next >= MAX_ATTEMPTS
            ? date('Y-m-d H:i:s', time() + LOCKOUT_TIME)
            : null;
        Database::query(
            'UPDATE ' . DB_PREFIX . 'users SET failed_attempts = ?, locked_until = ? WHERE id = ?',
            [$next, $lockUntil, $userId]
        );
    }

    public static function setUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['_uid']       = (int) $user['id'];
        $_SESSION['_role']      = $user['role'];
        $_SESSION['_logged_in'] = true;
        $_SESSION['_last']      = time();
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 86400,
                    $p['path'], $p['domain'], $p['secure'], $p['httponly']
                );
            }
            session_destroy();
        }
    }

    // ═══════════════════════════════════════════════════
    //  CONTRÔLE D'ACCÈS
    // ═══════════════════════════════════════════════════

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['_logged_in']) && !empty($_SESSION['_uid']);
    }

    public static function requireLogin(string $redirect = '/login.php'): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . $redirect . '?next=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    public static function requireRole(string $minRole, string $redirect = '/login.php'): void
    {
        self::requireLogin($redirect);
        $hierarchy = [
            self::ROLE_MEMBER      => 1,
            self::ROLE_PRO         => 2,
            self::ROLE_ADMIN       => 3,
            self::ROLE_SUPER_ADMIN => 4,
        ];
        $userLevel = $hierarchy[$_SESSION['_role'] ?? ''] ?? 0;
        $reqLevel  = $hierarchy[$minRole] ?? 99;

        if ($userLevel < $reqLevel) {
            http_response_code(403);
            header('Location: /errors/403.php');
            exit;
        }
    }

    public static function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) return null;
        $stmt = Database::query(
            'SELECT id, email, first_name, last_name, role, avatar, created_at, last_login
             FROM ' . DB_PREFIX . 'users WHERE id = ? LIMIT 1',
            [$_SESSION['_uid']]
        );
        return $stmt->fetch() ?: null;
    }

    public static function getUserRole(): string
    {
        return $_SESSION['_role'] ?? '';
    }

    // ═══════════════════════════════════════════════════
    //  OAUTH2 — Initiation
    // ═══════════════════════════════════════════════════

    public static function getOAuthUrl(string $provider): string
    {
        $cfg = OAUTH[$provider] ?? null;

        if (!$cfg || !($cfg['enabled'] ?? false)) {
            throw new RuntimeException("Provider OAuth2 désactivé : $provider");
        }

        $state = Security::generateToken(16);
        $_SESSION['_oauth_state']    = $state;
        $_SESSION['_oauth_provider'] = $provider;

        $params = [
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => implode(' ', $cfg['scopes']),
            'state'         => $state,
        ];

        // Options spécifiques
        if ($provider === 'google') {
            $params['access_type'] = 'online';
            $params['prompt']      = 'select_account';
        }
        if ($provider === 'microsoft') {
            $params['response_mode'] = 'query';
        }
        if ($provider === 'x' && ($cfg['pkce'] ?? false)) {
            $verifier                        = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $_SESSION['_oauth_pkce']         = $verifier;
            $params['code_challenge']        = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
            $params['code_challenge_method'] = 'S256';
        }

        return $cfg['auth_url'] . '?' . http_build_query($params);
    }

    // ═══════════════════════════════════════════════════
    //  OAUTH2 — Callback
    // ═══════════════════════════════════════════════════

    public static function handleOAuthCallback(string $provider, string $code, string $state): array
    {
        if (
            empty($_SESSION['_oauth_state']) ||
            !hash_equals($_SESSION['_oauth_state'], $state) ||
            ($_SESSION['_oauth_provider'] ?? '') !== $provider
        ) {
            throw new RuntimeException('State OAuth2 invalide ou provider mismatch.');
        }

        // Nettoyer la session OAuth
        unset($_SESSION['_oauth_state'], $_SESSION['_oauth_provider']);

        $cfg         = OAUTH[$provider];
        $accessToken = self::oauthExchangeCode($provider, $cfg, $code);
        $userInfo    = self::oauthFetchUser($provider, $cfg, $accessToken);

        return self::oauthFindOrCreate($provider, $userInfo);
    }

    private static function oauthExchangeCode(string $provider, array $cfg, string $code): string
    {
        $params = [
            'client_id'     => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'code'          => $code,
            'redirect_uri'  => $cfg['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ];

        if ($provider === 'github') {
            // GitHub veut Accept: application/json
        }
        if ($provider === 'x' && isset($_SESSION['_oauth_pkce'])) {
            $params['code_verifier'] = $_SESSION['_oauth_pkce'];
            unset($_SESSION['_oauth_pkce']);
        }

        $ch = curl_init($cfg['token_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'ANRDI/1.0',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode((string)$res, true);
        if (empty($data['access_token'])) {
            error_log('[ANRDI OAuth] Token invalide pour ' . $provider . ': ' . $res);
            throw new RuntimeException('Token OAuth2 non reçu.');
        }
        return $data['access_token'];
    }

    private static function oauthFetchUser(string $provider, array $cfg, string $token): array
    {
        $ch = curl_init($cfg['userinfo_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'User-Agent: ANRDI/1.0',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $res  = curl_exec($ch);
        curl_close($ch);
        $data = json_decode((string)$res, true) ?? [];

        // Microsoft retourne des champs différents
        if ($provider === 'microsoft') {
            $data['email'] = $data['mail'] ?? $data['userPrincipalName'] ?? '';
            $data['given_name']  = $data['givenName']  ?? '';
            $data['family_name'] = $data['surname']    ?? '';
        }

        // X — email séparé
        if ($provider === 'x') {
            $data['email'] = $data['data']['email'] ?? '';
            $data['name']  = $data['data']['name']  ?? '';
            $data['id']    = $data['data']['id']    ?? '';
        }

        return $data;
    }

    private static function oauthFindOrCreate(string $provider, array $info): array
    {
        $email     = Security::sanitizeEmail($info['email'] ?? '');
        $firstName = Security::sanitize($info['given_name']  ?? $info['name'] ?? '');
        $lastName  = Security::sanitize($info['family_name'] ?? '');
        $avatar    = Security::sanitize($info['picture'] ?? $info['avatar_url'] ?? '');
        $oauthId   = (string)($info['sub'] ?? $info['id'] ?? '');

        if (!Security::validateEmail($email)) {
            throw new RuntimeException('Email OAuth2 invalide ou manquant.');
        }

        $stmt = Database::query(
            'SELECT * FROM ' . DB_PREFIX . 'users WHERE email = ? LIMIT 1',
            [$email]
        );
        $user = $stmt->fetch();

        if (!$user) {
            Database::query(
                'INSERT INTO ' . DB_PREFIX . 'users
                 (email, first_name, last_name, role, oauth_provider, oauth_id,
                  is_active, is_verified, avatar, rgpd_consented_at, rgpd_consent_version, created_at)
                 VALUES (?,?,?,?,?,?,1,1,?,NOW(),?,NOW())',
                [$email, $firstName, $lastName, self::ROLE_MEMBER, $provider, $oauthId, $avatar, '1.0']
            );
            $user = ['id' => (int)Database::lastInsertId(), 'role' => self::ROLE_MEMBER, 'email' => $email];
        } else {
            Database::query(
                'UPDATE ' . DB_PREFIX . 'users SET last_login = NOW(), avatar = ? WHERE id = ?',
                [$avatar, $user['id']]
            );
        }

        self::setUserSession($user);
        return ['success' => true, 'user' => $user];
    }

    // ═══════════════════════════════════════════════════
    //  RESET MOT DE PASSE
    // ═══════════════════════════════════════════════════

    public static function generateResetToken(string $email): ?string
    {
        $email = Security::sanitizeEmail($email);
        $stmt  = Database::query(
            'SELECT id FROM ' . DB_PREFIX . 'users WHERE email = ? AND is_active = 1 LIMIT 1',
            [$email]
        );
        $user = $stmt->fetch();
        if (!$user) return null; // Silencieux (anti-énumération)

        $token   = Security::generateToken(32);
        $expires = date('Y-m-d H:i:s', time() + 3600);
        Database::query(
            'INSERT INTO ' . DB_PREFIX . 'password_resets (user_id, token, expires_at)
             VALUES (?,?,?) ON DUPLICATE KEY UPDATE token=VALUES(token), expires_at=VALUES(expires_at)',
            [$user['id'], hash('sha256', $token), $expires]
        );
        return $token;
    }

    public static function validateResetToken(string $token): ?array
    {
        $stmt = Database::query(
            'SELECT pr.user_id, u.email, u.first_name FROM ' . DB_PREFIX . 'password_resets pr
             JOIN ' . DB_PREFIX . 'users u ON u.id = pr.user_id
             WHERE pr.token = ? AND pr.expires_at > NOW() LIMIT 1',
            [hash('sha256', $token)]
        );
        return $stmt->fetch() ?: null;
    }
}
