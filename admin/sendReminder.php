<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$id = (int)($_GET['id'] ?? 0);
if ($id < 1) { http_response_code(404); exit('Member not found.'); }
$statement = $conn->prepare('UPDATE members SET reminder = 1 WHERE id = ?');
$statement->bind_param('i', $id);
$statement->execute();
$_SESSION['success'] = 'Alert Sent';
header('Location: payment.php');
exit;
