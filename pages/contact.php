<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
Security::setSecurityHeaders(); Auth::startSession();
$success=$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    Security::requireCsrf(); Security::checkHoneypot(); Security::checkRateLimit('contact',3,900);
    $name=Security::sanitize($_POST['name']??''); $email=Security::sanitizeEmail($_POST['email']??'');
    $subject=Security::sanitize($_POST['subject']??''); $message=Security::sanitize($_POST['message']??'');
    if(!$name||!$email||!$subject||!$message){$error='Tous les champs sont obligatoires.';}
    elseif(!Security::validateEmail($email)){$error='Adresse email invalide.';}
    else { try { Database::query('INSERT INTO '.DB_PREFIX.'contacts(name,email,subject,message,ip_address)VALUES(?,?,?,?,?)',[$name,$email,$subject,$message,Security::encrypt(Security::getClientIp())]); Mailer::sendContactAcknowledgement($email,$name,$subject); Mailer::sendInternalContactNotification($name,$email,$subject,$message); $success='Votre message a été envoyé. Nous vous répondrons sous 48h ouvrées.'; } catch(Exception $e){ $error='Erreur. Veuillez réessayer.'; } }
}
$pageTitle='Contact — ANRDI'; $pageDescription='Contactez l\'ANRDI.';
include __DIR__.'/../includes/header.php';
?>
<section class="section"><div class="container" style="max-width:680px;">
<div class="section-header"><span class="section-label">Nous écrire</span><h1 class="section-title">Contacter l'ANRDI</h1><p class="section-desc">Notre équipe répond dans un délai de 48h ouvrées.</p></div>
<?php if($success): ?><div class="alert alert--success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><span><?= htmlspecialchars($success,ENT_QUOTES,'UTF-8') ?></span></div>
<?php elseif($error): ?><div class="alert alert--error"><span><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></span></div><?php endif; ?>
<?php if(!$success): ?>
<form method="POST" action="/pages/contact.php" novalidate><?= Security::csrfField() ?><?= Security::honeypotField() ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
<div class="form-group"><label for="name" class="form-label form-label--required">Nom complet</label><input type="text" id="name" name="name" class="form-input" required autocomplete="name" placeholder="Jean Dupont" value="<?= htmlspecialchars($_POST['name']??'',ENT_QUOTES,'UTF-8') ?>"></div>
<div class="form-group"><label for="email" class="form-label form-label--required">Email</label><input type="email" id="email" name="email" class="form-input" required autocomplete="email" placeholder="vous@exemple.fr" value="<?= htmlspecialchars($_POST['email']??'',ENT_QUOTES,'UTF-8') ?>"></div>
</div>
<div class="form-group"><label for="subject" class="form-label form-label--required">Objet</label><input type="text" id="subject" name="subject" class="form-input" required placeholder="Objet de votre message" value="<?= htmlspecialchars($_POST['subject']??'',ENT_QUOTES,'UTF-8') ?>"></div>
<div class="form-group"><label for="message" class="form-label form-label--required">Message</label><textarea id="message" name="message" class="form-textarea" required rows="6" placeholder="Votre message…"><?= htmlspecialchars($_POST['message']??'',ENT_QUOTES,'UTF-8') ?></textarea></div>
<button type="submit" class="btn btn--primary btn--lg">Envoyer le message</button>
</form><?php endif; ?>
</div></section>
<?php include __DIR__.'/../includes/footer.php'; ?>
