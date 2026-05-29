<?php
/**
 * ANRDI — Classe de sécurité centrale
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }

class Security
{
    // ═══════════════════════════════════════════════════
    //  CSRF (token à usage unique)
    // ═══════════════════════════════════════════════════

    public static function generateCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $token  = bin2hex(random_bytes((int)(CSRF_LENGTH / 2)));
        $expiry = time() + CSRF_EXPIRY;
        $_SESSION['_csrf_token']  = $token;
        $_SESSION['_csrf_expiry'] = $expiry;
        return $token;
    }

    public static function verifyCsrfToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['_csrf_token']) || empty($_SESSION['_csrf_expiry'])) return false;
        if (time() > $_SESSION['_csrf_expiry']) {
            unset($_SESSION['_csrf_token'], $_SESSION['_csrf_expiry']);
            return false;
        }
        $valid = hash_equals($_SESSION['_csrf_token'], $token);
        // Token à usage unique → on l'efface immédiatement
        unset($_SESSION['_csrf_token'], $_SESSION['_csrf_expiry']);
        return $valid;
    }

    public static function csrfField(): string
    {
        $t = self::generateCsrfToken();
        return sprintf(
            '<input type="hidden" name="_csrf" value="%s" autocomplete="off">',
            htmlspecialchars($t, ENT_QUOTES, 'UTF-8')
        );
    }

    public static function requireCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::verifyCsrfToken($token)) {
            http_response_code(403);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                die(json_encode(['error' => 'Token de sécurité invalide. Rechargez la page.']));
            }
            die('Requête invalide. <a href="javascript:history.back()">Retour</a>');
        }
    }

    // ═══════════════════════════════════════════════════
    //  SANITISATION
    // ═══════════════════════════════════════════════════

    public static function sanitize(mixed $input): mixed
    {
        if (is_array($input)) return array_map([self::class, 'sanitize'], $input);
        if (!is_string($input)) return $input;
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
    }

    public static function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));
        return preg_replace('/\.{2,}/', '.', $name);
    }

    public static function sanitizeHtml(string $html): string
    {
        // Liste blanche stricte pour l'éditeur riche
        $allowed = '<p><br><strong><em><u><ul><ol><li><a><h2><h3><h4><blockquote>';
        $html    = strip_tags($html, $allowed);
        $html    = preg_replace('/(<[^>]+)\s+(on\w+|style|xmlns|formaction)\s*=\s*["\'][^"\']*["\']/i', '$1', $html);
        return    preg_replace('/href\s*=\s*["\']?\s*javascript:/i', 'href="#"', $html);
    }

    // ═══════════════════════════════════════════════════
    //  VALIDATION
    // ═══════════════════════════════════════════════════

    public static function validateEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateProEmail(string $email): bool
    {
        if (!self::validateEmail($email)) return false;
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return !in_array($domain, PRO_FORBIDDEN_DOMAINS, true);
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];
        if (strlen($password) < 12)            $errors[] = 'Minimum 12 caractères.';
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Au moins une majuscule.';
        if (!preg_match('/[a-z]/', $password)) $errors[] = 'Au moins une minuscule.';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Au moins un chiffre.';
        if (!preg_match('/[\W_]/', $password)) $errors[] = 'Au moins un caractère spécial.';
        return $errors;
    }

    // ═══════════════════════════════════════════════════
    //  CHIFFREMENT AES-256-CBC avec HMAC
    // ═══════════════════════════════════════════════════

    public static function encrypt(string $data): string
    {
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($data, ENC_ALGO, hex2bin(ENC_KEY), OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) throw new RuntimeException('Erreur de chiffrement.');
        $hmac = hash_hmac('sha256', $iv . $encrypted, ENC_KEY, true);
        return base64_encode($iv . $hmac . $encrypted);
    }

    public static function decrypt(string $data): string|false
    {
        $raw = base64_decode($data, true);
        if ($raw === false || strlen($raw) < 48) return false;

        $iv        = substr($raw, 0, 16);
        $hmac      = substr($raw, 16, 32);
        $encrypted = substr($raw, 48);
        $expected  = hash_hmac('sha256', $iv . $encrypted, ENC_KEY, true);

        if (!hash_equals($expected, $hmac)) {
            self::triggerDataCorruption();
            return false;
        }
        return openssl_decrypt($encrypted, ENC_ALGO, hex2bin(ENC_KEY), OPENSSL_RAW_DATA, $iv) ?: false;
    }

    /**
     * En cas de falsification HMAC :
     * Renvoie des données corrompues aléatoires pour leurrer l'attaquant.
     */
    public static function triggerDataCorruption(): never
    {
        error_log('[ANRDI SECURITY CRITICAL] Falsification HMAC — IP: ' . self::getClientIp());
        http_response_code(200);
        header('Content-Type: application/octet-stream');
        echo base64_encode(random_bytes(random_int(256, 1024)));
        exit;
    }

    // ═══════════════════════════════════════════════════
    //  MOTS DE PASSE (Argon2id)
    // ═══════════════════════════════════════════════════

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 2,
        ]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // ═══════════════════════════════════════════════════
    //  HONEYPOT
    // ═══════════════════════════════════════════════════

    public static function honeypotField(): string
    {
        $field = HONEYPOT_FIELD ?? 'website_url';
        return sprintf(
            '<div style="position:absolute;left:-99999px;top:-99999px;visibility:hidden;" aria-hidden="true" tabindex="-1">' .
            '<label for="%1$s">Ne pas remplir ce champ</label>' .
            '<input type="text" id="%1$s" name="%1$s" value="" tabindex="-1" autocomplete="off" aria-hidden="true">' .
            '</div>',
            htmlspecialchars($field, ENT_QUOTES, 'UTF-8')
        );
    }

    public static function checkHoneypot(): void
    {
        $field = HONEYPOT_FIELD ?? 'website_url';
        if (!empty($_POST[$field])) {
            error_log('[ANRDI SECURITY] Bot honeypot — IP: ' . self::getClientIp());
            http_response_code(200);
            die('Merci pour votre message.');
        }
    }

    // ═══════════════════════════════════════════════════
    //  RATE LIMITING (basé session + IP)
    // ═══════════════════════════════════════════════════

    public static function checkRateLimit(
        string $action,
        int    $maxAttempts = 5,
        int    $window      = 900
    ): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $key  = '_rl_' . md5($action . self::getClientIp());
        $now  = time();
        $data = $_SESSION[$key] ?? ['count' => 0, 'reset_at' => $now + $window];

        if ($now > $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => $now + $window];
        }

        $data['count']++;
        $_SESSION[$key] = $data;

        if ($data['count'] > $maxAttempts) {
            $remaining = $data['reset_at'] - $now;
            http_response_code(429);
            header('Retry-After: ' . $remaining);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                die(json_encode(['error' => sprintf(
                    'Trop de tentatives. Réessayez dans %d minute(s).',
                    (int) ceil($remaining / 60)
                )]));
            }
            die(sprintf('Trop de tentatives. Réessayez dans %d minute(s).', (int) ceil($remaining / 60)));
        }
    }

    // ═══════════════════════════════════════════════════
    //  HEADERS DE SÉCURITÉ
    // ═══════════════════════════════════════════════════

    public static function setSecurityHeaders(string $context = 'main'): void
    {
        header_remove('X-Powered-By');
        header_remove('Server');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-site');

        // CSP adaptée au contexte
        $cdnUrl     = URL_CDN  ?? 'https://cdn.anrdi.fr';
        $apiUrl     = URL_API  ?? 'https://api.anrdi.fr';
        $mainUrl    = URL_MAIN ?? 'https://anrdi.fr';
        $consentCdn = 'https://cdn.axet.fr';

        $csp = match($context) {
            'api'   => "default-src 'none'; connect-src 'self'; frame-ancestors 'none';",
            'admin' => "default-src 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn; script-src 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn; style-src 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn 'unsafe-inline'; style-src-elem 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn 'unsafe-inline'; font-src 'self' $cdnUrl https://cdnjs.cloudflare.com; img-src 'self' $cdnUrl $consentCdn data:; connect-src 'self' $apiUrl $consentCdn; frame-ancestors 'none'; form-action 'self';",
            default => "default-src 'self'; script-src 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn; style-src 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn 'unsafe-inline'; style-src-elem 'self' $cdnUrl https://cdnjs.cloudflare.com $consentCdn 'unsafe-inline'; font-src 'self' $cdnUrl https://cdnjs.cloudflare.com; img-src 'self' $cdnUrl $consentCdn data: blob:; connect-src 'self' $apiUrl $consentCdn; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;",
        };
        header("Content-Security-Policy: $csp");
    }

    // ═══════════════════════════════════════════════════
    //  UTILITAIRES
    // ═══════════════════════════════════════════════════

    public static function getClientIp(): string
    {
        $candidates = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];
        foreach ($candidates as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function isAjax(): bool
    {
        return (
            ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' ||
            str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        );
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
