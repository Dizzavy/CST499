<?php
require_once  'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$db = (new Database())->connect();
$userId = (int)$_SESSION['user_id'];
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['join_course_id'])) {
        $courseId = (int)$_POST['join_course_id'];
        // ensure not registered
        $chkReg = $db->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND course_id = ?");
        $chkReg->execute([$userId, $courseId]);
        if ($chkReg->fetch()) {
            $errors[] = "You are already registered for this course.";
        } else {
            // capacity check
            $cnt = $db->prepare("SELECT COUNT(*) FROM registrations WHERE course_id = ?");
            $cnt->execute([$courseId]);
            $enrolled = (int)$cnt->fetchColumn();
            $capQ = $db->prepare("SELECT capacity FROM courses WHERE course_id = ?");
            $capQ->execute([$courseId]);
            $cap = (int)$capQ->fetchColumn();
            if ($cap > 0 && $enrolled < $cap) {
                $errors[] = "Course has available seats; use Register Classes to enroll directly.";
            } else {
                $chk = $db->prepare("SELECT 1 FROM waitlist WHERE user_id = ? AND course_id = ?");
                $chk->execute([$userId, $courseId]);
                if ($chk->fetch()) {
                    $errors[] = "You are already on the waitlist for this course.";
                } else {
                    $ins = $db->prepare("INSERT INTO waitlist (user_id, course_id) VALUES (?, ?)");
                    $ins->execute([$userId, $courseId]);
                    $messages[] = "You have been added to the waitlist.";
                }
            }
        }
    } elseif (!empty($_POST['leave_wait_id'])) {
        $waitId = (int)$_POST['leave_wait_id'];
        $del = $db->prepare("DELETE FROM waitlist WHERE wait_id = ? AND user_id = ?");
        $del->execute([$waitId, $userId]);
        $messages[] = "You have left the waitlist.";
    }
}

// course list with wait counts
$list = $db->query("
  SELECT c.course_id, c.course_code, c.course_name, c.capacity,
    (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.course_id) AS enrolled,
    (SELECT COUNT(*) FROM waitlist w WHERE w.course_id = c.course_id) AS wait_count
  FROM courses c
  ORDER BY c.course_code
")->fetchAll();

// map waitlist entries for this user
$wait_map = [];
$stmt = $db->prepare("SELECT wait_id, course_id FROM waitlist WHERE user_id = ?");
$stmt->execute([$userId]);
while ($r = $stmt->fetch()) {
    $wait_map[$r['course_id']] = $r['wait_id'];
}

require_once  'header.php';
?>
<div class="row">
  <div class="col-md-10">
    <h2>Waitlist Management</h2>
    <?php foreach ($messages as $m): ?><div class="alert alert-success"><?=htmlspecialchars($m)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <table class="table">
      <thead><tr><th>Code</th><th>Name</th><th>Enrolled</th><th>Capacity</th><th>Waitlist</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($list as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['course_code'])?></td>
            <td><?=htmlspecialchars($c['course_name'])?></td>
            <td><?= (int)$c['enrolled'] ?></td>
            <td><?= ($c['capacity']==0 ? 'Unlimited' : (int)$c['capacity']) ?></td>
            <td><?= (int)$c['wait_count'] ?></td>
            <td>
              <?php if (isset($wait_map[$c['course_id']])): ?>
                <form method="post" class="form-inline">
                  <input type="hidden" name="leave_wait_id" value="<?= (int)$wait_map[$c['course_id']] ?>">
                  <button class="btn btn-sm btn-warning">Leave Waitlist</button>
                </form>
              <?php else: ?>
                <form method="post" class="form-inline">
                  <input type="hidden" name="join_course_id" value="<?= (int)$c['course_id'] ?>">
                  <button class="btn btn-sm btn-primary">Join Waitlist</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>
<?php require_once 'footer.php'; ?>
