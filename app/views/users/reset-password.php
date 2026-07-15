<div class="row justify-content-center">
<div class="col-lg-5">
<div class="content-card">
    <div class="card-header">
        <i class="bi bi-key-fill me-2"></i>Reset Password —
        <?= Format::e($user['first_name'] . ' ' . $user['last_name']) ?>
    </div>
    <div class="p-4">
        <div class="alert alert-warning py-2 mb-4 small">
            <i class="bi bi-info-circle me-1"></i>
            The user will be required to change this password on their next login.
        </div>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-medium">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="new_password" class="form-control"
                           placeholder="Min. 8 characters" required minlength="8">
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePw('new_password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-key me-1"></i> Reset Password
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
function togglePw(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.innerHTML = el.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
}
</script>
