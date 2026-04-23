<?php
/* ============================================================
   products_search.php — Recherche de produits (SELECT)
   UsellBuy — ENSI 2025/2026
   Démontre : query(), fetchAll(), fetch(), fetchObject()
   ============================================================ */

require_once 'db.php';
$pdo = getConnection();

$keyword  = trim($_GET['keyword']  ?? '');
$category = trim($_GET['category'] ?? '');
$minPrice = $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice = $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

$results  = [];
$searched = false;

/* ── Récupérer les catégories distinctes pour le filtre (query) ── */
$categories = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

/* ── Recherche avec critères (prepare + execute positionnels) ── */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searched = true;
    $conditions = ['1=1'];
    $params     = [];

    if ($keyword !== '') {
        $conditions[] = '(name LIKE ? OR description LIKE ? OR country LIKE ?)';
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    if ($category !== '') {
        $conditions[] = 'category = ?';
        $params[] = $category;
    }
    if ($minPrice !== null) {
        $conditions[] = 'price >= ?';
        $params[] = $minPrice;
    }
    if ($maxPrice !== null) {
        $conditions[] = 'price <= ?';
        $params[] = $maxPrice;
    }

    $sql  = 'SELECT * FROM products WHERE ' . implode(' AND ', $conditions) . ' ORDER BY name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(); /* fetchAll() */
}

/* ── Récupérer un produit aléatoire via fetchObject() pour la démo ── */
$featured = $pdo->query('SELECT * FROM products ORDER BY RAND() LIMIT 1')->fetchObject();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Product Search</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .page-wrap  { max-width:1100px; margin:60px auto; padding:0 40px; }
    .search-card { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:36px; margin-bottom:32px; }
    .search-card h2 { margin-bottom:20px; }
    .filter-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px; align-items:end; }
    .filter-grid label { display:block; color:#aaa; font-size:13px; margin-bottom:6px; }
    .filter-grid input, .filter-grid select { width:100%; }
    .results-table { width:100%; border-collapse:collapse; margin-top:20px; }
    .results-table th, .results-table td { padding:12px 16px; border:1px solid #2a2a2a; text-align:left; }
    .results-table th { background:#1a1a1a; color:var(--yellow); }
    .results-table td { color:#ccc; }
    .results-table tr:hover td { background:#1a1a1a; }
    .results-table img { width:50px; height:50px; object-fit:cover; border-radius:6px; }
    .price-badge { background:var(--golden); color:var(--yellow); padding:3px 10px; border-radius:20px; font-size:13px; }
    .cat-badge   { background:#1a1a2a; color:#88aaff; padding:3px 10px; border-radius:20px; font-size:12px; }
    .no-results  { color:#aaa; padding:20px 0; }
    .featured-box { background:#0d1a0d; border:1px solid #1f3a1f; border-radius:10px; padding:20px; display:flex; gap:20px; align-items:center; }
    .featured-box img { width:80px; height:80px; object-fit:cover; border-radius:8px; }
    .featured-box h4 { color:var(--yellow); margin-bottom:6px; }
    .section-title { color:var(--yellow); font-size:13px; text-transform:uppercase; letter-spacing:1px; margin:28px 0 12px; }
    .crud-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
    .crud-links a { padding:8px 18px; border-radius:8px; font-size:13px; text-decoration:none; border:1px solid var(--yellow); color:var(--yellow); transition:.2s; }
    .crud-links a:hover { background:var(--yellow); color:#000; }
    .crud-links a.active { background:var(--yellow); color:#000; }
    @media(max-width:768px){ .filter-grid{ grid-template-columns:1fr 1fr; } }
    @media(max-width:480px){ .filter-grid{ grid-template-columns:1fr; } }
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
    <h1 style="margin-bottom:8px;">Product <span class="yellow">Search</span></h1>
    <p class="sub" style="margin-bottom:28px;">Search our catalogue using keywords, category or price range.</p>

    <!-- CRUD Navigation -->
    <div class="crud-links">
      <a href="products_search.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> Search</a>
      <a href="products_insert.php"><i class="fa-solid fa-plus"></i> Add Product</a>
      <a href="products_update.php"><i class="fa-solid fa-pen"></i> Update Product</a>
      <a href="products_delete.php"><i class="fa-solid fa-trash"></i> Delete Product</a>
    </div>

    <!-- Featured Product (fetchObject demo) -->
    <?php if ($featured): ?>
    <div class="section-title"><i class="fa-solid fa-star"></i> Featured Product (fetchObject)</div>
    <div class="featured-box">
      <img src="<?= htmlspecialchars($featured->image_url) ?>" alt="<?= htmlspecialchars($featured->name) ?>">
      <div>
        <h4><?= htmlspecialchars($featured->name) ?></h4>
        <p style="color:#aaa;font-size:13px;"><?= htmlspecialchars($featured->description) ?></p>
        <span class="price-badge">$<?= number_format($featured->price, 2) ?></span>
        &nbsp;<span class="cat-badge"><?= htmlspecialchars($featured->category) ?></span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="search-card" style="margin-top:28px;">
      <h2><i class="fa-solid fa-filter"></i> Search Filters</h2>
      <form method="GET" action="products_search.php">
        <div class="filter-grid">
          <div>
            <label for="keyword">Keyword (name / description / country)</label>
            <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="e.g. Guitar, Italy…">
          </div>
          <div>
            <label for="category">Category</label>
            <select id="category" name="category">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="min_price">Min Price ($)</label>
            <input type="number" id="min_price" name="min_price" min="0" step="0.01"
                   value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" placeholder="0.00">
          </div>
          <div>
            <label for="max_price">Max Price ($)</label>
            <input type="number" id="max_price" name="max_price" min="0" step="0.01"
                   value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" placeholder="9999.99">
          </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;">
          <button type="submit" name="search" class="btn"><i class="fa-solid fa-search"></i> Search</button>
          <a href="products_search.php" class="btn" style="background:transparent;border:1px solid #444;color:#aaa;">
            <i class="fa-solid fa-xmark"></i> Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Results -->
    <?php if ($searched): ?>
    <div class="section-title">
      <i class="fa-solid fa-table-list"></i>
      Results — <?= count($results) ?> product(s) found
    </div>
    <?php if (empty($results)): ?>
      <p class="no-results"><i class="fa-solid fa-circle-info"></i> No products match your criteria.</p>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="results-table">
          <thead>
            <tr>
              <th>#</th><th>Photo</th><th>Name</th><th>Description</th>
              <th>Price</th><th>Country</th><th>Category</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td><img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></td>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 50, '…')) ?></td>
              <td><span class="price-badge">$<?= number_format($p['price'], 2) ?></span></td>
              <td><?= htmlspecialchars($p['country']) ?></td>
              <td><span class="cat-badge"><?= htmlspecialchars($p['category']) ?></span></td>
              <td style="white-space:nowrap;">
                <a href="products_update.php?id=<?= (int)$p['id'] ?>" style="color:var(--yellow);margin-right:10px;" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <a href="products_delete.php?id=<?= (int)$p['id'] ?>" style="color:var(--error);" title="Delete"
                   onclick="return confirm('Delete this product?')">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</body>
</html>
