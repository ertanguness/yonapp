<?php

require_once __DIR__ . '/configs/bootstrap.php';

use App\Services\Gate;

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
// echo "<pre>";
//     print_r($user);
//     echo "</pre>";
//     exit;


$username = $user->full_name;

use Model\KisilerModel;
use Model\SitelerModel;
use App\Helper\Security;
use App\Services\FlashMessageService;

// use Model\MyFirmModel;
$Site = new SitelerModel();
$Kisi = new KisilerModel();


// $myFirmObj = new MyFirmModel();
// $myFirms = $myFirmObj->getMyFirmByUserId(); // Firma kontrolü


/** Kullanıcı site sakini ise kullanıcının sitesini sessiona ata */
if (Gate::isResident()) {
    $_SESSION['site_id'] = $Kisi->getSiteIdByKisiId($user_id);
    

    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';
    header("Location: $redirectUri");
    exit();
}

//Kullanıcının sitelerin getir
$mySites = $Site->Sitelerim(); // Kullanıcının sitelerini getir
$favoriteSites = $Site->getFavorites();

// Aktif ve pasif site sayılarını hesapla
$activeSitesCount = 0;
$inactiveSitesCount = 0;
foreach ($mySites as $site) {
    if ($site->aktif_mi == 1) {
        $activeSitesCount++;
    } else {
        $inactiveSitesCount++;
    }
}
$totalSitesCount = count($mySites);


if (count($mySites) == 1) {
    if (count($mySites) === 1) {
        $_SESSION['site_id'] = $mySites[0]->id;
    }
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';
    header("Location: $redirectUri");
    exit();
} elseif (count($mySites) < 1) {
    $_SESSION['site_id'] = null;
    FlashMessageService::add('info', 'Bilgi', 'Lütfen önce bir site ekleyiniz.');
    header("Location: site-ekle");
}

// Seçim sonrası yönlendirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_id'])) {
    $selSiteId = (int)$_POST['site_id'];
    try {
        $LockModel = new \Model\UserSiteLockModel();
        $isLocked = $LockModel->isLocked((int)$user_id, $selSiteId);
        if ($isLocked) {
            \App\Services\FlashMessageService::add('error', 'Site Kilitli', 'Bu site için ödeme gecikmesi bulunduğu için seçim engellendi.', 'ikaz2.png');
            header("Location: company-list.php");
            exit();
        }
    } catch (\Throwable $e) {}
    $_SESSION['site_id'] = $selSiteId;
    $Site->incrementClickCount($selSiteId);
    $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : 'ana-sayfa';
    header("Location: $redirectUri");
    exit();
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>YonApp - Site Seçim</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <!-- <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css" /> -->
    <!-- Feather Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <!-- Custom CSS -->
    <!-- <link rel="stylesheet" type="text/css" href="/assets/css/style.css" /> -->
    <?php
    include './partials/head.php';

    ?>

    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            width: 100%;
            background: #f8f9fa;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 0;
            display: flex;
            justify-content: center;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .topbar-container {
            max-width: 1400px;
            width: 100%;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #ff6b6b;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: #ff5252;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        /* Page Header */
        .page-header {
            padding: 0;
            background: white;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: center;
        }

        .page-header-container {
            max-width: 1400px;
            width: 100%;
            padding: 30px;
        }

        .page-header-container .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .page-header-container .page-subtitle {
            font-size: 0.95rem;
            color: #999;
            margin-top: 5px;
        }

        /* Content Area */
        .page-content {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Container */
        .content-container {
            width: 100%;
        }

        /* Search and Controls Card */
        .search-controls-card {
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-group { position: relative; flex: 1; min-width: 300px; }
        .search-controls-card .search-input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            transition: all 0.3s ease;
        }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }

        .search-controls-card .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-controls-card .search-input::placeholder {
            color: #999;
        }

        .search-controls-card .toggle-control {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }

        .search-controls-card .toggle-control .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            border: 2px solid #ddd;
        }

        .search-controls-card .toggle-control label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #555;
            font-size: 0.95rem;
        }

        .quick-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quick-actions .btn {
            border-radius: 8px;
        }

        .view-toggle .btn {
            border-radius: 8px;
        }

        .sites-container.list-view {
            grid-template-columns: 1fr;
        }

        /* Favorites Section */
        .favorites-section { background: white; border-radius: 8px; padding: 12px 16px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 16px; }
        .favorites-header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 12px; }
        .favorites-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }

        /* Sites Section */
        .sites-section { background: white; border-radius: 8px; padding: 12px 16px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 16px; }
        .sites-header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 12px; }

        /* Controls */
        .controls-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
        }

        .toggle-control {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toggle-control .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            border: 2px solid #ddd;
        }

        .toggle-control .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .toggle-control label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #555;
            font-size: 0.95rem;
        }

        /* Sites Grid */
        .sites-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .sites-container.search-active {
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
        }

        .sites-container.search-active form {
            flex: 0 0 calc(33.333% - 17px);
            max-width: 300px;
        }

        .site-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #eee;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .favorite-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            padding: 4px;
            cursor: pointer;
        }

        .favorite-toggle:hover svg {
            filter: drop-shadow(0 1px 2px rgba(245, 166, 35, 0.4));
        }

        .site-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .site-card.inactive {
            opacity: 0.5;
            display: none;
            pointer-events: none;
        }

        .site-card.inactive.show {
            display: block;
            opacity: 1;
            pointer-events: auto;
        }

        /* Site Card Header */
        .site-card-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .site-logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .site-header-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
        }

        .site-header-info p {
            font-size: 0.85rem;
            color: #999;
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
            width: 8px;
            height: 8px;
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
            width: 16px;
            height: 16px;
            margin: 0;
            cursor: pointer;
            border: 2px solid #ddd;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        /* Info Box */
        .info-section {
            background: white;
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .accordion-header {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border: none;
            width: 100%;
            text-align: left;
            transition: all 0.3s ease;
            user-select: none;
        }

        .accordion-header:hover {
            background: #f8f9fa;
        }

        .accordion-header h5 {
            color: #333;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .accordion-toggle {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
            color: #667eea;
            flex-shrink: 0;
        }

        .accordion-toggle svg {
            width: 20px;
            height: 20px;
        }

        .accordion-header.active .accordion-toggle {
            transform: rotate(180deg);
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            padding: 0 20px;
        }

        .accordion-content.active {
            max-height: 500px;
            padding: 0 20px 20px 20px;
        }

        .info-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-section li {
            padding: 10px 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-section li::before {
            content: '✓';
            color: #667eea;
            font-weight: bold;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #999;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .topbar {
                padding: 0;
            }

            .topbar-container {
                flex-direction: row;
                gap: 10px;
                padding: 12px 15px;
            }

            .topbar-breadcrumb {
                font-size: 0.85rem;
                flex: 1;
            }


            .page-header-container {
                padding: 20px 15px;
            }

            .page-content {
                padding: 15px;
            }

            .search-controls-card {
                flex-direction: column;
                align-items: stretch;
            }

            .search-controls-card .search-input {
                min-width: 100%;
                flex: 1;
            }

            .search-controls-card .toggle-control {
                width: 100%;
                justify-content: flex-start;
            }

            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .sites-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .stats-section,.info-section{
                display: none;
            }

            /* Quick actions: prevent horizontal overflow on mobile */
            .quick-actions {
                flex-wrap: wrap;
                gap: 6px;
            }
            .quick-actions .toggle-control,
            .quick-actions .view-toggle,
            .quick-actions .btn-group,
            .quick-actions #addSiteBtn {
                flex: 1 1 100%;
            }
            .quick-actions .btn {
                padding: .375rem .5rem;
            }
        }
    </style>

</head>

<body>
    <!-- Main Content -->

    <!-- Topbar -->
    <div class="page-content">

        <?php include_once 'partials/_flash_messages.php';  ?>

        <div class="topbar mb-3">
            <div class="topbar-container">
                <div class="topbar-breadcrumb">
                    <span>Hoşgeldiniz <b><?php echo htmlspecialchars($username); ?></b></span>
                </div>
                <a href="logout.php" class="logout-btn" title="Çıkış Yap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3m7-5l5-5m0 0l-5-5m5 5H9"></path>
                    </svg>
                    Çıkış
                </a>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row stats-section ">
            <div class="col-xxl-4 col-md-6">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <h5 class="fs-4"><?= $activeSitesCount ?></h5>
                            <span class="text-muted">Aktif Siteler</span>
                        </div>
                        <div class="avatar-text avatar-lg bg-success text-white rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <h5 class="fs-4"><?= $inactiveSitesCount ?></h5>
                            <span class="text-muted">Pasif Siteler</span>
                        </div>
                        <div class="avatar-text avatar-lg bg-warning text-white rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <h5 class="fs-4"><?= $totalSitesCount ?></h5>
                            <span class="text-muted">Toplam Siteler</span>
                        </div>
                        <div class="avatar-text avatar-lg bg-primary text-white rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

   
        <div class="content-container">
            

            <!-- Search and Controls Card -->
            <div class="search-controls-card">
                <div class="search-group">
                    <span class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input
                        type="text"
                        class="search-input"
                        id="siteSearchInput"
                        placeholder="Site adı, adres veya başka bilgi ile ara...">
                </div>
                <div class="quick-actions">
                    <div class="toggle-control">
                        <input class="form-check-input" type="checkbox" id="showInactiveSwitch">
                        <label class="form-check-label" for="showInactiveSwitch">Pasif Siteleri Göster</label>
                    </div>
                    <div class="view-toggle btn-group" role="group" aria-label="Görünüm">
                        <button type="button" class="btn btn-outline-primary" id="gridViewBtn" data-bs-toggle="tooltip" data-bs-title="Grid görünüm">Grid</button>
                        <button type="button" class="btn btn-outline-primary" id="listViewBtn" data-bs-toggle="tooltip" data-bs-title="Liste görünüm">Liste</button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Sırala">
                        <button type="button" class="btn btn-outline-secondary" id="sortAscBtn" data-bs-toggle="tooltip" data-bs-title="A→Z sırala">A→Z</button>
                        <button type="button" class="btn btn-outline-secondary" id="sortDescBtn" data-bs-toggle="tooltip" data-bs-title="Z→A sırala">Z→A</button>
                    </div>
                    <a href="site-ekle" class="btn btn-primary" id="addSiteBtn" data-bs-toggle="tooltip" data-bs-title="Yeni site ekle">Site Ekle</a>
                </div>
            </div>

            <!-- Favorites Section (Cards) -->
            <div class="favorites-section">
                <div class="favorites-header">
                    <h6 class="m-0">Favoriler</h6>
                    <small class="text-muted">Sık eriştiğiniz siteler</small>
                </div>
                <div class="favorites-container">
                    <?php if (!empty($favoriteSites)): ?>
                        <?php foreach ($favoriteSites as $site): ?>
                            <form method="POST" class="firm-select-form">
                                <input type="hidden" name="site_id" value="<?= $site->id ?>">
                                <div class="site-card list-item <?= $site->aktif_mi == 0 ? 'inactive' : '' ?>" data-site-id="<?= $site->id ?>">
                                    <button type="button" class="favorite-toggle" data-site-id="<?= $site->id ?>" aria-label="Favori" data-bs-toggle="tooltip" data-bs-title="Favoriye ekle/çıkar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#f5a623" stroke="#f5a623" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"></polygon>
                                        </svg>
                                    </button>
                                    <div class="site-card-header">
                                        <?php
                                        $fullLogo = $site->logo_path ?? '';
                                        $logoSrc = !empty($fullLogo) ? '/assets/images/logo/' . $fullLogo : '/assets/images/logo/default.png';
                                        ?>
                                        <img src="<?= $logoSrc ?>" alt="<?= htmlspecialchars($site->site_adi) ?>" class="site-logo">
                                        <div class="site-header-info">
                                            <h3><?= htmlspecialchars($site->site_adi) ?></h3>
                                            <?php if (!empty($site->tam_adres)): ?>
                                                <p><?= htmlspecialchars($site->tam_adres) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="site-card-footer">
                                        <div class="site-status">
                                            <span class="status-badge <?= !$site->aktif_mi ? 'inactive' : '' ?>"></span>
                                            <span class="status-text <?= !$site->aktif_mi ? 'inactive' : '' ?>">
                                                <?= $site->aktif_mi ? 'Aktif' : 'Pasif' ?>
                                            </span>
                                        </div>
                                        <div class="site-toggle">
                                            <input class="form-check-input firm-status-switch" type="checkbox" data-site-id="<?= $site->id ?>" id="firmStatusSwitchFav<?= $site->id ?>" <?= $site->aktif_mi ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">Henüz favori site bulunmuyor.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sites Grid -->
            <?php if (!empty($mySites)): ?>
                <div class="sites-section">
                    <div class="sites-header">
                        <h6 class="m-0">Siteler</h6>
                        <small class="text-muted">Kayıtlı siteleriniz</small>
                    </div>
                    <div class="sites-container">
                        <?php foreach ($mySites as $site): if (($site->favori_mi ?? 0) == 1) { continue; } ?>
                            <form method="POST" class="firm-select-form">
                                <input type="hidden" name="site_id" value="<?= $site->id ?>">
                                <div class="site-card list-item <?= $site->aktif_mi == 0 ? 'inactive' : '' ?>" data-site-id="<?= $site->id ?>">
                                    <button type="button" class="favorite-toggle" data-site-id="<?= $site->id ?>" aria-label="Favori" data-bs-toggle="tooltip" data-bs-title="Favoriye ekle/çıkar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="<?= ($site->favori_mi ?? 0) ? '#f5a623' : 'none' ?>" stroke="#f5a623" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"></polygon>
                                        </svg>
                                    </button>
                                    <div class="site-card-header">
                                        <?php
                                        $fullLogo = $site->logo_path ?? '';
                                        $logoSrc = !empty($fullLogo) ? '/assets/images/logo/' . $fullLogo : '/assets/images/logo/default.png';
                                        ?>
                                        <img src="<?= $logoSrc ?>" alt="<?= htmlspecialchars($site->site_adi) ?>" class="site-logo">
                                        <div class="site-header-info">
                                            <h3><?= htmlspecialchars($site->site_adi) ?></h3>
                                            <?php if (!empty($site->tam_adres)): ?>
                                                <p><?= htmlspecialchars($site->tam_adres) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="site-card-footer">
                                        <div class="site-status">
                                            <span class="status-badge <?= !$site->aktif_mi ? 'inactive' : '' ?>"></span>
                                            <span class="status-text <?= !$site->aktif_mi ? 'inactive' : '' ?>">
                                                <?= $site->aktif_mi ? 'Aktif' : 'Pasif' ?>
                                            </span>
                                        </div>
                                        <div class="site-toggle">
                                            <input class="form-check-input firm-status-switch" type="checkbox" data-site-id="<?= $site->id ?>" id="firmStatusSwitch<?= $site->id ?>" <?= $site->aktif_mi ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #ccc;">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <h4>Kayıtlı Site Bulunamadı</h4>
                    <p>Lütfen önce bir site ekleyiniz.</p>
                    <a href="site-ekle" class="btn btn-primary mt-3">Site Ekle</a>
                </div>
            <?php endif; ?>

        </div>
    </div>


    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./assets/js/jquery.3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function refreshFavoritesEmptyState() {
                var hasCards = $('.favorites-container .site-card').length > 0;
                if (hasCards) {
                    $('.favorites-container .text-muted').remove();
                } else {
                    if ($('.favorites-container .text-muted').length === 0) {
                        $('.favorites-container').append('<div class="text-muted">Henüz favori site bulunmuyor.</div>');
                    }
                }
            }
            // Accordion işlevi
            $('#accordionHeader').click(function() {
                var $header = $(this);
                var $content = $('#accordionContent');

                $header.toggleClass('active');
                $content.toggleClass('active');
            });

            // Firma kartına tıklayınca önce kilit ve borç kontrolü
            $(document).on('click', '.list-item', function() {
                var $form = $(this).closest('form');
                var siteId = $form.find('input[name=\"site_id\"]').val();
                var fd = new FormData();
                fd.append('action','site_lock_status');
                fd.append('user_id','<?= (int)$user_id ?>');
                fd.append('site_id', siteId);
                fetch((window.API_BASE||'') + '/pages/panel/api.php', { method:'POST', body: fd, credentials: 'same-origin' })
                  .then(function(r){ return r.text(); })
                  .then(function(t){ try { return JSON.parse(t); } catch(e){ return {status:'error'}; } })
                  .then(function(j){
                      if (j && j.status === 'success' && j.data) {
                          if (parseInt(j.data.locked||0,10) === 1) {
                              var months = j.data.unpaid_months||[];
                              var rowsHtml = '';
                              if (months.length > 0) {
                                  rowsHtml = months.map(function(m){
                                      return '<tr><td class="text-start text-secondary">'+m.period+'</td><td class="text-end fw-bold text-dark">'+(m.amount||0)+' TL</td></tr>';
                                  }).join('');
                              } else {
                                  rowsHtml = '<tr><td colspan="2" class="text-center text-muted">Detay bulunamadı</td></tr>';
                              }

                              Swal.fire({
                                  title: '',
                                  html: `
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                            </span>
                                        </div>
                                        <h3 class="mb-2 text-dark fw-bold">Erişim Engellendi!</h3>
                                        <p class="text-muted mb-4">Bu siteye giriş kısıtlanmıştır.</p>
                                        
                                        <div class="bg-light rounded-3 p-3 mb-3 text-start border">
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Site Adı</span>
                                                <span class="fw-bold text-dark">${j.data.site_name||''}</span>
                                            </div>
                                            
                                            <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Ödenmemiş Dönemler</h6>
                                            <div style="max-height: 150px; overflow-y: auto;">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tbody>
                                                        ${rowsHtml}
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="border-top mt-2 pt-3 d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-dark">Toplam Borç</span>
                                                <span class="fw-bold text-danger fs-4">${j.data.total_debt||0} TL</span>
                                            </div>
                                        </div>
                                        
                                        <p class="small text-muted mb-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info" style="vertical-align: text-bottom;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                            Lütfen ödeme yapmak için yönetim ile iletişime geçiniz.
                                        </p>
                                    </div>
                                  `,
                                  showConfirmButton: true,
                                  confirmButtonText: 'Tamam, Anlaşıldı',
                                  confirmButtonColor: '#dc3545',
                                  buttonsStyling: true,
                                  customClass: {
                                      popup: 'rounded-4 shadow-lg p-4',
                                      confirmButton: 'btn btn-danger btn-lg px-5 rounded-3 mt-2'
                                  },
                                  width: '450px'
                              });
                          } else {
                              $form.submit();
                          }
                      } else {
                          $form.submit();
                      }
                  })
                  .catch(function(){ $form.submit(); });
            });

            // Favori toggle butonu tıklandığında kartın submit'ini engelle
            $(document).on('click', '.favorite-toggle', function(event) {
                event.stopPropagation();
                var siteID = $(this).data('site-id');
                var $btn = $(this);
                var isFav = $btn.find('svg').attr('fill') !== 'none';
                var $form = $btn.closest('form');

                $.ajax({
                    url: 'update_favorite_status.php',
                    type: 'POST',
                    data: { site_id: siteID, is_favorite: isFav ? 0 : 1 },
                    success: function() {
                        if (isFav) {
                            // Favoriden çıkar: ikonu boşalt ve karta ana listede yer ver
                            $btn.find('svg').attr('fill', 'none');
                            if ($('.sites-container').length) {
                                $('.sites-container').prepend($form);
                            }
                            refreshFavoritesEmptyState();
                        } else {
                            // Favoriye ekle: ikonu doldur ve kartı favoriler alanına taşı
                            $btn.find('svg').attr('fill', '#f5a623');
                            if ($('.favorites-container').length) {
                                $('.favorites-container').prepend($form);
                            } else {
                                // Favoriler alanı yoksa oluştur
                                var favSection = '<div class="favorites-section">\n' +
                                    '<div class="favorites-header">\n' +
                                    '<h6 class="m-0">Favoriler</h6>\n' +
                                    '<small class="text-muted">Sık eriştiğiniz siteler</small>\n' +
                                    '</div>\n' +
                                    '<div class="favorites-container"></div>\n' +
                                    '</div>';
                                $('.search-controls-card').after(favSection);
                                $('.favorites-container').prepend($form);
                            }
                            refreshFavoritesEmptyState();
                        }
                    },
                    error: function() {
                        alert('Favori güncellenirken hata oluştu');
                    }
                });
            });

            // Switch tıklandığında kartın click'ine gitmesini engelle
            $(document).on('click', '.firm-status-switch', function(event) {
                event.stopPropagation();
            });

            // Arama işlevi
            $('#siteSearchInput').on('keyup', function() {
                var searchText = $(this).val().toLowerCase().trim();
                var $container = $('.sites-container');

                if (searchText === '') {
                    // Arama boşsa tüm kartları göster
                    $container.removeClass('search-active');
                    $('.site-card').each(function() {
                        $(this).css('display', '');
                        $(this).closest('form').css('display', '');
                    });
                } else {
                    // Arama aktifse filtrele
                    $container.addClass('search-active');
                    var visibleCount = 0;

                    $('.site-card').each(function() {
                        var siteName = $(this).find('.site-header-info h3').text().toLowerCase();
                        var siteAddress = $(this).find('.site-header-info p').text().toLowerCase();
                        var combinedText = siteName + ' ' + siteAddress;
                        var $form = $(this).closest('form');

                        if (combinedText.includes(searchText)) {
                            $(this).css('display', '');
                            $form.css('display', '');
                            $form.css('order', visibleCount);
                            visibleCount++;
                        } else {
                            $(this).css('display', 'none');
                            $form.css('display', 'none');
                        }
                    });
                }
            });

            // Firma durumunu değiştiren switch
            $(document).on('change', '.firm-status-switch', function() {
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

            if (window.bootstrap && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
            }

            $('#gridViewBtn').on('click', function() {
                $('.sites-container').removeClass('list-view');
            });

            $('#listViewBtn').on('click', function() {
                $('.sites-container').addClass('list-view');
            });

            function sortSitesAsc() {
                var items = $('.sites-container form').get();
                items.sort(function(a, b) {
                    var an = $(a).find('.site-header-info h3').text().toLowerCase();
                    var bn = $(b).find('.site-header-info h3').text().toLowerCase();
                    return an.localeCompare(bn);
                });
                $.each(items, function(idx, itm) { $('.sites-container').append(itm); });
            }

            function sortSitesDesc() {
                var items = $('.sites-container form').get();
                items.sort(function(a, b) {
                    var an = $(a).find('.site-header-info h3').text().toLowerCase();
                    var bn = $(b).find('.site-header-info h3').text().toLowerCase();
                    return bn.localeCompare(an);
                });
                $.each(items, function(idx, itm) { $('.sites-container').append(itm); });
            }

            $('#sortAscBtn').on('click', function() { sortSitesAsc(); });
            $('#sortDescBtn').on('click', function() { sortSitesDesc(); });
            sortSitesAsc();
        });
    </script>
</body>

<script>
    if (window.history && window.history.pushState) {
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.location.href = 'sign-in.php'; // Geri basarsa buraya yönlendir
        };
    }
</script>

<?php include './partials/footer.php'; ?>

</html>
