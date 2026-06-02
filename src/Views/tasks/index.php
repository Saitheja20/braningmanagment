<?php

$statusLabels = [
    'todo' => 'To do',
    'in_progress' => 'In progress',
    'review' => 'Review',
    'completed' => 'Completed',
];

$priorityLabels = [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent',
];
?>

<section class="module-hero compact" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Task management</p>
    <h2>Assign work, monitor progress, collect files, and keep every creative handoff moving.</h2>
  </div>
  <div class="hero-actions">
    <a class="btn btn-light" href="/projects/kanban"><i class="bi bi-kanban"></i> Kanban</a>
    <button class="btn btn-outline-light" type="button" data-bs-toggle="modal" data-bs-target="#taskModal"><i class="bi bi-plus-lg"></i> New task</button>
  </div>
</section>

<?php if ($message = flash('success')): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
  <div class="alert alert-danger"><?= e($message) ?></div>
<?php endif; ?>

<section class="row g-3 g-xl-4 dashboard-metrics mb-4">
  <div class="col-sm-6 col-xl-3" data-aos="fade-up">
    <article class="metric-card">
      <div class="metric-icon bg-blue-soft"><i class="bi bi-check2-square"></i></div>
      <div>
        <p>Total tasks</p>
        <h3><?= e((int) ($stats['total'] ?? 0)) ?></h3>
        <span class="positive">Across projects</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
    <article class="metric-card">
      <div class="metric-icon bg-green-soft"><i class="bi bi-check-circle"></i></div>
      <div>
        <p>Completed</p>
        <h3><?= e((int) ($stats['completed'] ?? 0)) ?></h3>
        <span class="positive">Closed work</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
    <article class="metric-card">
      <div class="metric-icon bg-rose-soft"><i class="bi bi-calendar-x"></i></div>
      <div>
        <p>Overdue</p>
        <h3><?= e((int) ($stats['overdue'] ?? 0)) ?></h3>
        <span class="warning">Needs unblock</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
    <article class="metric-card">
      <div class="metric-icon bg-amber-soft"><i class="bi bi-activity"></i></div>
      <div>
        <p>Avg progress</p>
        <h3><?= e((int) round((float) ($stats['avg_progress'] ?? 0))) ?>%</h3>
        <span class="positive">Checklist based</span>
      </div>
    </article>
  </div>
</section>

<section class="glass-card" data-aos="fade-up">
  <div class="card-heading">
    <div>
      <p class="eyebrow mb-1">Workbench</p>
      <h3>Task queue</h3>
    </div>
    <form class="project-filter" method="get" action="/tasks">
      <div class="search-box project-search">
        <i class="bi bi-search"></i>
        <input name="search" type="search" value="<?= e((string) ($_GET['search'] ?? '')) ?>" placeholder="Search task or project">
      </div>
      <select class="form-select" name="status" aria-label="Filter status">
        <option value="">All statuses</option>
        <?php foreach ($statusLabels as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= ($_GET['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i></button>
    </form>
  </div>

  <div class="task-workbench">
    <?php if (!$tasks): ?>
      <div class="empty-state">
        <i class="bi bi-check2-square"></i>
        <h4>No tasks found</h4>
        <p>Create a task or adjust filters.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($tasks as $task): ?>
      <?php
        $progress = (int) ($task['progress_percent'] ?? 0);
        $labelsText = (string) ($task['label_names'] ?? '');
      ?>
      <article class="task-row-card">
        <div class="task-row-main">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="min-w-0">
              <h4><a href="/tasks/detail?id=<?= e($task['id']) ?>"><?= e($task['title']) ?></a></h4>
              <p><?= e($task['project_name']) ?></p>
            </div>
            <span class="priority-pill <?= e($task['priority']) ?>"><?= e($priorityLabels[$task['priority']] ?? $task['priority']) ?></span>
          </div>
          <?php if ($labelsText): ?>
            <div class="task-label-line">
              <?php foreach (explode(', ', $labelsText) as $label): ?>
                <span><?= e($label) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="task-row-meta">
          <span><i class="bi bi-person"></i> <?= e($task['assignee_name'] ?? 'Unassigned') ?></span>
          <span><i class="bi bi-calendar-event"></i> <?= e($task['due_date'] ?: 'No due date') ?></span>
          <span><i class="bi bi-chat-left-text"></i> <?= e((int) $task['comment_count']) ?></span>
          <span><i class="bi bi-paperclip"></i> <?= e((int) $task['attachment_count']) ?></span>
        </div>

        <div class="task-progress-block">
          <div class="d-flex justify-content-between"><span>Progress</span><strong><?= e($progress) ?>%</strong></div>
          <div class="progress"><div class="progress-bar" style="width: <?= e($progress) ?>%"></div></div>
          <small><?= e((int) ($task['checklist_done'] ?? 0)) ?>/<?= e((int) ($task['checklist_total'] ?? 0)) ?> checklist items</small>
        </div>

        <div class="task-inline-controls">
          <select class="form-select form-select-sm js-task-status" data-task-id="<?= e($task['id']) ?>" aria-label="Update task status">
            <?php foreach ($statusLabels as $value => $label): ?>
              <option value="<?= e($value) ?>" <?= $task['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select form-select-sm js-task-priority" data-task-id="<?= e($task['id']) ?>" aria-label="Update priority">
            <?php foreach ($priorityLabels as $value => $label): ?>
              <option value="<?= e($value) ?>" <?= $task['priority'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/task-modal.php'; ?>
