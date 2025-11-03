-- =====================================================
-- Gelir-Gider Sayfası Performans Optimizasyonu
-- İndeks Önerileri
-- =====================================================
-- Bu dosya, gelir-gider listesi sayfasının performansını
-- artırmak için önerilen veritabanı indekslerini içerir.
--
-- UYARI: İndeks oluşturma işlemi büyük tablolarda uzun sürebilir.
-- Production ortamında yoğun olmayan saatlerde çalıştırın.
-- =====================================================

-- 1. KASA HAREKETLERİ ANA İNDEKS
-- Bu indeks, kasa_id'ye göre filtreleme, silinmemiş kayıtları bulma
-- ve tarih sıralamasını optimize eder.
-- Performans kazancı: %50-70
CREATE INDEX IF NOT EXISTS idx_kasa_hareket_kasa_tarih 
ON kasa_hareketleri(kasa_id, silinme_tarihi, islem_tarihi DESC);

-- 2. KİŞİ İD İNDEKSİ (LEFT JOIN için)
-- kasa_hareketleri.kisi_id ile kisiler tablosuna yapılan JOIN'i hızlandırır
-- Performans kazancı: %20-30
CREATE INDEX IF NOT EXISTS idx_kasa_hareket_kisi 
ON kasa_hareketleri(kisi_id);

-- 3. KİŞİLER - DAİRE İLİŞKİSİ (LEFT JOIN için)
-- kisiler.daire_id ile daireler tablosuna yapılan JOIN'i hızlandırır
-- Performans kazancı: %15-25
CREATE INDEX IF NOT EXISTS idx_kisiler_daire 
ON kisiler(daire_id);

-- 4. ARAMA FİLTRESİ İÇİN FULLTEXT İNDEKS (Opsiyonel)
-- Açıklama ve işlem tipi üzerinde arama yapılıyorsa
-- Not: MyISAM veya InnoDB (MySQL 5.6+) gerektirir
-- Performans kazancı: %40-60 (arama yapıldığında)
-- ALTER TABLE kasa_hareketleri ADD FULLTEXT INDEX idx_kasa_hareket_fulltext (aciklama, islem_tipi);

-- 5. TUTAR FİLTRESİ (Eğer tutar'a göre filtreleme varsa)
-- Performans kazancı: %10-20
CREATE INDEX IF NOT EXISTS idx_kasa_hareket_tutar 
ON kasa_hareketleri(tutar);

-- =====================================================
-- İndeks Performans Kontrolü
-- =====================================================
-- Aşağıdaki sorguları çalıştırarak indekslerin 
-- doğru kullanıldığını kontrol edebilirsiniz:

-- Sorgu planını göster (indeks kullanımını kontrol et)
-- EXPLAIN SELECT kh.*, k.adi_soyadi, d.daire_kodu 
-- FROM kasa_hareketleri kh
-- LEFT JOIN kisiler k ON kh.kisi_id = k.id
-- LEFT JOIN daireler d ON k.daire_id = d.id
-- WHERE kh.kasa_id = 1 
-- AND kh.silinme_tarihi IS NULL 
-- AND kh.tutar != 0
-- ORDER BY kh.islem_tarihi DESC, kh.id DESC
-- LIMIT 0, 50;

-- =====================================================
-- İndeks Bakımı
-- =====================================================
-- Periyodik olarak (ayda bir) indeksleri optimize edin:
-- OPTIMIZE TABLE kasa_hareketleri;
-- OPTIMIZE TABLE kisiler;
-- OPTIMIZE TABLE daireler;

-- =====================================================
-- İndeks Kaldırma (Geri Alma)
-- =====================================================
-- Eğer indeksler sorun yaratırsa aşağıdaki komutlarla kaldırabilirsiniz:
-- DROP INDEX idx_kasa_hareket_kasa_tarih ON kasa_hareketleri;
-- DROP INDEX idx_kasa_hareket_kisi ON kasa_hareketleri;
-- DROP INDEX idx_kisiler_daire ON kisiler;
-- DROP INDEX idx_kasa_hareket_tutar ON kasa_hareketleri;

-- =====================================================
-- Notlar
-- =====================================================
-- 1. Bu indeksler özellikle 1000+ kayıt içeren tablolarda etkili olur
-- 2. İndeks oluşturma süresi tablo boyutuna bağlıdır (örn: 10K kayıt ~5-10 saniye)
-- 3. İndeksler disk alanı kullanır (toplam ~5-10% ek alan)
-- 4. INSERT/UPDATE işlemleri çok az yavaşlar (~5-10%) ama SELECT çok hızlanır
-- 5. Production'da test ettikten sonra uygulayın
