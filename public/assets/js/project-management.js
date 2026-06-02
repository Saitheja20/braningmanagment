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
});
