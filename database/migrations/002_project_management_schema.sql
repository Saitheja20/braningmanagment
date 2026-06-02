CREATE TABLE IF NOT EXISTS clients (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(180) NOT NULL,
  contact_name VARCHAR(150) NULL,
  email VARCHAR(180) NULL,
  phone VARCHAR(50) NULL,
  status ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  KEY idx_clients_status (status),
  KEY idx_clients_company_name (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status ENUM('planned', 'active', 'on_hold', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
  priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  start_date DATE NULL,
  due_date DATE NULL,
  completed_at DATETIME NULL,
  budget DECIMAL(12,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  KEY idx_projects_client_id (client_id),
  KEY idx_projects_created_by (created_by),
  KEY idx_projects_status (status),
  KEY idx_projects_due_date (due_date),
  KEY idx_projects_priority (priority),
  CONSTRAINT fk_pm_projects_client
    FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_pm_projects_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_members (
  project_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  project_role ENUM('manager', 'designer', 'developer', 'copywriter', 'reviewer', 'client') NOT NULL DEFAULT 'designer',
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (project_id, user_id),
  KEY idx_project_members_user_id (user_id),
  CONSTRAINT fk_pm_project_members_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_pm_project_members_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  assigned_to BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  status ENUM('todo', 'in_progress', 'review', 'completed') NOT NULL DEFAULT 'todo',
  priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  start_date DATE NULL,
  due_date DATE NULL,
  completed_at DATETIME NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  KEY idx_tasks_project_id (project_id),
  KEY idx_tasks_assigned_to (assigned_to),
  KEY idx_tasks_created_by (created_by),
  KEY idx_tasks_status (status),
  KEY idx_tasks_due_date (due_date),
  KEY idx_tasks_project_status_order (project_id, status, sort_order),
  CONSTRAINT fk_pm_tasks_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_pm_tasks_assigned_to
    FOREIGN KEY (assigned_to) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_pm_tasks_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS milestones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  due_date DATE NOT NULL,
  status ENUM('pending', 'in_progress', 'completed', 'missed') NOT NULL DEFAULT 'pending',
  completed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_milestones_project_id (project_id),
  KEY idx_milestones_due_date (due_date),
  KEY idx_milestones_status (status),
  CONSTRAINT fk_pm_milestones_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clients (company_name, contact_name, email, status)
SELECT 'Acme Studio', 'Maya Collins', 'maya@acmestudio.test', 'active'
WHERE NOT EXISTS (SELECT 1 FROM clients WHERE company_name = 'Acme Studio');

INSERT INTO clients (company_name, contact_name, email, status)
SELECT 'Northstar Labs', 'Arjun Mehta', 'arjun@northstar.test', 'active'
WHERE NOT EXISTS (SELECT 1 FROM clients WHERE company_name = 'Northstar Labs');
