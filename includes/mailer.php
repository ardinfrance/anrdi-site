<?php
/**
 * ANRDI - Service Email (PHPMailer wrapper)
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }

if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private static function make(): PHPMailer
    {
        if (!MAIL_ENABLED) {
            throw new RuntimeException('Le service email est desactive.');
        }

        if (MAIL_HOST === '' || MAIL_USER === '' || MAIL_PASS === '') {
            throw new RuntimeException('Configuration SMTP incomplete.');
        }

        $mail = new PHPMailer(true);
        $encryption = strtolower((string) MAIL_ENC);
        $supportsTls = extension_loaded('openssl');

        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPAuth = MAIL_USER !== '' && MAIL_PASS !== '';
        $mail->SMTPAutoTLS = $supportsTls;
        $mail->Host = MAIL_HOST;
        $mail->Port = MAIL_PORT;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->Timeout = 20;
        $mail->SMTPKeepAlive = false;
        if (!$supportsTls && !in_array($encryption, ['', 'none'], true)) {
            error_log('[ANRDI MAIL][WARN] OpenSSL indisponible, bascule SMTP sans TLS.');
            $encryption = 'none';
        }
        $mail->SMTPSecure = match ($encryption) {
            'ssl', 'smtps' => PHPMailer::ENCRYPTION_SMTPS,
            'tls', 'starttls' => PHPMailer::ENCRYPTION_STARTTLS,
            'none', '' => false,
            default => PHPMailer::ENCRYPTION_STARTTLS,
        };
        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        if (defined('MAIL_REPLY_TO') && MAIL_REPLY_TO !== '') {
            $mail->addReplyTo(MAIL_REPLY_TO, MAIL_NAME);
        }
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];

        return $mail;
    }

    private static function templatePath(string $name): string
    {
        return rtrim(TEMPLATE_PATH, '/\\') . '/mail/' . $name . '.html';
    }

    private static function loadTemplate(string $name, array $vars): string
    {
        $path = self::templatePath($name);
        if (!is_file($path)) {
            throw new RuntimeException("Template email introuvable : {$name}");
        }

        $html = (string) file_get_contents($path);
        foreach ($vars as $key => $value) {
            $html = str_replace('{{' . $key . '}}', self::escapeTemplateValue((string) $value), $html);
        }

        return (string) preg_replace('/\{\{[a-z0-9_]+\}\}/i', '', $html);
    }

    private static function escapeTemplateValue(string $value): string
    {
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
        return str_replace(['&apos;', '&#039;'], "'", $escaped);
    }

    private static function htmlToText(string $html): string
    {
        $text = str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</li>'], "\n", $html);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        return trim(wordwrap($text, 76, "\n", true));
    }

    public static function send(
        string $to,
        string $toName,
        string $subject,
        string $template,
        array $vars = []
    ): bool {
        if (!MAIL_ENABLED) {
            error_log("[ANRDI MAIL][DISABLED] {$template} -> {$to} | {$subject}");
            return false;
        }

        try {
            $html = self::loadTemplate($template, $vars);
            $mail = self::make();
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = self::htmlToText($html);
            $mail->send();
            error_log("[ANRDI MAIL][OK] {$template} -> {$to} | {$subject}");
            return true;
        } catch (Throwable $e) {
            error_log('[ANRDI MAIL][ERROR] ' . $subject . ' -> ' . $to . ' | ' . $e->getMessage());
            return false;
        }
    }

    public static function sendWelcome(string $email, string $firstName, string $verifyUrl): bool
    {
        return self::send($email, $firstName, 'Bienvenue sur ANRDI - Activez votre compte', 'welcome', [
            'first_name' => $firstName,
            'verify_url' => $verifyUrl,
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendPasswordReset(string $email, string $firstName, string $resetUrl): bool
    {
        return self::send($email, $firstName, 'Réinitialisation du mot de passe - ANRDI', 'password-reset', [
            'first_name' => $firstName,
            'reset_url' => $resetUrl,
            'expiry' => '1 heure',
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendSecurityAlert(string $email, string $firstName, string $action, string $ip): bool
    {
        return self::send($email, $firstName, 'Alerte sécurité - ANRDI', 'security-alert', [
            'first_name' => $firstName,
            'action' => $action,
            'ip_address' => $ip,
            'datetime' => date('d/m/Y à H:i:s'),
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendProValidation(string $email, string $orgName, bool $approved, string $reason = ''): bool
    {
        $template = $approved ? 'pro-approved' : 'pro-rejected';
        $subject = $approved ? 'Espace professionnel activé - ANRDI' : 'Votre demande professionnelle - ANRDI';
        return self::send($email, $orgName, $subject, $template, [
            'org_name' => $orgName,
            'reason' => $reason,
            'app_url' => URL_MAIN,
            'pro_url' => URL_PRO,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendDossierSubmitted(string $email, string $firstName, string $reference, string $title): bool
    {
        return self::send($email, $firstName, 'Votre dossier a bien été reçu - ANRDI', 'dossier-submitted', [
            'first_name' => $firstName,
            'reference' => $reference,
            'title' => $title,
            'dashboard_url' => URL_MAIN . '/membre/dossiers.php',
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendContactAcknowledgement(string $email, string $name, string $subjectLine): bool
    {
        return self::send($email, $name, 'Nous avons bien reçu votre message - ANRDI', 'contact-ack', [
            'name' => $name,
            'subject_line' => $subjectLine,
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendRgpdAcknowledgement(string $email, string $name, string $requestType): bool
    {
        return self::send($email, $name, 'Demande RGPD reçue - ANRDI', 'rgpd-ack', [
            'name' => $name,
            'request_type' => $requestType,
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendVerificationReminder(string $email, string $firstName, string $verifyUrl): bool
    {
        return self::send($email, $firstName, 'Rappel d’activation de votre compte - ANRDI', 'verification-reminder', [
            'first_name' => $firstName,
            'verify_url' => $verifyUrl,
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendInternalContactNotification(string $name, string $email, string $subjectLine, string $message): bool
    {
        return self::send(MAIL_CONTACT, 'Support ANRDI', 'Nouveau message de contact - ' . $subjectLine, 'internal-contact', [
            'name' => $name,
            'email' => $email,
            'subject_line' => $subjectLine,
            'message' => $message,
            'dashboard_url' => URL_ADMIN,
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }

    public static function sendInternalDossierNotification(string $email, string $firstName, string $reference, string $title, string $category): bool
    {
        return self::send(MAIL_CONTACT, 'Support ANRDI', 'Nouveau dossier membre - ' . $reference, 'internal-dossier', [
            'first_name' => $firstName,
            'email' => $email,
            'reference' => $reference,
            'title' => $title,
            'category' => $category,
            'dashboard_url' => URL_ADMIN . '/dossiers.php',
            'app_url' => URL_MAIN,
            'current_year' => date('Y'),
        ]);
    }
}
