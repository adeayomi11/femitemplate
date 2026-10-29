<?php

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/includes/layout.php';
define('APP_BOOTSTRAPPED', true);

try {
    $pdo = db();
} catch (PDOException $exception) {
    error_log('Student support database error: ' . $exception->getMessage());
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Database setup · <?= e(APP_NAME) ?></title><link rel="stylesheet" href="assets/css/complaints.css"></head>
    <body class="auth-page"><main class="auth-wrap"><a class="auth-brand" href="index.php"><span class="brand-mark">W</span><span><strong>FCWEM</strong><small>Student support</small></span></a><section class="auth-card"><h1>Database connection required</h1><p>The portal could not connect to MySQL or create its database. Start MySQL in the XAMPP Control Panel and check the database settings.</p><div class="alert alert-danger">Database: <?= e(DB_NAME) ?> · Host: <?= e(DB_HOST) ?> · Default XAMPP user: <?= e(DB_USER) ?></div><p class="mb-0">If your MySQL root account has a password, set <code>FCWEM_DB_PASSWORD</code> in your PHP environment. The detailed connection error was written to the PHP error log.</p></section></main></body></html>
    <?php
    exit;
}

$page = isset($_GET['page']) ? (string) $_GET['page'] : 'dashboard';
$user = current_user();
$hasAdmin = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;

if (!$hasAdmin) {
    $page = 'setup';
} elseif (!$user && !in_array($page, array('login', 'register', 'track'), true)) {
    $page = 'login';
} elseif ($user && in_array($page, array('login', 'register', 'setup'), true)) {
    $page = 'dashboard';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

    if ($action === 'setup_admin') {
        if ($hasAdmin) {
            set_flash('warning', 'The administrator account has already been created.');
            redirect('?page=login');
        }
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if ($name === '' || text_length($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 10) {
            set_flash('danger', 'Enter a name, valid email address, and password with at least 10 characters.');
            redirect('?page=setup');
        }
        $statement = $pdo->prepare("INSERT INTO users (full_name, email, role, password_hash) VALUES (?, ?, 'admin', ?)");
        try {
            $statement->execute(array($name, $email, password_hash($password, PASSWORD_DEFAULT)));
        } catch (PDOException $exception) {
            if (!is_duplicate_error($exception)) {
                throw $exception;
            }
            set_flash('danger', 'That email address is already in use.');
            redirect('?page=setup');
        }
        set_flash('success', 'Administrator account created. Sign in to manage the support portal.');
        redirect('?page=login');
    }

    if ($action === 'login') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.email = ? LIMIT 1');
        $statement->execute(array($email));
        $account = $statement->fetch();
        if (!$account || !password_verify($password, $account['password_hash'])) {
            set_flash('danger', 'The email or password you entered is incorrect.');
            redirect('?page=login');
        }
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        unset($account['password_hash']);
        $_SESSION['user'] = $account;
        redirect('?page=dashboard');
    }

    if ($action === 'logout') {
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }
        session_destroy();
        redirect('?page=login');
    }

    if ($action === 'register_student') {
        if ($hasAdmin && current_user()) {
            set_flash('warning', 'Sign out before creating a new student account.');
            redirect('?page=dashboard');
        }
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $matric = strtoupper(trim((string) ($_POST['matric_number'] ?? '')));
        $departmentId = filter_var($_POST['department_id'] ?? null, FILTER_VALIDATE_INT);
        $password = (string) ($_POST['password'] ?? '');
        $departmentCheck = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
        $departmentCheck->execute(array($departmentId));
        if ($name === '' || text_length($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || $matric === '' || strlen($matric) > 50 || !$departmentId || !$departmentCheck->fetch() || strlen($password) < 8) {
            set_flash('danger', 'Complete all fields with a valid email address and a password of at least 8 characters.');
            redirect('?page=register');
        }
        $statement = $pdo->prepare("INSERT INTO users (full_name, email, matric_number, department_id, role, password_hash) VALUES (?, ?, ?, ?, 'student', ?)");
        try {
            $statement->execute(array($name, $email, $matric, $departmentId, password_hash($password, PASSWORD_DEFAULT)));
        } catch (PDOException $exception) {
            if (!is_duplicate_error($exception)) {
                throw $exception;
            }
            set_flash('danger', 'That email address or matric number is already registered.');
            redirect('?page=register');
        }
        set_flash('success', 'Your student account is ready. Sign in to submit a complaint.');
        redirect('?page=login');
    }

    if ($action === 'create_ticket') {
        $user = require_role(array('student'));
        $category = trim((string) ($_POST['category'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        if (!in_array($category, ticket_categories(), true) || $subject === '' || text_length($subject) > 180 || text_length($description) < 15 || text_length($description) > 12000) {
            set_flash('danger', 'Choose a topic, enter a short subject, and describe the issue in at least 15 characters.');
            redirect('?page=new-ticket');
        }

        $reference = create_reference($pdo);
        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO tickets (reference_number, student_id, category, subject, description) VALUES (?, ?, ?, ?, ?)');
            $insert->execute(array($reference, $user['id'], $category, $subject, $description));
            $ticketId = $pdo->lastInsertId();
            $update = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, author_id, status, message) VALUES (?, ?, ?, ?)');
            $update->execute(array($ticketId, $user['id'], 'Submitted', 'Your complaint has been received and is waiting to be assigned to the appropriate department.'));
            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        set_flash('success', 'Your ticket was submitted. Keep the reference number ' . $reference . ' to track its progress.');
        redirect('?page=ticket&id=' . urlencode((string) $ticketId));
    }

    if ($action === 'assign_ticket') {
        $user = require_role(array('admin'));
        $ticketId = filter_var($_POST['ticket_id'] ?? null, FILTER_VALIDATE_INT);
        $departmentId = filter_var($_POST['department_id'] ?? null, FILTER_VALIDATE_INT);
        $departmentQuery = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
        $departmentQuery->execute(array($departmentId));
        $department = $departmentQuery->fetch();
        $ticketQuery = $pdo->prepare("SELECT id, status FROM tickets WHERE id = ?");
        $ticketQuery->execute(array($ticketId));
        $ticket = $ticketQuery->fetch();
        if (!$ticketId || !$departmentId || !$department || !$ticket) {
            set_flash('danger', 'Choose a valid department and ticket.');
            redirect('?page=tickets');
        }
        $newStatus = $ticket['status'] === 'Submitted' ? 'Under Review' : $ticket['status'];
        $pdo->beginTransaction();
        try {
            $updateTicket = $pdo->prepare('UPDATE tickets SET department_id = ?, status = ? WHERE id = ?');
            $updateTicket->execute(array($departmentId, $newStatus, $ticketId));
            $message = 'Assigned to ' . $department['name'] . ' by the support administrator.';
            $updateHistory = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, author_id, status, message) VALUES (?, ?, ?, ?)');
            $updateHistory->execute(array($ticketId, $user['id'], $newStatus, $message));
            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        set_flash('success', 'The ticket has been routed to ' . $department['name'] . '.');
        redirect('?page=ticket&id=' . urlencode((string) $ticketId));
    }

    if ($action === 'department_update') {
        $user = require_role(array('department'));
        $ticketId = filter_var($_POST['ticket_id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string) ($_POST['status'] ?? '');
        $message = trim((string) ($_POST['message'] ?? ''));
        $allowedStatuses = array('Under Review', 'In Progress', 'Resolved', 'Closed');
        $ticketQuery = $pdo->prepare('SELECT id FROM tickets WHERE id = ? AND department_id = ?');
        $ticketQuery->execute(array($ticketId, $user['department_id']));
        if (!$ticketId || !$ticketQuery->fetch() || !in_array($status, $allowedStatuses, true) || $message === '' || text_length($message) > 5000) {
            set_flash('danger', 'Select a valid status and add an update for a case assigned to your department.');
            redirect('?page=tickets');
        }
        $pdo->beginTransaction();
        try {
            $updateTicket = $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ? AND department_id = ?');
            $updateTicket->execute(array($status, $ticketId, $user['department_id']));
            $updateHistory = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, author_id, status, message) VALUES (?, ?, ?, ?)');
            $updateHistory->execute(array($ticketId, $user['id'], $status, $message));
            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        set_flash('success', 'The case status and student-visible update have been saved.');
        redirect('?page=ticket&id=' . urlencode((string) $ticketId));
    }

    if ($action === 'create_department_user') {
        require_role(array('admin'));
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $departmentId = filter_var($_POST['department_id'] ?? null, FILTER_VALIDATE_INT);
        $password = (string) ($_POST['password'] ?? '');
        $departmentQuery = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
        $departmentQuery->execute(array($departmentId));
        if ($name === '' || text_length($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || !$departmentId || !$departmentQuery->fetch() || strlen($password) < 8) {
            set_flash('danger', 'Enter a valid name, email, department, and password of at least 8 characters.');
            redirect('?page=users');
        }
        $statement = $pdo->prepare("INSERT INTO users (full_name, email, department_id, role, password_hash) VALUES (?, ?, ?, 'department', ?)");
        try {
            $statement->execute(array($name, $email, $departmentId, password_hash($password, PASSWORD_DEFAULT)));
        } catch (PDOException $exception) {
            if (!is_duplicate_error($exception)) {
                throw $exception;
            }
            set_flash('danger', 'That email address is already used by another account.');
            redirect('?page=users');
        }
        set_flash('success', 'Department account created. Share the login details securely with the department staff member.');
        redirect('?page=users');
    }

    if ($action === 'save_department') {
        require_role(array('admin'));
        $departmentId = filter_var($_POST['department_id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '' || text_length($name) > 120) {
            set_flash('danger', 'Enter a department name of no more than 120 characters.');
            redirect('?page=users');
        }
        try {
            if ($departmentId) {
                $exists = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
                $exists->execute(array($departmentId));
                if (!$exists->fetch()) {
                    set_flash('danger', 'That department could not be found.');
                    redirect('?page=users');
                }
                $save = $pdo->prepare('UPDATE departments SET name = ? WHERE id = ?');
                $save->execute(array($name, $departmentId));
                set_flash('success', 'Department name updated. Existing accounts and tickets remain linked to it.');
            } else {
                $save = $pdo->prepare('INSERT INTO departments (name) VALUES (?)');
                $save->execute(array($name));
                set_flash('success', 'Department added. It is now available for student registration and ticket routing.');
            }
        } catch (PDOException $exception) {
            if (!is_duplicate_error($exception)) {
                throw $exception;
            }
            set_flash('danger', 'A department with that name already exists.');
        }
        redirect('?page=users');
    }

    set_flash('danger', 'That action is not available.');
    redirect($user ? '?page=dashboard' : '?page=login');
}

$user = current_user();
if ($page === 'setup' || $page === 'login' || $page === 'register') {
    $departments = departments($pdo);
    require __DIR__ . '/pages/auth.php';
    exit;
}

if ($page === 'track') {
    $reference = strtoupper(trim((string) ($_GET['reference'] ?? '')));
    $ticket = null;
    $updates = array();
    if ($reference !== '') {
        $statement = $pdo->prepare('SELECT t.*, d.name AS department_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id WHERE t.reference_number = ?');
        $statement->execute(array($reference));
        $ticket = $statement->fetch();
        if ($ticket) {
            $history = $pdo->prepare('SELECT tu.status, tu.message, tu.created_at, COALESCE(u.full_name, \'Support team\') AS author_name FROM ticket_updates tu LEFT JOIN users u ON u.id = tu.author_id WHERE tu.ticket_id = ? ORDER BY tu.created_at DESC, tu.id DESC');
            $history->execute(array($ticket['id']));
            $updates = $history->fetchAll();
        }
    }
    require __DIR__ . '/pages/track.php';
    exit;
}

$title = 'Overview';
$active = $page;
$view = 'dashboard';
$data = array();

if ($page === 'dashboard') {
    $user = require_role(array('student', 'department', 'admin'));
    $title = $user['role'] === 'student' ? 'Student dashboard' : ($user['role'] === 'admin' ? 'Admin dashboard' : 'Department dashboard');
    $active = 'dashboard';
    $where = '';
    $params = array();
    if ($user['role'] === 'student') {
        $where = ' WHERE student_id = ?';
        $params[] = $user['id'];
    } elseif ($user['role'] === 'department') {
        $where = ' WHERE department_id = ?';
        $params[] = $user['department_id'];
    }
    $statsQuery = $pdo->prepare("SELECT COUNT(*) AS total, SUM(status IN ('Submitted', 'Under Review', 'In Progress')) AS open_count, SUM(status = 'Resolved') AS resolved_count FROM tickets" . $where);
    $statsQuery->execute($params);
    $data['stats'] = $statsQuery->fetch();
    $recentSql = 'SELECT t.id, t.reference_number, t.subject, t.category, t.status, t.created_at, d.name AS department_name, u.full_name AS student_name FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id' . $where . ' ORDER BY t.updated_at DESC LIMIT 6';
    $recentQuery = $pdo->prepare($recentSql);
    $recentQuery->execute($params);
    $data['tickets'] = $recentQuery->fetchAll();
    $view = 'dashboard';
} elseif ($page === 'tickets') {
    $user = require_role(array('student', 'department', 'admin'));
    $title = $user['role'] === 'student' ? 'My complaints' : ($user['role'] === 'admin' ? 'All complaints' : 'Department cases');
    $active = 'tickets';
    $filters = array();
    $clauses = array();
    $params = array();
    if ($user['role'] === 'student') {
        $clauses[] = 't.student_id = ?';
        $params[] = $user['id'];
    } elseif ($user['role'] === 'department') {
        $clauses[] = 't.department_id = ?';
        $params[] = $user['department_id'];
    }
    $statusFilter = trim((string) ($_GET['status'] ?? ''));
    if (in_array($statusFilter, ticket_statuses(), true)) {
        $clauses[] = 't.status = ?';
        $params[] = $statusFilter;
        $filters['status'] = $statusFilter;
    }
    $search = trim((string) ($_GET['q'] ?? ''));
    if ($search !== '') {
        $clauses[] = '(t.reference_number LIKE ? OR t.subject LIKE ? OR u.full_name LIKE ? OR u.matric_number LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
        $filters['q'] = $search;
    }
    $sql = 'SELECT t.id, t.reference_number, t.subject, t.category, t.status, t.created_at, t.updated_at, d.name AS department_name, u.full_name AS student_name, u.matric_number FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id';
    if ($clauses) {
        $sql .= ' WHERE ' . implode(' AND ', $clauses);
    }
    $sql .= ' ORDER BY t.updated_at DESC LIMIT 150';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $data['tickets'] = $statement->fetchAll();
    $data['filters'] = $filters;
    $data['departments'] = departments($pdo);
    $view = 'tickets';
} elseif ($page === 'new-ticket') {
    $user = require_role(array('student'));
    $title = 'Create a ticket';
    $active = 'new-ticket';
    $data['categories'] = ticket_categories();
    $view = 'new-ticket';
} elseif ($page === 'ticket') {
    $user = require_role(array('student', 'department', 'admin'));
    $ticketId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    $conditions = array('t.id = ?');
    $params = array($ticketId);
    if ($user['role'] === 'student') {
        $conditions[] = 't.student_id = ?';
        $params[] = $user['id'];
    } elseif ($user['role'] === 'department') {
        $conditions[] = 't.department_id = ?';
        $params[] = $user['department_id'];
    }
    $statement = $pdo->prepare('SELECT t.*, d.name AS department_name, u.full_name AS student_name, u.email AS student_email, u.matric_number FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id WHERE ' . implode(' AND ', $conditions));
    $statement->execute($params);
    $data['ticket'] = $statement->fetch();
    if (!$data['ticket']) {
        set_flash('warning', 'That ticket could not be found or you do not have permission to view it.');
        redirect($user['role'] === 'student' ? '?page=tickets' : '?page=tickets');
    }
    $history = $pdo->prepare('SELECT tu.status, tu.message, tu.created_at, COALESCE(u.full_name, \'Support team\') AS author_name FROM ticket_updates tu LEFT JOIN users u ON u.id = tu.author_id WHERE tu.ticket_id = ? ORDER BY tu.created_at DESC, tu.id DESC');
    $history->execute(array($ticketId));
    $data['updates'] = $history->fetchAll();
    $data['departments'] = $user['role'] === 'admin' ? departments($pdo) : array();
    $title = 'Ticket details';
    $active = $user['role'] === 'admin' ? 'tickets' : ($user['role'] === 'department' ? 'tickets' : 'tickets');
    $view = 'ticket';
} elseif ($page === 'users') {
    $user = require_role(array('admin'));
    $title = 'Department accounts';
    $active = 'users';
    $data['departments'] = departments($pdo);
    $data['departmentUsers'] = $pdo->query("SELECT u.full_name, u.email, u.created_at, d.name AS department_name FROM users u JOIN departments d ON d.id = u.department_id WHERE u.role = 'department' ORDER BY d.name, u.full_name")->fetchAll();
    $view = 'users';
} else {
    set_flash('warning', 'The requested page was not found.');
    redirect('?page=dashboard');
}

extract($data, EXTR_SKIP);
layout_start($title, $active, $user);
require __DIR__ . '/pages/' . $view . '.php';
layout_end();
