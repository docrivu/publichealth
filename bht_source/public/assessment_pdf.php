<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_login();

$user = current_user();
$assessment = load_assessment_for_user((int) ($_GET['id'] ?? 0), $user);
if (!$assessment) {
    exit('Assessment not found');
}

$payload = json_decode((string) $assessment['payload_json'], true) ?: [];
$modules = cga_modules();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($assessment['uid']) ?></title>
    <style>
        body { color: #111; font-family: Arial, sans-serif; margin: 24px; }
        h1, h2, h3 { margin: 0 0 10px; }
        .top { margin-bottom: 20px; }
        .module { margin: 0 0 22px; page-break-inside: avoid; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 18px; }
        .heading { grid-column: 1 / -1; padding: 8px; background: #eef3f5; font-weight: 700; margin-top: 8px; }
        p { margin: 0 0 6px; font-size: 12px; line-height: 1.4; }
        .actions { margin-bottom: 16px; }
        @media print { .actions { display: none; } body { margin: 10mm; } }
    </style>
</head>
<body>
    <div class="actions"><button onclick="window.print()">Print / Save as PDF</button></div>
    <div class="top">
        <h1>Comprehensive Geriatric Assessment</h1>
        <p><strong>UID:</strong> <?= e($assessment['uid']) ?></p>
        <p><strong>Name:</strong> <?= e($assessment['patient_name']) ?> | <strong>Age/Sex:</strong> <?= e($assessment['patient_age']) ?> / <?= e($assessment['patient_sex']) ?></p>
        <p><strong>Collector:</strong> <?= e($assessment['collector_name']) ?> | <strong>Assessment date:</strong> <?= e($assessment['first_assessment_date']) ?></p>
    </div>
    <?php foreach ($modules as $moduleKey => $module): ?>
        <section class="module">
            <h2><?= e($module['title']) ?></h2>
            <div class="grid">
                <?php foreach ($module['fields'] as $field): ?>
                    <?php if (($field['type'] ?? '') === 'heading'): ?>
                        <div class="heading"><?= e($field['label']) ?></div>
                    <?php else: ?>
                        <p><strong><?= e($field['label']) ?>:</strong> <?= nl2br(e((string) ($payload[$moduleKey][$field['name']] ?? ''))) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <script>window.print();</script>
</body>
</html>
