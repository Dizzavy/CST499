<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
// Production: restrict to admin users. For demo any logged-in user may add.
// not entirely sure this works as intended. local testing ran into errors.
$db = (new Database())->connect();
$messages = $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['course_code'] ?? '');
    $name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 3);
    $capacity = (int)($_POST['capacity'] ?? 0);

    if ($code === '' || $name === '') {
        $errors[] = "Course code and name are required.";
    } else {
        $ins = $db->prepare("INSERT INTO courses (course_code, course_name, credits, capacity) VALUES (?, ?, ?, ?)");
        $ins->execute([$code, $name, $credits, $capacity]);
        $messages[] = "Course added.";
    }
}

$courses = $db->query("SELECT * FROM courses ORDER BY course_code")->fetchAll();
require_once 'header.php';
?>
<div class="row">
  <div class="col-md-10">
    <h2>Add Course</h2>
    <?php foreach ($messages as $m): ?><div class="alert alert-success"><?=htmlspecialchars($m)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <form method="post" class="form-inline">
      <div class="form-group">
        <label>Course Code</label>
        <input name="course_code" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Course Name</label>
        <input name="course_name" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Credits</label>
        <input name="credits" type="number" class="form-control" value="3" style="width:80px;">
      </div>
      <div class="form-group">
        <label>Capacity</label>
        <input name="capacity" type="number" class="form-control" value="30" style="width:100px;">
      </div>
      <button class="btn btn-primary">Add Course</button>
    </form>

    <h3 class="mt-4">Existing Courses</h3>
    <table class="table">
      <thead><tr><th>Code</th><th>Name</th><th>Credits</th><th>Capacity</th></tr></thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['course_code'])?></td>
            <td><?=htmlspecialchars($c['course_name'])?></td>
            <td><?= (int)$c['credits'] ?></td>
            <td><?= ($c['capacity'] == 0 ? 'Unlimited' : (int)$c['capacity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>
<?php require_once 'footer.php'; ?>
