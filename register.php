<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Basic validation
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = "All fields are required.";
    }
    elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = "Username must be 3–30 characters (letters, numbers, underscore).";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }
    else {

        $db = (new Database())->connect();

        // Check if username or email already exists
        $stmt = $db->prepare("
            SELECT id 
            FROM users 
            WHERE username = :u OR email = :e
        ");
        $stmt->execute([':u' => $username, ':e' => $email]);

        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        } else {

            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $ins = $db->prepare("
                INSERT INTO users (username, email, password) 
                VALUES (:u, :e, :p)
            ");
            $ins->execute([
                ':u' => $username,
                ':e' => $email,
                ':p' => $hash
            ]);

            $newId = $db->lastInsertId();

            // Secure session
            session_regenerate_id(true);

            $_SESSION['user_id']  = $newId;
            $_SESSION['username'] = $username;

            header("Location: index.php");
            exit;
        }
    }
}

require_once 'header.php';
?>
<div class="row">
  <div class="col-md-6">
    <h2>Register</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
          <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-group">
        <label>Username</label>
        <input name="username" class="form-control" value="<?= htmlspecialchars($username ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input name="confirm" type="password" class="form-control" required>
      </div>

      <button class="btn btn-primary">Register</button>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>
