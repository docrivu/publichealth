<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_role('super_admin');

$modules = cga_modules();
$moduleKey = (string) ($_GET['module'] ?? 'summary');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="cga_' . preg_replace('/[^a-z0-9_]/i', '_', $moduleKey) . '_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');

if ($moduleKey === 'summary') {
    fputcsv($out, ['uid', 'collector', 'patient_name', 'patient_age', 'patient_sex', 'first_assessment_date', 'contact_person', 'created_at', 'updated_at']);
    $rows = db()->query(
        'SELECT a.*, u.full_name AS collector_name
         FROM assessments a
         INNER JOIN users u ON u.id = a.collector_id
         ORDER BY a.created_at DESC'
    )->fetchAll();
    foreach ($rows as $row) {
        fputcsv($out, [$row['uid'], $row['collector_name'], $row['patient_name'], $row['patient_age'], $row['patient_sex'], $row['first_assessment_date'], $row['contact_person'], $row['created_at'], $row['updated_at']]);
    }
    exit;
}

if (!isset($modules[$moduleKey])) {
    fputcsv($out, ['error']);
    fputcsv($out, ['Unknown module']);
    exit;
}

$fields = array_values(array_filter($modules[$moduleKey]['fields'], fn (array $field): bool => ($field['type'] ?? '') !== 'heading'));
$header = ['uid', 'collector', 'patient_name', 'patient_age', 'patient_sex', 'first_assessment_date'];
foreach ($fields as $field) {
    $header[] = $field['label'];
}
fputcsv($out, $header);

$rows = db()->query(
    'SELECT a.*, u.full_name AS collector_name
     FROM assessments a
     INNER JOIN users u ON u.id = a.collector_id
     ORDER BY a.created_at DESC'
)->fetchAll();

foreach ($rows as $row) {
    $payload = json_decode((string) $row['payload_json'], true) ?: [];
    $csvRow = [$row['uid'], $row['collector_name'], $row['patient_name'], $row['patient_age'], $row['patient_sex'], $row['first_assessment_date']];
    foreach ($fields as $field) {
        $csvRow[] = (string) ($payload[$moduleKey][$field['name']] ?? '');
    }
    fputcsv($out, $csvRow);
}
