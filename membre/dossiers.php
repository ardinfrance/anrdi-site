<?php
/**
 * ANRDI — Mes dossiers (espace membre)
 */
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

Security::setSecurityHeaders();
Auth::startSession();
Auth::requireLogin('/login.php');

$user = Auth::getCurrentUser();

/* ── Soumission d'un nouveau dossier ─────────────────────────── */
$formError   = '';
$formSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_dossier') {
    Security::requireCsrf();
    Security::checkHoneypot();
    Security::checkRateLimit('dossier_submit_' . $user['id'], 5, 900);

    $title       = Security::sanitize($_POST['title'] ?? '');
    $category    = Security::sanitize($_POST['category'] ?? '');
    $description = Security::sanitize($_POST['description'] ?? '');

    $validCategories = ['domaine', 'protection', 'agrement', 'rgpd', 'signalement', 'autre'];

    if (strlen($title) < 5 || strlen($title) > 200) {
        $formError = 'L\'objet du dossier doit contenir entre 5 et 200 caractères.';
    } elseif (!in_array($category, $validCategories, true)) {
        $formError = 'Catégorie invalide.';
    } elseif (strlen($description) < 20) {
        $formError = 'La description doit contenir au moins 20 caractères.';
    } else {
        try {
            $ref = 'ANR-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
            Database::query(
                'INSERT INTO ' . DB_PREFIX . 'dossiers
                 (user_id, reference, title, category, description, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, "submitted", NOW(), NOW())',
                [$user['id'], $ref, $title, $category, $description]
            );
            $memberName = trim((string) ($user['first_name'] ?? ''));
            $memberName = $memberName !== '' ? $memberName : 'membre';
            Mailer::sendDossierSubmitted($user['email'], $memberName, $ref, $title);
            Mailer::sendInternalDossierNotification($user['email'], $memberName, $ref, $title, $category);
            $formSuccess = 'Votre dossier <strong>' . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') . '</strong> a été déposé avec succès. Vous recevrez une réponse sous 5 à 10 jours ouvrés.';
        } catch (Exception $e) {
            $formError = 'Une erreur est survenue lors du dépôt. Veuillez réessayer.';
        }
    }
}

/* ── Filtres & récupération ───────────────────────────────────── */
$filterStatus = Security::sanitize($_GET['status'] ?? 'all');
$validFilters = ['all', 'draft', 'submitted', 'under_review', 'awaiting_info', 'approved', 'rejected', 'closed'];
if (!in_array($filterStatus, $validFilters, true)) $filterStatus = 'all';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

try {
    $where  = $filterStatus === 'all' ? 'user_id = ?' : 'user_id = ? AND status = ?';
    $params = $filterStatus === 'all' ? [$user['id']] : [$user['id'], $filterStatus];

    $total   = (int) Database::query('SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE ' . $where, $params)->fetchColumn();
    $dossiers = Database::query(
        'SELECT * FROM ' . DB_PREFIX . 'dossiers WHERE ' . $where . ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset,
        $params
    )->fetchAll();
    $pages = max(1, (int) ceil($total / $perPage));
} catch (Exception $e) {
    $total = 0; $dossiers = []; $pages = 1;
}

/* ── Compteurs par statut ────────────────────────────────────── */
$counts = [];
try {
    $rows = Database::query(
        'SELECT status, COUNT(*) as n FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ? GROUP BY status',
        [$user['id']]
    )->fetchAll();
    foreach ($rows as $r) $counts[$r['status']] = (int) $r['n'];
} catch (Exception $e) {}
$countAll = array_sum($counts);

/* ── Helpers ─────────────────────────────────────────────────── */
$statusLabels = [
    'draft'        => 'Brouillon',
    'submitted'    => 'Soumis',
    'under_review' => 'En examen',
    'awaiting_info'=> 'Informations attendues',
    'approved'     => 'Approuvé',
    'rejected'     => 'Rejeté',
    'closed'       => 'Clôturé',
];
$statusClass = [
    'draft'        => 'inactive',
    'submitted'    => 'pending',
    'under_review' => 'pending',
    'awaiting_info'=> 'pending',
    'approved'     => 'active',
    'rejected'     => 'rejected',
    'closed'       => 'inactive',
];
$categoryLabels = [
    'domaine'     => 'Régulation de domaine',
    'protection'  => 'Protection des internautes',
    'agrement'    => 'Agrément professionnel',
    'rgpd'        => 'RGPD / Données personnelles',
    'signalement' => 'Signalement prioritaire',
    'autre'       => 'Autre demande',
];

$pageTitle = 'Mes dossiers — ANRDI';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard">

  <!-- ── Sidebar ───────────────────────────────────────────── -->
  <aside class="sidebar">
    <nav class="sidebar-nav">
      <span class="sidebar-group-title">Navigation</span>
      <a href="/membre/dashboard.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="/membre/dossiers.php" class="sidebar-link sidebar-link--active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Mes dossiers
      </a>
      <a href="/membre/profile.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Mon profil
      </a>
      <hr class="sidebar-divider">
      <span class="sidebar-group-title">Espaces</span>
      <a href="<?= URL_PRO ?>" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Espace Pro
      </a>
      <a href="/pages/contact.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Contacter l'ANRDI
      </a>
      <hr class="sidebar-divider">
      <a href="/logout.php" class="sidebar-link" style="color:var(--c-error);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </nav>
  </aside>

  <!-- ── Contenu ───────────────────────────────────────────── -->
  <div class="dashboard-content">

    <!-- En-tête -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
      <div>
        <h1 style="font-family:'Satoshi',sans-serif;font-size:1.625rem;font-weight:800;color:var(--c-navy);letter-spacing:-.03em;">Mes dossiers</h1>
        <p style="color:var(--c-gray-500);margin-top:.25rem;font-size:.9375rem;"><?= $countAll ?> dossier<?= $countAll !== 1 ? 's' : '' ?> au total</p>
      </div>
      <button onclick="document.getElementById('modal-new').style.display='flex'" class="btn btn--primary btn--sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Déposer un dossier
      </button>
    </div>

    <!-- Alertes formulaire -->
    <?php if ($formSuccess): ?>
    <div class="alert alert--success" style="margin-bottom:1.5rem;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <div><?= $formSuccess ?></div>
    </div>
    <?php elseif ($formError): ?>
    <div class="alert alert--error" style="margin-bottom:1.5rem;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>

    <!-- Filtres par statut -->
    <div style="display:flex;gap:.375rem;flex-wrap:wrap;margin-bottom:1.5rem;">
      <?php
      $filterDefs = [
        'all'          => ['Tous', $countAll],
        'submitted'    => ['Soumis', $counts['submitted'] ?? 0],
        'under_review' => ['En examen', $counts['under_review'] ?? 0],
        'awaiting_info'=> ['Informations attendues', $counts['awaiting_info'] ?? 0],
        'approved'     => ['Approuvés', $counts['approved'] ?? 0],
        'rejected'     => ['Rejetés', $counts['rejected'] ?? 0],
        'closed'       => ['Clôturés', $counts['closed'] ?? 0],
      ];
      foreach ($filterDefs as $fKey => [$fLabel, $fCount]):
        $active = $filterStatus === $fKey;
      ?>
      <a href="?status=<?= $fKey ?>"
         style="display:inline-flex;align-items:center;gap:.4375rem;padding:.375rem .875rem;font-size:.8125rem;font-weight:<?= $active ? '700' : '500' ?>;border-radius:var(--r-full);text-decoration:none;border:1.5px solid <?= $active ? 'var(--c-blue)' : 'var(--c-gray-200)' ?>;background:<?= $active ? 'var(--c-blue-pale)' : 'var(--c-white)' ?>;color:<?= $active ? 'var(--c-blue)' : 'var(--c-gray-600)' ?>;transition:all .1s;">
        <?= htmlspecialchars($fLabel, ENT_QUOTES, 'UTF-8') ?>
        <span style="background:<?= $active ? 'var(--c-blue)' : 'var(--c-gray-100)' ?>;color:<?= $active ? '#fff' : 'var(--c-gray-500)' ?>;font-size:.68rem;font-weight:700;padding:.125rem .4375rem;border-radius:99px;"><?= $fCount ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Tableau des dossiers -->
    <?php if (empty($dossiers)): ?>
    <div style="background:var(--c-white);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:4rem 2rem;text-align:center;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;color:var(--c-gray-300);margin:0 auto 1rem;display:block;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <p style="color:var(--c-gray-400);font-size:.9375rem;margin-bottom:1.25rem;">
        <?= $filterStatus === 'all' ? 'Aucun dossier déposé pour l\'instant.' : 'Aucun dossier avec ce statut.' ?>
      </p>
      <?php if ($filterStatus === 'all'): ?>
      <button onclick="document.getElementById('modal-new').style.display='flex'" class="btn btn--primary btn--sm">Déposer mon premier dossier</button>
      <?php else: ?>
      <a href="?" class="btn btn--ghost btn--sm">Voir tous les dossiers</a>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Objet</th>
            <th>Catégorie</th>
            <th>Statut</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dossiers as $d):
            $sc = $statusClass[$d['status']] ?? 'inactive';
            $sl = $statusLabels[$d['status']] ?? ucfirst($d['status']);
            $cl = $categoryLabels[$d['category'] ?? ''] ?? ($d['category'] ?? '—');
          ?>
          <tr>
            <td>
              <code style="font-size:.8rem;font-family:'JetBrains Mono','Fira Code',monospace;color:var(--c-blue);background:var(--c-blue-pale);padding:.15rem .4rem;border-radius:3px;">
                <?= htmlspecialchars($d['reference'], ENT_QUOTES, 'UTF-8') ?>
              </code>
            </td>
            <td style="font-weight:500;color:var(--c-navy);max-width:280px;">
              <?= htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td style="font-size:.8125rem;color:var(--c-gray-500);">
              <?= htmlspecialchars($cl, ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td><span class="status status--<?= $sc ?>"><?= $sl ?></span></td>
            <td style="color:var(--c-gray-400);font-size:.8125rem;white-space:nowrap;">
              <?= date('d/m/Y', strtotime($d['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1.25rem;flex-wrap:wrap;gap:.75rem;">
      <p style="font-size:.8125rem;color:var(--c-gray-400);">
        Page <?= $page ?> sur <?= $pages ?> — <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
      </p>
      <div style="display:flex;gap:.375rem;">
        <?php if ($page > 1): ?>
        <a href="?status=<?= $filterStatus ?>&page=<?= $page - 1 ?>" class="btn btn--ghost btn--sm">← Précédent</a>
        <?php endif; ?>
        <?php if ($page < $pages): ?>
        <a href="?status=<?= $filterStatus ?>&page=<?= $page + 1 ?>" class="btn btn--ghost btn--sm">Suivant →</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Info délais -->
    <div style="margin-top:1.5rem;padding:1.125rem 1.375rem;background:var(--c-blue-pale);border:1px solid var(--c-blue-pale2);border-radius:var(--r-xl);display:flex;gap:.875rem;align-items:flex-start;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:var(--c-blue);flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div style="font-size:.875rem;color:#1E3A8A;line-height:1.65;">
        <strong>Délai de traitement :</strong> Les dossiers soumis sont traités sous <strong>5 à 10 jours ouvrés</strong>.
        Pour toute urgence, contactez-nous via <a href="/pages/contact.php" style="color:var(--c-blue);">le formulaire de contact</a>.
      </div>
    </div>

  </div><!-- /.dashboard-content -->
</div><!-- /.dashboard -->

<!-- ══ Modal — Nouveau dossier ═══════════════════════════════ -->
<div id="modal-new" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(8,21,40,.55);align-items:center;justify-content:center;padding:1.5rem;" onclick="if(event.target===this)this.style.display='none'">
  <div style="background:var(--c-white);border-radius:var(--r-xl);width:min(580px,100%);max-height:90vh;overflow-y:auto;box-shadow:var(--sh-xl);">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:1.5rem 1.75rem;border-bottom:1px solid var(--c-gray-100);">
      <h2 style="font-family:'Satoshi',sans-serif;font-size:1.25rem;font-weight:800;color:var(--c-navy);letter-spacing:-.025em;">Déposer un dossier</h2>
      <button onclick="document.getElementById('modal-new').style.display='none'" style="background:none;border:none;cursor:pointer;color:var(--c-gray-400);padding:.25rem;border-radius:var(--r-md);" aria-label="Fermer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form method="POST" action="/membre/dossiers.php" style="padding:1.75rem;">
      <input type="hidden" name="action" value="new_dossier">
      <?= Security::csrfField() ?>
      <?= Security::honeypotField() ?>

      <div class="form-group">
        <label class="form-label form-label--required" for="dossier-title">Objet du dossier</label>
        <input type="text" id="dossier-title" name="title" class="form-input"
               placeholder="Ex : Demande d'arbitrage pour le domaine exemple.fr"
               maxlength="200" required>
        <span class="form-hint">Entre 5 et 200 caractères.</span>
      </div>

      <div class="form-group">
        <label class="form-label form-label--required" for="dossier-category">Catégorie</label>
        <select id="dossier-category" name="category" class="form-select" required>
          <option value="" disabled selected>Sélectionner une catégorie</option>
          <?php foreach ($categoryLabels as $val => $lbl): ?>
          <option value="<?= $val ?>"><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label form-label--required" for="dossier-desc">Description de la demande</label>
        <textarea id="dossier-desc" name="description" class="form-textarea"
                  placeholder="Décrivez votre demande en détail : contexte, éléments factuels, pièces disponibles…"
                  rows="5" minlength="20" required></textarea>
        <span class="form-hint">Minimum 20 caractères. Soyez aussi précis que possible.</span>
      </div>

      <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-top:.75rem;border-top:1px solid var(--c-gray-100);">
        <button type="button" onclick="document.getElementById('modal-new').style.display='none'" class="btn btn--ghost">Annuler</button>
        <button type="submit" class="btn btn--primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 20-7z"/></svg>
          Déposer le dossier
        </button>
      </div>
    </form>

  </div>
</div>

<?php if ($formError && isset($_POST['action'])): ?>
<script>document.getElementById('modal-new').style.display='flex';</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
