<?php use App\Core\Csrf; ?>

<section class="module-hero" data-aos="fade-up">
  <div><p class="eyebrow mb-2">Employee profile</p><h2><?= e($employee['name']) ?></h2></div>
  <a class="btn btn-light" href="/employees"><i class="bi bi-arrow-left"></i> Directory</a>
</section>

<?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>

<section class="row g-3 g-xl-4">
  <div class="col-xl-5">
    <article class="glass-card">
      <div class="card-heading"><div><p class="eyebrow mb-1">Profile</p><h3>Employment details</h3></div></div>
      <form method="post" action="/employees/update" class="row g-3">
        <?= Csrf::field() ?><input type="hidden" name="id" value="<?= e($employee['id']) ?>">
        <input type="hidden" name="user_id" value="<?= e($employee['user_id']) ?>">
        <div class="col-md-6"><label class="form-label">Employee code</label><input class="form-control" name="employee_code" value="<?= e($employee['employee_code']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?= $employee['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $employee['status']==='inactive'?'selected':'' ?>>Inactive</option><option value="terminated" <?= $employee['status']==='terminated'?'selected':'' ?>>Terminated</option></select></div>
        <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" name="designation" value="<?= e($employee['designation'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Department</label><input class="form-control" name="department" value="<?= e($employee['department'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Joining date</label><input class="form-control" name="joining_date" type="date" value="<?= e($employee['joining_date'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Hourly rate</label><input class="form-control" name="hourly_rate" type="number" step="0.01" value="<?= e($employee['hourly_rate'] ?? '') ?>"></div>
        <input type="hidden" name="employment_type" value="<?= e($employee['employment_type']) ?>">
        <div class="col-12"><button class="btn btn-primary">Save profile</button></div>
      </form>
    </article>
  </div>
  <div class="col-xl-7">
    <article class="glass-card">
      <div class="card-heading"><div><p class="eyebrow mb-1">Assigned tasks</p><h3>Current workload</h3></div></div>
      <div class="task-workbench">
        <?php foreach ($employee['tasks'] as $task): ?>
          <div class="task-row-card compact-row"><div><strong><?= e($task['title']) ?></strong><p><?= e($task['project_name']) ?></p></div><span class="priority-pill <?= e($task['priority']) ?>"><?= e($task['priority']) ?></span><span class="status-pill <?= e($task['status']) ?>"><?= e(str_replace('_', ' ', $task['status'])) ?></span></div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-6"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Attendance</p><h3>Daily tracking</h3></div></div>
    <form method="post" action="/employees/attendance" class="row g-2 mb-3"><?= Csrf::field() ?><input type="hidden" name="employee_id" value="<?= e($employee['id']) ?>"><div class="col-md-4"><input class="form-control" name="attendance_date" type="date" value="<?= e(date('Y-m-d')) ?>"></div><div class="col-md-4"><select class="form-select" name="status"><option value="present">Present</option><option value="late">Late</option><option value="half_day">Half day</option><option value="leave">Leave</option><option value="absent">Absent</option></select></div><div class="col-md-4"><button class="btn btn-primary w-100">Save</button></div></form>
    <div class="milestone-list"><?php foreach ($employee['attendance'] as $row): ?><div class="milestone-row"><span class="timeline-dot <?= $row['status']==='present'?'green':'amber' ?>"></span><strong><?= e($row['attendance_date']) ?></strong><span class="status-pill <?= e($row['status']) ?>"><?= e($row['status']) ?></span><small><?= e($row['notes'] ?? '') ?></small></div><?php endforeach; ?></div>
  </article></div>
  <div class="col-xl-6"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Work logs</p><h3>Productivity notes</h3></div></div>
    <form method="post" action="/employees/work-log" class="row g-2 mb-3"><?= Csrf::field() ?><input type="hidden" name="employee_id" value="<?= e($employee['id']) ?>"><div class="col-md-4"><input class="form-control" name="log_date" type="date" value="<?= e(date('Y-m-d')) ?>"></div><div class="col-md-3"><input class="form-control" name="hours" type="number" step="0.25" placeholder="Hours"></div><div class="col-md-5"><button class="btn btn-primary w-100">Add work log</button></div><div class="col-12"><textarea class="form-control" name="description" rows="2" placeholder="Work completed"></textarea></div></form>
    <div class="comment-list"><?php foreach ($employee['work_logs'] as $log): ?><article class="comment-item"><strong><?= e($log['log_date']) ?> - <?= e($log['hours']) ?>h</strong><p><?= e($log['description'] ?? 'No notes') ?></p><small><?= e($log['task_title'] ?? 'General work') ?></small></article><?php endforeach; ?></div>
  </article></div>
</section>
