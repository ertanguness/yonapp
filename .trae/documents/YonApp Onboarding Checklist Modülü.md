## Genel Yaklaşım

* Mevcut PHP tabanlı özel mimariye uygun modüler bir Onboarding altyapısı eklenir: `App/Modules/Onboarding`.

* Veritabanında iki tablo kullanılır: `onboarding_tasks` (görev tanımları), `user_onboarding_progress` (kullanıcı bazlı ilerleme ve durum).

* İlk login sonrası checklist bir kez gösterilir; kullanıcı isterse erteleyebilir. Görevler otomatik veya elle tamamlanabilir; tümü bitince otomatik açılmaz.

*  modern, minimal bir modal ve progress bar uygulanır. Bundle gerektirmez.

## Veri Şeması (Migration)

* `onboarding_tasks`

  * `id` PK, `key` VARCHAR(64) UNIQUE, `title` VARCHAR(255), `description` TEXT NULL, `order_no` INT DEFAULT 0, `active` TINYINT(1) DEFAULT 1

* `user_onboarding_progress`

  * `id` PK, `user_id` INT, `site_id` INT NULL, `task_key` VARCHAR(64), `is_completed` TINYINT(1) DEFAULT 0, `completed_at` DATETIME NULL, `source` ENUM('manual','auto') DEFAULT 'manual', `is_dismissed` TINYINT(1) DEFAULT 0, `dismissed_at` DATETIME NULL, `first_shown_at` DATETIME NULL

  * UNIQUE index `(user_id, site_id, task_key)`

* Migration SQL dosyaları oluşturulur ve yoksa otomatik yaratılır (ilk isteklerde OnboardingService tetikler).

## Varsayılan Görevler (Seed)

* `create_default_cash_account` — Kasa oluşturma

* `add_flat_types` — Daire türleri ekleme

* `create_site` — Site oluşturma

* `add_blocks` — Blok ekleme

* `add_apartments` — Daire ekleme

* `add_people` — Kişiler ekleme

* `set_default_cash_account` — Varsayılan kasa ayarı

* `add_dues_types` — Aidat türleri ekleme

* Seed işlemi: `onboarding_tasks` boşsa varsayılan kayıtlar eklenir.

## Backend Yapısı

* `App/Modules/Onboarding/Models/OnboardingTaskModel.php` — `onboarding_tasks` erişimi

* `App/Modules/Onboarding/Models/UserOnboardingProgressModel.php` — kullanıcı ilerleme

* `App/Modules/Onboarding/Services/OnboardingService.php`

  * `getTasks()`; `getUserProgress(user_id, site_id)`; `shouldShowChecklist(user_id, site_id)`; `dismiss(user_id, site_id)`; `completeTask(user_id, task_key, site_id, source)`; `seedDefaults()`; `ensureMigrations()`

* `App/Modules/Onboarding/Controllers/OnboardingController.php`

  * API eylemleri: `status`, `complete`, `dismiss`, `reset`

* `App/Modules/Onboarding/Policies/OnboardingPolicy.php`

  * Gate/roles ile görüntüleme/güncelleme yetki kontrolü (`$_SESSION['user_role']` veya `App\Services\Gate`)

* `App/Modules/Onboarding/Events/OnboardingEvents.php`

  * Basit `fire(event_key, context)` yardımcıları; ilgili API’lerde çağrılır

## Event Entegrasyonları (Otomatik Tamamlama)

* Site oluşturma: `pages/management/sites/api.php:13` → başarılı `save_sites` sonrası `create_site` tamamla

* Blok ekleme: `pages/management/blocks/api.php:11` → başarılı `save_blocks` sonrası `add_blocks` tamamla

* Daire ekleme: `pages/management/apartment/api.php:15` → başarılı `save_apartment` sonrası `add_apartments` tamamla

* Daire tipi ekleme: `pages/defines/apartment-type/api.php:10` → başarılı kayıt sonrası `add_flat_types` tamamla

* Kişi ekleme: `pages/management/peoples/api/KisilerGenelBilgilerApi.php:19` → başarılı `save_peoples` sonrası `add_people` tamamla

* Kasa oluşturma: `pages/finans-yonetimi/kasa/api.php:23` → ilk kasa ise `create_default_cash_account` tamamla

* Varsayılan kasa ayarı: `pages/finans-yonetimi/kasa/api.php:112` → `set_default_cash_account` tamamla

* Aidat türü ekleme: `pages/dues/dues-defines/api.php:19` → `add_dues_types` tamamla

## Rotalar ve Sayfalar

* `route.php`

  * `ilk-kurulum-durumu` → `pages/onboarding/status.php` (progress görünümü)

* `api/onboarding.php`

  * `action=status|complete|dismiss|reset` — JSON API

* `index.php`

  * Body içine `#onboarding-checklist-root` konur; Vue uygulaması burada mount olur ve sadece `shouldShowChecklist` true ise açılır

* `assets/js/onboarding.js` (module)

  * API’den görev ve ilerleme çekimi

  * Modal (Tailwind) + progress bar

  * Her görevin yanında “Tamamla”; tıklamada `complete` çağrısı

  * “Sonra Göster”; `dismiss` çağrısı

  * Tamamlanan görev üstü çizili; progress anlık güncellenir

  * Checkbox için basit SVG animasyon

## Gösterim Kuralları

* İlk login sonrası bir kez göster: login akışında (Auth sonrası ilk sayfa yüklemesinde) `shouldShowChecklist` kontrolü; gösterildiyse `first_shown_at` işaretlenir

* Sonra göster: `is_dismissed=1` ve zaman damgası; kullanıcı menüden `ilk-kurulum-durumu` sayfasına giderek tekrar görüntüler

* Tüm görevler tamamlanınca `shouldShowChecklist=false` ve otomatik açılmaz

## Doğrulama

* API uçları Postman ile test edilebilir; kayıt sonrası event noktaları tetiklendiğinde ilerleme otomatik güncellenir

* UI test: giriş yapan kullanıcı için modal açılması, butonların çalışması ve progress güncellenmesi

## UX Önerileri

* Görevleri mantıksal gruplar ve kısa açıklamalarla göster; her görev için “Git” butonu ilgili modüle yönlendirsin

* İlerleme % ve tamamlanan görev sayısı üstte dursun; “Sonra Göster” butonu erişilebilir konumda ve açıkça etiketli olsun

* İlk site oluşturulmamışsa modalda bu görevi birincil aksiyon olarak vurgula; tamamlanmışların listede en alta alınması okunabilirliği artırır

* Küçük başarı mikro-animasyonları (checkbox tik, progress bar yumuşak geçiş) motivasyonu artırır

