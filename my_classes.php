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

// drop class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['drop_reg_id'])) {
    $regId = (int)$_POST['drop_reg_id'];

    $q = $db->prepare("SELECT course_id FROM registrations WHERE reg_id = ? AND user_id = ?");
    $q->execute([$regId, $userId]);
    $row = $q->fetch();
    if ($row) {
        $courseId = (int)$row['course_id'];
        $del = $db->prepare("DELETE FROM registrations WHERE reg_id = ?");
        $del->execute([$regId]);
        $messages[] = "Class removed from your schedule.";

        // promote first waitlisted user
        $wl = $db->prepare("SELECT wait_id, user_id FROM waitlist WHERE course_id = ? ORDER BY created_at ASC LIMIT 1");
        $wl->execute([$courseId]);
        $next = $wl->fetch();
        if ($next) {
            $db->beginTransaction();
            try {
                $ins = $db->prepare("INSERT INTO registrations (user_id, course_id, semester) VALUES (?, ?, ?)");
                $ins->execute([$next['user_id'], $courseId, 'Fall 2025']);
                $delwl = $db->prepare("DELETE FROM waitlist WHERE wait_id = ?");
                $delwl->execute([$next['wait_id']]);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }
}

// fetch registrations for user
$regs = $db->prepare("
  SELECT r.reg_id, c.course_code, c.course_name, c.credits, r.semester
  FROM registrations r
  JOIN courses c ON r.course_id = c.course_id
  WHERE r.user_id = ?
  ORDER BY c.course_code
");
$regs->execute([$userId]);
$registered = $regs->fetchAll();

// fetch waitlist entries for user
$wlq = $db->prepare("
  SELECT w.wait_id, c.course_code, c.course_name, w.created_at
  FROM waitlist w
  JOIN courses c ON w.course_id = c.course_id
  WHERE w.user_id = ?
  ORDER BY w.created_at
");
$wlq->execute([$userId]);
$waitlisted = $wlq->fetchAll();

require_once 'header.php';
?>
<div class="row">
  <div class="col-md-10">
    <h2>My Registered Classes</h2>
    <?php foreach ($messages as $m): ?><div class="alert alert-success"><?=htmlspecialchars($m)?></div><?php endforeach; ?>

    <?php if ($registered): ?>
      <table class="table table-striped">
        <thead><tr><th>Code</th><th>Name</th><th>Credits</th><th>Semester</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($registered as $r): ?>
            <tr>
              <td><?=htmlspecialchars($r['course_code'])?></td>
              <td><?=htmlspecialchars($r['course_name'])?></td>
              <td><?= (int)$r['credits'] ?></td>
              <td><?=htmlspecialchars($r['semester'])?></td>
              <td>
                <form method="post" class="form-inline" onsubmit="return confirm('Remove this class?');">
                  <input type="hidden" name="drop_reg_id" value="<?= (int)$r['reg_id'] ?>">
                  <button class="btn btn-danger btn-sm">Drop</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You have no registered classes.</p>
    <?php endif; ?>

    <h3 class="mt-4">My Waitlist</h3>
    <?php if ($waitlisted): ?>
      <table class="table">
        <thead><tr><th>Code</th><th>Name</th><th>Added</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($waitlisted as $w): ?>
            <tr>
              <td><?=htmlspecialchars($w['course_code'])?></td>
              <td><?=htmlspecialchars($w['course_name'])?></td>
              <td><?=htmlspecialchars($w['created_at'])?></td>
              <td>
                <form method="post" action="waitlist.php" class="form-inline">
                  <input type="hidden" name="leave_wait_id" value="<?= (int)$w['wait_id'] ?>">
                  <button class="btn btn-warning btn-sm">Leave Waitlist</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You are not on any waitlist.</p>
    <?php endif; ?>

  </div>
</div>
<?php require_once 'footer.php'; ?>
