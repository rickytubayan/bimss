<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Create Budget</h1>
        <p class="page-subtitle mb-0">Set the budget header and its line items</p>
    </div>
    <a href="<?= admin_url('budget') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Budget</a>
</div>

<form method="POST" action="<?= admin_url('budget/store') ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex align-items-center"><i class="bi bi-clipboard-data me-2 text-primary"></i>Budget Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                    <select id="fiscal_year" name="fiscal_year" class="form-select">
                        <?php for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++): ?>
                            <option value="<?= $y ?>" <?= ((int)old('fiscal_year', $currentYear)) === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="fund_type">Fund Type <span class="text-danger">*</span></label>
                    <select id="fund_type" name="fund_type" class="form-select">
                        <?php foreach (['general', 'development', 'sk', 'drrm', 'gad'] as $ft): ?>
                            <option value="<?= $ft ?>" <?= (old('fund_type') ?: 'general') === $ft ? 'selected' : '' ?>><?= strtoupper($ft) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="total_amount">Total Budget Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" id="total_amount" name="total_amount" class="form-control" step="0.01" min="0.01" value="<?= e(old('total_amount')) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach (['draft', 'approved', 'active', 'closed'] as $st): ?>
                            <option value="<?= $st ?>" <?= (old('status') ?: 'draft') === $st ? 'selected' : '' ?>><?= ucwords($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2 text-primary"></i>Line Items</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLineItem"><i class="bi bi-plus-lg me-1"></i>Add Line Item</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="lineItemsTable">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Allocated Amount</th>
                            <th>Description</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="line-item">
                            <td style="min-width:220px;">
                                <select name="account_id[]" class="form-select">
                                    <option value="">Select account...</option>
                                    <?php foreach ($accounts as $acct): ?>
                                        <option value="<?= $acct['id'] ?>"><?= e($acct['code'] . ' - ' . $acct['name'] . ' (' . $acct['type'] . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="min-width:140px;">
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="allocated_amount[]" class="form-control allocated" step="0.01" min="0.01" placeholder="0.00">
                                </div>
                            </td>
                            <td>
                                <input type="text" name="description[]" class="form-control" placeholder="Optional note">
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Budget</button>
        <a href="<?= admin_url('budget') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>

<script>
(function () {
    const table = document.getElementById('lineItemsTable');
    const tbody = table.querySelector('tbody');
    const template = tbody.querySelector('.line-item');

    document.getElementById('addLineItem').addEventListener('click', function () {
        const clone = template.cloneNode(true);
        clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        clone.querySelector('select').selectedIndex = 0;
        tbody.appendChild(clone);
    });

    tbody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-line')) {
            const rows = tbody.querySelectorAll('.line-item');
            if (rows.length > 1) {
                e.target.closest('.line-item').remove();
            }
        }
    });
})();
</script>