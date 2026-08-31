<div class="page-header">
    <h1 class="page-title">Edit Resident</h1>
    <p class="page-subtitle">Update resident information</p>
</div>
<?php
$formAction = admin_url('residents/' . $resident['id'] . '/update');
$submitLabel = 'Update Resident';
include __DIR__ . '/_form.php';
?>
