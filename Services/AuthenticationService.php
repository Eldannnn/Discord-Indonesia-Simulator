<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExecutiveUserModel;

/**
 * Custom Authentication & Session Service for Pejabat Negara & Warga Negara.
 * Enforces custom validation, verifies hashed credentials against MySQL, and manages session state.
 */
final class AuthenticationService
{
    /** @var string Session key for authenticated user payload */
    private const SESSION_KEY = 'nusantara_auth_user';

    /** @var ExecutiveUserModel User data access model */
    private ExecutiveUserModel $userModel;

    /**
     * Initializes the authentication service and ensures PHP session is active.
     *
     * @param ExecutiveUserModel|null $userModel Optional injected user model.
     */
    public function __construct(?ExecutiveUserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new ExecutiveUserModel();
        $this->ensureSessionStarted();
    }

    /**
     * Validates login credentials, stores authenticated session, and returns user state profile.
     *
     * @param array<string, mixed> $input Raw POST parameters (`login_identifier`, `login_password`).
     * @return array{valid: bool, errors: array<string, string>, user: array<string, mixed>}
     */
    public function authenticate(array $input): array
    {
        $errors = [];
        $identifier = trim((string) ($input['login_identifier'] ?? ''));
        $password = (string) ($input['login_password'] ?? '');

        if ($identifier === '') {
            $errors['login_identifier'] = 'Username atau Nomor Induk Kependudukan (NIK) wajib diisi.';
        }

        if ($password === '') {
            $errors['login_password'] = 'Kata sandi wajib diisi.';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'errors' => $errors,
                'user' => [],
            ];
        }

        $userRecord = $this->userModel->findUserByIdentifier($identifier);
        if ($userRecord === null || !password_verify($password, (string) ($userRecord['password_hash'] ?? ''))) {
            return [
                'valid' => false,
                'errors' => [
                    'login_identifier' => 'Identitas pengguna atau kata sandi tidak sesuai dengan data kependudukan.',
                ],
                'user' => [],
            ];
        }

        $sessionUser = [
            'id' => (int) $userRecord['id'],
            'role_id' => (int) $userRecord['role_id'],
            'role_code' => (string) $userRecord['role_code'],
            'nik_rp' => (string) $userRecord['nik_rp'],
            'character_name' => (string) $userRecord['character_name'],
            'username' => (string) $userRecord['username'],
            'role_title' => (string) $userRecord['role_title'],
            'branch_name' => (string) ($userRecord['branch_name'] ?? 'Eksekutif Nasional'),
            'constitutional_basis' => (string) $userRecord['constitutional_basis'],
            'inauguration_decree' => (string) $userRecord['inauguration_decree'],
            'domicile_province' => (string) $userRecord['domicile_province'],
            'faction_party' => (string) $userRecord['faction_party'],
        ];

        $_SESSION[self::SESSION_KEY] = $sessionUser;

        return [
            'valid' => true,
            'errors' => [],
            'user' => $sessionUser,
        ];
    }

    /**
     * Retrieves the currently logged-in user from the active session.
     *
     * @return array<string, mixed>|null Authenticated user array or null if guest.
     */
    public function getCurrentUser(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;
        return is_array($user) ? $user : null;
    }

    /**
     * Terminates the current user session.
     *
     * @return void
     */
    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Starts a PHP session safely when running in HTTP or CLI environments.
     *
     * @return void
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
