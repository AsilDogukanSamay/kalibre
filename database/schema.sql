-- ============================================================
-- Kalibre - iletisim modulu semasi
-- MySQL 8.0+ / MariaDB 10.4+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kalibre`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `kalibre`;

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`  VARCHAR(120)  NOT NULL,
    `email`      VARCHAR(180)  NOT NULL,
    `phone`      VARCHAR(32)   NOT NULL,
    `message`    TEXT          NOT NULL,
    `ip_address` VARCHAR(45)   NOT NULL DEFAULT '',   -- IPv6 icin 45 karakter
    `user_agent` VARCHAR(255)  NOT NULL DEFAULT '',
    `status`     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    -- KVKK ispat yukumlulugu: onayin hangi metin surumune, ne zaman
    -- verildigi kaydin kendisinde durur.
    `consent_at`      DATETIME     NULL DEFAULT NULL,
    `consent_version` VARCHAR(16)  NOT NULL DEFAULT '',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_email`      (`email`),
    -- Spam freni sorgusu (ip + zaman) bu bilesik indeksi kullanir
    KEY `idx_ip_created` (`ip_address`, `created_at`),
    KEY `idx_status`     (`status`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
