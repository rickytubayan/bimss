<?php
namespace Models;

class Household extends \Model {
    protected $table = 'households';
    protected $fillable = [
        'barangay_id', 'purok_id', 'house_number', 'street', 'gps_latitude',
        'gps_longitude', 'classification', 'status'
    ];

    public function getMembers($householdId) {
        $resident = new Resident();
        return $resident->findWhere('household_id', $householdId);
    }

    public function getHead($householdId) {
        $db = $this->db;
        $stmt = $db->prepare("
            SELECT r.* FROM residents r
            JOIN resident_links rl ON rl.resident_id_a = r.id
            WHERE rl.resident_id_b = ? AND rl.relationship = 'parent'
        ");
        $stmt->execute([$householdId]);
        return $stmt->fetch() ?: null;
    }
}
