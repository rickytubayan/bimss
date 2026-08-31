<?php
namespace Controllers\Public;

class HomeController extends \Controller {
    public function index() {
        $this->viewPublic('home/index', [
            'title' => 'Barangay Information Management System',
            'barangayName' => $this->db->query("SELECT name FROM barangays LIMIT 1")->fetch()['name'] ?? 'Barangay',
        ]);
    }

    public function dashboard() {
        $residentModel = new \Models\Resident();
        $stats = $residentModel->getDisaggregatedCounts();

        $db = $this->db;
        $user = \Auth::user();

        $recentBulletins = $db->query("SELECT * FROM bulletins WHERE published_at IS NOT NULL AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY published_at DESC LIMIT 5")->fetchAll();
        $recentComplaints = $db->query("SELECT * FROM complaints WHERE resident_id = " . intval($user['id']) . " ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $notifications = $db->query("SELECT * FROM notifications WHERE recipient_id = " . intval($user['id']) . " ORDER BY created_at DESC LIMIT 5")->fetchAll();

        $this->viewPublic('home/dashboard', [
            'title' => 'My Dashboard',
            'user' => $user['username'],
            'stats' => $stats,
            'recentBulletins' => $recentBulletins,
            'recentComplaints' => $recentComplaints,
            'notifications' => $notifications,
        ]);
    }
}
