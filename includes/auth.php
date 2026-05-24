<?php

require_once __DIR__ . '/helpers.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function is_moderator(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'moderator';
}

function require_login(): void {
    if (!is_logged_in()) {
        flash('error', 'Du måste logga in först.');
        redirect('/auth/login.php');
    }
}

function require_moderator(): void {
    require_login();
    if (!is_moderator()) {
        http_response_code(403);
        exit('Endast moderatorer har tillgång till denna sida.');
    }
}

function login_user(array $row): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $row['id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'role' => $row['role'],
    ];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']);
    }
    session_destroy();
}
