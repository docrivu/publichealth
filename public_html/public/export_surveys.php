<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_role('super_admin');

$modules = survey_modules();
$householdFields = household_characteristic_fields();
$moduleKey = trim((string) ($_GET['module'] ?? 'all_summary'));

$stmt = db()->query(
    'SELECT s.*, u.full_name AS collector_name, u.username AS collector_username
     FROM surveys s
     INNER JOIN users u ON u.id = s.collector_id
     ORDER BY s.created_at DESC'
);
$surveys = $stmt->fetchAll();

if ($moduleKey === 'all_summary' || !array_key_exists($moduleKey, $modules)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cd-portal-summary-export.csv"');
    $output = fopen('php://output', 'w');
    $header = [
        'survey_id', 'collector_name', 'collector_username', 'team_no', 'investigator_name', 'visit_date',
        'village_name', 'locality_name', 'head_name', 'informant_name', 'household_member_count',
        'geo_latitude', 'geo_longitude', 'photo_urls'
    ];
    foreach (array_keys($householdFields) as $fieldName) {
        $header[] = $fieldName;
    }
    fputcsv($output, $header);

    foreach ($surveys as $survey) {
        $payload = json_decode((string) $survey['payload_json'], true) ?: [];
        $identification = survey_identification_payload($survey, $payload);
        $line = [
            $survey['id'], $survey['collector_name'], $survey['collector_username'], $survey['team_no'], $survey['investigator_name'],
            $survey['visit_date'], $survey['village_name'], $survey['locality_name'], $survey['head_name'], $survey['informant_name'],
            $survey['household_member_count'], $identification['geo_latitude'], $identification['geo_longitude'], implode(' | ', $identification['photos'] ?? []),
        ];
        foreach (array_keys($householdFields) as $fieldName) {
            $line[] = $payload['household_characteristics'][$fieldName] ?? '';
        }
        fputcsv($output, $line);
    }
    fclose($output);
    exit;
}

$module = $modules[$moduleKey];
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $moduleKey . '-responses.csv"');
$output = fopen('php://output', 'w');
$header = [
    'survey_id', 'village_name', 'head_name', 'collector_name', 'visit_date'
];
foreach ($module['fields'] as $field) {
    $header[] = $field['name'];
}
fputcsv($output, $header);

foreach ($surveys as $survey) {
    $payload = json_decode((string) $survey['payload_json'], true) ?: [];
    $rows = $payload[$moduleKey] ?? [];
    if ($rows === []) {
        continue;
    }
    foreach ($rows as $row) {
        $line = [
            $survey['id'],
            $survey['village_name'],
            $survey['head_name'],
            $survey['collector_name'],
            $survey['visit_date'],
        ];
        foreach ($module['fields'] as $index => $field) {
            $line[] = trim((string) ($row[$field['name']] ?? $row['col_' . ($index + 1)] ?? ''));
        }
        fputcsv($output, $line);
    }
}

fclose($output);
exit;
