-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 05 Eki 2025, 22:04:30
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `yonapp`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `permission_id` int(11) DEFAULT NULL COMMENT 'Burası dolu olduğu zaman yetkiye tabi olur, 0 veya boş ise herkese açık olur',
  `page_name` varchar(50) NOT NULL DEFAULT '0',
  `menu_link` varchar(255) NOT NULL DEFAULT '0',
  `icon` varchar(255) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `group_name` varchar(50) DEFAULT NULL,
  `group_order` int(11) DEFAULT 1,
  `isMenu` int(11) DEFAULT 1,
  `menu_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin5 COLLATE=latin5_turkish_ci COMMENT='isMenu alanı, menüde görünüp görünmesi için, \r\nindex_no alanı ile menülerin sırası belirlenir\r\nis_authorize alanı, yetki kontolü yapılıp yapılmayacağını belirler';

--
-- Tablo döküm verisi `menu`
--

INSERT INTO `menu` (`id`, `permission_id`, `page_name`, `menu_link`, `icon`, `parent_id`, `is_active`, `group_name`, `group_order`, `isMenu`, `menu_order`) VALUES
(100, 100, 'Anasayfa', 'ana-sayfa', 'feather-airplay', 0, 1, 'Ana Sayfa', 1, 1, 1),
(200, 200, 'Site Yönetimi', ' ', 'feather-layers', 0, 1, 'Site Yönetim', 2, 1, 1),
(201, 201, 'Siteler', 'siteler', 'buildings', 200, 1, NULL, 1, 1, 1),
(202, 202, 'Site Blokları', 'site-bloklari', 'buildings', 200, 1, NULL, 1, 1, 2),
(203, 203, 'Site Daireleri', 'site-daireleri', '', 200, 1, NULL, 1, 1, 3),
(204, 204, 'Site Sakinleri', 'site-sakinleri', '', 200, 1, NULL, 1, 1, 4),
(205, 205, 'Site Düzenle', 'site-duzenle', '', 201, 1, NULL, 1, 0, 4),
(206, 206, 'Site Sakini Düzenle', 'site-sakini-duzenle', '', 204, 1, NULL, 1, 0, 4),
(300, 300, 'Aidat/Tahsilat Yönetimi', ' ', 'feather-pie-chart', 0, 1, 'Finansal Yönetim', 2, 1, 3),
(301, 301, 'Aidat Türü Tanımlama', 'aidat-turu-listesi', '0', 300, 1, NULL, 1, 1, 1),
(302, 302, 'Borçlandırma', 'borclandirma', '0', 300, 1, '', 1, 1, 2),
(303, 303, 'Yönetici Aidat Ödeme', 'yonetici-aidat-odeme', '0', 300, 1, NULL, 1, 1, 3),
(304, 304, 'Online Aidat Takip', 'dues/list', '0', 300, 1, NULL, 1, 1, 4),
(305, 305, 'Borç Ekle', 'borclandirma-yap', '0', 302, 1, '', 1, 0, NULL),
(306, 306, 'Borçlandırma Detay', 'borclandirma-detayi', '0', 302, 1, '', 1, 0, NULL),
(307, 307, 'Borçlarım', 'dues/user-payment/list', '0', 300, 1, '', 1, 1, 5),
(308, 308, 'Yönetici Tahsilat Onaylama', 'onay-bekleyen-tahsilatlar', '0', 310, 1, NULL, 1, 0, NULL),
(309, 309, 'Excelden Ödeme Yükleme', 'dues/payment/upload-from-xls', 'feather-pie-chart', 300, 1, NULL, 1, 0, 3),
(310, 310, 'Tahsilatlar', 'tahsilatlar', '', 300, 1, NULL, 1, 1, 3),
(311, 0, 'Periyodik Borçlandırma', 'dues/debit/periyodik-borclandirma', '0', 300, 1, '', 1, 1, 6),
(312, 0, 'Aidat Düzenleme', 'aidat-tanimlama', '0', 300, 1, '', 1, 0, 6),
(313, 0, 'Aidat Türü Tanımlama', 'aidat-turu-tanimlama', '0', 301, 1, '', 1, 0, 6),
(314, 0, 'Borçlandırma Düzenle', 'borclandirma-duzenle', '0', 302, 1, '', 1, 0, 6),
(400, 400, 'Bakım ve Arıza Takip', ' ', 'feather-tool', 0, 1, 'Site Yönetim', 2, 1, 2),
(401, 401, 'Bakım ve Arıza Yönetimi', 'bakim-ariza-takip', '0', 400, 1, NULL, 1, 1, NULL),
(402, 402, 'Periyodik Bakım Takip', 'periyodik-bakim', '0', 400, 1, NULL, 1, 1, NULL),
(403, 403, 'Maliyet / Faturalandırma', 'maliyet-faturalandirma', '0', 400, 1, NULL, 3, 1, NULL),
(500, 500, 'Finans Yönetimi', ' ', 'feather-dollar-sign', 0, 0, 'Finansal Yönetim', 2, 0, 5),
(501, 501, 'Gelir-Gider İşlemleri', 'finans-yonetimi/list', '0', 500, 1, NULL, 1, 1, NULL),
(502, 502, 'Kasa Listesi', 'kasa-listesi', '', 500, 1, NULL, 1, 1, NULL),
(503, 503, 'Gelir-Gider Türü Ekle/Güncelle', 'defines/incexp/manage', '0', 500, 1, NULL, 1, 0, NULL),
(600, 600, 'Güvenlik ve Ziyaretçi', ' ', 'feather-shield', 0, 1, 'Site Yönetim', 2, 1, 3),
(601, 601, 'Güvenlik Yönetimi', 'guvenlik', '', 600, 1, NULL, 1, 1, NULL),
(602, 602, 'Ziyaretçi Yönetimi', 'ziyaretci-listesi', '', 600, 1, NULL, 1, 1, NULL),
(603, 603, 'Görev Yeri Ekle', 'guvenlik-gorev-yerleri', '', 600, 1, NULL, 1, 1, NULL),
(604, 604, 'Vardiya Tanımla', 'vardiya-listesi', '', 600, 1, NULL, 5, 1, NULL),
(605, 605, 'Personel Yönetimi', 'personel-listesi', '', 600, 1, NULL, 3, 1, NULL),
(700, 700, 'Personel Yönetimi', ' ', 'feather-users', 0, 1, 'Diğer', 3, 0, 2),
(701, 701, 'Personel Ekle/Güncelle', 'persons/manage', '0', 700, 1, NULL, 1, 0, NULL),
(702, 702, 'Personel Bilgileri', 'persons/list', '0', 700, 1, NULL, 1, 0, NULL),
(800, 800, 'Duyuru ve Talep', ' ', 'feather-message-circle', 0, 0, 'Site Yönetim', 2, 0, 4),
(801, 801, 'Duyuru Yönetimi', 'notice/admin/announcements-list', '', 800, 0, NULL, 1, 1, 0),
(802, 802, 'Duyurular', 'notice/peoples/announcements-list', '', 800, 0, NULL, 1, 1, 0),
(803, 803, 'Anketler', 'notice/peoples/survey-list', '', 800, 0, NULL, 1, 1, 0),
(804, 804, 'Anket Yönetimi', 'notice/admin/survey-list', '', 800, 0, NULL, 1, 1, 0),
(805, 805, 'Şikayet/Öneri Yönetimi', 'notice/admin/complaints-list', '', 800, 0, NULL, 1, 1, 0),
(806, 806, 'Şikayet/Öneri', 'notice/peoples/complaints-list', '', 800, 0, NULL, 1, 1, 0),
(900, 900, 'İcra İşlemleri', ' ', 'feather-x-octagon', 0, 1, 'Site Yönetim', 2, 1, 6),
(901, 901, 'İcra Takibi', 'icra-takibi', '', 900, 1, NULL, 1, 1, 12),
(902, 902, 'İcralarım', 'icralarim', '', 900, 1, NULL, 1, 1, 0),
(903, 903, 'İcra Detay', 'icra-detay', '', 900, 1, NULL, 1, 0, 0),
(904, 904, 'İcra Sakin Detay', 'icra-sakin-detay', '', 900, 1, NULL, 1, 0, 0),
(1000, 1000, 'Tanımlamalar', ' ', 'feather-clipboard', 0, 1, 'Diğer', 3, 1, 1),
(1002, 1002, 'Daire Tipi Tanımlama', 'daire-turu-listesi', '0', 1000, 1, NULL, 1, 1, NULL),
(1005, 1005, 'İş Grubu Ekle/Güncelle', 'defines/job-groups/manage', '0', 1000, 1, NULL, 1, 0, NULL),
(1100, 1100, 'Email & Sms Yönetimi', ' ', 'feather-send', 0, 1, 'Diğer', 4, 1, 4),
(1101, 1101, 'Email-Sms Takibi', 'email-sms/list', '', 1100, 1, '', 0, 1, NULL),
(1200, 1200, 'Ayarlar', 'ayarlar', 'feather-settings', 0, 1, 'Diğer', 4, 1, 5),
(1300, 1300, 'Maliyet ve Faturalandırma', 'maliyet-faturalandirma', '0', 0, 1, 'Finansal Yönetim', 2, 0, NULL),
(1400, 1400, 'Kullanıcı Yönetimi', 'kullanici-yonetimi', 'feather-user-plus', 0, 1, 'Diğer', 3, 1, 3),
(1401, 1401, 'Kullanıcı Listesi', 'kullanici-listesi', '', 1400, 1, NULL, 1, 1, NULL),
(1402, 1402, 'Yetki Grupları', 'kullanici-gruplari', '0', 1400, 1, NULL, 1, 1, NULL),
(1403, 1403, 'Kullanıcı Ekle Güncelle', 'users/manage', '0', 1400, 1, NULL, 1, 0, NULL),
(1404, 1404, 'İşlem Yetkileri', 'users/roles/authorities', '0', 1400, 1, NULL, 1, 0, NULL),
(1405, 1405, 'Yetki Grubu Ekle/Güncelle', 'kullanici-gruplari/duzenle', '0', 1400, 1, NULL, 1, 0, NULL),
(1406, 1406, 'Yetki Grubu Yetkilerini düzenle', 'yetki-yonetimi', '0', 1400, 1, NULL, 1, 0, NULL),
(1500, 1500, 'Görüş & Öneri', 'feedback/send', 'feather-brand-feedly', 0, 0, 'Diğer', 4, 1, 13),
(1600, 1600, 'Araç Yönetimi', 'arac-yonetimi', 'feather-truck', 0, 1, 'Site Yönetim', 2, 1, 7),
(1700, 1700, 'Acil Durum Kişileri', 'acil-durum-yonetimi', 'feather-truck', 0, 1, 'Site Yönetim', 2, 1, 8),
(5000, 5000, 'Daire Düzenle', 'daire-duzenle', '0', 200, 1, NULL, 1, 0, NULL),
(5001, 5001, 'Excelden Kişileri Yükle', 'management/peoples/upload-from-xls', '0', 200, 1, NULL, 1, 0, NULL),
(5002, 5002, 'Excelden Daire Yükle', 'management/apartment/upload-from-xls', '0', 200, 1, NULL, 1, 0, NULL),
(5003, 5003, 'Kişi Düzenle', 'management/peoples/manage', '0', 200, 1, NULL, 1, 0, NULL),
(5004, 5003, 'Borc Detayı Düzenle', 'dues/debit/single-manage', '0', 300, 1, NULL, 1, 0, NULL),
(5005, 5003, 'Borca Ait Tahsilatıları Görüntüle', 'tahsilat-detayi', '0', 310, 1, NULL, 1, 0, NULL),
(5006, 5003, 'Eseleşmeyen Tahsilatıları Görüntüle', 'dues/payment/tahsilat-eslesmeyen', '0', 300, 1, NULL, 1, 0, NULL),
(5007, 5003, 'Kasa Hareketlerini Görüntüleme', 'kasa-hareketleri', '0', 500, 1, NULL, 1, 0, NULL),
(5009, 0, 'page_name', 'menu_link', 'icon', 0, 0, 'group_name', 0, 0, 0),
(5012, 1406, 'Yetki Grubu Ekle/Düzenle', 'kullanici-grubu-duzenle', '0', 1402, 1, NULL, 1, 0, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5015;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
