/* MEMBRES:
    BOUGHATTASS BADIS
    GHODHBANI MOHAMED SAJED
    AZZOUZ MALEK
*/
/*
   PARTIE 4a —
   Met à jour la date ET l'heure chaque seconde
*/
 
/**
 * Met à jour la date et l'heure affichées dans la bannière
 * Appelée toutes les secondes via setInterval
 */
function majBanniere() {
  var maintenant = new Date();
 
  /* Formater la date en anglais*/
  var optionsDate = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
  var dateStr = maintenant.toLocaleDateString("en-EN", optionsDate);
 
  /* Formater l'heure : ex. "14:35:09" */
  var heureStr = maintenant.toLocaleTimeString("en-EN");
 
  /* Mettre à jour les deux copies de la bannière (pour le défilement continu) */
  document.getElementById("banner-date-1").textContent  = dateStr;
  document.getElementById("banner-time-1").textContent  = heureStr;
  document.getElementById("banner-date-2").textContent = dateStr;
  document.getElementById("banner-time-2").textContent = heureStr;
}
 
/* Appel immédiat pour éviter le délai d'1 seconde */
majBanniere();
/* Mise à jour chaque seconde */
setInterval(majBanniere, 1000);

/*
   PARTIE 4b 
   Rotation toutes les 3 secondes minimum
*/
 
/** Récupérer toutes les images et les données associées */
var slides     = document.querySelectorAll(".gallery-slide");
var totalSlides = slides.length;
var currentSlide = 0;        /* Index de la slide active */
var DURATION   = 4000;       /* Durée d'affichage : 4 secondes (> 3s requis) */
var timerSecs  = DURATION / 1000;
var timerRemaining = timerSecs;
var autoInterval;             /* Référence à l'intervalle auto */
var timerInterval;            /* Référence à l'intervalle du compteur */
 
/**
 * Affiche une slide spécifique
 * @param {number} index — index de la slide à afficher
 */
function postSlide(index) {
  /* Désactiver la slide courante */
  slides[currentSlide].classList.remove("active");
  dots[currentSlide].classList.remove("active");
 
  /* Normaliser l'index (bouclage) */
  currentSlide = (index + totalSlides) % totalSlides;
 
  /* Activer la nouvelle slide */
  slides[currentSlide].classList.add("active");
  dots[currentSlide].classList.add("active");
 
  /* Mettre à jour la légende */
  var img = slides[currentSlide];
  document.getElementById("gallery-caption-text").textContent = img.dataset.caption || "";
  document.getElementById("gallery-caption-sub").textContent  = img.dataset.sub || "";
 
  /* Réinitialiser le compteur de temps */
  resetTimer();
}
 
/**
 * Passe à la slide suivante (rotation automatique)
 */
function slideNext() {
  postSlide(currentSlide + 1);
}
 
/**
 * Passe à la slide précédente
 */
function slidePrev() {
  postSlide(currentSlide - 1);
}
 
/* ── Générer les indicateurs (dots) ── */
var dotsContainer = document.getElementById("gallery-dots");
var dots = [];
 
for (var i = 0; i < totalSlides; i++) {
  var dot = document.createElement("button");
  dot.className = "gallery-dot" + (i === 0 ? " active" : "");
  dot.dataset.index = i;
  /* addEventListener pour cliquer directement sur un dot */
  dot.addEventListener("click", (function(idx) {
    return function() {
      clearInterval(autoInterval);
      postSlide(idx);
      demarrerAuto();
    };
  })(i));
  dotsContainer.appendChild(dot);
  dots.push(dot);
}

/* ── Initialiser la légende de la première image ── */
document.getElementById("gallery-caption-text").textContent = slides[0].dataset.caption || "";
document.getElementById("gallery-caption-sub").textContent  = slides[0].dataset.sub     || "";
 
/* ── Boutons précédent / suivant ── */
document.getElementById("btn-prev").addEventListener("click", function() {
  clearInterval(autoInterval);
  slidePrev();
  demarrerAuto();
});
 
document.getElementById("btn-next").addEventListener("click", function() {
  clearInterval(autoInterval);
  slideNext();
  demarrerAuto();
});
 
/* ── Compteur de temps (barre de progression) ── */
function resetTimer() {
  timerRemaining = timerSecs;
  clearInterval(timerInterval);
  timerInterval = setInterval(function() {
    timerRemaining--;
    document.getElementById("timer-text").textContent = timerRemaining + "s";
    var pct = (timerRemaining / timerSecs) * 100;
    document.getElementById("timer-fill").style.width = pct + "%";
    if (timerRemaining <= 0) { clearInterval(timerInterval); }
  }, 1000);
}
 
/**
 * Démarre la rotation automatique toutes les DURATION ms
 */
function demarrerAuto() {
  autoInterval = setInterval(slideNext, DURATION);
}
 
/* Démarrer la rotation automatique et le compteur */
resetTimer();
demarrerAuto();

/* ============================================================
   PARTIE IV — Validation du formulaire Contact (JavaScript)
   ============================================================ */

/**
 * Affiche un message d'erreur sous un champ
 * @param {string} fieldId  — id du champ input
 * @param {string} errId    — id du span d'erreur
 * @param {string} message  — texte à afficher
 */
function ctSetError(fieldId, errId, message) {
  var field = document.getElementById(fieldId);
  var err   = document.getElementById(errId);
  if (field) { field.style.borderColor = 'var(--error)'; }
  if (err)   { err.textContent = message; err.style.display = 'block'; err.style.color = 'var(--error)'; }
}

/**
 * Efface le message d'erreur d'un champ
 * @param {string} fieldId
 * @param {string} errId
 */
function ctClearError(fieldId, errId) {
  var field = document.getElementById(fieldId);
  var err   = document.getElementById(errId);
  if (field) { field.style.borderColor = 'var(--valide)'; }
  if (err)   { err.textContent = ''; err.style.display = 'none'; }
}

/**
 * Valide le formulaire de contact avant envoi au serveur PHP.
 * Vérifie : champs obligatoires, longueur, format email, format téléphone.
 * @returns {boolean} false bloque l'envoi si une erreur est détectée
 */
function validateContactForm() {
  var valid = true;

  /* --- Prénom : obligatoire, min 2 lettres, lettres uniquement --- */
  var firstName = document.getElementById('ct-firstname').value.trim();
  var nameRegex = /^[a-zA-ZÀ-ÿ\s\-']{2,50}$/;
  if (firstName === '') {
    ctSetError('ct-firstname', 'err-firstname', 'First name is required.');
    valid = false;
  } else if (!nameRegex.test(firstName)) {
    ctSetError('ct-firstname', 'err-firstname', 'First name must contain only letters (min 2 characters).');
    valid = false;
  } else {
    ctClearError('ct-firstname', 'err-firstname');
  }

  /* --- Nom : obligatoire, min 2 lettres --- */
  var lastName = document.getElementById('ct-lastname').value.trim();
  if (lastName === '') {
    ctSetError('ct-lastname', 'err-lastname', 'Last name is required.');
    valid = false;
  } else if (!nameRegex.test(lastName)) {
    ctSetError('ct-lastname', 'err-lastname', 'Last name must contain only letters (min 2 characters).');
    valid = false;
  } else {
    ctClearError('ct-lastname', 'err-lastname');
  }

  /* --- Email : obligatoire, format valide --- */
  var email = document.getElementById('ct-email').value.trim();
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (email === '') {
    ctSetError('ct-email', 'err-email', 'Email address is required.');
    valid = false;
  } else if (!emailRegex.test(email)) {
    ctSetError('ct-email', 'err-email', 'Please enter a valid email address (e.g. user@example.com).');
    valid = false;
  } else {
    ctClearError('ct-email', 'err-email');
  }

  /* --- Téléphone : optionnel, mais si renseigné doit être valide --- */
  var phone = document.getElementById('ct-phone').value.trim();
  var phoneRegex = /^(\+?[0-9\s\-\(\)]{7,20})$/;
  if (phone !== '' && !phoneRegex.test(phone)) {
    ctSetError('ct-phone', 'err-phone', 'Invalid phone number format (e.g. +216 29 255 110).');
    valid = false;
  } else {
    ctClearError('ct-phone', 'err-phone');
  }

  /* --- Message : obligatoire, min 10 caractères --- */
  var message = document.getElementById('ct-message').value.trim();
  if (message === '') {
    ctSetError('ct-message', 'err-message', 'Message is required.');
    valid = false;
  } else if (message.length < 10) {
    ctSetError('ct-message', 'err-message', 'Message must be at least 10 characters long.');
    valid = false;
  } else if (message.length > 1000) {
    ctSetError('ct-message', 'err-message', 'Message must not exceed 1000 characters.');
    valid = false;
  } else {
    ctClearError('ct-message', 'err-message');
  }

  /* Faire défiler jusqu'au premier champ en erreur */
  if (!valid) {
    var firstError = document.querySelector('.error-msg[style*="block"]');
    if (firstError) { firstError.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
  }

  return valid; /* false = bloque l'envoi */
}
