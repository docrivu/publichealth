<?php

declare(strict_types=1);

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', ''), '/');
    $path = ltrim($path, '/');

    if ($base === '') {
        return $path === '' ? './' : $path;
    }

    return $base . ($path === '' ? '' : '/' . $path);
}

function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function post_string(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function generate_assessment_uid(): string
{
    return 'CGA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function module_payload_from_post(array $module): array
{
    $payload = [];
    foreach ($module['fields'] as $field) {
        if (($field['type'] ?? '') === 'heading') {
            continue;
        }
        $payload[$field['name']] = post_string($field['name']);
    }
    return $payload;
}

function flatten_assessment_payload(array $payload, string $moduleKey, array $fields): array
{
    $moduleData = $payload[$moduleKey] ?? [];
    $row = [];
    foreach ($fields as $field) {
        if (($field['type'] ?? '') === 'heading') {
            continue;
        }
        $row[$field['name']] = (string) ($moduleData[$field['name']] ?? '');
    }
    return $row;
}

function load_assessment_for_user(int $id, array $user): ?array
{
    if ($user['role'] === 'super_admin') {
        $stmt = db()->prepare(
            'SELECT a.*, u.full_name AS collector_name
             FROM assessments a
             INNER JOIN users u ON u.id = a.collector_id
             WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    $stmt = db()->prepare(
        'SELECT a.*, u.full_name AS collector_name
         FROM assessments a
         INNER JOIN users u ON u.id = a.collector_id
         WHERE a.id = :id AND a.collector_id = :collector_id LIMIT 1'
    );
    $stmt->execute(['id' => $id, 'collector_id' => $user['id']]);
    return $stmt->fetch() ?: null;
}

function app_layout(string $title, callable $content): void
{
    $flash = consume_flash();
    $user = current_user();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | <?= e((string) config('app_name')) ?></title>
        <link rel="stylesheet" href="<?= e(base_url('assets/app-polished-20260611.css')) ?>">
        <style>
            body{background:linear-gradient(180deg,#e9f5f4 0,#f5f8fa 280px)!important;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif!important;color:#17212b!important}
            .sidebar{background:linear-gradient(180deg,#0f766e,#115e59)!important;color:#eafdfb!important}
            .card,.hero-panel{border-radius:8px!important;border:1px solid #dbe5ea!important;box-shadow:0 16px 42px rgba(24,45,63,.09)!important;background:#fff!important}
            .button{border-radius:8px!important;background:#0f766e!important;color:#fff!important;font-weight:800!important}
            input,select,textarea{border-radius:8px!important;border:1px solid #c9d8df!important;font-family:inherit!important}
            .form-heading{background:#e8f7f5!important;border-left:5px solid #0f766e!important;border-radius:8px!important}
            @media(max-width:980px){.shell{display:block!important}.sidebar{position:static!important;height:auto!important}.main{padding:16px!important}.two-up,.dashboard-stats,.admin-grid{grid-template-columns:1fr!important}}
        </style>
    </head>
    <body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <p class="eyebrow">Survey Platform</p>
                <h1><?= e((string) config('app_name')) ?></h1>
                <p class="muted">Community diagnosis, household, and NCD data collection.</p>
            </div>
            <nav class="nav">
                <a href="<?= e(base_url('index.php')) ?>">Dashboard</a>
                <?php if ($user): ?>
                    <a href="<?= e(base_url('assessment_form.php')) ?>">New Survey</a>
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <a href="<?= e(base_url('admin_users.php')) ?>">Manage Collectors</a>
                        <a href="<?= e(base_url('export.php?module=summary')) ?>">Summary CSV</a>
                    <?php endif; ?>
                    <a href="<?= e(base_url('logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a href="<?= e(base_url('login.php')) ?>">Login</a>
                <?php endif; ?>
            </nav>
            <?php if ($user): ?>
                <div class="user-card">
                    <strong><?= e($user['full_name']) ?></strong>
                    <span><?= e(str_replace('_', ' ', $user['role'])) ?></span>
                </div>
            <?php endif; ?>
        </aside>
        <main class="main">
            <?php if ($flash): ?>
                <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
            <?php $content(); ?>
        </main>
    </div>
    <script src="<?= e(base_url('assets/app.js?v=20260611b')) ?>"></script>
    </body>
    </html>
    <?php
}
