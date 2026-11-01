<?php

declare(strict_types=1);

/**
 * Front Controller & Entrypoint (`index.php`).
 * Bootstraps PSR-4 style autoloading, loads environment configuration,
 * and dispatches multi-page GET views (`beranda`, `presiden`, `wakil-presiden`) and POST actions.
 */

spl_autoload_register(static function (string $className): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR;

    $prefixLength = strlen($prefix);
    if (strncmp($prefix, $className, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($className, $prefixLength);
    $filePath = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($filePath)) {
        require_once $filePath;
    }
});

use App\Controllers\LandingController;

$controller = new LandingController();
$action = isset($_GET['action']) ? trim((string) $_GET['action']) : '';
$page = isset($_GET['page']) ? trim((string) $_GET['page']) : 'beranda';
$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($action === 'register-citizen' && $requestMethod === 'POST') {
    $controller->registerCitizen($_POST);
    exit;
}

if ($action === 'login' && $requestMethod === 'POST') {
    $controller->handleLogin($_POST);
    exit;
}

if ($action === 'logout' && $requestMethod === 'POST') {
    $controller->handleLogout();
    exit;
}

if ($action === 'issue-presidential-act' && $requestMethod === 'POST') {
    $controller->handleIssuePresidentialAct($_POST);
    exit;
}

if ($action === 'issue-vice-presidential-act' && $requestMethod === 'POST') {
    $controller->handleIssueVicePresidentialAct($_POST);
    exit;
}

if ($action === 'update-cabinet-name' && $requestMethod === 'POST') {
    $controller->handleUpdateCabinetName($_POST);
    exit;
}

if ($action === 'appoint-minister' && $requestMethod === 'POST') {
    $controller->handleAppointMinister($_POST);
    exit;
}

if ($action === 'dismiss-minister' && $requestMethod === 'POST') {
    $controller->handleDismissMinister($_POST);
    exit;
}

if ($page === 'presiden') {
    $controller->presidentPage();
    exit;
}

if ($page === 'wakil-presiden') {
    $controller->vicePresidentPage();
    exit;
}

$controller->index();
