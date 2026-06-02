<section class="module-hero compact" data-aos="fade-up">
  <div><p class="eyebrow mb-2">Real-time hub</p><h2>Notifications, internal chat, live task updates, and the agency activity feed.</h2></div>
  <span class="btn btn-light disabled"><i class="bi bi-broadcast"></i> AJAX polling</span>
</section>

<section class="row g-3 g-xl-4 realtime-hub" data-realtime-hub>
  <div class="col-xl-4"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Notifications</p><h3>Live alerts</h3></div><span class="badge text-bg-primary js-live-count">0</span></div><div class="notification-stream js-live-notifications"></div></article></div>
  <div class="col-xl-4"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Chat</p><h3>Internal messaging</h3></div></div>
    <form class="chat-form js-chat-form"><select class="form-select" name="receiver_id"><option value="">Team channel</option><?php foreach ($users as $user): ?><option value="<?= e($user['id']) ?>"><?= e($user['name']) ?></option><?php endforeach; ?></select><textarea class="form-control" name="body" rows="3" placeholder="Send an internal update" required></textarea><button class="btn btn-primary">Send</button></form>
    <div class="chat-stream js-live-messages mt-3"></div>
  </article></div>
  <div class="col-xl-4"><article class="glass-card"><div class="card-heading"><div><p class="eyebrow mb-1">Activity</p><h3>Latest events</h3></div></div><div class="activity-timeline js-live-activities"></div></article></div>
</section>

<section class="glass-card mt-4"><div class="card-heading"><div><p class="eyebrow mb-1">Task updates</p><h3>Live task movement</h3></div></div><div class="task-workbench js-live-tasks"></div></section>
