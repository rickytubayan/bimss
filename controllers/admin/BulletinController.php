<?php
namespace Controllers\Admin;

class BulletinController extends \Controller {

    public function index() {
        $db = $this->db;
        $search = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $where = "WHERE b.deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $where .= " AND (b.title LIKE ? OR b.content LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($category !== '') {
            $where .= " AND b.category = ?";
            $params[] = $category;
        }

        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM bulletins b {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $db->prepare("
            SELECT b.*, u.username AS posted_by_name
            FROM bulletins b
            LEFT JOIN users u ON u.id = b.posted_by
            {$where}
            ORDER BY b.is_pinned DESC, b.published_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $bulletins = $stmt->fetchAll();

        $this->viewAdmin('bulletins/index', [
            'title' => 'Bulletins',
            'bulletins' => $bulletins,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int)ceil($total / $perPage),
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function create() {
        $this->viewAdmin('bulletins/create', ['title' => 'Create Bulletin']);
    }

    public function store() {
        $input = $this->getInput();
        $errors = [];
        if (empty(trim($input['title'] ?? ''))) $errors[] = 'Title is required.';
        if (empty(trim($input['content'] ?? ''))) $errors[] = 'Content is required.';
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('bulletins/create'));
        }

        $db = $this->db;
        $stmt = $db->prepare("INSERT INTO bulletins (title, content, category, posted_by, is_pinned, published_at, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            trim($input['title']),
            trim($input['content']),
            $input['category'] ?? 'general',
            \Auth::id(),
            isset($input['is_pinned']) ? 1 : 0,
            $input['published_at'] ? date('Y-m-d H:i:s', strtotime($input['published_at'])) : date('Y-m-d H:i:s'),
            $input['expires_at'] ? date('Y-m-d H:i:s', strtotime($input['expires_at'])) : null,
        ]);

        flash('success', 'Bulletin published successfully.');
        redirect(admin_url('bulletins'));
    }

    public function delete($id) {
        $db = $this->db;
        $stmt = $db->prepare("UPDATE bulletins SET deleted_at = NOW(), updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        flash('success', 'Bulletin deleted.');
        redirect(admin_url('bulletins'));
    }
}
