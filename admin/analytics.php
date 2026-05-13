<?php
declare(strict_types=1);

$_GET['page'] = 'analytics';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$adminPath = '/admin/analytics.php';
if (substr($scriptName, -strlen($adminPath)) === $adminPath) {
    $_SERVER['SCRIPT_NAME'] = substr($scriptName, 0, -strlen($adminPath)) . '/public/index.php';
}

require __DIR__ . '/../public/index.php';
