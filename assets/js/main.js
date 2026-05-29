/**
 * ANRDI — JavaScript principal v2
 * Vanilla JS · Sans dépendance externe
 */
'use strict';

// ── Utilitaires ──────────────────────────────────────────────────────────────
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
const debounce = (fn, ms = 200) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
const escapeHtml = str => {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str)));
  return d.innerHTML;
};

// ── Anti Self-XSS ────────────────────────────────────────────────────────────
(function selfXssWarning() {
  const s = [
    console.clear(),
    '%c⛔ STOP — Console réservée aux développeurs.',
    'color:#FF3B30;font-size:28px;font-weight:900;',
  ];
  console.log(...s);
  console.log('%cSi quelqu\'un vous demande de coller du code ici → fermez immédiatement.', 'color:#FF3B30;font-size:13px;');
})();

// ── Header scroll ────────────────────────────────────────────────────────────
(function initHeader() {
  const h = $('#site-header');
  if (!h) return;
  const onScroll = debounce(() => h.classList.toggle('scrolled', window.scrollY > 20), 40);
  window.addEventListener('scroll', onScroll, { passive: true });
})();

// ── Navigation mobile ─────────────────────────────────────────────────────────
(function initMobileNav() {
  const burger = $('#nav-burger');
  const nav    = $('#header-nav');
  if (!burger || !nav) return;

  const iBurger = burger.querySelector('.icon-burger');
  const iClose  = burger.querySelector('.icon-close');

  const toggle = (force) => {
    const open = typeof force === 'boolean' ? force : !nav.classList.contains('open');
    nav.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', String(open));
    if (iBurger) iBurger.hidden = open;
    if (iClose)  iClose.hidden  = !open;
    document.body.style.overflow = open ? 'hidden' : '';
  };

  burger.addEventListener('click', () => toggle());
  $$('.nav-link', nav).forEach(l => l.addEventListener('click', () => toggle(false)));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') toggle(false); });
})();

// ── Dropdown utilisateur ─────────────────────────────────────────────────────
(function initUserDropdown() {
  const wrapper  = $('#user-dropdown-trigger');
  const btn      = wrapper?.querySelector('.btn-avatar');
  const dropdown = wrapper?.querySelector('.user-dropdown');
  if (!wrapper || !dropdown) return;

  const open  = () => { dropdown.classList.add('open'); btn?.setAttribute('aria-expanded', 'true'); };
  const close = () => { dropdown.classList.remove('open'); btn?.setAttribute('aria-expanded', 'false'); };
  const toggle = () => dropdown.classList.contains('open') ? close() : open();

  btn?.addEventListener('click', e => { e.stopPropagation(); toggle(); });
  document.addEventListener('click', e => { if (!wrapper.contains(e.target)) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();

// ── Scroll animations ─────────────────────────────────────────────────────────
(function initScrollAnim() {
  if (!('IntersectionObserver' in window)) return;
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('anim-up');
        obs.unobserve(e.target);
      }
    });
  }, { threshold: .1, rootMargin: '0px 0px -40px 0px' });

  $$('.card, .kpi-card, .section-header').forEach(el => {
    el.style.opacity = '0';
    obs.observe(el);
  });
})();

// ── Toggle mot de passe ───────────────────────────────────────────────────────
(function initPasswordToggle() {
  $$('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const wrapper = btn.closest('.input-wrapper, .auth-input-wrapper');
      const input = wrapper?.querySelector('input');
      if (!input) return;
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.querySelector('.icon-eye')?.classList.toggle('hidden', !isText);
      btn.querySelector('.icon-eye-off')?.classList.toggle('hidden', isText);
      btn.setAttribute('aria-label', isText ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
    });
  });

  // Désactiver clic-droit sur les champs password
  $$('input[type="password"]').forEach(i => i.addEventListener('contextmenu', e => e.preventDefault()));
})();

// ── Force du mot de passe ─────────────────────────────────────────────────────
(function initPasswordStrength() {
  $$('input[data-strength]').forEach(input => {
    const bar   = document.getElementById(input.dataset.strength);
    const label = bar?.closest('.strength-wrapper')?.querySelector('.strength-label');
    if (!bar) return;

    const getScore = pwd => {
      let s = 0;
      if (pwd.length >= 8)  s++;
      if (pwd.length >= 12) s++;
      if (/[A-Z]/.test(pwd)) s++;
      if (/[a-z]/.test(pwd)) s++;
      if (/[0-9]/.test(pwd)) s++;
      if (/[\W_]/.test(pwd)) s++;
      return Math.min(4, Math.floor(s / 1.5));
    };

    const colors = ['', '#EF4444', '#F59E0B', '#10B981', '#059669'];
    const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];

    input.addEventListener('input', () => {
      const score = getScore(input.value);
      bar.style.cssText = `width:${score * 25}%;background:${colors[score]};height:100%;border-radius:4px;transition:width .3s,background .3s;`;
      if (label) label.textContent = labels[score] || '';
    });
  });
})();

// ── Validation email pro ──────────────────────────────────────────────────────
(function initProEmailValidation() {
  const input = $('#pro-email-input');
  const hint  = $('#pro-email-hint');
  if (!input || !hint) return;

  const forbidden = [
    'gmail.com','googlemail.com','outlook.com','outlook.fr','hotmail.com','hotmail.fr',
    'live.com','live.fr','msn.com','yahoo.com','yahoo.fr','ymail.com','icloud.com',
    'me.com','mac.com','aol.com','protonmail.com','proton.me','laposte.net','orange.fr',
    'sfr.fr','free.fr','wanadoo.fr','bbox.fr','gmx.com','gmx.fr','mail.com','zoho.com',
  ];

  input.addEventListener('blur', () => {
    const domain = (input.value.split('@')[1] || '').toLowerCase();
    if (!domain) return;
    if (forbidden.includes(domain)) {
      hint.textContent = '⚠ Les adresses personnelles ne sont pas autorisées pour l\'espace professionnel.';
      hint.className = 'form-error';
      input.classList.add('is-error');
      input.classList.remove('is-success');
    } else {
      hint.textContent = '✓ Adresse professionnelle valide.';
      hint.style.color = 'var(--c-success)';
      hint.className = 'form-hint';
      input.classList.remove('is-error');
      input.classList.add('is-success');
    }
  });
})();

// ── Formulaires AJAX ──────────────────────────────────────────────────────────
(function initAjaxForms() {
  $$('form[data-ajax]').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const btn   = form.querySelector('[type="submit"]');
      const orig  = btn?.innerHTML;
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="loader" aria-hidden="true"></span> Chargement…'; }

      try {
        const res  = await fetch(form.action || location.href, {
          method: 'POST', body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        showAlert(form, data);
        if (data.redirect && res.ok) setTimeout(() => location.href = data.redirect, 1200);
      } catch {
        showAlert(form, { error: 'Erreur inattendue. Veuillez réessayer.' });
      } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = orig; }
      }
    });
  });

  function showAlert(form, data) {
    let el = form.querySelector('.form-ajax-alert');
    if (!el) { el = document.createElement('div'); el.className = 'form-ajax-alert'; form.prepend(el); }
    const isError = !!data.error;
    el.className = `form-ajax-alert alert alert--${isError ? 'error' : 'success'}`;
    const icon = isError
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
      : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>';
    el.innerHTML = icon + `<span>${escapeHtml(data.error || data.success || '')}</span>`;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
})();

// ── Cookie consent (bouton footer) ────────────────────────────────────────────
(function initCookieConsent() {
  const btn = $('#rgpd-consent-btn');
  if (btn) btn.addEventListener('click', () => window.dispatchEvent(new CustomEvent('anrdi:openCookieManager')));
})();

// ── Export global ─────────────────────────────────────────────────────────────
window.ANRDI = { escapeHtml, version: '2.0.0' };
