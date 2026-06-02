<section class="dashboard-hero" data-aos="fade-up">
  <div>
    <p class="eyebrow mb-2">Welcome back, <?= e(explode(' ', $user['name'] ?? 'Admin')[0]) ?></p>
    <h2>Brand operations, deadlines, and client approvals in one calm workspace.</h2>
    <p class="mb-0">Track active campaigns, creative velocity, file handoffs, and project health across the agency.</p>
  </div>
  <div class="hero-actions">
    <button class="btn btn-light"><i class="bi bi-plus-lg"></i> New project</button>
    <button class="btn btn-outline-light"><i class="bi bi-upload"></i> Upload files</button>
  </div>
</section>

<section class="row g-3 g-xl-4 dashboard-metrics">
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
    <article class="metric-card">
      <div class="metric-icon bg-blue-soft"><i class="bi bi-kanban"></i></div>
      <div>
        <p>Active projects</p>
        <h3>42</h3>
        <span class="positive"><i class="bi bi-arrow-up-right"></i> 12% this month</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
    <article class="metric-card">
      <div class="metric-icon bg-green-soft"><i class="bi bi-check2-circle"></i></div>
      <div>
        <p>Tasks completed</p>
        <h3>318</h3>
        <span class="positive"><i class="bi bi-arrow-up-right"></i> 28 ahead</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
    <article class="metric-card">
      <div class="metric-icon bg-amber-soft"><i class="bi bi-clock-history"></i></div>
      <div>
        <p>Pending approvals</p>
        <h3>16</h3>
        <span class="warning"><i class="bi bi-exclamation-circle"></i> 5 due today</span>
      </div>
    </article>
  </div>
  <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
    <article class="metric-card">
      <div class="metric-icon bg-rose-soft"><i class="bi bi-currency-dollar"></i></div>
      <div>
        <p>Monthly revenue</p>
        <h3>$84.2k</h3>
        <span class="positive"><i class="bi bi-arrow-up-right"></i> 9.4% growth</span>
      </div>
    </article>
  </div>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-8" data-aos="fade-up">
    <article class="glass-card chart-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Analytics</p>
          <h3>Project velocity</h3>
        </div>
        <div class="segmented-control" aria-label="Chart period">
          <button class="active" type="button">Week</button>
          <button type="button">Month</button>
          <button type="button">Quarter</button>
        </div>
      </div>
      <div class="chart-frame">
        <canvas id="velocityChart" height="120" aria-label="Project velocity chart"></canvas>
      </div>
    </article>
  </div>

  <div class="col-xl-4" data-aos="fade-up" data-aos-delay="100">
    <article class="glass-card chart-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Portfolio</p>
          <h3>Project status</h3>
        </div>
        <button class="icon-btn subtle" type="button" aria-label="More project status options"><i class="bi bi-three-dots"></i></button>
      </div>
      <div class="donut-frame">
        <canvas id="statusChart" height="210" aria-label="Project status chart"></canvas>
      </div>
      <div class="status-legend">
        <span><i class="legend-dot active-dot"></i> Active</span>
        <span><i class="legend-dot review-dot"></i> Review</span>
        <span><i class="legend-dot hold-dot"></i> On hold</span>
      </div>
    </article>
  </div>
</section>

<section class="row g-3 g-xl-4 mt-1">
  <div class="col-xl-7" data-aos="fade-up">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Projects</p>
          <h3>Priority workstream</h3>
        </div>
        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i> View all</button>
      </div>

      <div class="project-list">
        <div class="project-row">
          <div class="project-name">
            <span class="project-code">AK</span>
            <div>
              <strong>Acme rebrand system</strong>
              <small>Identity, guidelines, launch assets</small>
            </div>
          </div>
          <div class="progress-block">
            <div class="d-flex justify-content-between"><span>Progress</span><strong>82%</strong></div>
            <div class="progress"><div class="progress-bar" style="width: 82%"></div></div>
          </div>
          <span class="status-pill active">Active</span>
        </div>

        <div class="project-row">
          <div class="project-name">
            <span class="project-code teal">NR</span>
            <div>
              <strong>Northstar website refresh</strong>
              <small>Wireframes, UI system, prototype</small>
            </div>
          </div>
          <div class="progress-block">
            <div class="d-flex justify-content-between"><span>Progress</span><strong>64%</strong></div>
            <div class="progress"><div class="progress-bar bg-success" style="width: 64%"></div></div>
          </div>
          <span class="status-pill review">Review</span>
        </div>

        <div class="project-row">
          <div class="project-name">
            <span class="project-code amber">MV</span>
            <div>
              <strong>Motion vault campaign</strong>
              <small>Social motion kit and ad variants</small>
            </div>
          </div>
          <div class="progress-block">
            <div class="d-flex justify-content-between"><span>Progress</span><strong>41%</strong></div>
            <div class="progress"><div class="progress-bar bg-warning" style="width: 41%"></div></div>
          </div>
          <span class="status-pill hold">On hold</span>
        </div>
      </div>
    </article>
  </div>

  <div class="col-xl-5" data-aos="fade-up" data-aos-delay="100">
    <article class="glass-card">
      <div class="card-heading">
        <div>
          <p class="eyebrow mb-1">Activity</p>
          <h3>Latest timeline</h3>
        </div>
        <button class="icon-btn subtle" type="button" aria-label="Refresh activity"><i class="bi bi-arrow-clockwise"></i></button>
      </div>

      <div class="activity-timeline">
        <div class="timeline-item">
          <span class="timeline-dot"></span>
          <div>
            <strong>Logo direction approved</strong>
            <p>Acme Studio approved concept B for production.</p>
            <small>12 minutes ago</small>
          </div>
        </div>
        <div class="timeline-item">
          <span class="timeline-dot green"></span>
          <div>
            <strong>New task assigned</strong>
            <p>Homepage mobile states assigned to Priya.</p>
            <small>38 minutes ago</small>
          </div>
        </div>
        <div class="timeline-item">
          <span class="timeline-dot amber"></span>
          <div>
            <strong>Invoice marked sent</strong>
            <p>Northstar phase two invoice sent to client portal.</p>
            <small>2 hours ago</small>
          </div>
        </div>
        <div class="timeline-item">
          <span class="timeline-dot rose"></span>
          <div>
            <strong>Deadline risk flagged</strong>
            <p>Campaign animation pass needs one more reviewer.</p>
            <small>Yesterday</small>
          </div>
        </div>
      </div>
    </article>
  </div>
</section>
