<?php
$r = $resident ?? [];
function rf($key) { return old($key, $r[$key] ?? ''); }
?>
<form method="POST" action="<?= $formAction ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i>Personal Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= e(rf('first_name')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" class="form-control" value="<?= e(rf('middle_name')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= e(rf('last_name')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="suffix">Suffix</label>
                    <select id="suffix" name="suffix" class="form-select">
                        <option value="">None</option>
                        <?php foreach (['Jr.', 'Sr.', 'II', 'III', 'IV'] as $s): ?>
                            <option value="<?= $s ?>" <?= rf('suffix') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="sex">Sex <span class="text-danger">*</span></label>
                    <select id="sex" name="sex" class="form-select" required>
                        <option value="">Select</option>
                        <option value="male" <?= rf('sex') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= rf('sex') === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="birthdate">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?= e(rf('birthdate')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="civil_status">Civil Status</label>
                    <select id="civil_status" name="civil_status" class="form-select">
                        <?php foreach (['single', 'married', 'widowed', 'separated', 'divorced'] as $cs): ?>
                            <option value="<?= $cs ?>" <?= rf('civil_status') === $cs ? 'selected' : '' ?>><?= ucfirst($cs) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="blood_type">Blood Type</label>
                    <select id="blood_type" name="blood_type" class="form-select">
                        <option value="">Unknown</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt): ?>
                            <option value="<?= $bt ?>" <?= rf('blood_type') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="national_id">National ID</label>
                    <input type="text" id="national_id" name="national_id" class="form-control" value="<?= e(rf('national_id')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= e(rf('phone')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= e(rf('email')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex align-items-center"><i class="bi bi-house me-2 text-primary"></i>Residence</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="purok_id">Purok / Sitio</label>
                    <select id="purok_id" name="purok_id" class="form-select">
                        <option value="">Select</option>
                        <?php foreach ($puroks as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (int)rf('purok_id') === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="household_id">Household</label>
                    <select id="household_id" name="household_id" class="form-select">
                        <option value="">Select</option>
                        <?php foreach ($households as $h): ?>
                            <option value="<?= $h['id'] ?>" <?= (int)rf('household_id') === (int)$h['id'] ? 'selected' : '' ?>>
                                <?= e(trim(($h['street'] ?? '') . ' ' . ($h['house_number'] ?? ''))) ?: ('Household #' . $h['id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex align-items-center"><i class="bi bi-clipboard-pulse me-2 text-primary"></i>Demographics & Flags</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="disability_type">Disability Type</label>
                    <select id="disability_type" name="disability_type" class="form-select">
                        <?php foreach (['none', 'visual', 'hearing', 'motor', 'speech', 'intellectual', 'psychosocial', 'multiple'] as $dt): ?>
                            <option value="<?= $dt ?>" <?= rf('disability_type') === $dt ? 'selected' : '' ?>><?= ucfirst($dt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="educational_attainment">Education</label>
                    <select id="educational_attainment" name="educational_attainment" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (['none', 'elementary', 'high_school', 'vocational', 'college', 'post_graduate'] as $ed): ?>
                            <option value="<?= $ed ?>" <?= rf('educational_attainment') === $ed ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $ed)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" class="form-control" value="<?= e(rf('occupation')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="monthly_income">Monthly Income (₱)</label>
                    <input type="number" id="monthly_income" name="monthly_income" class="form-control" step="0.01" min="0" value="<?= e(rf('monthly_income')) ?>">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-4 mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_pwd" name="is_pwd" value="1" <?= rf('is_pwd') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_pwd">PWD (Person with Disability)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_senior" name="is_senior" value="1" <?= rf('is_senior') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_senior">Senior Citizen</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_voter" name="is_voter" value="1" <?= rf('is_voter') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_voter">Registered Voter</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1" <?= rf('is_approved') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_approved">Approved</label>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach (['active', 'inactive', 'moved_out', 'deceased'] as $st): ?>
                            <option value="<?= $st ?>" <?= (rf('status') ?: 'active') === $st ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $submitLabel ?></button>
        <a href="<?= admin_url('residents') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>
