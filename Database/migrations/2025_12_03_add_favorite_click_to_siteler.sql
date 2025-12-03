-- Favori ve tıklama sayaç alanlarını ekler
ALTER TABLE `siteler`
  ADD COLUMN `favori_mi` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Favori, 0: Değil' AFTER `aktif_mi`,
  ADD COLUMN `click_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Seçilme/tıklanma sayısı' AFTER `favori_mi`;

-- İndeksler
ALTER TABLE `siteler`
  ADD INDEX `idx_favori_mi` (`favori_mi`),
  ADD INDEX `idx_click_count` (`click_count`);
