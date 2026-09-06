-- =====================================================================
-- seed_health.sql - Sample health records for development/testing
-- Idempotent, re-runnable: each sample row is guarded by a row-specific
-- NOT EXISTS check, so re-running never duplicates existing data.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Restore soft-deleted pre-existing residents (Juan Cruz #1, #2)
-- ---------------------------------------------------------------------
UPDATE residents SET deleted_at = NULL, status = 'active' WHERE id IN (1,2) AND deleted_at IS NOT NULL;

-- ---------------------------------------------------------------------
-- 2. Ensure a small sample family exists (added only if missing)
-- ---------------------------------------------------------------------
INSERT INTO residents (first_name, middle_name, last_name, sex, birthdate, civil_status, is_voter, status, is_approved)
SELECT 'Ana', 'Mae', 'Cruz', 'female', '1992-03-10', 'married', 1, 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM residents WHERE first_name = 'Ana' AND last_name = 'Cruz');

INSERT INTO residents (first_name, middle_name, last_name, sex, birthdate, civil_status, is_voter, status, is_approved)
SELECT 'Miguel', '', 'Cruz', 'male', '2024-02-20', 'single', 0, 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM residents WHERE first_name = 'Miguel' AND last_name = 'Cruz');

INSERT INTO residents (first_name, middle_name, last_name, sex, birthdate, civil_status, is_voter, status, is_approved)
SELECT 'Bella', '', 'Reyes', 'female', '2023-06-15', 'single', 0, 'active', 1
WHERE NOT EXISTS (SELECT 1 FROM residents WHERE first_name = 'Bella' AND last_name = 'Reyes');

-- ---------------------------------------------------------------------
-- 3. Maternal records (idempotent per resident)
-- ---------------------------------------------------------------------
INSERT INTO maternal_records (resident_id, lmp_date, expected_due_date, complications, attending_midwife, status)
SELECT r.id, '2026-06-01', '2027-03-08', 'Mild anemia', 'Nurse Ana Santos', 'pregnant'
FROM residents r
WHERE r.first_name = 'Ana' AND r.last_name = 'Cruz'
  AND NOT EXISTS (SELECT 1 FROM maternal_records m WHERE m.resident_id = r.id)
LIMIT 1;

INSERT INTO maternal_records (resident_id, birth_weight, birth_date, complications, attending_midwife, status)
SELECT r.id, 3.10, '2023-06-15', 'None', 'Nurse Ana Santos', 'delivered'
FROM residents r
WHERE r.first_name = 'Bella' AND r.last_name = 'Reyes'
  AND NOT EXISTS (SELECT 1 FROM maternal_records m WHERE m.resident_id = r.id)
LIMIT 1;

-- ---------------------------------------------------------------------
-- 4. Immunization records (idempotent per resident/vaccine/dose)
-- ---------------------------------------------------------------------
INSERT INTO immunization_records (child_resident_id, vaccine_name, dose_number, date_administered, batch_number, next_schedule, administered_by)
SELECT r.id, 'BCG', 1, '2024-02-25', 'BCG-2418', NULL, 'Nurse Ana Santos'
FROM residents r
WHERE r.first_name = 'Miguel' AND r.last_name = 'Cruz'
  AND NOT EXISTS (SELECT 1 FROM immunization_records i
                  WHERE i.child_resident_id = r.id AND i.vaccine_name = 'BCG' AND i.dose_number = 1)
LIMIT 1;

INSERT INTO immunization_records (child_resident_id, vaccine_name, dose_number, date_administered, batch_number, next_schedule, administered_by)
SELECT r.id, 'DPT-HepB', 2, '2024-07-01', 'DPT-3391', NULL, 'Nurse Ana Santos'
FROM residents r
WHERE r.first_name = 'Miguel' AND r.last_name = 'Cruz'
  AND NOT EXISTS (SELECT 1 FROM immunization_records i
                  WHERE i.child_resident_id = r.id AND i.vaccine_name = 'DPT-HepB' AND i.dose_number = 2)
LIMIT 1;

INSERT INTO immunization_records (child_resident_id, vaccine_name, dose_number, date_administered, batch_number, next_schedule, administered_by)
SELECT r.id, 'MMR', 1, '2024-11-12', 'MMR-4520', '2025-05-12', 'Dr. Reyes'
FROM residents r
WHERE r.first_name = 'Bella' AND r.last_name = 'Reyes'
  AND NOT EXISTS (SELECT 1 FROM immunization_records i
                  WHERE i.child_resident_id = r.id AND i.vaccine_name = 'MMR' AND i.dose_number = 1)
LIMIT 1;

-- ---------------------------------------------------------------------
-- 5. Child growth records (idempotent per resident/age/weight)
-- ---------------------------------------------------------------------
INSERT INTO child_growth_records (child_resident_id, age_months, weight_kg, height_cm, head_circumference, nutrition_status, recorded_by)
SELECT r.id, 24, 12.10, 86.0, 48.0, 'normal', 'Nurse Ana Santos'
FROM residents r
WHERE r.first_name = 'Miguel' AND r.last_name = 'Cruz'
  AND NOT EXISTS (SELECT 1 FROM child_growth_records g
                  WHERE g.child_resident_id = r.id AND g.age_months = 24 AND g.weight_kg = 12.10)
LIMIT 1;

INSERT INTO child_growth_records (child_resident_id, age_months, weight_kg, height_cm, head_circumference, nutrition_status, recorded_by)
SELECT r.id, 36, 13.40, 92.0, 49.0, 'normal', 'Nurse Ana Santos'
FROM residents r
WHERE r.first_name = 'Bella' AND r.last_name = 'Reyes'
  AND NOT EXISTS (SELECT 1 FROM child_growth_records g
                  WHERE g.child_resident_id = r.id AND g.age_months = 36 AND g.weight_kg = 13.40)
LIMIT 1;

INSERT INTO child_growth_records (child_resident_id, age_months, weight_kg, height_cm, head_circumference, nutrition_status, recorded_by)
SELECT r.id, 1, 4.20, 54.0, 36.0, 'normal', 'Nurse Ana Santos'
FROM residents r
WHERE r.first_name = 'adw'
  AND NOT EXISTS (SELECT 1 FROM child_growth_records g WHERE g.child_resident_id = r.id AND g.age_months = 1)
LIMIT 1;

-- ---------------------------------------------------------------------
-- 6. Disease surveillance (idempotent per disease/date/age)
-- ---------------------------------------------------------------------
INSERT INTO disease_surveillance (disease_name, date_reported, purok_id, patient_age, patient_sex, status, reported_to_doctor, notes)
SELECT 'dengue', '2026-08-20', NULL, 5, 'male', 'confirmed', 1, 'Admitted; recovered after 3 days'
WHERE NOT EXISTS (SELECT 1 FROM disease_surveillance s WHERE s.disease_name = 'dengue' AND s.date_reported = '2026-08-20');

INSERT INTO disease_surveillance (disease_name, date_reported, purok_id, patient_age, patient_sex, status, reported_to_doctor, notes)
SELECT 'influenza', '2026-09-01', NULL, 34, 'female', 'suspected', 0, 'Monitoring at home'
WHERE NOT EXISTS (SELECT 1 FROM disease_surveillance s WHERE s.disease_name = 'influenza' AND s.date_reported = '2026-09-01');

INSERT INTO disease_surveillance (disease_name, date_reported, purok_id, patient_age, patient_sex, status, reported_to_doctor, notes)
SELECT 'acute diarrhea', '2026-08-15', NULL, 2, 'male', 'recovered', 1, 'ORs given'
WHERE NOT EXISTS (SELECT 1 FROM disease_surveillance s WHERE s.disease_name = 'acute diarrhea' AND s.date_reported = '2026-08-15');

INSERT INTO disease_surveillance (disease_name, date_reported, purok_id, patient_age, patient_sex, status, reported_to_doctor, notes)
SELECT 'leptospirosis', '2026-08-28', NULL, 40, 'male', 'suspected', 1, 'Flood exposure; referred'
WHERE NOT EXISTS (SELECT 1 FROM disease_surveillance s WHERE s.disease_name = 'leptospirosis' AND s.date_reported = '2026-08-28');

-- ---------------------------------------------------------------------
-- 7. Health programs (idempotent per name/date)
-- ---------------------------------------------------------------------
INSERT INTO health_programs (name, target_group, schedule_date, attendees_count, conducted_by, notes)
SELECT 'Prenatal Checkup Drive', 'Pregnant women', '2026-08-20', 24, 'Rural Health Unit', 'Monthly prenatal visit'
WHERE NOT EXISTS (SELECT 1 FROM health_programs p WHERE p.name = 'Prenatal Checkup Drive' AND p.schedule_date = '2026-08-20');

INSERT INTO health_programs (name, target_group, schedule_date, attendees_count, conducted_by, notes)
SELECT 'Child Nutrition Assessment', 'Children 0-5', '2026-08-30', 18, 'BHW Isabel', 'Weighing + deworming'
WHERE NOT EXISTS (SELECT 1 FROM health_programs p WHERE p.name = 'Child Nutrition Assessment' AND p.schedule_date = '2026-08-30');

INSERT INTO health_programs (name, target_group, schedule_date, attendees_count, conducted_by, notes)
SELECT 'Dengue Awareness Campaign', 'All residents', '2026-09-10', 0, 'BHW Isabel', 'House-to-house info drive'
WHERE NOT EXISTS (SELECT 1 FROM health_programs p WHERE p.name = 'Dengue Awareness Campaign' AND p.schedule_date = '2026-09-10');

INSERT INTO health_programs (name, target_group, schedule_date, attendees_count, conducted_by, notes)
SELECT 'National Immunization Day', 'Children 0-5', '2026-09-15', 0, 'Rural Health Unit', 'Free vaccines at barangay hall'
WHERE NOT EXISTS (SELECT 1 FROM health_programs p WHERE p.name = 'National Immunization Day' AND p.schedule_date = '2026-09-15');