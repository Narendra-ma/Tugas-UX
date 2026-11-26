-- Garasi Surya: skema + data awal (MySQL 8 / InnoDB)
-- Hanya satu garasi (Garasi Surya, milik Ibu Surya, 6 slot). Sumber data: laporan UX kelompok 10.
-- Pakai SALAH SATU: file ini ATAU `php artisan migrate --seed`. Jangan dua-duanya.
-- Impor: mysql -u root -p < database/garasi_surya.sql
-- Kalau impor lewat file ini, di .env pakai QUEUE_CONNECTION=sync (tabel jobs tidak dibuat).

CREATE DATABASE IF NOT EXISTS garasi_surya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE garasi_surya;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS blacklists, documents, bookings, slots, garages,
    cache_locks, cache, sessions, password_reset_tokens, users;
SET FOREIGN_KEY_CHECKS = 1;

-- akun: role 'owner' (Ibu Surya) atau 'tenant' (penyewa)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'tenant',
    phone VARCHAR(20) NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    KEY cache_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    KEY cache_locks_expiration_index (expiration)
) ENGINE=InnoDB;

-- garasi dan tarif (tarif rupiah bulat)
CREATE TABLE garages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    description TEXT NULL,
    size_note VARCHAR(100) NULL,
    price_daily INT UNSIGNED NOT NULL,
    price_weekly INT UNSIGNED NOT NULL,
    price_monthly INT UNSIGNED NOT NULL,
    photo_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY garages_is_active_city_index (is_active, city),
    KEY garages_price_monthly_index (price_monthly),
    CONSTRAINT garages_owner_id_fk FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- satu baris per tempat parkir (A1..A6); dinonaktifkan, tidak dihapus
CREATE TABLE slots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    garage_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(10) NOT NULL,
    note VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY slots_garage_id_code_unique (garage_id, code),
    CONSTRAINT slots_garage_id_fk FOREIGN KEY (garage_id) REFERENCES garages (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- pengajuan dan sewa. Status yang menahan slot: menunggu_konfirmasi, menunggu_bayar, dibayar
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    slot_id BIGINT UNSIGNED NOT NULL,
    car_type VARCHAR(100) NOT NULL,
    plate VARCHAR(15) NOT NULL,
    period VARCHAR(10) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price INT UNSIGNED NOT NULL,
    status VARCHAR(25) NOT NULL DEFAULT 'menunggu_konfirmasi',
    expires_at TIMESTAMP NULL,
    payment_proof_path VARCHAR(255) NULL,
    paid_at TIMESTAMP NULL,
    reject_reason VARCHAR(255) NULL,
    terminate_reason VARCHAR(255) NULL,
    arrears_amount INT UNSIGNED NOT NULL DEFAULT 0,
    agreed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY bookings_slot_id_start_date_end_date_index (slot_id, start_date, end_date),
    KEY bookings_status_expires_at_index (status, expires_at),
    KEY bookings_plate_index (plate),
    CONSTRAINT bookings_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT bookings_slot_id_fk FOREIGN KEY (slot_id) REFERENCES slots (id) ON DELETE CASCADE,
    CONSTRAINT bookings_dates_chk CHECK (end_date >= start_date)
) ENGINE=InnoDB;

-- surat perjanjian, bukti bayar, identitas
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(20) NOT NULL,
    path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY documents_booking_id_category_index (booking_id, category),
    CONSTRAINT documents_booking_id_fk FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- catatan khusus penyewa bermasalah (plat / HP yang sama diberi tanda merah saat mengajukan lagi)
CREATE TABLE blacklists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    plate VARCHAR(15) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    arrears_amount INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY blacklists_owner_id_plate_index (owner_id, plate),
    KEY blacklists_owner_id_phone_index (owner_id, phone),
    CONSTRAINT blacklists_owner_id_fk FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT blacklists_booking_id_fk FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- data awal ----------
-- Semua kata sandi akun contoh: password (ganti sebelum dipublikasikan)
SET @pw = '$2y$12$ngsqjJDK9W/.E9dqzhv0nOLxBzaVdd/fw24oJ5q7NVviCu.nS8LEy';

INSERT INTO users (id, name, email, role, phone, password, created_at, updated_at) VALUES
(1, 'Ibu Surya', 'surya@garasi.test', 'owner', '081234500001', @pw, NOW(), NOW()),
(2, 'Dimas', 'dimas@example.test', 'tenant', '081234500003', @pw, NOW(), NOW()),
(3, 'Budi', 'budi@example.test', 'tenant', '081234500004', @pw, NOW(), NOW()),
(4, 'Rudi', 'rudi@example.test', 'tenant', '081234500005', @pw, NOW(), NOW()),
(5, 'Penyewa Contoh Bermasalah', 'kabur@example.test', 'tenant', '081234500009', @pw, NOW(), NOW());

-- Tarif hanya perkiraan (wawancara cuma menyebut kisaran Rp 300.000 - 750.000 per bulan)
INSERT INTO garages (id, owner_id, name, city, address, description, size_note,
                     price_daily, price_weekly, price_monthly, is_active, created_at, updated_at) VALUES
(1, 1, 'Garasi Surya', 'Karangasem',
 'Dusun Bangbang, Desa Rendang, Kec. Rendang, Kab. Karangasem, Bali',
 'Garasi pribadi yang dikelola langsung oleh pemilik. Bersih dan nyaman.',
 'Muat 6 mobil', 30000, 150000, 500000, 1, NOW(), NOW());

INSERT INTO slots (id, garage_id, code, created_at, updated_at) VALUES
(1, 1, 'A1', NOW(), NOW()), (2, 1, 'A2', NOW(), NOW()), (3, 1, 'A3', NOW(), NOW()),
(4, 1, 'A4', NOW(), NOW()), (5, 1, 'A5', NOW(), NOW()), (6, 1, 'A6', NOW(), NOW());

INSERT INTO bookings (id, user_id, slot_id, car_type, plate, period, start_date, end_date, total_price,
                      status, expires_at, paid_at, agreed_at, created_at, updated_at) VALUES
-- sewa aktif (Dimas, A1)
(1, 2, 1, 'Avanza', 'DK 1111 AA', 'monthly', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY),
 500000, 'dibayar', NULL, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY), NOW(), NOW()),
-- sudah lewat jatuh tempo (Budi, A2)
(2, 3, 2, 'Brio', 'DK 2222 BB', 'monthly', DATE_SUB(CURDATE(), INTERVAL 35 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY),
 500000, 'dibayar', NULL, DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 36 DAY), NOW(), NOW()),
-- pengajuan baru belum dijawab (Rudi, A3), batal otomatis 24 jam
(3, 4, 3, 'Xenia', 'DK 3333 CC', 'monthly', DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 32 DAY),
 500000, 'menunggu_konfirmasi', DATE_ADD(NOW(), INTERVAL 24 HOUR), NULL, NOW(), NOW(), NOW());

-- kasus penyewa yang marah saat diingatkan lalu keluar tanpa melunasi
INSERT INTO blacklists (owner_id, name, phone, plate, reason, arrears_amount, created_at, updated_at) VALUES
(1, 'Penyewa Contoh Bermasalah', '081234500009', 'DK 9999 ZZ',
 'Marah saat diingatkan jatuh tempo dan keluar tanpa melunasi', 500000, NOW(), NOW());
