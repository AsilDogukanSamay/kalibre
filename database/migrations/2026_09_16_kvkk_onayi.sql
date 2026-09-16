-- Mevcut kurulumlari guncellemek icin. Yeni kurulumda schema.sql yeterlidir.
-- Calistirma:  npm run db:sql < database/migrations/2026_09_16_kvkk_onayi.sql

ALTER TABLE `contact_messages`
    ADD COLUMN `consent_at`      DATETIME    NULL DEFAULT NULL AFTER `status`,
    ADD COLUMN `consent_version` VARCHAR(16) NOT NULL DEFAULT '' AFTER `consent_at`;
