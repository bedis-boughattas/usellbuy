/* MEMBRES:
    BOUGHATTASS BADIS
    GHODHBANI MOHAMED SAJED
    AZZOUZ MALEK
*/
// JEU MEMORY - Gestion des cartes et des paires


// Tableau des icônes Font Awesome représentant des éléments de marketplace (8 paires)
const GAME_ICONS = [
    '<i class="fa-solid fa-box"></i>',           // 📦 Package
    '<i class="fa-solid fa-credit-card"></i>',   // 💳 Credit Card
    '<i class="fa-solid fa-cart-shopping"></i>', // 🛒 Shopping Cart
    '<i class="fa-solid fa-truck"></i>',         // 🚚 Delivery Truck
    '<i class="fa-solid fa-gift"></i>',          // 🎁 Gift
    '<i class="fa-solid fa-mobile-screen-button"></i>', // 📱 Mobile
    '<i class="fa-solid fa-laptop"></i>',        // 💻 Laptop
    '<i class="fa-solid fa-tag"></i>'            // 🏷️ Price Tag
];

// Variables d'état du jeu
let memFlipped = [];      // Cartes actuellement retournées
let memMatched = 0;       // Nombre de paires trouvées
let memTries = 0;         // Nombre de tentatives
let memLocked = false;    // Verrou pour éviter les clics multiples pendant l'animation

/**
 * Mélange un tableau (algorithme Fisher-Yates)
 * @param {Array} arr - Tableau à mélanger
 * @returns {Array} Nouveau tableau mélangé
 */
function shuffle(arr) {
    let a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

/**
 * Initialise ou réinitialise le jeu Memory
 * Crée dynamiquement la grille 4x4 avec addEventListener sur chaque carte
 */
function initMemory() {
    // Réinitialisation des variables d'état
    memFlipped = [];
    memMatched = 0;
    memTries = 0;
    memLocked = false;
    
    // Mise à jour de l'affichage du score
    document.getElementById("mem-tries").textContent = "0";
    document.getElementById("mem-pairs").textContent = "0";
    document.getElementById("mem-msg").innerHTML = "";
    
    // Création du deck : chaque icône apparaît 2 fois, puis mélange
    let deck = shuffle([...GAME_ICONS, ...GAME_ICONS]);
    let grid = document.getElementById("memory-grid");
    grid.innerHTML = "";
    
    // Génération dynamique des cartes dans le DOM
    deck.forEach((iconHtml, index) => {
        let card = document.createElement("div");
        card.className = "mem-card";
        card.dataset.icon = iconHtml;
        card.dataset.index = index;
        
        // Question mark icon (face cachée)
        let questionIcon = document.createElement("i");
        questionIcon.className = "fa-solid fa-circle-question";
        
        // Icône Font Awesome (face visible quand retournée)
        let iconSpan = document.createElement("span");
        iconSpan.className = "card-icon";
        iconSpan.innerHTML = iconHtml;
        
        card.appendChild(questionIcon);
        card.appendChild(iconSpan);
        
        // addEventListener pour la gestion du clic sur chaque carte
        card.addEventListener("click", () => onMemCardClick(card));
        grid.appendChild(card);
    });
}

/**
 * Gère le clic sur une carte du Memory
 * @param {HTMLElement} card - La carte cliquée
 */
function onMemCardClick(card) {
    // Conditions d'ignorance : jeu verrouillé, carte déjà retournée ou déjà appariée
    if (memLocked) return;
    if (card.classList.contains("flipped")) return;
    if (card.classList.contains("matched")) return;
    if (memFlipped.length >= 2) return;
    
    // Retourner la carte - cache l'icône question et montre l'icône Font Awesome
    card.classList.add("flipped");
    memFlipped.push(card);
    
    // Si deux cartes sont retournées, vérifier la paire
    if (memFlipped.length === 2) {
        memTries++;
        document.getElementById("mem-tries").textContent = memTries;
        memLocked = true;
        
        // Vérification si les deux cartes ont le même contenu HTML (même icône)
        if (memFlipped[0].dataset.icon === memFlipped[1].dataset.icon) {
            // Paire trouvée : marquer comme "matched"
            memFlipped[0].classList.add("matched");
            memFlipped[1].classList.add("matched");
            memMatched++;
            document.getElementById("mem-pairs").textContent = memMatched;
            memFlipped = [];
            memLocked = false;
            
            // Vérifier la victoire (8 paires = jeu terminé)
            if (memMatched === 8) {
                document.getElementById("mem-msg").innerHTML = '<i class="fa-solid fa-trophy"></i> Bravo ! Terminé en ' + memTries + ' tentatives !';
            }
        } else {
            // Pas de paire : retourner les cartes après 900ms
            setTimeout(() => {
                memFlipped[0].classList.remove("flipped");
                memFlipped[1].classList.remove("flipped");
                memFlipped = [];
                memLocked = false;
            }, 900);
        }
    }
}

// Bouton pour réinitialiser le jeu Memory
document.getElementById("btn-mem-reset").addEventListener("click", initMemory);

// Démarrer le jeu Memory
initMemory();


// PROPAGATION DES ÉVÉNEMENTS - Event Bubbling et stopPropagation()

let logCount = 0;  // Compteur pour gérer l'affichage du journal

/**
 * Ajoute une entrée dans le journal des événements
 * @param {string} message - Message à afficher
 * @param {string} type - Classe CSS pour la couleur (outer, zone, btn, stop, mouse, key)
 */
function logEvent(message, type) {
    let log = document.getElementById("ev-log");
    // Supprimer le message d'attente au premier événement
    if (logCount === 0) {
        log.innerHTML = '<span class="ev-log-clear" id="clear-log"><i class="fa-solid fa-trash-can"></i> Clear</span>';
        attachClearLog();
    }
    logCount++;
    let ts = new Date().toLocaleTimeString("en-EN");
    let entry = document.createElement("div");
    entry.className = "ev-log-entry " + (type || "");
    entry.innerHTML = "<i class='fa-regular fa-clock'></i> [" + ts + "] " + message;
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight; // Auto-scroll vers le bas
}

/**
 * Attache l'événement de suppression au bouton "Effacer"
 * Utilise stopPropagation() pour éviter que le clic ne remonte
 */
function attachClearLog() {
    let btn = document.getElementById("clear-log");
    if (btn) {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();  // Empêche la propagation vers les éléments parents
            document.getElementById("ev-log").innerHTML = '<span class="ev-log-clear" id="clear-log"><i class="fa-solid fa-trash-can"></i> Clear</span><div style="color:var(--gray);font-style:italic;"><i class="fa-solid fa-hourglass-half"></i> En attente d\'un événement…</div>';
            logCount = 0;
            attachClearLog();
        });
    }
}

attachClearLog();

// Récupération des éléments DOM pour la propagation
let evOuter = document.getElementById("ev-outer");      // Niveau 1 - DIV parent
let evZone = document.getElementById("ev-zone");        // Niveau 2 - Zone de jeu
let evBtnNormal = document.getElementById("ev-btn-normal"); // Niveau 3a - Bouton normal
let evBtnStop = document.getElementById("ev-btn-stop");     // Niveau 3b - Bouton avec stopPropagation

/**
 * Événement sur DIV parent (niveau 1)
 * Reçoit l'événement après bubbling depuis les enfants
 */
evOuter.addEventListener("click", () => {
    logEvent("DIV parent received the event (bubbling from the child)", "outer");
});

/**
 * Événement sur Zone de jeu (niveau 2)
 * Reçoit l'événement après bubbling depuis le bouton
 */
evZone.addEventListener("click", () => {
    logEvent("The play area has received the event (bubbling from the button)", "zone");
});

/**
 * Événement sur Bouton normal (niveau 3a)
 * Laisse l'événement remonter (bubbling normal)
 */
evBtnNormal.addEventListener("click", () => {
    logEvent("Clicking the NORMAL BUTTON — the event will be sent to Zone and then DIV", "btn");
});

/**
 * Événement sur Bouton stopPropagation (niveau 3b)
 * Utilise stopPropagation() pour bloquer la remontée de l'événement
 * L'événement ne remontera pas vers Zone ni DIV
 */
evBtnStop.addEventListener("click", (e) => {
    logEvent("STOP PROPAGATION BUTTON clicked — event BLOCKED here (stopPropagation))", "stop");
    e.stopPropagation();  // Bloque la propagation vers les éléments parents
});

//
// ÉVÉNEMENT DE MOUVEMENT DE SOURIS (mousemove)
//

let mouseTimer = null;

/**
 * Suivi de la position de la souris sur toute la page
 * Limitation des logs pour éviter le spam (1 log par seconde maximum)
 */
document.addEventListener("mousemove", (e) => {
    // Mise à jour de l'affichage de la position
    document.getElementById("mouse-x").textContent = e.clientX;
    document.getElementById("mouse-y").textContent = e.clientY;
    
    // Limitation des logs pour ne pas surcharger le journal
    if (!mouseTimer) {
        logEvent(`<i class="fa-solid fa-mouse-pointer"></i> Mouse position : position (${e.clientX}, ${e.clientY})`, "mouse");
        mouseTimer = setTimeout(() => {
            mouseTimer = null;
        }, 1000);
    }
});

// 
// ÉVÉNEMENT DE CLAVIER (keydown)
//

/**
 * Gestion des touches du clavier :
 * - ESPACE : Réinitialiser le jeu Memory
 * - K : Effacer le journal des événements
 */
document.addEventListener("keydown", (e) => {
    // Éviter les répétitions automatiques
    if (e.repeat) return;
    
    // Touche ESPACE : réinitialiser le jeu
    if (e.code === "Space") {
        e.preventDefault();  // Empêche le scroll de la page
        initMemory();
        logEvent(`<i class="fa-solid fa-keyboard"></i> Press [SPACE] key — Resets Memory game`, "key");
    }
    // Touche K : effacer le journal
    else if (e.key === "k" || e.key === "K") {
        e.preventDefault();
        document.getElementById("ev-log").innerHTML = '<span class="ev-log-clear" id="clear-log"><i class="fa-solid fa-trash-can"></i> Clear</span><div style="color:var(--gray);font-style:italic;"><i class="fa-solid fa-hourglass-half"></i> En attente d\'un événement…</div>';
        logCount = 0;
        attachClearLog();
        logEvent(`<i class="fa-solid fa-keyboard"></i> Press [K] key — Deleted journal`, "key");
    }
    // Log pour toutes les autres touches (limité pour éviter le spam)
    else if (e.key.length === 1 && !e.ctrlKey && !e.altKey) {
        logEvent(`<i class="fa-solid fa-keyboard"></i> Key pressed : [${e.key.toUpperCase()}]`, "key");
    }
});