<?php

function fitness_record_payment(mysqli $conn, int $memberId, int $plan, float $amount, string $role, int $actorId): int
{
    if (!in_array($plan, [1, 3, 6, 12], true) || !is_finite($amount) || $amount <= 0
        || $amount > 10000000 || !in_array($role, ['admin', 'trainer'], true)) {
        throw new InvalidArgumentException('Invalid payment details.');
    }
    $amount = round($amount, 2);
    if ($amount <= 0) {
        throw new InvalidArgumentException('Payment amount must be at least 0.01.');
    }
    $total = round($amount * $plan, 2);
    $date = date('Y-m-d');
    $conn->begin_transaction();
    try {
        $member = $conn->prepare('SELECT id FROM members WHERE id = ? FOR UPDATE');
        $member->bind_param('i', $memberId);
        $member->execute();
        if (!$member->get_result()->fetch_assoc()) {
            throw new RuntimeException('Member not found.');
        }
        $payment = $conn->prepare('INSERT INTO payments (member_id, monthly_amount, months, total_amount, paid_on, recorded_by, recorded_role) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $payment->bind_param('ididsis', $memberId, $amount, $plan, $total, $date, $actorId, $role);
        $payment->execute();
        $receiptId = $conn->insert_id;
        $update = $conn->prepare("UPDATE members SET plan = ?, pay_date = ?, reminder = 0, status = 'active' WHERE id = ?");
        $update->bind_param('isi', $plan, $date, $memberId);
        $update->execute();
        $conn->commit();
        return $receiptId;
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}

function fitness_payment_history(mysqli $conn, int $memberId): array
{
    $query = $conn->prepare('SELECT * FROM payments WHERE member_id = ? ORDER BY paid_on DESC, id DESC');
    $query->bind_param('i', $memberId);
    $query->execute();
    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}
