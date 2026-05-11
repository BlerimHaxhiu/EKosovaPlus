<?php
declare(strict_types=1);

function refresh_session_user(int $userId): void
{
    $stmt = db()->prepare('SELECT id, name, username, email, role, provider_type FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
    }
}

