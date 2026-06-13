<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_login();

$modules = survey_modules();
$householdFields = household_characteristic_fields();

function render_field_input(string $name, array $field, string $prefix = '', string $indexToken = '__INDEX__'): string
{
    $fieldName = $prefix === '' ? $name : sprintf('%s[%s][%s]', $prefix, $indexToken, $name);
    $type = $field['type'] ?? 'text';
    $attributes = $field['attributes'] ?? [];
    $common = '';

    foreach (['min', 'max', 'step', 'placeholder'] as $attr) {
        if (isset($field[$attr])) {
            $common .= sprintf(' %s="%s"', $attr, e((string) $field[$attr]));
        }
    }

    foreach ($attributes as $attr => $value) {
        $common .= sprintf(' %s="%s"', $attr, e((string) $value));
    }

    if ($type === 'number') {
        $common .= ' inputmode="decimal"';
    }

    if ($type === 'date') {
        $common .= ' data-date-input="1"';
    }

    if ($type === 'text') {
        $common .= ' autocomplete="off"';
    }

    if ($type === 'select') {
        $html = sprintf('<select name="%s"%s>', e($fieldName), $common);
        foreach (($field['options'] ?? []) as $value => $label) {
            $html .= sprintf('<option value="%s">%s</option>', e((string) $value), e((string) $label));
        }
        $html .= '</select>';
        return $html;
    }

    return sprintf('<input type="%s" name="%s"%s>', e($type), e($fieldName), $common);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = current_user();
    $householdPayload = [];

    foreach (array_keys($householdFields) as $fieldName) {
        $householdPayload[$fieldName] = post_string($fieldName);
    }

    $payload = [
        'household_characteristics' => $householdPayload,
        'identification_media' => [
            'geo_latitude' => post_string('geo_latitude'),
            'geo_longitude' => post_string('geo_longitude'),
            'photos' => uploaded_photo_urls('survey_photos'),
        ],
    ];

    foreach (array_keys($modules) as $moduleKey) {
        $payload[$moduleKey] = normalize_rows(post_array($moduleKey));
    }

    db()->prepare(
        'INSERT INTO surveys (
            collector_id, team_no, investigator_name, visit_date, village_name, locality_name,
            head_name, informant_name, household_member_count, payload_json
        ) VALUES (
            :collector_id, :team_no, :investigator_name, :visit_date, :village_name, :locality_name,
            :head_name, :informant_name, :household_member_count, :payload_json
        )'
    )->execute([
        'collector_id' => $user['id'],
        'team_no' => post_string('team_no'),
        'investigator_name' => post_string('investigator_name'),
        'visit_date' => post_string('visit_date') ?: null,
        'village_name' => post_string('village_name'),
        'locality_name' => post_string('locality_name'),
        'head_name' => post_string('head_name'),
        'informant_name' => post_string('informant_name'),
        'household_member_count' => post_string('household_member_count') !== '' ? (int) post_string('household_member_count') : null,
        'payload_json' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    ]);

    clear_old_input();
    flash('success', 'Survey saved successfully.');
    redirect_to('index.php');
}

app_layout('New Survey', function () use ($modules, $householdFields): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">Data Entry</p>
            <h2>Community diagnosis form</h2>
            <p class="muted">This workflow combines the household survey, immunization, morbidity, eligible couples, and NCD schedule.</p>
        </div>
    </section>

    <section class="card portal-intro">
        <div>
            <h3>Survey workspace</h3>
            <p class="muted">Use the quick navigation below to move through the survey blocks. Open only the modules needed for that household during fieldwork.</p>
        </div>
        <div class="module-toolbar">
            <span class="chip" data-progress-chip>0 / 0 modules opened</span>
            <button class="button secondary" type="button" data-expand-all>Open all modules</button>
            <button class="button secondary" type="button" data-collapse-all>Collapse all modules</button>
        </div>
        <p class="muted mobile-tip">On mobile, each added row becomes a stacked card so collectors can enter data without side-scrolling.</p>
        <nav class="section-nav">
            <a href="#section-identification">Identification</a>
            <a href="#section-household">Household</a>
            <?php foreach ($modules as $name => $module): ?>
                <a href="#section-<?= e($name) ?>"><?= e($module['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </section>

    <form method="post" class="stack survey-form" enctype="multipart/form-data">
        <section class="card module-card is-open" id="section-identification" data-form-section data-module-card>
            <div class="module-head">
                <div>
                    <h3>1. Team and household identification</h3>
                    <p class="muted">Core visit details, geo-location, and field photos for this submission.</p>
                </div>
                <button class="button secondary module-toggle" type="button" data-module-toggle>Collapse</button>
            </div>
            <div class="module-body" data-module-body>
                <div class="grid two-up">
                    <label><span>Team number</span><input type="text" name="team_no" required></label>
                    <label><span>Investigator name</span><input type="text" name="investigator_name" required></label>
                    <label><span>Date of visit</span><input type="date" name="visit_date" required></label>
                    <label><span>Name of village</span><input type="text" name="village_name" required></label>
                    <label><span>Locality</span><input type="text" name="locality_name"></label>
                    <label><span>Head of family</span><input type="text" name="head_name" required></label>
                    <label><span>Informant name</span><input type="text" name="informant_name"></label>
                    <label><span>No. of members in family</span><input type="number" name="household_member_count" min="0"></label>
                    <label>
                        <span>Latitude</span>
                        <input type="text" name="geo_latitude" inputmode="decimal" autocomplete="off" placeholder="e.g. 22.572645">
                    </label>
                    <label>
                        <span>Longitude</span>
                        <input type="text" name="geo_longitude" inputmode="decimal" autocomplete="off" placeholder="e.g. 88.363892">
                    </label>
                </div>
                <div class="module-toolbar collector-tools">
                    <button class="button secondary" type="button" data-fill-location>Use current location</button>
                    <p class="geo-status muted" data-location-status>Tap the button to capture GPS coordinates. Location works best over HTTPS with device location enabled.</p>
                </div>
                <div class="photo-block">
                    <label>
                        <span>Upload or take up to 4 pictures</span>
                        <input type="file" name="survey_photos[]" accept="image/*" capture="environment" multiple>
                    </label>
                    <div class="photo-hint muted">Use the phone camera or upload from gallery. Only image files are accepted.</div>
                </div>
            </div>
        </section>

        <section class="card module-card is-open" id="section-household" data-form-section data-module-card>
            <div class="module-head">
                <div>
                    <h3>2. Household characteristics</h3>
                    <p class="muted">Housing, sanitation, water, fuel, and family profile details.</p>
                </div>
                <button class="button secondary module-toggle" type="button" data-module-toggle>Collapse</button>
            </div>
            <div class="module-body" data-module-body>
                <div class="grid three-up">
                    <?php foreach ($householdFields as $fieldName => $field): ?>
                        <label>
                            <span><?= e($field['label']) ?></span>
                            <?= render_field_input($fieldName, $field) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php foreach ($modules as $name => $module): ?>
            <section class="card repeater module-card" data-repeater="<?= e($name) ?>" id="section-<?= e($name) ?>" data-form-section data-module-card>
                <div class="section-title module-head">
                    <div>
                        <h3><?= e($module['title']) ?></h3>
                        <p class="muted"><?= e($module['description']) ?></p>
                    </div>
                    <div class="module-actions">
                        <button class="button secondary module-toggle" type="button" data-module-toggle>Open</button>
                        <button class="button secondary" type="button" data-add-row>Add row</button>
                    </div>
                </div>
                <div class="module-body" data-module-body hidden>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <?php foreach ($module['fields'] as $field): ?>
                                    <th><?= e($field['label']) ?></th>
                                <?php endforeach; ?>
                                <th>Remove</th>
                            </tr>
                            </thead>
                            <tbody data-rows></tbody>
                        </table>
                    </div>
                    <template>
                        <?php $rowAttributes = []; if ($name === 'household_members') { $rowAttributes[] = 'data-household-member-row'; } if ($name === 'eligible_couples') { $rowAttributes[] = 'data-eligible-couple-row'; } ?>
                        <tr <?= implode(' ', $rowAttributes) ?>>
                            <?php foreach ($module['fields'] as $field): ?>
                                <td data-label="<?= e($field['label']) ?>"><?= render_field_input($field['name'], $field, $name) ?></td>
                            <?php endforeach; ?>
                            <td data-label="Remove"><button class="button danger" type="button" data-remove-row>Remove</button></td>
                        </tr>
                    </template>
                </div>
            </section>
        <?php endforeach; ?>

        <div class="form-actions">
            <a class="button secondary" href="<?= e(base_url('index.php')) ?>">Back to dashboard</a>
            <button class="button" type="submit">Save survey</button>
        </div>
    </form>
    <?php
});
