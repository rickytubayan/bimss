<?php
namespace Controllers\Admin;

class SettingsController extends \Controller {

    private const ROLES = ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair', 'resident'];
    private const STATUSES = ['active', 'inactive', 'suspended'];

    public function index() {
        $config = require CONFIG_PATH . '/app.php';
        $barangay = $config['barangay'] ?? [];

        $this->viewAdmin('settings/index', [
            'title' => 'Settings',
            'appName' => $config['name'] ?? 'BIMS',
            'barangay' => $barangay,
        ]);
    }

    public function update() {
        $input = $this->getInput();

        $path = CONFIG_PATH . '/app.php';
        $config = require $path;

        $config['barangay'] = [
            'name' => trim($input['barangay_name'] ?? ''),
            'code' => trim($input['barangay_code'] ?? ''),
            'municipality' => trim($input['barangay_municipality'] ?? ''),
            'province' => trim($input['barangay_province'] ?? ''),
            'region' => trim($input['barangay_region'] ?? ''),
        ];

        if (isset($input['app_name']) && trim($input['app_name']) !== '') {
            $config['name'] = trim($input['app_name']);
        }

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        if (@file_put_contents($path, $content) === false) {
            flash('error', 'Could not save settings. Check write permission for config/app.php.');
        } else {
            flash('success', 'Settings saved successfully.');
        }
        redirect(admin_url('settings'));
    }

    public function users() {
        $db = $this->db;

        $search = trim($_GET['q'] ?? '');
        $role = $_GET['role'] ?? '';

        $where = "WHERE u.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $where .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (in_array($role, self::ROLES, true)) {
            $where .= " AND u.role = ?";
            $params[] = $role;
        }

        $stmt = $db->prepare("
            SELECT u.* FROM users u
            {$where}
            ORDER BY u.created_at DESC
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $userModel = new \Models\User();
        $roleCounts = [];
        foreach (self::ROLES as $r) {
            $roleCounts[$r] = (int)$db->query("SELECT COUNT(*) c FROM users WHERE deleted_at IS NULL AND role = '{$r}'")->fetch()['c'];
        }

        $this->viewAdmin('settings/users', [
            'title' => 'User Management',
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'roles' => self::ROLES,
            'roleCounts' => $roleCounts,
            'userModel' => $userModel,
        ]);
    }

    public function createUser() {
        $this->viewAdmin('settings/users_create', [
            'title' => 'Create User',
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function storeUser() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['username'] ?? ''))) $errors[] = 'Username is required.';
        if (empty(trim($input['email'] ?? ''))) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($input['password'] ?? '')) {
            $errors[] = 'Password is required.';
        } elseif (strlen($input['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif (($input['password_confirm'] ?? '') !== $input['password']) {
            $errors[] = 'Passwords do not match.';
        }
        if (!in_array($input['role'] ?? '', self::ROLES, true)) $errors[] = 'Please select a valid role.';
        if (!in_array($input['status'] ?? '', self::STATUSES, true)) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('settings/users/create'));
        }

        $username = trim($input['username']);
        $email = trim($input['email']);

        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            set_old_input($input);
            flash('error', 'A user with that username or email already exists.');
            redirect(admin_url('settings/users/create'));
        }

        $userModel = new \Models\User();
        $userModel->createUser(
            $username,
            $email,
            $input['password'],
            $input['role'],
            trim($input['first_name'] ?? ''),
            trim($input['last_name'] ?? '')
        );

        if ($input['status'] !== 'active') {
            $db->prepare("UPDATE users SET status = ? WHERE username = ?")->execute([$input['status'], $username]);
        }

        flash('success', 'User account created.');
        redirect(admin_url('settings/users'));
    }
}