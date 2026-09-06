<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Budget</h1>
        <p class="page-subtitle mb-0"><?= number_format(count($budgets)) ?> budget allocation(s)</p>
    </div>
    <a href="<?= admin_url('budget/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Budget</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fiscal Year</th>
                    <th>Fund Type</th>
                    <th>Allocated</th>
                    <th>Utilized</th>
                    <th>Balance</th>
                    <th>Line Items</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $b): ?>
                    <?php
                    $balance = (float)$b['total_allocated'] - (float)$b['total_utilized'];
                    $stBadge = match ($b['status']) {
                        'approved', 'active' => 'badge-green',
                        'draft' => 'badge-yellow',
                        'closed' => 'badge-gray',
                        default => 'badge-gray',
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= (int)$b['fiscal_year'] ?></td>
                        <td><span class="text-capitalize"><?= e($b['fund_type']) ?></span></td>
                        <td><?= format_currency($b['total_allocated']) ?></td>
                        <td class="text-danger"><?= format_currency($b['total_utilized']) ?></td>
                        <td class="text-success fw-semibold"><?= format_currency($balance) ?></td>
                        <td><?= (int)$b['line_count'] ?></td>
                        <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucwords($b['status']) ?></span></td>
                        <td class="text-end">
                            <a href="<?= admin_url('budget/' . $b['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($budgets)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No budgets yet. Create the first one.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>