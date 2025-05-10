<div class="main-content">
    <div class="row row-cards">
        <!-- [Mini Card] start -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-success">₺120.000</h4>
                            <div class="text-muted">Toplam Aidat Geliri</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-credit-card fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger">₺25.000</h4>
                            <div class="text-muted">Gecikmiş Ödemeler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-danger py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">+2.5% artış</p>
                        <div class="text-end">
                            <i class="feather-trending-up text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-warning">₺80.000</h4>
                            <div class="text-muted">Toplam Giderler</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-dollar-sign fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">-1.2% azalma</p>
                        <div class="text-end">
                            <i class="feather-trending-down text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <h4 class="text-danger">12</h4>
                            <div class="text-muted">Gecikmiş Aidat Sayısı</div>
                        </div>
                        <div class="text-end">
                            <i class="feather-alert-circle fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info py-3">
                    <div class="hstack justify-content-between">
                        <p class="text-white mb-0">Sabit</p>
                        <div class="text-end">
                            <i class="feather-minus text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [Mini Card] end -->
    </div>
  <!-- Takvim -->
  <div class="card stretch stretch-full">
        <div class="apps-container apps-calendar">
            <div class="nxl-content without-header nxl-full-content">
                <!-- [ Main Content ] start -->
                <div class="main-content d-flex">
                    <!-- [ Content Sidebar ] start -->
                    <div class="content-sidebar content-sidebar-md" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-sidebar-header bg-white sticky-top hstack justify-content-between">
                            <h4 class="fw-bolder mb-0">Etkinlik Takvimi</h4>
                            <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex">
                                <i class="feather-x"></i>
                            </a>
                        </div>
                        <div class="content-sidebar-header">
                            <a href="javascript:void(0);" id="btn-new-schedule" class="btn btn-primary w-100" data-toggle="modal">
                                <i class="feather-calendar me-2"></i>
                                <span>Yeni Etkinlik</span>
                            </a>
                        </div>

                        <div class="content-sidebar-body">
                            <div id="lnb-calendars" class="lnb-calendars">
                                <div class="lnb-calendars-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="viewAllSchedules" value="all" checked="checked">
                                        <label class="custom-control-label c-pointer" for="viewAllSchedules">
                                            <span class="fs-13 fw-semibold lh-lg" style="margin-top: -2px">Tüm Etkinlikleri Göster</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="calendarList" class="lnb-calendars-d1">
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="1" checked=""><span style="border-color: #5485e4; background-color: #5485e4"></span><span>Ofis</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="2" checked=""><span style="border-color: #25b865; background-color: #25b865"></span><span>Aile</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="3" checked=""><span style="border-color: rgb(209, 59, 76); background-color: rgb(209, 59, 76)"></span><span>Arkadaş</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="4" checked=""><span style="border-color: #17a2b8; background-color: #17a2b8"></span><span>Seyahat</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="5" checked=""><span style="border-color: #e49e3d; background-color: #e49e3d"></span><span>Özel</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="6" checked=""><span style="border-color: #5856d6; background-color: #5856d6"></span><span>Tatil</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="7" checked=""><span style="border-color: #3dc7be; background-color: #3dc7be"></span><span>Şirket</span></label>
                                    </div>
                                    <div class="lnb-calendars-item">
                                        <label><input type="checkbox" class="tui-full-calendar-checkbox-round" value="8" checked=""><span style="border-color: #475e77; background-color: #475e77"></span><span>Doğum Günleri</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Content Sidebar  ] end -->
                    <!-- [ Main Area  ] start -->
                    <div class="content-area" data-scrollbar-target="#psScrollbarInit">
                        <div class="content-area-header sticky-top">
                            <div class="page-header-left d-flex align-items-center gap-2">
                               
                                <div id="menu" class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex calendar-action-btn">
                                        <div class="dropdown me-1">
                                            <button id="dropdownMenu-calendarType" class="dropdown-toggle calendar-dropdown-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-offset="0,17">
                                                <i id="calendarTypeIcon" class="feather-grid calendar-icon fs-12 me-1"></i>
                                                <span id="calendarTypeName">Görünüm</span>
                                            </button>
                                        </div>
                                        <div class="menu-navi d-none d-sm-flex">
                                            <button type="button" class="move-today" data-action="move-today">
                                                <i class="feather-clock calendar-icon me-1 fs-12" data-action="move-today"></i>
                                                <span>Bugün</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="page-header-right ms-auto">
                                <div class="hstack gap-2">
                                    <div id="renderRange" class="render-range d-none d-sm-flex"></div>
                                    <div class="btn-group gap-1 menu-navi" role="group">
                                        <button type="button" class="avatar-text avatar-md move-day" data-action="move-prev">
                                            <i class="feather-chevron-left fs-12" data-action="move-prev"></i>
                                        </button>
                                        <button type="button" class="avatar-text avatar-md move-day" data-action="move-next">
                                            <i class="feather-chevron-right fs-12" data-action="move-next"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-area-body p-0">
                            <div id="tui-calendar-init"></div>
                        </div>
                        <!-- [ Footer ] start -->
                        <?php include './partials/footer.php' ?>
                        <!-- [ Footer ] end -->
                    </div>
                    <!-- [ Content Area ] end -->
                </div>
                <!-- [ Main Content ] end -->
            </div>
        </div>
    </div>
    <!-- [Takvim] end -->
    <!-- [Aylık Gelir Gider Tablosu] -->
    <div class="col-xxl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Gelir-Gider Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="payment-records-chart"></div>
                <div class="d-none d-md-flex flex-wrap pt-4 border-top">
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gelir</p>
                        <h2 class="fs-20 fw-bold mb-0">$65,658 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gider</p>
                        <h2 class="fs-20 fw-bold mb-0">$34,54 USD</h2>
                    </div>
                    <div class="vr mx-4 text-gray-600"></div>
                    <div class="flex-fill">
                        <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Kar / Zarar</p>
                        <h2 class="fs-20 fw-bold mb-0">$20,478 USD</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
    

    <!-- [Payment Records] end -->
    <!-- [Inquiry Channel] start -->
    <div class="col-xxl-12">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Yıllık Aidat Ödeme Grafiği</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">
                        <div data-bs-toggle="tooltip" title="Delete">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Refresh">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div id="leads-inquiry-channel"></div>
            </div>
        </div>
    </div>
    <!-- [Inquiry Channel] end -->
</div>

<?php include './partials/calender-scripts.php' ?>