<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, full_name, username, role, is_active FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !(int) $user['is_active']) {
        logout_user();
        return null;
    }

    return $user;
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !(int) $user['is_active'] || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user_id'] = $user['id'];
    return true;
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!current_user()) {
        flash('error', 'Please log in to continue.');
        redirect_to('login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        flash('error', 'You do not have access to that page.');
        redirect_to('index.php');
    }
}
