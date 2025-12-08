<?php
$site_id = isset($_SESSION['site_id']) ? $_SESSION['site_id'] : 0;

// require_once "App/Helper/company.php";

// $company = new CompanyHelper();


use App\Helper\Site;
use App\Services\Gate;
use Model\DairelerModel;





// Mevcut URL'yi al
$current_url = $_SERVER['REQUEST_URI'];

// URL'yi parse et
$url_parts = parse_url($current_url);

// Query string'i al ve parse et
parse_str($url_parts['query'] ?? '', $query_params);

// theme parametresini kontrol et ve değiştir
if (isset($query_params['theme']) && $query_params['theme'] === 'dark') {
    $query_params['theme'] = 'light';
} else {
    $query_params['theme'] = 'dark';
}


// Yeni query string oluştur
$new_query_string = http_build_query($query_params);

// Yeni URL oluştur
$new_url = $url_parts['path'] . '?' . $new_query_string;

// Kullanıcı avatarı için dinamik yol
$avatarPath = "/assets/images/avatar/1.png";
try {
    if (isset($_SESSION['user']->id)) {
        $uid = (int)$_SESSION['user']->id;
        $baseFs = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/') . "/uploads/avatars/user_{$uid}";
        foreach (["jpg", "jpeg", "png", "webp"] as $ext) {
            $candidate = $baseFs . "." . $ext;
            if (file_exists($candidate)) {
                $avatarPath = "/uploads/avatars/user_{$uid}.{$ext}";
                break;
            }
        }
    }
} catch (\Throwable $e) {
    // sessiz geç
}
?>
<!--! ================================================================ !-->
<!--! [Start] Header !-->
<!--! ================================================================ !-->
<header class="nxl-header">
    <div class="header-wrapper">
        <!--! [Start] Header Left !-->
        <div class="header-left d-flex align-items-center gap-4">
            <!--! [Start] nxl-head-mobile-toggler !-->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <!--! [Start] nxl-head-mobile-toggler !-->
            <!--! [Start] nxl-navigation-toggle !-->
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
            <!--! [End] nxl-navigation-toggle !-->
        </div>
        <style>
            .py-07 {
                padding-top: 0.75rem !important;
                padding-bottom: 0.75rem !important;
            }

            .py-08 {
                padding-top: 0.8rem !important;
                padding-bottom: 0.8rem !important;
            }

            .nxl-header img.user-avtar {
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                object-position: center !important;
                border: 2px solid #d1d5db;
                background-color: #f0f2f5;
                display: inline-block;
            }

            .nxl-user-dropdown .dropdown-header img.user-avtar {
                width: 56px !important;
                height: 56px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                object-position: center !important;
                border: 3px solid #d1d5db;
                background-color: #f0f2f5;
                display: inline-block;
            }

            /* Dark Mode Select2 Fix */
            html.app-skin-dark .select2-container--default .select2-selection--single {
                background-color: #0f172a !important;
                border-color: #1b2436 !important;
            }
            html.app-skin-dark .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #b1b4c0 !important;
            }
            html.app-skin-dark .select2-dropdown {
                background-color: #0f172a !important;
                border-color: #1b2436 !important;
            }
            html.app-skin-dark .select2-results__option {
                color: #b1b4c0 !important;
            }
            html.app-skin-dark .select2-container--default .select2-results__option--highlighted[aria-selected],
            html.app-skin-dark .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #1b2436 !important;
                color: #fff !important;
            }
        </style>

        <!--! [End] Header Left !-->
        <div class="header d-flex me-auto">
            <div class="d-flex align-items-center">
                <div class="col-6 d-flex me-auto">

                    <?php if (Gate::isResident()) {
                        $currentSite = (new Site())->getCurrentSite();
                        if ($currentSite) {
                            echo '<span class="text-nowrap site-select" style="color: #333;">' . htmlspecialchars($currentSite->site_adi ?? 'Site Adı Yok', ENT_QUOTES, 'UTF-8') . '</span>';
                        }
                    } else { ?>
                        <div class="input-group flex-nowrap w-100 p-0 site-select ps-3" style="min-width: 260px;">
                            <div class="input-group-text"><i class="feather-grid"></i></div>
                            <?php

                            if (basename($_SERVER['PHP_SELF']) != 'company-list.php') {
                                echo Site::SitelerimSelect("mySite", $site_id);
                            }

                            ?>
                        </div>

                    <?php        }           ?>

                    <!-- Arama Kutusu -->
                    <?php if (!Gate::isResident()) { ?>
                        <?php if (Gate::allows('yonetici_aidat_odeme')) { ?>
                            <div class="d-flex align-items-center" id="globalHeaderSearchWrap">
                                <a href="javascript:void(0);" class="nxl-head-link me-0" id="globalHeaderSearchToggle"><i class="feather-search"></i></a>
                                <div class="d-none ms-2 position-relative" id="globalHeaderSearchBox" style="width: 180px;">
                                    <input type="text" class="form-control"
                                        autocomplete="off"
                                        id="globalHeaderSearch" placeholder="Daire kodu veya ad soyad">
                                    <div id="globalHeaderSearchResults" class="shadow" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1055; display: none; background: #fff; border: 1px solid #e5e7eb; border-top: 0; max-height: 280px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        <?php } ?>

                    <?php } ?>
                    <?php
                    $selectedApartmentId = (int)($_SESSION['selected_apartment_id'] ?? 0);
                    $selectedApartmentCode = '';

                    if ($selectedApartmentId && $site_id) {
                        try {
                            $Daireler = new DairelerModel();
                            $apt = $Daireler->DaireBilgisi((int)$site_id, (int)$selectedApartmentId);
                            $selectedApartmentCode = (string)($apt->daire_kodu ?? '');
                        } catch (\Throwable $e) {
                        }
                    }
                    ?>
                    <div class="ms-3 d-flex align-items-center gap-2">
                        <?php if (!empty($selectedApartmentCode)) { ?>
                            <span class="badge bg-soft-primary text-primary border-soft-primary py-08">
                                <i class="feather-home me-1"></i><?php echo htmlspecialchars($selectedApartmentCode, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php } ?>
                        <?php if (!empty($selectedApartmentCode)) { ?>
                            <a href="/sakin/daire?clear_context=1" class="badge bg-soft-secondary text-secondary border-soft-secondary py-08">
                                <i class="feather-x me-1"></i>Temizle
                            </a>
                        <?php } ?>
                    </div>



                </div>

            </div>
        </div>
        <!--! [Start] Header Right !-->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">



                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0"
                            onclick="$('body').fullScreenHelper('toggle');">
                            <i class="feather-maximize maximize"></i>
                            <i class="feather-minimize minimize"></i>
                        </a>
                    </div>
                </div>
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>

                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" alt="user-image" class="rounded-circle user-avtar me-0" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" alt="user-image" class="rounded-circle user-avtar" />
                                <div>
                                    <h6 class="text-dark mb-0"><?php echo $_SESSION["user"]->full_name; ?> <span
                                            class="badge bg-soft-success text-success ms-1">PRO</span></h6>
                                    <span
                                        class="fs-12 fw-medium text-muted"><?php echo $_SESSION["user"]->email; ?></span>
                                </div>
                            </div>
                        </div>
                        <script>
                            (function() {
                                var $input = $('#globalHeaderSearch');
                                var $list = $('#globalHeaderSearchResults');
                                var $wrap = $('#globalHeaderSearchWrap');
                                var $box = $('#globalHeaderSearchBox');
                                var $toggle = $('#globalHeaderSearchToggle');
                                var $close = $('#globalHeaderSearchClose');
                                var pending;

                                function render(items) {
                                    if (!items || !items.length) {
                                        $list.hide().empty();
                                        return;
                                    }
                                    var html = '';
                                    for (var i = 0; i < items.length; i++) {
                                        var r = items[i] || {};
                                        var name = (r.adi_soyadi || '').toString();
                                        var code = (r.daire_kodu || '').toString();
                                        var uyelik = (r.uyelik_tipi || '').toString();
                                        var durum = (r.durum || '').toString();
                                        var durumClass = (durum.toLowerCase() === 'aktif') ? 'text-success' : 'text-danger';
                                        var uyelikLower = uyelik.toLowerCase();
                                        var roleTone = (uyelikLower.indexOf('kiracı') !== -1) ? 'warning' : (uyelikLower.indexOf('kat mal') !== -1 ? 'teal' : 'teal');
                                        var uyelikClass = (roleTone === 'warning') ? 'text-warning' : 'text-teal';
                                        html += '<a href="javascript:void(0)" class="d-block px-3 py-3 text-decoration-none" data-daire-kodu="' + $('<div>').text(code).html() + '" style="border-top:1px solid #f1f5f9;">' +
                                            '<div class="d-flex align-items-start">' +
                                            '<div class="avatar-text avatar bg-soft-' + roleTone + ' text-' + roleTone + ' border-soft-' + roleTone + ' rounded me-3">' + $('<div>').text(code).html() + '</div>' +
                                            '<div class="flex-grow-1">' +
                                            '<div class="fw-semibold text-dark">' + $('<div>').text(name).html() + '</div>' +
                                            '<div class="d-flex align-items-center gap-2 mt-1">' +
                                            '<span class="badge ' + uyelikClass + ' border border-dashed border-gray-500">' + $('<div>').text(uyelik).html() + '</span>' +
                                            '<span class="badge ' + durumClass + ' border border-dashed border-gray-500">' + $('<div>').text(durum).html() + '</span>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>' +
                                            '</a>';
                                    }
                                    $list.html(html).show();
                                }

                                function parseBlockFlat(daire) {
                                    daire = (daire || '').toString();
                                    var m = daire.match(/^([A-Za-zÇĞİÖŞÜ]+)\s*[-]?\s*(\d+)/);
                                    if (m) return {
                                        block: m[1],
                                        flat: m[2]
                                    };
                                    var letters = daire.match(/[A-Za-zÇĞİÖŞÜ]+/);
                                    var digits = daire.match(/\d+/);
                                    return {
                                        block: letters ? letters[0] : '',
                                        flat: digits ? digits[0] : ''
                                    };
                                }

                                function search(q) {
                                    if (pending) {
                                        clearTimeout(pending);
                                    }
                                    pending = setTimeout(function() {
                                        var term = (q || '').trim();
                                        if (term.length < 2) {
                                            render([]);
                                            return;
                                        }
                                        $.getJSON('/pages/dues/payment/server_processing.php', {
                                            action: 'sms_kisiler',
                                            'search[value]': term,
                                            start: 0,
                                            length: 7,
                                            draw: 1
                                        }).done(function(resp) {
                                            var rows = (resp && resp.data) ? resp.data : [];
                                            render(rows);
                                        }).fail(function() {
                                            render([]);
                                        });
                                    }, 200);
                                }
                                $input.on('input', function() {
                                    search(this.value);
                                });
                                $input.on('focus', function() {
                                    if ($list.children().length) {
                                        $list.show();
                                    }
                                });

                                function openBox() {
                                    $box.removeClass('d-none').addClass('d-inline-block');
                                    setTimeout(function() {
                                        $input.trigger('focus');
                                    }, 10);
                                }

                                function closeBox() {
                                    $box.addClass('d-none').removeClass('d-inline-block');
                                    $list.hide().empty();
                                    $input.val('');
                                }
                                $(document).on('click', function(e) {
                                    if (!$(e.target).closest('#globalHeaderSearchWrap').length) {
                                        closeBox();
                                    }
                                });
                                $toggle.on('click', function() {
                                    if ($box.hasClass('d-none')) {
                                        openBox();
                                    } else {
                                        closeBox();
                                    }
                                });
                                $close.on('click', function() {
                                    closeBox();
                                });
                                $(document).on('keydown', function(e) {
                                    if (e.key === 'Escape') {
                                        closeBox();
                                    }
                                });
                                $list.on('click', 'a', function() {
                                    var code = $(this).data('daire-kodu') || '';
                                    var url = '/index?p=yonetici-aidat-odeme&q=' + encodeURIComponent(code);
                                    window.location.href = url;
                                });
                                $input.on('keydown', function(e) {
                                    if (e.key === 'Enter') {
                                        var term = ($input.val() || '').trim();
                                        if (term) {
                                            window.location.href = '/index?p=yonetici-aidat-odeme&q=' + encodeURIComponent(term);
                                        }
                                    }
                                });
                            })();
                        </script>

                        <a href="/profile" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profil</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" id="headerLockScreenBtn">
                            <i class="feather-lock"></i>
                            <span>Kilit Ekranı</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="/logout" class="dropdown-item">
                            <i class="feather-log-out"></i>
                            <span>Çıkış</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--! [End] Header Right !-->
    </div>
</header>
<!--! ================================================================ !-->
<!--! [End] Header !-->
<!--! ================================================================ !-->

<!-- Global Kilit Ekranı -->
<div id="globalLockScreen" style="display: none;">
    <div class="lock-screen-content">
        <div class="lock-screen-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h2 class="lock-screen-title">Profil Korumalı</h2>
        <p class="lock-screen-subtitle">Bu alanı görüntülemek için şifrenizi girin</p>

        <img src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" class="lock-screen-avatar" alt="avatar">
        <div class="lock-screen-user-info">
            <div class="lock-screen-user-name"><?= htmlspecialchars($_SESSION['user']->full_name ?? '', ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <form id="globalLockForm" class="lock-screen-form">
            <div class="lock-screen-input-group">
                <label>Şifreniz</label>
                <input type="password" id="globalLockPassword" placeholder="Şifrenizi girin" autocomplete="current-password" autofocus>
            </div>

            <div id="globalLockAttemptsContainer" class="lock-screen-attempts">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Kalan deneme: <span id="globalLockAttempts">3</span>
            </div>

            <div id="globalLockInfoContainer" class="lock-screen-info" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Şifreyi hatırlamıyorsanız:</strong> 3 kez yanlış girişte otomatik olarak çıkış yapılacaksınız.
            </div>

            <button type="button" id="globalUnlockBtn" class="lock-screen-btn">
                <i class="fas fa-unlock me-2"></i>Kilidi Aç
            </button>
        </form>
    </div>
</div>