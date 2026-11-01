-- =============================================================================
-- REPUBLIK NUSANTARA ROLEPLAY — MYSQL DATABASE SCHEMA & SEEDER
-- Scope: Portal Informasi Nasional (Berita, Ekonomi, DPR, UU Aktif, Partai Politik)
--        & Menu Eksekutif Terpisah (Presiden RI dan Wakil Presiden RI)
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `nusantara_rp`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `nusantara_rp`;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW IF EXISTS `vw_user_constitutional_authorities`;
DROP TABLE IF EXISTS `cabinet_ministries`;
DROP TABLE IF EXISTS `cabinet_settings`;
DROP TABLE IF EXISTS `national_news`;
DROP TABLE IF EXISTS `active_laws`;
DROP TABLE IF EXISTS `political_parties`;
DROP TABLE IF EXISTS `national_economy_metrics`;
DROP TABLE IF EXISTS `executive_decrees_log`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `rbac_role_permissions`;
DROP TABLE IF EXISTS `rbac_permissions`;
DROP TABLE IF EXISTS `rbac_roles`;
DROP TABLE IF EXISTS `server_telemetry`;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- 1. TABEL MASTER JABATAN (rbac_roles)
-- -----------------------------------------------------------------------------
CREATE TABLE `rbac_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_code` VARCHAR(64) NOT NULL UNIQUE,
    `role_title` VARCHAR(120) NOT NULL,
    `branch_code` VARCHAR(32) NOT NULL DEFAULT 'EKSEKUTIF',
    `branch_name` VARCHAR(120) NOT NULL,
    `constitutional_basis` VARCHAR(160) NOT NULL,
    `clearance_level` VARCHAR(80) NOT NULL,
    `clearance_rank` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `quota_limit` INT UNSIGNED NOT NULL DEFAULT 1,
    `active_holders` INT UNSIGNED NOT NULL DEFAULT 1,
    `salary_rp` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `recruitment_type` VARCHAR(120) NOT NULL,
    `summary_text` TEXT NOT NULL,
    `command_modules_json` JSON NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_roles_rank_active` (`is_active`, `clearance_rank`, `branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. TABEL MASTER BIDANG KEWENANGAN EKSEKUTIF (rbac_permissions)
-- -----------------------------------------------------------------------------
CREATE TABLE `rbac_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `permission_key` VARCHAR(96) NOT NULL UNIQUE,
    `permission_name` VARCHAR(160) NOT NULL,
    `authority_category` ENUM(
        'KEPALA_PEMERINTAHAN',
        'KEPALA_NEGARA',
        'LEGISLASI_DAN_APBN',
        'PERTAHANAN_DAN_DIPLOMASI',
        'YUDISIAL_DAN_KEHORMATAN',
        'KOORDINASI_DAN_SUKSESI',
        'PEMBATASAN_KONSTITUSI'
    ) NOT NULL,
    `legal_basis` VARCHAR(160) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_perm_category` (`authority_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. TABEL PIVOT WEWENANG JABATAN (rbac_role_permissions)
-- -----------------------------------------------------------------------------
CREATE TABLE `rbac_role_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `authorization_status` ENUM(
        'GRANTED_PREROGATIVE',
        'GRANTED_WITH_DPR_APPROVAL',
        'GRANTED_WITH_DPR_CONSIDERATION',
        'GRANTED_WITH_MA_CONSIDERATION',
        'GRANTED_DELEGATED_MANDATE',
        'CONDITIONAL_PASAL_8_SUCCESSION',
        'DENIED_CONSTITUTIONAL'
    ) NOT NULL,
    `access_label` VARCHAR(80) NOT NULL,
    `can_execute_directly` TINYINT(1) NOT NULL DEFAULT 0,
    `check_and_balance_organ` VARCHAR(80) NOT NULL DEFAULT 'MANDIRI',
    `constitutional_explanation` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_role_permission` (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. TABEL PENGGUNA / WARGA NEGARA & PEJABAT (users)
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nik_rp` VARCHAR(32) NOT NULL UNIQUE,
    `character_name` VARCHAR(120) NOT NULL,
    `username` VARCHAR(64) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `career_track` VARCHAR(80) NOT NULL DEFAULT 'KEPEMIMPINAN_NASIONAL',
    `faction_party` VARCHAR(120) NOT NULL DEFAULT 'Independen',
    `domicile_province` VARCHAR(100) NOT NULL,
    `character_backstory` TEXT NULL,
    `office_status` ENUM('MENJABAT_AKTIF', 'DINAS_LUAR_NEGERI', 'BERHALANGAN_SEMENTARA', 'PLT_PRESIDEN', 'WARGA_AKTIF', 'NON_AKTIF') NOT NULL DEFAULT 'WARGA_AKTIF',
    `inauguration_decree` VARCHAR(80) NOT NULL DEFAULT 'Akta Kependudukan Nasional',
    `term_start_date` DATE NOT NULL,
    `term_end_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role_status` (`role_id`, `office_status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. TABEL LOG KEPUTUSAN & TINDAKAN EKSEKUTIF (executive_decrees_log)
-- -----------------------------------------------------------------------------
CREATE TABLE `executive_decrees_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `document_number` VARCHAR(64) NOT NULL UNIQUE,
    `document_type` ENUM(
        'KEPPRES',
        'PERPRES',
        'PP',
        'PERPPU',
        'NOTA_KEUANGAN',
        'INSTRUKSI_WAPRES',
        'RISALAH_RAKOR_WAPRES',
        'AUDIT_KINERJA_KABINET'
    ) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `summary_content` TEXT NULL,
    `counter_approval_status` VARCHAR(80) NOT NULL DEFAULT 'Disahkan Pemerintah',
    `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_decrees_user_time` (`user_id`, `executed_at`),
    CONSTRAINT `fk_decrees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_decrees_permission` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. TABEL INDIKATOR EKONOMI NASIONAL (national_economy_metrics)
-- -----------------------------------------------------------------------------
CREATE TABLE `national_economy_metrics` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `metric_code` VARCHAR(64) NOT NULL UNIQUE,
    `metric_label` VARCHAR(120) NOT NULL,
    `metric_value` VARCHAR(80) NOT NULL,
    `metric_unit` VARCHAR(40) NOT NULL DEFAULT '',
    `trend_direction` ENUM('UP', 'DOWN', 'STABLE') NOT NULL DEFAULT 'STABLE',
    `trend_change` VARCHAR(60) NOT NULL,
    `period_label` VARCHAR(80) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `display_order` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. TABEL PARTAI POLITIK & KOMPOSISI DPR RI (political_parties)
-- -----------------------------------------------------------------------------
CREATE TABLE `political_parties` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `party_code` VARCHAR(32) NOT NULL UNIQUE,
    `party_name` VARCHAR(140) NOT NULL,
    `party_acronym` VARCHAR(24) NOT NULL,
    `ideology` VARCHAR(140) NOT NULL,
    `coalition_status` ENUM('KOALISI_PEMERINTAH', 'OPOSISI', 'INDEPENDEN') NOT NULL DEFAULT 'KOALISI_PEMERINTAH',
    `chairperson_name` VARCHAR(120) NOT NULL,
    `dpr_seats` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_parliament_seats` INT UNSIGNED NOT NULL DEFAULT 580,
    `badge_theme` VARCHAR(40) NOT NULL DEFAULT 'crimson',
    `description` TEXT NOT NULL,
    `display_order` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. TABEL UNDANG-UNDANG & REGULASI AKTIF (active_laws)
-- -----------------------------------------------------------------------------
CREATE TABLE `active_laws` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `law_number` VARCHAR(80) NOT NULL UNIQUE,
    `law_type` ENUM('UNDANG-UNDANG', 'PERPRES', 'PP', 'PERPPU') NOT NULL DEFAULT 'UNDANG-UNDANG',
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `summary` TEXT NOT NULL,
    `enacted_by` VARCHAR(120) NOT NULL,
    `enacted_date` DATE NOT NULL,
    `status` ENUM('BERLAKU_AKTIF', 'DALAM_EVALUASI', 'DICABUT') NOT NULL DEFAULT 'BERLAKU_AKTIF',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. TABEL BERITA NASIONAL (national_news)
-- -----------------------------------------------------------------------------
CREATE TABLE `national_news` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `news_code` VARCHAR(64) NOT NULL UNIQUE,
    `category` VARCHAR(80) NOT NULL,
    `headline` VARCHAR(255) NOT NULL,
    `summary` TEXT NOT NULL,
    `author_name` VARCHAR(120) NOT NULL,
    `published_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. TABEL TELEMETRI UMUM (server_telemetry)
-- -----------------------------------------------------------------------------
CREATE TABLE `server_telemetry` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `metric_key` VARCHAR(64) NOT NULL UNIQUE,
    `metric_value` VARCHAR(255) NOT NULL,
    `is_current` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. TABEL SETELAN KABINET PRESIDEN (cabinet_settings)
-- -----------------------------------------------------------------------------
CREATE TABLE `cabinet_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cabinet_name` VARCHAR(120) NOT NULL DEFAULT 'Kabinet Indonesia Emas',
    `cabinet_vision` VARCHAR(255) NOT NULL DEFAULT 'Akselerasi Pembangunan Terpadu, Kedaulatan Nasional, dan Transformasi Digital 2026–2031',
    `president_user_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `formation_date` DATE NOT NULL DEFAULT '2026-10-21',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cabinet_pres` FOREIGN KEY (`president_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. TABEL KEMENTERIAN NEGARA (cabinet_ministries)
-- -----------------------------------------------------------------------------
CREATE TABLE `cabinet_ministries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ministry_group` ENUM('GOLONGAN_I', 'GOLONGAN_II', 'GOLONGAN_III', 'SETINGKAT_MENTERI') NOT NULL DEFAULT 'GOLONGAN_I',
    `ministry_code` VARCHAR(32) NOT NULL UNIQUE,
    `ministry_name` VARCHAR(160) NOT NULL,
    `minister_title` VARCHAR(160) NOT NULL,
    `constitutional_basis` VARCHAR(160) NOT NULL,
    `scope_description` TEXT NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `office_status` ENUM('MENJABAT', 'KOSONG') NOT NULL DEFAULT 'KOSONG',
    `appointed_at` DATETIME NULL,
    `decree_reference` VARCHAR(80) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ministry_group` (`ministry_group`),
    CONSTRAINT `fk_ministry_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DATA SEEDING: JABATAN PRESIDEN, WAKIL PRESIDEN, DAN WARGA NEGARA
-- =============================================================================

INSERT INTO `rbac_roles` (
    `id`, `role_code`, `role_title`, `branch_code`, `branch_name`,
    `constitutional_basis`, `clearance_level`, `clearance_rank`,
    `quota_limit`, `active_holders`, `salary_rp`, `recruitment_type`,
    `summary_text`, `command_modules_json`, `is_active`
) VALUES
(
    1,
    'ROLE_PRESIDEN',
    'Presiden Republik Indonesia',
    'EKSEKUTIF',
    'Kepala Negara & Kepala Pemerintahan',
    'Kepemimpinan Eksekutif Nasional',
    'Kedudukan Tertinggi Eksekutif Negara',
    1,
    1,
    1,
    125000000,
    'Pemilihan Umum Presiden Nasional',
    'Memegang kekuasaan pemerintahan negara, memimpin kabinet kementerian, menerbitkan Peraturan Presiden, Keputusan Presiden, Peraturan Pemerintah, serta mengarahkan kebijakan pembangunan dan anggaran negara.',
    JSON_ARRAY(
        'Pengangkatan & Perombakan Menteri Kabinet',
        'Penerbitan Peraturan Presiden, Peraturan Pemerintah & Perppu',
        'Pengajuan Rancangan APBN & Nota Keuangan Negara',
        'Komando Tertinggi Pertahanan & Keamanan Nasional'
    ),
    1
),
(
    2,
    'ROLE_WAKIL_PRESIDEN',
    'Wakil Presiden Republik Indonesia',
    'EKSEKUTIF',
    'Wakil Kepala Negara & Wakil Kepala Pemerintahan',
    'Kepemimpinan Koordinasi & Pengawasan Eksekutif',
    'Wakil Kepala Pemerintahan Nasional',
    2,
    1,
    1,
    95000000,
    'Pemilihan Umum Presiden Nasional',
    'Membantu Presiden dalam menjalankan tugas pemerintahan, memimpin koordinasi lintas kementerian strategis, mengawasi reformasi birokrasi, percepatan pengentasan kemiskinan, serta mengevaluasi capaian kinerja kabinet.',
    JSON_ARRAY(
        'Koordinasi Lintas Kementerian & Reformasi Birokrasi',
        'Pengawasan Kinerja Kementerian & Evaluasi Program Strategis',
        'Pimpinan Rapat Koordinasi Kabinet Terbatas',
        'Pelaksana Tugas Kepresidenan Saat Presiden Berhalangan'
    ),
    1
),
(
    3,
    'ROLE_WARGA_SIPIL',
    'Warga Negara Republik Indonesia',
    'SIPIL',
    'Warga Negara & Sektor Sipil',
    'Kependudukan Nasional Republik Nusantara',
    'Kewarganegaraan Umum',
    10,
    2000,
    318,
    15000000,
    'Pendaftaran Kependudukan Nasional',
    'Warga negara yang berhak memilih dalam Pemilihan Umum, bergabung dengan partai politik, mendirikan badan usaha, serta meniti karir menuju jabatan kenegaraan.',
    JSON_ARRAY(
        'Hak Pilih & Pencalonan dalam Pemilihan Umum',
        'Pendirian Badan Usaha & Aktivitas Politik Nasional'
    ),
    0
),
(
    4,
    'ROLE_MENTERI',
    'Menteri Negara Republik Indonesia',
    'EKSEKUTIF',
    'Menteri Pembantu Presiden',
    'Pasal 17 UUD NRI 1945 & UU Kementerian Negara',
    'Menteri Kabinet Negara',
    3,
    50,
    2,
    75000000,
    'Hak Prerogatif Presiden RI',
    'Membantu Presiden dalam memimpin kementerian negara, merumuskan dan menetapkan kebijakan sektoral, serta mengoordinasikan pelaksanaan urusan pemerintahan sesuai bidang tugasnya.',
    JSON_ARRAY(
        'Penetapan Kebijakan Sektoral Kementerian',
        'Pengelolaan Anggaran & Program Kerja Kementerian',
        'Koordinasi Lembaga Teknis & Pemangku Kebijakan'
    ),
    1
);

-- =============================================================================
-- DATA SEEDING: BIDANG KEBIJAKAN PRESIDEN & WAKIL PRESIDEN
-- =============================================================================

INSERT INTO `rbac_permissions` (
    `id`, `permission_key`, `permission_name`, `authority_category`, `legal_basis`, `description`
) VALUES
(
    1,
    'eksekutif.kabinet.kelembagaan',
    'Tata Kelola Kabinet & Kelembagaan Pemerintahan',
    'KEPALA_PEMERINTAHAN',
    'Kewenangan Kepala Pemerintahan',
    'Penataan kementerian negara, pengangkatan pejabat tinggi, serta penugasan khusus kabinet.'
),
(
    2,
    'legislasi.regulasi.nasional',
    'Penerbitan Regulasi & Kebijakan Pembangunan Nasional',
    'KEPALA_PEMERINTAHAN',
    'Kewenangan Regulasi Eksekutif',
    'Penetapan Peraturan Presiden dan Peraturan Pemerintah untuk mempercepat program pembangunan nasional.'
),
(
    3,
    'anggaran.ekonomi.fiskal',
    'Kebijakan Ekonomi Makro, Investasi & Keuangan Negara',
    'LEGISLASI_DAN_APBN',
    'Kewenangan Pengelolaan Fiskal',
    'Penetapan arah anggaran negara, stimulus industri, stabilitas harga pangan, dan investasi strategis.'
),
(
    4,
    'pertahanan.keamanan.kedaulatan',
    'Pertahanan Negara, Keamanan Siber & Ketertiban Umum',
    'PERTAHANAN_DAN_DIPLOMASI',
    'Kewenangan Keamanan Nasional',
    'Penetapan kebijakan strategis pertahanan wilayah, pelindungan data warga, dan ketertiban umum.'
),
(
    5,
    'wapres.koordinasi.birokrasi',
    'Koordinasi Reformasi Birokrasi & Pelayanan Publik',
    'KOORDINASI_DAN_SUKSESI',
    'Mandat Koordinasi Wakil Presiden',
    'Koordinasi lintas kementerian untuk penyederhanaan birokrasi dan integrasi layanan publik.'
),
(
    6,
    'wapres.pengawasan.kabinet',
    'Pengawasan Kinerja Kementerian & Kesejahteraan Daerah',
    'KOORDINASI_DAN_SUKSESI',
    'Mandat Pengawasan Wakil Presiden',
    'Evaluasi capaian program kementerian, pengentasan kemiskinan, dan percepatan pembangunan daerah.'
);

INSERT INTO `rbac_role_permissions` (
    `role_id`, `permission_id`, `authorization_status`, `access_label`,
    `can_execute_directly`, `check_and_balance_organ`, `constitutional_explanation`
) VALUES
(1, 1, 'GRANTED_PREROGATIVE', 'Kewenangan Presiden', 1, 'Istana Kepresidenan', 'Presiden menetapkan susunan dan penugasan menteri kabinet.'),
(1, 2, 'GRANTED_PREROGATIVE', 'Kewenangan Presiden', 1, 'Sekretariat Negara', 'Presiden menerbitkan Perpres, PP, dan Perppu.'),
(1, 3, 'GRANTED_PREROGATIVE', 'Kewenangan Presiden', 1, 'Kementerian Keuangan & DPR', 'Presiden mengarahkan kebijakan ekonomi makro dan APBN.'),
(1, 4, 'GRANTED_PREROGATIVE', 'Kewenangan Presiden', 1, 'Dewan Ketahanan Nasional', 'Presiden menetapkan kebijakan pertahanan dan keamanan nasional.'),
(2, 5, 'GRANTED_PREROGATIVE', 'Kewenangan Wakil Presiden', 1, 'Sekretariat Wakil Presiden', 'Wakil Presiden memimpin koordinasi reformasi birokrasi nasional.'),
(2, 6, 'GRANTED_PREROGATIVE', 'Kewenangan Wakil Presiden', 1, 'Sekretariat Wakil Presiden', 'Wakil Presiden memimpin evaluasi dan pengawasan kinerja kementerian.');

-- =============================================================================
-- DATA SEEDING: USER PRESIDEN & USER WAKIL PRESIDEN (Kata Sandi: Nusantara1945)
-- =============================================================================

INSERT INTO `users` (
    `id`, `nik_rp`, `character_name`, `username`, `email`, `password_hash`,
    `role_id`, `career_track`, `faction_party`, `domicile_province`, `office_status`,
    `inauguration_decree`, `term_start_date`, `term_end_date`
) VALUES
(
    1,
    '317101-PRES-0001',
    'Ir. H. Pratama Wijaya, M.P.A.',
    'presiden_pratama',
    'presiden@setneg.nusantara.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    1,
    'KEPEMIMPINAN_NASIONAL',
    'Partai Persatuan Demokrasi Nusantara (PPDN)',
    'DKI Jakarta — Istana Merdeka',
    'MENJABAT_AKTIF',
    'Keputusan Pelantikan MPR RI 2026',
    '2026-10-20',
    '2031-10-20'
),
(
    2,
    '317101-WPRS-0002',
    'Prof. Dr. H. Mahendra Kusuma, S.H., LL.M.',
    'wapres_mahendra',
    'wapres@setwapres.nusantara.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    2,
    'KEPEMIMPINAN_NASIONAL',
    'Partai Karya Maju Indonesia (PKMI)',
    'DKI Jakarta — Istana Wakil Presiden',
    'MENJABAT_AKTIF',
    'Keputusan Pelantikan MPR RI 2026',
    '2026-10-20',
    '2031-10-20'
),
(
    3,
    '317102-MNTR-0003',
    'Dr. H. Faisal Pratama, S.H., LL.M.',
    'menlu_faisal',
    'menlu@kemlu.nusantara.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    4,
    'KABINET_MENTERI',
    'Independen',
    'DKI Jakarta',
    'MENJABAT_AKTIF',
    'KEPPRES-01/P/2026',
    '2026-10-21',
    '2031-10-20'
),
(
    4,
    '317102-MNTR-0004',
    'Jenderal Pol. (Purn.) Tito Dian, M.A., Ph.D.',
    'mendagri_tito',
    'mendagri@kemendagri.nusantara.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    4,
    'KABINET_MENTERI',
    'Independen',
    'DKI Jakarta',
    'MENJABAT_AKTIF',
    'KEPPRES-01/P/2026',
    '2026-10-21',
    '2031-10-20'
),
(
    5,
    '317103-WRGA-0005',
    'Letjen TNI (Purn.) Raden Soepomo, M.Sc.',
    'raden_soepomo',
    'soepomo@pertahanan.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    3,
    'STRATEGI_PERTAHANAN',
    'Independen',
    'Jawa Barat',
    'WARGA_AKTIF',
    'Akta Kependudukan Nasional',
    '2026-01-01',
    '2031-12-31'
),
(
    6,
    '317103-WRGA-0006',
    'Anindya Bakrie, B.Sc., M.B.A.',
    'anindya_b',
    'anindya@kadin.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    3,
    'EKONOMI_BISNIS',
    'Partai Karya Maju Indonesia (PKMI)',
    'DKI Jakarta',
    'WARGA_AKTIF',
    'Akta Kependudukan Nasional',
    '2026-01-01',
    '2031-12-31'
),
(
    7,
    '317103-WRGA-0007',
    'Dra. Hj. Siti Nurbaya Hasan, M.Sc.',
    'siti_nurbaya',
    'siti.nurbaya@birokrasi.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    3,
    'BIROKRASI_PUBLIK',
    'Partai Amanat Bangsa (PAB)',
    'Jawa Timur',
    'WARGA_AKTIF',
    'Akta Kependudukan Nasional',
    '2026-01-01',
    '2031-12-31'
),
(
    8,
    '317103-WRGA-0008',
    'Dr. Ir. Budi Gunadi Sadikin, CHFC',
    'budi_gunadi',
    'budi.gunadi@kesehatan.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    3,
    'TATA_KELOLA_PUBLIK',
    'Independen',
    'Banten',
    'WARGA_AKTIF',
    'Akta Kependudukan Nasional',
    '2026-01-01',
    '2031-12-31'
),
(
    9,
    '317103-WRGA-0009',
    'Dr. Meutya Hafid, M.I.P.',
    'meutya_h',
    'meutya@diplomasi.rp',
    '$2y$10$OPz7vvcpinlHMGcTp2szueNwkg7YzP3tS/RmU/eg/g6752fgjNKiS',
    3,
    'KOMUNIKASI_DIPLOMASI',
    'Partai Karya Maju Indonesia (PKMI)',
    'Sumatera Utara',
    'WARGA_AKTIF',
    'Akta Kependudukan Nasional',
    '2026-01-01',
    '2031-12-31'
);

-- =============================================================================
-- DATA SEEDING: SETELAN KABINET & KEMENTERIAN NEGARA (GOLONGAN I)
-- =============================================================================

INSERT INTO `cabinet_settings` (
    `id`, `cabinet_name`, `cabinet_vision`, `president_user_id`, `formation_date`
) VALUES (
    1,
    'Kabinet Indonesia Emas',
    'Akselerasi Pembangunan Terpadu, Kedaulatan Nasional, dan Transformasi Digital 2026–2031',
    1,
    '2026-10-21'
);

INSERT INTO `cabinet_ministries` (
    `id`, `ministry_group`, `ministry_code`, `ministry_name`, `minister_title`,
    `constitutional_basis`, `scope_description`, `user_id`, `office_status`,
    `appointed_at`, `decree_reference`
) VALUES
(
    1,
    'GOLONGAN_I',
    'KEMENLU',
    'Kementerian Luar Negeri',
    'Menteri Luar Negeri Republik Indonesia',
    'Pasal 8 ayat (3) & Pasal 17 ayat (3) UUD NRI 1945',
    'Menyelenggarakan urusan pemerintahan di bidang hubungan luar negeri dan politik luar negeri untuk membantu Presiden dalam menyelenggarakan pemerintahan negara serta pemegang mandat triumvirat kepresidenan.',
    3,
    'MENJABAT',
    '2026-10-21 09:30:00',
    'KEPPRES-01/P/2026'
),
(
    2,
    'GOLONGAN_I',
    'KEMENDAGRI',
    'Kementerian Dalam Negeri',
    'Menteri Dalam Negeri Republik Indonesia',
    'Pasal 8 ayat (3) & Pasal 17 ayat (3) UUD NRI 1945',
    'Menyelenggarakan urusan pemerintahan dalam negeri, otonomi daerah, pembinaan administrasi kependudukan dan ketertiban umum untuk membantu Presiden serta pemegang mandat triumvirat kepresidenan.',
    4,
    'MENJABAT',
    '2026-10-21 09:30:00',
    'KEPPRES-01/P/2026'
),
(
    3,
    'GOLONGAN_I',
    'KEMENHAN',
    'Kementerian Pertahanan',
    'Menteri Pertahanan Republik Indonesia',
    'Pasal 8 ayat (3) & Pasal 17 ayat (3) UUD NRI 1945',
    'Menyelenggarakan urusan pemerintahan di bidang pertahanan negara, kesiapsiagaan komponen utama, cadangan dan pendukung untuk menegakkan kedaulatan negara serta pemegang mandat triumvirat kepresidenan.',
    NULL,
    'KOSONG',
    NULL,
    NULL
);

-- =============================================================================
-- DATA SEEDING: RIWAYAT KEPUTUSAN EKSEKUTIF (executive_decrees_log)
-- =============================================================================

INSERT INTO `executive_decrees_log` (
    `user_id`, `permission_id`, `document_number`, `document_type`,
    `title`, `summary_content`, `counter_approval_status`, `executed_at`
) VALUES
(
    1,
    1,
    'KEPPRES-01/P/2026',
    'KEPPRES',
    'Pengangkatan Menteri-Menteri Negara Kabinet Indonesia Emas Periode 2026–2031',
    'Menetapkan susunan menteri koordinator dan menteri teknis untuk menjalankan program prioritas pemerintah periode 2026–2031.',
    'Disahkan Presiden RI',
    '2026-10-21 09:30:00'
),
(
    1,
    2,
    'PERPRES-14/2026',
    'PERPRES',
    'Percepatan Transformasi Layanan Publik Digital dan Satu Data Kependudukan Nasional',
    'Menginstruksikan seluruh kementerian dan pemerintah daerah mengintegrasikan layanan administrasi warga secara digital.',
    'Disahkan Presiden RI',
    '2026-10-28 14:00:00'
),
(
    2,
    5,
    'INS-WAPRES/04/2026',
    'INSTRUKSI_WAPRES',
    'Koordinasi Nasional Percepatan Reformasi Birokrasi dan Evaluasi Kinerja Kementerian',
    'Mengoordinasikan kementerian koordinator dalam rangka percepatan sasaran pembangunan nasional dan kepatuhan layanan publik.',
    'Diterbitkan Wakil Presiden RI',
    '2026-10-30 10:15:00'
);

-- =============================================================================
-- DATA SEEDING: INDIKATOR EKONOMI NASIONAL (national_economy_metrics)
-- =============================================================================

INSERT INTO `national_economy_metrics` (
    `metric_code`, `metric_label`, `metric_value`, `metric_unit`,
    `trend_direction`, `trend_change`, `period_label`, `description`, `display_order`
) VALUES
(
    'GDP_GROWTH',
    'Pertumbuhan Ekonomi (PDB)',
    '5,42',
    '%',
    'UP',
    '+0,28% (YoY)',
    'Kuartal IV — 2026',
    'Didorong oleh peningkatan hilirisasi industri manufaktur, konsumsi rumah tangga, dan investasi infrastruktur nasional.',
    1
),
(
    'APBN_REVENUE',
    'Pendapatan Negara (APBN)',
    'Rp 2.984,5',
    'Triliun',
    'UP',
    '96,4% dari Target',
    'Tahun Anggaran 2026',
    'Penerimaan perpajakan, bea cukai, dan Penerimaan Negara Bukan Pajak (PNBP) sektor sumber daya alam.',
    2
),
(
    'INFLATION_RATE',
    'Tingkat Inflasi Tahunan',
    '2,35',
    '%',
    'DOWN',
    '-0,15% (MoM)',
    'Oktober 2026',
    'Terkendali dalam rentang sasaran nasional berkat stabilisasi pasokan beras dan distribusi pangan antarprovinsi.',
    3
),
(
    'EXCHANGE_RATE',
    'Nilai Tukar Rupiah',
    'Rp 15.120',
    '/ USD',
    'UP',
    'Menguat +0,65%',
    'Pasar Spot Jakarta',
    'Stabilitas nilai tukar ditopang oleh surplus neraca perdagangan selama 42 bulan berturut-turut.',
    4
),
(
    'FOREIGN_RESERVES',
    'Cadangan Devisa Negara',
    'USD 148,6',
    'Miliar',
    'UP',
    '+USD 3,2 Miliar',
    'Bank Sentral Nusantara',
    'Setara dengan pembiayaan 6,5 bulan impor dan pembayaran utang luar negeri pemerintah.',
    5
),
(
    'BI_RATE',
    'Suku Bunga Acuan Nasional',
    '5,75',
    '%',
    'STABLE',
    'Tetap (3 RDG Terakhir)',
    'Kebijakan Moneter',
    'Menjaga keseimbangan antara stabilitas moneter dan dorongan penyaluran kredit usaha produktif.',
    6
);

-- =============================================================================
-- DATA SEEDING: PARTAI POLITIK & KOMPOSISI DPR RI (political_parties)
-- Total Kursi DPR RI = 580 Kursi
-- =============================================================================

INSERT INTO `political_parties` (
    `party_code`, `party_name`, `party_acronym`, `ideology`,
    `coalition_status`, `chairperson_name`, `dpr_seats`, `total_parliament_seats`,
    `badge_theme`, `description`, `display_order`
) VALUES
(
    'PPDN',
    'Partai Persatuan Demokrasi Nusantara',
    'PPDN',
    'Nasionalisme Kerakyatan & Sosial Demokrasi',
    'KOALISI_PEMERINTAH',
    'Dr. H. Surya Dharmawan, M.Si.',
    128,
    580,
    'crimson',
    'Partai pemenang pemilu yang berfokus pada kedaulatan pangan, perlindungan buruh dan petani, pembangunan infrastruktur merata di luar Pulau Jawa, serta penguatan jaring pengaman sosial.',
    1
),
(
    'PKMI',
    'Partai Karya Maju Indonesia',
    'PKMI',
    'Nasionalisme Pembangunan & Ekonomi Pasar Terkelola',
    'KOALISI_PEMERINTAH',
    'Ir. Airlangga Hartono, M.B.A.',
    104,
    580,
    'amber',
    'Partai teknokratik yang mendorong percepatan industrialisasi nasional, kemudahan berusaha, stabilitas makroekonomi, hilirisasi mineral, dan modernisasi tata kelola BUMN.',
    2
),
(
    'PGRN',
    'Partai Gerakan Rakyat Nusantara',
    'PGRN',
    'Nasionalisme Patriotik & Kemandirian Pertahanan',
    'KOALISI_PEMERINTAH',
    'Jenderal (Purn.) Wirawan Prakoso',
    96,
    580,
    'slate',
    'Menitikberatkan pada kemandirian pertahanan dan keamanan negara, swasembada energi dan air, ketahanan industri strategis dalam negeri, serta ketegasan penegakan hukum.',
    3
),
(
    'PKSN',
    'Partai Keadilan Sejahtera Nusantara',
    'PKSN',
    'Demokrasi Religius & Kesejahteraan Sosial',
    'OPOSISI',
    'K.H. Ahmad Fauzi Rahman, Lc., M.A.',
    82,
    580,
    'emerald',
    'Fraksi oposisi utama di parlemen yang mengawal transparansi anggaran pemerintah, pemberantasan korupsi, penguatan ekonomi syariah dan UMKM, serta pemerataan akses pendidikan.',
    4
),
(
    'PRB',
    'Partai Reformasi Pembaharuan',
    'PRB',
    'Liberalisme Sosial, Ekonomi Hijau & Digitalisasi',
    'OPOSISI',
    'Nadia Kartika Putri, S.E., M.P.P.',
    68,
    580,
    'sky',
    'Partai modern yang memperjuangkan kebebasan sipil, transisi energi terbarukan, ekonomi kreatif dan teknologi digital, serta reformasi birokrasi berbasis meritokrasi.',
    5
),
(
    'PKBN',
    'Partai Kebangkitan Bangsa Nusantara',
    'PKBN',
    'Nasionalisme Religius Moderat & Kemandirian Desa',
    'KOALISI_PEMERINTAH',
    'Drs. H. Muhaimin Iskandar Muda',
    58,
    580,
    'teal',
    'Berfokus pada pemberdayaan dana desa, peningkatan kesejahteraan pesantren dan lembaga pendidikan daerah, serta pelestarian kerukunan masyarakat majemuk.',
    6
),
(
    'PDKN',
    'Partai Demokratik Konstitusi',
    'PDKN',
    'Nasionalis-Religius & Demokrasi Konstitusional',
    'OPOSISI',
    'Mayor (Purn.) Agus Harimurti Kusumo',
    44,
    580,
    'indigo',
    'Menempatkan diri sebagai penyeimbang kritis di DPR RI dengan fokus pada pengawasan belanja APBN, perlindungan daya beli kelas menengah, dan profesionalisme aparat.',
    7
);

-- =============================================================================
-- DATA SEEDING: UNDANG-UNDANG & REGULASI AKTIF (active_laws)
-- =============================================================================

INSERT INTO `active_laws` (
    `law_number`, `law_type`, `title`, `category`,
    `summary`, `enacted_by`, `enacted_date`, `status`
) VALUES
(
    'UU No. 1 Tahun 2026',
    'UNDANG-UNDANG',
    'Undang-Undang tentang Anggaran Pendapatan dan Belanja Negara Tahun Anggaran 2026',
    'Ekonomi & Keuangan Negara',
    'Mengatur postur pendapatan negara sebesar Rp 2.984,5 Triliun, alokasi belanja prioritas pendidikan 20%, subsidi energi tepat sasaran, dan dana transfer ke daerah.',
    'Presiden RI & DPR RI',
    '2026-01-15',
    'BERLAKU_AKTIF'
),
(
    'UU No. 4 Tahun 2026',
    'UNDANG-UNDANG',
    'Undang-Undang tentang Perlindungan Data Pribadi dan Ketahanan Siber Nasional',
    'Keamanan & Teknologi Informasi',
    'Mewajibkan seluruh penyelenggara sistem elektronik pemerintah maupun swasta menerapkan enkripsi standar nasional serta membentuk Badan Pengawas Data Pribadi.',
    'Presiden RI & DPR RI',
    '2026-04-10',
    'BERLAKU_AKTIF'
),
(
    'UU No. 7 Tahun 2026',
    'UNDANG-UNDANG',
    'Undang-Undang tentang Hilirisasi Industri Strategis dan Kemandirian Energi',
    'Industri & Sumber Daya Alam',
    'Melarang ekspor bijih mineral mentah secara bertahap dan memberikan insentif fiskal bagi pembangunan fasilitas pengolahan dalam negeri.',
    'Presiden RI & DPR RI',
    '2026-07-22',
    'BERLAKU_AKTIF'
),
(
    'UU No. 9 Tahun 2026',
    'UNDANG-UNDANG',
    'Undang-Undang tentang Pemilihan Umum Nasional dan Kepartaian',
    'Politik & Pemerintahan',
    'Mengatur ambang batas parlemen (parliamentary threshold) sebesar 4%, mekanisme pemilihan presiden secara langsung, serta transparansi dana kampanye partai politik.',
    'Presiden RI & DPR RI',
    '2026-09-05',
    'BERLAKU_AKTIF'
),
(
    'PERPRES-14/2026',
    'PERPRES',
    'Peraturan Presiden tentang Percepatan Transformasi Layanan Publik Digital dan Satu Data Kependudukan',
    'Pemerintahan & Birokrasi',
    'Mengintegrasikan identitas tunggal warga (E-KTP Nasional) dengan layanan kesehatan, perizinan usaha, dan bantuan sosial.',
    'Presiden Republik Indonesia',
    '2026-10-28',
    'BERLAKU_AKTIF'
);

-- =============================================================================
-- DATA SEEDING: BERITA NASIONAL (national_news)
-- =============================================================================

INSERT INTO `national_news` (
    `news_code`, `category`, `headline`, `summary`, `author_name`, `published_at`
) VALUES
(
    'BERITA-2026-001',
    'KEPRESIDENAN',
    'Presiden Pratama Wijaya Resmikan Integrasi Layanan Publik Digital Nasional di Istana Merdeka',
    'Melalui penerbitan Perpres Nomor 14 Tahun 2026, pemerintah menargetkan pemangkasan waktu pengurusan izin usaha dan administrasi kependudukan hingga 70 persen di seluruh provinsi.',
    'Biro Pers Sekretariat Presiden',
    '2026-10-28 14:30:00'
),
(
    'BERITA-2026-002',
    'EKONOMI NASIONAL',
    'Ekonomi Nusantara Tumbuh 5,42 Persen pada Kuartal IV, Neraca Perdagangan Kembali Cetak Surplus',
    'Kementerian Keuangan dan Bank Sentral melaporkan pertumbuhan ekonomi nasional melampaui proyeksi konsensus, didorong ekspansi sektor pengolahan nikel dan stabilitas konsumsi domestik.',
    'Kantor Berita Nasional Antara-RP',
    '2026-10-29 11:20:00'
),
(
    'BERITA-2026-003',
    'PARLEMEN DPR',
    'Komisi I dan Komisi XI DPR RI Gelar Rapat Kerja Gabungan Bahas Evaluasi Anggaran Pertahanan Siber',
    'Koalisi pemerintah (386 kursi) dan fraksi oposisi (194 kursi) menyepakati penambahan alokasi anggaran pengamanan pusat data nasional pada masa sidang paripurna bulan ini.',
    'Sekretariat Jenderal DPR RI',
    '2026-10-30 09:45:00'
),
(
    'BERITA-2026-004',
    'PEMERINTAHAN',
    'Wakil Presiden Mahendra Kusuma Pimpin Rapat Koordinasi Evaluasi Kinerja Kementerian di Istana Wapres',
    'Wakil Presiden menerbitkan Instruksi Wapres Nomor 04/2026 yang mewajibkan seluruh kementerian melaporkan capaian serapan anggaran dan target pengentasan kemiskinan ekstrem setiap kuartal.',
    'Sekretariat Wakil Presiden',
    '2026-10-30 15:10:00'
);

-- =============================================================================
-- DATA SEEDING: TELEMETRI UMUM (server_telemetry)
-- =============================================================================

INSERT INTO `server_telemetry` (`metric_key`, `metric_value`, `is_current`) VALUES
('current_president', 'Ir. H. Pratama Wijaya, M.P.A.', 1),
('current_vice_president', 'Prof. Dr. H. Mahendra Kusuma, S.H., LL.M.', 1),
('parliament_session_status', 'Masa Persidangan II Tahun Sidang 2026–2027', 1);
