<?php
namespace Controllers\Admin;

class DashboardController extends \Controller {
    public function index() {
        $db = $this->db;

        $stats = [
            'residents' => (int)$db->query("SELECT COUNT(*) c FROM residents WHERE deleted_at IS NULL AND status = 'active'")->fetch()['c'],
            'households' => (int)$db->query("SELECT COUNT(*) c FROM households WHERE deleted_at IS NULL")->fetch()['c'],
            'seniors_pwd' => (int)$db->query("SELECT COUNT(*) c FROM senior_pwd_profiles WHERE deleted_at IS NULL")->fetch()['c'],
            'pending_requests' => (int)$db->query("SELECT COUNT(*) c FROM document_requests WHERE deleted_at IS NULL AND status IN ('pending','processing','for_signing')")->fetch()['c'],
            'open_blotters' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE deleted_at IS NULL AND status IN ('filed','investigating','for_hearing')")->fetch()['c'],
            'open_complaints' => (int)$db->query("SELECT COUNT(*) c FROM complaints WHERE deleted_at IS NULL AND status IN ('submitted','investigating')")->fetch()['c'],
            'relief_units' => (int)$db->query("SELECT COALESCE(SUM(quantity),0) c FROM relief_inventory WHERE deleted_at IS NULL AND status = 'available'")->fetch()['c'],
            'month_revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM income_records WHERE deleted_at IS NULL AND YEAR(income_date) = YEAR(CURDATE()) AND MONTH(income_date) = MONTH(CURDATE())")->fetch()['c'],
            'total_revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM income_records WHERE deleted_at IS NULL")->fetch()['c'],
            'upcoming_bookings' => (int)$db->query("SELECT COUNT(*) c FROM venue_bookings WHERE deleted_at IS NULL AND status IN ('pending','confirmed') AND event_date >= CURDATE()")->fetch()['c'],
        ];

        $recentResidents = $db->query("
            SELECT r.*, p.name AS purok_name
            FROM residents r
            LEFT JOIN puroks p ON p.id = r.purok_id AND p.deleted_at IS NULL
            WHERE r.deleted_at IS NULL
            ORDER BY r.created_at DESC, r.id DESC
            LIMIT 5
        ")->fetchAll();

        $recentRequests = $db->query("
            SELECT dr.*, dt.name AS doc_type, CONCAT(r.last_name, ', ', r.first_name) AS resident_name
            FROM document_requests dr
            LEFT JOIN document_types dt ON dt.id = dr.document_type_id
            LEFT JOIN residents r ON r.id = dr.resident_id
            WHERE dr.deleted_at IS NULL
            ORDER BY dr.created_at DESC, dr.id DESC
            LIMIT 6
        ")->fetchAll();

        $recentBlotters = $db->query("
            SELECT b.*, p.name AS purok_name
            FROM blotters b
            LEFT JOIN puroks p ON p.id = b.purok_id
            WHERE b.deleted_at IS NULL
            ORDER BY b.date_time_of_incident DESC, b.id DESC
            LIMIT 5
        ")->fetchAll();

        $recentDistributions = $db->query("
            SELECT d.*, i.item_name, i.unit,
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   e.name AS event_name
            FROM relief_distributions d
            JOIN relief_inventory i ON i.id = d.inventory_id
            JOIN residents r ON r.id = d.resident_id
            JOIN disaster_events e ON e.id = d.disaster_event_id
            WHERE d.deleted_at IS NULL
            ORDER BY d.distributed_at DESC, d.id DESC
            LIMIT 5
        ")->fetchAll();

        $reliefItems = $db->query("
            SELECT * FROM relief_inventory
            WHERE deleted_at IS NULL
            ORDER BY FIELD(status, 'available', 'depleted', 'expired'), item_name
            LIMIT 5
        ")->fetchAll();

        $revenueRows = $db->query("
            SELECT MONTH(income_date) AS m, SUM(amount) AS total
            FROM income_records
            WHERE deleted_at IS NULL AND YEAR(income_date) = YEAR(CURDATE())
            GROUP BY MONTH(income_date)
        ")->fetchAll();

        $byMonth = array_fill(1, 12, 0.0);
        foreach ($revenueRows as $row) {
            $byMonth[(int)$row['m']] = (float)$row['total'];
        }

        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = [
                'month' => date('M', mktime(0, 0, 0, $m, 1)),
                'total' => $byMonth[$m],
            ];
        }

        $purokDistribution = $db->query("
            SELECT COALESCE(p.name, 'Unassigned') AS name, COUNT(r.id) AS total
            FROM residents r
            LEFT JOIN puroks p ON p.id = r.purok_id AND p.deleted_at IS NULL
            WHERE r.deleted_at IS NULL
            GROUP BY p.id, p.name
            ORDER BY total DESC, name
        ")->fetchAll();

        $this->viewAdmin('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentResidents' => $recentResidents,
            'recentRequests' => $recentRequests,
            'recentBlotters' => $recentBlotters,
            'recentDistributions' => $recentDistributions,
            'reliefItems' => $reliefItems,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyRevenueMax' => max(1, max(array_column($monthlyRevenue, 'total'))),
            'purokDistribution' => $purokDistribution,
            'purokDistributionMax' => max(1, max(array_column($purokDistribution, 'total') ?: [1])),
        ]);
    }
}
