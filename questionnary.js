// questionnaire.js — validation du formulaire avant envoi

// met à jour le compteur de caractères en temps réel
function updateCharCount() {
  var len = document.getElementById("q-commentaire").value.length; // récupère la longueur du texte saisi
  document.getElementById("char-num").textContent = len; // affiche ce nombre dans la page
}

// marque un champ en rouge et affiche son message d'erreur
function marquerErreur(groupId, errId) {
  var grp = document.getElementById(groupId); // cible le groupe du champ
  var err = document.getElementById(errId); // cible le message d'erreur
  if (grp) { grp.classList.add("field-error"); grp.classList.remove("field-ok"); } // passe le champ en rouge
  if (err) { err.classList.add("visible"); } // rend le message d'erreur visible
}

// marque un champ en vert et cache son message d'erreur
function marquerOk(groupId, errId) {
  var grp = document.getElementById(groupId); // cible le groupe du champ
  var err = document.getElementById(errId); // cible le message d'erreur
  if (grp) { grp.classList.remove("field-error"); grp.classList.add("field-ok"); } // passe le champ en vert
  if (err) { err.classList.remove("visible"); } // cache le message d'erreur
}

// nom : minimum 3 lettres, pas de chiffres
function validerNom() {
  var val = document.getElementById("q-nom").value.trim(); // récupère la valeur saisie sans espaces
  var regex = /^[a-zA-Z ]*$/; // autorise uniquement les lettres et espaces
  if (val.length < 3 || !regex.test(val)  ) { // vérifie la longueur et le format
    marquerErreur("grp-nom", "err-nom"); // champ invalide : affiche l'erreur
    return false; // arrête et retourne false
  }
  marquerOk("grp-nom", "err-nom"); // champ valide : affiche le succès
  return true; // retourne true
}

// email : doit respecter le format standard
function validerEmail() {
  var val  = document.getElementById("q-email").value.trim(); // récupère l'email saisi
  var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // vérifie le format email classique
  if (!regex.test(val)) { // si le format ne correspond pas
    marquerErreur("grp-email", "err-email"); // champ invalide : affiche l'erreur
    return false; // arrête et retourne false
  }
  marquerOk("grp-email", "err-email"); // champ valide : affiche le succès
  return true; // retourne true
}

// satisfaction : une option radio doit être cochée
function validerSatisfaction() {
  var selectionne = document.querySelector('input[name="satisfaction"]:checked'); // cherche si une option est cochée
  if (!selectionne) { // si aucune option n'est cochée
    marquerErreur("grp-satisfaction", "err-satisfaction"); // affiche l'erreur
    return false; // arrête et retourne false
  }
  marquerOk("grp-satisfaction", "err-satisfaction"); // valide le champ
  return true; // retourne true
}

// note : au moins une étoile doit être choisie
function validerNote() {
  var selectionne = document.querySelector('input[name="note"]:checked'); // cherche si une étoile est cochée
  if (!selectionne) { // si aucune étoile n'est sélectionnée
    marquerErreur("grp-note", "err-note"); // affiche l'erreur
    return false; // arrête et retourne false
  }
  marquerOk("grp-note", "err-note"); // valide le champ
  return true; // retourne true
}

// commentaire : minimum 20 caractères
function validerCommentaire() {
  var val = document.getElementById("q-commentaire").value.trim(); // récupère le texte saisi
  if (val.length < 20) { // si le texte est trop court
    marquerErreur("grp-commentaire", "err-commentaire"); // affiche l'erreur
    return false; // arrête et retourne false
  }
  marquerOk("grp-commentaire", "err-commentaire"); // valide le champ
  return true; // retourne true
}

// validation en direct quand l'utilisateur quitte un champ
document.getElementById("q-nom").addEventListener("blur", validerNom); // valide le nom à la sortie du champ
document.getElementById("q-email").addEventListener("blur", validerEmail); // valide l'email à la sortie du champ
document.getElementById("q-commentaire").addEventListener("blur", validerCommentaire); // valide le commentaire à la sortie du champ

// écoute la soumission du formulaire
document.getElementById("quiz-form").addEventListener("submit", function(e) {
  e.preventDefault(); // bloque le rechargement de la page

  // lance la validation de chaque champ
  var v1 = validerNom(); // valide le nom
  var v2 = validerEmail(); // valide l'email
  var v3 = validerSatisfaction(); // valide la satisfaction
  var v4 = validerNote(); // valide la note
  var v5 = validerCommentaire(); // valide le commentaire

  if (v1 && v2 && v3 && v4 && v5) { // si tout est valide
    document.getElementById("quiz-form").style.display = "none"; // cache le formulaire
    document.getElementById("success-panel").classList.add("visible"); // affiche le message de succès
    document.getElementById("q-card").scrollIntoView({ behavior: "smooth" }); // remonte en douceur vers le haut
  } else { // si au moins un champ est invalide
    var firstError = document.querySelector(".field-error"); // cherche le premier champ en erreur
    if (firstError) { firstError.scrollIntoView({ behavior: "smooth", block: "center" }); } // scrolle vers ce champ
  }
});