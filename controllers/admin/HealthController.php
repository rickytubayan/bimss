<?php
namespace Controllers\Admin;

class HealthController extends \Controller {

    private const NAME_SQL = "CONCAT(r.last_name, ', ', IFNULL(CONCAT(r.first_name, ' ', IFNULL(r.middle_name, '')), r.first_name))";

    public function index() {
        $db = $this->db;

        $data = [
            'pregnant' => (int)$db->query("SELECT COUNT(*) c FROM maternal_records WHERE deleted_at IS NULL AND status = 'pregnant'")->fetch()['c'],
            'maternal' => (int)$db->query("SELECT COUNT(*) c FROM maternal_records WHERE deleted_at IS NULL")->fetch()['c'],
            'immunization' => (int)$db->query("SELECT COUNT(*) c FROM immunization_records WHERE deleted_at IS NULL")->fetch()['c'],
            'growth' => (int)$db->query("SELECT COUNT(*) c FROM child_growth_records WHERE deleted_at IS NULL")->fetch()['c'],
            'surveillance' => (int)$db->query("SELECT COUNT(*) c FROM disease_surveillance WHERE deleted_at IS NULL")->fetch()['c'],
            'programs' => (int)$db->query("SELECT COUNT(*) c FROM health_programs WHERE deleted_at IS NULL")->fetch()['c'],
        ];

        $recentPrograms = $db->query("SELECT * FROM health_programs WHERE deleted_at IS NULL ORDER BY schedule_date DESC, id DESC LIMIT 5")->fetchAll();
        $recentSurveillance = $db->query("
            SELECT s.*, p.name AS purok_name
            FROM disease_surveillance s
            LEFT JOIN puroks p ON p.id = s.purok_id
            WHERE s.deleted_at IS NULL
            ORDER BY s.date_reported DESC, s.id DESC LIMIT 5
        ")->fetchAll();

        $this->viewAdmin('health/index', [
            'title' => 'Health',
            'stats' => $data,
            'recentPrograms' => $recentPrograms,
            'recentSurveillance' => $recentSurveillance,
        ]);
    }

    public function maternal() {
        $records = $this->db->query("
            SELECT m.*, " . self::NAME_SQL . " AS resident_name
            FROM maternal_records m
            JOIN residents r ON r.id = m.resident_id
            WHERE m.deleted_at IS NULL
            ORDER BY m.created_at DESC, m.id DESC
        ")->fetchAll();

        $residents = $this->residentOptions();

        $this->viewAdmin('health/maternal', [
            'title' => 'Health - Maternal Records',
            'records' => $records,
            'residents' => $residents,
        ]);
    }

    public function storeMaternal() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['resident_id'] ?? '')) $errors[] = 'Resident is required.';
        if (!in_array($input['status'] ?? '', ['pregnant', 'delivered', 'postpartum'])) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('health/maternal'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO maternal_records (resident_id, lmp_date, expected_due_date, birth_weight, birth_date, complications, attending_midwife, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$input['resident_id'],
            ($input['lmp_date'] ?? '') === '' ? null : $input['lmp_date'],
            ($input['expected_due_date'] ?? '') === '' ? null : $input['expected_due_date'],
            ($input['birth_weight'] ?? '') === '' ? null : (float)$input['birth_weight'],
            ($input['birth_date'] ?? '') === '' ? null : $input['birth_date'],
            ($input['complications'] ?? '') === '' ? null : trim($input['complications']),
            ($input['attending_midwife'] ?? '') === '' ? null : trim($input['attending_midwife']),
            $input['status'],
        ]);

        flash('success', 'Maternal record saved successfully.');
        redirect(admin_url('health/maternal'));
    }

    public function immunization() {
        $records = $this->db->query("
            SELECT i.*, " . self::NAME_SQL . " AS child_name
            FROM immunization_records i
            JOIN residents r ON r.id = i.child_resident_id
            WHERE i.deleted_at IS NULL
            ORDER BY i.date_administered DESC, i.id DESC
        ")->fetchAll();

        $residents = $this->residentOptions();

        $this->viewAdmin('health/immunization', [
            'title' => 'Health - Immunization',
            'records' => $records,
            'residents' => $residents,
        ]);
    }

    public function storeImmunization() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['child_resident_id'] ?? '')) $errors[] = 'Child resident is required.';
        if (empty(trim($input['vaccine_name'] ?? ''))) $errors[] = 'Vaccine name is required.';
        if (empty(trim($input['dose_number'] ?? '')) || (int)$input['dose_number'] <= 0) $errors[] = 'Dose number is required.';
        if (empty(trim($input['date_administered'] ?? ''))) $errors[] = 'Date administered is required.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('health/immunization'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO immunization_records (child_resident_id, vaccine_name, dose_number, date_administered, batch_number, next_schedule, administered_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$input['child_resident_id'],
            trim($input['vaccine_name']),
            (int)$input['dose_number'],
            $input['date_administered'],
            ($input['batch_number'] ?? '') === '' ? null : trim($input['batch_number']),
            ($input['next_schedule'] ?? '') === '' ? null : $input['next_schedule'],
            ($input['administered_by'] ?? '') === '' ? null : trim($input['administered_by']),
        ]);

        flash('success', 'Immunization record saved successfully.');
        redirect(admin_url('health/immunization'));
    }

    public function growth() {
        $records = $this->db->query("
            SELECT g.*, " . self::NAME_SQL . " AS child_name
            FROM child_growth_records g
            JOIN residents r ON r.id = g.child_resident_id
            WHERE g.deleted_at IS NULL
            ORDER BY g.created_at DESC, g.id DESC
        ")->fetchAll();

        $residents = $this->residentOptions();

        $this->viewAdmin('health/growth', [
            'title' => 'Health - Child Growth',
            'records' => $records,
            'residents' => $residents,
        ]);
    }

    public function storeGrowth() {
        $input = $this->getInput();

        $errors = [];
        if (empty($input['child_resident_id'] ?? '')) $errors[] = 'Child resident is required.';
        if (empty(trim($input['age_months'] ?? '')) || (int)$input['age_months'] < 0) $errors[] = 'Age (months) is required.';
        if (empty(trim($input['weight_kg'] ?? '')) || (float)$input['weight_kg'] <= 0) $errors[] = 'Weight is required.';
        if (empty(trim($input['height_cm'] ?? '')) || (float)$input['height_cm'] <= 0) $errors[] = 'Height is required.';
        if (!in_array($input['nutrition_status'] ?? '', ['normal', 'overweight', 'underweight', 'severely_underweight', 'wasted', 'stunted'])) $errors[] = 'Please select a valid nutrition status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('health/growth'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO child_growth_records (child_resident_id, age_months, weight_kg, height_cm, head_circumference, nutrition_status, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$input['child_resident_id'],
            (int)$input['age_months'],
            (float)$input['weight_kg'],
            (float)$input['height_cm'],
            ($input['head_circumference'] ?? '') === '' ? null : (float)$input['head_circumference'],
            $input['nutrition_status'],
            ($input['recorded_by'] ?? '') === '' ? null : trim($input['recorded_by']),
        ]);

        flash('success', 'Child growth record saved successfully.');
        redirect(admin_url('health/growth'));
    }

    public function surveillance() {
        $records = $this->db->query("
            SELECT s.*, p.name AS purok_name
            FROM disease_surveillance s
            LEFT JOIN puroks p ON p.id = s.purok_id
            WHERE s.deleted_at IS NULL
            ORDER BY s.date_reported DESC, s.id DESC
        ")->fetchAll();

        $puroks = $this->db->query("SELECT id, name FROM puroks WHERE deleted_at IS NULL ORDER BY name")->fetchAll();

        $this->viewAdmin('health/surveillance', [
            'title' => 'Health - Disease Surveillance',
            'records' => $records,
            'puroks' => $puroks,
        ]);
    }

    public function storeSurveillance() {
        $input = $this->getInput();

        $errors = [];
        if (empty(trim($input['disease_name'] ?? ''))) $errors[] = 'Disease name is required.';
        if (empty(trim($input['date_reported'] ?? ''))) $errors[] = 'Date reported is required.';
        if (!in_array($input['status'] ?? '', ['suspected', 'confirmed', 'recovered', 'deceased'])) $errors[] = 'Please select a valid status.';

        if (!empty($errors)) {
            set_old_input($input);
            flash('error', implode(' ', $errors));
            redirect(admin_url('health/surveillance'));
        }

        $stmt = $this->db->prepare("
            INSERT INTO disease_surveillance (disease_name, date_reported, purok_id, patient_age, patient_sex, status, reported_to_doctor, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($input['disease_name']),
            $input['date_reported'],
            ($input['purok_id'] ?? '') === '' ? null : (int)$input['purok_id'],
            ($input['patient_age'] ?? '') === '' ? null : (int)$input['patient_age'],
            ($input['patient_sex'] ?? '') === '' ? null : $input['patient_sex'],
            $input['status'],
            isset($input['reported_to_doctor']) ? 1 : 0,
            ($input['notes'] ?? '') === '' ? null : trim($input['notes']),
        ]);

        flash('success', 'Surveillance record saved successfully.');
        redirect(admin_url('health/surveillance'));
    }

    private function residentOptions() {
        return $this->db->query("
            SELECT id, CONCAT(last_name, ', ', first_name) AS label
            FROM residents
            WHERE deleted_at IS NULL AND status = 'active'
            ORDER BY last_name, first_name
        ")->fetchAll();
    }
}