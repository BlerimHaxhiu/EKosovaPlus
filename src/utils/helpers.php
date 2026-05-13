<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars(normalize_ui_text((string) $value), ENT_QUOTES, 'UTF-8');
}

function normalize_ui_text(string $value): string
{
    $lower = chr(0xC3) . chr(0xAB);
    $upper = chr(0xC3) . chr(0x8B);
    $brokenLower = chr(0xC3) . chr(0x83) . chr(0xC2) . chr(0xAB);
    $brokenUpper = chr(0xC3) . chr(0x83) . chr(0xC2) . chr(0x8B);
    $brokenUpperAlt = chr(0xC3) . chr(0x83) . chr(0xE2) . chr(0x82) . chr(0xAC) . chr(0xCB) . chr(0x9C);
    $cLower = chr(0xC3) . chr(0xA7);
    $cUpper = chr(0xC3) . chr(0x87);
    $brokenCLower = chr(0xC3) . chr(0x83) . chr(0xC2) . chr(0xA7);
    $brokenCUpper = chr(0xC3) . chr(0x83) . chr(0xC2) . chr(0x87);

    return strtr($value, [
        $brokenLower => 'e',
        $brokenUpper => 'E',
        $brokenUpperAlt => 'E',
        $lower => 'e',
        $upper => 'E',
        $brokenCLower => 'c',
        $brokenCUpper => 'C',
        $cLower => 'c',
        $cUpper => 'C',
    ]);
}

function t(string $key): string
{
    global $lang;
    if (isset($lang[$key]) && is_string($lang[$key])) {
        return $lang[$key];
    }

    if (isset($lang['__legacy'][$key]) && is_string($lang['__legacy'][$key])) {
        return $lang['__legacy'][$key];
    }

    return $key;
}

function redirect(string $page): never
{
    header('Location: ' . BASE_URL . '/index.php?page=' . $page);
    exit;
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}

function popup_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type, 'popup' => true];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        flash(t('invalid_request'), 'error');
        redirect('login');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        flash(t('login_required'), 'error');
        redirect('login');
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        flash(t('access_denied'), 'error');
        redirect('home');
    }
}

function selected(string $value, ?string $current): string
{
    return $value === $current ? 'selected' : '';
}

function checked_bool(bool $condition): string
{
    return $condition ? 'checked' : '';
}
