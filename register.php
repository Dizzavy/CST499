<?php
// Registration page, built off old project works as intended for the most part.
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (username, email, password) VALUES (:u, :e, :p)");
            $ins->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
            $_SESSION['user'] = $username;
            $_SESSION['user_id'] = $db->lastInsertId();
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
        <?php foreach ($errors as $err) echo '<div>' . htmlspecialchars($err) . '</div>'; ?>
      </div>
    <?php endif; ?>
    <form method="post" novalidate>
      <div class="form-group">
        <label>Username</label>
        <input name="username" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input name="email" type="email" class="form-control" required>
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
<?php require_once 'footer.php';
 ?>
