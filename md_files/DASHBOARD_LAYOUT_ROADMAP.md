# Dashboard Kart Yerleşimi ve Sıralama Yol Haritası

## Genel Bakış
- Ana sayfa kartları `pages/home/cards` klasöründeki dosyalardan yüklenir.
- Kullanıcıya özel sıralama ve iki kolonlu yerleşim veritabanında saklanır.
- Mobilde yanlış sürüklemeyi önlemek için yalnızca header’daki `drag-handle` ile sürükleme yapılır.

## Dosya Yapısı
- Kart dosyaları: `pages/home/cards/<widget_key>.php`
- Ana sayfa: `pages/home/home.php` (kartları include eder ve iki kolon render eder)
- API: `pages/home/api.php` (`save_dashboard_layout` ve `save_dashboard_order`)
- Model: `Model/UserDashBoardModel.php`

## Yeni Kart Ekleme
1. `pages/home/cards/<widget_key>.php` dosyasını oluştur.
2. Dış sarmalayıcıyı ayarla:
   ```php
   <div class="col-xxl-12 card-wrapper" data-card="my-new-card">
     <div class="card stretch stretch-full">
       <div class="card-header d-flex align-items-center justify-content-between">
         <h5 class="card-title mb-0">Başlık</h5>
         <span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>
       </div>
       <div class="card-body">
         <!-- içerik -->
       </div>
     </div>
   </div>
   ```
3. İsteğe bağlı: `pages/home/home.php` içindeki `defaultOrder` dizisine `<widget_key>` ekle. Eklememesen de dosya otomatik keşfedilir ve sol kolonda görünür.

## Sürükle-Bırak ve Kaydetme
- İki kolon: `#dashboard-col-1` ve `#dashboard-col-2` (her biri `col-xxl-6 col-md-6`).
- SortableJS yalnızca `.drag-handle` ile sürüklemeyi kabul eder.
- Bırakıldığında payload:
  ```json
  [
    {"widget_key":"quick-actions-card","column":1,"position":0},
    {"widget_key":"calendar-card","column":2,"position":0}
  ]
  ```
- İstek:
  - `action`: `save_dashboard_layout`
  - `items`: JSON string

## API Sözleşmeleri
- `save_dashboard_layout`:
  - Girdi: `items: [{widget_key: string, column|col: number, position: number}]`
  - Normalizasyon: `column` alanı yoksa `col` kullanılır; her ikisi de desteklenir.
  - Çıktı: `{status: "success", message: "Dashboard layout saved"}`
- `save_dashboard_order` (tek kolon kullanılınca):
  - Girdi: `order: [string | {widget_key: string}]`
  - Çıktı: `{status: "success", message: "Dashboard order saved"}`

## Model Metodları
- `saveUserDashboardLayout(int $userId, array $items): void`
  - `DELETE` ardından `INSERT (user_id, widget_key, position, col)` (kolon alanı varsa)
- `getUserDashboardLayout(int $userId): array`
  - Döner: `[{widget_key, position, column}]` (DB’de `col` saklanır, `column` olarak döner)
- `saveUserDashboardOrder(int $userId, array $order): void`
  - Tek kolon dizin sıralamasını kaydeder.

## Veritabanı Şema
- Kart anahtarı ve iki kolon için önerilen sütunlar:
  ```sql
  ALTER TABLE user_dashboard_order MODIFY widget_key VARCHAR(100) NOT NULL;
  ALTER TABLE user_dashboard_order ADD COLUMN col TINYINT NOT NULL DEFAULT 1;
  ```
- Not: `column` ayrılmış anahtar sözcük olduğu için sütun adı `col` olarak kullanılmalıdır.

## Varsayılan Sıra ve Otomatik Keşif
- `home.php` içinde `defaultOrder` dizisi ile başlangıç önceliği belirlenir.
- Otomatik keşif: `pages/home/cards/*.php` dosya adları (`.php` hariç) bulunur ve layout’ta yoksa sol kolona eklenir.

## Mobil Kullanım
- Sürükleme sadece `.drag-handle` ile yapılır (`handle: ".drag-handle"`).
- `.drag-handle` stil ayarları `home.php` içine eklenmiştir (dokunma/kaydırma uyumu).

## Hızlı Kontrol Listesi
- Kart dosya adı = `widget_key` ve `data-card` ile aynı olmalı.
- Header içinde `drag-handle` olmalı.
- `widget_key` sütunu `VARCHAR` olmalı; aksi halde MySQL sayıya zorlayıp `0` kaydedebilir.
- İki kolon kalıcılığı için `col` sütunu eklenmiş olmalı.

