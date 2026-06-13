<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_login();

$user = current_user();
$modules = cga_modules();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$assessment = $id > 0 ? load_assessment_for_user($id, $user) : null;

if ($id > 0 && !$assessment) {
    flash('error', 'Assessment not found.');
    redirect_to('index.php');
}

$payload = $assessment ? (json_decode((string) $assessment['payload_json'], true) ?: []) : [];

function field_value(array $payload, string $moduleKey, string $name): string
{
    return (string) ($payload[$moduleKey][$name] ?? '');
}

function render_cga_field(array $field, string $value = ''): void
{
    if (($field['type'] ?? '') === 'heading') {
        echo '<div class="form-heading">' . e($field['label']) . '</div>';
        return;
    }

    $name = $field['name'];
    $label = $field['label'];
    $type = $field['type'] ?? 'text';
    echo '<label><span>' . e($label) . '</span>';
    if ($type === 'select') {
        echo '<select name="' . e($name) . '">';
        foreach (($field['options'] ?? []) as $optionValue => $optionLabel) {
            $selected = (string) $optionValue === $value ? ' selected' : '';
            echo '<option value="' . e((string) $optionValue) . '"' . $selected . '>' . e((string) $optionLabel) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea name="' . e($name) . '">' . e($value) . '</textarea>';
    } else {
        $inputType = in_array($type, ['date', 'number'], true) ? $type : 'text';
        $extra = $inputType === 'number' ? ' inputmode="decimal"' : '';
        echo '<input type="' . e($inputType) . '" name="' . e($name) . '" value="' . e($value) . '"' . $extra . '>';
    }
    echo '</label>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPayload = [];
    foreach ($modules as $moduleKey => $module) {
        $newPayload[$moduleKey] = module_payload_from_post($module);
    }

    $section1 = $newPayload['section_1_basic_details'] ?? [];
    $params = [
        'patient_name' => $section1['patient_name'] ?? '',
        'patient_age' => $section1['age_completed_years'] ?? '',
        'patient_sex' => $section1['sex'] ?? '',
        'first_assessment_date' => ($section1['first_assessment_date'] ?? '') ?: null,
        'contact_person' => $section1['contact_person_relationship'] ?? '',
        'payload_json' => json_encode($newPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    ];

    if ($assessment) {
        $params['id'] = $assessment['id'];
        db()->prepare(
            'UPDATE assessments
             SET patient_name = :patient_name, patient_age = :patient_age, patient_sex = :patient_sex,
                 first_assessment_date = :first_assessment_date, contact_person = :contact_person,
                 payload_json = :payload_json
             WHERE id = :id'
        )->execute($params);
        flash('success', 'Assessment updated.');
        redirect_to('assessment_view.php?id=' . $assessment['id']);
    }

    $uid = generate_assessment_uid();
    db()->prepare(
        'INSERT INTO assessments
            (uid, collector_id, patient_name, patient_age, patient_sex, first_assessment_date, contact_person, payload_json)
         VALUES
            (:uid, :collector_id, :patient_name, :patient_age, :patient_sex, :first_assessment_date, :contact_person, :payload_json)'
    )->execute($params + ['uid' => $uid, 'collector_id' => $user['id']]);
    flash('success', 'Assessment saved.');
    redirect_to('index.php');
}

app_layout($assessment ? 'Edit CGA' : 'New CGA', function () use ($modules, $payload, $assessment): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Data collection</p>
            <h2><?= $assessment ? 'Edit Comprehensive Geriatric Assessment' : 'New Comprehensive Geriatric Assessment' ?></h2>
            <p class="muted">Collect data in the same section-wise flow as Annexure II. All modules are linked by one CGA UID.</p>
        </div>
        <?php if ($assessment): ?><span class="chip"><?= e($assessment['uid']) ?></span><?php endif; ?>
    </section>

    <section class="card portal-intro">
        <div class="module-toolbar">
            <span class="chip" data-progress-chip></span>
            <button class="button secondary" type="button" data-expand-all>Open all</button>
            <button class="button secondary" type="button" data-collapse-all>Collapse all</button>
        </div>
        <nav class="section-nav">
            <?php foreach ($modules as $moduleKey => $module): ?>
                <a href="#<?= e($moduleKey) ?>"><?= e($module['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </section>

    <form method="post" class="stack">
        <?php $first = true; ?>
        <?php foreach ($modules as $moduleKey => $module): ?>
            <section class="card module-card <?= $first ? 'is-open' : '' ?>" id="<?= e($moduleKey) ?>" data-module-card>
                <div class="module-head">
                    <div>
                        <h3><?= e($module['title']) ?></h3>
                        <p class="muted"><?= e($module['description']) ?></p>
                    </div>
                    <button class="button secondary module-toggle" type="button" data-module-toggle><?= $first ? 'Collapse' : 'Open' ?></button>
                </div>
                <div class="module-body" data-module-body <?= $first ? '' : 'hidden' ?>>
                    <div class="grid two-up">
                        <?php foreach ($module['fields'] as $field): ?>
                            <?php render_cga_field($field, isset($field['name']) ? field_value($payload, $moduleKey, $field['name']) : ''); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php $first = false; ?>
        <?php endforeach; ?>

        <div class="form-actions">
            <a class="button secondary" href="<?= e(base_url('index.php')) ?>">Cancel</a>
            <button class="button" type="submit">Save CGA</button>
        </div>
    </form>
    <?php
});
