<?php 
    use App\Services\Gate;
?>

<?php if(Gate::allows('takvimi_goruntule')): ?>
<div class="card card-wrapper" data-card="calendar-card">
    <div class="apps-container apps-calendar">
        <style>
            .calendar-filter-entry{display:flex;align-items:center;gap:8px}
            .calendar-filter-item{display:inline-flex;align-items:center;gap:8px;cursor:pointer}
            .calendar-filter-entry .form-check-input{cursor:pointer;margin-top:0;accent-color:var(--calendar-color,#0d6efd);border-color:var(--calendar-color,#0d6efd);background-color:transparent;transition:background-color .2s,border-color .2s,box-shadow .2s}
            .calendar-filter-entry .form-check-input:checked{background-color:var(--calendar-color,#0d6efd);border-color:var(--calendar-color,#0d6efd)}
            .content-area-body{margin-top:36px}
            #calendar{padding-top:8px}
            .content-area-header.sticky-top{z-index:5}
            .tui-full-calendar-month-view .tui-full-calendar-dayname-container{margin-top:8px !important}
            .tui-full-calendar-month-view .tui-full-calendar-day-grid-item{min-height:120px}
            .tui-full-calendar-month-view .tui-full-calendar-day-grid-date{min-height:120px}
            .tui-full-calendar-month-view .tui-full-calendar-dayname,
            .tui-full-calendar-month-view .tui-full-calendar-dayname-item,
            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area{display:flex;align-items:center;justify-content:center;text-align:center}
            .tui-full-calendar-month-view .tui-full-calendar-dayname-item{width:100%}
            .tui-full-calendar-month-view .tui-full-calendar-dayname{width:100%}
            .tui-full-calendar-dayname-name,.calendar-dayname{display:block;width:100%;text-align:center !important}
            .tui-full-calendar-week-view .tui-full-calendar-dayname-name{display:block;width:100%;text-align:center !important}
            #tui-calendar-init .calendar-grid-header{display:flex;align-items:center;justify-content:center}
            .tui-full-calendar-month-view .tui-full-calendar-day-grid-date{display:flex;flex-direction:column;align-items:center}
            .tui-full-calendar-month-view .tui-full-calendar-date{display:flex;align-items:center;justify-content:center;width:100%;text-align:center}
            .tui-full-calendar-month-view .tui-full-calendar-month-day .tui-full-calendar-date{position:relative !important;left:auto !important;right:auto !important;margin:0 auto !important}
            .tui-full-calendar-month-view .tui-full-calendar-date .tui-full-calendar-dayname-date{display:inline-block;text-align:center;margin:0 auto}
            .calendar-event-modal .modal-footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
            .calendar-event-modal .modal-footer .action-buttons{display:flex;gap:.5rem;margin-left:auto}
            .calendar-event-modal .modal-footer .btn{padding:.5rem 1rem;font-size:.85rem;font-weight:600;border-radius:10px}
            #preview-edit-btn,#preview-delete-btn{min-width:120px}
            #calendar-save-btn{min-width:120px}
            #calendar-delete-btn{min-width:120px}
        </style>
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
                        <div id="calendar"><div id="tui-calendar-init"></div></div>
                    </div>

                    <!-- [ Footer ] end -->
                </div>
                <!-- [ Content Area ] end -->
            </div>
            <!-- [ Main Content Calendar ] end -->
        </div>
    </div>
</div>
<div class="modal fade" id="calendarEventPreviewModal" tabindex="-1" aria-labelledby="calendarEventPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered calendar-event-modal">
        <div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title mb-1" id="calendarEventPreviewModalLabel">Etkinlik Detayları</h5><p class="text-muted mb-0 small" id="calendarEventPreviewModalSubtitle">Etkinlik bilgileri</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button></div>
            <div class="modal-body"><div class="mb-4"><div class="d-flex align-items-center mb-3"><div id="preview-event-color" class="rounded-2 me-3" style="width:24px;height:24px;"></div><h5 class="mb-0 fw-bold" id="preview-event-title"></h5></div><div class="mb-3" id="preview-event-datetime"></div><div class="mb-3 d-none" id="preview-event-location"><i class="feather-map-pin feather fs-12 me-2"></i><span id="preview-location-text"></span></div><div class="mb-3 d-none" id="preview-event-description-container"><h6 class="text-muted mb-2 fw-semibold">Açıklama</h6><p class="mb-0" id="preview-event-description"></p></div><div class="pt-2"><small id="preview-event-type"></small></div></div></div>
            <div class="modal-footer calendar-modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button><div class="action-buttons"><button type="button" class="btn btn-outline-secondary" id="preview-edit-btn">Düzenle</button><button type="button" class="btn btn-outline-danger" id="preview-delete-btn">Sil</button></div></div>
        </div>
    </div>
    </div>
<div class="modal fade" id="calendarEventModal" tabindex="-1" aria-labelledby="calendarEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered calendar-event-modal">
        <form class="modal-content" id="calendar-event-form" autocomplete="off">
            <div class="modal-header"><div><h5 class="modal-title mb-1" id="calendarEventModalLabel">Etkinlik Kaydı</h5><p class="text-muted mb-0 small" id="calendarEventModalSubtitle">Yeni bir etkinlik oluşturun.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button></div>
            <div class="modal-body"><input type="hidden" id="calendar-event-id" name="event_id"><div class="alert alert-danger d-none" role="alert" id="calendar-form-alert"></div><div class="mb-3"><label for="calendar-event-title" class="form-label">Başlık</label><input type="text" class="form-control" id="calendar-event-title" name="title" maxlength="255" required></div><div class="mb-3"><label for="calendar-event-type" class="form-label">Etkinlik Türü</label><select class="form-select" id="calendar-event-type" name="calendar_id" required><?php foreach ($calendarTypes as $type): ?><option value="<?php echo htmlspecialchars($type['id'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?></option><?php endforeach; ?></select></div><div class="row g-3"><div class="col-md-6"><label for="calendar-event-start" class="form-label">Başlangıç</label><input type="datetime-local" class="form-control" id="calendar-event-start" name="start" required></div><div class="col-md-6"><label for="calendar-event-end" class="form-label">Bitiş</label><input type="datetime-local" class="form-control" id="calendar-event-end" name="end" required></div></div><div class="form-check form-switch my-3"><input class="form-check-input" type="checkbox" role="switch" id="calendar-event-all-day" name="is_all_day"><label class="form-check-label" for="calendar-event-all-day">Tüm Gün</label></div><div class="mb-3"><label for="calendar-event-location" class="form-label">Konum</label><input type="text" class="form-control" id="calendar-event-location" name="location" maxlength="255"></div><div class="mb-0"><label for="calendar-event-description" class="form-label">Açıklama</label><textarea class="form-control" id="calendar-event-description" name="description" rows="3"></textarea></div></div>
            <div class="modal-footer calendar-modal-footer"><button type="button" class="btn btn-outline-danger d-none" id="calendar-delete-btn">Etkinliği Sil</button><div class="action-buttons"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button><button type="submit" class="btn btn-primary" id="calendar-save-btn">Kaydet</button></div></div>
        </form>
    </div>
    </div>
<?php endif; ?>
