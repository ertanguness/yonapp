<?php

require_once __DIR__ . '/configs/bootstrap.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");



if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    $returnUrl = urlencode($_SERVER["REQUEST_URI"]);
    if (!isset($_GET["p"])) {
        $returnUrl = urlencode("ana-sayfa");
    }
    header("Location: sign-in?returnUrl={$returnUrl}");
   exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$user = $_SESSION['user'];
$user_id = $user->id;
$email = $user->email;

use App\Services\FlashMessageService;
use Model\SitelerModel;

// use Model\MyFirmModel;

// $myFirmObj = new MyFirmModel();
// $myFirms = $myFirmObj->getMyFirmByUserId(); // Firma kontrolü


//Kullanıcının sitelerin getir
$Site= new SitelerModel();
$mySites = $Site->Sitelerim(); // Kullanıcının sitelerini getir
// echo count($mySites);
// exit;

if (count($mySites) == 1) {
    if (count($mySites) === 1) {
        $_SESSION['site_id'] = $mySites[0]->id;
    }
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';
    header("Location: $redirectUri");
    exit();
}elseif(count($mySites) < 1){
    $_SESSION['site_id'] = null;
    FlashMessageService::add('info', 'Bilgi', 'Lütfen önce bir site ekleyiniz.');
    header("Location: site-ekle");
}

// Seçim sonrası yönlendirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_id'])) {
    $_SESSION['site_id'] = $_POST['site_id'];
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';
    header("Location: $redirectUri");
    exit();
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>YonApp - Apartman/Site Yönetim Sistemi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
    <!-- Feather Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        /* Arka plan animasyonu */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="1200" height="600" fill="%23667eea"/><rect width="1200" height="600" fill="url(%23grid)"/></svg>');
            pointer-events: none;
            z-index: 1;
        }

        .main-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1000px;
            padding: 20px;
        }

        /* Header */
        .welcome-header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
            animation: slideDown 0.6s ease-out;
        }

        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .welcome-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .welcome-header .site-count {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
        }

        /* Controls Bar */
        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .toggle-control {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.95);
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .toggle-control:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .toggle-control .form-check-input {
            width: 20px;
            height: 20px;
            margin: 0;
            cursor: pointer;
        }

        .toggle-control label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: #ff6b6b;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            font-size: 1.3rem;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            background: #ff5252;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            color: white;
        }

        /* Sites Grid */
        .sites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .site-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .site-card:nth-child(1) { animation-delay: 0.1s; }
        .site-card:nth-child(2) { animation-delay: 0.2s; }
        .site-card:nth-child(3) { animation-delay: 0.3s; }
        .site-card:nth-child(n+4) { animation-delay: 0.4s; }

        .site-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }

        .site-card.inactive {
            opacity: 0.5;
            display: none;
        }

        .site-card.inactive.show {
            display: block;
            opacity: 1;
        }

        /* Site Card Header */
        .site-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .site-logo {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-right: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .site-info h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
        }

        .site-info p {
            font-size: 0.85rem;
            color: #666;
            margin: 0;
            line-height: 1.4;
            max-height: 2.4em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* Site Card Footer */
        .site-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .site-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
        }

        .status-badge.inactive {
            background: #ff9800;
        }

        .status-text {
            color: #4caf50;
        }

        .status-text.inactive {
            color: #ff9800;
        }

        .site-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            border: 2px solid #ddd;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            margin: 0;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            color: #666;
        }

        /* Info Box */
        .info-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .info-section h5 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .info-section h5 i {
            font-size: 1.3rem;
        }

        .info-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-section li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-section li:last-child {
            border-bottom: none;
        }

        .info-section li::before {
            content: '✓';
            color: #667eea;
            font-weight: bold;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-header h1 {
                font-size: 1.8rem;
            }

            .sites-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .controls-bar {
                flex-direction: column;
                margin-bottom: 30px;
            }

            .toggle-control,
            .logout-btn {
                width: 100%;
            }

            .logout-btn {
                width: auto;
            }

            .info-section {
                padding: 20px;
            }
        }
    </style>

</head>

<body>
    <div class="main-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1>Hoş Geldiniz 👋</h1>
            <p class="subtitle"><?= htmlspecialchars($user->full_name ?? $user->email) ?></p>
            <div class="site-count">
                <i class="feather" style="display: inline; margin-right: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </i>
                <?= count($mySites) ?> Siteniz Bulundu
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-bar">
            <div class="toggle-control">
                <input class="form-check-input" type="checkbox" id="showInactiveSwitch">
                <label class="form-check-label" for="showInactiveSwitch">
                    <i class="feather" style="display: inline; width: 16px; height: 16px; margin-right: 5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </i>
                    Pasif Siteleri Göster
                </label>
            </div>
            <a href="logout.php" title="Çıkış Yap" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3m7-5l5-5m0 0l-5-5m5 5H9"></path>
                </svg>
            </a>
        </div>

        <!-- Sites Grid -->
        <div class="sites-grid">
            <?php foreach ($mySites as $index => $site): ?>
                <form method="POST" class="firm-select-form">
                    <input type="hidden" name="site_id" value="<?= $site->id ?>">
                    <div class="site-card list-item <?= $site->aktif_mi == 0 ? 'inactive' : '' ?>" data-site-id="<?= $site->id ?>">
                        <!-- Site Card Header -->
                        <div class="site-card-header">
                            <?php
                                $fullLogo = $site->logo_path ?? '';
                                $logoSrc = !empty($fullLogo) ? '/assets/images/logo/' . $fullLogo : '/assets/images/logo/default.png';
                            ?>
                            <img src="<?= $logoSrc ?>" alt="<?= htmlspecialchars($site->site_adi) ?>" class="site-logo">
                            <div class="site-info">
                                <h3><?= htmlspecialchars($site->site_adi) ?></h3>
                                <?php if (!empty($site->tam_adres)): ?>
                                    <p><?= htmlspecialchars($site->tam_adres) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Site Card Footer -->
                        <div class="site-card-footer">
                            <div class="site-status">
                                <span class="status-badge <?= !$site->aktif_mi ? 'inactive' : '' ?>"></span>
                                <span class="status-text <?= !$site->aktif_mi ? 'inactive' : '' ?>">
                                    <?= $site->aktif_mi ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </div>
                            <div class="site-toggle">
                                <input class="form-check-input firm-status-switch" type="checkbox" data-site-id="<?= $site->id ?>" id="firmStatusSwitch<?= $site->id ?>" <?= $site->aktif_mi ? 'checked' : '' ?>>
                                <label class="form-check-label" for="firmStatusSwitch<?= $site->id ?>"></label>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <h5>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Kullanım Talimatları
            </h5>
            <ul>
                <li><strong>Bir Site Seçin:</strong> Sisteme giriş yapmak için aşağıdan kayıtlı sitelerinizden birini seçiniz.</li>
                <li><strong>Durum Değiştirme:</strong> Her bir kartın sağ alt köşesindeki anahtar ile sitenizin durumunu (Aktif/Pasif) değiştirebilirsiniz.</li>
                <li><strong>Pasif Siteleri Görmek:</strong> Varsayılan olarak sadece aktif siteler gösterilir. Pasif siteleri görmek için üstteki "Pasif Siteleri Göster" seçeneğini aktif hale getirin.</li>
                <li><strong>Çıkış Yapmak:</strong> Oturum sonlandırmak için sağ üst köşedeki çıkış butonunu kullanın.</li>
            </ul>
        </div>
    </div>

    <!-- JS -->
    <script src="./assets/js/jquery.3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Firma kartına tıklayınca form submit
            $('.list-item').click(function() {
                $(this).closest('form').submit();
            });

            // Switch tıklandığında kartın click'ine gitmesini engelle
            $('.firm-status-switch').click(function(event) {
                event.stopPropagation();
            });

            // Firma durumunu değiştiren switch
            $('.firm-status-switch').change(function() {
                var siteID = $(this).data('site-id');
                var isActive = $(this).is(':checked') ? 1 : 0;
                var $card = $(this).closest('.site-card');

                $.ajax({
                    url: 'update_firm_status.php',
                    type: 'POST',
                    data: {
                        site_id: siteID,
                        is_active: isActive
                    },
                    success: function(response) {
                        console.log('Durum güncellendi: ', response);

                        // Durum badge ve text'i güncelle
                        var $statusBadge = $card.find('.status-badge');
                        var $statusText = $card.find('.status-text');
                        
                        if (isActive) {
                            $statusBadge.removeClass('inactive');
                            $statusText.removeClass('inactive').text('Aktif');
                        } else {
                            $statusBadge.addClass('inactive');
                            $statusText.addClass('inactive').text('Pasif');
                        }

                        // Eğer 'pasifleri göster' kapalı ve firma pasif olduysa → kartı gizle
                        if (!$('#showInactiveSwitch').is(':checked') && isActive == 0) {
                            $card.fadeOut(300, function() {
                                $(this).addClass('inactive');
                                $(this).fadeIn(0);
                            });
                        }
                    },
                    error: function() {
                        alert('Durum güncellenirken hata oluştu!');
                        // AJAX hatası olursa switch geri çevir
                        $(this).prop('checked', !isActive);
                    }
                });
            });

            // Pasifleri göster switch kontrolü
            $('#showInactiveSwitch').change(function() {
                var showInactive = $(this).is(':checked');

                $('.site-card').each(function() {
                    var isActive = $(this).find('.firm-status-switch').is(':checked');
                    if (!showInactive && !isActive) {
                        $(this).addClass('inactive');
                    } else {
                        $(this).removeClass('inactive');
                    }
                });
            });

            // Sayfa yüklenirken default olarak pasifleri gizle
            if (!$('#showInactiveSwitch').is(':checked')) {
                $('.site-card').each(function() {
                    var isActive = $(this).find('.firm-status-switch').is(':checked');
                    if (!isActive) {
                        $(this).addClass('inactive');
                    }
                });
            }
        });
    </script>
</body>

<script>
    if (window.history && window.history.pushState) {
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.location.href = 'sign-in.php'; // Geri basarsa buraya yönlendir
        };
    }
</script>

</html>