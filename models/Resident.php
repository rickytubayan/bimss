<?php
namespace Models;

class Resident extends \Model {
    protected $table = 'residents';
    protected $fillable = [
        'household_id', 'purok_id', 'national_id', 'first_name', 'middle_name',
        'last_name', 'suffix', 'sex', 'birthdate', 'civil_status', 'blood_type',
        'disability_type', 'is_pwd', 'is_senior', 'is_voter', 'educational_attainment',
        'occupation', 'monthly_income', 'phone', 'email', 'photo', 'status', 'is_approved'
    ];

    public function getByPurok($purokId) {
        return $this->findWhere('purok_id', $purokId);
    }

    public function getSeniors() {
        return $this->findAll(['is_senior' => 1]);
    }

    public function getPWDs() {
        return $this->findAll(['is_pwd' => 1]);
    }

    public function getDisaggregatedCounts() {
        $db = $this->db;
        $total = $this->count(['status' => 'active']);
        $male = $this->count(['status' => 'active', 'sex' => 'male']);
        $female = $this->count(['status' => 'active', 'sex' => 'female']);
        $seniors = $this->count(['status' => 'active', 'is_senior' => 1]);
        $pwds = $this->count(['status' => 'active', 'is_pwd' => 1]);
        $voters = $this->count(['status' => 'active', 'is_voter' => 1]);

        return [
            'total' => $total,
            'male' => $male,
            'female' => $female,
            'seniors' => $seniors,
            'pwds' => $pwds,
            'voters' => $voters,
        ];
    }

    public function getFullName($resident) {
        $name = trim($resident['first_name'] . ' ' . ($resident['middle_name'] ?? '') . ' ' . $resident['last_name']);
        return $name;
    }
}
