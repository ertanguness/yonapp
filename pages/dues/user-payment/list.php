<?php

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;

use App\Helper\Site;


use Dompdf\Css\Style;
use Model\UserPaymentModel;

// Kullanıcı Ödemeleri
$UserPayment = new UserPaymentModel();
$user_id = $_SESSION['user']->kisi_id ?? 0;
$user_id = 82;

// Kullanıcının Gruplanmış Borç Başlıklarını ve Ödeme Durumlarını Getirir
$BorcTahsilatDetay = $UserPayment->kisiBorcTahsilatDetay(user_id: $user_id);

// echo "<pre>";
//  print_r($BorcTahsilatDetay);
//  echo "</pre>";
//  exit;

//Borc_adina göre grupla
$gruplanmisBorc = array_reduce($BorcTahsilatDetay, function ($acc, $item) {
    $kategori = $item->borc_adi;
    if (!isset($acc[$kategori])) {
        $acc[$kategori] = (object)[
            'borc_adi' => $kategori,
            'kayit_sayisi' => 0,
            "bakiye" => 0,
            "detaylar" => []
        ];
    }


    // Kaydı detaylar dizisine ekle
    $acc[$kategori]->detaylar[] = (object)[
        'islem_tarihi' => $item->islem_tarihi,
        "borc_adi"     => $item->borc_adi,
        'aciklama'     => $item->aciklama,
        'islem_turu'   => $item->islem_turu,
        'tutar'        => $item->tutar,
        'gecikme_zammi' => $item->gecikme_zammi,
        'yuruyen_bakiye' => 0 // şimdilik boş, sonra dolduracağız
    ];
    $acc[$kategori]->kayit_sayisi += 1;
    $acc[$kategori]->bakiye += (float)$item->tutar - (float)$item->gecikme_zammi;

    return $acc;
}, []);


// Her kategori için ayrı yürüyen bakiye
foreach ($gruplanmisBorc as $kategori => $borc) {
    // Önce tarihe göre sırala (eskiden yeniye)
    usort($borc->detaylar, function ($a, $b) {
        return strtotime($a->islem_tarihi) - strtotime($b->islem_tarihi);
    });

    // Sonra yürüyen bakiye hesapla
    $bakiye = 0;
    foreach ($borc->detaylar as $detay) {
        $bakiye += (float)$detay->tutar - (float)$detay->gecikme_zammi;
        $detay->yuruyen_bakiye = $bakiye;
    }
}

//$gruplanmisBorc = array_values($gruplanmisBorc); // Reindex array

// echo "<pre>";
//  print_r($gruplanmisBorc);
// echo "</pre>";
// exit;
$hesap_ozet = (object)[
    'toplam_borc' => 0,
    'toplam_tahsilat' => 0,
    'bakiye' => 0
];


$hesap_ozet = $UserPayment->KullaniciToplamBorc(257);
$bakiye_color = $hesap_ozet->bakiye > 0 ? "success" : "danger";

?>
<style>
    <?php require "style.css";
    ?>
</style>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlarım</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlarım</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    <i class="feather-bar-chart"></i>
                </a>
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                        <i class="feather-filter"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-eye me-3"></i>
                            <span>All</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-send me-3"></i>
                            <span>Sent</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-book-open me-3"></i>
                            <span>Open</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-archive me-3"></i>
                            <span>Draft</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-bell me-3"></i>
                            <span>Revised</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-shield-off me-3"></i>
                            <span>Declined</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-check me-3"></i>
                            <span>Accepted</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-briefcase me-3"></i>
                            <span>Leads</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-wifi-off me-3"></i>
                            <span>Expired</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-users me-3"></i>
                            <span>Customers</span>
                        </a>
                    </div>
                </div>
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                        <i class="feather-paperclip"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-pdf me-3"></i>
                            <span>PDF</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-csv me-3"></i>
                            <span>CSV</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-xml me-3"></i>
                            <span>XML</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-txt me-3"></i>
                            <span>Text</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-filetype-exe me-3"></i>
                            <span>Excel</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="bi bi-printer me-3"></i>
                            <span>Print</span>
                        </a>
                    </div>
                </div>
                <a href="invoice-create.html" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>Create Invoice</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>
<style>
    .activity-feed-1 .feed-item {
        padding-bottom: 15px !important;

    }
</style>
<div class="main-content">
    <!-- Mobile Navigation - Soft Bottom Bar -->
    <!-- Tab Navigation -->






    <div class="tab-container">
        <!-- Tab Navigation -->
        <ul class="tab-nav">
            <li class="active" data-tab="home">
                <a href="#"><i class="feather feather-home"></i> Özet</a>
            </li>
            <li data-tab="finans">
                <a href="#"><i class="feather feather-credit-card"></i>Finans</a>
            </li>
            <li data-tab="taleplerim">
                <a href="#"><i class="feather feather-file-text"></i> Taleplerim</a>
            </li>
            <li data-tab="dairelerim">
                <a href="#"><i class="feather feather-database"></i> Dairelerim</a>
            </li>

            <li data-tab="diger">
                <a href="#"><i class="feather feather-list"></i> Diğer</a>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div id="home" class="tab-content active">

            <div class="card-header">

                <h5 class="card-title">Özet Bilgilerim</h5>


            </div>
            <div class="card-body p-0">
                <div class="px-4 pt-4">
                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fs-24 fw-bolder font-montserrat-alt text-uppercase">Dairem</div>
                            <address class="text-muted">
                                P.O. Box 18728,<br>
                                DeLorean New York<br>
                                VAT No: 2617 348 2752
                            </address>

                        </div>
                        <div class="lh-lg pt-3 pt-sm-0 text-right">
                            <h2 class="fs-4 fw-bold text-primary">Özet</h2>
                            <div class="d-flex">
                                <span class="fw-bold text-dark">BORÇ (₺) : </span>
                                <h4><span class="counter text-danger"><?php echo $hesap_ozet->toplam_borc ?>
                                        ₺</span></h4>
                            </div>
                            <div class="d-flex">
                                <span class="fw-bold text-dark">ÖDENEN (₺) : </span>
                                <h4><span
                                        class="counter text-success"><?php echo $hesap_ozet->toplam_tahsilat ?>
                                        ₺</span></h4>
                            </div>
                            <div class="d-flex">
                                <span class="fw-bold text-dark">KALAN (₺) : </span>
                                <h4><span
                                        class="counter text-<?php echo $bakiye_color ?>"><?php echo $hesap_ozet->bakiye ?>
                                        ₺</span></h4>

                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>




        <div id="finans" class="card tab-content">


            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Gelir-Gider İşlemleri</h5>
                <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $user_id; ?>&format=pdf" class="printBTN">
                    <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Yazdır">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                </a>
            </div>


            <div class="card-body custom-card-action">

                <div class="accordion" id="weeklyBestsellerAccordion">
                    <?php

                    $i = 0;
                    foreach ($gruplanmisBorc as $borc) {
                        $i++;
                        $color = $borc->bakiye >= 0 ? "success" : "danger";
                    ?>

                        <div class="accordion-item border border-dashed border-gray-500 my-3">
                            <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                <button class="accordion-button collapsed bg-white" type="button"
                                    aria-expanded="true" data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?php echo $i; ?>">
                                    <div class="d-flex align-items-center w-100">
                                        <div
                                            class="avatar-text avatar-lg bg-soft-<?php echo $color; ?> text-<?php echo $color; ?> border-soft-<?php echo $color; ?> rounded me-3">
                                            <?php echo Helper::getInitials($borc->borc_adi) ?>
                                            </i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);"><?php echo $borc->borc_adi  ?></a>
                                        </div>
                                        <div class="text-end ms-auto pe-3">
                                            <span
                                                class="fw-bold d-block text-<?php echo $color; ?>"><?php echo Helper::formattedMoney($borc->bakiye) ?>
                                            </span>
                                            <span class="fs-12 text-muted"><?php echo $borc->kayit_sayisi ?>
                                                Hareket</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse"
                                aria-labelledby="heading<?php echo $i; ?>">
                                <div class="accordion-body">

                                    <ul class="list-unstyled mb-0 activity-feed-1">


                                        <?php

                                        $detaylar = array_reverse($borc->detaylar);
                                        foreach ($detaylar as $detay) {
                                            if ($detay->borc_adi != $borc->borc_adi) {
                                                continue;
                                            }
                                            $enc_id = Security::encrypt($detay->borc_id ?? 0);
                                            $color = $detay->islem_turu == "tahsilat" ? "success" : "danger";


                                        ?>
                                            <li class="feed-item feed-item-<?php echo  $color; ?>">
                                                <div class="d-flex gap-4 justify-content-between">
                                                    <div class="d-flex flex-column">
                                                        <div class="text-truncate-1-line"><a
                                                                href="javascript:void(0)"
                                                                class="fw-semibold text-dark">
                                                                <?php echo $detay->borc_adi; ?>
                                                                <!-- Ödeme Tarihi : -->
                                                            </a>
                                                        </div>
                                                        <span class="text-muted">
                                                            <?php echo Date::dmy($detay->islem_tarihi,); ?>
                                                        </span>
                                                        <p class="fs-12 text-muted text-truncate-2-line">
                                                            <?php echo  $detay->aciklama; ?></p>

                                                    </div>
                                                    <div class="text-end">
                                                        <div>

                                                            <div
                                                                class="fw-semibold text-<?php echo  $color; ?> text-uppercase text-muted text-nowrap">
                                                                <?php echo Helper::formattedMoneyWithoutCurrency($detay->tutar); ?> ₺

                                                            </div>
                                                            <?php if ($detay->gecikme_zammi > 0) { ?>
                                                                <div class="text-danger fs-12 text-muted text-truncate-2-line">
                                                                    <?php echo "Gecikme Zammı : " . ($detay->gecikme_zammi ?? 0); ?> ₺
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <span class="fs-10 fw-semibold text-muted">Bak.
                                                            <?php echo Helper::formattedMoneyWithoutCurrency($detay->yuruyen_bakiye ?? 0); ?>
                                                            ₺</span>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ul>

                                </div>
                            </div>
                        </div>

                    <?php } ?>
                </div>


            </div>
        </div>

        <style>

        </style>

        <div id="taleplerim" class="tab-content">
            <h5 class="card-title">Taleplerim</h5>
            <div class="card-body custom-card-action p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>Konu</th>
                                <th>Açıklama</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center">
                                <td>1</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        Su Faturası Hakkında
                                    </div>
                                </td>

                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        Su faturamda bir yanlışlık var, lütfen kontrol eder misiniz?
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-warning">Beklemede</span>
                                </td>

                                <td>2024-10-01</td>

                                <td>
                                    <div class="hstack gap-2 justify-content-center">
                                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-primary"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Düzenle">
                                            <i class="feather-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-danger"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Sil">
                                            <i class="feather-trash-2"></i>
                                        </a>
                                    </div>

                                </td>
                            </tr>
                        </tbody>
                    </table>    
                </div>
            </div>
            <button class="btn btn-primary sticky-bottom-btn btn-block mt-5">Yeni Talep Ekle</button>
        </div>

        <div id="dairelerim" class="tab-content">
            <h5 class="card-title">Dairelerim</h5>
            <div class="card-body custom-card-action mt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded">
                            <i class="feather-shopping-bag"></i>
                        </div>
                        <div class="ms-3">
                            <a href="javascript:void(0);" class="fw-semibold">A1D3</a>
                            <div class="fs-12 text-muted fw-normal">A Blok Daire 3</div>
                        </div>
                    </div>
                    <div class="avatar-text avatar-md">
                        <i class="feather feather-arrow-right"></i>
                    </div>
                </div>
                <hr class="border-dashed my-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                            <i class="feather-clipboard"></i>
                        </div>
                        <div class="ms-3">
                            <a href="javascript:void(0);" class="fw-semibold">A1D4</a>
                            <div class="fs-12 text-muted fw-normal">A Blok Daire 4 </div>
                        </div>
                    </div>
                    <div class="avatar-text avatar-md">
                        <i class="feather feather-arrow-right"></i>
                    </div>
                </div>



            </div>
        </div>




        <div id="diger" class="tab-content">
            <div class="card-body custom-card-action p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-chrome fs-16 text-primary me-2"></i>
                                        <span>Google Chrome</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">90%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-success" style="width: 90%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-firefox-browser fs-16 text-warning me-2"></i>
                                        <span>Mozila Firefox</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">76%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-primary" style="width: 76%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-safari fs-16 text-info me-2"></i>
                                        <span>Apple Safari</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">50%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-warning" style="width: 50%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-edge fs-16 text-success me-2"></i>
                                        <span>Edge Browser</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">20%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-success" style="width: 20%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-opera fs-16 text-danger me-2"></i>
                                        <span>Opera mini</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">15%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-danger" style="width: 15%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-internet-explorer fs-16 text-teal me-2"></i>
                                        <span>Internet Explorer</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">12%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-teal" style="width: 12%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="javascript:void(0);">
                                        <i class="fa-brands fa-octopus-deploy fs-16 text-dark me-2"></i>
                                        <span>Others Browser</span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-end d-flex align-items-center m-0">
                                        <span class="me-3">6%</span>
                                        <span class="progress w-100 ht-5">
                                            <span class="progress-bar bg-dark" style="width: 6%"></span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabItems = document.querySelectorAll('.tab-nav li');
            const tabContents = document.querySelectorAll('.tab-content');

            tabItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all tabs and contents
                    tabItems.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>