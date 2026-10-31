<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="page-title">Relief Inventory</h1>
        <p class="page-subtitle mb-0"><?= number_format($stats['items']) ?> item<?= $stats['items'] === 1 ? '' : 's' ?> · <?= number_format($stats['available_units']) ?> units available</p>
    </div>
    <a href="<?= admin_url('drrm') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to DRRM</a>
</div>

<div class="stats-grid">
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon blue d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Item Types"><?= $stats['items'] ?></div>
            <div class="stat-label">Item Types</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon green d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Units Available"><?= number_format($stats['available_units']) ?></div>
            <div class="stat-label">Units Available</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon yellow d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Units Distributed"><?= number_format($stats['distributions']) ?></div>
            <div class="stat-label">Units Distributed</div>
        </div>
    </div>
    <div class="stat-card d-flex align-items-center gap-3">
        <div class="stat-icon red d-flex align-items-center justify-content-center" aria-hidden="true"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-value" data-read-aloud="Recipients"><?= number_format($stats['recipients']) ?></div>
            <div class="stat-label">Unique Recipients</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-box-seam me-2 text-primary"></i>Inventory</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Item
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i): ?>
                            <?php
                            $stBadge = match ($i['status']) {
                                'available' => 'badge-green',
                                'depleted' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($i['item_name']) ?></td>
                                <td class="text-muted small text-capitalize"><?= e($i['category']) ?></td>
                                <td class="text-end fw-semibold"><?= (int)$i['quantity'] ?></td>
                                <td class="text-muted small"><?= e($i['unit'] ?? '—') ?></td>
                                <td class="text-muted small"><?= e($i['storage_location'] ?? '—') ?></td>
                                <td><span class="badge rounded-pill <?= $stBadge ?>"><?= ucfirst($i['status']) ?></span></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editInventory<?= (int)$i['id'] ?>" title="Edit item">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= admin_url('drrm/relief/inventory/' . (int)$i['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this inventory item? This cannot be undone.');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete item"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No relief items in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-arrow-up-right me-2 text-primary"></i>Distributions</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDistributionModal" <?= empty($items) ? 'disabled title="Add an inventory item first"' : '' ?>>
                    <i class="bi bi-plus-lg me-1"></i>Record Distribution
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th>Recipient</th>
                            <th>Event</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($distributions as $d): ?>
                            <tr>
                                <td class="text-muted small text-nowrap"><?= format_date($d['distributed_at'], 'M j, Y g:i A') ?></td>
                                <td class="fw-semibold small"><?= e($d['item_name']) ?></td>
                                <td class="text-end"><?= (int)$d['quantity'] ?></td>
                                <td class="text-muted small"><?= e($d['resident_name']) ?></td>
                                <td class="text-muted small"><?= e($d['event_name']) ?></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDistribution<?= (int)$d['id'] ?>" title="Edit distribution">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= admin_url('drrm/relief/distributions/' . (int)$d['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this distribution record? The stock will be restored.');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete distribution"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($distributions)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No distributions recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= admin_url('drrm/relief/inventory/store') ?>">
                <?= CSRF::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addInventoryModalLabel"><i class="bi bi-box-seam me-2 text-primary"></i>Add Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_item_name">Item Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_item_name" name="item_name" class="form-control" maxlength="150" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_category">Category <span class="text-danger">*</span></label>
                            <select id="add_category" name="category" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>"><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_quantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="add_quantity" name="quantity" class="form-control" min="0" step="1" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_unit">Unit</label>
                            <input type="text" id="add_unit" name="unit" class="form-control" maxlength="30" placeholder="e.g. packs, boxes, pcs">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_expiry_date">Expiry Date</label>
                            <input type="date" id="add_expiry_date" name="expiry_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="add_storage_location">Storage Location</label>
                            <input type="text" id="add_storage_location" name="storage_location" class="form-control" maxlength="200">
                        </div>
                    </div>
                    <div class="form-text mt-2">Status is set automatically: <strong>Depleted</strong> at 0 quantity, <strong>Expired</strong> past the expiry date, otherwise <strong>Available</strong>.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($items as $i): ?>
    <!-- Edit Inventory Modal -->
    <div class="modal fade" id="editInventory<?= (int)$i['id'] ?>" tabindex="-1" aria-labelledby="editInventoryLabel<?= (int)$i['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?= admin_url('drrm/relief/inventory/' . (int)$i['id'] . '/update') ?>">
                    <?= CSRF::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editInventoryLabel<?= (int)$i['id'] ?>"><i class="bi bi-pencil me-2 text-primary"></i>Edit Item &mdash; <?= e($i['item_name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="item_name_<?= (int)$i['id'] ?>">Item Name <span class="text-danger">*</span></label>
                                <input type="text" id="item_name_<?= (int)$i['id'] ?>" name="item_name" class="form-control" maxlength="150" value="<?= e($i['item_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_<?= (int)$i['id'] ?>">Category <span class="text-danger">*</span></label>
                                <select id="category_<?= (int)$i['id'] ?>" name="category" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= e($cat) ?>" <?= $i['category'] === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="quantity_<?= (int)$i['id'] ?>">Quantity <span class="text-danger">*</span></label>
                                <input type="number" id="quantity_<?= (int)$i['id'] ?>" name="quantity" class="form-control" min="0" step="1" value="<?= (int)$i['quantity'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="unit_<?= (int)$i['id'] ?>">Unit</label>
                                <input type="text" id="unit_<?= (int)$i['id'] ?>" name="unit" class="form-control" maxlength="30" value="<?= e($i['unit'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="expiry_date_<?= (int)$i['id'] ?>">Expiry Date</label>
                                <input type="date" id="expiry_date_<?= (int)$i['id'] ?>" name="expiry_date" class="form-control" value="<?= e($i['expiry_date'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="storage_location_<?= (int)$i['id'] ?>">Storage Location</label>
                                <input type="text" id="storage_location_<?= (int)$i['id'] ?>" name="storage_location" class="form-control" maxlength="200" value="<?= e($i['storage_location'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-text mt-2">Status is set automatically: <strong>Depleted</strong> at 0 quantity, <strong>Expired</strong> past the expiry date, otherwise <strong>Available</strong>.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
$inventoryOptions = array_map(static function ($item) {
    $unit = $item['unit'] ?: 'unit';
    return [
        'id' => (int)$item['id'],
        'label' => $item['item_name'] . ' (' . (int)$item['quantity'] . ' ' . $unit . ' in stock)',
        'status' => $item['status'],
    ];
}, $items);
$eventOptions = array_map(static fn($event) => ['id' => (int)$event['id'], 'label' => $event['name']], $events);
$residentOptions = array_map(static fn($resident) => ['id' => (int)$resident['id'], 'label' => $resident['label']], $residents);
?>

<!-- Add Distribution Modal -->
<div class="modal fade" id="addDistributionModal" tabindex="-1" aria-labelledby="addDistributionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= admin_url('drrm/relief/distributions/store') ?>">
                <?= CSRF::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addDistributionModalLabel"><i class="bi bi-arrow-up-right me-2 text-primary"></i>Record Distribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_inventory_id">Item <span class="text-danger">*</span></label>
                            <select id="add_inventory_id" name="inventory_id" class="form-select" required>
                                <option value="">Select item...</option>
                                <?php foreach ($inventoryOptions as $opt): ?>
                                    <option value="<?= $opt['id'] ?>"><?= e($opt['label']) ?><?= $opt['status'] !== 'available' ? ' — ' . ucfirst($opt['status']) : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_quantity_dist">Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="add_quantity_dist" name="quantity" class="form-control" min="1" step="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_resident_id">Recipient <span class="text-danger">*</span></label>
                            <select id="add_resident_id" name="resident_id" class="form-select" required>
                                <option value="">Select resident...</option>
                                <?php foreach ($residentOptions as $opt): ?>
                                    <option value="<?= $opt['id'] ?>"><?= e($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_disaster_event_id">Disaster Event <span class="text-danger">*</span></label>
                            <select id="add_disaster_event_id" name="disaster_event_id" class="form-select" required>
                                <option value="">Select event...</option>
                                <?php foreach ($eventOptions as $opt): ?>
                                    <option value="<?= $opt['id'] ?>"><?= e($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_distributed_at">Distributed At</label>
                            <input type="datetime-local" id="add_distributed_at" name="distributed_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_distributed_by">Distributed By</label>
                            <input type="text" id="add_distributed_by" name="distributed_by" class="form-control" maxlength="100" value="<?= e($currentUserName ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_or_number">OR Number</label>
                            <input type="text" id="add_or_number" name="or_number" class="form-control" maxlength="30">
                        </div>
                    </div>
                    <div class="form-text mt-2">Recording a distribution deducts the quantity from the selected inventory item.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Record Distribution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($distributions as $d): ?>
    <?php
    $distItemAvailable = in_array((int)$d['inventory_id'], array_column($inventoryOptions, 'id'), true);
    $distResidentAvailable = in_array((int)$d['resident_id'], array_column($residentOptions, 'id'), true);
    $distEventAvailable = in_array((int)$d['disaster_event_id'], array_column($eventOptions, 'id'), true);
    ?>
    <!-- Edit Distribution Modal -->
    <div class="modal fade" id="editDistribution<?= (int)$d['id'] ?>" tabindex="-1" aria-labelledby="editDistributionLabel<?= (int)$d['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?= admin_url('drrm/relief/distributions/' . (int)$d['id'] . '/update') ?>">
                    <?= CSRF::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDistributionLabel<?= (int)$d['id'] ?>"><i class="bi bi-pencil me-2 text-primary"></i>Edit Distribution &mdash; <?= e($d['item_name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="inventory_id_<?= (int)$d['id'] ?>">Item <span class="text-danger">*</span></label>
                                <select id="inventory_id_<?= (int)$d['id'] ?>" name="inventory_id" class="form-select" required>
                                    <?php if (!$distItemAvailable): ?>
                                        <option value="<?= (int)$d['inventory_id'] ?>" selected><?= e($d['item_name']) ?> (current)</option>
                                    <?php endif; ?>
                                    <?php foreach ($inventoryOptions as $opt): ?>
                                        <option value="<?= $opt['id'] ?>" <?= (int)$d['inventory_id'] === $opt['id'] ? 'selected' : '' ?>><?= e($opt['label']) ?><?= $opt['status'] !== 'available' ? ' — ' . ucfirst($opt['status']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="quantity_dist_<?= (int)$d['id'] ?>">Quantity <span class="text-danger">*</span></label>
                                <input type="number" id="quantity_dist_<?= (int)$d['id'] ?>" name="quantity" class="form-control" min="1" step="1" value="<?= (int)$d['quantity'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="resident_id_<?= (int)$d['id'] ?>">Recipient <span class="text-danger">*</span></label>
                                <select id="resident_id_<?= (int)$d['id'] ?>" name="resident_id" class="form-select" required>
                                    <?php if (!$distResidentAvailable): ?>
                                        <option value="<?= (int)$d['resident_id'] ?>" selected><?= e($d['resident_name']) ?> (current)</option>
                                    <?php endif; ?>
                                    <?php foreach ($residentOptions as $opt): ?>
                                        <option value="<?= $opt['id'] ?>" <?= (int)$d['resident_id'] === $opt['id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="disaster_event_id_<?= (int)$d['id'] ?>">Disaster Event <span class="text-danger">*</span></label>
                                <select id="disaster_event_id_<?= (int)$d['id'] ?>" name="disaster_event_id" class="form-select" required>
                                    <?php if (!$distEventAvailable): ?>
                                        <option value="<?= (int)$d['disaster_event_id'] ?>" selected><?= e($d['event_name']) ?> (current)</option>
                                    <?php endif; ?>
                                    <?php foreach ($eventOptions as $opt): ?>
                                        <option value="<?= $opt['id'] ?>" <?= (int)$d['disaster_event_id'] === $opt['id'] ? 'selected' : '' ?>><?= e($opt['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="distributed_at_<?= (int)$d['id'] ?>">Distributed At</label>
                                <input type="datetime-local" id="distributed_at_<?= (int)$d['id'] ?>" name="distributed_at" class="form-control" value="<?= e(date('Y-m-d\TH:i', strtotime($d['distributed_at']))) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="distributed_by_<?= (int)$d['id'] ?>">Distributed By</label>
                                <input type="text" id="distributed_by_<?= (int)$d['id'] ?>" name="distributed_by" class="form-control" maxlength="100" value="<?= e($d['distributed_by'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="or_number_<?= (int)$d['id'] ?>">OR Number</label>
                                <input type="text" id="or_number_<?= (int)$d['id'] ?>" name="or_number" class="form-control" maxlength="30" value="<?= e($d['or_number'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-text mt-2">Changing the item or quantity automatically reconciles the affected stock levels.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
