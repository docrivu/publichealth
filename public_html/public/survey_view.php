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
    flash('error', 'Survey not found.');
    redirect_to('index.php');
}

$payload = json_decode((string) $survey['payload_json'], true) ?: [];
$identification = survey_identification_payload($survey, $payload);

function row_value(array $row, string $fieldName, int $index): string
{
    return trim((string) ($row[$fieldName] ?? $row['col_' . ($index + 1)] ?? ''));
}

app_layout('View Survey', function () use ($survey, $payload, $modules, $householdFields, $user, $identification): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Submission</p>
            <h2>Survey #<?= e((string) $survey['id']) ?></h2>
            <p class="muted"><?= e($survey['village_name']) ?> | <?= e($survey['head_name']) ?> | Collected by <?= e($survey['collector_name']) ?></p>
        </div>
        <div class="hero-actions">
            <a class="button secondary" href="<?= e(base_url('survey_pdf.php?id=' . $survey['id'])) ?>" target="_blank">Print / Save PDF</a>
            <?php if ($user['role'] === 'super_admin'): ?>
                <a class="button" href="<?= e(base_url('export_surveys.php?module=all_summary')) ?>">Download all summary CSV</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <h3>Summary</h3>
        <div class="grid two-up detail-grid">
            <p><strong>Visit date:</strong> <?= e($survey['visit_date']) ?></p>
            <p><strong>Investigator:</strong> <?= e($survey['investigator_name']) ?></p>
            <p><strong>Village:</strong> <?= e($survey['village_name']) ?></p>
            <p><strong>Locality:</strong> <?= e($survey['locality_name']) ?></p>
            <p><strong>Head of family:</strong> <?= e($survey['head_name']) ?></p>
            <p><strong>Informant:</strong> <?= e($survey['informant_name']) ?></p>
            <p><strong>Latitude:</strong> <?= e((string) $identification['geo_latitude']) ?></p>
            <p><strong>Longitude:</strong> <?= e((string) $identification['geo_longitude']) ?></p>
        </div>
        <?php if (($identification['photos'] ?? []) !== []): ?>
            <div class="photo-gallery">
                <?php foreach ($identification['photos'] as $photo): ?>
                    <a href="<?= e((string) $photo) ?>" target="_blank"><img src="<?= e((string) $photo) ?>" alt="Survey photo"></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h3>Household characteristics</h3>
        <div class="grid two-up detail-grid">
            <?php foreach ($householdFields as $fieldName => $field): ?>
                <p><strong><?= e($field['label']) ?>:</strong> <?= e((string) ($payload['household_characteristics'][$fieldName] ?? '')) ?></p>
            <?php endforeach; ?>
        </div>
    </section>

    <?php foreach ($modules as $moduleKey => $module): ?>
        <?php $rows = $payload[$moduleKey] ?? []; ?>
        <section class="card">
            <div class="section-title">
                <h3><?= e($module['title']) ?></h3>
                <?php if ($user['role'] === 'super_admin'): ?>
                    <a href="<?= e(base_url('export_surveys.php?module=' . urlencode($moduleKey))) ?>">Download module CSV</a>
                <?php endif; ?>
            </div>
            <?php if ($rows === []): ?>
                <p class="muted">No responses recorded for this module.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <?php foreach ($module['fields'] as $field): ?>
                                <th><?= e($field['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($module['fields'] as $index => $field): ?>
                                    <td><?= e(row_value($row, $field['name'], $index)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php
});
