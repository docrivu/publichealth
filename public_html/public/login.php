<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect_to('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post_string('username');
    $password = post_string('password');

    if (attempt_login($username, $password)) {
        flash('success', 'Login successful.');
        redirect_to('index.php');
    }

    flash('error', 'Invalid username or password.');
    save_old_input($_POST);
    redirect_to('login.php');
}

app_layout('Login', function (): void {
    ?>
    <section class="auth-card card narrow">
        <p class="eyebrow">Access</p>
        <h2>Sign in</h2>
        <p class="muted">Super admins can create collector accounts after installation.</p>
        <form method="post" class="stack">
            <label>
                <span>Username</span>
                <input type="text" name="username" value="<?= e((string) old('username')) ?>" required>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required>
            </label>
            <button class="button" type="submit">Login</button>
        </form>
    </section>
    <?php
    clear_old_input();
});
