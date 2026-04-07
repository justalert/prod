<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold"><?= translate('auth.login.title') ?></h1>
            <p class="text-muted"><?= translate('auth.login.subtitle') ?></p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="/login/send" method="POST">
                    
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label"><?= translate('home.form.email.label') ?></label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="<?= translate('home.form.email.placeholder') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <?= translate('auth.login.submit') ?>
                    </button>

                </form>
            </div>
        </div>
        
    </div>
</div>
