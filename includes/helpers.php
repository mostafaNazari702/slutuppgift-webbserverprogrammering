<?php

require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        exit('Ogiltig CSRF-token. Ladda om sidan och försök igen.');
    }
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function password_issues(string $pw): array {
    $issues = [];
    if (strlen($pw) < 8) $issues[] = 'minst 8 tecken';
    if (!preg_match('/[A-ZÅÄÖ]/u', $pw)) $issues[] = 'en versal';
    if (!preg_match('/[a-zåäö]/u', $pw)) $issues[] = 'en gemen';
    if (!preg_match('/[0-9]/', $pw)) $issues[] = 'en siffra';
    if (!preg_match('/[^A-Za-zÅÄÖåäö0-9]/u', $pw)) $issues[] = 'ett specialtecken';
    return $issues;
}

function clean_str(?string $s, int $max = 255): string {
    return trim(mb_substr($s ?? '', 0, $max));
}

function valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function redirect(string $path): void {
    header('Location: ' . APP_URL . $path);
    exit;
}
