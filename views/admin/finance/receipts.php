<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Official Receipts</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($records)) ?> receipt(s) — Total <?= format_currency($total) ?></p>
    </div>
    <a href="<?= admin_url('finance') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Finance</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex align-items-center"><i class="bi bi-plus-circle me-2 text-primary"></i>Generate Receipt</div>
            <div class="card-body">
                <form method="POST" action="<?= admin_url('finance/receipts/generate') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="receipt_date">Date <span class="text-danger">*</span></label>
                        <input type="date" id="receipt_date" name="receipt_date" class="form-control" value="<?= e(old('receipt_date', date('Y-m-d'))) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="payer">Payer <span class="text-danger">*</span></label>
                        <input type="text" id="payer" name="payer" class="form-control" placeholder="e.g. Juan Cruz" value="<?= e(old('payer')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" value="<?= e(old('amount')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purpose">Purpose</label>
                        <input type="text" id="purpose" name="purpose" class="form-control" placeholder="e.g. Barangay Clearance" value="<?= e(old('purpose')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="or_type">Receipt Type</label>
                        <select id="or_type" name="or_type" class="form-select">
                            <?php foreach (['income', 'refund'] as $ot): ?>
                                <option value="<?= $ot ?>" <?= (old('or_type') ?: 'income') === $ot ? 'selected' : '' ?>><?= ucwords($ot) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-receipt me-1"></i>Generate Receipt</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><i class="bi bi-list-ul me-2 text-primary"></i>All Receipts</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Payer</th>
                            <th>Purpose</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($r['sequence_no']) ?></td>
                                <td class="text-muted small"><?= format_date($r['receipt_date']) ?></td>
                                <td><?= e($r['payer']) ?></td>
                                <td class="text-muted"><?= e($r['purpose'] ?? '—') ?></td>
                                <td><span class="text-capitalize small"><?= e($r['or_type']) ?></span></td>
                                <td class="text-end fw-semibold text-success"><?= format_currency($r['amount']) ?></td>
                                <td>
                                    <?php if ((bool)$r['is_voided']): ?>
                                        <span class="badge rounded-pill badge-yellow">Voided</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill badge-green">Valid</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No receipts yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>