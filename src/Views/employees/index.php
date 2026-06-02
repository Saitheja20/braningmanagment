<?php use App\Core\Csrf; ?>

<section class="module-hero" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Employee management</p>
    <h2>Profiles, attendance, assigned work, and productivity analytics for the agency team.</h2>
  </div>
  <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#employeeModal"><i class="bi bi-person-plus"></i> Add employee</button>
</section>

<?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>

<section class="row g-3 g-xl-4 dashboard-metrics mb-4">
  <div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-icon bg-blue-soft"><i class="bi bi-people"></i></div><div><p>Total employees</p><h3><?= e((int)($analytics['total_employees'] ?? 0)) ?></h3><span class="positive">Team size</span></div></article></div>
  <div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-icon bg-green-soft"><i class="bi bi-person-check"></i></div><div><p>Active</p><h3><?= e((int)($analytics['active_employees'] ?? 0)) ?></h3><span class="positive">Available talent</span></div></article></div>
  <div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-icon bg-amber-soft"><i class="bi bi-calendar-check"></i></div><div><p>Present today</p><h3><?= e((int)($analytics['present_today'] ?? 0)) ?></h3><span class="warning">Attendance</span></div></article></div>
  <div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-icon bg-rose-soft"><i class="bi bi-clock-history"></i></div><div><p>Weekly hours</p><h3><?= e(number_format((float)($analytics['weekly_hours'] ?? 0), 1)) ?></h3><span class="positive">Logged work</span></div></article></div>
</section>

<section class="glass-card" data-aos="fade-up">
  <div class="card-heading"><div><p class="eyebrow mb-1">Directory</p><h3>Employee profiles</h3></div></div>
  <div class="employee-grid">
    <?php foreach ($employees as $employee): ?>
      <?php $done = (int)($employee['completed_tasks'] ?? 0); $total = max(1, (int)($employee['assigned_tasks'] ?? 0)); $score = (int)round(($done / $total) * 100); ?>
      <article class="employee-card">
        <div class="employee-head">
          <div class="avatar"><?= e(strtoupper(substr($employee['name'], 0, 1))) ?></div>
          <div class="min-w-0"><h4><?= e($employee['name']) ?></h4><p><?= e($employee['designation'] ?? 'Team member') ?></p></div>
        </div>
        <div class="employee-meta"><span><?= e($employee['department'] ?? 'General') ?></span><span><?= e($employee['employee_code']) ?></span></div>
        <div class="progress"><div class="progress-bar" style="width: <?= e($score) ?>%"></div></div>
        <small><?= e($done) ?>/<?= e((int)$employee['assigned_tasks']) ?> assigned tasks complete</small>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="status-pill <?= e($employee['status']) ?>"><?= e($employee['status']) ?></span>
          <a class="btn btn-sm btn-outline-primary" href="/employees/detail?id=<?= e($employee['id']) ?>">Open</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" method="post" action="/employees/store">
      <?= Csrf::field() ?>
      <div class="modal-header"><h2 class="h5 mb-0">Create employee profile</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">User</label><select class="form-select" name="user_id" required><option value="">Select user</option><?php foreach ($users as $user): ?><option value="<?= e($user['id']) ?>"><?= e($user['name']) ?> - <?= e($user['email']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label class="form-label">Employee code</label><input class="form-control" name="employee_code" required></div>
          <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" name="designation"></div>
          <div class="col-md-6"><label class="form-label">Department</label><input class="form-control" name="department"></div>
          <div class="col-md-4"><label class="form-label">Joining date</label><input class="form-control" name="joining_date" type="date"></div>
          <div class="col-md-4"><label class="form-label">Type</label><select class="form-select" name="employment_type"><option value="full_time">Full time</option><option value="part_time">Part time</option><option value="contract">Contract</option><option value="intern">Intern</option></select></div>
          <div class="col-md-4"><label class="form-label">Hourly rate</label><input class="form-control" name="hourly_rate" type="number" min="0" step="0.01"></div>
          <input type="hidden" name="status" value="active">
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">Save employee</button></div>
    </form>
  </div>
</div>
