<?php
/* ============================================================
   products_update.php — Mise à jour d'un produit (UPDATE)
   UsellBuy — ENSI 2025/2026
   Démontre : prepare() + execute() nommés, revalidation PHP
   ============================================================ */

require_once 'db.php';
$pdo = getConnection();

$errors  = [];
$success = false;
$product = null;

/* ── Charger un produit par ID (GET ou POST) ── */
$loadId = (int)($_GET['id'] ?? $_POST['load_id'] ?? 0);

if ($loadId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$loadId]);
    $product = $stmt->fetch(); /* fetch() — un seul enregistrement */
    if (!$product) {
        $errors[] = "No product found with ID #$loadId.";
        $product  = null;
    }
}

/* ── Traitement POST : mise à jour ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id          = (int)($_POST['id']          ?? 0);
    $name        = trim($_POST['name']         ?? '');
    $description = trim($_POST['description']  ?? '');
    $price       = trim($_POST['price']        ?? '');
    $country     = trim($_POST['country']      ?? '');
    $category    = trim($_POST['category']     ?? '');
    $imageUrl    = trim($_POST['image_url']    ?? '');

    /* ── Revalidation PHP ── */
    if ($id <= 0) {
        $errors[] = 'Invalid product ID.';
    }
    if ($name === '' || strlen($name) < 2) {
        $errors[] = 'Product name must be at least 2 characters.';
    }
    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Please enter a valid positive price.';
    }
    $validCategories = ['Electronics', 'Fashion', 'Furniture', 'Food', 'Music', 'Sport', 'Général'];
    if (!in_array($category, $validCategories)) {
        $errors[] = 'Please select a valid category.';
    }
    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Image URL is not valid.';
    }

    if (empty($errors)) {
        /* UPDATE via prepare() + execute() nommés */
        $sql = 'UPDATE products
                SET name = :name, description = :description, price = :price,
                    country = :country, category = :category, image_url = :image_url
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'        => $name,
            ':description' => $description,
            ':price'       => (float)$price,
            ':country'     => $country,
            ':category'    => $category,
            ':image_url'   => $imageUrl,
            ':id'          => $id,
        ]);
        $success = true;
        /* Recharger le produit mis à jour */
        $stmt2 = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt2->execute([$id]);
        $product = $stmt2->fetch();
    }
}

/* ── Récupérer tous les produits pour la liste de sélection ── */
$allProducts = $pdo->query('SELECT id, name, price, category FROM products ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Update Product</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .page-wrap { max-width:900px; margin:60px auto; padding:0 40px; }
    .upd-card  { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:40px; margin-bottom:28px; }
    .upd-card h2 { margin-bottom:20px; }
    .fg { margin-bottom:18px; }
    .fg label { display:block; color:#aaa; font-size:13px; margin-bottom:6px; }
    .fg input, .fg select, .fg textarea { width:100%; }
    .err-list { list-style:none; padding:0; margin-bottom:20px; }
    .err-list li { color:var(--error); padding:6px 0; border-bottom:1px solid #2a2a2a; }
    .err-list li::before { content:"✕  "; }
    .success-box { background:#0d1f0d; border:1px solid #1f3a1f; border-radius:10px; padding:20px; margin-bottom:20px; color:#44cc44; }
    .all-table { width:100%; border-collapse:collapse; }
    .all-table th, .all-table td { padding:10px 14px; border:1px solid #2a2a2a; text-align:left; }
    .all-table th { background:#1a1a1a; color:var(--yellow); }
    .all-table td { color:#ccc; }
    .all-table tr:hover td { background:#1a1a1a; }
    .edit-btn { background:#1a1a0d; color:var(--yellow); border:1px solid var(--yellow); padding:4px 12px; border-radius:6px; cursor:pointer; font-size:12px; text-decoration:none; }
    .edit-btn:hover { background:var(--yellow); color:#000; }
    .price-badge { background:var(--golden); color:var(--yellow); padding:2px 8px; border-radius:20px; font-size:12px; }
    .crud-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
    .crud-links a { padding:8px 18px; border-radius:8px; font-size:13px; text-decoration:none; border:1px solid var(--yellow); color:var(--yellow); transition:.2s; }
    .crud-links a:hover, .crud-links a.active { background:var(--yellow); color:#000; }
    .load-form { display:flex; gap:12px; align-items:flex-end; margin-bottom:24px; }
    .load-form input { flex:1; }
  </style>
</head>
<body>
  <header>
    <div class = "logo">
        <a href="index.html"><img src = "u__3_-removebg-preview.png"></a>
    </div>
    
  <nav id ="nav_1">
    <a href="index.html">Home</a>
    <a href="services.html">Services</a>
    <a href="shop.html">Shop</a>
    <a href="faq.html">FAQ</a>
    <a href="about.html"> About us</a>
    <a href="contact.html">Contact</a>
  </nav>
  <nav id = "nav-2">
      <div class = "social">

          <a href="https://facebook.com" target="_blank" class="social fb">
              <i class="fa-brands fa-facebook-f"></i>
            </a>
            
            <a href="https://instagram.com" target="_blank" class="social ig">
                <i class="fa-brands fa-instagram"></i>
            </a>
            
            <a href="https://x.com" target="_blank" class="social x">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
        </div>
    </nav>
</header>

  <div class="page-wrap">
    <div class="label">Database</div>
    <h1 style="margin-bottom:8px;">Update <span class="yellow">Product</span></h1>
    <p class="sub" style="margin-bottom:28px;">Modify an existing product's details.</p>

    <div class="crud-links">
      <a href="products_search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
      <a href="products_insert.php"><i class="fa-solid fa-plus"></i> Add Product</a>
      <a href="products_update.php" class="active"><i class="fa-solid fa-pen"></i> Update Product</a>
      <a href="products_delete.php"><i class="fa-solid fa-trash"></i> Delete Product</a>
    </div>

    <!-- Load Product by ID -->
    <div class="upd-card">
      <h2><i class="fa-solid fa-magnifying-glass"></i> Load Product by ID</h2>
      <form method="GET" action="products_update.php" class="load-form">
        <div style="flex:1;">
          <label for="load-id" style="color:#aaa;font-size:13px;display:block;margin-bottom:6px;">Product ID</label>
          <input type="number" id="load-id" name="id" min="1" placeholder="Enter ID to edit"
                 value="<?= $loadId > 0 ? $loadId : '' ?>">
        </div>
        <button type="submit" class="btn"><i class="fa-solid fa-upload"></i> Load</button>
      </form>

      <?php if (!empty($errors)): ?>
      <ul class="err-list">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="success-box">
        <i class="fa-solid fa-circle-check"></i>
        Product updated successfully!
      </div>
      <?php endif; ?>
    </div>

    <!-- Edit Form (shown when product is loaded) -->
    <?php if ($product): ?>
    <div class="upd-card">
      <h2><i class="fa-solid fa-pen"></i> Editing: <?= htmlspecialchars($product['name']) ?> (ID #<?= (int)$product['id'] ?>)</h2>
      <form method="POST" action="products_update.php" onsubmit="return validateUpdateForm()">
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="fg">
            <label for="upd-name">Product Name *</label>
            <input type="text" id="upd-name" name="name"
                   value="<?= htmlspecialchars($product['name']) ?>" minlength="2" maxlength="255">
            <span id="err-upd-name" style="color:var(--error);font-size:12px;"></span>
          </div>
          <div class="fg">
            <label for="upd-price">Price ($) *</label>
            <input type="number" id="upd-price" name="price" min="0" step="0.01"
                   value="<?= htmlspecialchars($product['price']) ?>">
            <span id="err-upd-price" style="color:var(--error);font-size:12px;"></span>
          </div>
        </div>
        <div class="fg">
          <label for="upd-desc">Description</label>
          <input type="text" id="upd-desc" name="description" maxlength="500"
                 value="<?= htmlspecialchars($product['description']) ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="fg">
            <label for="upd-country">Country</label>
            <input type="text" id="upd-country" name="country" maxlength="100"
                   value="<?= htmlspecialchars($product['country']) ?>">
          </div>
          <div class="fg">
            <label for="upd-category">Category *</label>
            <select id="upd-category" name="category">
              <?php foreach (['Electronics','Fashion','Furniture','Food','Music','Sport','Général'] as $cat): ?>
                <option value="<?= $cat ?>" <?= $product['category'] === $cat ? 'selected' : '' ?>>
                  <?= $cat ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="fg">
          <label for="upd-image">Image URL</label>
          <input type="url" id="upd-image" name="image_url"
                 value="<?= htmlspecialchars($product['image_url']) ?>">
        </div>
        <button type="submit" class="btn" style="width:100%;">
          <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- All Products Table -->
    <div class="upd-card">
      <h2><i class="fa-solid fa-table-list"></i> All Products — Click to Edit</h2>
      <div style="overflow-x:auto;">
        <table class="all-table">
          <thead>
            <tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Edit</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allProducts as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td><span class="price-badge">$<?= number_format($p['price'], 2) ?></span></td>
              <td>
                <a href="products_update.php?id=<?= (int)$p['id'] ?>" class="edit-btn">
                  <i class="fa-solid fa-pen"></i> Edit
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
  function validateUpdateForm() {
    var valid = true;

    var name = document.getElementById('upd-name').value.trim();
    var errName = document.getElementById('err-upd-name');
    if (name.length < 2) {
      errName.textContent = 'Product name must be at least 2 characters.';
      valid = false;
    } else { errName.textContent = ''; }

    var price = parseFloat(document.getElementById('upd-price').value);
    var errPrice = document.getElementById('err-upd-price');
    if (isNaN(price) || price < 0) {
      errPrice.textContent = 'Please enter a valid positive price.';
      valid = false;
    } else { errPrice.textContent = ''; }

    return valid;
  }
  </script>
</body>
</html>
