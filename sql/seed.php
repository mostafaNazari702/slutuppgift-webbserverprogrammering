<?php

require_once __DIR__ . '/../includes/db.php';

$testPassword = 'Test1234!';
$hash = password_hash($testPassword, PASSWORD_DEFAULT);

$pdo = db();
$stmt = $pdo->prepare(
    "INSERT INTO users (username, email, password_hash, role, verified)
     VALUES (?, ?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), verified = 1"
);

$stmt->execute(['moderator', 'mod@test.com', $hash, 'moderator']);
$stmt->execute(['klatter_christian', 'christian@test.com', $hash, 'user']);

$christianId = (int)$pdo->query("SELECT id FROM users WHERE username = 'klatter_christian'")->fetchColumn();
$moderatorId = (int)$pdo->query("SELECT id FROM users WHERE username = 'moderator'")->fetchColumn();

$pdo->prepare("UPDATE routes SET created_by = ? WHERE created_by IS NULL")->execute([$moderatorId]);
$pdo->prepare("UPDATE events SET created_by = ? WHERE created_by IS NULL")->execute([$moderatorId]);

$check = $pdo->prepare("SELECT COUNT(*) FROM sends WHERE user_id = ?");
$check->execute([$christianId]);
if ((int)$check->fetchColumn() === 0) {
    $pdo->prepare(
        "INSERT INTO sends (user_id, route_id, attempts, send_date, note)
         VALUES (?, 1, 1, CURDATE() - INTERVAL 3 DAY, 'Flash!'),
                (?, 2, 3, CURDATE() - INTERVAL 1 DAY, 'Crux tog tid')"
    )->execute([$christianId, $christianId]);
}

echo "<h1>Klart</h1>";
echo "<p>Testanvändare skapade eller uppdaterade:</p>";
echo "<ul>
        <li><strong>moderator</strong> / mod@test.com (moderator)</li>
        <li><strong>klatter_christian</strong> / christian@test.com (user)</li>
      </ul>";
echo "<p>Lösenord för båda: <code>Test1234!</code></p>";