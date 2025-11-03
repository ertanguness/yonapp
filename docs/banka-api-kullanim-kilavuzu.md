# Banka API Entegrasyonu - Kullanım Kılavuzu

## 🎯 Genel Bakış

Bu modül, banka hesaplarınızdan gerçek zamanlı olarak işlem hareketlerini çekerek, otomatik olarak site sakinlerinin hesaplarına eşleştirmeyi sağlar.

## 📋 Özellikler

### ✅ Tamamlanan Özellikler

1. **Banka API Entegrasyonu**
   - Demo modu ile test edilebilir yapı
   - Akbank, İş Bankası, Garanti BBVA için genişletilebilir altyapı
   - Gerçek banka API'leri için hazır çerçeve

2. **Otomatik İşlem Analizi**
   - İşlem açıklamalarından kişi adı çıkarma
   - Daire numarası tespiti (101, A-5, vb.)
   - Blok kodu tespiti
   - Güvenilirlik skoru hesaplama (%0-100)

3. **Tahsilat Havuzu Yönetimi**
   - Çift kayıt önleme (banka referans no ile)
   - Eşleşmiş/eşleşmemiş ayrımı
   - Kısmi ödeme desteği
   - Hareket yönü filtreleme (Gelir/Gider)

4. **Kullanıcı Arayüzü**
   - Sezgisel sorgulama formu
   - Gerçek zamanlı senkronizasyon bildirimleri
   - Otomatik eşleşme skorları
   - Manuel eşleştirme sayfasına yönlendirme

## 🚀 Kurulum

### 1. Veritabanı Güncellemesi

```bash
# MySQL'de çalıştırın:
mysql -u root -p yonapp < Database/alter-tahsilat-havuzu-banka-api.sql
```

### 2. Dosya Yapısı

Eklenen/Güncellenen dosyalar:
```
App/Services/BankaApiService.php         (YENİ)
Model/TahsilatHavuzuModel.php           (GÜNCELLENDİ)
pages/dues/online-payment/sorgula.php   (YENİ)
pages/dues/online-payment/list.php      (YENİ)
route.php                                (GÜNCELLENDİ)
```

### 3. Routing Yapılandırması

`route.php` dosyasına şu route'lar eklendi:
- `/banka-hesap-sorgula` → Sorgulama formu
- `/banka-hesap-hareketleri` → API sonuçları

## 📖 Kullanım

### Adım 1: Banka Hesap Hareketlerini Sorgulama

1. **Ana Menü > Gelir/Gider > Banka Hesap Hareketleri** sayfasına gidin
2. Formu doldurun:
   - **Banka Hesabı**: Sorgulanacak banka hesabını seçin
   - **Başlangıç Tarihi**: İşlemlerin başlangıç tarihi
   - **Bitiş Tarihi**: İşlemlerin bitiş tarihi
   - **Hareket Yönü**: Tümü / Gelen / Giden

3. **Sorgula** butonuna tıklayın

### Adım 2: Sonuçları İnceleme

Sonuç sayfasında görecekleriniz:

#### 📊 Özet Kartlar
- Toplam Gelen
- Toplam Giden
- Net Hareket
- Toplam İşlem

#### 📋 İşlem Listesi
Her işlem için:
- **Tarih**: İşlem tarihi ve saati
- **Referans No**: Banka referans numarası
- **İşlem Türü**: Gelen (yeşil) / Giden (kırmızı)
- **Tutar**: İşlem tutarı
- **Açıklama**: İşlem açıklaması
- **Otomatik Eşleşme**: 
  - 🟢 Yüksek (%70+): Otomatik eşleştirilebilir
  - 🟡 Orta (%40-69): Manuel kontrol önerilir
  - ⚪ Düşük (%0-39): Manuel eşleştirme gerekli
- **Durum**: Havuzda / Yeni

### Adım 3: Eşleştirme

#### Otomatik Eşleşenler (%70+ güvenilirlik)
Sistem şu bilgileri otomatik tespit eder:
- Kişi adı (örn: "Ali Yılmaz")
- Daire numarası (örn: "101", "A-5")
- Blok kodu (örn: "Blok A")

#### Manuel Eşleştirme Gerekli
Düşük güvenilirlikli işlemler için:
1. Sarı uyarı kutusunda **"Eşleştir"** butonuna tıklayın
2. **Tahsilat Eşleştirme** sayfasına yönlendirileceksiniz
3. Her işlem için manuel olarak daire seçin
4. Tahsilatı kaydedin

## 🔧 Gerçek Banka API Entegrasyonu

### Demo Moddan Gerçek Moda Geçiş

Şu anda sistem **DEMO** modunda çalışmaktadır. Gerçek banka API'si kullanmak için:

#### 1. Banka API Bilgilerini Alın
Her banka için gerekli:
- API URL (endpoint)
- API Key
- API Secret
- Developer dokümantasyon

#### 2. Banka Hesabına API Bilgilerini Ekleyin

`kasa` tablosuna şu kolonları ekleyin:
```sql
ALTER TABLE `kasa` 
ADD COLUMN `api_url` VARCHAR(255) NULL,
ADD COLUMN `api_key` VARCHAR(255) NULL,
ADD COLUMN `api_secret` VARCHAR(255) NULL,
ADD COLUMN `banka_kodu` VARCHAR(50) NULL COMMENT 'akbank, isbank, garanti vb';
```

#### 3. BankaApiService.php Güncellemesi

`App/Services/BankaApiService.php` dosyasında ilgili banka metodunu doldurun:

```php
private function getAkbankHareketleri($hesapNo, $baslangicTarihi, $bitisTarihi)
{
    // API endpoint
    $url = $this->apiUrl . "/accounts/{$hesapNo}/transactions";
    
    // Parametreler
    $params = [
        'startDate' => $baslangicTarihi,
        'endDate' => $bitisTarihi
    ];
    
    // API isteği
    $response = $this->makeApiRequest($url . '?' . http_build_query($params), 'GET');
    
    // Normalize et
    return $this->normalizeResponse($response, 'akbank');
}

private function normalizeResponse($rawData, $bankCode)
{
    $normalized = [];
    
    // Akbank yanıtını normalize et
    if ($bankCode === 'akbank') {
        foreach ($rawData['transactions'] ?? [] as $item) {
            $normalized[] = [
                'islem_tarihi' => $item['transactionDate'],
                'aciklama' => $item['description'],
                'tutar' => abs($item['amount']),
                'hareket_yonu' => $item['amount'] > 0 ? 'Gelir' : 'Gider',
                'banka_ref_no' => $item['referenceNumber'],
                'hesap_no' => $hesapNo,
                'currency' => $item['currency'] ?? 'TRY',
                'bakiye' => $item['balance'] ?? 0
            ];
        }
    }
    
    return $normalized;
}
```

#### 4. Test Etme

```php
// Test kodu
$bankaApi = new BankaApiService('akbank', [
    'api_url' => 'https://api.akbank.com/v1',
    'api_key' => 'XXXXX',
    'api_secret' => 'YYYYY'
]);

$islemler = $bankaApi->getHesapHareketleri(
    'TR330006200009800001234567',
    '2024-01-01',
    '2024-01-31'
);

print_r($islemler);
```

## 🧠 Otomatik Eşleşme Algoritması

### Şu Anki Kurallar

```php
extractResidentInfo($aciklama) kullanır:

1. Daire Numarası: (%30 güvenilirlik)
   - 3 haneli sayılar (101, 202, vb.)
   - Harf-sayı kombinasyonları (A-5, B12)
   
2. Blok Kodu: (%20 güvenilirlik)
   - "Blok A", "A Blok" formatları
   
3. Kişi Adı: (%40 güvenilirlik)
   - Büyük harfle başlayan 2 kelime
   - Türkçe karakter desteği
   
4. Anahtar Kelimeler: (%5 her biri)
   - "aidat", "ödeme", "daire", "tahsilat"
```

### Geliştirme Önerileri

1. **Makine Öğrenmesi Entegrasyonu**
   - Geçmiş eşleşmelerden öğrenme
   - Fuzzy matching algoritmaları

2. **Gelişmiş Pattern Matching**
   - Regex pattern kütüphanesi
   - Banka bazında özelleştirilmiş kurallar

3. **Sakin Veritabanı Entegrasyonu**
   - İsim benzerlik skoru
   - Telefon numarası cross-check
   - IBAN eşleştirmesi

## 🔐 Güvenlik

### API Kimlik Bilgileri
- ⚠️ API key ve secret'ları **asla** git'e commit etmeyin
- `.env` dosyası veya encrypted database kullanın
- Production'da HTTPS zorunludur

### SQL Injection Koruması
- Tüm sorgular PDO prepared statements kullanır
- User input sanitize edilir

### XSS Koruması
- `htmlspecialchars()` kullanımı
- CSP headers önerilir

## 📊 Veritabanı Yapısı

### tahsilat_havuzu Tablosu

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| id | INT | Primary key |
| site_id | INT | Site ID (FK) |
| kasa_id | INT | Banka hesabı ID (FK) |
| daire_id | INT | Eşleşen daire ID (FK) |
| islem_tarihi | VARCHAR(50) | İşlem tarihi |
| aciklama | TEXT | İşlem açıklaması |
| tahsilat_tutari | DECIMAL(12,2) | Toplam tutar |
| islenen_tutar | DECIMAL(12,2) | İşlenen kısım |
| kalan_tutar | DECIMAL(12,2) | Kalan kısım |
| hareket_yonu | ENUM | Gelir/Gider |
| banka_ref_no | VARCHAR(100) | Banka referans no |
| kaynak | VARCHAR(20) | api, excel, manuel vb |
| created_at | TIMESTAMP | Oluşturulma |
| updated_at | TIMESTAMP | Güncelleme |

## 🐛 Sorun Giderme

### "Banka bulunamadı" Hatası
- `kasa` tablosunda ilgili bankanın `aktif_mi = 1` olduğundan emin olun
- `kasa_tipi = 'Banka'` olmalı

### "Eşleşmemiş işlem" Uyarısı
- Normal bir durumdur, manuel eşleştirme yapın
- Açıklamalarda kişi adı ve daire no yazılmasını isteyin

### API Zaman Aşımı
- `BankaApiService.php` içinde `CURLOPT_TIMEOUT` değerini artırın
- Tarih aralığını daraltın

## 📝 TODO / Geliştirme Planı

- [ ] Gerçek banka API'leri entegrasyonu
- [ ] Makine öğrenmesi ile eşleşme geliştirme
- [ ] Toplu eşleştirme özelliği
- [ ] Excel export özelliği
- [ ] Email bildirimleri (yeni tahsilat geldiğinde)
- [ ] Webhook desteği (gerçek zamanlı push)
- [ ] Multi-currency desteği
- [ ] API rate limiting
- [ ] Audit log (kim ne zaman eşleştirdi)

## 📞 Destek

Sorularınız için:
- Sistem Yöneticisi ile iletişime geçin
- Dokümantasyonu inceleyin
- Log dosyalarını kontrol edin (`logs/` klasörü)

---

**Son Güncelleme**: 2024
**Versiyon**: 1.0.0
**Geliştirici**: YonApp Ekibi
