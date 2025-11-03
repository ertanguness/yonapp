-- Tahsilat Havuzu tablosuna Banka API için gerekli kolonları ekle
-- 2024 - Banka API Entegrasyon Güncellemesi

USE yonapp;

-- Site ID kolonu ekle (varsa atla)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `site_id` INT(11) NULL COMMENT 'Site ID' AFTER `id`;

-- Kasa ID kolonu ekle (hangi banka hesabından geldiği)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `kasa_id` INT(11) NULL COMMENT 'Banka hesabı (kasa) ID' AFTER `site_id`;

-- Daire ID kolonu ekle (manuel veya otomatik eşleşen daire)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `daire_id` INT(11) NULL COMMENT 'Eşleşen daire ID' AFTER `kasa_id`;

-- İşlenen tutar kolonu ekle (kısmi ödemeler için)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `islenen_tutar` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'İşlenen tutar' AFTER `tahsilat_tutari`;

-- Kalan tutar kolonu ekle (kısmi ödemeler için)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `kalan_tutar` DECIMAL(12,2) NULL COMMENT 'Kalan tutar' AFTER `islenen_tutar`;

-- Hareket yönü kolonu ekle (Gelir/Gider)
ALTER TABLE `tahsilat_havuzu` 
ADD COLUMN IF NOT EXISTS `hareket_yonu` ENUM('Gelir','Gider') DEFAULT 'Gelir' COMMENT 'İşlem yönü' AFTER `kalan_tutar`;

-- Banka referans numarası kolonu ekle (zaten 'referans_no' var ama rename edelim)
ALTER TABLE `tahsilat_havuzu` 
CHANGE COLUMN IF EXISTS `referans_no` `banka_ref_no` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Banka referans numarası';

-- Created_at kolonu ekle (zaten 'olusturulma_tarihi' var ama standart isim kullan)
ALTER TABLE `tahsilat_havuzu` 
CHANGE COLUMN IF EXISTS `olusturulma_tarihi` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Kayıt oluşturulma tarihi';

-- Updated_at kolonu ekle (zaten 'guncelleme_tarihi' var)
ALTER TABLE `tahsilat_havuzu` 
CHANGE COLUMN IF EXISTS `guncelleme_tarihi` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Son güncelleme tarihi';

-- Foreign key'ler ekle
ALTER TABLE `tahsilat_havuzu` 
ADD CONSTRAINT IF NOT EXISTS `fk_tahsilat_havuzu_site` 
FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE CASCADE;

ALTER TABLE `tahsilat_havuzu` 
ADD CONSTRAINT IF NOT EXISTS `fk_tahsilat_havuzu_kasa` 
FOREIGN KEY (`kasa_id`) REFERENCES `kasa`(`id`) ON DELETE SET NULL;

ALTER TABLE `tahsilat_havuzu` 
ADD CONSTRAINT IF NOT EXISTS `fk_tahsilat_havuzu_daire` 
FOREIGN KEY (`daire_id`) REFERENCES `daireler`(`id`) ON DELETE SET NULL;

-- Index'ler ekle
CREATE INDEX IF NOT EXISTS `idx_site_id` ON `tahsilat_havuzu` (`site_id`);
CREATE INDEX IF NOT EXISTS `idx_kasa_id` ON `tahsilat_havuzu` (`kasa_id`);
CREATE INDEX IF NOT EXISTS `idx_daire_id` ON `tahsilat_havuzu` (`daire_id`);
CREATE INDEX IF NOT EXISTS `idx_banka_ref_no` ON `tahsilat_havuzu` (`banka_ref_no`);
CREATE INDEX IF NOT EXISTS `idx_islem_tarihi` ON `tahsilat_havuzu` (`islem_tarihi`);
CREATE INDEX IF NOT EXISTS `idx_hareket_yonu` ON `tahsilat_havuzu` (`hareket_yonu`);
CREATE INDEX IF NOT EXISTS `idx_kalan_tutar` ON `tahsilat_havuzu` (`kalan_tutar`);

-- Kaynak kolonu güncelle (API kaynaklarını ekle)
ALTER TABLE `tahsilat_havuzu` 
MODIFY COLUMN IF EXISTS `kaynak` VARCHAR(20) NOT NULL DEFAULT 'api' COMMENT 'Kaynak (excel, api, bank_csv, manuel vb)';

SELECT 'Tahsilat havuzu tablosu banka API entegrasyonu için güncellendi!' AS status;
