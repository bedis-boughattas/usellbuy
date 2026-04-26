<?php
require_once 'db.php';
$pdo = getConnection();

$categories = ['Electronics','Fashion','Furniture','Food','Music','Sport','Général'];
$activeTab  = $_GET['tab'] ?? 'search';
$msg        = $_GET['msg']  ?? '';

/* ═══════════════════════════════════════════════
   POST — ADD
═══════════════════════════════════════════════ */
$addErrors  = [];
$addSuccess = false;
$addId      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $aName  = trim($_POST['name']        ?? '');
    $aDesc  = trim($_POST['description'] ?? '');
    $aPrice = trim($_POST['price']       ?? '');
    $aCntry = trim($_POST['country']     ?? '');
    $aCat   = trim($_POST['category']    ?? '');
    $aImg   = trim($_POST['image_url']   ?? '');

    if ($aName === '' || strlen($aName) < 2 || strlen($aName) > 255) {
        $addErrors[] = 'Product name must be between 2 and 255 characters.';
    }
    if ($aPrice === '' || !is_numeric($aPrice) || (float)$aPrice < 0 || (float)$aPrice > 999999.99) {
        $addErrors[] = 'A valid price (0 – 999 999.99) is required.';
    }
    if (!in_array($aCat, $categories)) {
        $addErrors[] = 'Please select a valid category.';
    }
    if ($aImg !== '' && !filter_var($aImg, FILTER_VALIDATE_URL)) {
        $addErrors[] = 'Image URL is not valid.';
    }

    if (empty($addErrors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, description, price, country, category, image_url)
             VALUES (:name, :description, :price, :country, :category, :image_url)'
        );
        $stmt->execute([
            ':name'        => $aName,
            ':description' => $aDesc,
            ':price'       => (float)$aPrice,
            ':country'     => $aCntry,
            ':category'    => $aCat,
            ':image_url'   => $aImg ?: 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=600&q=80',
        ]);
        $addId      = $pdo->lastInsertId();
        $addSuccess = true;
    }
    $activeTab = 'add';
}

/* ═══════════════════════════════════════════════
   POST — UPDATE
═══════════════════════════════════════════════ */
$updErrors  = [];
$updSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $uId    = (int)($_POST['id']          ?? 0);
    $uName  = trim($_POST['name']         ?? '');
    $uDesc  = trim($_POST['description']  ?? '');
    $uPrice = trim($_POST['price']        ?? '');
    $uCntry = trim($_POST['country']      ?? '');
    $uCat   = trim($_POST['category']     ?? '');
    $uImg   = trim($_POST['image_url']    ?? '');

    if ($uId <= 0) {
        $updErrors[] = 'Invalid product ID.';
    }
    if ($uName === '' || strlen($uName) < 2) {
        $updErrors[] = 'Product name must be at least 2 characters.';
    }
    if ($uPrice === '' || !is_numeric($uPrice) || (float)$uPrice < 0) {
        $updErrors[] = 'Please enter a valid positive price.';
    }
    if (!in_array($uCat, $categories)) {
        $updErrors[] = 'Please select a valid category.';
    }
    if ($uImg !== '' && !filter_var($uImg, FILTER_VALIDATE_URL)) {
        $updErrors[] = 'Image URL is not valid.';
    }

    if (empty($updErrors)) {
        $stmt = $pdo->prepare(
            'UPDATE products
             SET name=:name, description=:description, price=:price,
                 country=:country, category=:category, image_url=:image_url
             WHERE id=:id'
        );
        $stmt->execute([
            ':name'        => $uName,
            ':description' => $uDesc,
            ':price'       => (float)$uPrice,
            ':country'     => $uCntry,
            ':category'    => $uCat,
            ':image_url'   => $uImg,
            ':id'          => $uId,
        ]);
        $updSuccess = true;
    }
    $activeTab = 'update';
}

/* ═══════════════════════════════════════════════
   POST — DELETE
═══════════════════════════════════════════════ */
$delMsg  = '';
$delType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $dId = (int)($_POST['id'] ?? 0);
    if ($dId <= 0) {
        $delMsg  = 'Invalid product ID.';
        $delType = 'error';
    } else {
        $chk = $pdo->prepare('SELECT name FROM products WHERE id = ?');
        $chk->execute([$dId]);
        $existing = $chk->fetch();
        if (!$existing) {
            $delMsg  = "No product found with ID #$dId.";
            $delType = 'error';
        } else {
            $deleted = $pdo->exec("DELETE FROM products WHERE id = $dId");
            if ($deleted > 0) {
                $delMsg  = 'Product "' . htmlspecialchars($existing['name']) . '" deleted successfully.';
                $delType = 'success';
            } else {
                $delMsg  = 'Deletion failed. Please try again.';
                $delType = 'error';
            }
        }
    }
    $activeTab = 'delete';
}

/* ═══════════════════════════════════════════════
   SEARCH query
═══════════════════════════════════════════════ */
$searchResults  = [];
$searchDone     = false;
$featuredProduct = null;

$kw      = trim($_GET['kw']       ?? '');
$sCat    = trim($_GET['category'] ?? '');
$sMin    = trim($_GET['min']      ?? '');
$sMax    = trim($_GET['max']      ?? '');

if ($activeTab === 'search') {
    $sql    = 'SELECT * FROM products WHERE 1=1';
    $params = [];

    if ($kw !== '') {
        $sql     .= ' AND (name LIKE ? OR description LIKE ? OR country LIKE ?)';
        $like     = "%$kw%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ($sCat !== '') {
        $sql     .= ' AND category = ?';
        $params[] = $sCat;
    }
    if ($sMin !== '' && is_numeric($sMin)) {
        $sql     .= ' AND price >= ?';
        $params[] = (float)$sMin;
    }
    if ($sMax !== '' && is_numeric($sMax)) {
        $sql     .= ' AND price <= ?';
        $params[] = (float)$sMax;
    }
    $sql .= ' ORDER BY id DESC';

    if (!empty($params)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll();
        $searchDone    = true;
    } else {
        $searchResults = $pdo->query($sql)->fetchAll();
        $searchDone    = true;
    }

    $countAll = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($countAll > 0) {
        $offset = rand(0, (int)$countAll - 1);
        $fStmt  = $pdo->query("SELECT * FROM products ORDER BY id LIMIT 1 OFFSET $offset");
        $featuredProduct = $fStmt->fetchObject();
    }
}

/* ═══════════════════════════════════════════════
   All products for Update / Delete tabs
═══════════════════════════════════════════════ */
$allProducts = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();

/* Product to edit (inline) */
$editProduct = null;
$editId      = (int)($_GET['edit'] ?? 0);
if ($editId > 0 && $activeTab === 'update') {
    $es = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $es->execute([$editId]);
    $editProduct = $es->fetch();
}
if ($updSuccess && isset($uId)) {
    $es2 = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $es2->execute([$uId]);
    $editProduct = $es2->fetch();
    $allProducts = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Products Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    :root {
      --yellow:  #ffd60a;
      --error:   #ff7070;
      --valide:  #44cc44;
      --golden:  #ffd60a40;
    }

    .pm-wrap { max-width: 1000px; margin: 60px auto; padding: 0 32px; }

    /* ── Tabs ── */
    .tab-bar {
      display: flex; gap: 6px; flex-wrap: wrap;
      border-bottom: 2px solid #1f1f1f; margin-bottom: 32px;
    }
    .tab-btn {
      padding: 10px 22px; border: none; background: transparent;
      color: #888; font-family: 'Inter', sans-serif; font-size: 14px;
      font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent;
      margin-bottom: -2px; transition: color .2s, border-color .2s;
    }
    .tab-btn:hover { color: #ccc; }
    .tab-btn.active { color: var(--yellow); border-bottom-color: var(--yellow); }
    .tab-btn i { margin-right: 7px; }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── Cards ── */
    .card {
      background: #111; border: 1px solid #1f1f1f;
      border-radius: 12px; padding: 32px; margin-bottom: 24px;
    }
    .card h2 { margin-bottom: 20px; font-size: 18px; }

    /* ── Form groups ── */
    .fg { margin-bottom: 16px; }
    .fg label { display: block; color: #aaa; font-size: 13px; margin-bottom: 6px; }
    .fg input, .fg select, .fg textarea {
      width: 100%; background: #0d0d0d; border: 1px solid #2a2a2a;
      color: #eee; border-radius: 8px; padding: 10px 14px;
      font-family: 'Inter', sans-serif; font-size: 14px; box-sizing: border-box;
      transition: border-color .2s;
    }
    .fg input:focus, .fg select:focus, .fg textarea:focus {
      outline: none; border-color: var(--yellow);
    }
    .fg .fe { color: var(--error); font-size: 12px; margin-top: 4px; display: block; }

    /* ── Grid helpers ── */
    .g2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .g3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    @media(max-width:640px) { .g2,.g3 { grid-template-columns: 1fr; } }

    /* ── Feedback ── */
    .msg-box { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .msg-success { background: #0d1f0d; border: 1px solid #1f3a1f; color: var(--valide); }
    .msg-error   { background: #1f0d0d; border: 1px solid #3a1f1f; color: var(--error); }
    .err-list { list-style: none; padding: 0; margin-bottom: 18px; }
    .err-list li { color: var(--error); padding: 5px 0; border-bottom: 1px solid #2a2a2a; font-size: 13px; }
    .err-list li::before { content: "✕  "; }

    /* ── Tables ── */
    .tbl { width: 100%; border-collapse: collapse; }
    .tbl th, .tbl td { padding: 10px 12px; border: 1px solid #2a2a2a; text-align: left; font-size: 13px; }
    .tbl th { background: #1a1a1a; color: var(--yellow); font-weight: 600; }
    .tbl td { color: #ccc; }
    .tbl tr:hover td { background: #161616; }
    .tbl img { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; }

    /* ── Badges & buttons ── */
    .price-badge {
      background: var(--golden); color: var(--yellow);
      padding: 2px 9px; border-radius: 20px; font-size: 12px; white-space: nowrap;
    }
    .cat-badge {
      background: #1a1a1a; color: #aaa;
      padding: 2px 9px; border-radius: 20px; font-size: 12px;
    }
    .btn-edit {
      background: #1a1a0d; color: var(--yellow); border: 1px solid var(--yellow);
      padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;
      font-family: 'Inter', sans-serif; text-decoration: none; display: inline-block;
    }
    .btn-edit:hover { background: var(--yellow); color: #000; }
    .btn-del {
      background: #1f0d0d; color: var(--error); border: 1px solid var(--error);
      padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;
      font-family: 'Inter', sans-serif;
    }
    .btn-del:hover { background: var(--error); color: #fff; }

    /* ── Search filters ── */
    .search-bar {
      display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 24px;
    }
    .search-bar .fg { margin-bottom: 0; flex: 1; min-width: 140px; }

    /* ── Featured card ── */
    .featured {
      display: flex; gap: 20px; align-items: center;
      background: #0d0d0d; border: 1px solid #2a2a2a;
      border-radius: 10px; padding: 20px; margin-bottom: 28px;
    }
    .featured img {
      width: 90px; height: 90px; object-fit: cover;
      border-radius: 8px; flex-shrink: 0;
    }
    .featured-info h3 { color: var(--yellow); margin-bottom: 6px; font-size: 16px; }
    .featured-info p  { color: #888; font-size: 13px; margin-bottom: 8px; }

    /* ── Inline edit form ── */
    .inline-edit {
      background: #0d0d0d; border: 1px solid var(--yellow);
      border-radius: 10px; padding: 24px; margin-bottom: 24px;
    }
    .inline-edit h3 { color: var(--yellow); margin-bottom: 18px; font-size: 15px; }

    .tbl-wrap { overflow-x: auto; }
  </style>
</head>
<body>

<header>
  <div class="logo"><a href="index.html"><img src="u__3_-removebg-preview.png" alt="UsellBuy"></a></div>
  <nav id="nav_1">
    <a href="index.html">Home</a>
    <a href="services.html">Services</a>
    <a href="shop.html">Shop</a>
    <a href="faq.html">FAQ</a>
    <a href="about.html">About us</a>
    <a href="contact.html">Contact</a>
  </nav>
</header>

<div class="pm-wrap">
  <div class="label">Database</div>
  <h1 style="margin-bottom:6px;">Products <span class="yellow">Manager</span></h1>
  <p class="sub" style="margin-bottom:28px;">Search, add, update and delete products — all in one place.</p>

  <div class="tab-bar">
    <button class="tab-btn <?= $activeTab==='search' ?'active':'' ?>" onclick="switchTab('search')">
      <i class="fa-solid fa-magnifying-glass"></i>Search
    </button>
    <button class="tab-btn <?= $activeTab==='add' ?'active':'' ?>" onclick="switchTab('add')">
      <i class="fa-solid fa-plus"></i>Add
    </button>
    <button class="tab-btn <?= $activeTab==='update' ?'active':'' ?>" onclick="switchTab('update')">
      <i class="fa-solid fa-pen"></i>Update
    </button>
    <button class="tab-btn <?= $activeTab==='delete' ?'active':'' ?>" onclick="switchTab('delete')">
      <i class="fa-solid fa-trash"></i>Delete
    </button>
  </div>

  <!-- ══════════════════════════════════════════
       TAB: SEARCH
  ══════════════════════════════════════════ -->
  <div id="tab-search" class="tab-panel <?= $activeTab==='search'?'active':'' ?>">

    <?php if ($featuredProduct): ?>
    <div class="featured">
      <img src="<?= htmlspecialchars($featuredProduct->image_url) ?>"
           alt="<?= htmlspecialchars($featuredProduct->name) ?>"
           onerror="this.src='https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=200&q=60'">
      <div class="featured-info">
        <div class="label" style="font-size:11px;margin-bottom:4px;">Featured Product</div>
        <h3><?= htmlspecialchars($featuredProduct->name) ?></h3>
        <p><?= htmlspecialchars(mb_strimwidth($featuredProduct->description ?? '', 0, 100, '…')) ?></p>
        <span class="price-badge">$<?= number_format($featuredProduct->price, 2) ?></span>
        <span class="cat-badge" style="margin-left:6px;"><?= htmlspecialchars($featuredProduct->category) ?></span>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <h2><i class="fa-solid fa-filter"></i> Filter Products</h2>
      <form method="GET" action="products_search.php">
        <input type="hidden" name="tab" value="search">
        <div class="search-bar">
          <div class="fg">
            <label>Keyword (name / description / country)</label>
            <input type="text" name="kw" placeholder="Search…" value="<?= htmlspecialchars($kw) ?>">
          </div>
          <div class="fg" style="min-width:160px;">
            <label>Category</label>
            <select name="category">
              <option value="">All categories</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c ?>" <?= $sCat===$c?'selected':'' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg" style="min-width:110px;">
            <label>Min price ($)</label>
            <input type="number" name="min" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars($sMin) ?>">
          </div>
          <div class="fg" style="min-width:110px;">
            <label>Max price ($)</label>
            <input type="number" name="max" min="0" step="0.01" placeholder="∞" value="<?= htmlspecialchars($sMax) ?>">
          </div>
          <div style="padding-bottom:0;">
            <button type="submit" class="btn" style="white-space:nowrap;">
              <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
          </div>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>
        <i class="fa-solid fa-table-list"></i>
        Results
        <span style="color:#555;font-size:14px;font-weight:400;margin-left:8px;">(<?= count($searchResults) ?> product<?= count($searchResults)!==1?'s':'' ?>)</span>
      </h2>
      <?php if (empty($searchResults)): ?>
        <p style="color:#555;font-size:14px;">No products match your criteria.</p>
      <?php else: ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>#</th><th>Photo</th><th>Name</th><th>Description</th>
              <th>Price</th><th>Country</th><th>Category</th><th>Edit</th><th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($searchResults as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td>
                <img src="<?= htmlspecialchars($r['image_url']) ?>"
                     alt="<?= htmlspecialchars($r['name']) ?>"
                     onerror="this.src='https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=100&q=60'">
              </td>
              <td style="font-weight:600;color:#eee;"><?= htmlspecialchars($r['name']) ?></td>
              <td style="max-width:200px;color:#888;"><?= htmlspecialchars(mb_strimwidth($r['description'] ?? '', 0, 60, '…')) ?></td>
              <td><span class="price-badge">$<?= number_format($r['price'], 2) ?></span></td>
              <td><?= htmlspecialchars($r['country']) ?></td>
              <td><span class="cat-badge"><?= htmlspecialchars($r['category']) ?></span></td>
              <td>
                <a href="products_search.php?tab=update&edit=<?= (int)$r['id'] ?>" class="btn-edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
              </td>
              <td>
                <form method="POST" action="products_search.php"
                      onsubmit="return confirm('Delete <?= addslashes(htmlspecialchars($r['name'])) ?>?')">
                  <input type="hidden" name="_action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit" class="btn-del"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB: ADD
  ══════════════════════════════════════════ -->
  <div id="tab-add" class="tab-panel <?= $activeTab==='add'?'active':'' ?>">
    <div class="card">
      <h2><i class="fa-solid fa-circle-plus"></i> Add New Product</h2>

      <?php if ($addSuccess): ?>
      <div class="msg-box msg-success">
        <i class="fa-solid fa-circle-check"></i>
        Product added successfully with ID <strong>#<?= (int)$addId ?></strong>.
      </div>
      <?php endif; ?>

      <?php if (!empty($addErrors)): ?>
      <ul class="err-list">
        <?php foreach ($addErrors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <form method="POST" action="products_search.php" onsubmit="return validateAdd()">
        <input type="hidden" name="_action" value="add">
        <div class="fg">
          <label>Product Name *</label>
          <input type="text" id="a-name" name="name" placeholder="e.g. iPhone 15"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" maxlength="255">
          <span class="fe" id="e-a-name"></span>
        </div>
        <div class="fg">
          <label>Description</label>
          <input type="text" id="a-desc" name="description" placeholder="Short description"
                 value="<?= htmlspecialchars($_POST['description'] ?? '') ?>" maxlength="500">
        </div>
        <div class="g2">
          <div class="fg">
            <label>Price ($) *</label>
            <input type="number" id="a-price" name="price" placeholder="99.99" min="0" step="0.01"
                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            <span class="fe" id="e-a-price"></span>
          </div>
          <div class="fg">
            <label>Country</label>
            <input type="text" id="a-country" name="country" placeholder="e.g. France"
                   value="<?= htmlspecialchars($_POST['country'] ?? '') ?>" maxlength="100">
          </div>
        </div>
        <div class="fg">
          <label>Category *</label>
          <select id="a-cat" name="category">
            <option value="">— Select —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c ?>" <?= (($_POST['category'] ?? '')===$c)?'selected':'' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
          <span class="fe" id="e-a-cat"></span>
        </div>
        <div class="fg">
          <label>Image URL (optional)</label>
          <input type="url" id="a-img" name="image_url" placeholder="https://…"
                 value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
          <span class="fe" id="e-a-img"></span>
        </div>
        <button type="submit" class="btn" style="width:100%;margin-top:8px;">
          <i class="fa-solid fa-plus"></i> Add to Catalogue
        </button>
      </form>
    </div>
  </div>

