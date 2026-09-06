<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Tax Ledger</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($rows)) ?> tax entr<?= count($rows) === 1 ? 'y' : 'ies' ?></p>
    </div>
    <a href="<?= admin_url('tax/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Tax Entry</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-buildings"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Assessed Value"><?= format_currency($totalAssessed) ?></div>
            <div class="stat-label">Assessed Value</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Total Due"><?= format_currency($totalDue) ?></div>
            <div class="stat-label">Total Due</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check2-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Collected"><?= format_currency($totalPaid) ?></div>
            <div class="stat-label">Collected</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-exclamation-octagon"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Delinquent"><?= $delinquentCount ?></div>
            <div class="stat-label">Delinquent Accounts</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Taxpayer</th>
                    <th>Type</th>
                    <th>Assessed Value</th>
                    <th>Amount Due</th>
                    <th>Penalties</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $stBadge = match ($r['status']) {
                        'paid' => 'badge-green',
                        'partial' => 'badge-yellow',
                        'delinquent' => 'badge-red',
                        default => 'badge-gray',
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['taxpayer_name']) ?></td>
                        <td><span class="text-capitalize"><?= e($r['tax_type']) === 'rpt' ? 'Real Property' : 'Business' ?></span></td>
                        <td><?= format_currency($r['assessed_value']) ?></td>
                        <td><?= format_currency($r['amount_due']) ?></td>
                        <td><?= e($r['penalties'] > 0 ? format_currency($r['penalties']) : '—') ?></td>
                        <td class="text-success"><?= format_currency($r['amount_paid']) ?></td>
                        <td class="fw-semibold <?= $r['balance'] > 0 ? 'text-danger' : 'text-success' ?>"><?= format_currency($r['balance']) ?></td>
                        <td>
                            <?= format_date($r['due_date']) ?>
                            <?php if ($r['status'] === 'delinquent'): ?>
                                <span class="badge rounded-pill badge-red">OVERDUE</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td class="text-end">
                            <?php if ($r['balance'] > 0.005): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary pay-btn" data-id="<?= $r['id'] ?>"
                                        data-name="<?= e($r['taxpayer_name']) ?>" data-balance="<?= e(format_currency($r['balance'])) ?>">
                                    <i class="bi bi-cash-coin me-1"></i>Record Payment
                                </button>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-check-lg me-1"></i>Settled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($r['balance'] > 0.005): ?>
                        <tr class="d-none pay-row" id="pay-row-<?= $r['id'] ?>">
                            <td colspan="10" class="bg-light-subtle">
                                <form method="POST" action="<?= admin_url('tax/payment/' . $r['id']) ?>" class="row g-2 align-items-end" novalidate>
                                    <?= CSRF::field() ?>
                                    <div class="col-auto">
                                        <label class="form-label small mb-1" for="amount-<?= $r['id'] ?>">Payment Amount</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" min="0.01" max="<?= e($r['balance']) ?>" name="amount" id="amount-<?= $r['id'] ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label small mb-1" for="pdate-<?= $r['id'] ?>">Payment Date</label>
                                        <input type="date" name="payment_date" id="pdate-<?= $r['id'] ?>" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label small mb-1" for="or-<?= $r['id'] ?>">OR Number</label>
                                        <input type="text" name="or_number" id="or-<?= $r['id'] ?>" class="form-control form-control-sm" placeholder="Optional">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check2-circle me-1"></i>Save Payment</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No tax entries yet. Add the first one.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.pay-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var row = document.getElementById('pay-row-' + id);
            if (row) {
                row.classList.toggle('d-none');
            }
        });
    });
})();
</script>