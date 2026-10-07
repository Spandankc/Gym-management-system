<?php
require_once __DIR__ . '/includes/auth.php';
date_default_timezone_set('Asia/Kathmandu');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // TCP avoids accidentally connecting to another MySQL installation's socket.
    $conn = new mysqli(
        getenv('FITNESS_DB_HOST') ?: '127.0.0.1',
        getenv('FITNESS_DB_USER') ?: 'root',
        getenv('FITNESS_DB_PASSWORD') ?: '',
        getenv('FITNESS_DB_NAME') ?: 'gymnsb',
        (int) (getenv('FITNESS_DB_PORT') ?: 3308)
    );
    $conn->set_charset('utf8mb4');
    $conn->query("SET time_zone = '+05:45'");
} catch (mysqli_sql_exception $error) {
    error_log('Fitness Hub database connection: ' . $error->getMessage());
    http_response_code(503);
    exit('Fitness Hub cannot connect to its database. Please start MySQL in XAMPP on port 3308 and make sure the gymnsb database is imported.');
}

// Expiry includes all offered durations and renewals; reminders do not set status.
$conn->query("UPDATE members SET status = CASE WHEN DATE_ADD(COALESCE(NULLIF(pay_date, '0000-00-00'), dor), INTERVAL CAST(plan AS UNSIGNED) MONTH) <= CURDATE() THEN 'expired' ELSE 'active' END WHERE LOWER(status) IN ('active', 'expired')");
