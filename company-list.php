<?php

require_once __DIR__ . '/configs/bootstrap.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");



if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    $returnUrl = urlencode($_SERVER["REQUEST_URI"]);
    if (!isset($_GET["p"])) {
        $returnUrl = urlencode("index?p=home");
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



use Model\SitelerModel;

// use Model\MyFirmModel;

// $myFirmObj = new MyFirmModel();
// $myFirms = $myFirmObj->getMyFirmByUserId(); // Firma kontrolü


//Kullanıcının sitelerin getir
$Site= new SitelerModel();
$mySites = $Site->Sitelerim(); // Kullanıcının sitelerini getir

if (count($mySites) == 1) {
    $_SESSION['site_id'] = $mySites[0]->id;
    // header('Location: index?p=home');
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'index?p=home';
    header("Location: $redirectUri");
    exit();
}

// Seçim sonrası yönlendirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_id'])) {
    $_SESSION['site_id'] = $_POST['site_id'];
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'index?p=home';
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

    <style>
        body {
            background: #f8f9fa;
        }

        .firm-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .firm-card:hover {
            transform: scale(1.01);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .firm-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        .inactive {
            display: none;
        }

        /* Ortak Switch Stil */
        .form-check-input {
            transform: scale(1.5);
            /* Switch boyutunu büyüt */
        }

        .form-check-label {
            font-size: 1.2rem;
            /* Label font boyutunu büyüt */
        }

        /* Pasifleri Göster Switch */
        #showInactiveSwitch {
            margin-right: 10px;
            /* Label ile arasını açmak için */
        }

        /* Firma durum switch */
        .form-check.firm-status-switch {
            margin-right: 10px;
            /* Label ile arasını açmak için */
        }

        .logout-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            background-color: #dc3545;
            /* Kırmızı arkaplan */
            border-radius: 50%;
            transition: background-color 0.2s, transform 0.2s;
        }

        .logout-icon img {
            width: 50px;
            height: 50px;
        }

        .logout-icon:hover {
            background-color: #bb2d3b;
            /* Hover rengi */
            transform: scale(1.1);
        }
    </style>

</head>

<body>
    <div class="container py-5">
        <div class="text-center mb-4">
            <h3 class="text-muted">Hoş Geldiniz, <strong><?= htmlspecialchars($user->full_name ?? $user->email) ?></strong></h3>
            <p class="text-muted"><?= count($mySites) ?> adet kayıtlı siteniz bulundu. İlerlemek için lütfen birini seçiniz.</p>



            <div class="row justify-content-center mb-2">
                <div class="col-md-8">
                    <div class="p-2 d-flex flex-row align-items-center justify-content-between">
                        <!-- Pasifleri Göster Switch (sol) -->
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="showInactiveSwitch">
                            <label class="form-check-label ms-2" for="showInactiveSwitch">Pasif Siteleri Göster</label>
                        </div>
                        <!-- Çıkış Butonu (sağ) -->
                        <a href="logout.php" title="Çıkış Yap" class="logout-icon">
                            <img src="../assets/images/icons/logout.png" alt="Çıkış">
                        </a>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php foreach ($mySites as $site): ?>
                        <form method="POST" class="mb-3 firm-select-form">
                            <input type="hidden" name="site_id" value="<?= $site->id ?>">
                            <div class="card firm-card shadow-sm p-3 list-item bg-white <?= $site->aktif_mi == 0 ? 'inactive' : '' ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/images/logo/google-wallet.png" alt="Firma Logo" class="firm-logo me-3">
                                        <div class="text-start">
                                            <h5 class="mb-0"><?= htmlspecialchars($site->site_adi) ?></h5>
                                            <?php if (!empty($site->tam_adres)): ?>
                                                <small class="text-muted"><?= htmlspecialchars($site->tam_adres) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Aktif/Pasif Switch -->

                                    <div class="form-check form-switch">
                                        <input class="form-check-input firm-status-switch" type="checkbox" data-site-id="<?= $site->id ?>" id="firmStatusSwitch<?= $site->id ?>" <?= $site->aktif_mi ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="firmStatusSwitch<?= $site->id ?>">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Bilgi Kutusu -->
            <div class="alert alert-info text-start mx-auto col-md-8" style="max-width: none;">
                <ul class="mb-0 ps-3">
                    <li>İlerlemek için kayıtlı sitelerinizden birini seçmeniz gerekmektedir.</li>
                    <li>Her bir kartın sağ üst köşesindeki <strong>Pasif anahtarı</strong> ile ilgili sitenizin durumunu değiştirebilirsiniz.</li>
                    <li>Varsayılan olarak sadece <strong>aktif siteler</strong> gösterilir. Pasif siteleri görmek için <strong>Pasifleri Göster</strong> anahtarını açabilirsiniz.</li>
                    <li> Bir site kartına tıklayarak ilgili siteyi seçebilir ve sisteme giriş yapabilirsiniz.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- JS -->
    <script src="./assets/js/jquery.3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Firma kartına tıklayınca form submit (var olan kodun)
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
                var $card = $(this).closest('.firm-card');

                $.ajax({
                    url: 'update_firm_status.php',
                    type: 'POST',
                    data: {
                        site_id: siteID,
                        is_active: isActive
                    },
                    success: function(response) {
                        console.log('Durum güncellendi: ', response);

                        // Eğer 'pasifleri göster' kapalı ve firma pasif olduysa → kartı gizle
                        if (!$('#showInactiveSwitch').is(':checked') && isActive == 0) {
                            $card.fadeOut(300, function() {
                                $(this).remove();
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

                $('.firm-card').each(function() {
                    var isActive = $(this).find('.firm-status-switch').is(':checked');
                    if (!showInactive && !isActive) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Sayfa yüklenirken default olarak pasifleri gizle
            if (!$('#showInactiveSwitch').is(':checked')) {
                $('.firm-card').each(function() {
                    var isActive = $(this).find('.firm-status-switch').is(':checked');
                    if (!isActive) {
                        $(this).hide();
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