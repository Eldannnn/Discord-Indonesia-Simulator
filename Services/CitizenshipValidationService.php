<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExecutiveUserModel;

/**
 * Custom Action Validation Service for Citizen Registration & Career Track Selection.
 * Strictly enforces business validation rules and persists new citizens into the `users` table.
 */
final class CitizenshipValidationService
{
    /** @var array<int, string> Allowed initial career tracks for new roleplay citizens */
    private const ALLOWED_TRACKS = [
        'TRACK_WARGA_PENGUSAHA',
        'TRACK_KADER_PARTAI',
        'TRACK_CPNS_KEMENTERIAN',
        'TRACK_AKADEMI_HANKAM',
        'TRACK_JURNALIS_PERS',
    ];

    /** @var array<int, string> Allowed regional domicile provinces in the simulation */
    private const ALLOWED_DOMICILES = [
        'DKI Jakarta — Pusat Pemerintahan',
        'IKN Nusantara — Kalimantan Timur',
        'Jawa Barat — Koridor Industri',
        'Jawa Timur — Poros Maritim Timur',
        'Sumatera Utara — Gerbang Barat',
        'Sulawesi Selatan — Hub Indonesia Timur',
    ];

    /** @var ExecutiveUserModel User model for database persistence */
    private ExecutiveUserModel $userModel;

    /**
     * Initializes the validation service.
     *
     * @param ExecutiveUserModel|null $userModel Optional injected user model.
     */
    public function __construct(?ExecutiveUserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new ExecutiveUserModel();
    }

    /**
     * Validates a citizen registration payload and stores the new citizen in the database.
     *
     * @param array<string, mixed> $input Raw sanitized request payload.
     * @return array{valid: bool, errors: array<string, string>, data: array<string, string>}
     */
    public function validateRegistrationPayload(array $input): array
    {
        $errors = [];

        $characterName = trim((string) ($input['character_name'] ?? ''));
        $username = trim((string) ($input['discord_handle'] ?? ''));
        $password = (string) ($input['account_password'] ?? '');
        $careerTrack = trim((string) ($input['career_track'] ?? ''));
        $domicile = trim((string) ($input['domicile'] ?? ''));
        $backstory = trim((string) ($input['character_backstory'] ?? ''));
        $oathAccepted = (string) ($input['oath_accepted'] ?? '') === '1';

        if ($characterName === '') {
            $errors['character_name'] = 'Nama lengkap karakter wajib diisi sesuai format kependudukan.';
        } elseif (mb_strlen($characterName) < 5 || mb_strlen($characterName) > 60) {
            $errors['character_name'] = 'Nama karakter harus terdiri dari 5 hingga 60 karakter.';
        } elseif (!preg_match('/^[a-zA-Z\s\.,\'-]+$/u', $characterName)) {
            $errors['character_name'] = 'Nama karakter hanya boleh mengandung huruf alfabet, gelar akademik, titik, atau koma.';
        }

        if ($username === '') {
            $errors['discord_handle'] = 'Username akun wajib dicantumkan.';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 32) {
            $errors['discord_handle'] = 'Username harus memiliki panjang 3 hingga 32 karakter.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors['discord_handle'] = 'Username hanya boleh berisi huruf, angka, titik, atau garis bawah.';
        } elseif ($this->userModel->isUsernameTaken($username)) {
            $errors['discord_handle'] = 'Username tersebut sudah terdaftar oleh warga lain.';
        }

        if ($password === '') {
            $errors['account_password'] = 'Kata sandi akun wajib dibuat untuk masuk ke portal.';
        } elseif (mb_strlen($password) < 6) {
            $errors['account_password'] = 'Kata sandi minimal terdiri dari 6 karakter.';
        }

        if ($careerTrack === '' || !in_array($careerTrack, self::ALLOWED_TRACKS, true)) {
            $errors['career_track'] = 'Silakan pilih jalur pengabdian awal yang tersedia.';
        }

        if ($domicile === '' || !in_array($domicile, self::ALLOWED_DOMICILES, true)) {
            $errors['domicile'] = 'Pilih wilayah provinsi domisili yang tersedia.';
        }

        if ($backstory === '') {
            $errors['character_backstory'] = 'Ringkasan latar belakang & visi karakter wajib diisi.';
        } elseif (mb_strlen($backstory) < 25) {
            $errors['character_backstory'] = 'Latar belakang karakter terlalu singkat (minimal 25 karakter).';
        } elseif (mb_strlen($backstory) > 500) {
            $errors['character_backstory'] = 'Latar belakang karakter maksimal 500 karakter.';
        }

        if (!$oathAccepted) {
            $errors['oath_accepted'] = 'Anda wajib menyetujui Sumpah Setia Konstitusi sebelum mendaftar.';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'errors' => $errors,
                'data' => [],
            ];
        }

        $nikPrefix = '3171' . date('dmy');
        $nikSequence = strtoupper(substr(hash('crc32b', $characterName . $username . microtime()), 0, 6));
        $nikRp = $nikPrefix . '-' . $nikSequence;
        $trackLabel = $this->resolveInitialCareerLabel($careerTrack);

        $this->userModel->createCitizenUser([
            'nik_rp' => $nikRp,
            'character_name' => $characterName,
            'username' => $username,
            'password_hash' => (string) password_hash($password, PASSWORD_BCRYPT),
            'career_track_label' => $trackLabel,
            'domicile' => $domicile,
            'character_backstory' => $backstory,
        ]);

        return [
            'valid' => true,
            'errors' => [],
            'data' => [
                'nik_rp' => $nikRp,
                'character_name' => $characterName,
                'username' => $username,
                'career_track' => $careerTrack,
                'initial_rbac_role' => $trackLabel,
                'domicile' => $domicile,
                'issued_at' => date('Y-m-d H:i:s') . ' WIB',
            ],
        ];
    }

    /**
     * Maps a selected starting career track to its human-readable citizenship status.
     *
     * @param string $careerTrack Validated career track code.
     * @return string Human-readable citizenship assignment.
     */
    private function resolveInitialCareerLabel(string $careerTrack): string
    {
        $mapping = [
            'TRACK_WARGA_PENGUSAHA' => 'Warga Negara & Pendiri Badan Usaha Swasta',
            'TRACK_KADER_PARTAI' => 'Kader Partai Politik & Bakal Calon Legislatif',
            'TRACK_CPNS_KEMENTERIAN' => 'Aparatur Sipil Negara & Staf Kementerian',
            'TRACK_AKADEMI_HANKAM' => 'Taruna Akademi Pertahanan & Keamanan',
            'TRACK_JURNALIS_PERS' => 'Jurnalis Pers Nasional & Pengawas Publik',
        ];

        return $mapping[$careerTrack] ?? 'Warga Negara Republik Indonesia';
    }
}
