-- --------------------------------------------------------
-- Sunucu:                       127.0.0.1
-- Sunucu sürümü:                10.4.32-MariaDB - mariadb.org binary distribution
-- Sunucu İşletim Sistemi:       Win64
-- HeidiSQL Sürüm:               12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- yonapp için veritabanı yapısı dökülüyor
CREATE DATABASE IF NOT EXISTS `yonapp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `yonapp`;

-- tablo yapısı dökülüyor yonapp.tahsilatlar
DROP TABLE IF EXISTS `tahsilatlar`;
CREATE TABLE IF NOT EXISTS `tahsilatlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borc_id` int(11) DEFAULT NULL,
  `tahsilat_tipi` varchar(50) DEFAULT NULL,
  `kisi_id` int(11) DEFAULT NULL,
  `tahsilat_onay_id` int(11) DEFAULT NULL,
  `kasa_id` int(11) DEFAULT NULL,
  `daire_id` int(11) DEFAULT NULL,
  `tutar` decimal(10,2) DEFAULT NULL,
  `islem_tarihi` datetime DEFAULT NULL,
  `makbuz_no` varchar(100) DEFAULT NULL,
  `taksit_no` int(11) DEFAULT NULL,
  `toplam_taksit` int(11) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `delete_user` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `create_user` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `update_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tahsilatlar_kisiler` (`kisi_id`) USING BTREE,
  KEY `islem_tarihi` (`islem_tarihi`),
  CONSTRAINT `FK_tahsilatlar_kisiler` FOREIGN KEY (`kisi_id`) REFERENCES `kisiler` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=743 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- yonapp.tahsilatlar: ~13 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `tahsilatlar`;
INSERT INTO `tahsilatlar` (`id`, `borc_id`, `tahsilat_tipi`, `kisi_id`, `tahsilat_onay_id`, `kasa_id`, `daire_id`, `tutar`, `islem_tarihi`, `makbuz_no`, `taksit_no`, `toplam_taksit`, `aciklama`, `deleted_at`, `delete_user`, `created_at`, `create_user`, `updated_at`, `update_user`) VALUES
	(670, NULL, 'AİDAT', 179, NULL, NULL, 179, 2260.00, '2025-05-26 15:41:30', NULL, NULL, NULL, 'NUĞMAN EMİR AKGÜN*0010*c5 blok no 19 azime akgün*4573006598*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(671, NULL, 'AİDAT', 161, NULL, NULL, 161, 870.67, '2025-05-26 14:02:40', NULL, NULL, NULL, 'MİMTAŞ MİMARLIK İNŞAAT MÜHENDİSLİK TAAHHÜT MAKİNA SANAYİ VE TİCARET LİMİTED ŞİRKETİ*0205*C5 D1 ŞUBAT AİDAT ÖDEMESİ*9365833*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(673, NULL, 'AİDAT', 25, NULL, NULL, 25, 1000.00, '2025-05-26 13:53:52', NULL, NULL, NULL, 'SAMİ ERGİN*0010*SAMİERGİNB1D5 MAYIS AYI AİDATI VE EK ÖDEMEASANSÖR BAKIM ÜCRETİ*4572147426*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(674, NULL, 'AİDAT', 81, NULL, NULL, 81, 860.00, '2025-05-26 12:49:36', NULL, NULL, NULL, 'Zekeriya Kurt*0111*C1 DAiRE 1 MAYIS - asansör arizasi*753392602*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(675, NULL, 'AİDAT', 113, NULL, NULL, 113, 1060.00, '2025-05-26 11:45:17', NULL, NULL, NULL, 'OGÜN PEÇENEK*0205*C2. D.13 asansör ücreti*9586641*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(676, NULL, 'AİDAT', 65, NULL, NULL, 65, 2060.00, '2025-05-26 11:19:51', NULL, NULL, NULL, 'B3 BLOK DAİRE5*EDA ÇİÇEK*H2505707539207', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(677, NULL, 'DEMİRBAŞ', 113, NULL, NULL, 37, 600.00, '2025-05-26 10:41:41', NULL, NULL, NULL, 'MEHMET SÖNMEZ*0046*b1 blok d 17 demirbaş ihtiyaçları Ayşe sönmez*1887458*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, '2025-05-29 11:20:33', NULL),
	(678, NULL, 'ASANSOR', 9, NULL, NULL, 9, 1060.00, '2025-05-26 10:31:12', NULL, NULL, NULL, 'üsküb evleri A1-DAİRE 9 ASANSÖR BAKIMI *GÜNEŞ ÖZÇİL*H2505707237626', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(679, NULL, 'DEMİRBAŞ', 3, NULL, NULL, 3, 1060.00, '2025-05-26 10:22:01', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, '2025-05-29 08:31:39', NULL, NULL, NULL),
	(737, NULL, 'DEMİRBAŞ', NULL, 12, NULL, NULL, 1060.00, '2025-05-30 13:17:23', NULL, NULL, NULL, 'NUĞMAN EMİR AKGÜN*0010*c5 blok no 19 azime akgün*4573006598*FAST', NULL, NULL, '2025-05-30 14:17:23', NULL, NULL, NULL),
	(738, NULL, 'AİDAT', NULL, 12, NULL, NULL, 600.00, '2025-05-30 13:17:29', NULL, NULL, NULL, 'NUĞMAN EMİR AKGÜN*0010*c5 blok no 19 azime akgün*4573006598*FAST', NULL, NULL, '2025-05-30 14:17:29', NULL, NULL, NULL),
	(741, NULL, 'DEMİRBAŞ', NULL, 16, NULL, NULL, 860.00, '2025-05-30 13:20:39', NULL, NULL, NULL, 'Zekeriya Kurt*0111*C1 DAiRE 1 MAYIS - asansör arizasi*753392602*FAST', NULL, NULL, '2025-05-30 14:20:39', NULL, NULL, NULL),
	(742, NULL, 'DEMİRBAŞ', NULL, 17, NULL, NULL, 1060.00, '2025-05-30 13:22:50', NULL, NULL, NULL, 'OGÜN PEÇENEK*0205*C2. D.13 asansör ücreti*9586641*FAST', NULL, NULL, '2025-05-30 14:22:50', NULL, NULL, NULL);

-- tablo yapısı dökülüyor yonapp.tahsilat_havuzu
DROP TABLE IF EXISTS `tahsilat_havuzu`;
CREATE TABLE IF NOT EXISTS `tahsilat_havuzu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
  `tahsilat_tutari` decimal(12,2) NOT NULL COMMENT 'Ödeme tutarı',
  `islem_tarihi` varchar(50) NOT NULL DEFAULT '' COMMENT 'Ödeme tarihi',
  `referans_no` varchar(50) DEFAULT NULL COMMENT 'Banka/ödeme referans numarası',
  `aciklama` text DEFAULT NULL COMMENT 'Ödeme açıklaması',
  `para_birimi` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
  `cikarilan_blok_kodu` varchar(10) DEFAULT NULL COMMENT 'Açıklamadan çıkarılan blok kodu',
  `cikarilan_daire_no` varchar(10) DEFAULT NULL COMMENT 'Açıklamadan çıkarılan daire numarası',
  `cikarilan_kisi_adi` varchar(100) DEFAULT NULL COMMENT 'Açıklamadan çıkarılan kişi adı',
  `ham_aciklama` text DEFAULT NULL COMMENT 'Orjinal açıklama metni',
  `ham_veri` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Excel satırının tam ham verisi' CHECK (json_valid(`ham_veri`)),
  `eslesen_daire_id` int(11) DEFAULT NULL COMMENT 'Eşleşen daire ID',
  `eslesen_kisi_id` int(11) DEFAULT NULL COMMENT 'Eşleşen kişi ID',
  `eslesme_durumu` enum('eslesmedi','otomatik_eslesti','manuel_eslesti') DEFAULT 'eslesmedi',
  `eslesme_tarihi` datetime DEFAULT NULL COMMENT 'Eşleşme tarihi',
  `kaynak` varchar(20) NOT NULL COMMENT 'Kaynak (excel,api,bank_csv vb)',
  `dosya_adi` varchar(100) DEFAULT NULL COMMENT 'Yüklenen dosya adı',
  `satir_no` int(11) DEFAULT NULL COMMENT 'Excel satır numarası',
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturulma tarihi',
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Son güncelleme tarihi',
  PRIMARY KEY (`id`),
  KEY `eslesen_daire_id` (`eslesen_daire_id`),
  KEY `eslesen_kisi_id` (`eslesen_kisi_id`),
  KEY `referans_no` (`referans_no`),
  KEY `tahsilat_tarihi` (`islem_tarihi`) USING BTREE,
  CONSTRAINT `tahsilat_havuzu_ibfk_1` FOREIGN KEY (`eslesen_daire_id`) REFERENCES `daireler` (`id`),
  CONSTRAINT `tahsilat_havuzu_ibfk_2` FOREIGN KEY (`eslesen_kisi_id`) REFERENCES `kisiler` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=788 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Eşleşmeyen ödeme kayıtlarının bekletildiği havuz tablosu';

-- yonapp.tahsilat_havuzu: ~41 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `tahsilat_havuzu`;
INSERT INTO `tahsilat_havuzu` (`id`, `tahsilat_tutari`, `islem_tarihi`, `referans_no`, `aciklama`, `para_birimi`, `cikarilan_blok_kodu`, `cikarilan_daire_no`, `cikarilan_kisi_adi`, `ham_aciklama`, `ham_veri`, `eslesen_daire_id`, `eslesen_kisi_id`, `eslesme_durumu`, `eslesme_tarihi`, `kaynak`, `dosya_adi`, `satir_no`, `olusturulma_tarihi`, `guncelleme_tarihi`) VALUES
	(747, 1660.00, '19700101', '14320244598320250526185026821', 'Daire Kodu eşleşmedi: A1D1', 'TRY', NULL, NULL, NULL, 'NEZİHA GÜL*0046**3069951*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(748, 600.00, '19700101', '14320244598320250526161731619', 'Daire Kodu eşleşmedi: A1D2', 'TRY', NULL, NULL, NULL, 'YONCA BANU YÜKSEL*0205*C5 Daire 9 Gamze Kiraz Özyağcı MAYIS Demirbaş Bedeli*9132885*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(749, 2260.00, '19700101', '14320244598320250526154130698', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'NUĞMAN EMİR AKGÜN*0010*c5 blok no 19 azime akgün*4573006598*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(750, 1060.00, '19700101', '14320244598320250526150252610', 'Bilgi var SİBEL GÜR*0067*B1 DAİRE 6 RAHİME AK Mayis 2025 ASANSOR BAKIM UCRETI*1812160669*FAST', 'TRY', NULL, NULL, NULL, 'SİBEL GÜR*0067*B1 DAİRE 6 RAHİME AK Mayis 2025 ASANSOR BAKIM UCRETI*1812160669*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(751, 1075.00, '19700101', '14320244598320250526145304022', 'Bilgi var İSMAİL TOPÇU*0046*Mayis ayi demirbas*2548756*FAST', 'TRY', NULL, NULL, NULL, 'İSMAİL TOPÇU*0046*Mayis ayi demirbas*2548756*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(752, 1060.00, '19700101', '14320244598320250526140252878', 'Bilgi var c5 16 numara asansör bakım ücreti*YURDAGÜL KALYON*H2505708525678', 'TRY', NULL, NULL, NULL, 'c5 16 numara asansör bakım ücreti*YURDAGÜL KALYON*H2505708525678', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(753, 87067.00, '19700101', '14320244598320250526140240829', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'MİMTAŞ MİMARLIK İNŞAAT MÜHENDİSLİK TAAHHÜT MAKİNA SANAYİ VE TİCARET LİMİTED ŞİRKETİ*0205*C5 D1 ŞUBAT AİDAT ÖDEMESİ*9365833*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(754, 1660.00, '19700101', '14320244598320250526140140522', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'FATİH KALAYCI*0012*C2- Daire:15 Canan SUBAŞI KALAYCI Demirbaş ve Asansör ücreti*886249885*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(755, 1000.00, '19700101', '14320244598320250526135352564', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'SAMİ ERGİN*0010*SAMİERGİNB1D5 MAYIS AYI AİDATI VE EK ÖDEMEASANSÖR BAKIM ÜCRETİ*4572147426*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(756, 860.00, '19700101', '14320244598320250526124936189', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'Zekeriya Kurt*0111*C1 DAiRE 1 MAYIS - asansör arizasi*753392602*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(757, 600.00, '19700101', '14320244598320250526120214880', 'Bilgi var MEHMET YILDIZ*0205*C2 BLOK DAİRE 18 2025 YILI DEMİRBAŞ BEDELİ 1. TAKSİT *9559967*FAST', 'TRY', NULL, NULL, NULL, 'MEHMET YILDIZ*0205*C2 BLOK DAİRE 18 2025 YILI DEMİRBAŞ BEDELİ 1. TAKSİT *9559967*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(758, 1060.00, '19700101', '14320244598320250526114517848', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'OGÜN PEÇENEK*0205*C2. D.13 asansör ücreti*9586641*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(759, 2260.00, '19700101', '14320244598320250526114309596', 'Bilgi var YUNUS EMRE ÇARHACIOĞLU*0062**9725000113*FAST', 'TRY', NULL, NULL, NULL, 'YUNUS EMRE ÇARHACIOĞLU*0062**9725000113*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(760, 2060.00, '19700101', '14320244598320250526111951345', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'B3 BLOK DAİRE5*EDA ÇİÇEK*H2505707539207', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(761, 600.00, '19700101', '14320244598320250526104141850', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'MEHMET SÖNMEZ*0046*b1 blok d 17 demirbaş ihtiyaçları Ayşe sönmez*1887458*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(762, 1060.00, '19700101', '14320244598320250526103112190', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'üsküb evleri A1-DAİRE 9 ASANSÖR BAKIMI *GÜNEŞ ÖZÇİL*H2505707237626', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(763, 1060.00, '19700101', '14320244598320250526102201155', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(764, 1060.00, '19700101', '14320244598320250526102201155', 'Daire Kodu eşleşmedi: A1D4', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(765, 1060.00, '19700101', '14320244598320250526102201155', 'SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'site_id\' cannot be null', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(766, 1060.00, '19700101', '14320244598320250526102201155', 'Daire Kodu eşleşmedi: A1D6', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(767, 2060.00, '19700101', '14320244598320250526094920623', 'Bilgi var FİLİZ KIYCI*0010*aidat ve asansor B1 daire 16 mayis ayi*4570667321*FAST', 'TRY', NULL, NULL, NULL, 'FİLİZ KIYCI*0010*aidat ve asansor B1 daire 16 mayis ayi*4570667321*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(768, 415.00, '19700101', '14320244598320250526092540798', 'Bilgi var B3 Blok Daire 10- Mayıs Aidat*DERYA AKTAŞ*H2505706881571', 'TRY', NULL, NULL, NULL, 'B3 Blok Daire 10- Mayıs Aidat*DERYA AKTAŞ*H2505706881571', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(769, 400.00, '19700101', '14320244598320250526083150437', 'Bilgi var RAHMİ KOCABAŞ*0015*RAHMİ KOCABAŞ C4 DAİRE:12 AİDAT ÖDEMESİ*7605700', 'TRY', NULL, NULL, NULL, 'RAHMİ KOCABAŞ*0015*RAHMİ KOCABAŞ C4 DAİRE:12 AİDAT ÖDEMESİ*7605700', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(770, 400.00, '19700101', '14320244598320250526052000880', 'Bilgi var FATMA ÇALIK*0010*FATMA ÇALIK AİDAT 8/12 KRED / 144845715 / 24 Gönderen Hesap:65311315 Al.T*4570299515*FAST', 'TRY', NULL, NULL, NULL, 'FATMA ÇALIK*0010*FATMA ÇALIK AİDAT 8/12 KRED / 144845715 / 24 Gönderen Hesap:65311315 Al.T*4570299515*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(771, 400.00, '19700101', '14320244598320250526052000304', 'Bilgi var TÜRKAN KIYICI*0010*TÜRKAN KIYICI C3 16 4/11 KRED / 148796644 / 4 Gönderen Hesap:5776253 Al.Tel*4570299505*FAST', 'TRY', NULL, NULL, NULL, 'TÜRKAN KIYICI*0010*TÜRKAN KIYICI C3 16 4/11 KRED / 148796644 / 4 Gönderen Hesap:5776253 Al.Tel*4570299505*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(772, 1600.00, '19700101', '14320244598320250525233924022', 'Bilgi var Üsküp evleri sitesi B3 blok Dükkan 20. şubat-mart-nisan-mayıs aylarına ait aidat ödemesidir.*NURCAN ÖZCAN SEVİNDİK*H2505705650493', 'TRY', NULL, NULL, NULL, 'Üsküp evleri sitesi B3 blok Dükkan 20. şubat-mart-nisan-mayıs aylarına ait aidat ödemesidir.*NURCAN ÖZCAN SEVİNDİK*H2505705650493', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:13', '2025-05-29 10:07:13'),
	(773, 1660.00, '19700101', '14320244598320250526185026821', 'Daire Kodu eşleşmedi: A1D1', 'TRY', NULL, NULL, NULL, 'NEZİHA GÜL*0046**3069951*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(774, 600.00, '19700101', '14320244598320250526161731619', 'Daire Kodu eşleşmedi: A1D2', 'TRY', NULL, NULL, NULL, 'YONCA BANU YÜKSEL*0205*C5 Daire 9 Gamze Kiraz Özyağcı MAYIS Demirbaş Bedeli*9132885*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(775, 1060.00, '19700101', '14320244598320250526150252610', 'Bilgi var SİBEL GÜR*0067*B1 DAİRE 6 RAHİME AK Mayis 2025 ASANSOR BAKIM UCRETI*1812160669*FAST', 'TRY', NULL, NULL, NULL, 'SİBEL GÜR*0067*B1 DAİRE 6 RAHİME AK Mayis 2025 ASANSOR BAKIM UCRETI*1812160669*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(776, 1075.00, '19700101', '14320244598320250526145304022', 'Bilgi var İSMAİL TOPÇU*0046*Mayis ayi demirbas*2548756*FAST', 'TRY', NULL, NULL, NULL, 'İSMAİL TOPÇU*0046*Mayis ayi demirbas*2548756*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(777, 1060.00, '19700101', '14320244598320250526140252878', 'Bilgi var c5 16 numara asansör bakım ücreti*YURDAGÜL KALYON*H2505708525678', 'TRY', NULL, NULL, NULL, 'c5 16 numara asansör bakım ücreti*YURDAGÜL KALYON*H2505708525678', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(778, 600.00, '19700101', '14320244598320250526120214880', 'Bilgi var MEHMET YILDIZ*0205*C2 BLOK DAİRE 18 2025 YILI DEMİRBAŞ BEDELİ 1. TAKSİT *9559967*FAST', 'TRY', NULL, NULL, NULL, 'MEHMET YILDIZ*0205*C2 BLOK DAİRE 18 2025 YILI DEMİRBAŞ BEDELİ 1. TAKSİT *9559967*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(779, 2260.00, '19700101', '14320244598320250526114309596', 'Bilgi var YUNUS EMRE ÇARHACIOĞLU*0062**9725000113*FAST', 'TRY', NULL, NULL, NULL, 'YUNUS EMRE ÇARHACIOĞLU*0062**9725000113*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(780, 1060.00, '19700101', '14320244598320250526102201155', 'Daire Kodu eşleşmedi: A1D4', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(781, 1060.00, '19700101', '14320244598320250526102201155', 'Daire Kodu eşleşmedi: A1D6', 'TRY', NULL, NULL, NULL, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(782, 2060.00, '19700101', '14320244598320250526094920623', 'Bilgi var FİLİZ KIYCI*0010*aidat ve asansor B1 daire 16 mayis ayi*4570667321*FAST', 'TRY', NULL, NULL, NULL, 'FİLİZ KIYCI*0010*aidat ve asansor B1 daire 16 mayis ayi*4570667321*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(783, 415.00, '19700101', '14320244598320250526092540798', 'Bilgi var B3 Blok Daire 10- Mayıs Aidat*DERYA AKTAŞ*H2505706881571', 'TRY', NULL, NULL, NULL, 'B3 Blok Daire 10- Mayıs Aidat*DERYA AKTAŞ*H2505706881571', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(784, 400.00, '19700101', '14320244598320250526083150437', 'Bilgi var RAHMİ KOCABAŞ*0015*RAHMİ KOCABAŞ C4 DAİRE:12 AİDAT ÖDEMESİ*7605700', 'TRY', NULL, NULL, NULL, 'RAHMİ KOCABAŞ*0015*RAHMİ KOCABAŞ C4 DAİRE:12 AİDAT ÖDEMESİ*7605700', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(785, 400.00, '19700101', '14320244598320250526052000880', 'Bilgi var FATMA ÇALIK*0010*FATMA ÇALIK AİDAT 8/12 KRED / 144845715 / 24 Gönderen Hesap:65311315 Al.T*4570299515*FAST', 'TRY', NULL, NULL, NULL, 'FATMA ÇALIK*0010*FATMA ÇALIK AİDAT 8/12 KRED / 144845715 / 24 Gönderen Hesap:65311315 Al.T*4570299515*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(786, 400.00, '19700101', '14320244598320250526052000304', 'Bilgi var TÜRKAN KIYICI*0010*TÜRKAN KIYICI C3 16 4/11 KRED / 148796644 / 4 Gönderen Hesap:5776253 Al.Tel*4570299505*FAST', 'TRY', NULL, NULL, NULL, 'TÜRKAN KIYICI*0010*TÜRKAN KIYICI C3 16 4/11 KRED / 148796644 / 4 Gönderen Hesap:5776253 Al.Tel*4570299505*FAST', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28'),
	(787, 1600.00, '19700101', '14320244598320250525233924022', 'Bilgi var Üsküp evleri sitesi B3 blok Dükkan 20. şubat-mart-nisan-mayıs aylarına ait aidat ödemesidir.*NURCAN ÖZCAN SEVİNDİK*H2505705650493', 'TRY', NULL, NULL, NULL, 'Üsküp evleri sitesi B3 blok Dükkan 20. şubat-mart-nisan-mayıs aylarına ait aidat ödemesidir.*NURCAN ÖZCAN SEVİNDİK*H2505705650493', NULL, NULL, NULL, 'eslesmedi', NULL, '', NULL, NULL, '2025-05-29 10:07:28', '2025-05-29 10:07:28');

-- tablo yapısı dökülüyor yonapp.tahsilat_onay
DROP TABLE IF EXISTS `tahsilat_onay`;
CREATE TABLE IF NOT EXISTS `tahsilat_onay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daire_id` int(11) NOT NULL DEFAULT 0,
  `site_id` int(11) NOT NULL DEFAULT 0,
  `kisi_id` varchar(100) NOT NULL,
  `tutar` decimal(15,2) NOT NULL,
  `tahsilat_tipi` varchar(50) NOT NULL DEFAULT '',
  `islem_tarihi` date NOT NULL,
  `kasa_id` int(11) NOT NULL COMMENT 'Hangi apartman hesabına yatırıldığı',
  `aciklama` text DEFAULT NULL,
  `referans_no` varchar(50) DEFAULT NULL COMMENT 'Banka referans numarası',
  `kayit_yapan` int(11) NOT NULL,
  `kayit_tarihi` datetime DEFAULT current_timestamp(),
  `onay_durumu` tinyint(4) DEFAULT 0,
  `onaylayan_yonetici` varchar(50) DEFAULT NULL,
  `onay_tarihi` datetime DEFAULT NULL,
  `onay_aciklamasi` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daire_id` (`daire_id`),
  KEY `onay_durumu` (`onay_durumu`),
  KEY `islem_tarihi` (`islem_tarihi`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- yonapp.tahsilat_onay: ~11 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `tahsilat_onay`;
INSERT INTO `tahsilat_onay` (`id`, `daire_id`, `site_id`, `kisi_id`, `tutar`, `tahsilat_tipi`, `islem_tarihi`, `kasa_id`, `aciklama`, `referans_no`, `kayit_yapan`, `kayit_tarihi`, `onay_durumu`, `onaylayan_yonetici`, `onay_tarihi`, `onay_aciklamasi`) VALUES
	(12, 179, 1, '179', 2260.00, 'AİDAT', '2025-05-26', 0, 'NUĞMAN EMİR AKGÜN*0010*c5 blok no 19 azime akgün*4573006598*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(13, 161, 1, '161', 870.67, 'AİDAT', '2025-05-26', 0, 'MİMTAŞ MİMARLIK İNŞAAT MÜHENDİSLİK TAAHHÜT MAKİNA SANAYİ VE TİCARET LİMİTED ŞİRKETİ*0205*C5 D1 ŞUBAT AİDAT ÖDEMESİ*9365833*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(14, 115, 1, '115', 1660.00, 'AİDAT', '2025-05-26', 0, 'FATİH KALAYCI*0012*C2- Daire:15 Canan SUBAŞI KALAYCI Demirbaş ve Asansör ücreti*886249885*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(15, 25, 1, '25', 1000.00, 'AİDAT', '2025-05-26', 0, 'SAMİ ERGİN*0010*SAMİERGİNB1D5 MAYIS AYI AİDATI VE EK ÖDEMEASANSÖR BAKIM ÜCRETİ*4572147426*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(16, 81, 1, '81', 860.00, 'AİDAT', '2025-05-26', 0, 'Zekeriya Kurt*0111*C1 DAiRE 1 MAYIS - asansör arizasi*753392602*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(17, 113, 1, '113', 1060.00, 'AİDAT', '2025-05-26', 0, 'OGÜN PEÇENEK*0205*C2. D.13 asansör ücreti*9586641*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(18, 65, 1, '65', 2060.00, 'AİDAT', '2025-05-26', 0, 'B3 BLOK DAİRE5*EDA ÇİÇEK*H2505707539207', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(19, 37, 1, '37', 600.00, 'DEMİRBAŞ', '2025-05-26', 0, 'MEHMET SÖNMEZ*0046*b1 blok d 17 demirbaş ihtiyaçları Ayşe sönmez*1887458*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(20, 9, 1, '9', 1060.00, 'ASANSOR', '2025-05-26', 0, 'üsküb evleri A1-DAİRE 9 ASANSÖR BAKIMI *GÜNEŞ ÖZÇİL*H2505707237626', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(21, 3, 1, '3', 1060.00, 'DEMİRBAŞ', '2025-05-26', 0, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL),
	(22, 5, 1, '5', 1060.00, 'DEMİRBAŞ', '2025-05-26', 0, 'DÜZCE İLME VE HAYRA HİZMET VAKFI*0205*A1 BLOK 3-4-5-6 NOLU DAİRELERİN ORTAK BAKIM BEDELİ*9711627*FAST', '14320244598320250526124936189', 0, '2025-05-29 13:07:28', 0, NULL, NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
