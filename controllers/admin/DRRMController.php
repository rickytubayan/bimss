<?php
namespace Controllers\Admin;

class DRRMController extends \Controller {

    private const EVENT_TYPES = ['flood', 'typhoon', 'earthquake', 'fire', 'landslide', 'volcanic', 'drought', 'other'];
    private const SEVERITIES = ['minor', 'moderate', 'severe', 'catastrophic'];

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
            SELECT d.*, i.item_name, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, e.name AS event_name
            FROM relief_distributions d
            JOIN relief_inventory i ON i.id = d.inventory_id
            JOIN residents r ON r.id = d.resident_id
            JOIN disaster_events e ON e.id = d.disaster_event_id
            WHERE d.deleted_at IS NULL
            ORDER BY d.distributed_at DESC, d.id DESC LIMIT 10
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

        $this->viewAdmin('drrm/relief', [
            'title' => 'DRRM - Relief',
            'items' => $items,
            'distributions' => $distributions,
            'stats' => $stats,
            'events' => $events,
            'residents' => $residents,
        ]);
    }
}