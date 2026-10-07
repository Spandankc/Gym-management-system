<?php

function fitness_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function fitness_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fitness_require_role(string $role, string $login = 'index.php'): void
{
    fitness_start_session();
    if (empty($_SESSION['is_login']) || ($_SESSION['role'] ?? '') !== $role || empty($_SESSION['uid'])) {
        $_SESSION['error'] = 'Please log in to access this page.';
        header('Location: ' . $login);
        exit;
    }
}

function fitness_authenticate(mysqli $conn, string $role, string $username, string $password): bool
{
    $tables = ['member' => 'members', 'admin' => 'admin', 'trainer' => 'staffs'];
    if (!isset($tables[$role]) || $username === '' || $password === '') {
        return false;
    }
    $statement = $conn->prepare('SELECT id, username, password FROM ' . $tables[$role] . ' WHERE username = ? LIMIT 1');
    $statement->bind_param('s', $username);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();
    if (!$user) {
        return false;
    }
    $stored = $user['password'];
    $valid = password_verify($password, $stored);
    // Existing accounts in the supplied database use MD5, or plaintext for trainers.
    if (!$valid && preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        $valid = hash_equals(strtolower($stored), md5($password));
    } elseif (!$valid && $role === 'trainer' && password_get_info($stored)['algo'] === null) {
        $valid = hash_equals($stored, $password);
    }
    if (!$valid) {
        return false;
    }
    fitness_start_session();
    session_regenerate_id(true);
    unset($_SESSION['error'], $_SESSION['signup_data']);
    $_SESSION['user'] = $user['username'];
    $_SESSION['uid'] = (int) $user['id'];
    $_SESSION['is_login'] = true;
    $_SESSION['role'] = $role;
    return true;
}

fitness_start_session();
