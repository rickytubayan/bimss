<?php
namespace Controllers\Admin;

class DashboardController extends \Controller {
    public function index() {
        $db = $this->db;

        $stats = [
            'residents' => (int)$db->query("SELECT COUNT(*) c FROM residents WHERE deleted_at IS NULL AND status='active'")->fetch()['c'],
            'households' => (int)$db->query("SELECT COUNT(*) c FROM households WHERE deleted_at IS NULL")->fetch()['c'],
            'pending_requests' => (int)$db->query("SELECT COUNT(*) c FROM document_requests WHERE status IN ('pending','processing','for_signing')")->fetch()['c'],
            'open_complaints' => (int)$db->query("SELECT COUNT(*) c FROM complaints WHERE status IN ('submitted','investigating')")->fetch()['c'],
            'open_blotters' => (int)$db->query("SELECT COUNT(*) c FROM blotters WHERE status IN ('filed','investigating','for_hearing')")->fetch()['c'],
            'evacuation_occupancy' => (int)$db->query("SELECT COALESCE(SUM(current_occupancy),0) c FROM evacuation_centers")->fetch()['c'],
            'total_revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) c FROM income_records WHERE deleted_at IS NULL")->fetch()['c'],
            'recent_clearances' => (int)$db->query("SELECT COUNT(*) c FROM document_requests WHERE status IN ('ready','released') AND DATE(requested_at) = CURDATE()")->fetch()['c'],
        ];

        $recentResidents = $db->query("SELECT * FROM residents ORDER BY created_at DESC LIMIT 8")->fetchAll();
        $recentRequests = $db->query("SELECT dr.*, dt.name as doc_type FROM document_requests dr LEFT JOIN document_types dt ON dt.id = dr.document_type_id ORDER BY dr.created_at DESC LIMIT 8")->fetchAll();
        $recentComplaints = $db->query("SELECT * FROM complaints ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $monthlyRevenue = $db->query("SELECT MONTHNAME(income_date) as month, SUM(amount) as total FROM income_records WHERE YEAR(income_date) = YEAR(CURDATE()) GROUP BY MONTH(income_date) ORDER BY MIN(income_date)")->fetchAll();
        $purokDistribution = $db->query("SELECT p.name, COUNT(r.id) as total FROM puroks p LEFT JOIN residents r ON r.purok_id = p.id WHERE r.deleted_at IS NULL GROUP BY p.id")->fetchAll();

        $this->viewAdmin('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentResidents' => $recentResidents,
            'recentRequests' => $recentRequests,
            'recentComplaints' => $recentComplaints,
            'monthlyRevenue' => $monthlyRevenue,
            'purokDistribution' => $purokDistribution,
        ]);
    }
}
