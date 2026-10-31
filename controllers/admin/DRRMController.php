<?php
namespace Controllers\Admin;

class DRRMController extends \Controller {

    private const EVENT_TYPES = ['flood', 'typhoon', 'earthquake', 'fire', 'landslide', 'volcanic', 'drought', 'other'];
    private const SEVERITIES = ['minor', 'moderate', 'severe', 'catastrophic'];
    private const RELIEF_CATEGORIES = ['food', 'water', 'medicine', 'blanket', 'hygiene', 'other'];
    private const RELIEF_STATUSES = ['available', 'depleted', 'expired'];

    public function index() {
        $db = $this->db;

        $stats = [
            'events' => (int)$db->query("SELECT COUNT(*) c FROM disaster_events WHERE deleted_at IS NULL")->fetch()['c'],
            'severe' => (int)$db->query("SELECT COUNT(*) c FROM disaster_events WHERE deleted_at IS NULL AND severity IN ('severe','catastrophic')")->fetch()['c'],
            'rdana' => (int)$db->query("SELECT COUNT(*) c FROM rdana_reports WHERE deleted_at IS NULL")->fetch()['c'],
            'relief' => (int)$db->query("SELECT COALESCE(SUM(quantity),0) c FROM relief_inventory WHERE deleted_at IS NULL AND status = 'available'")->fetch()['c'],
        ];

        $recentEvents = $db->query("
            SELECT * FROM disaster_events
            WHERE deleted_at IS NULL
            ORDER BY datetime_start DESC, id DESC LIMIT 5
        ")->fetchAll();

        $this->viewAdmin('drrm/index', [
            'title' => 'DRRM',
            'stats' => $stats,
            'recentEvents' => $recentEvents,
        ]);
    }

    public function events() {
        $db = $this->db;

        $type = $_GET['type'] ?? '';
        $type = in_array($type, self::EVENT_TYPES) ? $type : '';

        $where = "e.deleted_at IS NULL";
        $params = [];
        if ($type !== '') {
            $where .= " AND e.type = ?";
            $params[] = $type;
        }

        $stmt = $db->prepare("
            SELECT e.*, (SELECT COUNT(*) FROM rdana_reports r WHERE r.disaster_event_id = e.id AND r.deleted_at IS NULL) AS rdana_count
            FROM disaster_events e
            WHERE {$where}
            ORDER BY e.datetime_start DESC, e.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $this->viewAdmin('drrm/events', [
            'title' => 'DRRM - Disaster Events',
            'rows' => $rows,
            'type' => $type,
        ]);
    }

    public function storeEvent() {
        $input = $this->getInput();

        $errors = [];
        if (empty(trim($input['name'] ?? ''))) $errors[] = 'Event name is required.';
        if (!in_array($input['type'] ?? '', self::EVENT_TYPES)) $errors[] = 'Please select a valid event type.';
        if (empty(trim($input['datetime_start'] ?? ''))) $errors[] = 'Start date/time is required.';
        if (!in_array($input['severity'] ?? '', self::SEVERITIES)) $errors[] = 'Please select a valid severity.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/events'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO disaster_events (name, type, datetime_start, datetime_end, severity, gps_latitude, gps_longitude, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['name']),
            $input['type'],
            $input['datetime_start'],
            ($input['datetime_end'] ?? '') === '' ? null : $input['datetime_end'],
            $input['severity'],
            ($input['gps_latitude'] ?? '') === '' ? null : (float)$input['gps_latitude'],
            ($input['gps_longitude'] ?? '') === '' ? null : (float)$input['gps_longitude'],
            ($input['description'] ?? '') === '' ? null : trim($input['description']),
        ]);

        flash('success', 'Disaster event recorded.');
        redirect(admin_url('drrm/events'));
    }

    public function showEvent($id) {
        $db = $this->db;

        $stmt = $db->prepare("SELECT * FROM disaster_events WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if (!$row) {
            flash('error', 'Disaster event not found.');
            redirect(admin_url('drrm/events'));
        }

        $rdanas = $db->prepare("SELECT * FROM rdana_reports WHERE disaster_event_id = ? AND deleted_at IS NULL ORDER BY assessment_date, id");
        $rdanas->execute([(int)$id]);

        $this->viewAdmin('drrm/showEvent', [
            'title' => $row['name'],
            'row' => $row,
            'rdanas' => $rdanas->fetchAll(),
        ]);
    }

    public function rdana($event_id) {
        $db = $this->db;

        $stmt = $db->prepare("SELECT * FROM disaster_events WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([(int)$event_id]);
        $event = $stmt->fetch();

        if (!$event) {
            flash('error', 'Disaster event not found.');
            redirect(admin_url('drrm/events'));
        }

        $listStmt = $db->prepare("
            SELECT r.*, e.name AS event_name
            FROM rdana_reports r
            JOIN disaster_events e ON e.id = r.disaster_event_id
            WHERE r.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY r.assessment_date DESC, r.id DESC
        ");
        $listStmt->execute();
        $reports = $listStmt->fetchAll();

        $this->viewAdmin('drrm/rdana', [
            'title' => 'DRRM - RDANA',
            'event' => $event,
            'reports' => $reports,
        ]);
    }

    public function storeRDANA() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['disaster_event_id'] ?? '')) $errors[] = 'Disaster event is required.';
        if (empty(trim($input['assessment_date'] ?? ''))) $errors[] = 'Assessment date is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/rdana/' . (int)($input['disaster_event_id'] ?? 0)));
        }

        $db = $this->db;
        $db->beginTransaction();
        try {
            $rep = $db->prepare("
                INSERT INTO rdana_reports (disaster_event_id, assessment_date, assessed_by, local_authority_name, local_authority_position, summary_narrative, status)
                VALUES (?, ?, ?, ?, ?, ?, 'draft')
            ");
            $rep->execute([
                (int)$input['disaster_event_id'],
                $input['assessment_date'],
                ($input['assessed_by'] ?? '') === '' ? null : trim($input['assessed_by']),
                ($input['local_authority_name'] ?? '') === '' ? null : trim($input['local_authority_name']),
                ($input['local_authority_position'] ?? '') === '' ? null : trim($input['local_authority_position']),
                ($input['summary_narrative'] ?? '') === '' ? null : trim($input['summary_narrative']),
            ]);
            $rdanaId = (int)$db->lastInsertId();

            $shelter = $db->prepare("
                INSERT INTO rdana_shelter (rdana_id, destroyed_count, damaged_count, percentage_destroyed, percentage_damaged, immediate_needs)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $shelter->execute([
                $rdanaId,
                (int)($input['destroyed_count'] ?? 0),
                (int)($input['damaged_count'] ?? 0),
                ($input['percentage_destroyed'] ?? '') === '' ? null : trim($input['percentage_destroyed']),
                ($input['percentage_damaged'] ?? '') === '' ? null : trim($input['percentage_damaged']),
                ($input['immediate_needs'] ?? '') === '' ? null : json_encode(preg_split('/\r?\n|\r/', trim($input['immediate_needs']))),
            ]);

            $effects = $db->prepare("
                INSERT INTO rdana_effects (rdana_id, category, male_count, female_count, children_count, senior_count, pwd_count)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($categories = ['affected', 'displaced', 'dead', 'injured', 'missing'] as $cat) {
                if (($input['effects_' . $cat . '_male'] ?? '') === '' && ($input['effects_' . $cat . '_female'] ?? '') === '') {
                    continue;
                }
                $effects->execute([
                    $rdanaId,
                    $cat,
                    (int)($input['effects_' . $cat . '_male'] ?? 0),
                    (int)($input['effects_' . $cat . '_female'] ?? 0),
                    (int)($input['effects_' . $cat . '_children'] ?? 0),
                    (int)($input['effects_' . $cat . '_senior'] ?? 0),
                    (int)($input['effects_' . $cat . '_pwd'] ?? 0),
                ]);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not save the RDANA report. Please try again.');
            redirect(admin_url('drrm/rdana/' . (int)$input['disaster_event_id']));
        }

        flash('success', 'RDANA report saved.');
        redirect(admin_url('drrm/rdana/' . (int)$input['disaster_event_id']));
    }

    public function hazardMap() {
        $db = $this->db;

        $zones = $db->query("
            SELECT z.*, p.name AS purok_name
            FROM hazard_zones z
            LEFT JOIN puroks p ON p.id = z.purok_id
            WHERE z.deleted_at IS NULL
            ORDER BY z.risk_level, p.name, z.street_name
        ")->fetchAll();

        $this->viewAdmin('drrm/hazardMap', [
            'title' => 'DRRM - Hazard Map',
            'zones' => $zones,
        ]);
    }

    public function relief() {
        $db = $this->db;

        $items = $db->query("
            SELECT * FROM relief_inventory
            WHERE deleted_at IS NULL
            ORDER BY item_name
        ")->fetchAll();

        $distributions = $db->query("
            SELECT d.*, i.item_name, i.unit, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, e.name AS event_name
            FROM relief_distributions d
            JOIN relief_inventory i ON i.id = d.inventory_id
            JOIN residents r ON r.id = d.resident_id
            JOIN disaster_events e ON e.id = d.disaster_event_id
            WHERE d.deleted_at IS NULL
            ORDER BY d.distributed_at DESC, d.id DESC
        ")->fetchAll();

        $stats = [
            'items' => count($items),
            'available_units' => (int)$db->query("SELECT COALESCE(SUM(quantity),0) c FROM relief_inventory WHERE deleted_at IS NULL AND status = 'available'")->fetch()['c'],
            'distributions' => (int)$db->query("SELECT COALESCE(SUM(quantity),0) c FROM relief_distributions WHERE deleted_at IS NULL")->fetch()['c'],
            'recipients' => (int)$db->query("SELECT COUNT(DISTINCT resident_id) c FROM relief_distributions WHERE deleted_at IS NULL")->fetch()['c'],
        ];

        $events = $db->query("SELECT id, name FROM disaster_events WHERE deleted_at IS NULL ORDER BY datetime_start DESC")->fetchAll();
        $residents = $db->query("
            SELECT id, CONCAT(last_name, ', ', first_name) AS label
            FROM residents WHERE deleted_at IS NULL AND status = 'active' ORDER BY last_name, first_name
        ")->fetchAll();
        $currentUser = $this->getUser();

        $this->viewAdmin('drrm/relief', [
            'title' => 'DRRM - Relief',
            'items' => $items,
            'distributions' => $distributions,
            'stats' => $stats,
            'events' => $events,
            'residents' => $residents,
            'categories' => self::RELIEF_CATEGORIES,
            'currentUserName' => trim((string)($currentUser['first_name'] ?? '')),
        ]);
    }

    public function storeInventory() {
        $input = $this->getInput();

        $errors = $this->validateInventory($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/relief'));
        }

        $data = $this->inventoryData($input);
        $data['status'] = $this->inventoryStatus($data['quantity'], $data['expiry_date']);

        $stmt = $this->db->prepare("
            INSERT INTO relief_inventory (item_name, category, quantity, unit, expiry_date, storage_location, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['item_name'],
            $data['category'],
            $data['quantity'],
            $data['unit'],
            $data['expiry_date'],
            $data['storage_location'],
            $data['status'],
        ]);

        flash('success', 'Inventory item added.');
        redirect(admin_url('drrm/relief'));
    }

    public function updateInventory($id) {
        $item = $this->findInventoryItem($id);
        if (!$item) {
            flash('error', 'Inventory item not found.');
            redirect(admin_url('drrm/relief'));
        }

        $input = $this->getInput();
        $errors = $this->validateInventory($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/relief'));
        }

        $data = $this->inventoryData($input);
        $data['status'] = $this->inventoryStatus($data['quantity'], $data['expiry_date']);

        $stmt = $this->db->prepare("
            UPDATE relief_inventory
            SET item_name = ?, category = ?, quantity = ?, unit = ?, expiry_date = ?, storage_location = ?, status = ?, updated_at = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([
            $data['item_name'],
            $data['category'],
            $data['quantity'],
            $data['unit'],
            $data['expiry_date'],
            $data['storage_location'],
            $data['status'],
            date('Y-m-d H:i:s'),
            (int)$item['id'],
        ]);

        flash('success', 'Inventory item updated.');
        redirect(admin_url('drrm/relief'));
    }

    public function deleteInventory($id) {
        $item = $this->findInventoryItem($id);
        if (!$item) {
            flash('error', 'Inventory item not found.');
            redirect(admin_url('drrm/relief'));
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) c FROM relief_distributions
            WHERE inventory_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([(int)$item['id']]);

        if ((int)$stmt->fetch()['c'] > 0) {
            flash('error', 'This item has existing distributions. Delete those records first.');
            redirect(admin_url('drrm/relief'));
        }

        $stmt = $this->db->prepare("
            UPDATE relief_inventory
            SET deleted_at = ?, updated_at = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), (int)$item['id']]);

        flash('success', 'Inventory item deleted.');
        redirect(admin_url('drrm/relief'));
    }

    public function storeDistribution() {
        $input = $this->getInput();

        $errors = $this->validateDistribution($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/relief'));
        }

        $data = $this->distributionData($input);
        $db = $this->db;
        $db->beginTransaction();

        try {
            $item = $this->findInventoryItem($data['inventory_id'], true);
            if (!$item) {
                throw new \RuntimeException('The selected item is no longer available.');
            }
            if ((int)$item['quantity'] < $data['quantity']) {
                throw new \RuntimeException('Only ' . (int)$item['quantity'] . ' ' . ($item['unit'] ?: 'unit') . '(s) of ' . $item['item_name'] . ' are in stock.');
            }

            $stmt = $db->prepare("
                INSERT INTO relief_distributions
                    (inventory_id, resident_id, disaster_event_id, quantity, distributed_at, distributed_by, or_number)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['inventory_id'],
                $data['resident_id'],
                $data['disaster_event_id'],
                $data['quantity'],
                $data['distributed_at'],
                $data['distributed_by'],
                $data['or_number'],
            ]);

            $this->adjustInventoryStock($item, -$data['quantity']);

            $db->commit();
        } catch (\RuntimeException $e) {
            $db->rollBack();
            flash('error', $e->getMessage());
            redirect(admin_url('drrm/relief'));
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not record the distribution. Please try again.');
            redirect(admin_url('drrm/relief'));
        }

        flash('success', 'Distribution recorded.');
        redirect(admin_url('drrm/relief'));
    }

    public function updateDistribution($id) {
        $distribution = $this->findDistribution($id);
        if (!$distribution) {
            flash('error', 'Distribution record not found.');
            redirect(admin_url('drrm/relief'));
        }

        $input = $this->getInput();
        $errors = $this->validateDistribution($input);
        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('drrm/relief'));
        }

        $data = $this->distributionData($input);
        $oldInventoryId = (int)$distribution['inventory_id'];
        $newInventoryId = (int)$data['inventory_id'];
        $oldQuantity = (int)$distribution['quantity'];
        $newQuantity = (int)$data['quantity'];

        $db = $this->db;
        $db->beginTransaction();

        try {
            $lockIds = array_values(array_unique([$oldInventoryId, $newInventoryId]));
            sort($lockIds);

            $locked = [];
            foreach ($lockIds as $lockId) {
                $locked[$lockId] = $this->findInventoryItem($lockId, true);
            }

            $oldItem = $locked[$oldInventoryId] ?? null;
            if (!$oldItem) {
                throw new \RuntimeException('The original inventory item is no longer available.');
            }

            if ($oldInventoryId === $newInventoryId) {
                if ((int)$oldItem['quantity'] + $oldQuantity < $newQuantity) {
                    throw new \RuntimeException('Only ' . ((int)$oldItem['quantity'] + $oldQuantity) . ' ' . ($oldItem['unit'] ?: 'unit') . '(s) of ' . $oldItem['item_name'] . ' are available.');
                }
            } else {
                $newItem = $locked[$newInventoryId] ?? null;
                if (!$newItem) {
                    throw new \RuntimeException('The selected item is no longer available.');
                }
                if ((int)$newItem['quantity'] < $newQuantity) {
                    throw new \RuntimeException('Only ' . (int)$newItem['quantity'] . ' ' . ($newItem['unit'] ?: 'unit') . '(s) of ' . $newItem['item_name'] . ' are in stock.');
                }
            }

            $stmt = $db->prepare("
                UPDATE relief_distributions
                SET inventory_id = ?, resident_id = ?, disaster_event_id = ?, quantity = ?,
                    distributed_at = ?, distributed_by = ?, or_number = ?, updated_at = ?
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([
                $data['inventory_id'],
                $data['resident_id'],
                $data['disaster_event_id'],
                $data['quantity'],
                $data['distributed_at'],
                $data['distributed_by'],
                $data['or_number'],
                date('Y-m-d H:i:s'),
                (int)$distribution['id'],
            ]);

            if ($oldInventoryId === $newInventoryId) {
                $this->adjustInventoryStock($oldItem, $oldQuantity - $newQuantity);
            } else {
                $this->adjustInventoryStock($oldItem, $oldQuantity);
                $this->adjustInventoryStock($locked[$newInventoryId], -$newQuantity);
            }

            $db->commit();
        } catch (\RuntimeException $e) {
            $db->rollBack();
            flash('error', $e->getMessage());
            redirect(admin_url('drrm/relief'));
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not update the distribution. Please try again.');
            redirect(admin_url('drrm/relief'));
        }

        flash('success', 'Distribution updated.');
        redirect(admin_url('drrm/relief'));
    }

    public function deleteDistribution($id) {
        $distribution = $this->findDistribution($id);
        if (!$distribution) {
            flash('error', 'Distribution record not found.');
            redirect(admin_url('drrm/relief'));
        }

        $db = $this->db;
        $db->beginTransaction();

        try {
            $item = $this->findInventoryItem((int)$distribution['inventory_id'], true);
            if ($item) {
                $this->adjustInventoryStock($item, (int)$distribution['quantity']);
            }

            $stmt = $db->prepare("
                UPDATE relief_distributions
                SET deleted_at = ?, updated_at = ?
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), (int)$distribution['id']]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            flash('error', 'Could not delete the distribution. Please try again.');
            redirect(admin_url('drrm/relief'));
        }

        flash('success', 'Distribution deleted and stock restored.');
        redirect(admin_url('drrm/relief'));
    }

    private function validateInventory(array $input) {
        $errors = [];

        $name = trim((string)($input['item_name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Item name is required.';
        } elseif (mb_strlen($name) > 150) {
            $errors[] = 'Item name must be 150 characters or fewer.';
        }

        if (!in_array($input['category'] ?? '', self::RELIEF_CATEGORIES, true)) {
            $errors[] = 'Please select a valid category.';
        }

        $quantity = trim((string)($input['quantity'] ?? ''));
        if ($quantity === '' || !ctype_digit($quantity)) {
            $errors[] = 'Quantity must be a whole number of 0 or more.';
        }

        if (mb_strlen(trim((string)($input['unit'] ?? ''))) > 30) {
            $errors[] = 'Unit must be 30 characters or fewer.';
        }

        $expiry = trim((string)($input['expiry_date'] ?? ''));
        if ($expiry !== '' && !$this->isValidDate($expiry)) {
            $errors[] = 'Expiry date is not a valid date.';
        }

        if (mb_strlen(trim((string)($input['storage_location'] ?? ''))) > 200) {
            $errors[] = 'Storage location must be 200 characters or fewer.';
        }

        return $errors;
    }

    private function inventoryData(array $input) {
        return [
            'item_name' => trim((string)$input['item_name']),
            'category' => (string)$input['category'],
            'quantity' => (int)$input['quantity'],
            'unit' => trim((string)($input['unit'] ?? '')) === '' ? null : trim((string)$input['unit']),
            'expiry_date' => trim((string)($input['expiry_date'] ?? '')) === '' ? null : trim((string)$input['expiry_date']),
            'storage_location' => trim((string)($input['storage_location'] ?? '')) === '' ? null : trim((string)$input['storage_location']),
        ];
    }

    private function inventoryStatus($quantity, $expiryDate) {
        if ($expiryDate !== null && $expiryDate !== '' && $expiryDate < date('Y-m-d')) {
            return 'expired';
        }

        return (int)$quantity <= 0 ? 'depleted' : 'available';
    }

    private function validateDistribution(array $input) {
        $errors = [];

        $inventoryId = trim((string)($input['inventory_id'] ?? ''));
        if ($inventoryId === '' || !ctype_digit($inventoryId) || !$this->findInventoryItem((int)$inventoryId)) {
            $errors[] = 'Please select a valid inventory item.';
        }

        $residentId = trim((string)($input['resident_id'] ?? ''));
        if ($residentId === '' || !ctype_digit($residentId) || !$this->recordExists('residents', (int)$residentId)) {
            $errors[] = 'Please select a valid resident.';
        }

        $eventId = trim((string)($input['disaster_event_id'] ?? ''));
        if ($eventId === '' || !ctype_digit($eventId) || !$this->recordExists('disaster_events', (int)$eventId)) {
            $errors[] = 'Please select a valid disaster event.';
        }

        $quantity = trim((string)($input['quantity'] ?? ''));
        if ($quantity === '' || !ctype_digit($quantity) || (int)$quantity < 1) {
            $errors[] = 'Quantity must be a whole number of 1 or more.';
        }

        $distributedAt = trim((string)($input['distributed_at'] ?? ''));
        if ($distributedAt !== '' && strtotime($distributedAt) === false) {
            $errors[] = 'Distribution date/time is not valid.';
        }

        if (mb_strlen(trim((string)($input['distributed_by'] ?? ''))) > 100) {
            $errors[] = 'Distributed by must be 100 characters or fewer.';
        }

        if (mb_strlen(trim((string)($input['or_number'] ?? ''))) > 30) {
            $errors[] = 'OR number must be 30 characters or fewer.';
        }

        return $errors;
    }

    private function distributionData(array $input) {
        $distributedAt = trim((string)($input['distributed_at'] ?? ''));

        return [
            'inventory_id' => (int)$input['inventory_id'],
            'resident_id' => (int)$input['resident_id'],
            'disaster_event_id' => (int)$input['disaster_event_id'],
            'quantity' => (int)$input['quantity'],
            'distributed_at' => $distributedAt === '' ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($distributedAt)),
            'distributed_by' => trim((string)($input['distributed_by'] ?? '')) === '' ? null : trim((string)$input['distributed_by']),
            'or_number' => trim((string)($input['or_number'] ?? '')) === '' ? null : trim((string)$input['or_number']),
        ];
    }

    private function findInventoryItem($id, $lock = false) {
        $sql = "SELECT * FROM relief_inventory WHERE id = ? AND deleted_at IS NULL";
        if ($lock) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function findDistribution($id) {
        $stmt = $this->db->prepare("SELECT * FROM relief_distributions WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function adjustInventoryStock(array $item, $delta) {
        $quantity = max(0, (int)$item['quantity'] + (int)$delta);
        $status = $this->inventoryStatus($quantity, $item['expiry_date'] ?? null);

        $stmt = $this->db->prepare("
            UPDATE relief_inventory
            SET quantity = ?, status = ?, updated_at = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$quantity, $status, date('Y-m-d H:i:s'), (int)$item['id']]);
    }

    private function recordExists($table, $id) {
        $allowed = ['residents', 'disaster_events'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM {$table} WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([(int)$id]);

        return (int)$stmt->fetch()['c'] > 0;
    }

    private function isValidDate($value) {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}