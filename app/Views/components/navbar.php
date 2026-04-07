<?php
$currentLang = $_SESSION['lang'] ?? 'ro';
$supportedLangs = [
    'ro' => '🇷🇴 RO',
    'en' => '🇬🇧 EN'
];
?>

<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="/">
            <span class="fs-4">⚖️</span> <?= translate('app.name', 'JustAlert') ?><span class="text-muted fw-normal">.eu</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
            <ul class="navbar-nav align-items-center gap-2 mt-3 mt-md-0">
                
                <li class="nav-item dropdown me-md-2 mt-2 mt-md-0">
                    <a class="nav-link dropdown-toggle fw-bold text-secondary border rounded-pill px-3 py-1" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $supportedLangs[$currentLang] ?? '🇷🇴 RO' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="languageDropdown">
                        <?php foreach ($supportedLangs as $code => $label): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 <?= $code === $currentLang ? 'active bg-primary text-white fw-bold' : '' ?>" href="/change-language?lang=<?= $code ?>">
                                    <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="/dashboard">
                            <?= translate('nav.dashboard', 'Panoul meu') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-md-2 mt-2 mt-md-0" href="/logout">
                            <?= translate('nav.logout', 'Deconectare') ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="/">
                            <?= translate('nav.home', 'Acasă') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-md-2 mt-2 mt-md-0" href="/login">
                            <?= translate('nav.login', 'Autentificare') ?>
                        </a>
                    </li>
                <?php endif; ?>
                
            </ul>
        </div>
    </div>
</nav>
