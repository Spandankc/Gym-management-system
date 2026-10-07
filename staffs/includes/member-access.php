<?php
function fitness_require_assigned_member(mysqli $conn, int $member_id): void
{
    if ($member_id < 1) { http_response_code(404); exit('Member not found.'); }
    $trainer_id = (int)($_SESSION['uid'] ?? 0);
    $statement = $conn->prepare('SELECT id FROM members WHERE id = ? AND trainer_id = ?');
    $statement->bind_param('ii', $member_id, $trainer_id);
    $statement->execute();
    if ($statement->get_result()->num_rows === 0) { http_response_code(404); exit('Member not found.'); }
}
