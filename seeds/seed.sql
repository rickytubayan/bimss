-- =====================================================
-- BIMS Seed Data
-- Roles, Permissions, Default Users, and Sample Data
-- =====================================================

-- =====================================================
-- 1. ROLES
-- =====================================================
INSERT IGNORE INTO roles (id, name, description, is_system, created_at, updated_at) VALUES
(1, 'captain', 'Punong Barangay - Full access, view-only on financial approvals', 1, NOW(), NOW()),
(2, 'kagawad', 'Barangay Kagawad - Committee access', 1, NOW(), NOW()),
(3, 'secretary', 'Barangay Secretary - Documents, certificates, records', 1, NOW(), NOW()),
(4, 'treasurer', 'Barangay Treasurer - Finance, budget, tax', 1, NOW(), NOW()),
(5, 'bhw', 'Barangay Health Worker - Health module', 1, NOW(), NOW()),
(6, 'tanod', 'Barangay Tanod - Peace and order', 1, NOW(), NOW()),
(7, 'census', 'Census Officer - Demographics, households', 1, NOW(), NOW()),
(8, 'sk_chair', 'Sangguniang Kabataan Chairman - Youth programs', 1, NOW(), NOW()),
(9, 'resident', 'Registered citizen resident', 1, NOW(), NOW());

-- =====================================================
-- 2. PERMISSIONS
-- =====================================================
INSERT IGNORE INTO permissions (module, action, description, created_at, updated_at) VALUES
-- Residents module
('residents', 'view', 'View resident records', NOW(), NOW()),
('residents', 'create', 'Create resident records', NOW(), NOW()),
('residents', 'edit', 'Edit resident records', NOW(), NOW()),
('residents', 'delete', 'Delete resident records', NOW(), NOW()),
('residents', 'export', 'Export resident data', NOW(), NOW()),
-- Households module
('households', 'view', 'View households', NOW(), NOW()),
('households', 'create', 'Create households', NOW(), NOW()),
('households', 'edit', 'Edit households', NOW(), NOW()),
('households', 'delete', 'Delete households', NOW(), NOW()),
-- Certificates module
('certificates', 'view', 'View certificates', NOW(), NOW()),
('certificates', 'create', 'Create certificates', NOW(), NOW()),
('certificates', 'edit', 'Edit certificates', NOW(), NOW()),
('certificates', 'approve', 'Approve certificates', NOW(), NOW()),
-- Finance module
('finance', 'view', 'View finance records', NOW(), NOW()),
('finance', 'create', 'Create finance records', NOW(), NOW()),
('finance', 'edit', 'Edit finance records', NOW(), NOW()),
('finance', 'approve', 'Approve financial transactions', NOW(), NOW()),
('finance', 'export', 'Export financial data', NOW(), NOW()),
-- Budget module
('budget', 'view', 'View budget', NOW(), NOW()),
('budget', 'create', 'Create budget', NOW(), NOW()),
('budget', 'edit', 'Edit budget', NOW(), NOW()),
('budget', 'approve', 'Approve budget', NOW(), NOW()),
-- Tax module
('tax', 'view', 'View tax ledger', NOW(), NOW()),
('tax', 'create', 'Create tax records', NOW(), NOW()),
('tax', 'edit', 'Edit tax records', NOW(), NOW()),
-- Health module
('health', 'view', 'View health records', NOW(), NOW()),
('health', 'create', 'Create health records', NOW(), NOW()),
('health', 'edit', 'Edit health records', NOW(), NOW()),
-- Seniors/PWD module
('seniors', 'view', 'View senior/PWD records', NOW(), NOW()),
('seniors', 'create', 'Create senior/PWD records', NOW(), NOW()),
('seniors', 'edit', 'Edit senior/PWD records', NOW(), NOW()),
-- Peace & Order
('peace_order', 'view', 'View peace and order records', NOW(), NOW()),
('peace_order', 'create', 'Create blotter records', NOW(), NOW()),
('peace_order', 'edit', 'Edit blotter records', NOW(), NOW()),
-- Lupon
('lupon', 'view', 'View Lupon cases', NOW(), NOW()),
('lupon', 'create', 'Create KP cases', NOW(), NOW()),
('lupon', 'edit', 'Edit KP cases', NOW(), NOW()),
('lupon', 'approve', 'Approve settlements/CFA', NOW(), NOW()),
-- DRRM
('drrm', 'view', 'View DRRM records', NOW(), NOW()),
('drrm', 'create', 'Create DRRM records', NOW(), NOW()),
('drrm', 'edit', 'Edit DRRM records', NOW(), NOW()),
('drrm', 'approve', 'Approve DRRM actions', NOW(), NOW()),
-- Assets
('assets', 'view', 'View assets', NOW(), NOW()),
('assets', 'create', 'Create assets', NOW(), NOW()),
('assets', 'edit', 'Edit assets', NOW(), NOW()),
-- Compliance
('compliance', 'view', 'View compliance records', NOW(), NOW()),
('compliance', 'create', 'Create compliance docs', NOW(), NOW()),
('compliance', 'edit', 'Edit compliance records', NOW(), NOW()),
-- Reports
('reports', 'view', 'View reports', NOW(), NOW()),
('reports', 'create', 'Generate reports', NOW(), NOW()),
('reports', 'export', 'Export reports', NOW(), NOW()),
-- Notifications
('notifications', 'view', 'View notifications', NOW(), NOW()),
('notifications', 'create', 'Send notifications', NOW(), NOW()),
('notifications', 'send_emergency', 'Send emergency alerts', NOW(), NOW()),
-- Bulletins
('bulletins', 'view', 'View bulletins', NOW(), NOW()),
('bulletins', 'create', 'Create bulletins', NOW(), NOW()),
('bulletins', 'edit', 'Edit bulletins', NOW(), NOW()),
('bulletins', 'delete', 'Delete bulletins', NOW(), NOW()),
-- Settings
('settings', 'view', 'View settings', NOW(), NOW()),
('settings', 'edit', 'Edit settings', NOW(), NOW());

-- =====================================================
-- 3. ROLE PERMISSIONS
-- =====================================================
-- Captain: all permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r CROSS JOIN permissions p
WHERE r.name = 'captain';

-- Secretary: residents, households, certificates, compliance, reports, notifications, bulletins
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'secretary' AND p.module IN ('residents','households','certificates','compliance','reports','notifications','bulletins','settings')
AND p.action IN ('view','create','edit','export');

-- Treasurer: finance, budget, tax, reports
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'treasurer' AND p.module IN ('finance','budget','tax','reports')
AND p.action IN ('view','create','edit','export');

-- BHW: health, seniors
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'bhw' AND p.module IN ('health','seniors')
AND p.action IN ('view','create','edit');

-- Tanod: peace_order, lupon (view), drrm (view)
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'tanod' AND (
    (p.module = 'peace_order' AND p.action IN ('view','create','edit'))
    OR (p.module = 'lupon' AND p.action = 'view')
    OR (p.module = 'drrm' AND p.action IN ('view','create'))
);

-- Census: residents, households
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'census' AND p.module IN ('residents','households')
AND p.action IN ('view','create','edit','export');

-- SK Chair: budget (view), reports
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'sk_chair' AND p.module IN ('budget','reports') AND p.action = 'view';

-- Kagawad: view majority
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)
SELECT r.id, p.id, NOW(), NOW()
FROM roles r JOIN permissions p ON 1=1
WHERE r.name = 'kagawad' AND p.action = 'view';

-- =====================================================
-- 4. DEFAULT USERS (Password: Admin@12345 (default) - bcrypt hash)
-- =====================================================
INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, status, email_verified_at, created_at, updated_at) VALUES
('captain', 'captain@bims.local', '$2y$10$3eSmRjKExTbqqK14ue2/refx5BkP/jQtXfboygid17X7OKpbHY23K', 'Punong', 'Barangay', 'captain', 'active', NOW(), NOW(), NOW()),
('secretary', 'secretary@bims.local', '$2y$10$3eSmRjKExTbqqK14ue2/refx5BkP/jQtXfboygid17X7OKpbHY23K', 'Barangay', 'Secretary', 'secretary', 'active', NOW(), NOW(), NOW()),
('treasurer', 'treasurer@bims.local', '$2y$10$3eSmRjKExTbqqK14ue2/refx5BkP/jQtXfboygid17X7OKpbHY23K', 'Barangay', 'Treasurer', 'treasurer', 'active', NOW(), NOW(), NOW()),
('admin', 'admin@bims.local', '$2y$10$3eSmRjKExTbqqK14ue2/refx5BkP/jQtXfboygid17X7OKpbHY23K', 'System', 'Administrator', 'captain', 'active', NOW(), NOW(), NOW());

-- =====================================================
-- 5. DOCUMENT TYPES
-- =====================================================
INSERT IGNORE INTO document_types (name, category, fee, requirements_json, is_active, description, created_at, updated_at) VALUES
('Barangay Clearance', 'clearance', 50.00, '["Valid ID", "Proof of Residency"]', 1, 'Standard barangay clearance certificate', NOW(), NOW()),
('Certificate of Indigency', 'certificate', 0.00, '["Valid ID"]', 1, 'For residents classified as indigent', NOW(), NOW()),
('Certificate of Residency', 'certificate', 30.00, '["Valid ID", "Proof of Residence"]', 1, 'Proof of residency within the barangay', NOW(), NOW()),
('Certificate of Good Moral Character', 'certificate', 50.00, '["Valid ID"]', 1, 'Character reference certificate', NOW(), NOW()),
('Business Clearance', 'clearance', 200.00, '["Mayors Permit", "BIR Registration", "Sanitary Permit"]', 1, 'Business operation clearance', NOW(), NOW());

-- =====================================================
-- 6. DEFAULT APPOINTMENT SLOTS ARE HANDLED VIA UI
-- =====================================================
