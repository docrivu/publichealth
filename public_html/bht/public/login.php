<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect_to('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (attempt_login(post_string('username'), post_string('password'))) {
        redirect_to('index.php');
    }
    flash('error', 'Invalid username or password.');
}

app_layout('Login', function (): void {
    ?>
    <section class="auth-card card narrow">
        <p class="eyebrow">Access</p>
        <h2>Sign in</h2>
        <p class="muted">Super admins can create collector accounts after installation.</p>
        <form method="post" class="stack">
            <label><span>Username</span><input type="text" name="username" autocomplete="username" required></label>
            <label><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button" type="submit">Login</button>
        </form>
    </section>
    <?php
});
