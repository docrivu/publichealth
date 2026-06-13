<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = current_user();
$modules = survey_modules();
$householdFields = household_characteristic_fields();

if ($user['role'] === 'super_admin') {
    $stmt = db()->prepare(
        'SELECT s.*, u.full_name AS collector_name
         FROM surveys s
         INNER JOIN users u ON u.id = s.collector_id
         WHERE s.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
} else {
    $stmt = db()->prepare(
        'SELECT s.*, u.full_name AS collector_name
         FROM surveys s
         INNER JOIN users u ON u.id = s.collector_id
         WHERE s.id = :id AND s.collector_id = :collector_id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id, 'collector_id' => $user['id']]);
}

$survey = $stmt->fetch();
if (!$survey) {
    exit('Survey not found');
}

$payload = json_decode((string) $survey['payload_json'], true) ?: [];
$identification = survey_identification_payload($survey, $payload);

function pdf_row_value(array $row, string $fieldName, int $index): string
{
    return trim((string) ($row[$fieldName] ?? $row['col_' . ($index + 1)] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey <?= htmlspecialchars((string) $survey['id']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 24px; }
        h1, h2, h3 { margin: 0 0 12px; }
        .meta, .grid { display: grid; gap: 8px; }
        .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 20px; }
        .card { margin-bottom: 24px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; text-align: left; }
        .photos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .photos img { width: 100%; max-height: 260px; object-fit: cover; border: 1px solid #ccc; }
        .actions { margin-bottom: 18px; }
        @media print { .actions { display: none; } body { margin: 12px; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
    <div class="card">
        <h1>Community Diagnosis Survey #<?= htmlspecialchars((string) $survey['id']) ?></h1>
        <div class="grid">
            <div><strong>Village:</strong> <?= htmlspecialchars((string) $survey['village_name']) ?></div>
            <div><strong>Collector:</strong> <?= htmlspecialchars((string) $survey['collector_name']) ?></div>
            <div><strong>Visit date:</strong> <?= htmlspecialchars((string) $survey['visit_date']) ?></div>
            <div><strong>Investigator:</strong> <?= htmlspecialchars((string) $survey['investigator_name']) ?></div>
            <div><strong>Latitude:</strong> <?= htmlspecialchars((string) $identification['geo_latitude']) ?></div>
            <div><strong>Longitude:</strong> <?= htmlspecialchars((string) $identification['geo_longitude']) ?></div>
        </div>
        <?php if (($identification['photos'] ?? []) !== []): ?>
            <div class="photos">
                <?php foreach ($identification['photos'] as $photo): ?>
                    <img src="<?= htmlspecialchars((string) $photo) ?>" alt="Survey photo">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Household characteristics</h2>
        <div class="grid">
            <?php foreach ($householdFields as $fieldName => $field): ?>
                <div><strong><?= htmlspecialchars((string) $field['label']) ?>:</strong> <?= htmlspecialchars((string) ($payload['household_characteristics'][$fieldName] ?? '')) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php foreach ($modules as $moduleKey => $module): ?>
        <?php $rows = $payload[$moduleKey] ?? []; ?>
        <div class="card">
            <h3><?= htmlspecialchars((string) $module['title']) ?></h3>
            <?php if ($rows === []): ?>
                <p>No responses recorded for this module.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($module['fields'] as $field): ?>
                                <th><?= htmlspecialchars((string) $field['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($module['fields'] as $index => $field): ?>
                                    <td><?= htmlspecialchars(pdf_row_value($row, $field['name'], $index)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <script>window.print();</script>
</body>
</html>
