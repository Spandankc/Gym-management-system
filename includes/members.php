<?php

function fitness_create_member(mysqli $conn, array $data): int
{
    $conn->begin_transaction();
    try {
        $date = $data['dor'] ?? date('Y-m-d');
        $service = (int) $data['services'];
        $plan = (string) $data['plan'];
        $statement = $conn->prepare('INSERT INTO members (fullname, username, password, contact, address, gender, plan, services_id, dor, pay_date, plan_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, \'\')');
        $statement->bind_param('sssssssis', $data['fullname'], $data['username'], $data['password_hash'], $data['contact'], $data['address'], $data['gender'], $plan, $service, $date);
        $statement->execute();
        $id = $conn->insert_id;
        // The supplied dump also has a trigger; this works with and without it.
        $progress = $conn->prepare('INSERT INTO progress (ini_weight, curr_weight, ini_bodytype, curr_bodytype, member_id) SELECT 0, 0, \'\', \'\', ? WHERE NOT EXISTS (SELECT 1 FROM progress WHERE member_id = ?)');
        $progress->bind_param('ii', $id, $id);
        $progress->execute();
        $conn->commit();
        return $id;
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}
