<?php use App\Core\Csrf; ?>

<section class="module-hero compact" data-aos="fade-up">
  <div><p class="eyebrow mb-2">Client portal</p><h2><?= e($client['company_name']) ?> project progress, approvals, files, feedback, and invoices.</h2></div>
  <span class="btn btn-light disabled"><i class="bi bi-shield-check"></i> Secure client access</span>
</section>

<?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>

<section class="row g-3 g-xl-4">
  <div class="col-xl-7"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Projects</p><h3>Progress overview</h3></div></div><div class="project-list">
    <?php foreach ($portal['projects'] as $project): ?><?php $progress = (int)$project['total_tasks'] > 0 ? (int)round(((int)$project['completed_tasks'] / (int)$project['total_tasks']) * 100) : 0; ?>
      <div class="project-row"><div class="project-name"><span class="project-code"><?= e(strtoupper(substr($project['name'],0,2))) ?></span><div><strong><?= e($project['name']) ?></strong><small><?= e($project['due_date'] ?: 'No deadline') ?></small></div></div><div class="progress-block"><div class="d-flex justify-content-between"><span>Progress</span><strong><?= e($progress) ?>%</strong></div><div class="progress"><div class="progress-bar" style="width: <?= e($progress) ?>%"></div></div></div><span class="status-pill <?= e($project['status']) ?>"><?= e(str_replace('_',' ', $project['status'])) ?></span></div>
    <?php endforeach; ?>
  </div></article></div>
  <div class="col-xl-5"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Feedback</p><h3>Message the agency</h3></div></div>
    <form method="post" action="/portal/feedback" class="comment-form"><?= Csrf::field() ?><select class="form-select" name="project_id" required><option value="">Select project</option><?php foreach ($portal['projects'] as $project): ?><option value="<?= e($project['id']) ?>"><?= e($project['name']) ?></option><?php endforeach; ?></select><textarea class="form-control" name="message" rows="4" placeholder="Share feedback or a change request" required></textarea><button class="btn btn-primary">Send feedback</button></form>
  </article></div>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-6"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Approvals</p><h3>Design review</h3></div></div><div class="approval-list">
    <?php foreach ($portal['approvals'] as $approval): ?><article class="approval-card"><div><strong><?= e($approval['title']) ?></strong><p><?= e($approval['project_name']) ?></p><span class="status-pill <?= e($approval['status']) ?>"><?= e($approval['status']) ?></span></div><?php if ($approval['file_path']): ?><div class="file-preview"><i class="bi bi-file-earmark-image"></i><small><?= e($approval['preview_mime'] ?? 'Preview') ?></small></div><?php endif; ?><form method="post" action="/portal/approval" class="approval-actions"><?= Csrf::field() ?><input type="hidden" name="approval_id" value="<?= e($approval['id']) ?>"><textarea class="form-control" name="feedback" rows="2" placeholder="Approval feedback"></textarea><div class="d-flex gap-2"><button class="btn btn-success btn-sm" name="status" value="approved">Approve</button><button class="btn btn-outline-danger btn-sm" name="status" value="rejected">Reject</button></div></form></article><?php endforeach; ?>
  </div></article></div>
  <div class="col-xl-6"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Files and invoices</p><h3>Downloads</h3></div></div><div class="attachment-list">
    <?php foreach ($portal['files'] as $file): ?><a class="attachment-item text-decoration-none" href="/portal/file?path=<?= e(urlencode($file['storage_path'])) ?>"><i class="bi bi-download"></i><div><strong><?= e($file['original_name']) ?></strong><small><?= e($file['project_name']) ?> - <?= e($file['task_title']) ?></small></div></a><?php endforeach; ?>
    <?php foreach ($portal['invoices'] as $invoice): ?><div class="attachment-item"><i class="bi bi-receipt"></i><div><strong><?= e($invoice['invoice_number']) ?> - $<?= e(number_format((float)$invoice['total_amount'], 2)) ?></strong><small><?= e($invoice['status']) ?> due <?= e($invoice['due_date']) ?></small></div></div><?php endforeach; ?>
  </div></article></div>
</section>
