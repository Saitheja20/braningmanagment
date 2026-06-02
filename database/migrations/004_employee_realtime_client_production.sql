CREATE TABLE IF NOT EXISTS employees (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  employee_code VARCHAR(50) NOT NULL,
  designation VARCHAR(120) NULL,
  department VARCHAR(120) NULL,
  joining_date DATE NULL,
  employment_type ENUM('full_time', 'part_time', 'contract', 'intern') NOT NULL DEFAULT 'full_time',
  hourly_rate DECIMAL(10,2) NULL,
  status ENUM('active', 'inactive', 'terminated') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_employees_user_id (user_id),
  UNIQUE KEY uq_employees_employee_code (employee_code),
  KEY idx_employees_department (department),
  KEY idx_employees_status (status),
  CONSTRAINT fk_phase8_employees_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  check_in DATETIME NULL,
  check_out DATETIME NULL,
  status ENUM('present', 'absent', 'late', 'half_day', 'leave') NOT NULL DEFAULT 'present',
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_attendance_employee_date (employee_id, attendance_date),
  KEY idx_attendance_date (attendance_date),
  KEY idx_attendance_status (status),
  CONSTRAINT fk_phase8_attendance_employee
    FOREIGN KEY (employee_id) REFERENCES employees(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  task_id BIGINT UNSIGNED NULL,
  log_date DATE NOT NULL,
  hours DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_work_logs_employee_date (employee_id, log_date),
  KEY idx_work_logs_task_id (task_id),
  CONSTRAINT fk_phase8_work_logs_employee
    FOREIGN KEY (employee_id) REFERENCES employees(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_phase8_work_logs_task
    FOREIGN KEY (task_id) REFERENCES tasks(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_threads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NULL,
  client_id BIGINT UNSIGNED NULL,
  subject VARCHAR(200) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_message_threads_project_id (project_id),
  KEY idx_message_threads_client_id (client_id),
  CONSTRAINT fk_phase9_message_threads_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase9_message_threads_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase9_message_threads_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thread_id BIGINT UNSIGNED NOT NULL,
  sender_id BIGINT UNSIGNED NULL,
  receiver_id BIGINT UNSIGNED NULL,
  body TEXT NOT NULL,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_messages_thread_id (thread_id),
  KEY idx_messages_sender_id (sender_id),
  KEY idx_messages_receiver_id (receiver_id),
  KEY idx_messages_created_at (created_at),
  CONSTRAINT fk_phase9_messages_thread
    FOREIGN KEY (thread_id) REFERENCES message_threads(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_phase9_messages_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase9_messages_receiver
    FOREIGN KEY (receiver_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  client_id BIGINT UNSIGNED NULL,
  project_id BIGINT UNSIGNED NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  description VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_activities_user_id (user_id),
  KEY idx_activities_client_id (client_id),
  KEY idx_activities_project_id (project_id),
  KEY idx_activities_entity (entity_type, entity_id),
  KEY idx_activities_created_at (created_at),
  CONSTRAINT fk_phase9_activities_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase9_activities_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase9_activities_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_approvals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NULL,
  submitted_by BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  file_path VARCHAR(500) NULL,
  preview_mime VARCHAR(120) NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  feedback TEXT NULL,
  decided_by BIGINT UNSIGNED NULL,
  decided_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_design_approvals_project_id (project_id),
  KEY idx_design_approvals_client_id (client_id),
  KEY idx_design_approvals_status (status),
  CONSTRAINT fk_phase10_design_approvals_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_phase10_design_approvals_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase10_design_approvals_submitted_by
    FOREIGN KEY (submitted_by) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_phase10_design_approvals_decided_by
    FOREIGN KEY (decided_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  invoice_number VARCHAR(80) NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft',
  pdf_path VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoices_invoice_number (invoice_number),
  KEY idx_invoices_client_id (client_id),
  KEY idx_invoices_project_id (project_id),
  KEY idx_invoices_status (status),
  CONSTRAINT fk_phase10_invoices_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_phase10_invoices_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (name, slug, module) VALUES
('Manage Employees', 'employees.manage', 'employees'),
('View Realtime', 'realtime.view', 'realtime'),
('Use Client Portal', 'client_portal.use', 'client_portal')
ON DUPLICATE KEY UPDATE name = VALUES(name), module = VALUES(module);

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('employees.manage', 'realtime.view')
WHERE roles.slug IN ('super-admin', 'agency-admin', 'project-manager')
ON DUPLICATE KEY UPDATE permission_id = permission_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('client_portal.use', 'client_portal.view')
WHERE roles.slug = 'client'
ON DUPLICATE KEY UPDATE permission_id = permission_id;
