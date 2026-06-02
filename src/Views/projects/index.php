<?php

use App\Core\Csrf;

$statusLabels = [
    'planned' => 'Planned',
    'active' => 'Active',
    'on_hold' => 'On hold',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];

$priorityLabels = [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent',
];
?>

<section class="module-hero" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Project management</p>
    <h2>Plan brand work, assign people, and keep every deadline visible.</h2>
  </div>
  <div class="hero-actions">
    <a class="btn btn-light" href="/projects/kanban"><i class="bi bi-kanban"></i> Kanban board</a>
    <button class="btn btn-outline-light" type="button" data-bs-toggle="modal" data-bs-target="#projectModal">
      <i class="bi bi-plus-lg"></i> New project
    </button>
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
      <div class="metric-icon bg-blue-soft"><i class="bi bi-folder2-open"></i></div>
      <div>
        <p>Total projects</p>
        <h3><?= e((int) ($stats['total'] ?? 0)) ?></h3>
        <span class="positive">Portfolio scope</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
    <article class="metric-card">
      <div class="metric-icon bg-green-soft"><i class="bi bi-lightning-charge"></i></div>
      <div>
        <p>Active</p>
        <h3><?= e((int) ($stats['active'] ?? 0)) ?></h3>
        <span class="positive">In production</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
    <article class="metric-card">
      <div class="metric-icon bg-amber-soft"><i class="bi bi-pause-circle"></i></div>
      <div>
        <p>On hold</p>
        <h3><?= e((int) ($stats['on_hold'] ?? 0)) ?></h3>
        <span class="warning">Needs attention</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
    <article class="metric-card">
      <div class="metric-icon bg-rose-soft"><i class="bi bi-calendar-x"></i></div>
      <div>
        <p>Overdue</p>
        <h3><?= e((int) ($stats['overdue'] ?? 0)) ?></h3>
        <span class="warning">Past deadline</span>
      </div>
    </article>
  </div>
</section>

<section class="glass-card" data-aos="fade-up">
  <div class="card-heading">
    <div>
      <p class="eyebrow mb-1">Projects</p>
      <h3>All workstreams</h3>
    </div>
    <form class="project-filter" method="get" action="/projects">
      <div class="search-box project-search">
        <i class="bi bi-search"></i>
        <input name="search" type="search" value="<?= e((string) ($_GET['search'] ?? '')) ?>" placeholder="Search projects or clients">
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

  <div class="project-table">
    <?php if (!$projects): ?>
      <div class="empty-state">
        <i class="bi bi-folder-plus"></i>
        <h4>No projects yet</h4>
        <p>Create the first project to begin tracking brand work.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($projects as $project): ?>
      <?php
        $progress = (int) ($project['total_tasks'] ?? 0) > 0
            ? round(((int) $project['completed_tasks'] / (int) $project['total_tasks']) * 100)
            : 0;
        $memberIds = $project['member_ids_csv'] ? array_map('intval', explode(',', $project['member_ids_csv'])) : [];
      ?>
      <article class="project-card-row">
        <div class="project-main">
          <span class="project-code"><?= e(strtoupper(substr($project['name'], 0, 2))) ?></span>
          <div class="min-w-0">
            <h4><?= e($project['name']) ?></h4>
            <p><?= e($project['company_name'] ?? 'No client assigned') ?></p>
          </div>
        </div>

        <div class="project-meta">
          <span class="status-pill <?= e($project['status']) ?>"><?= e($statusLabels[$project['status']] ?? $project['status']) ?></span>
          <span class="priority-pill <?= e($project['priority']) ?>"><?= e($priorityLabels[$project['priority']] ?? $project['priority']) ?></span>
        </div>

        <div class="progress-block">
          <div class="d-flex justify-content-between"><span>Progress</span><strong><?= e($progress) ?>%</strong></div>
          <div class="progress"><div class="progress-bar" style="width: <?= e($progress) ?>%"></div></div>
          <small><?= e((int) ($project['completed_tasks'] ?? 0)) ?>/<?= e((int) ($project['total_tasks'] ?? 0)) ?> tasks complete</small>
        </div>

        <div class="deadline-block">
          <span><?= e($project['due_date'] ?: 'No deadline') ?></span>
          <small><?= e((int) ($project['member_count'] ?? 0)) ?> assigned</small>
        </div>

        <div class="status-update">
          <select class="form-select form-select-sm js-project-status" data-project-id="<?= e($project['id']) ?>" aria-label="Update project status">
            <?php foreach ($statusLabels as $value => $label): ?>
              <option value="<?= e($value) ?>" <?= $project['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="row-actions">
          <button
            class="icon-btn subtle js-edit-project"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#projectModal"
            data-project='<?= e(json_encode([
                'id' => $project['id'],
                'client_id' => $project['client_id'],
                'name' => $project['name'],
                'description' => $project['description'],
                'status' => $project['status'],
                'priority' => $project['priority'],
                'start_date' => $project['start_date'],
                'due_date' => $project['due_date'],
                'budget' => $project['budget'],
                'member_ids' => $memberIds,
            ])) ?>'
            aria-label="Edit project"
          >
            <i class="bi bi-pencil"></i>
          </button>
          <form method="post" action="/projects/delete" class="mb-0 js-confirm-delete">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= e($project['id']) ?>">
            <button class="icon-btn subtle danger" type="submit" aria-label="Delete project"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" method="post" action="/projects/store" id="projectForm">
      <?= Csrf::field() ?>
      <input type="hidden" name="id" id="project_id">
      <div class="modal-header">
        <div>
          <p class="eyebrow mb-1">Project setup</p>
          <h2 class="modal-title h5" id="projectModalLabel">Create project</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label" for="project_name">Project name</label>
            <input class="form-control" id="project_name" name="name" required>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="project_client_id">Client</label>
            <select class="form-select" id="project_client_id" name="client_id">
              <option value="">Unassigned</option>
              <?php foreach ($clients as $client): ?>
                <option value="<?= e($client['id']) ?>"><?= e($client['company_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="project_description">Description</label>
            <textarea class="form-control" id="project_description" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="project_status">Status</label>
            <select class="form-select" id="project_status" name="status">
              <?php foreach ($statusLabels as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="project_priority">Priority</label>
            <select class="form-select" id="project_priority" name="priority">
              <?php foreach ($priorityLabels as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="project_start_date">Start date</label>
            <input class="form-control" id="project_start_date" name="start_date" type="date">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="project_due_date">Due date</label>
            <input class="form-control" id="project_due_date" name="due_date" type="date">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="project_budget">Budget</label>
            <input class="form-control" id="project_budget" name="budget" type="number" min="0" step="0.01">
          </div>
          <div class="col-md-8">
            <label class="form-label" for="project_member_ids">Assign employees</label>
            <select class="form-select" id="project_member_ids" name="member_ids[]" multiple size="4">
              <?php foreach ($employees as $employee): ?>
                <option value="<?= e($employee['id']) ?>"><?= e($employee['name']) ?> - <?= e($employee['role_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save project</button>
      </div>
    </form>
  </div>
</div>
