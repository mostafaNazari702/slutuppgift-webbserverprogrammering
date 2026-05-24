<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/events.php');
}
csrf_check();

$me = current_user();
$event_id = (int)($_POST['event_id'] ?? 0);

if ($event_id <= 0) {
    flash('error', 'Ogiltigt event.');
    redirect('/events.php');
}

$stmt = db()->prepare(
    "SELECT e.id, e.max_participants,
            (SELECT COUNT(*) FROM registrations r
             WHERE r.event_id = e.id AND r.status = 'registered') AS num
     FROM events e WHERE e.id = ? AND e.event_date >= NOW()"
);
$stmt->execute([$event_id]);
$ev = $stmt->fetch();

if (!$ev) {
    flash('error', 'Eventet kunde inte hittas.');
    redirect('/events.php');
}

if ($ev['max_participants'] !== null && $ev['num'] >= $ev['max_participants']) {
    flash('error', 'Eventet är fullt.');
    redirect('/events.php');
}

try {
    $ins = db()->prepare(
        "INSERT INTO registrations (user_id, event_id, status)
         VALUES (?, ?, 'registered')
         ON DUPLICATE KEY UPDATE status = 'registered'"
    );
    $ins->execute([$me['id'], $event_id]);
    flash('success', 'Du är anmäld!');
} catch (PDOException $e) {
    flash('error', 'Något gick fel vid anmälan.');
}

redirect('/user/bookings.php');
