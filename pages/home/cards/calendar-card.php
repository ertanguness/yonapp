<?php 
    use App\Services\Gate;
?>

<?php if(Gate::allows('takvimi_goruntule')): ?>
<div class="card card-wrapper" data-card="calendar-card">
    <div class="apps-container apps-calendar">
        <div class="p-2 text-end">
            <span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>
        </div>
        <div class="nxl-content without-header nxl-full-content">
            <!-- [ Main Content ] start -->
            <div class="main-content d-flex" style="gap: 0;">
                <!-- [ Content Sidebar ] start -->
                <div class="content-sidebar content-sidebar-md" data-scrollbar-target="#psScrollbarInit" style="flex: 0 0 280px; width: 280px; border-right: 1px solid #e9ecef; padding: 1.5rem; overflow-y: auto; max-height: 800px;">
                    <div class="content-sidebar-header bg-white hstack justify-content-between mb-3" style="padding: 0; border: none;">
                        <h4 class="fw-bolder mb-0 card-header">Etkinlik Takvimi</h4>
                        <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex">
                            <i class="feather-x"></i>
                        </a>
                    </div>
                    <div class="content-sidebar-header" style="padding: 0; border: none; margin-bottom: 1rem;">
                        <a href="#" id="btn-new-schedule" class="btn btn-primary w-100">
                            <i class="feather-calendar me-2"></i>
                            <span>Yeni Etkinlik</span>
                        </a>
                    </div>

                    <div class="content-sidebar-body" style="padding: 0; background: transparent;">
                        <div id="lnb-calendars" class="lnb-calendars">
                            <div class="lnb-calendars-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="viewAllSchedules"
                                        value="all" checked="checked">
                                    <label class="custom-control-label c-pointer" for="viewAllSchedules">
                                        <span class="fs-13 fw-semibold lh-lg" style="margin-top: -2px">Tüm
                                            Etkinlikleri Göster</span>
                                    </label>
                                </div>
                            </div>
                            <div id="calendarList" class="lnb-calendars-d1">
                                <?php foreach ($calendarTypes as $type): ?>
                                    <?php
                                    $typeIdRaw = (string) $type['id'];
                                    $inputId = 'calendar-filter-' . preg_replace('/[^a-z0-9_-]+/i', '-', $typeIdRaw);
                                    ?>
                                  <div class="form-check calendar-filter-entry mb-2" style="--calendar-color: <?php echo htmlspecialchars($type['bgColor'], ENT_QUOTES); ?>;">
                                        <input type="checkbox" class="form-check-input calendar-filter" id="<?php echo htmlspecialchars($inputId, ENT_QUOTES); ?>"
                                            value="<?php echo htmlspecialchars($type['id'], ENT_QUOTES); ?>" checked="checked">
                                        <label class="form-check-label calendar-filter-item" for="<?php echo htmlspecialchars($inputId, ENT_QUOTES); ?>">
                                            <span><?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Content Sidebar  ] end -->
                <!-- [ Main Area  ] start -->
                <div class="content-area" data-scrollbar-target="#psScrollbarInit" style="flex: 1; min-width: 0;">
                    <div class="content-area-header sticky-top">
                        <div class="page-header-left d-flex align-items-center gap-2">

                            <div id="menu" class="d-flex align-items-center justify-content-between">
                                <div class="d-flex calendar-action-btn">
                                    <div class="dropdown me-1">
                                        <button id="dropdownMenu-calendarType"
                                            class="dropdown-toggle calendar-dropdown-btn" type="button"
                                            data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            data-bs-offset="0,17">
                                            <i id="calendarTypeIcon"
                                                class="feather-grid calendar-icon fs-12 me-1"></i>
                                            <span id="calendarTypeName">Görünüm</span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu-calendarType">
                                        <li><button class="dropdown-item calendar-view-option" type="button"
                                            data-view="day">Günlük Görünüm</button></li>
                                        <li><button class="dropdown-item calendar-view-option" type="button"
                                            data-view="week">Haftalık Görünüm</button></li>
                                        <li><button class="dropdown-item calendar-view-option" type="button"
                                            data-view="month">Aylık Görünüm</button></li>
                                        <li><button class="dropdown-item calendar-view-option" type="button"
                                            data-view="2weeks">2 Haftalık Görünüm</button></li>
                                        <li><button class="dropdown-item calendar-view-option" type="button"
                                            data-view="3weeks">3 Haftalık Görünüm</button></li>
                                        </ul>
                                    </div>
                                    <div class="menu-navi d-none d-sm-flex">
                                        <button type="button" class="move-today" data-action="move-today">
                                            <i class="feather-clock calendar-icon me-1 fs-12"
                                                data-action="move-today"></i>
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
                                    <button type="button" class="avatar-text avatar-md move-day"
                                        data-action="move-prev">
                                        <i class="feather-chevron-left fs-12" data-action="move-prev"></i>
                                    </button>
                                    <button type="button" class="avatar-text avatar-md move-day"
                                        data-action="move-next">
                                        <i class="feather-chevron-right fs-12" data-action="move-next"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-area-body p-0">
                        <div id="tui-calendar-init"></div>
                    </div>

                    <!-- [ Footer ] end -->
                </div>
                <!-- [ Content Area ] end -->
            </div>
            <!-- [ Main Content Calendar ] end -->
        </div>
    </div>
</div>
<?php endif; ?>
