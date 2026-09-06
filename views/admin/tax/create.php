<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Add Tax Entry</h1>
        <p class="page-subtitle mb-0">Create a new entry in the tax ledger</p>
    </div>
    <a href="<?= admin_url('tax') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tax Ledger</a>
</div>

<form method="POST" action="<?= admin_url('tax/store') ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center"><i class="bi bi-bank me-2 text-primary"></i>Tax Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="taxpayer_name">Taxpayer Name <span class="text-danger">*</span></label>
                    <input type="text" id="taxpayer_name" name="taxpayer_name" class="form-control" value="<?= e(old('taxpayer_name')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tax_type">Tax Type <span class="text-danger">*</span></label>
                    <select id="tax_type" name="tax_type" class="form-select" required>
                        <option value="">Select type...</option>
                        <option value="rpt" <?= old('tax_type') === 'rpt' ? 'selected' : '' ?>>Real Property Tax</option>
                        <option value="business" <?= old('tax_type') === 'business' ? 'selected' : '' ?>>Business Tax</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="due_date">Due Date <span class="text-danger">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?= e(old('due_date') ?: date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="assessed_value">Assessed Value</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" id="assessed_value" name="assessed_value" class="form-control" step="0.01" min="0" value="<?= e(old('assessed_value')) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="amount_due">Amount Due <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" id="amount_due" name="amount_due" class="form-control" step="0.01" min="0.01" value="<?= e(old('amount_due')) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="penalties">Penalties</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" id="penalties" name="penalties" class="form-control" step="0.01" min="0" value="<?= e(old('penalties', 0)) ?>">
                    </div>
                </div>
            </div>
            <p class="form-text mt-3 mb-0">Overdue entries are automatically flagged as delinquent.</p>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Entry</button>
        <a href="<?= admin_url('tax') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>