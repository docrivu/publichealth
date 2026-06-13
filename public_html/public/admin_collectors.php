<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_role('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = post_string('full_name');
    $username = post_string('username');
    $password = post_string('password');

    if ($fullName === '' || $username === '' || $password === '') {
        flash('error', 'Name, username, and password are required.');
        save_old_input($_POST);
        redirect_to('admin_collectors.php');
    }

    $stmt = db()->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (:full_name, :username, :password_hash, "collector")');

    try {
        $stmt->execute([
            'full_name' => $fullName,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        clear_old_input();
        flash('success', 'Collector account created.');
    } catch (PDOException $exception) {
        save_old_input($_POST);
        flash('error', 'That username already exists.');
    }

    redirect_to('admin_collectors.php');
}

$collectors = db()->query(
    'SELECT u.id, u.full_name, u.username, u.is_active, u.created_at, COUNT(s.id) AS survey_count
     FROM users u
     LEFT JOIN surveys s ON s.collector_id = u.id
     WHERE u.role = "collector"
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

app_layout('Collectors', function () use ($collectors): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Administration</p>
            <h2>Collector accounts</h2>
            <p class="muted">Create field collector logins and monitor how many submissions each user has saved.</p>
        </div>
    </section>

    <section class="grid admin-grid">
        <article class="card">
            <h3>Create collector</h3>
            <form method="post" class="stack">
                <label>
                    <span>Full name</span>
                    <input type="text" name="full_name" value="<?= e((string) old('full_name')) ?>" required>
                </label>
                <label>
                    <span>Username</span>
                    <input type="text" name="username" value="<?= e((string) old('username')) ?>" required>
                </label>
                <label>
                    <span>Temporary password</span>
                    <input type="text" name="password" value="<?= e((string) old('password')) ?>" required>
                </label>
                <button class="button" type="submit">Create collector</button>
            </form>
        </article>

        <article class="card">
            <h3>Existing collectors</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($collectors as $collector): ?>
                        <tr>
                            <td><?= e($collector['full_name']) ?></td>
                            <td><?= e($collector['username']) ?></td>
                            <td><?= e((string) $collector['survey_count']) ?></td>
                            <td><?= (int) $collector['is_active'] ? 'Active' : 'Inactive' ?></td>
                            <td><?= e($collector['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($collectors === []): ?>
                        <tr>
                            <td colspan="5">No collectors created yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
    <?php
    clear_old_input();
});
