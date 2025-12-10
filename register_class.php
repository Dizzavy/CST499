<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$db = (new Database())->connect();
$userId = (int)$_SESSION['user_id'];
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['course_id'])) {
    $courseId = (int)$_POST['course_id'];
    $semester = $_POST['semester'] ?? 'Fall 2025';

    // already registered?
    $check = $db->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND course_id = ?");
    $check->execute([$userId, $courseId]);
    if ($check->fetch()) {
        $errors[] = "You are already registered for this course.";
    } else {
        // current enrolled count
        $count = $db->prepare("SELECT COUNT(*) FROM registrations WHERE course_id = ?");
        $count->execute([$courseId]);
        $cnt = (int)$count->fetchColumn();

        // capacity
        $stmtCap = $db->prepare("SELECT capacity FROM courses WHERE course_id = ?");
        $stmtCap->execute([$courseId]);
        $cap = (int)$stmtCap->fetchColumn();

        if ($cap > 0 && $cnt >= $cap) {
            // add to waitlist if not already
            $chkwl = $db->prepare("SELECT 1 FROM waitlist WHERE user_id = ? AND course_id = ?");
            $chkwl->execute([$userId, $courseId]);
            if ($chkwl->fetch()) {
                $errors[] = "You are already on the waitlist for this course.";
            
            } else {
                $ins = $db->prepare("INSERT INTO waitlist (user_id, course_id) VALUES (?, ?)");
                $ins->execute([$userId, $courseId]);
                $messages[] = "Course is full. You have been added to the waitlist.";
            }
        } else {
            // register
            $ins = $db->prepare("INSERT INTO registrations (user_id, course_id, semester) VALUES (?, ?, ?)");
            $ins->execute([$userId, $courseId, $semester]);
            $messages[] = "Successfully registered for the course.";
        }
    }
}

// fetch courses and enrollment counts
$courses = $db->query("
  SELECT c.course_id, c.course_code, c.course_name, c.credits, c.capacity,
    (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.course_id) AS enrolled
  FROM courses c
  ORDER BY c.course_code
")->fetchAll();

require_once  'header.php';
?>
<div class="row">
  <div class="col-md-10">
    <h2>Available Courses</h2>

    <?php foreach ($messages as $m): ?><div class="alert alert-success"><?=htmlspecialchars($m)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <table class="table table-bordered">
      <thead><tr><th>Code</th><th>Name</th><th>Credits</th><th>Capacity</th><th>Enrolled</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['course_code'])?></td>
            <td><?=htmlspecialchars($c['course_name'])?></td>
            <td><?= (int)$c['credits'] ?></td>
            <td><?= ($c['capacity'] == 0 ? 'Unlimited' : (int)$c['capacity']) ?></td>
            <td><?= (int)$c['enrolled'] ?></td>
            <td>
              <form method="post" class="form-inline">
                <input type="hidden" name="course_id" value="<?= (int)$c['course_id'] ?>">
                <input type="hidden" name="semester" value="Fall 2025">
                <button class="btn btn-sm btn-primary">Register</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>
<?php require_once 'footer.php'; ?>
