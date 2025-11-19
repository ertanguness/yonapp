# Duyuru Modülü

## Listeleme
- URL: `/pages/duyuru-talep/admin/api/APIDuyuru.php?datatables=1`
- Metod: `GET`
- Parametreler: `draw`, `start`, `length`, `search[value]`
- Dönen: DataTables biçiminde JSON

## Tekil Kayıt
- URL: `/pages/duyuru-talep/admin/api/APIDuyuru.php?id={id}`
- Metod: `GET`
- Dönen: `{ status, message, data }`

## Oluşturma
- URL: `/pages/duyuru-talep/admin/api/APIDuyuru.php`
- Metod: `POST`
- Gövde: `title`, `content`, `start_date?`, `end_date?`, `status?`
- Güvenlik: `Authorization: Bearer <JWT>` veya oturum ile yetki
- Dönen: `{ status, message, id }`

## Güncelleme
- URL: `/pages/duyuru-talep/admin/api/APIDuyuru.php`
- Metod: `PUT`
- Gövde: `id`, alanlar: `title|content|start_date|end_date|status`
- Güvenlik: `Authorization: Bearer <JWT>` veya oturum ile yetki
- Dönen: `{ status, message }`

## Silme
- URL: `/pages/duyuru-talep/admin/api/APIDuyuru.php`
- Metod: `DELETE`
- Gövde: `id`
- Güvenlik: `Authorization: Bearer <JWT>` veya oturum ile yetki
- Dönen: `{ status, message }`

## JWT
- Algoritma: `HS256`
- Gizli Anahtar: `.env` içinde `JWT_SECRET`
- Örnek payload: `{ "sub": "user-id", "exp": time()+3600 }`

## Arayüzler
- Liste sayfası: `duyuru-list.php` DataTables ile sayfalama/filtreleme
- Yönetim sayfası: `duyuru-manage.php` form doğrulama ve kaydetme
- İstemci JS: `pages/duyuru-talep/admin/js/duyuru.js`

## Veritabanı
- Tablo: `duyurular`
- Alanlar: `id, baslik, icerik, baslangic_tarihi, bitis_tarihi, durum, olusturulma_tarihi`