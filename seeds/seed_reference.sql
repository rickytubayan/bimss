-- =====================================================
-- BIMS Reference / Lookup Seed Data (idempotent)
-- Fills drop-downs and reference lists so forms can be tested.
-- Re-runnable: uses INSERT IGNORE + WHERE NOT EXISTS guards.
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Administrative geography (single sample barangay tree)
-- =====================================================
INSERT IGNORE INTO regions (code, name) VALUES
('1300000000', 'NCR - National Capital Region');

INSERT IGNORE INTO provinces (code, name, region_code) VALUES
('1380000000', 'Metro Manila (NCR)', '1300000000');

INSERT IGNORE INTO cities_municipalities (code, name, province_code, type) VALUES
('1380000000', 'Manila', '1380000000', 'city');

INSERT IGNORE INTO barangays (code, name, municipality_code, psgc_10digit) VALUES
('1380010000', 'Sample Barangay', '1380000000', '1380010000');

INSERT IGNORE INTO puroks (barangay_id, name, code, type, classification)
SELECT b.id, p.name, p.code, p.type, p.classification
FROM barangays b
JOIN (
    SELECT 'Purok 1' name, 'P1' code, 'purok' type, 'urban' classification UNION ALL
    SELECT 'Purok 2', 'P2', 'purok', 'urban' UNION ALL
    SELECT 'Purok 3', 'P3', 'purok', 'rural' UNION ALL
    SELECT 'Sitio Mabini', 'S1', 'sitio', 'rural' UNION ALL
    SELECT 'Sitio Bonifacio', 'S2', 'sitio', 'urban' UNION ALL
    SELECT 'Zone 5', 'Z5', 'zone', 'urban'
) p
WHERE b.name = 'Sample Barangay'
  AND NOT EXISTS (SELECT 1 FROM puroks pk WHERE pk.name = p.name LIMIT 1);

-- =====================================================
-- Households (linked to Purok 1–3)
-- =====================================================
INSERT IGNORE INTO households (barangay_id, purok_id, house_number, street, classification, status)
SELECT b.id, p.id, h.house_number, h.street, h.classification, h.status
FROM barangays b
JOIN (
    SELECT purok_name, house_number, street, classification, status FROM (
        SELECT 'Purok 1' purok_name, '001' house_number, 'Rizal Street' street, 'residential' classification, 'active' status UNION ALL
        SELECT 'Purok 1', '002', 'Rizal Street', 'residential', 'active' UNION ALL
        SELECT 'Purok 2', '003', 'Bonifacio Avenue', 'commercial', 'active' UNION ALL
        SELECT 'Purok 2', '004', 'Bonifacio Avenue', 'residential', 'vacant' UNION ALL
        SELECT 'Purok 3', '005', 'Mabini Street', 'residential', 'active' UNION ALL
        SELECT 'Purok 3', '006', 'Luna Street', 'mixed', 'active' UNION ALL
        SELECT 'Purok 1', '007', 'Quezon Street', 'residential', 'active' UNION ALL
        SELECT 'Purok 2', '008', 'Quezon Street', 'residential', 'active'
    ) x
) h
JOIN puroks p ON p.barangay_id = b.id AND p.name = h.purok_name
WHERE b.name = 'Sample Barangay'
  AND NOT EXISTS (
      SELECT 1 FROM households hh
      WHERE hh.barangay_id = b.id
        AND hh.purok_id = p.id
        AND hh.house_number = h.house_number
      LIMIT 1
  );

-- =====================================================
-- Residents (assigned to households via subqueries)
-- =====================================================
INSERT IGNORE INTO residents
    (household_id, purok_id, national_id, first_name, middle_name, last_name, suffix, sex,
     birthdate, civil_status, blood_type, disability_type, is_pwd, is_senior, is_voter,
     educational_attainment, occupation, monthly_income, phone, email, status, is_approved)
SELECT hh.id, p.id, r.national_id, r.first_name, r.middle_name, r.last_name, r.suffix, r.sex,
       r.birthdate, r.civil_status, r.blood_type, r.disability_type, r.is_pwd, r.is_senior, r.is_voter,
       r.educational_attainment, r.occupation, r.monthly_income, r.phone, r.email, r.status, 1
FROM (
    SELECT 'R-2024-001' national_id, 'Juan' first_name, 'Santos' middle_name, 'Cruz' last_name, '' suffix,
           'male' sex, '1980-04-12' birthdate, 'married' civil_status, 'O+' blood_type, 'none' disability_type,
           0 is_pwd, 0 is_senior, 1 is_voter, 'college' educational_attainment, 'Teacher' occupation,
           25000.00 monthly_income, '09170000001' phone, 'juan.cruz@example.com' email, 'active' status,
           '001' hh_number, 'Purok 1' purok_name UNION ALL
    SELECT 'R-2024-002', 'Maria', 'Cruz', 'Dela Cruz', '', 'female', '1983-09-25', 'married', 'A+', 'none',
           0, 0, 1, 'college', 'Nurse', 30000.00, '09170000002', 'maria.dc@example.com', 'active',
           '001', 'Purok 1' UNION ALL
    SELECT 'R-2024-003', 'Jose', '', 'Rizal', 'Jr.', 'male', '1961-06-19', 'married', 'B+', 'none',
           0, 1, 1, 'high_school', 'Retired', 10000.00, '09170000003', 'jose.rizal@example.com', 'active',
           '002', 'Purok 1' UNION ALL
    SELECT 'R-2024-004', 'Ana', '', 'Lim', '', 'female', '1995-01-30', 'single', 'AB+', 'none',
           0, 0, 1, 'college', 'Engineer', 40000.00, '09170000004', 'ana.lim@example.com', 'active',
           '003', 'Purok 2' UNION ALL
    SELECT 'R-2024-005', 'Pedro', 'Garcia', 'Mendoza', '', 'male', '1972-11-08', 'married', 'O-', 'none',
           0, 0, 1, 'college', 'Police Officer', 28000.00, '09170000005', 'pedro.mendoza@example.com', 'active',
           '003', 'Purok 2' UNION ALL
    SELECT 'R-2024-006', 'Liza', '', 'Reyes', '', 'female', '1988-03-14', 'married', 'A-', 'visual',
           1, 0, 1, 'vocational', 'Seamstress', 12000.00, '09170000006', 'liza.reyes@example.com', 'active',
           '004', 'Purok 2' UNION ALL
    SELECT 'R-2024-007', 'Carlo', '', 'Santos', '', 'male', '1990-07-21', 'single', 'B-', 'none',
           0, 0, 1, 'college', 'Business Owner', 50000.00, '09170000007', 'carlo.santos@example.com', 'active',
           '005', 'Purok 3' UNION ALL
    SELECT 'R-2024-008', 'Rosa', '', 'Aquino', '', 'female', '1958-12-02', 'widowed', 'O+', 'none',
           0, 1, 1, 'elementary', 'Housewife', 5000.00, '09170000008', 'rosa.aquino@example.com', 'active',
           '006', 'Purok 3' UNION ALL
    SELECT 'R-2024-009', 'Miguel', '', 'Torres', '', 'male', '1985-08-17', 'married', 'A+', 'none',
           0, 0, 1, 'college', 'Accountant', 35000.00, '09170000009', 'miguel.torres@example.com', 'active',
           '007', 'Purok 1' UNION ALL
    SELECT 'R-2024-010', 'Elena', 'Reyes', 'Garcia', '', 'female', '1978-02-28', 'married', 'B+', 'none',
           0, 0, 1, 'college', 'Teacher', 28000.00, '09170000010', 'elena.garcia@example.com', 'active',
           '008', 'Purok 2'
) r
JOIN households hh ON hh.house_number = r.hh_number
JOIN puroks p    ON p.id = hh.purok_id AND p.name = r.purok_name
WHERE NOT EXISTS (
    SELECT 1 FROM residents ex WHERE ex.national_id = r.national_id
);

-- Resident links (head-spouse/child relationships for first two households)
INSERT IGNORE INTO resident_links (resident_id_a, resident_id_b, relationship)
SELECT a.id, b.id, link.relationship
FROM (
    SELECT id, first_name, last_name FROM residents WHERE national_id = 'R-2024-001' LIMIT 1
) a
JOIN (
    SELECT id, first_name, last_name FROM residents WHERE national_id = 'R-2024-002' LIMIT 1
) b
CROSS JOIN (SELECT 'spouse' relationship) link;

INSERT IGNORE INTO resident_links (resident_id_a, resident_id_b, relationship)
SELECT a.id, b.id, 'parent'
FROM (
    SELECT id FROM residents WHERE national_id = 'R-2024-001' LIMIT 1
) a
JOIN (
    SELECT id FROM residents WHERE national_id = 'R-2024-003' LIMIT 1
) b;

-- =====================================================
-- Chart of Accounts (finance/budget module lookups)
-- =====================================================
INSERT IGNORE INTO chart_of_accounts (code, name, type, is_active) VALUES
('1010', 'Cash on Hand', 'asset', 1),
('1020', 'Cash in Bank', 'asset', 1),
('2010', 'Accounts Payable', 'liability', 1),
('3010', 'General Fund Balance', 'equity', 1),
('4010', 'Real Property Tax', 'income', 1),
('4020', 'Business Tax', 'income', 1),
('4021', 'Revenue - Barangay Clearance', 'income', 1),
('4022', 'Revenue - Business Clearance', 'income', 1),
('4030', 'Donations and Contributions', 'income', 1),
('5010', 'Personnel Services', 'expense', 1),
('5020', 'Maintenance and Other Operating Expenses (MOOE)', 'expense', 1),
('5030', 'Capital Outlay', 'expense', 1),
('5040', 'Financial Expenses', 'expense', 1);

-- =====================================================
-- Appointment slots (next 7 days, morning + afternoon)
-- =====================================================
INSERT IGNORE INTO appointment_slots (slot_date, time_start, time_end, max_capacity, slot_type)
SELECT CURDATE() + INTERVAL d.a DAY, '09:00:00', '12:00:00', 10, 'regular'
FROM (
    SELECT 0 a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
    UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
) d
WHERE NOT EXISTS (
    SELECT 1 FROM appointment_slots s
    WHERE s.slot_date = CURDATE() + INTERVAL d.a DAY
      AND s.time_start = '09:00:00'
    LIMIT 1
);

INSERT IGNORE INTO appointment_slots (slot_date, time_start, time_end, max_capacity, slot_type)
SELECT CURDATE() + INTERVAL d.a DAY, '13:00:00', '17:00:00', 10, 'regular'
FROM (
    SELECT 0 a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
    UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
) d
WHERE NOT EXISTS (
    SELECT 1 FROM appointment_slots s
    WHERE s.slot_date = CURDATE() + INTERVAL d.a DAY
      AND s.time_start = '13:00:00'
    LIMIT 1
);

-- =====================================================
-- Health programs
-- =====================================================
INSERT IGNORE INTO health_programs (name, target_group, schedule_date, attendees_count, conducted_by, notes)
SELECT h.name, h.target_group, h.schedule_date, 0, h.conducted_by, h.notes
FROM (
    SELECT 'Dengue Awareness Campaign' name, 'All residents' target_group,
           CURDATE() + INTERVAL 10 DAY schedule_date, 'RHU - Barangay Health Station' conducted_by,
           'Community clean-up and awareness drive.' notes UNION ALL
    SELECT 'Free Blood Pressure Clinic', 'Senior citizens',
           CURDATE() + INTERVAL 15 DAY, 'Barangay Health Workers',
           'Free BP monitoring and consultation.' UNION ALL
    SELECT 'Child Immunization Day', 'Children 0-5 years',
           CURDATE() + INTERVAL 20 DAY, 'RHU - Manila',
           'Routine immunization for infants and children.' UNION ALL
    SELECT 'Maternal Health Check-up', 'Pregnant women',
           CURDATE() + INTERVAL 25 DAY, 'RHU - Manila',
           'Prenatal check-up and counselling.'
) h
WHERE NOT EXISTS (SELECT 1 FROM health_programs hp WHERE hp.name = h.name LIMIT 1);

-- =====================================================
-- Notification templates
-- =====================================================
INSERT IGNORE INTO notification_templates (name, template_text, variables)
SELECT t.name, t.template_text, t.variables
FROM (
    SELECT 'Emergency Alert' name,
           'ATTENTION: Emergency in Barangay. {{message}}' template_text,
           '["message"]' variables UNION ALL
    SELECT 'General Announcement',
           'Notice: {{subject}} - {{message}}',
           '["subject","message"]' UNION ALL
    SELECT 'Assembly Reminder',
           'Reminder: Zone assembly on {{date}} at {{time}}. Venue: {{venue}}.',
           '["date","time","venue"]' UNION ALL
    SELECT 'Community Cleanup',
           'Community cleanup this {{date}}. Please participate. Meet at {{venue}}.',
           '["date","venue"]'
) t
WHERE NOT EXISTS (SELECT 1 FROM notification_templates nt WHERE nt.name = t.name LIMIT 1);

-- =====================================================
-- Lupon members (chair, secretary, 2 members)
-- =====================================================
INSERT IGNORE INTO lupon_members (resident_id, position, appointed_date, status)
SELECT r.id, l.position, CURDATE(), 'active'
FROM (
    SELECT 'R-2024-001' national_id, 'chair' position UNION ALL
    SELECT 'R-2024-002', 'secretary' UNION ALL
    SELECT 'R-2024-004', 'member' UNION ALL
    SELECT 'R-2024-007', 'member'
) l
JOIN residents r ON r.national_id = l.national_id
WHERE NOT EXISTS (
    SELECT 1 FROM lupon_members lm WHERE lm.resident_id = r.id AND lm.position = l.position
);

SET FOREIGN_KEY_CHECKS = 1;
