<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Ayarlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Ayarlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <button type="button" class="btn btn-primary" id="saveSettings">
                <i class="feather-save me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action='' id='settingsForm'>
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body">
                                    <div class="row mb-4 align-items-center">
                                        <!--********** NAV TABS ************** -->
                                        <div class="card-header p-0">
                                            <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" id="settingsTab" role="tablist">
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#generalSettingsTab" role="tab">Genel Ayarlar</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#notificationSettingsTab" role="tab">Bildirim Ayarları</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#paymentSettingsTab" role="tab">Ödeme Ayarları</a>
                                                </li>
                                                <li class="nav-item flex-fill border-top" role="presentation">
                                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#feeSettingsTab" role="tab">Aidat Ayarları</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content">
                                            <!-- Genel Ayarlar Tab -->
                                            <div class="tab-pane fade show active" id="generalSettingsTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/settings/content/GeneralSettingsPage.php';
                                                ?>
                                            </div>

                                            <!-- Bildirim Ayarları Tab -->
                                            <div class="tab-pane fade" id="notificationSettingsTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/settings/content/NotificationSettingsPage.php';
                                                ?>
                                            </div>

                                            <!-- Ödeme Ayarları Tab -->
                                            <div class="tab-pane fade" id="paymentSettingsTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/settings/content/PaymentSettingsPage.php';
                                                ?>
                                            </div>

                                            <!-- Aidat Ayarları Tab -->
                                            <div class="tab-pane fade" id="feeSettingsTab" role="tabpanel">
                                                <?php
                                                require_once 'pages/settings/content/FeeSettingsPage.php';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
