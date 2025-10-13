-- --------------------------------------------------------
-- Sunucu:                       127.0.0.1
-- Sunucu sürümü:                10.4.32-MariaDB - mariadb.org binary distribution
-- Sunucu İşletim Sistemi:       Win64
-- HeidiSQL Sürüm:               12.11.0.7065
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

-- tablo yapısı dökülüyor yonapp.menu
DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) DEFAULT NULL COMMENT 'Burası dolu olduğu zaman yetkiye tabi olur, 0 veya boş ise herkese açık olur',
  `page_name` varchar(50) NOT NULL DEFAULT '0',
  `menu_link` varchar(255) NOT NULL DEFAULT '0',
  `icon` varchar(255) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `group_name` varchar(50) DEFAULT NULL,
  `group_order` int(11) DEFAULT 1,
  `isMenu` int(11) DEFAULT 1,
  `menu_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5016 DEFAULT CHARSET=latin5 COLLATE=latin5_turkish_ci COMMENT='isMenu alanı, menüde görünüp görünmesi için, \r\nindex_no alanı ile menülerin sırası belirlenir\r\nis_authorize alanı, yetki kontolü yapılıp yapılmayacağını belirler';

-- yonapp.menu: ~81 rows (yaklaşık) tablosu için veriler indiriliyor
DELETE FROM `menu`;
INSERT INTO `menu` (`id`, `permission_id`, `page_name`, `menu_link`, `icon`, `parent_id`, `is_active`, `group_name`, `group_order`, `isMenu`, `menu_order`) VALUES
	(100, 100, 'Anasayfa', 'ana-sayfa', 'feather-airplay', 0, 1, 'Ana Sayfa', 1, 1, 1),
	(200, 200, 'Site Yönetimi', ' ', 'feather-layers', 0, 1, 'Site Yönetim', 2, 1, 1),
	(201, 201, 'Siteler', 'siteler', 'buildings', 200, 1, NULL, 1, 1, 1),
	(202, 202, 'Site Blokları', 'site-bloklari', 'buildings', 200, 1, NULL, 1, 1, 2),
	(203, 203, 'Site Daireleri', 'site-daireleri', '', 200, 1, NULL, 1, 1, 3),
	(204, 204, 'Site Sakinleri', 'site-sakinleri', '', 200, 1, NULL, 1, 1, 4),
	(205, 205, 'Site Düzenle', 'site-duzenle', '', 201, 1, NULL, 1, 0, 4),
	(206, 206, 'Site Sakini Düzenle', 'site-sakini-duzenle', '', 204, 1, NULL, 1, 0, 4),
	(207, 207, 'Site Sakini Ekle', 'site-sakini-ekle', '0', 204, 1, NULL, 1, 0, NULL),
	(300, 300, 'Aidat/Tahsilat Yönetimi', ' ', 'feather-pie-chart', 0, 1, 'Finansal Yönetim', 2, 1, 3),
	(301, 301, 'Aidat Türü Tanımlama', 'aidat-turu-listesi', '0', 300, 1, NULL, 1, 1, 1),
	(302, 302, 'Borçlandırma', 'borclandirma', '0', 300, 1, '', 1, 1, 2),
	(303, 303, 'Yönetici Aidat Ödeme', 'yonetici-aidat-odeme', '0', 300, 1, NULL, 1, 1, 3),
	(304, 304, 'Online Aidat Takip', 'dues/list', '0', 300, 1, NULL, 1, 1, 4),
	(305, 305, 'Borç Ekle', 'borclandirma-yap', '0', 302, 1, '', 1, 0, NULL),
	(306, 306, 'Borçlandırma Detay', 'borclandirma-detayi', '0', 302, 1, '', 1, 0, NULL),
	(307, 307, 'Borçlarım', 'borclarim', '0', 300, 1, '', 1, 1, 5),
	(308, 308, 'Yönetici Tahsilat Onaylama', 'onay-bekleyen-tahsilatlar', '0', 310, 1, NULL, 1, 0, NULL),
	(309, 309, 'Excelden Ödeme Yükleme', 'dues/payment/upload-from-xls', 'feather-pie-chart', 300, 1, NULL, 1, 0, 3),
	(310, 310, 'Tahsilatlar', 'tahsilatlar', '', 300, 1, NULL, 1, 1, 3),
	(311, 0, 'Periyodik Borçlandırma', 'dues/debit/periyodik-borclandirma', '0', 300, 0, '', 1, 1, 6),
	(312, 0, 'Aidat Düzenleme', 'aidat-tanimlama', '0', 300, 1, '', 1, 0, 6),
	(313, 0, 'Aidat Türü Tanımlama', 'aidat-turu-tanimlama', '0', 301, 1, '', 1, 0, 6),
	(314, 0, 'Borçlandırma Düzenle', 'borclandirma-duzenle', '0', 302, 1, '', 1, 0, 6),
	(400, 400, 'Bakım ve Arıza Takip', ' ', 'feather-tool', 0, 1, 'Site Yönetim', 2, 1, 2),
	(401, 401, 'Bakım ve Arıza Yönetimi', 'repair/list', '0', 400, 1, NULL, 1, 1, NULL),
	(402, 402, 'Periyodik Bakım Takip', 'repair/care/list', '0', 400, 1, NULL, 1, 1, NULL),
	(500, 500, 'Finans Yönetimi', ' ', 'feather-dollar-sign', 0, 0, 'Finansal Yönetim', 2, 0, 5),
	(501, 501, 'Gelir-Gider İşlemleri', 'finans-yonetimi/list', '0', 500, 1, NULL, 1, 1, NULL),
	(502, 502, 'Kasa Listesi', 'kasa-listesi', '', 500, 1, NULL, 1, 1, NULL),
	(503, 503, 'Gelir-Gider Türü Ekle/Güncelle', 'defines/incexp/manage', '0', 500, 1, NULL, 1, 0, NULL),
	(504, 504, 'Kasa Hareketlerini Görüntüleme', 'kasa-hareketleri', '0', 500, 1, NULL, 1, 0, NULL),
	(505, 505, 'Gelir Gider İşlemleri', 'gelir-gider-islemleri', '0', 500, 1, NULL, 1, 1, NULL),
	(600, 600, 'Güvenlik ve Ziyaretçi', ' ', 'feather-shield', 0, 1, 'Site Yönetim', 2, 1, 3),
	(601, 601, 'Güvenlik Yönetimi', 'visitors/security/list', '', 600, 1, NULL, 1, 1, NULL),
	(602, 602, 'Ziyaretçi Yönetimi', 'ziyaretci-listesi', '', 600, 1, NULL, 1, 1, NULL),
	(603, 603, 'Görev Yeri Ekle', 'visitors/security/location/list', '', 600, 1, NULL, 1, 1, NULL),
	(700, 700, 'Personel Yönetimi', ' ', 'feather-users', 0, 1, 'Diğer', 3, 1, 2),
	(701, 701, 'Personel Ekle/Güncelle', 'persons/manage', '0', 700, 1, NULL, 1, 0, NULL),
	(702, 702, 'Personel Bilgileri', 'persons/list', '0', 700, 1, NULL, 1, 1, NULL),
	(800, 800, 'Duyuru ve Talep', ' ', 'feather-message-circle', 0, 1, 'Site Yönetim', 2, 1, 4),
	(801, 801, 'Duyuru Yönetimi', 'notice/admin/announcements-list', '', 800, 1, NULL, 1, 1, 0),
	(802, 802, 'Duyurular', 'notice/peoples/announcements-list', '', 800, 1, NULL, 1, 1, 0),
	(803, 803, 'Anketler', 'notice/peoples/survey-list', '', 800, 1, NULL, 1, 1, 0),
	(804, 804, 'Anket Yönetimi', 'notice/admin/survey-list', '', 800, 1, NULL, 1, 1, 0),
	(805, 805, 'Şikayet/Öneri Yönetimi', 'notice/admin/complaints-list', '', 800, 1, NULL, 1, 1, 0),
	(806, 806, 'Şikayet/Öneri', 'notice/peoples/complaints-list', '', 800, 1, NULL, 1, 1, 0),
	(900, 900, 'İcra İşlemleri', ' ', 'feather-x-octagon', 0, 1, 'Site Yönetim', 2, 1, 6),
	(901, 901, 'İcra Takibi', 'icra-takibi', '', 900, 1, NULL, 1, 1, 12),
	(902, 902, 'İcralarım', 'icralarim', '', 900, 1, NULL, 1, 1, 0),
	(1000, 1000, 'Tanımlamalar', ' ', 'feather-clipboard', 0, 1, 'Diğer', 3, 1, 1),
	(1001, 1001, 'Borçlandırma Türü Tanımlama', 'defines/debit-type/list', '0', 1000, 1, '', 2, 1, NULL),
	(1002, 1002, 'Daire Tipi Tanımlama', 'daire-tipi-tanimlamalari', '0', 1000, 1, NULL, 1, 1, NULL),
	(1003, 1003, 'Gelir-Gider Türü Tanımlama', 'defines/incexp/list', '0', 1000, 1, NULL, 1, 1, NULL),
	(1004, 1004, 'İş Grubu Tanımlama', 'defines/job-groups/list', '0', 1000, 1, NULL, 1, 1, NULL),
	(1005, 1005, 'İş Grubu Ekle/Güncelle', 'defines/job-groups/manage', '0', 1000, 1, NULL, 1, 0, NULL),
	(1100, 1100, 'Email & Sms Yönetimi', ' ', 'feather-send', 0, 1, 'Diğer', 4, 1, 4),
	(1101, 1101, 'Email-Sms Takibi', 'email-sms/list', '', 1100, 1, '', 0, 1, NULL),
	(1200, 1200, 'Ayarlar', 'settings/manage', 'feather-settings', 0, 1, 'Diğer', 4, 1, 5),
	(1300, 1300, 'Maliyet ve Faturalandırma', 'repair/cost/list', '0', 0, 1, 'Finansal Yönetim', 2, 0, NULL),
	(1400, 1400, 'Kullanıcı Yönetimi', 'kullanici-yonetimi', 'feather-user-plus', 0, 1, 'Diğer', 3, 1, 3),
	(1401, 1401, 'Kullanıcı Listesi', 'kullanici-listesi', '', 1400, 1, NULL, 1, 1, NULL),
	(1402, 1402, 'Yetki Grupları', 'kullanici-gruplari', '0', 1400, 1, NULL, 1, 1, NULL),
	(1403, 1403, 'Kullanıcı Ekle Güncelle', 'users/manage', '0', 1400, 1, NULL, 1, 0, NULL),
	(1404, 1404, 'İşlem Yetkileri', 'users/roles/authorities', '0', 1400, 1, NULL, 1, 0, NULL),
	(1405, 1405, 'Yetki Grubu Ekle/Güncelle', 'kullanici-gruplari/duzenle', '0', 1400, 1, NULL, 1, 0, NULL),
	(1406, 1406, 'Yetki Grubu Yetkilerini düzenle', 'yetki-yonetimi', '0', 1400, 1, NULL, 1, 0, NULL),
	(1500, 1500, 'Görüş & Öneri', 'feedback/send', 'feather-brand-feedly', 0, 0, 'Diğer', 4, 1, 13),
	(1600, 1600, 'Araç Yönetimi', 'management/peoples/manage&tab=car', 'feather-truck', 0, 1, 'Site Yönetim', 2, 1, 7),
	(1700, 1700, 'Acil Durum Kişileri', 'management/peoples/manage&tab=emergency\n', 'feather-truck', 0, 1, 'Site Yönetim', 2, 1, 8),
	(5000, 5000, 'Daire Düzenle', 'daire-duzenle', '0', 200, 1, NULL, 1, 0, NULL),
	(5001, 5001, 'Excelden Kişileri Yükle', 'kisileri-excelden-yukle', '0', 200, 1, NULL, 1, 0, NULL),
	(5002, 5002, 'Excelden Daire Yükle', 'daireleri-excelden-yukle', '0', 200, 1, NULL, 1, 0, NULL),
	(5003, 5003, 'Kişi Düzenle', 'site-sakini-duzenle', '0', 200, 1, NULL, 1, 0, NULL),
	(5004, 5003, 'Borc Detayı Düzenle', 'dues/debit/single-manage', '0', 300, 1, NULL, 1, 0, NULL),
	(5005, 5003, 'Borca Ait Tahsilatıları Görüntüle', 'tahsilat-detayi', '0', 310, 1, NULL, 1, 0, NULL),
	(5006, 5003, 'Eseleşmeyen Tahsilatıları Görüntüle', 'dues/payment/tahsilat-eslesmeyen', '0', 300, 1, NULL, 1, 0, NULL),
	(5012, 1406, 'Yetki Grubu Ekle/Düzenle', 'kullanici-grubu-duzenle', '0', 1402, 1, NULL, 1, 0, NULL),
	(5013, 0, 'İcralarım', 'icra-detay', '', 900, 1, NULL, 1, 0, 0),
	(5014, 5003, 'Kişi Borçandırması Düzenle', 'borclandirma-kisi-duzenle', '0', 302, 1, NULL, 1, 0, NULL),
	(5015, 5003, 'Kişi Borçandırması Ekle', 'borclandirma-kisi-ekle', '0', 302, 1, NULL, 1, 0, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
