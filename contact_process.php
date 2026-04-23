<?php
/* ============================================================
   contact_process.php — Traitement du formulaire Contact
   UsellBuy — ENSI 2025/2026
   Reçoit les données POST, revalide côté serveur, insère en BD
   et affiche un résumé HTML formaté.
   ============================================================ */

require_once 'db.php';

/* ── 1. Vérifier que la requête est bien POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

/* ── 2. Récupérer et nettoyer les données ── */
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name']  ?? '');
$email     = trim($_POST['email']      ?? '');
$phone     = trim($_POST['phone']      ?? '');
$message   = trim($_POST['message']    ?? '');

/* ── 3. Revalidation PHP (côté serveur) ── */
$errors = [];

$nameRegex  = '/^[a-zA-ZÀ-ÿ\s\-\']{2,50}$/u';
$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
$phoneRegex = '/^(\+?[0-9\s\-\(\)]{7,20})$/';

if ($firstName === '') {
    $errors[] = 'First name is required.';
} elseif (!preg_match($nameRegex, $firstName)) {
    $errors[] = 'First name must contain only letters (min 2 characters).';
}

if ($lastName === '') {
    $errors[] = 'Last name is required.';
} elseif (!preg_match($nameRegex, $lastName)) {
    $errors[] = 'Last name must contain only letters (min 2 characters).';
}

if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!preg_match($emailRegex, $email)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($phone !== '' && !preg_match($phoneRegex, $phone)) {
    $errors[] = 'Invalid phone number format.';
}

if ($message === '') {
    $errors[] = 'Message is required.';
} elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters long.';
} elseif (strlen($message) > 1000) {
    $errors[] = 'Message must not exceed 1000 characters.';
}

/* ── 4. Si erreurs → afficher et stopper ── */
if (!empty($errors)) {
    showPage('error', $errors, null);
    exit;
}

/* ── 5. Insertion en base de données via PDO (prepare + execute nommés) ── */
try {
    $pdo = getConnection();
    $sql = 'INSERT INTO contact_messages (first_name, last_name, email, message)
            VALUES (:first_name, :last_name, :email, :message)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name'  => $lastName,
        ':email'      => $email,
        ':message'    => $message,
    ]);
    $insertedId = $pdo->lastInsertId();

    /* Récupérer tous les messages pour affichage (fetchAll) */
    $allMessages = $pdo->query('SELECT * FROM contact_messages ORDER BY sent_at DESC')->fetchAll();

    showPage('success', [], [
        'id'         => $insertedId,
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
        'phone'      => $phone,
        'message'    => $message,
        'all'        => $allMessages,
    ]);
} catch (PDOException $e) {
    showPage('error', ['Database error: ' . $e->getMessage()], null);
}

/* ============================================================
   showPage() — Génère la page HTML de résultat
   ============================================================ */
function showPage(string $status, array $errors, ?array $data): void {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Contact Result</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .result-wrap { max-width:900px; margin:60px auto; padding:0 40px; }
    .result-card { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:40px; }
    .result-card h2 { margin-bottom:20px; }
    .result-card table { width:100%; border-collapse:collapse; margin-top:20px; }
    .result-card th, .result-card td { padding:12px 16px; border:1px solid #2a2a2a; text-align:left; }
    .result-card th { background:#1a1a1a; color:var(--yellow); font-weight:600; }
    .result-card td { color:#ccc; }
    .result-card tr:hover td { background:#1a1a1a; }
    .err-list { list-style:none; padding:0; }
    .err-list li { color:var(--error); padding:8px 0; border-bottom:1px solid #2a2a2a; }
    .err-list li::before { content:"✕  "; }
    .badge-ok  { background:#1a3a1a; color:#44cc44; padding:4px 10px; border-radius:20px; font-size:12px; }
    .badge-err { background:#3a1a1a; color:#ff7070; padding:4px 10px; border-radius:20px; font-size:12px; }
    .section-title { color:var(--yellow); font-size:14px; text-transform:uppercase; letter-spacing:1px; margin:30px 0 12px; }
    .all-table-wrap { overflow-x:auto; margin-top:10px; }
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

  <div class="result-wrap">
    <div class="result-card">

<?php if ($status === 'error'): ?>
      <h2><i class="fa-solid fa-circle-xmark" style="color:var(--error)"></i> &nbsp;Submission Failed</h2>
      <p style="color:#aaa;margin-bottom:20px;">Please correct the following errors and try again.</p>
      <ul class="err-list">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="contact.html" class="btn" style="display:inline-block;margin-top:30px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Contact
      </a>

<?php else: ?>
      <h2><i class="fa-solid fa-circle-check" style="color:#44cc44"></i> &nbsp;Message Sent Successfully!</h2>
      <p style="color:#aaa;">Your message has been saved. We will get back to you shortly.</p>

      <div class="section-title"><i class="fa-solid fa-receipt"></i> Your Submission (ID #<?= (int)$data['id'] ?>)</div>
      <table>
        <thead>
          <tr><th>Field</th><th>Value</th></tr>
        </thead>
        <tbody>
          <tr><td>First Name</td><td><?= htmlspecialchars($data['first_name']) ?></td></tr>
          <tr><td>Last Name</td><td><?= htmlspecialchars($data['last_name']) ?></td></tr>
          <tr><td>Email</td><td><?= htmlspecialchars($data['email']) ?></td></tr>
          <?php if ($data['phone']): ?>
          <tr><td>Phone</td><td><?= htmlspecialchars($data['phone']) ?></td></tr>
          <?php endif; ?>
          <tr><td>Message</td><td><?= nl2br(htmlspecialchars($data['message'])) ?></td></tr>
        </tbody>
      </table>

      <div class="section-title"><i class="fa-solid fa-table-list"></i> All Contact Messages</div>
      <div class="all-table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Message</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($data['all'] as $row): ?>
            <tr>
              <td><?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['first_name']) ?></td>
              <td><?= htmlspecialchars($row['last_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars(mb_strimwidth($row['message'], 0, 60, '…')) ?></td>
              <td><?= htmlspecialchars($row['sent_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="margin-top:30px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="contact.html" class="btn"><i class="fa-solid fa-envelope"></i> Send Another</a>
        <a href="index.html" class="btn" style="background:transparent;border:1px solid var(--yellow);color:var(--yellow);">
          <i class="fa-solid fa-house"></i> Home
        </a>
      </div>
<?php endif; ?>

    </div>
  </div>
</body>
</html>
<?php
}
?>
