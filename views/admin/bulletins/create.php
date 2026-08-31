<div class="page-header">
    <h1 class="page-title">Create Bulletin</h1>
    <p class="page-subtitle">Publish a new announcement to residents</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= admin_url('bulletins/store') ?>" novalidate>
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= e(old('title')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="content">Content <span class="text-danger">*</span></label>
                        <textarea id="content" name="content" class="form-control" rows="6" required><?= e(old('content')) ?></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="category">Category</label>
                            <select id="category" name="category" class="form-select">
                                <?php foreach (['general','health','safety','event','emergency','job'] as $cat): ?>
                                    <option value="<?= $cat ?>" <?= (old('category') ?: 'general') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="published_at">Publish Date</label>
                            <input type="datetime-local" id="published_at" name="published_at" class="form-control" value="<?= e(old('published_at')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="expires_at">Expires At</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control" value="<?= e(old('expires_at')) ?>">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1" <?= old('is_pinned') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_pinned">Pin this bulletin to the top</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Publish</button>
                        <a href="<?= admin_url('bulletins') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
