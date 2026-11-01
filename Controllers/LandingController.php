<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\AppConfig;
use App\Services\AuthenticationService;
use App\Services\CabinetService;
use App\Services\CitizenshipValidationService;
use App\Services\ExecutiveActionService;
use App\Services\RbacService;

/**
 * Controller for the National Information Portal and Role-Exclusive Executive Pages (`Controllers/LandingController.php`).
 * Orchestrates multi-page rendering (`beranda`, `presiden`, `wakil-presiden`), cabinet management, and JSON action endpoints.
 */
final class LandingController
{
    /** @var RbacService Domain service for national portal and workspace data */
    private RbacService $rbacService;

    /** @var CitizenshipValidationService Custom validation service for citizen applications */
    private CitizenshipValidationService $validationService;

    /** @var AuthenticationService Custom authentication service for portal sign-in */
    private AuthenticationService $authService;

    /** @var ExecutiveActionService Custom validation & execution service for Presidential/VP acts */
    private ExecutiveActionService $executiveActionService;

    /** @var CabinetService Domain service for presidential cabinet administration & ministers */
    private CabinetService $cabinetService;

    /**
     * Initializes the controller with required domain services.
     *
     * @param RbacService|null $rbacService Optional injected service.
     * @param CitizenshipValidationService|null $validationService Optional injected validator.
     * @param AuthenticationService|null $authService Optional injected auth service.
     * @param ExecutiveActionService|null $executiveActionService Optional injected executive service.
     * @param CabinetService|null $cabinetService Optional injected cabinet service.
     */
    public function __construct(
        ?RbacService $rbacService = null,
        ?CitizenshipValidationService $validationService = null,
        ?AuthenticationService $authService = null,
        ?ExecutiveActionService $executiveActionService = null,
        ?CabinetService $cabinetService = null
    ) {
        $this->rbacService = $rbacService ?? new RbacService();
        $this->validationService = $validationService ?? new CitizenshipValidationService();
        $this->authService = $authService ?? new AuthenticationService();
        $this->executiveActionService = $executiveActionService ?? new ExecutiveActionService();
        $this->cabinetService = $cabinetService ?? new CabinetService();
    }

    /**
     * Renders the Main National Information Portal (`?page=beranda`).
     * Contains News, DPR Composition, Economy Indicators, Active Laws, and Political Parties.
     *
     * @return void
     */
    public function index(): void
    {
        $authUser = $this->authService->getCurrentUser();
        $payload = $this->rbacService->getLandingPagePayload();

        $viewData = [
            'pageTitle' => AppConfig::get('APP_NAME', 'Discord Indonesia Simulator') . ' — Portal Informasi Nasional',
            'activePage' => 'beranda',
            'appName' => AppConfig::get('APP_NAME', 'Discord Indonesia Simulator'),
            'simulationEra' => AppConfig::get('SIMULATION_ERA', 'Kabinet Indonesia Emas 2026'),
            'authUser' => $authUser,
            'telemetry' => $payload['telemetry'],
            'newsFeed' => $payload['news_feed'],
            'economyMetrics' => $payload['economy_metrics'],
            'politicalParties' => $payload['political_parties'],
            'dprSummary' => $payload['dpr_summary'],
            'activeLaws' => $payload['active_laws'],
            'gazetteFeed' => $payload['gazette_feed'],
            'executiveUsers' => $payload['executive_users'],
        ];

        $this->renderView('landing/index', $viewData);
    }

    /**
     * Renders the dedicated Presidential Workspace Page (`?page=presiden`).
     * Accessible ONLY when logged in as `ROLE_PRESIDEN`.
     *
     * @return void
     */
    public function presidentPage(): void
    {
        $authUser = $this->authService->getCurrentUser();
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
            header('Location: ?page=beranda');
            exit;
        }

        $payload = $this->rbacService->getPresidentWorkspacePayload();
        $cabinetData = $this->cabinetService->getCabinetWorkspaceData($authUser);
        $activeTab = isset($_GET['tab']) && in_array($_GET['tab'], ['regulasi', 'kabinet'], true)
            ? (string) $_GET['tab']
            : 'regulasi';

        $viewData = [
            'pageTitle' => 'Menu Presiden RI — ' . AppConfig::get('APP_NAME', 'Discord Indonesia Simulator'),
            'activePage' => 'presiden',
            'activeTab' => $activeTab,
            'appName' => AppConfig::get('APP_NAME', 'Discord Indonesia Simulator'),
            'simulationEra' => AppConfig::get('SIMULATION_ERA', 'Kabinet Indonesia Emas 2026'),
            'authUser' => $authUser,
            'telemetry' => $payload['telemetry'],
            'presidentPermissions' => $payload['president_permissions'],
            'presidentDecrees' => $payload['president_decrees'],
            'cabinetData' => $cabinetData,
        ];

        $this->renderView('president/index', $viewData);
    }

    /**
     * Renders the dedicated Vice-Presidential Workspace Page (`?page=wakil-presiden`).
     * Accessible ONLY when logged in as `ROLE_WAKIL_PRESIDEN`.
     *
     * @return void
     */
    public function vicePresidentPage(): void
    {
        $authUser = $this->authService->getCurrentUser();
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_WAKIL_PRESIDEN') {
            header('Location: ?page=beranda');
            exit;
        }

        $payload = $this->rbacService->getVicePresidentWorkspacePayload();

        $viewData = [
            'pageTitle' => 'Menu Wakil Presiden RI — ' . AppConfig::get('APP_NAME', 'Discord Indonesia Simulator'),
            'activePage' => 'wakil-presiden',
            'appName' => AppConfig::get('APP_NAME', 'Discord Indonesia Simulator'),
            'simulationEra' => AppConfig::get('SIMULATION_ERA', 'Kabinet Indonesia Emas 2026'),
            'authUser' => $authUser,
            'telemetry' => $payload['telemetry'],
            'vicePresidentPermissions' => $payload['vice_president_permissions'],
            'vicePresidentDecrees' => $payload['vice_president_decrees'],
        ];

        $this->renderView('vice-president/index', $viewData);
    }

    /**
     * Handles POST citizen registration requests (`?action=register-citizen`).
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function registerCitizen(array $postParams): void
    {
        $validationResult = $this->validationService->validateRegistrationPayload($postParams);

        if (!$validationResult['valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => 'Terdapat kolom pendaftaran yang belum sesuai ketentuan.',
                'errors' => $validationResult['errors'],
            ], 422);
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => 'Data kependudukan Anda berhasil dicatat dan E-KTP telah diterbitkan.',
            'citizen' => $validationResult['data'],
        ], 200);
    }

    /**
     * Handles POST login requests (`?action=login`).
     *
     * @param array<string, mixed> $postParams Submitted login form payload.
     * @return void
     */
    public function handleLogin(array $postParams): void
    {
        $authResult = $this->authService->authenticate($postParams);

        if (!$authResult['valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => 'Identitas pengguna atau kata sandi tidak sesuai.',
                'errors' => $authResult['errors'],
            ], 422);
            return;
        }

        $user = $authResult['user'];
        $roleCode = (string) ($user['role_code'] ?? '');
        $redirectUrl = '?page=beranda';

        if ($roleCode === 'ROLE_PRESIDEN') {
            $redirectUrl = '?page=presiden';
        } elseif ($roleCode === 'ROLE_WAKIL_PRESIDEN') {
            $redirectUrl = '?page=wakil-presiden';
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => 'Selamat datang kembali, ' . (string) ($user['character_name'] ?? '') . '.',
            'user' => $user,
            'redirect_url' => $redirectUrl,
        ], 200);
    }

    /**
     * Handles POST logout requests (`?action=logout`).
     *
     * @return void
     */
    public function handleLogout(): void
    {
        $this->authService->logout();

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => 'Anda telah keluar dari sesi pejabat negara.',
            'redirect_url' => '?page=beranda',
        ], 200);
    }

    /**
     * Handles POST Presidential Decree/Regulation submissions (`?action=issue-presidential-act`).
     * Restricted exclusively to `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function handleIssuePresidentialAct(array $postParams): void
    {
        $authUser = $this->authService->getCurrentUser();
        $result = $this->executiveActionService->issuePresidentialAct($authUser, $postParams);

        if (!$result['is_valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], (int) ($result['http_code'] ?? 422));
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => $result['message'],
        ], 200);
    }

    /**
     * Handles POST Vice-Presidential Instruction submissions (`?action=issue-vice-presidential-act`).
     * Restricted exclusively to `ROLE_WAKIL_PRESIDEN`.
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function handleIssueVicePresidentialAct(array $postParams): void
    {
        $authUser = $this->authService->getCurrentUser();
        $result = $this->executiveActionService->issueVicePresidentialAct($authUser, $postParams);

        if (!$result['is_valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], (int) ($result['http_code'] ?? 422));
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => $result['message'],
        ], 200);
    }

    /**
     * Handles POST update cabinet name and vision requests (`?action=update-cabinet-name`).
     * Exclusively authorized for `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function handleUpdateCabinetName(array $postParams): void
    {
        $authUser = $this->authService->getCurrentUser();
        $result = $this->cabinetService->updateCabinetName($authUser, $postParams);

        if (!$result['is_valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], (int) ($result['http_code'] ?? 422));
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => $result['message'],
            'cabinet_name' => $result['cabinet_name'] ?? '',
            'cabinet_vision' => $result['cabinet_vision'] ?? '',
        ], 200);
    }

    /**
     * Handles POST ministerial appointment requests (`?action=appoint-minister`).
     * Exclusively authorized for `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function handleAppointMinister(array $postParams): void
    {
        $authUser = $this->authService->getCurrentUser();
        $result = $this->cabinetService->appointMinister($authUser, $postParams);

        if (!$result['is_valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], (int) ($result['http_code'] ?? 422));
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => $result['message'],
            'decree_number' => $result['decree_number'] ?? '',
            'minister_name' => $result['minister_name'] ?? '',
        ], 200);
    }

    /**
     * Handles POST ministerial dismissal requests (`?action=dismiss-minister`).
     * Exclusively authorized for `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed> $postParams Submitted form payload.
     * @return void
     */
    public function handleDismissMinister(array $postParams): void
    {
        $authUser = $this->authService->getCurrentUser();
        $result = $this->cabinetService->dismissMinister($authUser, $postParams);

        if (!$result['is_valid']) {
            $this->sendJsonResponse([
                'status' => 'validation_error',
                'message' => $result['message'],
                'errors' => $result['errors'],
            ], (int) ($result['http_code'] ?? 422));
            return;
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'message' => $result['message'],
            'decree_number' => $result['decree_number'] ?? '',
            'former_minister' => $result['former_minister'] ?? '',
        ], 200);
    }

    /**
     * Renders a view file wrapped inside the main HTML layout.
     *
     * @param string $viewPath Relative path inside `Views/` without `.php`.
     * @param array<string, mixed> $data Variables extracted for the view template.
     * @return void
     */
    private function renderView(string $viewPath, array $data = []): void
    {
        $baseViewsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR;
        $contentFile = $baseViewsDir . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';
        $layoutFile = $baseViewsDir . 'layouts' . DIRECTORY_SEPARATOR . 'main-layout.php';

        extract($data, EXTR_SKIP);

        ob_start();
        if (is_file($contentFile)) {
            require $contentFile;
        }
        $pageContent = (string) ob_get_clean();

        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $pageContent;
        }
    }

    /**
     * Emits a standardized JSON HTTP response and terminates script execution.
     *
     * @param array<string, mixed> $payload Associative payload to encode.
     * @param int $httpStatusCode HTTP status code.
     * @return void
     */
    private function sendJsonResponse(array $payload, int $httpStatusCode = 200): void
    {
        http_response_code($httpStatusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
