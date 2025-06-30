<?php
use App\Helper\Helper;
use App\Helper\Security;

use App\Helper\Site;


use Dompdf\Css\Style;
use Model\UserPaymentModel;

// Kullanıcı Ödemeleri
$UserPayment = new UserPaymentModel();

// Kullanıcının Gruplanmış Borç Başlıklarını ve Ödeme Durumlarını Getirir
$gruplanmisBorc = $UserPayment->KategoriBazliOzet(user_id: 257);
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
   
   
    <div class="row mb-5">
        <div class="container-xl mb-5">

            <div class="col-lg-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div>
                            <h2 class="fs-16 fw-700 text-truncate-1-line mb-0 mb-sm-1">Dairelerim</h2>
                            <div class="dropdown d-none d-sm-block">
                                <a href="javascript:void(0)" class="dropdown-toggle d-flex align-items-center"
                                    data-bs-toggle="dropdown" data-bs-offset="0,25" aria-expanded="false">
                                    <span class="fs-11 fw-400 text-muted me-2">Daire Seç</span>
                                </a>
                                <ul class="dropdown-menu" style="">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item active">Default</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Simple</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Classic</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Modern</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Untimate</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Essential</a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Create Template</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Delete Template</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <a href="javascript:void(0)" class="d-flex me-1" data-alert-target="invoicSendMessage">
                                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                    title="" data-bs-original-title="Send Invoice">
                                    <i class="feather feather-send"></i>
                                </div>
                            </a>
                            <a href="javascript:void(0)" class="d-flex me-1 printBTN">
                                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                    title="" data-bs-original-title="Print Invoice" aria-label="Print Invoice"><i
                                        class="feather feather-printer"></i></div>
                            </a>
                            <a href="javascript:void(0)" class="d-flex me-1">
                                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                    title="" data-bs-original-title="Add Payment" aria-label="Add Payment"><i
                                        class="feather feather-dollar-sign"></i></div>
                            </a>
                            <a href="javascript:void(0)" class="d-flex me-1 file-download">
                                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                    title="" data-bs-original-title="Download Invoice" aria-label="Download Invoice"><i
                                        class="feather feather-download"></i></div>
                            </a>
                            <a href="invoice-create.html" class="d-flex me-1">
                                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                    title="" data-bs-original-title="Edit Invoice">
                                    <i class="feather feather-edit"></i>
                                </div>
                            </a>
                        </div>
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
            </div>


            <div class="row">

                <div class="col-xxl-12 col-lg-12 ">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Özet Bilgilerim</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">

                                    <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                            data-bs-toggle="refresh"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                            data-bs-toggle="expand"> </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-body custom-card-action">
                            <div class="accordion" id="weeklyBestsellerAccordion">
                                <?php 
                                
                                $i = 0;
                                foreach ($gruplanmisBorc as $borc) { 
                                    $i++;
                                    $color = $borc->bakiye > 0 ? "success" : "danger";
                                    ?>

                                <div class="accordion-item border border-dashed border-gray-500 my-3">
                                    <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                        <button class="accordion-button collapsed bg-white" type="button"
                                            aria-expanded="true" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?php echo $i; ?>">
                                            <div class="d-flex align-items-center w-100">
                                                <div
                                                    class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded me-3">
                                                    <i class="feather-award"></i>
                                                </div>
                                                <div>
                                                    <a href="javascript:void(0);"><?php echo $borc->kategori_adi; ?></a>
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
                                                $borc_detay = $UserPayment->KullaniciBorcDetaylari(113,$borc->kategori_adi);

                                              
                                                foreach ($borc_detay as $detay) { 
                                                    $enc_id = Security::encrypt($detay->borc_id ?? 0);
                                                    $color = $detay->islem_turu == "tahsilat" ? "success" : "danger";


                                                ?>
                                                <li class="feed-item feed-item-<?php echo  $color ; ?>">
                                                    <div class="d-flex gap-4 justify-content-between">
                                                        <div>
                                                            <div class="mb-2 text-truncate-1-line"><a
                                                                    href="javascript:void(0)"
                                                                    class="fw-semibold text-dark">
                                                                    <?php echo $detay->islem_adi ; ?>
                                                                </a>
                                                            </div>
                                                            <p class="fs-12 text-muted mb-3 text-truncate-2-line">
                                                                <?php echo  $detay->aciklama ; ?></p>

                                                        </div>
                                                        <div class="text-end">
                                                            <div>

                                                                <div
                                                                    class="fw-semibold text-<?php echo  $color ; ?> text-uppercase text-muted text-nowrap">
                                                                    <?php echo Helper::formattedMoneyWithoutCurrency($detay->tutar); ?>
                                                                    ₺
                                                                </div>
                                                            </div>
                                                            <span class="fs-10 fw-semibold text-muted">Bak.
                                                                <?php echo Helper::formattedMoneyWithoutCurrency($detay->bakiye); ?>
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
                </div>

            </div>

        </div>

    </div>

    <div class="tab-container">
        <!-- Tab Navigation -->
        <ul class="tab-nav">
            <li class="active" data-tab="home">
                <a href="#"><i class="fas fa-home"></i> Home</a>
            </li>
            <li data-tab="messages">
                <a href="#"><i class="fas fa-comment-dots"></i> Messages</a>
            </li>
            <li data-tab="search">
                <a href="#"><i class="fas fa-search"></i> Search</a>
            </li>
            <li data-tab="history">
                <a href="#"><i class="fas fa-clock-rotate-left"></i> History</a>
            </li>
            <li data-tab="profile">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div id="home" class="tab-content active">
            <h2>Home</h2>
            <p>Welcome to the home page. This is your starting point for exploring our platform.</p>
        </div>

        <div id="messages" class="tab-content">
            <h2>Messages</h2>
            <p>View and manage your messages here. Stay connected with your contacts.</p>
        </div>

        <div id="search" class="tab-content">
            <h2>Search</h2>
            <p>Find what you're looking for with our powerful search tools.</p>
        </div>

        <div id="history" class="tab-content">
            <h2>History</h2>
            <p>Review your activity history and past interactions.</p>
        </div>

        <div id="profile" class="tab-content">
            <h2>Profile</h2>
            <p>Manage your account settings and personal information.</p>
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