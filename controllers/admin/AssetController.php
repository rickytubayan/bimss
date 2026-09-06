<?php
namespace Controllers\Admin;

class AssetController extends \Controller {

    private const CATEGORIES = ['vehicle', 'equipment', 'facility', 'furniture', 'other'];
    private const CONDITIONS = ['excellent', 'good', 'fair', 'poor', 'non_functional'];
    private const STATUSES = ['available', 'in_use', 'under_repair', 'disposed'];

    public function index() {
        $db = $this->db;

        $category = $_GET['category'] ?? '';
        $category = in_array($category, self::CATEGORIES) ? $category : '';
        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::STATUSES) ? $status : '';

        $where = "a.deleted_at IS NULL";
        $params = [];
        if ($category !== '') {
            $where .= " AND a.category = ?";
            $params[] = $category;
        }
        if ($status !== '') {
            $where .= " AND a.status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("
            SELECT a.*,
                   (SELECT COUNT(*) FROM maintenance_logs m WHERE m.asset_id = a.id AND m.deleted_at IS NULL) AS maintenance_count,
                   (SELECT MAX(m.maintenance_date) FROM maintenance_logs m WHERE m.asset_id = a.id AND m.deleted_at IS NULL) AS last_maintenance
            FROM assets a
            WHERE {$where}
            ORDER BY a.name
        ");
        $stmt->execute($params);
        $assets = $stmt->fetchAll();

        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) c FROM assets WHERE deleted_at IS NULL")->fetch()['c'],
            'in_use' => (int)$db->query("SELECT COUNT(*) c FROM assets WHERE deleted_at IS NULL AND status = 'in_use'")->fetch()['c'],
            'under_repair' => (int)$db->query("SELECT COUNT(*) c FROM assets WHERE deleted_at IS NULL AND status = 'under_repair'")->fetch()['c'],
            'available' => (int)$db->query("SELECT COUNT(*) c FROM assets WHERE deleted_at IS NULL AND status = 'available'")->fetch()['c'],
            'value' => (float)$db->query("SELECT COALESCE(SUM(purchase_cost), 0) c FROM assets WHERE deleted_at IS NULL")->fetch()['c'],
        ];

        $this->viewAdmin('assets/index', [
            'title' => 'Assets',
            'assets' => $assets,
            'stats' => $stats,
            'category' => $category,
            'status' => $status,
        ]);
    }

    public function store() {
        $input = $this->getInput();
        $db = $this->db;

        $errors = [];
        if (empty(trim($input['name'] ?? ''))) $errors[] = 'Asset name is required.';
        if (!in_array($input['category'] ?? '', self::CATEGORIES)) $errors[] = 'Please select a valid category.';
        if (!in_array($input['current_condition'] ?? '', self::CONDITIONS)) $errors[] = 'Please select a valid condition.';
        if (!in_array($input['status'] ?? '', self::STATUSES)) $errors[] = 'Please select a valid status.';
        if (($input['purchase_cost'] ?? '') !== '' && (float)$input['purchase_cost'] < 0) $errors[] = 'Purchase cost cannot be negative.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('assets'));
        }

        $stmt = $db->prepare("
            INSERT INTO assets (name, category, description, purchase_date, purchase_cost, current_condition, next_maintenance, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['name']),
            $input['category'],
            trim($input['description'] ?? ''),
            trim($input['purchase_date'] ?? '') === '' ? null : trim($input['purchase_date']),
            ($input['purchase_cost'] ?? '') === '' ? 0 : (float)$input['purchase_cost'],
            $input['current_condition'],
            trim($input['next_maintenance'] ?? '') === '' ? null : trim($input['next_maintenance']),
            $input['status'],
        ]);

        flash('success', 'Asset registered.');
        redirect(admin_url('assets'));
    }

    public function show($id) {
        $db = $this->db;
        $id = (int)$id;

        $stmt = $db->prepare("SELECT * FROM assets WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $asset = $stmt->fetch();

        if (!$asset) {
            flash('error', 'Asset not found.');
            redirect(admin_url('assets'));
        }

        $logs = $db->prepare("
            SELECT * FROM maintenance_logs
            WHERE asset_id = ? AND deleted_at IS NULL
            ORDER BY maintenance_date DESC, id DESC
        ");
        $logs->execute([$id]);
        $maintenance = $logs->fetchAll();

        $stats = [
            'maintenance_count' => (int)$db->query("SELECT COUNT(*) c FROM maintenance_logs WHERE asset_id = {$id} AND deleted_at IS NULL")->fetch()['c'],
            'maintenance_cost' => (float)$db->query("SELECT COALESCE(SUM(cost), 0) c FROM maintenance_logs WHERE asset_id = {$id} AND deleted_at IS NULL")->fetch()['c'],
            'total_investment' => (float)$asset['purchase_cost'] + (float)$db->query("SELECT COALESCE(SUM(cost), 0) c FROM maintenance_logs WHERE asset_id = {$id} AND deleted_at IS NULL")->fetch()['c'],
        ];

        $this->viewAdmin('assets/show', [
            'title' => 'Asset - ' . $asset['name'],
            'asset' => $asset,
            'maintenance' => $maintenance,
            'stats' => $stats,
        ]);
    }

    public function addMaintenance($id) {
        $input = $this->getInput();
        $db = $this->db;
        $id = (int)$id;

        $stmt = $db->prepare("SELECT * FROM assets WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $asset = $stmt->fetch();

        if (!$asset) {
            flash('error', 'Asset not found.');
            redirect(admin_url('assets'));
        }

        $errors = [];
        if (empty(trim($input['maintenance_date'] ?? ''))) $errors[] = 'Maintenance date is required.';
        if (empty(trim($input['description'] ?? ''))) $errors[] = 'Description is required.';
        if (($input['cost'] ?? '') !== '' && (float)$input['cost'] < 0) $errors[] = 'Cost cannot be negative.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('assets/' . $id));
        }

        $db->beginTransaction();
        try {
            $ins = $db->prepare("
                INSERT INTO maintenance_logs (asset_id, maintenance_date, description, cost, performed_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $id,
                trim($input['maintenance_date']),
                trim($input['description']),
                ($input['cost'] ?? '') === '' ? 0 : (float)$input['cost'],
                trim($input['performed_by'] ?? ''),
            ]);

            $nextMaintenance = ($input['next_maintenance'] ?? '') !== '' ? trim($input['next_maintenance']) : null;

            $db->prepare("UPDATE assets SET current_condition = 'fair', status = 'under_repair', next_maintenance = COALESCE(?, next_maintenance) WHERE id = ?")->execute([$nextMaintenance, $id]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not save the maintenance log. Please try again.');
            redirect(admin_url('assets/' . $id));
        }

        flash('success', 'Maintenance log saved.');
        redirect(admin_url('assets/' . $id));
    }
}
