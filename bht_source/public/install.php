<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$status = null;
$errors = [];

try {
    db();
    $status = 'Database connection looks good.';
} catch (Throwable $throwable) {
    $errors[] = $throwable->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = db();
        $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', (string) $schema))) as $statement) {
            $pdo->exec($statement);
        }

        $exists = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "super_admin"')->fetchColumn() > 0;
        if (!$exists) {
            $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (:full_name, :username, :password_hash, "super_admin")')
                ->execute([
                    'full_name' => post_string('full_name', 'Super Admin'),
                    'username' => post_string('username', 'admin'),
                    'password_hash' => password_hash(post_string('password', 'change-this-password'), PASSWORD_DEFAULT),
                ]);
        }

        flash('success', 'Installation completed. Please log in.');
        redirect_to('login.php');
    } catch (Throwable $throwable) {
        $errors[] = $throwable->getMessage();
    }
}

app_layout('Install', function () use ($status, $errors): void {
    ?>
    <section class="card narrow">
        <p class="eyebrow">Setup</p>
        <h2>Install CGA Portal</h2>
        <p class="muted">Edit <code>config/app.php</code> with your hosting MySQL database details, then run this installer once.</p>
        <?php if ($status): ?><div class="flash flash-success"><?= e($status) ?></div><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" class="stack">
            <label><span>Initial super admin name</span><input type="text" name="full_name" value="Super Admin" required></label>
            <label><span>Initial super admin username</span><input type="text" name="username" value="admin" required></label>
            <label><span>Initial super admin password</span><input type="text" name="password" value="change-this-password" required></label>
            <button class="button" type="submit">Run install</button>
        </form>
    </section>
    <?php
});
