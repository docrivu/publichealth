<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$defaultAdminName = 'rivu';
$defaultAdminUsername = 'rivu';
$defaultAdminPassword = 'change-this-password';

$status = null;
$errors = [];

try {
    db();
    $status = 'Database connection looks good.';
} catch (Throwable $throwable) {
    $errors[] = $throwable->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install'])) {
    try {
        $pdo = db();
        $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', (string) $schema))) as $statement) {
            $pdo->exec($statement);
        }

        $superAdminExists = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "super_admin"')->fetchColumn() > 0;

        if (!$superAdminExists) {
            $fullName = post_string('full_name', $defaultAdminName);
            $username = post_string('username', $defaultAdminUsername);
            $password = post_string('password', $defaultAdminPassword);

            $pdo->prepare(
                'INSERT INTO users (full_name, username, password_hash, role)
                 VALUES (:full_name, :username, :password_hash, "super_admin")'
            )->execute([
                'full_name' => $fullName,
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        flash('success', 'Installation completed. You can log in now.');
        redirect_to('login.php');
    } catch (Throwable $throwable) {
        $errors[] = $throwable->getMessage();
    }
}

app_layout('Install', function () use ($status, $errors, $defaultAdminName, $defaultAdminUsername, $defaultAdminPassword): void {
    ?>
    <section class="card narrow">
        <p class="eyebrow">Setup</p>
        <h2>Install the application</h2>
        <p class="muted">Set your Hostinger MySQL details in `config/app.php`, then run the installer. The requested default super admin is prefilled below.</p>
        <?php if ($status): ?>
            <div class="flash flash-success"><?= e($status) ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endforeach; ?>
        <div class="flash flash-success">
            Default super admin login after installation:
            <strong><?= e($defaultAdminUsername) ?></strong> / <strong><?= e($defaultAdminPassword) ?></strong>
        </div>
        <form method="post" class="stack">
            <input type="hidden" name="run_install" value="1">
            <label>
                <span>Initial super admin name</span>
                <input type="text" name="full_name" value="<?= e($defaultAdminName) ?>" required>
            </label>
            <label>
                <span>Initial super admin username</span>
                <input type="text" name="username" value="<?= e($defaultAdminUsername) ?>" required>
            </label>
            <label>
                <span>Initial super admin password</span>
                <input type="text" name="password" value="<?= e($defaultAdminPassword) ?>" required>
            </label>
            <button class="button" type="submit">Run install</button>
        </form>
    </section>
    <?php
});
