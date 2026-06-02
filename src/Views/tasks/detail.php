<?php

use App\Core\Csrf;

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

$selectedLabelIds = array_map(fn ($label) => (int) $label['id'], $task['labels']);
?>

<section class="module-hero compact" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Task detail</p>
    <h2><?= e($task['title']) ?></h2>
  </div>
  <div class="hero-actions">
    <a class="btn btn-light" href="/tasks"><i class="bi bi-arrow-left"></i> Back to tasks</a>
    <a class="btn btn-outline-light" href="/projects/kanban"><i class="bi bi-kanban"></i> Kanban</a>
  </div>
</section>

<?php if ($message = flash('success')): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
  <div class="alert alert-danger"><?= e($message) ?></div>
<?php endif; ?>

<section class="row g-3 g-xl-4">
  <div class="col-xl-7" data-aos="fade-up">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Edit task</p>
          <h3>Assignment and schedule</h3>
        </div>
        <span class="status-pill <?= e($task['status']) ?>"><?= e($statusLabels[$task['status']] ?? $task['status']) ?></span>
      </div>

      <form method="post" action="/tasks/update">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= e($task['id']) ?>">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label" for="task_title">Title</label>
            <input class="form-control" id="task_title" name="title" value="<?= e($task['title']) ?>" required>
          </div>
          <div class="col-md-5">
            <label class="form-label" for="task_project_id">Project</label>
            <select class="form-select" id="task_project_id" name="project_id" required>
              <?php foreach ($projects as $project): ?>
                <option value="<?= e($project['id']) ?>" <?= (int) $task['project_id'] === (int) $project['id'] ? 'selected' : '' ?>><?= e($project['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="task_description">Description</label>
            <textarea class="form-control" id="task_description" name="description" rows="4"><?= e($task['description'] ?? '') ?></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_status">Status</label>
            <select class="form-select" id="task_status" name="status">
              <?php foreach ($statusLabels as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $task['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_priority">Priority</label>
            <select class="form-select" id="task_priority" name="priority">
              <?php foreach ($priorityLabels as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $task['priority'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_assigned_to">Assignee</label>
            <select class="form-select" id="task_assigned_to" name="assigned_to">
              <option value="">Unassigned</option>
              <?php foreach ($employees as $employee): ?>
                <option value="<?= e($employee['id']) ?>" <?= (int) ($task['assigned_to'] ?? 0) === (int) $employee['id'] ? 'selected' : '' ?>><?= e($employee['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="task_estimated_hours">Estimated hours</label>
            <input class="form-control" id="task_estimated_hours" name="estimated_hours" type="number" min="0" step="0.25" value="<?= e($task['estimated_hours'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="task_start_date">Start date</label>
            <input class="form-control" id="task_start_date" name="start_date" type="date" value="<?= e($task['start_date'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="task_due_date">Due date</label>
            <input class="form-control" id="task_due_date" name="due_date" type="date" value="<?= e($task['due_date'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label" for="task_label_ids">Labels</label>
            <select class="form-select" id="task_label_ids" name="label_ids[]" multiple size="5">
              <?php foreach ($labels as $label): ?>
                <option value="<?= e($label['id']) ?>" <?= in_array((int) $label['id'], $selectedLabelIds, true) ? 'selected' : '' ?>><?= e($label['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Save task</button>
        </div>
      </form>
    </article>
  </div>

  <div class="col-xl-5" data-aos="fade-up" data-aos-delay="100">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Progress</p>
          <h3>Checklist</h3>
        </div>
        <strong class="task-progress-value" data-task-progress="<?= e($task['progress_percent']) ?>"><?= e($task['progress_percent']) ?>%</strong>
      </div>
      <div class="progress mb-3"><div class="progress-bar js-detail-progress" style="width: <?= e($task['progress_percent']) ?>%"></div></div>

      <form class="checklist-add js-checklist-add" data-task-id="<?= e($task['id']) ?>">
        <input class="form-control" name="title" placeholder="Add checklist item" required>
        <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
      </form>

      <div class="checklist-list mt-3">
        <?php foreach ($task['checklist'] as $item): ?>
          <label class="checklist-row">
            <input class="form-check-input js-checklist-toggle" type="checkbox" data-item-id="<?= e($item['id']) ?>" <?= (int) $item['is_completed'] === 1 ? 'checked' : '' ?>>
            <span><?= e($item['title']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-6" data-aos="fade-up">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Discussion</p>
          <h3>Comments</h3>
        </div>
      </div>
      <form class="comment-form js-comment-form" data-task-id="<?= e($task['id']) ?>">
        <textarea class="form-control" name="comment" rows="3" placeholder="Add an update for the team" required></textarea>
        <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Comment</button>
      </form>
      <div class="comment-list mt-3">
        <?php foreach ($task['comments'] as $comment): ?>
          <article class="comment-item">
            <strong><?= e($comment['user_name'] ?? 'System') ?></strong>
            <p><?= e($comment['comment']) ?></p>
            <small><?= e($comment['created_at']) ?></small>
          </article>
        <?php endforeach; ?>
      </div>
    </article>
  </div>

  <div class="col-xl-6" data-aos="fade-up" data-aos-delay="100">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Files</p>
          <h3>Attachments</h3>
        </div>
      </div>
      <form class="attachment-form js-attachment-form" data-task-id="<?= e($task['id']) ?>" enctype="multipart/form-data">
        <input class="form-control" name="attachment" type="file" required>
        <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Upload</button>
      </form>
      <div class="attachment-list mt-3">
        <?php foreach ($task['attachments'] as $attachment): ?>
          <div class="attachment-item">
            <i class="bi bi-paperclip"></i>
            <div>
              <strong><?= e($attachment['original_name']) ?></strong>
              <small><?= e(number_format(((int) $attachment['size_bytes']) / 1024, 1)) ?> KB</small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</section>
