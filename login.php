<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Please enter both username/email and password.";
    } else {

        $db = (new Database())->connect();

        $sql = "SELECT id, username, password
                FROM users
                WHERE LOWER(username) = LOWER(:u1)
                   OR LOWER(email) = LOWER(:u2)
                LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':u1' => $username,
            ':u2' => $username
        ]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit;

        } else {
            $errors[] = "Invalid username/email or password.";
        }
    }
}

require_once 'header.php';
?>
<div class="row">
  <div class="col-md-6">
    <h2>Login</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
          <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-group">
        <label>Username or Email</label>
        <input name="username" class="form-control"
               value="<?= htmlspecialchars($username ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <button class="btn btn-primary">Login</button>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>
