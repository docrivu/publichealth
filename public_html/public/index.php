<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
$user = current_user();
$modules = survey_modules();

function increment_count(array &$bucket, string $value): void
{
    $clean = trim($value);
    if ($clean === '') {
        return;
    }
    $bucket[$clean] = ($bucket[$clean] ?? 0) + 1;
}

function top_categories(array $counts, int $limit = 6): array
{
    arsort($counts);
    return array_slice($counts, 0, $limit, true);
}

function chart_card(string $title, array $counts): void
{
    $items = top_categories($counts);
    $max = $items === [] ? 0 : max($items);
    $total = array_sum($counts);
    ?>
    <article class="card chart-card">
        <div class="section-title">
            <h3><?= e($title) ?></h3>
            <span class="chip"><?= e((string) $total) ?> records</span>
        </div>
        <?php if ($items === []): ?>
            <p class="muted">No data captured yet.</p>
        <?php else: ?>
            <div class="bar-list">
                <?php foreach ($items as $label => $value): ?>
                    <?php $width = $max > 0 ? max(8, (int) round(($value / $max) * 100)) : 0; ?>
                    <?php $percent = $total > 0 ? round(($value / $total) * 100, 1) : 0; ?>
                    <div class="bar-row">
                        <div class="bar-meta">
                            <span><?= e($label) ?></span>
                            <strong><?= e((string) $value) ?> (<?= e((string) $percent) ?>%)</strong>
                        </div>
                        <div class="bar-track"><div class="bar-fill" style="width: <?= e((string) $width) ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
    <?php
}

$publicTotals = [
    'surveys' => 0,
    'collectors' => 0,
];
$publicModuleCounts = array_fill_keys(array_keys($modules), 0);
$publicInsights = [
    'family_type' => [],
    'house_type' => [],
    'drinking_water_source' => [],
    'latrine_type' => [],
    'cooking_fuel' => [],
    'member_sex' => [],
    'maternal_tt1' => [],
    'maternal_tt2' => [],
    'child_bcg' => [],
    'child_mr_measles' => [],
    'acute_illness_15_days' => [],
    'acute_treatment' => [],
];
$dbAvailable = true;

try {
    $publicTotals = db()->query(
        'SELECT
            (SELECT COUNT(*) FROM users WHERE role = "collector") AS collectors,
            (SELECT COUNT(*) FROM surveys) AS surveys
        '
    )->fetch() ?: $publicTotals;

    $metricStmt = db()->query('SELECT payload_json FROM surveys');
    foreach ($metricStmt->fetchAll() as $metricSurvey) {
        $payload = json_decode((string) $metricSurvey['payload_json'], true) ?: [];
        foreach (array_keys($modules) as $moduleKey) {
            $publicModuleCounts[$moduleKey] += count($payload[$moduleKey] ?? []);
        }
        $hh = $payload['household_characteristics'] ?? [];
        increment_count($publicInsights['family_type'], (string) ($hh['family_type'] ?? ''));
        increment_count($publicInsights['house_type'], (string) ($hh['house_type'] ?? ''));
        increment_count($publicInsights['drinking_water_source'], (string) ($hh['drinking_water_source'] ?? ''));
        increment_count($publicInsights['latrine_type'], (string) ($hh['latrine_type'] ?? ''));
        increment_count($publicInsights['cooking_fuel'], (string) ($hh['cooking_fuel'] ?? ''));
        foreach (($payload['household_members'] ?? []) as $member) {
            increment_count($publicInsights['member_sex'], (string) ($member['sex'] ?? ''));
        }
        foreach (($payload['immunization_mothers'] ?? []) as $mother) {
            increment_count($publicInsights['maternal_tt1'], (string) ($mother['tt1'] ?? ''));
            increment_count($publicInsights['maternal_tt2'], (string) ($mother['tt2'] ?? ''));
        }
        foreach (($payload['immunization_children'] ?? []) as $child) {
            increment_count($publicInsights['child_bcg'], (string) ($child['bcg'] ?? ''));
            increment_count($publicInsights['child_mr_measles'], (string) ($child['mr_measles'] ?? ''));
        }
        foreach (($payload['morbidity_acute'] ?? []) as $case) {
            increment_count($publicInsights['acute_illness_15_days'], (string) ($case['last_15_days'] ?? ''));
            increment_count($publicInsights['acute_treatment'], (string) ($case['treatment_received'] ?? ''));
        }
    }
} catch (Throwable $throwable) {
    $dbAvailable = false;
}

if (!$user) {
    app_layout('Portal Home', function () use ($modules, $publicTotals, $publicModuleCounts, $publicInsights, $dbAvailable): void {
        ?>
        <section class="portal-switcher" aria-label="Survey portals">
            <a class="portal-tab is-active" href="https://rivubasu.co.in/" target="_blank" rel="noopener">
                <span>Village Adoption Programme</span>
                <strong>rivubasu.co.in</strong>
            </a>
            <a class="portal-tab" href="https://rivubasu.co.in/bht/" target="_blank" rel="noopener">
                <span>BHT Portal</span>
                <strong>rivubasu.co.in/bht</strong>
            </a>
        </section>

        <section class="page-header dashboard-hero">
            <div>
                <p class="eyebrow">Village Adoption Programme</p>
                <h2>Live Data Collection Dashboard</h2>
                <p class="muted">This portal supports community diagnosis and follow-up in adopted villages through household, maternal-child, morbidity, eligible couple, disability, and NCD data collection.</p>
            </div>
            <div class="hero-actions">
                <a class="button" href="<?= e(base_url('login.php')) ?>">Portal Login</a>
            </div>
        </section>

        <?php if (!$dbAvailable): ?>
            <section class="card">
                <p class="muted">Live metrics will appear here once the database connection is configured.</p>
            </section>
        <?php else: ?>
            <section class="grid dashboard-stats">
                <article class="card stat-card">
                    <span>Total surveys</span>
                    <strong><?= e((string) ($publicTotals['surveys'] ?? 0)) ?></strong>
                    <p class="muted">Records submitted in the portal</p>
                </article>
                <article class="card stat-card">
                    <span>Collectors</span>
                    <strong><?= e((string) ($publicTotals['collectors'] ?? 0)) ?></strong>
                    <p class="muted">Field accounts in use</p>
                </article>
                <article class="card stat-card">
                    <span>Modules tracked</span>
                    <strong><?= e((string) count($modules)) ?></strong>
                    <p class="muted">Programme components being monitored</p>
                </article>
            </section>

            <?php $publicModuleLabels = []; foreach ($modules as $moduleKey => $module) { $publicModuleLabels[$module['title']] = $publicModuleCounts[$moduleKey] ?? 0; } ?>
            <section class="grid chart-grid">
                <?php chart_card('Module-wise Response Volume', $publicModuleLabels); ?>
            </section>

            <section class="card">
                <div class="section-title">
                    <h3>Key Survey Results</h3>
                    <span class="chip">Live graphs from submitted responses</span>
                </div>
                <div class="grid chart-grid">
                    <?php chart_card('Family Type', $publicInsights['family_type']); ?>
                    <?php chart_card('House Type', $publicInsights['house_type']); ?>
                    <?php chart_card('Drinking Water Source', $publicInsights['drinking_water_source']); ?>
                    <?php chart_card('Latrine Type', $publicInsights['latrine_type']); ?>
                    <?php chart_card('Main Cooking Fuel', $publicInsights['cooking_fuel']); ?>
                    <?php chart_card('Household Members by Sex', $publicInsights['member_sex']); ?>
                    <?php chart_card('Antenatal TT-1 Status', $publicInsights['maternal_tt1']); ?>
                    <?php chart_card('Antenatal TT-2 Status', $publicInsights['maternal_tt2']); ?>
                    <?php chart_card('Child BCG Status', $publicInsights['child_bcg']); ?>
                    <?php chart_card('Child MR / Measles Status', $publicInsights['child_mr_measles']); ?>
                    <?php chart_card('Acute Illness in Last 15 Days', $publicInsights['acute_illness_15_days']); ?>
                    <?php chart_card('Acute Morbidity Treatment Received', $publicInsights['acute_treatment']); ?>
                </div>
            </section>
        <?php endif; ?>
        <?php
    });
    return;
}

if ($user['role'] === 'super_admin') {
    $totals = db()->query(
        'SELECT
            (SELECT COUNT(*) FROM users WHERE role = "collector") AS collectors,
            (SELECT COUNT(*) FROM surveys) AS surveys
        '
    )->fetch();

    $stmt = db()->query(
        'SELECT s.id, s.village_name, s.head_name, s.visit_date, s.created_at, s.payload_json, u.full_name AS collector_name
         FROM surveys s
         INNER JOIN users u ON u.id = s.collector_id
         ORDER BY s.created_at DESC
         LIMIT 20'
    );

    $metricStmt = db()->query('SELECT id, payload_json FROM surveys');
    $moduleCounts = array_fill_keys(array_keys($modules), 0);
    $insights = [
        'family_type' => [],
        'house_type' => [],
        'drinking_water_source' => [],
        'latrine_type' => [],
        'cooking_fuel' => [],
        'member_sex' => [],
        'maternal_tt1' => [],
        'maternal_tt2' => [],
        'child_bcg' => [],
        'child_mr_measles' => [],
        'acute_illness_15_days' => [],
        'acute_treatment' => [],
    ];
    foreach ($metricStmt->fetchAll() as $metricSurvey) {
        $payload = json_decode((string) $metricSurvey['payload_json'], true) ?: [];
        foreach (array_keys($modules) as $moduleKey) {
            $moduleCounts[$moduleKey] += count($payload[$moduleKey] ?? []);
        }
        $hh = $payload['household_characteristics'] ?? [];
        increment_count($insights['family_type'], (string) ($hh['family_type'] ?? ''));
        increment_count($insights['house_type'], (string) ($hh['house_type'] ?? ''));
        increment_count($insights['drinking_water_source'], (string) ($hh['drinking_water_source'] ?? ''));
        increment_count($insights['latrine_type'], (string) ($hh['latrine_type'] ?? ''));
        increment_count($insights['cooking_fuel'], (string) ($hh['cooking_fuel'] ?? ''));
        foreach (($payload['household_members'] ?? []) as $member) {
            increment_count($insights['member_sex'], (string) ($member['sex'] ?? ''));
        }
        foreach (($payload['immunization_mothers'] ?? []) as $mother) {
            increment_count($insights['maternal_tt1'], (string) ($mother['tt1'] ?? ''));
            increment_count($insights['maternal_tt2'], (string) ($mother['tt2'] ?? ''));
        }
        foreach (($payload['immunization_children'] ?? []) as $child) {
            increment_count($insights['child_bcg'], (string) ($child['bcg'] ?? ''));
            increment_count($insights['child_mr_measles'], (string) ($child['mr_measles'] ?? ''));
        }
        foreach (($payload['morbidity_acute'] ?? []) as $case) {
            increment_count($insights['acute_illness_15_days'], (string) ($case['last_15_days'] ?? ''));
            increment_count($insights['acute_treatment'], (string) ($case['treatment_received'] ?? ''));
        }
    }
} else {
    $stmt = db()->prepare(
        'SELECT s.id, s.village_name, s.head_name, s.visit_date, s.created_at, s.payload_json, u.full_name AS collector_name
         FROM surveys s
         INNER JOIN users u ON u.id = s.collector_id
         WHERE s.collector_id = :collector_id
         ORDER BY s.created_at DESC
         LIMIT 20'
    );
    $stmt->execute(['collector_id' => $user['id']]);
    $totals = db()->prepare('SELECT COUNT(*) AS surveys FROM surveys WHERE collector_id = :collector_id');
    $totals->execute(['collector_id' => $user['id']]);
    $totals = $totals->fetch();
    $moduleCounts = [];
    $insights = [];
}

$surveys = $stmt->fetchAll();

app_layout('Dashboard', function () use ($user, $totals, $surveys, $modules, $moduleCounts, $insights): void {
    ?>
    <section class="page-header dashboard-hero">
        <div>
            <p class="eyebrow">Overview</p>
            <h2>Welcome back, <?= e($user['full_name']) ?></h2>
            <p class="muted">Use this portal to create survey records, review submissions, and manage village adoption field collection activity.</p>
        </div>
        <div class="hero-actions">
            <a class="button" href="<?= e(base_url('survey_create.php')) ?>">Create Survey</a>
            <?php if ($user['role'] === 'super_admin'): ?>
                <a class="button secondary" href="<?= e(base_url('admin_collectors.php')) ?>">Manage Collectors</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="grid dashboard-stats">
        <article class="card stat-card">
            <span>Total surveys</span>
            <strong><?= e((string) ($totals['surveys'] ?? 0)) ?></strong>
            <p class="muted">Submitted through this portal</p>
        </article>
        <?php if ($user['role'] === 'super_admin'): ?>
            <article class="card stat-card">
                <span>Collectors</span>
                <strong><?= e((string) ($totals['collectors'] ?? 0)) ?></strong>
                <p class="muted">Active field accounts</p>
            </article>
        <?php endif; ?>
        <article class="card stat-card">
            <span>Your role</span>
            <strong><?= e(str_replace('_', ' ', $user['role'])) ?></strong>
            <p class="muted">Access level for this portal</p>
        </article>
    </section>

    <?php if ($user['role'] === 'super_admin'): ?>
        <?php $moduleLabels = []; foreach ($modules as $moduleKey => $module) { $moduleLabels[$module['title']] = $moduleCounts[$moduleKey] ?? 0; } ?>
        <section class="grid chart-grid">
            <?php chart_card('Module-wise Response Volume', $moduleLabels); ?>
        </section>

        <section class="card">
            <div class="section-title">
                <h3>Module-wise Data Metrics</h3>
                <span class="chip">Village adoption dashboard</span>
            </div>
            <div class="grid module-metrics-grid">
                <?php foreach ($modules as $moduleKey => $module): ?>
                    <article class="card metric-mini">
                        <span><?= e($module['title']) ?></span>
                        <strong><?= e((string) ($moduleCounts[$moduleKey] ?? 0)) ?></strong>
                        <a href="<?= e(base_url('export_surveys.php?module=' . urlencode($moduleKey))) ?>">Download CSV</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card">
            <div class="section-title">
                <h3>Key Survey Results</h3>
                <span class="chip">Live graphs from submitted responses</span>
            </div>
            <div class="grid chart-grid">
                <?php chart_card('Family Type', $insights['family_type']); ?>
                <?php chart_card('House Type', $insights['house_type']); ?>
                <?php chart_card('Drinking Water Source', $insights['drinking_water_source']); ?>
                <?php chart_card('Latrine Type', $insights['latrine_type']); ?>
                <?php chart_card('Main Cooking Fuel', $insights['cooking_fuel']); ?>
                <?php chart_card('Household Members by Sex', $insights['member_sex']); ?>
                <?php chart_card('Antenatal TT-1 Status', $insights['maternal_tt1']); ?>
                <?php chart_card('Antenatal TT-2 Status', $insights['maternal_tt2']); ?>
                <?php chart_card('Child BCG Status', $insights['child_bcg']); ?>
                <?php chart_card('Child MR / Measles Status', $insights['child_mr_measles']); ?>
                <?php chart_card('Acute Illness in Last 15 Days', $insights['acute_illness_15_days']); ?>
                <?php chart_card('Acute Morbidity Treatment Received', $insights['acute_treatment']); ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="card">
        <div class="section-title">
            <h3>Recent submissions</h3>
            <span class="chip"><?= e((string) count($surveys)) ?> shown</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Village</th>
                    <th>Head of family</th>
                    <th>Visit date</th>
                    <th>Collector</th>
                    <th>Saved at</th>
                    <th>Open</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($surveys as $survey): ?>
                    <tr>
                        <td><?= e((string) $survey['id']) ?></td>
                        <td><?= e($survey['village_name']) ?></td>
                        <td><?= e($survey['head_name']) ?></td>
                        <td><?= e($survey['visit_date']) ?></td>
                        <td><?= e($survey['collector_name']) ?></td>
                        <td><?= e($survey['created_at']) ?></td>
                        <td><a href="<?= e(base_url('survey_view.php?id=' . $survey['id'])) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($surveys === []): ?>
                    <tr>
                        <td colspan="7">No survey submissions yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
});
