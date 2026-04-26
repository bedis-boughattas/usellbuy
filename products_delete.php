<?php
/* ============================================================
   products_delete.php — Suppression d'un produit (DELETE)
   UsellBuy — ENSI 2025/2026
   Démontre : exec(), prepare() + execute() positionnels
   ============================================================ */

require_once 'db.php';
$pdo = getConnection();

$message = '';
$msgType = '';
$product = null;

/* ── Pré-remplir si id passé en GET (depuis search) ── */
$preId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ── Traitement POST : suppression ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        $message = 'Invalid product ID.';
        $msgType = 'error';
    } else {
        /* Vérifier que le produit existe (prepare + execute positionnel) */
        $check = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $check->execute([$id]);
        $existing = $check->fetch();

        if (!$existing) {
            $message = "No product found with ID #$id.";
            $msgType = 'error';
        } else {
            /* Suppression via exec() */
            $deleted = $pdo->exec("DELETE FROM products WHERE id = $id");
            if ($deleted > 0) {
                $message = "Product \"" . htmlspecialchars($existing['name']) . "\" (ID #$id) was successfully deleted.";
                $msgType = 'success';
            } else {
                $message = 'Deletion failed. Please try again.';
                $msgType = 'error';
            }
        }
    }
}

/* ── Récupérer tous les produits pour le tableau (fetchAll) ── */
$allProducts = $pdo->query('SELECT id, name, category, price, country FROM products ORDER BY id')->fetchAll();

/* ── Pré-charger le produit si id en GET ── */
if ($preId > 0 && $msgType !== 'success') {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$preId]);
    $product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Delete Product</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .page-wrap { max-width:900px; margin:60px auto; padding:0 40px; }
    .del-card  { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:40px; margin-bottom:28px; }
    .del-card h2 { margin-bottom:20px; }
    .fg { margin-bottom:18px; }
    .fg label { display:block; color:#aaa; font-size:13px; margin-bottom:6px; }
    .fg input { width:100%; }
    .msg-box { padding:16px 20px; border-radius:8px; margin-bottom:20px; }
    .msg-success { background:#0d1f0d; border:1px solid #1f3a1f; color:#44cc44; }
    .msg-error   { background:#1f0d0d; border:1px solid #3a1f1f; color:var(--error); }
    .all-table { width:100%; border-collapse:collapse; }
    .all-table th, .all-table td { padding:10px 14px; border:1px solid #2a2a2a; text-align:left; }
    .all-table th { background:#1a1a1a; color:var(--yellow); }
    .all-table td { color:#ccc; }
    .all-table tr:hover td { background:#1a1a1a; }
    .del-btn { background:#3a1a1a; color:var(--error); border:1px solid var(--error); padding:4px 12px; border-radius:6px; cursor:pointer; font-size:12px; }
    .del-btn:hover { background:var(--error); color:#fff; }
    .price-badge { background:var(--golden); color:var(--yellow); padding:2px 8px; border-radius:20px; font-size:12px; }
    .crud-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
    .crud-links a { padding:8px 18px; border-radius:8px; font-size:13px; text-decoration:none; border:1px solid var(--yellow); color:var(--yellow); transition:.2s; }
    .crud-links a:hover, .crud-links a.active { background:var(--yellow); color:#000; }
    .preview-box { background:#1a1a1a; border-radius:8px; padding:16px; margin-bottom:16px; display:flex; gap:16px; align-items:center; }
    .preview-box img { width:70px; height:70px; object-fit:cover; border-radius:6px; }
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
    <h1 style="margin-bottom:8px;">Delete <span class="yellow">Product</span></h1>
    <p class="sub" style="margin-bottom:28px;">Remove a product from the catalogue by its ID.</p>

    <div class="crud-links">
      <a href="products_search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
      <a href="products_insert.php"><i class="fa-solid fa-plus"></i> Add Product</a>
      <a href="products_update.php"><i class="fa-solid fa-pen"></i> Update Product</a>
      <a href="products_delete.php" class="active"><i class="fa-solid fa-trash"></i> Delete Product</a>
    </div>

    <!-- Delete Form -->
    <div class="del-card">
      <h2><i class="fa-solid fa-trash" style="color:var(--error)"></i> Delete Product</h2>

      <?php if ($message): ?>
      <div class="msg-box msg-<?= $msgType ?>">
        <i class="fa-solid fa-<?= $msgType === 'success' ? 'circle-check' : 'circle-xmark' ?>"></i>
        <?= $message ?>
      </div>
      <?php endif; ?>

      <?php if ($product): ?>
      <div class="preview-box">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <div>
          <strong style="color:var(--yellow)"><?= htmlspecialchars($product['name']) ?></strong>
          <p style="color:#aaa;font-size:13px;margin-top:4px;"><?= htmlspecialchars($product['description']) ?></p>
          <span class="price-badge">$<?= number_format($product['price'], 2) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <form method="POST" action="products_delete.php" onsubmit="return confirmDelete()">
        <div class="fg">
          <label for="del-id">Product ID *</label>
          <input type="number" id="del-id" name="id" min="1" placeholder="Enter product ID"
                 value="<?= $preId > 0 ? $preId : '' ?>" required>
          <span id="err-del-id" style="color:var(--error);font-size:12px;"></span>
        </div>
        <button type="submit" class="btn" style="background:#3a1a1a;border:1px solid var(--error);color:var(--error);">
          <i class="fa-solid fa-trash"></i> Delete Product
        </button>
      </form>
    </div>

    <!-- All Products Table -->
    <div class="del-card">
      <h2><i class="fa-solid fa-table-list"></i> All Products (<?= count($allProducts) ?>)</h2>
      <div style="overflow-x:auto;">
        <table class="all-table">
          <thead>
            <tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Country</th><th>Delete</th></tr>
          </thead>
          <tbody>
            <?php foreach ($allProducts as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td><span class="price-badge">$<?= number_format($p['price'], 2) ?></span></td>
              <td><?= htmlspecialchars($p['country']) ?></td>
              <td>
                <form method="POST" action="products_delete.php" style="display:inline;"
                      onsubmit="return confirm('Delete <?= addslashes($p['name']) ?>?')">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="del-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
  function confirmDelete() {
    var id = parseInt(document.getElementById('del-id').value);
    var err = document.getElementById('err-del-id');
    if (isNaN(id) || id <= 0) {
      err.textContent = 'Please enter a valid product ID (positive integer).';
      return false;
    }
    err.textContent = '';
    return confirm('Are you sure you want to permanently delete product ID #' + id + '?');
  }
  </script>
</body>
</html>
