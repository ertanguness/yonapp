-- Duyurular tablosu oluşturma
-- Tarih: 2025-11-29

CREATE TABLE IF NOT EXISTS `duyurular` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `baslik` VARCHAR(255) NOT NULL,
  `icerik` TEXT NOT NULL,
  `baslangic_tarihi` DATE NULL,
  `bitis_tarihi` DATE NULL,
  `durum` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `target_type` VARCHAR(32) NULL,
  `target_ids` TEXT NULL,
  `olusturulma_tarihi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `silinme_tarihi` DATETIME NULL,
  `silen_kullanici` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_duyurular_durum` (`durum`),
  INDEX `idx_duyurular_baslangic` (`baslangic_tarihi`),
  INDEX `idx_duyurular_bitis` (`bitis_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;