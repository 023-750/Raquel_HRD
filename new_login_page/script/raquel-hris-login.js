'use strict';

/**
 * raquel-hris-login.js
 * Raquel Pawnshop HRIS — Login Page Scripts
 * Extracted & modernised from raquel-hris-login.html
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ─────────────────────────────────────────
     Toggle password visibility
     Called via onclick="tpw('inputId','iconId')"
  ───────────────────────────────────────── */
  function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
      input.type     = 'text';
      icon.className = 'ti ti-eye-off';
    } else {
      input.type     = 'password';
      icon.className = 'ti ti-eye';
    }
  }

  // Expose to inline onclick handlers in HTML
  window.tpw = togglePasswordVisibility;

  /* ─────────────────────────────────────────
     Panel switching — Show ESS portal
  ───────────────────────────────────────── */
  window.showESS = function () {
    document.getElementById('track').classList.add('show-ess');
    document.body.classList.add('show-ess');
  };

  /* ─────────────────────────────────────────
     Panel switching — Show HRIS portal
  ───────────────────────────────────────── */
  window.showHRIS = function () {
    document.getElementById('track').classList.remove('show-ess');
    document.body.classList.remove('show-ess');
  };

  /* ─────────────────────────────────────────
     HRIS form — client-side validation
  ───────────────────────────────────────── */
  const hrisForm = document.getElementById('hrisForm');
  if (hrisForm) {
    hrisForm.addEventListener('submit', function (e) {
      const username = document.getElementById('hris-username').value.trim();
      const password = document.getElementById('hp').value;

      if (!username || !password) {
        e.preventDefault();
        const errBox = document.getElementById('hrisError');
        document.getElementById('hrisErrorMsg').textContent =
          'Please fill in both username and password.';
        errBox.style.display = 'flex';
        errBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  }

  /* ─────────────────────────────────────────
     ESS form — client-side validation (added)
  ───────────────────────────────────────── */
  const essForm = document.getElementById('essForm');
  if (essForm) {
    essForm.addEventListener('submit', function (e) {
      const username = document.getElementById('ess-username').value.trim();
      const password = document.getElementById('ep').value;

      if (!username || !password) {
        e.preventDefault();
        const errBox = document.getElementById('essError');
        document.getElementById('essErrorMsg').textContent =
          'Please fill in both username and password.';
        errBox.style.display = 'flex';
        errBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  }

});
