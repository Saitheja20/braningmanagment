<?php

use App\Core\Csrf;

$taskModalStatuses = [
    'todo' => 'To do',
    'in_progress' => 'In progress',
    'review' => 'Review',
    'completed' => 'Completed',
];
?>

<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" method="post" action="/tasks/store">
      <?= Csrf::field() ?>
      <input type="hidden" name="redirect_to" value="/tasks">
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
            <label class="form-label" for="modal_task_title">Task title</label>
            <input class="form-control" id="modal_task_title" name="title" required>
          </div>
          <div class="col-md-5">
            <label class="form-label" for="modal_task_project_id">Project</label>
            <select class="form-select" id="modal_task_project_id" name="project_id" required>
              <option value="">Select project</option>
              <?php foreach ($projects as $project): ?>
                <option value="<?= e($project['id']) ?>"><?= e($project['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label" for="modal_task_description">Description</label>
            <textarea class="form-control" id="modal_task_description" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="modal_task_status">Status</label>
            <select class="form-select" id="modal_task_status" name="status">
              <?php foreach ($taskModalStatuses as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="modal_task_priority">Priority</label>
            <select class="form-select" id="modal_task_priority" name="priority">
              <?php foreach ($priorities as $priority): ?>
                <option value="<?= e($priority) ?>"><?= e(ucfirst($priority)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="modal_task_assigned_to">Assignee</label>
            <select class="form-select" id="modal_task_assigned_to" name="assigned_to">
              <option value="">Unassigned</option>
              <?php foreach ($employees as $employee): ?>
                <option value="<?= e($employee['id']) ?>"><?= e($employee['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="modal_task_estimated_hours">Estimated hours</label>
            <input class="form-control" id="modal_task_estimated_hours" name="estimated_hours" type="number" min="0" step="0.25">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modal_task_due_date">Due date</label>
            <input class="form-control" id="modal_task_due_date" name="due_date" type="date">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="modal_task_label_ids">Labels</label>
            <select class="form-select" id="modal_task_label_ids" name="label_ids[]" multiple size="4">
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
