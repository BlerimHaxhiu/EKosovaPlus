<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=2">
</head>
<body data-invalid-year-range="<?= e(t('invalid_year_range')) ?>">
<header class="topbar">
    <div class="wrap topbar-inner">
        <nav>
            <a href="<?= BASE_URL ?>/index.php?page=help"><?= e(t('help')) ?></a>
            <a class="placeholder" href="<?= BASE_URL ?>/index.php?page=home" data-placeholder="<?= e(t('faq_placeholder')) ?>"><?= e(t('faq')) ?></a>
            <a class="placeholder" href="<?= BASE_URL ?>/index.php?page=home" data-placeholder="<?= e(t('links_placeholder')) ?>"><?= e(t('links')) ?></a>
            <a class="placeholder" href="<?= BASE_URL ?>/index.php?page=home" data-placeholder="<?= e(t('webmail_placeholder')) ?>"><?= e(t('webmail')) ?></a>
        </nav>
        <nav class="language-switcher">
            <span><?= e(t('language')) ?></span>
            <a class="<?= current_lang() === 'sq' ? 'active' : '' ?>" href="<?= e(language_url('sq')) ?>">Shqi</a>
            <a class="<?= current_lang() === 'en' ? 'active' : '' ?>" href="<?= e(language_url('en')) ?>">Eng</a>
            <a class="<?= current_lang() === 'sr' ? 'active' : '' ?>" href="<?= e(language_url('sr')) ?>">Srb</a>
        </nav>
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
            <a class="<?= ($page ?? '') === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=home"><?= e(t('home')) ?></a>
            <a class="<?= in_array(($page ?? ''), ['services', 'education', 'scholarships'], true) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=services"><?= e(t('services')) ?></a>
            <?php if (($page ?? '') === 'education' || ($page ?? '') === 'scholarships'): ?>
                <a class="<?= ($page ?? '') === 'education' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=education"><?= e(t('education')) ?></a>
            <?php endif; ?>
            <?php if (($page ?? '') === 'scholarships'): ?>
                <a class="active" href="<?= BASE_URL ?>/index.php?page=scholarships"><?= e(t('scholarships')) ?></a>
            <?php else: ?>
                <a class="<?= ($page ?? '') === 'info' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?page=info"><?= e(t('information')) ?></a>
            <?php endif; ?>
            <?php if (($page ?? '') === 'help'): ?>
                <a class="active" href="<?= BASE_URL ?>/index.php?page=help"><?= e(t('help')) ?></a>
            <?php endif; ?>
            <?php if (current_user()): ?>
                <a class="notification-pill" href="<?= BASE_URL ?>/index.php?page=home" aria-label="<?= e(t('notifications')) ?>"><span>🔔</span><b>0</b></a>
                <div class="user-menu">
                    <button class="user-pill" type="button" id="userMenuButton" aria-expanded="false">
                        <span class="user-icon">♙</span><?= e(current_user()['name']) ?> <span>⌄</span>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?= BASE_URL ?>/index.php?page=profile"><?= e(t('my_details')) ?></a>
                        <a href="<?= BASE_URL ?>/index.php?page=dashboard"><?= e(t('dashboard')) ?></a>
                        <?php if (current_user()['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/index.php?page=analytics">Analitika</a>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="logout">
                            <button><?= e(t('logout')) ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="wrap content">
    <?php if (!empty($flash)): ?>
        <div class="flash <?= e($flash['type']) ?>" <?= !empty($flash['popup']) ? 'data-popup-message="' . e($flash['message']) . '"' : '' ?>><?= e($flash['message']) ?></div>
    <?php endif; ?>
