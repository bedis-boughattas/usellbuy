/* MEMBRES:
    BOUGHATTASS BADIS
    GHODHBANI MOHAMED SAJED
    AZZOUZ MALEK
*/
/*
 *1a.
 * Constructeur Product
 * Crée un objet produit avec toutes ses propriétés
 */
function Product(id, name, description, price, country, category, image) {
  this.id          = id;
  this.name        = name;
  this.description = description;
  this.price       = price;
  this.country     = country;
  this.category    = category;
  this.image       = image;
}
 
/*
  *1b. COLLECTIONS — Tableau d'objets initialisé
*/
/** Tableau principal contenant des instances de Product */
var products = [
  new Product(1,  "Polaroid Camera",      "Instant film camera — fun and retro.",         89.99,   "Italy",   "Electronics", "https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80"),
  new Product(2,  "Wireless Headphones",  "Amazing sound quality, long battery life.",    129.99,  "USA",     "Electronics", "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80"),
  new Product(3,  "Smartwatch Series 9",  "Sleek, functional and stylish on the wrist.",  249.99,  "Germany", "Electronics", "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80"),
  new Product(4,  "Handcrafted Chair",    "Super comfortable and solid build.",           199.99,  "Sweden",  "Furniture",   "https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=600&q=80"),
  new Product(5,  "Gaming Laptop",        "Runs all games smoothly, high performance.",   1299.99, "Japan",   "Electronics", "https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=600&q=80"),
  new Product(6,  "Coffee Beans",         "Café quality coffee from the comfort of home.",399.99,  "Italy",   "Food",        "https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?auto=format&fit=crop&w=600&q=80"),
  new Product(7,  "Designer Sneakers",    "Very comfortable and trendy street style.",    199.99,  "Italy",   "Fashion",     "https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?auto=format&fit=crop&w=600&q=80"),
  new Product(8,  "Electric Guitar",      "Incredible sound, perfect for all levels.",    599.99,  "USA",     "Music",       "https://images.unsplash.com/photo-1564186763535-ebb21ef5277f?auto=format&fit=crop&w=600&q=80")
];
 
/**Compteur global pour les IDs des nouveaux produits */
var nextId = products.length + 1;
 
/* 
  *1c. GÉNÉRATION DYNAMIQUE DU TABLEAU HTML (DOM)
*/
 
/**
 * Affiche tous les produits dans le tableau HTML #product-tbody
 * Vide d'abord le contenu existant, puis recrée les lignes
  @param {Product[]} liste // tableau de produits à afficher (par défaut : products)
 */
function displaytable(liste) {
  /* Si aucun paramètre, on affiche le tableau global */
  if (!liste) { liste = products; }
 
  var tbody = document.getElementById("product-tbody");
  if (!tbody) { return; } /* Sécurité : élément absent */
 
  tbody.innerHTML = ""; /* Vider les anciennes lignes */
 
  /* Parcourir chaque produit et créer une ligne <tr> */
  liste.forEach(function(p) {
    var tr = document.createElement("tr");
    tr.innerHTML =
      "<td>" + p.id + "</td>" +
      "<td><strong>" + p.name + "</strong></td>" +
      "<td>" + p.description + "</td>" +
      "<td><span class='price-badge'>$" + p.price.toFixed(2) + "</span></td>" +
      "<td>" + p.country + "</td>" +
      "<td><span class='cat-badge'>" + p.category + "</span></td>" +
      "<td><button class='del-btn' onclick='deleteProduct(" + p.id + ")'>✕</button></td>";
    tbody.appendChild(tr);
  });
 
  /* Mettre à jour le compteur */
  var counter = document.getElementById("product-count");
  if (counter) { counter.textContent = liste.length; }
}
 
/*
  *1d
*/
 
/**
 * FONCTION 1 — Ajouter un produit dans le tableau
 * Crée un nouvel objet Product et l'insère dans le tableau products
 */
function addProduct(nom, description, prix, pays, categorie) {
  /* Valider les données avant insertion */
  if (!nom || !prix || isNaN(parseFloat(prix))) {
    showNotification("Nom et prix valides sont obligatoires !", "error");
    return false;
  }
  /* Créer l'objet avec le constructeur */
  var nouveau = new Product(
    nextId++,
    nom,
    description || "Aucune description",
    parseFloat(prix),
    pays || "Unknown",
    categorie || "Général",
    "https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=600&q=80"
  );
  /* Ajouter au tableau global */
  products.push(nouveau);
  /* Rafraîchir l'affichage */
  displaytable();
  showCarts();
  showNotification("Produit « " + nom + " » ajouté avec succès !", "success");
  return true;
}
 
/**
 * FONCTION 2 — Afficher les données correspondantes (cartes produit)
 * Génère dynamiquement les cartes .prod-card dans la grille du shop
 */
function showCarts(liste) {
  if (!liste) { liste = products; }
 
  var grid = document.getElementById("prod-grid-dynamic");
  if (!grid) { return; }
 
  grid.innerHTML = "";
 
  liste.forEach(function(p) {
    var card = document.createElement("div");
    card.className = "prod-card";
    card.innerHTML =
      "<img src='" + p.image + "' alt='" + p.name + "' loading='lazy'>" +
      "<div class='prod-info'>" +
        "<h3>" + p.name + "</h3>" +
        "<p>" + p.description + "</p>" +
        "<div class='price'>$" + p.price.toFixed(2) + " <span>· " + p.country + "</span></div>" +
        "<button class='btn' onclick='addToCart(" + p.id + ")'>Add to Cart</button>" +
      "</div>";
    grid.appendChild(card);
  });
}
 
/*
   1e. GESTION VIA FORMULAIRES
*/
 
/**
 * Gère la soumission du formulaire d'ajout de produit
 * Récupère les valeurs des champs et appelle addProduct()
 */
function submitAdd(event) {
  event.preventDefault(); /* Empêcher le rechargement de la page */
 
  var nom = document.getElementById("f-name").value.trim();
  var desc = document.getElementById("f-desc").value.trim();
  var prix = document.getElementById("f-price").value.trim();
  var pays = document.getElementById("f-country").value.trim();
  var categorie = document.getElementById("f-category").value;
 
  var succes = addProduct(nom, desc, prix, pays, categorie);
 
  if (succes) {
    /* Réinitialiser le formulaire si ajout réussi */
    document.getElementById("form-add").reset();
  }
}
 
/**
 * Recherche des produits dans le tableau selon une requête
 * Filtre sur le nom, la description et la catégorie
 */
function searchProduct(query) {
  var q = query.toLowerCase().trim();
 
  if (q === "") {
    /* Si recherche vide, réafficher tout */
    displaytable();
    showCarts();
    return;
  }
 
  /* Filtrer les produits correspondant à la recherche */
  var resultats = products.filter(function(p) {
    return (
      p.name.toLowerCase().includes(q)        ||
      p.description.toLowerCase().includes(q) ||
      p.category.toLowerCase().includes(q)    ||
      p.country.toLowerCase().includes(q)
    );
  });
 
  /* Afficher les résultats */
  displaytable(resultats);
  showCarts(resultats);
 
  var msg = document.getElementById("search-msg");
  if (msg) {
    msg.textContent = resultats.length + " résultat(s) pour « " + query + " »";
  }
}
 
/**
 * Supprime un produit du tableau par son ID
 */
function deleteProduct(id) {
  products = products.filter(function(p) { return p.id !== id; });
  displaytable();
  showCarts();
  showNotification("Produit supprimé.", "info");
}
 
/**
 * Simule l'ajout au panier avec une notification
 */
function addToCart(id) {
  var produit = products.find(function(p) { return p.id === id; });
  if (produit) {
    showNotification("« " + produit.name + " » ajouté au panier !", "success");
  }
}
 
/*
  *UTILITAIRE — Notification toast
*/
 
/**
 * Affiche une notification temporaire en bas de l'écran
 * @param {string} message — texte à afficher
 * @param {string} type — "success" | "error" | "info"
 */
function showNotification(message, type) {
  /* Supprimer toute notification existante */
  var existing = document.getElementById("toast-notif");
  if (existing) { existing.remove(); }
 
  var toast = document.createElement("div");
  toast.id = "toast-notif";
  toast.className = "toast toast-" + (type || "info");
  toast.textContent = message;
  document.body.appendChild(toast);
 
  /* Afficher avec animation */
  setTimeout(function() { toast.classList.add("toast-show"); }, 10);
  /* Masquer après 3 secondes */
  setTimeout(function() {
    toast.classList.remove("toast-show");
    setTimeout(function() { toast.remove(); }, 400);
  }, 3000);
}
 
/*
  *INITIALISATION AU CHARGEMENT DE LA PAGE 
*/
 
document.addEventListener("DOMContentLoaded", function() {
 
  /* Générer le tableau et les cartes au démarrage */
  displaytable();
  showCarts();
 
  /*Formulaire d'ajout */
  var formAdd = document.getElementById("form-add");
  if (formAdd) {
    formAdd.addEventListener("submit", submitAdd);
  }
 
  /*Formulaire de recherche*/
  var searchInput = document.getElementById("search-input");
  if (searchInput) {
    /* Recherche en temps réel à chaque frappe */
    searchInput.addEventListener("input", function() {
     searchProduct(this.value);
    });
  }
 
  var formSearch = document.getElementById("form-search");
  if (formSearch) {
    formSearch.addEventListener("submit", function(e) {
      e.preventDefault();
     searchProduct(document.getElementById("search-input").value);
    });
  }
 
  /*Bouton réinitialiser la recherche */
  var btnReset = document.getElementById("btn-reset-search");
  if (btnReset) {
    btnReset.addEventListener("click", function() {
      document.getElementById("search-input").value = "";
     searchProduct("");
      var msg = document.getElementById("search-msg");
      if (msg) { msg.textContent = ""; }
    });
  }
});