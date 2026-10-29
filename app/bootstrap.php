<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(array(
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ));
    session_start();
}

define('APP_NAME', 'FCWEM Student Support');
define('DB_NAME', getenv('FCWEM_DB_NAME') ?: 'fcwem_complaints');
define('DB_HOST', getenv('FCWEM_DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('FCWEM_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('FCWEM_DB_PASSWORD') ?: '');

function db()
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $server = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASSWORD,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $connection = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASSWORD,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );
    initialize_database($connection);

    return $connection;
}

function initialize_database(PDO $pdo)
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS departments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            matric_number VARCHAR(50) NULL UNIQUE,
            department_id INT UNSIGNED NULL,
            role ENUM('student', 'department', 'admin') NOT NULL DEFAULT 'student',
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_users_department FOREIGN KEY (department_id)
                REFERENCES departments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tickets (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference_number VARCHAR(24) NOT NULL UNIQUE,
            student_id INT UNSIGNED NOT NULL,
            department_id INT UNSIGNED NULL,
            category VARCHAR(100) NOT NULL,
            subject VARCHAR(180) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('Submitted', 'Under Review', 'In Progress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Submitted',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tickets_student (student_id),
            INDEX idx_tickets_department (department_id),
            INDEX idx_tickets_status (status),
            CONSTRAINT fk_tickets_student FOREIGN KEY (student_id)
                REFERENCES users(id) ON DELETE RESTRICT,
            CONSTRAINT fk_tickets_department FOREIGN KEY (department_id)
                REFERENCES departments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ticket_updates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ticket_id BIGINT UNSIGNED NOT NULL,
            author_id INT UNSIGNED NULL,
            status VARCHAR(30) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_updates_ticket (ticket_id, created_at),
            CONSTRAINT fk_ticket_updates_ticket FOREIGN KEY (ticket_id)
                REFERENCES tickets(id) ON DELETE CASCADE,
            CONSTRAINT fk_ticket_updates_author FOREIGN KEY (author_id)
                REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB"
    );

    $departments = array(
        'Wildlife Management',
        'Ecotourism and Wildlife Recreation',
        'Environmental Management',
        'Fisheries and Aquaculture',
        'Forestry and Wood Technology',
        'General Administration',
    );
    $insert = $pdo->prepare('INSERT IGNORE INTO departments (name) VALUES (?)');
    foreach ($departments as $department) {
        $insert->execute(array($department));
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function text_length($value)
{
    $value = (string) $value;
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? strlen($value) : $count;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $submitted = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if ($submitted === '' || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('Your session expired or the form was invalid. Please go back, refresh, and try again.');
    }
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function take_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function current_user()
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_role($roles)
{
    $user = current_user();
    if (!$user || !in_array($user['role'], (array) $roles, true)) {
        set_flash('warning', 'Please sign in with an account that has access to that page.');
        redirect('?page=login');
    }

    return $user;
}

function departments(PDO $pdo)
{
    return $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
}

function ticket_statuses()
{
    return array('Submitted', 'Under Review', 'In Progress', 'Resolved', 'Closed');
}

function ticket_categories()
{
    return array(
        'Academic and examinations',
        'Student records and registration',
        'Fees and payments',
        'Hostel and accommodation',
        'Campus facilities and utilities',
        'Safety, security, and wellbeing',
        'IT and learning resources',
        'Staff conduct',
        'Other request or complaint',
    );
}

function is_duplicate_error(PDOException $exception)
{
    return isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] === 1062;
}

function create_reference(PDO $pdo)
{
    do {
        $reference = 'FCW-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(12)));
        $check = $pdo->prepare('SELECT id FROM tickets WHERE reference_number = ?');
        $check->execute(array($reference));
    } while ($check->fetch());

    return $reference;
}
