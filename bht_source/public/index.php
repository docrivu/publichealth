<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$user = current_user();
$modules = cga_modules();

if (!$user) {
    app_layout('Home', function (): void {
        ?>
        <section class="hero-panel">
            <div>
                <p class="eyebrow">Annexure II</p>
                <h2>Comprehensive Geriatric Assessment Portal</h2>
                <p>Collect CGA data section by section, edit later, export module-wise CSVs, and generate printable PDF records.</p>
                <a class="button" href="<?= e(base_url('login.php')) ?>">Portal login</a>
            </div>
        </section>
        <?php
    });
    return;
}

if ($user['role'] === 'super_admin') {
    $totals = db()->query('SELECT (SELECT COUNT(*) FROM assessments) AS assessments, (SELECT COUNT(*) FROM users WHERE role = "collector") AS collectors')->fetch();
    $stmt = db()->query(
        'SELECT a.*, u.full_name AS collector_name
         FROM assessments a
         INNER JOIN users u ON u.id = a.collector_id
         ORDER BY a.updated_at DESC'
    );
} else {
    $totalsStmt = db()->prepare('SELECT COUNT(*) AS assessments FROM assessments WHERE collector_id = :collector_id');
    $totalsStmt->execute(['collector_id' => $user['id']]);
    $totals = $totalsStmt->fetch();
    $stmt = db()->prepare(
        'SELECT a.*, u.full_name AS collector_name
         FROM assessments a
         INNER JOIN users u ON u.id = a.collector_id
         WHERE a.collector_id = :collector_id
         ORDER BY a.updated_at DESC'
    );
    $stmt->execute(['collector_id' => $user['id']]);
}

$assessments = $stmt->fetchAll();

app_layout('Dashboard', function () use ($user, $totals, $assessments, $modules): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Dashboard</p>
            <h2>Welcome, <?= e($user['full_name']) ?></h2>
            <p class="muted">Create, edit, review, export, and print Comprehensive Geriatric Assessment records.</p>
        </div>
        <div class="hero-actions">
            <a class="button" href="<?= e(base_url('assessment_form.php')) ?>">New CGA</a>
            <?php if ($user['role'] === 'super_admin'): ?><a class="button secondary" href="<?= e(base_url('admin_users.php')) ?>">Create users</a><?php endif; ?>
        </div>
    </section>

    <section class="grid dashboard-stats">
        <article class="card stat-card"><span>Assessments</span><strong><?= e((string) ($totals['assessments'] ?? 0)) ?></strong></article>
        <?php if ($user['role'] === 'super_admin'): ?><article class="card stat-card"><span>Collectors</span><strong><?= e((string) ($totals['collectors'] ?? 0)) ?></strong></article><?php endif; ?>
        <article class="card stat-card"><span>Modules</span><strong><?= e((string) count($modules)) ?></strong></article>
    </section>

    <?php if ($user['role'] === 'super_admin'): ?>
        <section class="card">
            <div class="section-title"><h3>Module-wise CSV export</h3><span class="chip">UID included in every row</span></div>
            <div class="export-grid">
                <?php foreach ($modules as $moduleKey => $module): ?>
                    <a class="export-link" href="<?= e(base_url('export.php?module=' . urlencode($moduleKey))) ?>"><?= e($module['title']) ?></a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="card">
        <div class="section-title"><h3>Assessments</h3><span class="chip"><?= e((string) count($assessments)) ?> shown</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>UID</th><th>Name</th><th>Age/Sex</th><th>Assessment date</th><th>Collector</th><th>Updated</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($assessments as $row): ?>
                    <tr>
                        <td><?= e($row['uid']) ?></td>
                        <td><?= e($row['patient_name']) ?></td>
                        <td><?= e(trim((string) $row['patient_age'] . ' / ' . (string) $row['patient_sex'], ' /')) ?></td>
                        <td><?= e($row['first_assessment_date']) ?></td>
                        <td><?= e($row['collector_name']) ?></td>
                        <td><?= e($row['updated_at']) ?></td>
                        <td class="actions-cell">
                            <a href="<?= e(base_url('assessment_view.php?id=' . $row['id'])) ?>">View</a>
                            <a href="<?= e(base_url('assessment_form.php?id=' . $row['id'])) ?>">Edit</a>
                            <a href="<?= e(base_url('assessment_pdf.php?id=' . $row['id'])) ?>" target="_blank">PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($assessments === []): ?><tr><td colspan="7">No CGA records yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
