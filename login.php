<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $errors[] = "Username and password required.";
    } else {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = :u OR email = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Invalid credentials.";
        }
    }
}
require_once 'header.php';
?>
<div class="row">
  <div class="col-md-6">
    <h2>Login</h2>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?=htmlspecialchars($errors[0])?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>Username or Email</label>
        <input name="username" class="form-control">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input name="password" type="password" class="form-control">
      </div>
      <button class="btn btn-primary">Login</button>
    </form>
  </div>
</div>
<?php require_once 'footer.php'; ?>
