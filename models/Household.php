<?php
namespace Models;

class Household extends \Model {
    protected $table = 'households';
    protected $fillable = [
        'barangay_id', 'purok_id', 'head_resident_id', 'house_number', 'street',
        'classification', 'status'
    ];

    public function getMembers($householdId) {
        $resident = new Resident();
        return $resident->findWhere('household_id', $householdId);
    }

    public function getMember($householdId, $residentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM residents
            WHERE id = ? AND household_id = ? AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$residentId, $householdId]);
        return $stmt->fetch() ?: null;
    }

    public function getHead($householdId) {
        $stmt = $this->db->prepare("
            SELECT r.* FROM residents r
            JOIN households h ON h.head_resident_id = r.id
            WHERE h.id = ? AND r.household_id = h.id
              AND h.deleted_at IS NULL AND r.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$householdId]);
        return $stmt->fetch() ?: null;
    }

    public function setHead($householdId, $residentId) {
        $stmt = $this->db->prepare("
            UPDATE households
            SET head_resident_id = ?, updated_at = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$residentId, date('Y-m-d H:i:s'), $householdId]);
    }

    public function clearHeadByResident($residentId) {
        $stmt = $this->db->prepare("
            UPDATE households
            SET head_resident_id = NULL, updated_at = ?
            WHERE head_resident_id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([date('Y-m-d H:i:s'), $residentId]);
    }
}
