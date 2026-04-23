<?php
/* ============================================================
   products_insert.php — Ajout d'un nouveau produit (INSERT)
   UsellBuy — ENSI 2025/2026
   Démontre : prepare() + execute() nommés, revalidation PHP
   ============================================================ */

require_once 'db.php';

$errors  = [];
$success = false;
$insertedId = null;

/* ── Traitement POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnection();

    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $country     = trim($_POST['country']     ?? '');
    $category    = trim($_POST['category']    ?? '');
    $imageUrl    = trim($_POST['image_url']   ?? '');

    /* ── Revalidation PHP ── */
    if ($name === '') {
        $errors[] = 'Product name is required.';
    } elseif (strlen($name) < 2 || strlen($name) > 255) {
        $errors[] = 'Product name must be between 2 and 255 characters.';
    }

    if ($price === '' || !is_numeric($price)) {
        $errors[] = 'A valid price is required.';
    } elseif ((float)$price < 0) {
        $errors[] = 'Price cannot be negative.';
    } elseif ((float)$price > 999999.99) {
        $errors[] = 'Price is too high.';
    }

    $validCategories = ['Electronics', 'Fashion', 'Furniture', 'Food', 'Music', 'Sport', 'Général'];
    if (!in_array($category, $validCategories)) {
        $errors[] = 'Please select a valid category.';
    }

    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Image URL is not valid.';
    }

    /* ── Insertion si pas d'erreurs ── */
    if (empty($errors)) {
        $sql = 'INSERT INTO products (name, description, price, country, category, image_url)
                VALUES (:name, :description, :price, :country, :category, :image_url)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'        => $name,
            ':description' => $description,
            ':price'       => (float)$price,
            ':country'     => $country,
            ':category'    => $category,
            ':image_url'   => $imageUrl ?: 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=600&q=80',
        ]);
        $insertedId = $pdo->lastInsertId();
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Add Product</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .page-wrap { max-width:700px; margin:60px auto; padding:0 40px; }
    .form-card  { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:40px; }
    .form-card h2 { margin-bottom:24px; }
    .fg { margin-bottom:18px; }
    .fg label { display:block; color:#aaa; font-size:13px; margin-bottom:6px; }
    .fg input, .fg select, .fg textarea { width:100%; }
    .err-list { list-style:none; padding:0; margin-bottom:20px; }
    .err-list li { color:var(--error); padding:6px 0; border-bottom:1px solid #2a2a2a; }
    .err-list li::before { content:"✕  "; }
    .success-box { background:#0d1f0d; border:1px solid #1f3a1f; border-radius:10px; padding:24px; margin-bottom:24px; }
    .success-box h3 { color:#44cc44; margin-bottom:8px; }
    .crud-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
    .crud-links a { padding:8px 18px; border-radius:8px; font-size:13px; text-decoration:none; border:1px solid var(--yellow); color:var(--yellow); transition:.2s; }
    .crud-links a:hover, .crud-links a.active { background:var(--yellow); color:#000; }
  </style>
</head>
<body>
  <header>
    <div class="logo"><a href="index.html"><img src="u__3_-removebg-preview.png" alt="logo"></a></div>
    <nav id="nav_1">
      <a href="index.html">Home</a>
      <a href="services.html">Services</a>
      <a href="shop.html">Shop</a>
      <a href="faq.html">FAQ</a>
      <a href="about.html">About us</a>
      <a href="contact.html">Contact</a>
    </nav>
  </header>

  <div class="page-wrap">
    <div class="label">Database</div>
    <h1 style="margin-bottom:8px;">Add <span class="yellow">Product</span></h1>
    <p class="sub" style="margin-bottom:28px;">Insert a new product into the catalogue.</p>

    <div class="crud-links">
      <a href="products_search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
      <a href="products_insert.php" class="active"><i class="fa-solid fa-plus"></i> Add Product</a>
      <a href="products_update.php"><i class="fa-solid fa-pen"></i> Update Product</a>
      <a href="products_delete.php"><i class="fa-solid fa-trash"></i> Delete Product</a>
    </div>

    <div class="form-card">
      <h2><i class="fa-solid fa-circle-plus"></i> New Product</h2>

      <?php if ($success): ?>
      <div class="success-box">
        <h3><i class="fa-solid fa-circle-check"></i> Product Added!</h3>
        <p style="color:#aaa;">Product was successfully inserted with ID <strong style="color:#44cc44">#<?= (int)$insertedId ?></strong>.</p>
      </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
      <ul class="err-list">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <form method="POST" action="products_insert.php" onsubmit="return validateInsertForm()">
        <div class="fg">
          <label for="ins-name">Product Name *</label>
          <input type="text" id="ins-name" name="name" placeholder="e.g. iPhone 15"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" minlength="2" maxlength="255">
          <span id="err-ins-name" style="color:var(--error);font-size:12px;"></span>
        </div>
        <div class="fg">
          <label for="ins-desc">Description</label>
          <input type="text" id="ins-desc" name="description" placeholder="Short description"
                 value="<?= htmlspecialchars($_POST['description'] ?? '') ?>" maxlength="500">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="fg">
            <label for="ins-price">Price ($) *</label>
            <input type="number" id="ins-price" name="price" placeholder="99.99" min="0" step="0.01"
                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            <span id="err-ins-price" style="color:var(--error);font-size:12px;"></span>
          </div>
          <div class="fg">
            <label for="ins-country">Country</label>
            <input type="text" id="ins-country" name="country" placeholder="e.g. France"
                   value="<?= htmlspecialchars($_POST['country'] ?? '') ?>" maxlength="100">
          </div>
        </div>
        <div class="fg">
          <label for="ins-category">Category *</label>
          <select id="ins-category" name="category">
            <option value="">— Select —</option>
            <?php foreach (['Electronics','Fashion','Furniture','Food','Music','Sport','Général'] as $cat): ?>
              <option value="<?= $cat ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span id="err-ins-category" style="color:var(--error);font-size:12px;"></span>
        </div>
        <div class="fg">
          <label for="ins-image">Image URL (optional)</label>
          <input type="url" id="ins-image" name="image_url" placeholder="https://…"
                 value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
        </div>
        <button type="submit" class="btn" style="width:100%;margin-top:8px;">
          <i class="fa-solid fa-plus"></i> Add to Catalogue
        </button>
      </form>
    </div>
  </div>

  <script>
  function validateInsertForm() {
    var valid = true;

    var name = document.getElementById('ins-name').value.trim();
    var errName = document.getElementById('err-ins-name');
    if (name.length < 2) {
      errName.textContent = 'Product name must be at least 2 characters.';
      valid = false;
    } else { errName.textContent = ''; }

    var price = parseFloat(document.getElementById('ins-price').value);
    var errPrice = document.getElementById('err-ins-price');
    if (isNaN(price) || price < 0) {
      errPrice.textContent = 'Please enter a valid positive price.';
      valid = false;
    } else { errPrice.textContent = ''; }

    var cat = document.getElementById('ins-category').value;
    var errCat = document.getElementById('err-ins-category');
    if (cat === '') {
      errCat.textContent = 'Please select a category.';
      valid = false;
    } else { errCat.textContent = ''; }

    return valid;
  }
  </script>
</body>
</html>
