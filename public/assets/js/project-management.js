document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const postForm = async (url, data) => {
    const form = new FormData();
    form.append('_csrf', csrfToken);
    Object.entries(data).forEach(([key, value]) => form.append(key, value));

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: form
    });

    return response.json();
  };

  document.querySelectorAll('.js-project-status').forEach((select) => {
    const previous = { value: select.value };

    select.addEventListener('focus', () => {
      previous.value = select.value;
    });

    select.addEventListener('change', async () => {
      select.disabled = true;

      try {
        const result = await postForm('/projects/status', {
          id: select.dataset.projectId,
          status: select.value
        });

        if (!result.success) {
          select.value = previous.value;
        }
      } catch (_error) {
        select.value = previous.value;
      } finally {
        select.disabled = false;
      }
    });
  });

  document.querySelectorAll('.js-task-status').forEach((select) => {
    const previous = { value: select.value };

    select.addEventListener('focus', () => {
      previous.value = select.value;
    });

    select.addEventListener('change', async () => {
      select.disabled = true;
      try {
        const result = await postForm('/tasks/status', {
          id: select.dataset.taskId,
          status: select.value
        });
        if (!result.success) select.value = previous.value;
      } catch (_error) {
        select.value = previous.value;
      } finally {
        select.disabled = false;
      }
    });
  });

  document.querySelectorAll('.js-task-priority').forEach((select) => {
    const previous = { value: select.value };

    select.addEventListener('focus', () => {
      previous.value = select.value;
    });

    select.addEventListener('change', async () => {
      select.disabled = true;
      try {
        const result = await postForm('/tasks/priority', {
          id: select.dataset.taskId,
          priority: select.value
        });
        if (!result.success) select.value = previous.value;
      } catch (_error) {
        select.value = previous.value;
      } finally {
        select.disabled = false;
      }
    });
  });

  const projectForm = document.getElementById('projectForm');
  const projectModal = document.getElementById('projectModal');

  if (projectForm && projectModal) {
    projectModal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      const isEdit = trigger?.classList.contains('js-edit-project');
      const title = projectModal.querySelector('#projectModalLabel');

      projectForm.reset();
      projectForm.action = '/projects/store';
      projectForm.querySelector('#project_id').value = '';
      projectForm.querySelectorAll('#project_member_ids option').forEach((option) => {
        option.selected = false;
      });

      if (!isEdit) {
        title.textContent = 'Create project';
        return;
      }

      const project = JSON.parse(trigger.dataset.project || '{}');
      title.textContent = 'Edit project';
      projectForm.action = '/projects/update';

      const fields = {
        project_id: project.id,
        project_client_id: project.client_id,
        project_name: project.name,
        project_description: project.description,
        project_status: project.status,
        project_priority: project.priority,
        project_start_date: project.start_date,
        project_due_date: project.due_date,
        project_budget: project.budget
      };

      Object.entries(fields).forEach(([id, value]) => {
        const field = projectForm.querySelector(`#${id}`);
        if (field) {
          field.value = value || '';
        }
      });

      const memberIds = (project.member_ids || []).map(String);
      projectForm.querySelectorAll('#project_member_ids option').forEach((option) => {
        option.selected = memberIds.includes(option.value);
      });
    });
  }

  document.querySelectorAll('.js-confirm-delete').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm('Delete this project? Related tasks and milestones will remain hidden with it.')) {
        event.preventDefault();
      }
    });
  });

  let draggedTask = null;

  document.querySelectorAll('.task-card[draggable="true"]').forEach((card) => {
    card.addEventListener('dragstart', () => {
      draggedTask = card;
      card.classList.add('is-dragging');
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      draggedTask = null;
    });
  });

  document.querySelectorAll('.kanban-column').forEach((column) => {
    const dropzone = column.querySelector('.kanban-dropzone');

    column.addEventListener('dragover', (event) => {
      event.preventDefault();
      column.classList.add('is-over');
    });

    column.addEventListener('dragleave', () => {
      column.classList.remove('is-over');
    });

    column.addEventListener('drop', async (event) => {
      event.preventDefault();
      column.classList.remove('is-over');

      if (!draggedTask || !dropzone) {
        return;
      }

      const oldColumn = draggedTask.closest('.kanban-column');
      const oldDropzone = draggedTask.parentElement;
      dropzone.appendChild(draggedTask);

      try {
        const result = await postForm('/tasks/status', {
          id: draggedTask.dataset.taskId,
          status: column.dataset.status
        });

        if (!result.success && oldDropzone) {
          oldDropzone.appendChild(draggedTask);
        } else {
          updateKanbanCount(column);
          if (oldColumn) {
            updateKanbanCount(oldColumn);
          }
        }
      } catch (_error) {
        if (oldDropzone) {
          oldDropzone.appendChild(draggedTask);
        }
      }
    });
  });

  const updateKanbanCount = (column) => {
    const counter = column.querySelector('.kanban-heading span');
    if (!counter) {
      return;
    }

    counter.textContent = column.querySelectorAll('.task-card').length.toString();
  };

  document.querySelectorAll('.js-comment-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const textarea = form.querySelector('textarea[name="comment"]');
      const list = document.querySelector('.comment-list');

      if (!textarea?.value.trim()) {
        return;
      }

      const result = await postForm('/tasks/comment', {
        task_id: form.dataset.taskId,
        comment: textarea.value
      });

      if (result.success && list) {
        const item = document.createElement('article');
        item.className = 'comment-item';
        item.innerHTML = `<strong>${escapeHtml(result.comment.user_name || 'You')}</strong><p>${escapeHtml(result.comment.comment)}</p><small>${escapeHtml(result.comment.created_at)}</small>`;
        list.prepend(item);
        textarea.value = '';
      }
    });
  });

  document.querySelectorAll('.js-checklist-add').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const input = form.querySelector('input[name="title"]');
      const list = document.querySelector('.checklist-list');

      if (!input?.value.trim()) {
        return;
      }

      const result = await postForm('/tasks/checklist/store', {
        task_id: form.dataset.taskId,
        title: input.value
      });

      if (result.success && list) {
        const label = document.createElement('label');
        label.className = 'checklist-row';
        label.innerHTML = `<input class="form-check-input js-checklist-toggle" type="checkbox" data-item-id="${result.item.id}"><span>${escapeHtml(result.item.title)}</span>`;
        list.appendChild(label);
        bindChecklistToggle(label.querySelector('.js-checklist-toggle'));
        input.value = '';
      }
    });
  });

  const bindChecklistToggle = (checkbox) => {
    if (!checkbox) return;

    checkbox.addEventListener('change', async () => {
      const result = await postForm('/tasks/checklist/toggle', {
        id: checkbox.dataset.itemId,
        completed: checkbox.checked ? '1' : '0'
      });

      if (!result.success) {
        checkbox.checked = !checkbox.checked;
        return;
      }

      updateDetailProgress(result.progress);
    });
  };

  document.querySelectorAll('.js-checklist-toggle').forEach(bindChecklistToggle);

  document.querySelectorAll('.js-attachment-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const input = form.querySelector('input[type="file"]');
      const file = input?.files?.[0];
      const list = document.querySelector('.attachment-list');

      if (!file) {
        return;
      }

      const body = new FormData();
      body.append('_csrf', csrfToken);
      body.append('task_id', form.dataset.taskId);
      body.append('attachment', file);

      const response = await fetch('/tasks/attachments/store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body
      });
      const result = await response.json();

      if (result.success && list) {
        const item = document.createElement('div');
        item.className = 'attachment-item';
        item.innerHTML = `<i class="bi bi-paperclip"></i><div><strong>${escapeHtml(result.attachment.original_name)}</strong><small>${Math.round(result.attachment.size_bytes / 102.4) / 10} KB</small></div>`;
        list.prepend(item);
        input.value = '';
      }
    });
  });

  const updateDetailProgress = (progress) => {
    const value = document.querySelector('.task-progress-value');
    const bar = document.querySelector('.js-detail-progress');
    if (value) value.textContent = `${progress}%`;
    if (bar) bar.style.width = `${progress}%`;
  };

  const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const realtimeHub = document.querySelector('[data-realtime-hub]');
  if (realtimeHub) {
    let lastMessageId = 0;
    let lastActivityId = 0;

    const renderSnapshot = (snapshot) => {
      const count = document.querySelector('.js-live-count');
      const notifications = document.querySelector('.js-live-notifications');
      const messages = document.querySelector('.js-live-messages');
      const activities = document.querySelector('.js-live-activities');
      const tasks = document.querySelector('.js-live-tasks');

      if (count) count.textContent = String(snapshot.unread_count || 0);
      if (notifications) notifications.innerHTML = (snapshot.notifications || []).map((item) => `<button class="notification-card js-read-notification" data-id="${item.id}"><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.created_at)}</small><p>${escapeHtml(item.body || '')}</p></button>`).join('');
      if (messages) messages.innerHTML = (snapshot.messages || []).map((item) => `<article class="comment-item"><strong>${escapeHtml(item.sender_name || 'Team')}</strong><p>${escapeHtml(item.body)}</p><small>${escapeHtml(item.created_at)}</small></article>`).join('');
      if (activities) activities.innerHTML = (snapshot.activities || []).map((item) => `<div class="timeline-item"><span class="timeline-dot green"></span><div><strong>${escapeHtml(item.action)}</strong><p>${escapeHtml(item.description || item.entity_type)}</p><small>${escapeHtml(item.created_at)}</small></div></div>`).join('');
      if (tasks) tasks.innerHTML = (snapshot.tasks || []).map((item) => `<article class="task-row-card compact-row"><div><strong>${escapeHtml(item.title)}</strong><p>${escapeHtml(item.updated_at)}</p></div><span class="priority-pill ${escapeHtml(item.priority)}">${escapeHtml(item.priority)}</span><span class="status-pill ${escapeHtml(item.status)}">${escapeHtml(item.status.replace('_', ' '))}</span><div class="progress"><div class="progress-bar" style="width:${Number(item.progress_percent || 0)}%"></div></div></article>`).join('');

      if ((snapshot.messages || [])[0]) lastMessageId = Math.max(lastMessageId, Number(snapshot.messages[0].id || 0));
      if ((snapshot.activities || [])[0]) lastActivityId = Math.max(lastActivityId, Number(snapshot.activities[0].id || 0));
    };

    const refresh = async () => {
      const response = await fetch(`/api/realtime/snapshot?after_id=${Math.max(lastMessageId, lastActivityId)}`);
      renderSnapshot(await response.json());
    };

    document.querySelector('.js-chat-form')?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      const body = form.querySelector('[name="body"]');
      await postForm('/api/realtime/message', {
        receiver_id: form.querySelector('[name="receiver_id"]').value,
        body: body.value
      });
      body.value = '';
      refresh();
    });

    document.addEventListener('click', async (event) => {
      const button = event.target.closest('.js-read-notification');
      if (!button) return;
      await postForm('/api/notifications/read', { id: button.dataset.id });
      refresh();
    });

    refresh();
    window.setInterval(refresh, 10000);
  }
});
