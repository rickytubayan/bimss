<div class="page-header">
    <h1 class="page-title">Edit Household</h1>
    <p class="page-subtitle">Update household information</p>
</div>
<?php
$formAction = admin_url('households/' . $household['id'] . '/update');
$submitLabel = 'Update Household';
include __DIR__ . '/_form.php';
?>
