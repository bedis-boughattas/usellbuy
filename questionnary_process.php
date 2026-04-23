<?php
/* ============================================================
   questionnary_process.php — Traitement du formulaire Questionnaire
   UsellBuy — ENSI 2025/2026
   Revalide côté serveur, insère en BD, affiche résumé HTML.
   Démontre : classe PHP, tableau d'objets, fonction d'affichage.
   ============================================================ */

require_once 'db.php';

/* ============================================================
   CLASSE QuestionnaireResponse
   Représente un enregistrement de la table questionnaire_responses
   ============================================================ */
class QuestionnaireResponse {
    private int    $id;
    private string $fullName;
    private string $email;
    private string $satisfaction;
    private string $featuresUsed;
    private int    $rating;
    private string $frequency;
    private int    $recommendation;
    private string $comments;
    private string $submittedAt;

    public function __construct(
        int    $id,
        string $fullName,
        string $email,
        string $satisfaction,
        string $featuresUsed,
        int    $rating,
        string $frequency,
        int    $recommendation,
        string $comments,
        string $submittedAt = ''
    ) {
        $this->id             = $id;
        $this->fullName       = $fullName;
        $this->email          = $email;
        $this->satisfaction   = $satisfaction;
        $this->featuresUsed   = $featuresUsed;
        $this->rating         = $rating;
        $this->frequency      = $frequency;
        $this->recommendation = $recommendation;
        $this->comments       = $comments;
        $this->submittedAt    = $submittedAt;
    }

    /* ── Getters ── */
    public function getId(): int            { return $this->id; }
    public function getFullName(): string   { return $this->fullName; }
    public function getEmail(): string      { return $this->email; }
    public function getSatisfaction(): string { return $this->satisfaction; }
    public function getFeaturesUsed(): string { return $this->featuresUsed; }
    public function getRating(): int        { return $this->rating; }
    public function getFrequency(): string  { return $this->frequency; }
    public function getRecommendation(): int { return $this->recommendation; }
    public function getComments(): string   { return $this->comments; }
    public function getSubmittedAt(): string { return $this->submittedAt; }

    /* ── Setters ── */
    public function setFullName(string $v): void   { $this->fullName = $v; }
    public function setEmail(string $v): void      { $this->email = $v; }
    public function setSatisfaction(string $v): void { $this->satisfaction = $v; }
    public function setRating(int $v): void        { $this->rating = max(1, min(5, $v)); }
    public function setRecommendation(int $v): void { $this->recommendation = max(0, min(10, $v)); }
    public function setComments(string $v): void   { $this->comments = $v; }

    /* ── Méthode utilitaire : étoiles HTML ── */
    public function getStarsHtml(): string {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $this->rating
                ? '<span style="color:var(--yellow)">★</span>'
                : '<span style="color:#444">★</span>';
        }
        return $stars;
    }

    /* ── Méthode utilitaire : badge satisfaction ── */
    public function getSatisfactionLabel(): string {
        $map = [
            'tres-satisfait' => '😊 Very Satisfied',
            'satisfait'      => '🙂 Satisfied',
            'neutre'         => '😐 Neutral',
            'insatisfait'    => '😞 Dissatisfied',
        ];
        return $map[$this->satisfaction] ?? $this->satisfaction;
    }
}

/* ============================================================
   FONCTION displayResponsesTable()
   Parcourt un tableau d'objets QuestionnaireResponse et génère
   un tableau HTML avec structures d'itération et de sélection.
   ============================================================ */
function displayResponsesTable(array $responses): void {
    if (empty($responses)) {
        echo '<p style="color:#aaa;">No responses found.</p>';
        return;
    }
    echo '<table>';
    echo '<thead><tr>
            <th>#</th><th>Name</th><th>Email</th>
            <th>Satisfaction</th><th>Rating</th>
            <th>Frequency</th><th>Recommend</th>
            <th>Comments</th><th>Date</th>
          </tr></thead>';
    echo '<tbody>';
    foreach ($responses as $r) {
        /* Sélection : couleur selon satisfaction */
        if ($r->getSatisfaction() === 'tres-satisfait') {
            $rowStyle = 'background:#0d1f0d;';
        } elseif ($r->getSatisfaction() === 'insatisfait') {
            $rowStyle = 'background:#1f0d0d;';
        } else {
            $rowStyle = '';
        }
        echo '<tr style="' . $rowStyle . '">';
        echo '<td>' . $r->getId() . '</td>';
        echo '<td>' . htmlspecialchars($r->getFullName()) . '</td>';
        echo '<td>' . htmlspecialchars($r->getEmail()) . '</td>';
        echo '<td>' . $r->getSatisfactionLabel() . '</td>';
        echo '<td>' . $r->getStarsHtml() . '</td>';
        echo '<td>' . htmlspecialchars($r->getFrequency()) . '</td>';
        echo '<td style="text-align:center;">' . $r->getRecommendation() . '/10</td>';
        echo '<td>' . htmlspecialchars(mb_strimwidth($r->getComments(), 0, 60, '…')) . '</td>';
        echo '<td>' . htmlspecialchars($r->getSubmittedAt()) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

/* ── Vérifier méthode POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: questionnary.html');
    exit;
}

/* ── Récupérer et nettoyer les données ── */
$fullName       = trim($_POST['full_name']      ?? '');
$email          = trim($_POST['email']          ?? '');
$satisfaction   = trim($_POST['satisfaction']   ?? '');
$featuresRaw    = $_POST['features']            ?? [];
$note           = (int)($_POST['note']          ?? 0);
$frequency      = trim($_POST['frequency']      ?? '');
$recommendation = (int)($_POST['recommendation'] ?? 7);
$comments       = trim($_POST['comments']       ?? '');

$featuresUsed = is_array($featuresRaw) ? implode(',', $featuresRaw) : '';

/* ── Revalidation PHP ── */
$errors = [];

$nameRegex  = '/^[a-zA-ZÀ-ÿ\s\-\']{3,80}$/u';
$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
$validSatisfactions = ['tres-satisfait', 'satisfait', 'neutre', 'insatisfait'];

if ($fullName === '') {
    $errors[] = 'Full name is required.';
} elseif (!preg_match($nameRegex, $fullName)) {
    $errors[] = 'Full name must contain only letters (min 3 characters).';
}

if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!preg_match($emailRegex, $email)) {
    $errors[] = 'Please enter a valid email address.';
}

if (!in_array($satisfaction, $validSatisfactions)) {
    $errors[] = 'Please select a satisfaction level.';
}

if ($note < 1 || $note > 5) {
    $errors[] = 'Please provide a rating between 1 and 5 stars.';
}

if ($recommendation < 0 || $recommendation > 10) {
    $errors[] = 'Recommendation score must be between 0 and 10.';
}

if ($comments === '') {
    $errors[] = 'Comments are required.';
} elseif (strlen($comments) < 20) {
    $errors[] = 'Comments must be at least 20 characters long.';
} elseif (strlen($comments) > 500) {
    $errors[] = 'Comments must not exceed 500 characters.';
}

/* ── Afficher erreurs si présentes ── */
if (!empty($errors)) {
    renderPage('error', $errors, null, []);
    exit;
}

/* ── Insertion en BD (prepare + execute nommés) ── */
try {
    $pdo = getConnection();
    $sql = 'INSERT INTO questionnaire_responses
                (full_name, email, satisfaction, features_used, rating, frequency, recommendation, comments)
            VALUES
                (:full_name, :email, :satisfaction, :features_used, :rating, :frequency, :recommendation, :comments)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'       => $fullName,
        ':email'           => $email,
        ':satisfaction'    => $satisfaction,
        ':features_used'   => $featuresUsed,
        ':rating'          => $note,
        ':frequency'       => $frequency,
        ':recommendation'  => $recommendation,
        ':comments'        => $comments,
    ]);
    $insertedId = $pdo->lastInsertId();

    /* Récupérer tous les enregistrements (fetchAll) et créer des objets */
    $rows = $pdo->query('SELECT * FROM questionnaire_responses ORDER BY submitted_at DESC')->fetchAll();

    /* Tableau d'objets QuestionnaireResponse */
    $responseObjects = [];
    foreach ($rows as $row) {
        $responseObjects[] = new QuestionnaireResponse(
            (int)$row['id'],
            $row['full_name'],
            $row['email'],
            $row['satisfaction'],
            $row['features_used'],
            (int)$row['rating'],
            $row['frequency'],
            (int)$row['recommendation'],
            $row['comments'],
            $row['submitted_at']
        );
    }

    $newResponse = new QuestionnaireResponse(
        (int)$insertedId, $fullName, $email, $satisfaction,
        $featuresUsed, $note, $frequency, $recommendation, $comments
    );

    renderPage('success', [], $newResponse, $responseObjects);

} catch (PDOException $e) {
    renderPage('error', ['Database error: ' . $e->getMessage()], null, []);
}

/* ============================================================
   renderPage() — Génère la page HTML de résultat
   ============================================================ */
function renderPage(string $status, array $errors, ?QuestionnaireResponse $newResp, array $allResponses): void {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UsellBuy — Questionnaire Result</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .result-wrap { max-width:1000px; margin:60px auto; padding:0 40px; }
    .result-card { background:#111; border:1px solid #1f1f1f; border-radius:12px; padding:40px; }
    .result-card table { width:100%; border-collapse:collapse; margin-top:16px; }
    .result-card th, .result-card td { padding:12px 16px; border:1px solid #2a2a2a; text-align:left; }
    .result-card th { background:#1a1a1a; color:var(--yellow); font-weight:600; }
    .result-card td { color:#ccc; }
    .result-card tr:hover td { background:#1a1a1a; }
    .err-list { list-style:none; padding:0; }
    .err-list li { color:var(--error); padding:8px 0; border-bottom:1px solid #2a2a2a; }
    .err-list li::before { content:"✕  "; }
    .section-title { color:var(--yellow); font-size:14px; text-transform:uppercase; letter-spacing:1px; margin:30px 0 12px; }
    .all-table-wrap { overflow-x:auto; }
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
      <ul class="err-list">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="questionnary.html" class="btn" style="display:inline-block;margin-top:24px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Questionnaire
      </a>

<?php else: ?>
      <h2><i class="fa-solid fa-circle-check" style="color:#44cc44"></i> &nbsp;Thank you for your feedback!</h2>
      <p style="color:#aaa;">Your response has been recorded. We read every submission carefully.</p>

      <div class="section-title"><i class="fa-solid fa-receipt"></i> Your Submission (ID #<?= $newResp->getId() ?>)</div>
      <table>
        <thead><tr><th>Field</th><th>Value</th></tr></thead>
        <tbody>
          <tr><td>Full Name</td><td><?= htmlspecialchars($newResp->getFullName()) ?></td></tr>
          <tr><td>Email</td><td><?= htmlspecialchars($newResp->getEmail()) ?></td></tr>
          <tr><td>Satisfaction</td><td><?= $newResp->getSatisfactionLabel() ?></td></tr>
          <tr><td>Features Used</td><td><?= htmlspecialchars($newResp->getFeaturesUsed()) ?></td></tr>
          <tr><td>Rating</td><td><?= $newResp->getStarsHtml() ?></td></tr>
          <tr><td>Frequency</td><td><?= htmlspecialchars($newResp->getFrequency()) ?></td></tr>
          <tr><td>Recommendation</td><td><?= $newResp->getRecommendation() ?>/10</td></tr>
          <tr><td>Comments</td><td><?= nl2br(htmlspecialchars($newResp->getComments())) ?></td></tr>
        </tbody>
      </table>

      <div class="section-title"><i class="fa-solid fa-table-list"></i> All Questionnaire Responses</div>
      <div class="all-table-wrap">
        <?php displayResponsesTable($allResponses); ?>
      </div>

      <div style="margin-top:30px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="questionnary.html" class="btn"><i class="fa-solid fa-clipboard-list"></i> New Response</a>
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
