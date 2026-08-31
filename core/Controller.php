<?php
class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function view($viewPath, $data = []) {
        extract($data);
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$viewPath}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutPath = $data['layout'] ?? null;
        if ($layoutPath) {
            $layoutFile = VIEWS_PATH . '/' . str_replace('.', '/', $layoutPath) . '.php';
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function viewAdmin($viewPath, $data = []) {
        $data['layout'] = 'admin/layouts/main';
        $this->view('admin/' . ltrim($viewPath, '/'), $data);
    }

    protected function viewPublic($viewPath, $data = []) {
        $data['layout'] = 'public/layouts/main';
        $this->view('public/' . ltrim($viewPath, '/'), $data);
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonError($message, $statusCode = 400) {
        $this->json(['success' => false, 'message' => $message], $statusCode);
    }

    protected function jsonSuccess($data = null, $message = 'Success') {
        $this->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    protected function getInput() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }

    protected function validate(array $data, array $rules) {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }

    protected function requireAuth() {
        if (!Auth::check()) {
            $this->jsonError('Unauthorized', 401);
        }
    }

    protected function getUser() {
        return Auth::user();
    }

    protected function paginate($query, $perPage = 20) {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $countQuery = preg_replace('/SELECT .+? FROM/i', 'SELECT COUNT(*) as total FROM', $query);
        $countQuery = preg_replace('/ORDER BY .+$/i', '', $countQuery);
        $countQuery = preg_replace('/LIMIT .+$/i', '', $countQuery);

        $stmt = $this->db->prepare($countQuery);
        $stmt->execute();
        $total = $stmt->fetch()['total'] ?? 0;

        $stmt = $this->db->prepare($query . " LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();
        $results = $stmt->fetchAll();

        return [
            'data' => $results,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ];
    }
}
