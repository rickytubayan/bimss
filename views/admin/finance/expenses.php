<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Expense Records</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> record(s) — Total <?= format_currency($total) ?></p>
    </div>
    <a href="<?= admin_url('finance') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Finance</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-danger"></i>Record Expense</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('finance/expenses/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="expense_date">Date <span class="text-danger">*</span></label>
                        <input type="date" id="expense_date" name="expense_date" class="form-control" value="<?= e(old('expense_date', date('Y-m-d'))) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="payee">Payee <span class="text-danger">*</span></label>
                        <input type="text" id="payee" name="payee" class="form-control" placeholder="e.g. ABC Office Supply" value="<?= e(old('payee')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" value="<?= e(old('amount')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purpose">Purpose</label>
                        <textarea id="purpose" name="purpose" class="form-control" rows="2" placeholder="What was this for?"><?= e(old('purpose')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="or_number">OR Number</label>
                        <input type="text" id="or_number" name="or_number" class="form-control" value="<?= e(old('or_number')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="dv_number">DV Number</label>
                        <input type="text" id="dv_number" name="dv_number" class="form-control" value="<?= e(old('dv_number')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="fund_type">Fund Type</label>
                        <select id="fund_type" name="fund_type" class="form-select">
                            <?php foreach (['general', 'development', 'sk', 'drrm', 'gad'] as $ft): ?>
                                <option value="<?= $ft ?>" <?= (old('fund_type') ?: 'general') === $ft ? 'selected' : '' ?>><?= strtoupper($ft) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-check-lg me-1"></i>Save Expense</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-list-ul me-2 text-primary"></i>All Expense Records</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Payee</th>
                            <th>Purpose</th>
                            <th>Fund</th>
                            <th>OR #</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td class="text-muted small"><?= format_date($r['expense_date']) ?></td>
                                <td class="fw-semibold"><?= e($r['payee']) ?></td>
                                <td class="text-muted"><?= e($r['purpose'] ?? '—') ?></td>
                                <td><span class="text-capitalize small"><?= e($r['fund_type']) ?></span></td>
                                <td class="text-muted"><?= e($r['or_number'] ?? '—') ?></td>
                                <td class="text-end fw-semibold text-danger"><?= format_currency($r['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No expense records yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>