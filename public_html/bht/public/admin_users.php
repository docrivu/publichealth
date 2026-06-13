<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_role('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        db()->prepare('INSERT INTO bht_users (full_name, username, password_hash, role) VALUES (:full_name, :username, :password_hash, "collector")')
            ->execute([
                'full_name' => post_string('full_name'),
                'username' => post_string('username'),
                'password_hash' => password_hash(post_string('password'), PASSWORD_DEFAULT),
            ]);
        flash('success', 'Collector account created.');
    } catch (Throwable $throwable) {
        flash('error', 'Could not create user. The username may already exist.');
    }
    redirect_to('admin_users.php');
}

$bht_users = db()->query(
    'SELECT u.id, u.full_name, u.username, u.is_active, u.created_at, COUNT(a.id) AS assessment_count
     FROM bht_users u
     LEFT JOIN bht_assessments a ON a.collector_id = u.id
     WHERE u.role = "collector"
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

app_layout('Users', function () use ($bht_users): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Administration</p>
            <h2>Collector bht_users</h2>
            <p class="muted">Create field accounts for CGA data collection.</p>
        </div>
    </section>
    <section class="grid admin-grid">
        <article class="card">
            <h3>Create collector</h3>
            <form method="post" class="stack">
                <label><span>Full name</span><input type="text" name="full_name" required></label>
                <label><span>Username</span><input type="text" name="username" required></label>
                <label><span>Temporary password</span><input type="text" name="password" required></label>
                <button class="button" type="submit">Create user</button>
            </form>
        </article>
        <article class="card">
            <h3>Existing collectors</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Username</th><th>Assessments</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php foreach ($bht_users as $user): ?>
                        <tr>
                            <td><?= e($user['full_name']) ?></td>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e((string) $user['assessment_count']) ?></td>
                            <td><?= (int) $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                            <td><?= e($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($bht_users === []): ?><tr><td colspan="5">No collectors yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
    <?php
});
