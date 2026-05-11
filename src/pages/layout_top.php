<!doctype html>
<html lang="sq">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <nav><a>Ndihme</a><a>FAQ</a><a>Vegzat</a><a>Webmail</a></nav>
        <nav><span>Gjuha:</span><a>Shqi</a><a>Eng</a><a>Srb</a></nav>
    </div>
</header>
<header class="main-header">
    <div class="wrap header-inner">
        <a class="brand brand-logo" href="<?= BASE_URL ?>/index.php?page=home" aria-label="EKosova+">
            <span class="brand-icon" aria-hidden="true">
                <svg class="brand-icon-svg" viewBox="0 0 78 78" role="img" focusable="false">
                    <circle cx="28" cy="39" r="25"></circle>
                    <circle cx="50" cy="18" r="17"></circle>
                    <circle cx="50" cy="60" r="17"></circle>
                    <text x="24" y="49">e</text>
                </svg>
            </span>
            <span class="brand-text"><span>KOSOVA</span><strong>+</strong></span>
        </a>
        <nav class="main-nav" aria-label="Navigimi kryesor">
            <a href="<?= BASE_URL ?>/index.php?page=home">Kryesore</a>
            <a href="<?= BASE_URL ?>/index.php?page=services">Shërbime</a>
            <?php if (($page ?? '') === 'education' || ($page ?? '') === 'scholarships'): ?>
                <a href="<?= BASE_URL ?>/index.php?page=education">Arsimi</a>
            <?php endif; ?>
            <?php if (($page ?? '') === 'scholarships'): ?>
                <a href="<?= BASE_URL ?>/index.php?page=scholarships">Bursat</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?page=home">Informatat</a>
            <?php endif; ?>
            <?php if (current_user()): ?>
                <a class="notification-pill" href="<?= BASE_URL ?>/index.php?page=home" aria-label="Njoftimet"><span>🔔</span><b>0</b></a>
                <div class="user-menu">
                    <button class="user-pill" type="button" id="userMenuButton" aria-expanded="false">
                        <span class="user-icon">♙</span><?= e(current_user()['name']) ?> <span>⌄</span>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?= BASE_URL ?>/index.php?page=profile">Te dhenat e mia</a>
                        <a href="<?= BASE_URL ?>/index.php?page=dashboard">Paneli</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="logout">
                            <button>Dil</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="wrap content">
    <?php if (!empty($flash)): ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
