<?php

use App\Core\Csrf;

$taskLabels = [
    'todo' => 'To do',
    'in_progress' => 'In progress',
    'review' => 'Review',
    'completed' => 'Completed',
];

$projectOptions = array_map(fn ($project) => [
    'id' => $project['id'],
    'name' => $project['name'],
    'due_date' => $project['due_date'],
    'status' => $project['status'],
], $projects);
?>

<section class="module-hero compact" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Execution board</p>
    <h2>Kanban, milestones, and deadlines for active brand work.</h2>
  </div>
  <div class="hero-actions">
    <a class="btn btn-light" href="/projects"><i class="bi bi-list-ul"></i> Project list</a>
    <button class="btn btn-outline-light" type="button" data-bs-toggle="modal" data-bs-target="#taskModal"><i class="bi bi-plus-lg"></i> New task</button>
    <button class="btn btn-outline-light" type="button" data-bs-toggle="modal" data-bs-target="#milestoneModal"><i class="bi bi-flag"></i> Milestone</button>
  </div>
</section>

<?php if ($message = flash('success')): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
  <div class="alert alert-danger"><?= e($message) ?></div>
<?php endif; ?>

<section class="kanban-board" data-aos="fade-up">
  <?php foreach ($taskLabels as $status => $label): ?>
    <div class="kanban-column" data-status="<?= e($status) ?>">
      <div class="kanban-heading">
        <h3><?= e($label) ?></h3>
        <span><?= e(count($tasksByStatus[$status] ?? [])) ?></span>
      </div>
      <div class="kanban-dropzone">
        <?php foreach (($tasksByStatus[$status] ?? []) as $task): ?>
          <article class="task-card" draggable="true" data-task-id="<?= e($task['id']) ?>">
            <div class="d-flex align-items-start justify-content-between gap-2">
              <h4><?= e($task['title']) ?></h4>
              <span class="priority-pill <?= e($task['priority']) ?>"><?= e($task['priority']) ?></span>
            </div>
            <p><?= e($task['project_name']) ?></p>
            <div class="task-footer">
              <span><i class="bi bi-person"></i> <?= e($task['assignee_name'] ?? 'Unassigned') ?></span>
              <span><i class="bi bi-calendar-event"></i> <?= e($task['due_date'] ?: 'No due date') ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-7" data-aos="fade-up">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Milestones</p>
          <h3>Upcoming checkpoints</h3>
        </div>
      </div>
      <div class="milestone-list">
        <?php if (!$milestones): ?>
          <div class="empty-state small-state">
            <i class="bi bi-flag"></i>
            <p>No milestones yet.</p>
          </div>
        <?php endif; ?>
        <?php foreach ($milestones as $milestone): ?>
          <div class="milestone-row">
            <span class="timeline-dot <?= $milestone['status'] === 'completed' ? 'green' : 'amber' ?>"></span>
            <div>
              <strong><?= e($milestone['title']) ?></strong>
              <small><?= e($milestone['project_name']) ?></small>
            </div>
            <span class="status-pill <?= e($milestone['status']) ?>"><?= e(str_replace('_', ' ', $milestone['status'])) ?></span>
            <time><?= e($milestone['due_date']) ?></time>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>

  <div class="col-xl-5" data-aos="fade-up" data-aos-delay="100">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Calendar</p>
          <h3>Deadline snapshot</h3>
        </div>
      </div>
      <div class="deadline-calendar">
        <?php foreach ($projectOptions as $project): ?>
          <?php if (!$project['due_date']) continue; ?>
          <div class="calendar-event">
            <time><?= e(date('M d', strtotime($project['due_date']))) ?></time>
            <div>
              <strong><?= e($project['name']) ?></strong>
              <small><?= e(str_replace('_', ' ', $project['status'])) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</section>

<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" method="post" action="/tasks/store">
      <?= Csrf::field() ?>
      <input type="hidden" name="redirect_to" value="/projects/kanban">
      <div class="modal-header">
        <div>
          <p class="eyebrow mb-1">Task management</p>
          <h2 class="modal-title h5" id="taskModalLabel">Create task</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label" for="task_title">Task title</label>
            <input class="form-control" id="task_title" name="title" required>
          </div>
          <div class="col-md-5">
            <label class="form-label" for="task_project_id">Project</label>
            <select class="form-select" id="task_project_id" name="project_id" required>
              <option value="">Select project</option>
              <?php foreach ($projects as $project): ?>
                <option value="<?= e($project['id']) ?>"><?= e($project['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="task_description">Description</label>
            <textarea class="form-control" id="task_description" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_status">Status</label>
            <select class="form-select" id="task_status" name="status">
              <?php foreach ($taskLabels as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_priority">Priority</label>
            <select class="form-select" id="task_priority" name="priority">
              <?php foreach ($priorities as $priority): ?>
                <option value="<?= e($priority) ?>"><?= e(ucfirst($priority)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_assigned_to">Assignee</label>
            <select class="form-select" id="task_assigned_to" name="assigned_to">
              <option value="">Unassigned</option>
              <?php foreach ($employees as $employee): ?>
                <option value="<?= e($employee['id']) ?>"><?= e($employee['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_estimated_hours">Estimated hours</label>
            <input class="form-control" id="task_estimated_hours" name="estimated_hours" type="number" min="0" step="0.25">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_due_date">Due date</label>
            <input class="form-control" id="task_due_date" name="due_date" type="date">
          </div>
          <div class="col-md-9">
            <label class="form-label" for="task_label_ids">Labels</label>
            <select class="form-select" id="task_label_ids" name="label_ids[]" multiple size="3">
              <?php foreach ($labels as $label): ?>
                <option value="<?= e($label['id']) ?>"><?= e($label['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save task</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="milestoneModal" tabindex="-1" aria-labelledby="milestoneModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/milestones/store">
      <?= Csrf::field() ?>
      <div class="modal-header">
        <div>
          <p class="eyebrow mb-1">Milestone</p>
          <h2 class="modal-title h5" id="milestoneModalLabel">Create milestone</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label" for="milestone_title">Title</label>
            <input class="form-control" id="milestone_title" name="title" required>
          </div>
          <div class="col-12">
            <label class="form-label" for="milestone_project_id">Project</label>
            <select class="form-select" id="milestone_project_id" name="project_id" required>
              <option value="">Select project</option>
              <?php foreach ($projects as $project): ?>
                <option value="<?= e($project['id']) ?>"><?= e($project['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="milestone_description">Description</label>
            <textarea class="form-control" id="milestone_description" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="milestone_due_date">Due date</label>
            <input class="form-control" id="milestone_due_date" name="due_date" type="date" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="milestone_status">Status</label>
            <select class="form-select" id="milestone_status" name="status">
              <option value="pending">Pending</option>
              <option value="in_progress">In progress</option>
              <option value="completed">Completed</option>
              <option value="missed">Missed</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save milestone</button>
      </div>
    </form>
  </div>
</div>
