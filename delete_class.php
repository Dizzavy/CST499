<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$db = (new Database())->connect();
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reg_id'])) {
    $regId = (int)$_POST['reg_id'];
    $q = $db->prepare("SELECT course_id FROM registrations WHERE reg_id = ? AND user_id = ?");
    $q->execute([$regId, $userId]);
    $row = $q->fetch();
    if ($row) {
        $courseId = (int)$row['course_id'];
        $del = $db->prepare("DELETE FROM registrations WHERE reg_id = ?");
        $del->execute([$regId]);

        // promote next waitlisted
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
header("Location: my_classes.php");
exit;