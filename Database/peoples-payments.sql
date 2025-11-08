-- Personel Ödemeleri Tablosu
CREATE TABLE IF NOT EXISTS `personel_odemeleri` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `personel_id` BIGINT UNSIGNED NOT NULL,
    `odeme_tarihi` DATE NOT NULL COMMENT 'Ödeme tarihi',
    `tutar` DECIMAL(12, 2) NOT NULL COMMENT 'Ödeme tutarı',
    `odeme_turu` VARCHAR(50) NOT NULL COMMENT 'Ödeme Türü: salary, bonus, advance, commission, incentive, other',
    `aciklama` TEXT COMMENT 'Ödeme açıklaması',
    `yonetici_notu` TEXT COMMENT 'Yalnızca yönetici görecek notlar',
    `kayit_yapan_id` BIGINT UNSIGNED COMMENT 'Ödemeyi kaydetmiş kullanıcı',
    `olusturulma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Kayıt tarihi',
    `guncelleme_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncellenme tarihi',
    `silinme_tarihi` TIMESTAMP NULL COMMENT 'Silinme tarihi (soft delete)',
    
    CONSTRAINT `fk_personel_odemeleri_personel` FOREIGN KEY (`personel_id`) REFERENCES `personel` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_personel_odemeleri_user` FOREIGN KEY (`kayit_yapan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    
    INDEX `idx_personel_id` (`personel_id`),
    INDEX `idx_odeme_tarihi` (`odeme_tarihi`),
    INDEX `idx_odeme_turu` (`odeme_turu`),
    INDEX `idx_olusturulma_tarihi` (`olusturulma_tarihi`),
    INDEX `idx_silinme_tarihi` (`silinme_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Personele yapılan ödemelerin kaydı';
