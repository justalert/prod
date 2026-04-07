<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ro' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? translate('app.title', 'JustAlert.eu - Alerte Dosare Instanță')) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Reset fundamental pentru ecranele de mobil */
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
            /* 100dvh (Dynamic Viewport Height) repară bug-ul barei de URL de pe mobile */
            min-height: 100vh;
            min-height: 100dvh;
            background-color: #f8f9fa;
        }
        
        main {
            /* Forțează zona principală să împingă footer-ul exact la capătul conținutului */
            flex: 1 0 auto;
        }
        
        footer {
            /* Împiedică footer-ul să se micșoreze și să fie acoperit de conținut */
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <?php require BASE_PATH . 'Views/components/navbar.php'; ?>

    <div class="container mt-2">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </div>

    <main class="container my-4">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . 'Views/components/footer.php'; ?>

</body>
</html>
