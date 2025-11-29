## Kapsam

* Yeni toplu SMS gönderim arayüzü ve iş akışı.

* Sunucu tarafı DataTables ile sakinlerin listelenmesi ve filtrelenmesi.

* Dinamik değişkenli mesaj, onay/önizleme, güvenlik ve loglama.

## UI (toplu_smsgonder_.php)

* `#smsResidentsTable` isimli yeni DataTable eklenir; kolonlar: `Seç` (checkbox), `Daire Kodu`, `Ad Soyad`, `Üyelik Türü`, `Durum (Aktif/Pasif)`, `Çıkış Tarihi`, `Telefon`.

* Üst araç çubuğu: `Tümünü Seç`, `Seçimi Kaldır`, `Seçililere Mesaj`, filtre seçicileri (Kiracı/Ev Sahibi ve Aktif/Pasif) — DataTables kolon araması ile entegre.

* Var olan `sms.js` dahil ve `#SendMessage` modalı yeniden kullanılır.

## Data Kaynağı (api/toplu\__sms\_server\_side_.php)

* Mevcut dosyaya aksiyon bazlı endpoint eklenir: `action=sms_kisiler`.

* `KisilerModel` + `DairelerModel` + üyelik/durum bilgileri birleştirilir; telefon alanı temiz format (`telefon_temiz`) döndürülür.

* DataTables uyumlu JSON: `draw`, `recordsTotal`, `recordsFiltered`, `data`.

* Sıralama ve kolon araması: `daire_kodu` için doğal sıralama, diğer kolonlar için basit anahtarlar.

## Mesaj Paneli (sms.js + modal)

* Dinamik değişken butonları: `{ADISOYADI}`, `{BORÇBAKİYESİ}`, `{SİTEADI}` — imleç konumuna eklenir.

* Ad/Soyad’ın mesaja dahil edilmesini aç/kapa seçeneği.

* Şablon seçimi: mevcut bildirim şablonlarından yükleme (varsa `notifications`/ayarlar üzerinden) ve mesaj alanına aktarma.

* Karakter sayacı ve SMS adet hesabı (GSM 03.38/Unicode tespiti; 160/70 limitlerine göre concat hesaplama) — `initSmsModal` genişletilir.

* Seçili satırlardan alıcı listesi `recipients` olarak hazırlanır.

## Gönderim Akışı (APIsms.php)

* İstek şeması: `{ message, recipients, senderID, csrf_token }`.

* CSRF kontrolü: `Security::csrf()` ile frontend’e token, backend’de doğrulama (`Security::checkCsrfToken()` veya eşleşme kontrolü).

* Yetki kontrolü: `Gate::allows('email_sms_gonder')` zorunlu.

* Değişken doldurma: alıcı bazında `{ADISOYADI}` (`KisilerModel`), `{BORÇBAKİYESİ}` (`FinansalRaporModel::getKisiGuncelBorcOzet`), `{SİTEADI}` (`Site::getCurrentSite()->site_adi`). Her alıcı için kişiselleştirilmiş metin üretilir.

* SMS gönderimi: `SmsGonderService::gonder([...])` mevcut entegrasyonla.

* Oran sınırı: site başına dakikada X SMS limiti (ör. 60). Basit limiter: `notifications` tablosundan son 60sn içindeki SMS sayımı; limit aşımında 429 döndür.

* Loglama: `notifications` tablosuna kayıt (tarih, alıcı sayısı, mesaj içeriği, başarı/başarısız). Kısmi başarılarda detaylı rapor.

## Güvenlik

* CSRF zorunlu; POST JSON + token.

* API kimlik bilgileri `.env`/`settings` — düz metin saklama yerine `Security::encrypt/decrypt` ile şifreli tutma (ayarlar yaz/okuma noktaları güncellenir).

* Gate yetkilendirme; yetkisiz erişimde 403.

## Test Senaryoları

* Değişken kombinasyonları: sadece `{ADISOYADI}`, tüm değişkenler, eksik verilerde graceful fallback.

* Uzun mesaj ve concat SMS: 160/70 sınırları, 2–3 parça mesaj sayacı doğrulaması.

* Özel karakterler: Unicode/Emoji içeren mesajlar.

* Çoklu alıcı: 2–50 kişi; başarı ve kısmi başarısızlık raporu.

* Hata durumları: CSRF eksik/yanlış, limit aşıldı (429), yetkisiz (403), API hatası (provider dönüşü).

## Kod Referansları

* DataTables yardımcıları: `src/app.js` (initDataTable, attachDtColumnSearch).

* Gönderim API: `pages/email-sms/api/APIsms.php`.

* SMS servis: `App/Services/SmsGonderService.php`.

* Borç özeti: `Model/FinansalRaporModel.php` (ör. `getKisiGuncelBorcOzet`).

* Site adı: `App/Helper/Site.php` → `getCurrentSite()`.

Onayınızla birlikte uygulanacak değişiklikler: `list.php` UI eklemeleri, `server_processing.php` aksiyon şubesi, `sms.js` genişletmeleri, `APIsms.php` güvenlik ve kişiselleştirme, ayarlarda şifreleme ve oran sınırlaması.
