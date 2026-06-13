<?php

declare(strict_types=1);

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', ''), '/');
    $path = ltrim($path, '/');

    if ($base === '') {
        return '/' . $path;
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

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old'][$key] ?? $default;
}

function save_old_input(array $input): void
{
    $_SESSION['old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['old']);
}

function post_string(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function post_array(string $key): array
{
    $value = $_POST[$key] ?? [];
    return is_array($value) ? $value : [];
}

function normalize_rows(array $rows): array
{
    $normalized = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $cleanRow = [];
        $hasValue = false;

        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $nested = normalize_rows($value);
                $cleanRow[$key] = $nested;
                if ($nested !== []) {
                    $hasValue = true;
                }
                continue;
            }

            $cleanValue = trim((string) $value);
            $cleanRow[$key] = $cleanValue;
            if ($cleanValue !== '') {
                $hasValue = true;
            }
        }

        if ($hasValue) {
            $normalized[] = $cleanRow;
        }
    }

    return $normalized;
}

function uploaded_photo_urls(string $field): array
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'] ?? null)) {
        return [];
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
    ];

    $urls = [];
    $count = min(4, count($_FILES[$field]['name']));
    for ($i = 0; $i < $count; $i++) {
        $error = $_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmpName = $_FILES[$field]['tmp_name'][$i] ?? '';
        $mime = mime_content_type($tmpName) ?: '';
        if (!isset($allowed[$mime])) {
            continue;
        }

        $filename = sprintf('survey_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $allowed[$mime]);
        $target = $uploadDir . '/' . $filename;
        if (move_uploaded_file($tmpName, $target)) {
            $urls[] = base_url('uploads/' . $filename);
        }
    }

    return $urls;
}

function survey_identification_payload(array $survey, array $payload): array
{
    return [
        'team_no' => $survey['team_no'] ?? '',
        'investigator_name' => $survey['investigator_name'] ?? '',
        'visit_date' => $survey['visit_date'] ?? '',
        'village_name' => $survey['village_name'] ?? '',
        'locality_name' => $survey['locality_name'] ?? '',
        'head_name' => $survey['head_name'] ?? '',
        'informant_name' => $survey['informant_name'] ?? '',
        'household_member_count' => $survey['household_member_count'] ?? '',
        'geo_latitude' => $payload['identification_media']['geo_latitude'] ?? '',
        'geo_longitude' => $payload['identification_media']['geo_longitude'] ?? '',
        'photos' => $payload['identification_media']['photos'] ?? [],
    ];
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
        <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>">
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
                <?php if ($user): ?>
                    <a href="<?= e(base_url('index.php')) ?>">Dashboard</a>
                    <a href="<?= e(base_url('survey_create.php')) ?>">New Survey</a>
                    <?php if ($user['role'] === 'super_admin'): ?>
                        <a href="<?= e(base_url('admin_collectors.php')) ?>">Manage Collectors</a>
                    <?php endif; ?>
                    <a href="<?= e(base_url('logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a href="<?= e(base_url('login.php')) ?>">Login</a>
                <?php endif; ?>
            </nav>
            <?php if ($user): ?>
                <div class="user-card">
                    <strong><?= e($user['full_name']) ?></strong>
                    <span><?= e($user['role']) ?></span>
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
    <script src="<?= e(base_url('assets/app.js')) ?>"></script>
    </body>
    </html>
    <?php
}
