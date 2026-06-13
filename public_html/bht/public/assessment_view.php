<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_login();

$user = current_user();
$assessment = load_assessment_for_user((int) ($_GET['id'] ?? 0), $user);
if (!$assessment) {
    flash('error', 'Assessment not found.');
    redirect_to('index.php');
}

$payload = json_decode((string) $assessment['payload_json'], true) ?: [];
$modules = cga_modules();

app_layout('View CGA', function () use ($assessment, $payload, $modules): void {
    ?>
    <section class="page-header">
        <div>
            <p class="eyebrow">CGA record</p>
            <h2><?= e($assessment['uid']) ?></h2>
            <p class="muted"><?= e($assessment['patient_name']) ?> | Collected by <?= e($assessment['collector_name']) ?></p>
        </div>
        <div class="hero-actions">
            <a class="button secondary" href="<?= e(base_url('assessment_form.php?id=' . $assessment['id'])) ?>">Edit</a>
            <a class="button" href="<?= e(base_url('assessment_pdf.php?id=' . $assessment['id'])) ?>" target="_blank">Print / PDF</a>
        </div>
    </section>

    <?php foreach ($modules as $moduleKey => $module): ?>
        <section class="card">
            <div class="section-title"><h3><?= e($module['title']) ?></h3></div>
            <div class="grid two-up detail-grid">
                <?php foreach ($module['fields'] as $field): ?>
                    <?php if (($field['type'] ?? '') === 'heading'): ?>
                        <div class="form-heading view-heading"><?= e($field['label']) ?></div>
                    <?php else: ?>
                        <p><strong><?= e($field['label']) ?>:</strong> <?= nl2br(e((string) ($payload[$moduleKey][$field['name']] ?? ''))) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <?php
});
