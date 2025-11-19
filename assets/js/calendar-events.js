'use strict';

(function () {
    const config = window.calendarConfig || null;
    if (!config) {
        console.warn('Takvim yapılandırması bulunamadı.');
        return;
    }

    // Dropdown menüsü için CSS stilleri ekle
    function addDropdownStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .dropdown {
                position: relative;
            }
            
            #dropdownMenu-calendarType + .dropdown-menu {
                position: absolute;
                top: 100%;
                left: 0;
                z-index: 1055;
                display: none;
                min-width: 12rem;
                padding: 0.5rem 0;
                margin: 0.125rem 0 0;
                font-size: 0.875rem;
                color: #212529;
                text-align: left;
                list-style: none;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid rgba(0, 0, 0, 0.15);
                border-radius: 0.25rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
                transform: translateY(0);
                transition: opacity 0.15s, transform 0.15s;
            }
            
            #dropdownMenu-calendarType + .dropdown-menu.show {
                display: block !important;
                opacity: 1;
                transform: translateY(0);
            }
            
            .calendar-view-option {
                display: block;
                width: 100%;
                padding: 0.5rem 1rem;
                clear: both;
                font-weight: 400;
                color: #212529;
                text-align: inherit;
                text-decoration: none;
                white-space: nowrap;
                background-color: transparent;
                border: 0;
                cursor: pointer;
                transition: background-color 0.15s ease-in-out;
            }
            
            .calendar-view-option:hover {
                color: #1e2125;
                background-color: #f8f9fa;
            }
            
            .calendar-view-option:focus {
                outline: 0;
                background-color: #e9ecef;
            }
            
            #dropdownMenu-calendarType {
                cursor: pointer;
                user-select: none;
            }
            
            #dropdownMenu-calendarType:hover {
                background-color: rgba(0, 0, 0, 0.05);
            }
            
            /* Gün başlıkları - aylık görünüm */
            .tui-full-calendar-month-view .tui-full-calendar-dayname-container {
                height: auto !important;
                min-height: 60px;
                padding: 8px 2px !important;
                display: grid !important;
                grid-auto-flow: column !important;
                align-items: stretch !important;
                gap: 0 !important;
                grid-template-columns: repeat(7, 1fr) !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-leftmargin {
                display: none !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-container .tui-full-calendar-dayname {
                position: relative !important;
                left: 0 !important;
                right: auto !important;
                top: auto !important;
                bottom: auto !important;
                width: auto !important;
                flex: 1 1 0 !important;
                margin: 0 !important;
                padding: 0 2px !important;
                height: auto !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: visible !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                width: 100% !important;
                min-height: 54px !important;
                padding: 8px 4px !important;
                margin: 0 !important;
                box-sizing: border-box !important;
                border-radius: 8px !important;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
                border: 1px solid #dee2e6 !important;
                text-align: center !important;
                text-transform: uppercase !important;
                letter-spacing: 0.4px !important;
                font-weight: 600 !important;
                color: #495057 !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
                transition: all 0.2s ease !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area:hover {
                background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
                transform: translateY(-1px) !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-today .tui-full-calendar-dayname-date-area {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                color: #1976d2 !important;
                border-color: #90caf9 !important;
                box-shadow: 0 4px 8px rgba(25, 118, 210, 0.12) !important;
            }

            /* Seçili tarih - aylık görünüm */
            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area.selected-date {
                background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
                color: #e65100 !important;
                border-color: #ffb74d !important;
                box-shadow: 0 4px 8px rgba(230, 81, 0, 0.12) !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area .tui-full-calendar-dayname-date {
                display: none !important;
            }

            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area .tui-full-calendar-dayname-name {
                font-size: 0.9rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px !important;
                color: #283c50 !important;
                display: inline-block !important;
            }

            /* Günlük görünümde başlık kısmını saatlerin başladığı yere uzat */
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
                border-bottom: 2px solid #dee2e6 !important;
                padding: 4px 2px !important;
                height: 50px !important;
                min-height: 50px !important;
                max-height: 50px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
                margin: 0 !important;
                position: relative !important;
            }

            .tui-full-calendar-day-view .tui-full-calendar-timegrid-col {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                height: 50px !important;
                min-height: 50px !important;
                padding: 4px 2px !important;
                text-align: center !important;
                border-right: 1px solid #dee2e6 !important;
                overflow: hidden !important;
            }

            .tui-full-calendar-day-view .tui-full-calendar-timegrid-col:last-child {
                border-right: none !important;
            }

            .tui-full-calendar-day-view .tui-full-calendar-timegrid-col .tui-full-calendar-dayname-name {
                font-size: 0.75rem !important;
                font-weight: 600 !important;
                color: #6c757d !important;
                text-transform: uppercase !important;
                letter-spacing: 0.3px !important;
                display: block !important;
                order: 1 !important;
                margin-bottom: 4px !important;
            }

            .tui-full-calendar-day-view .tui-full-calendar-timegrid-date {
                font-size: 1rem !important;
                font-weight: 700 !important;
                color: #495057 !important;
                display: block !important;
                order: 2 !important;
            }

            .tui-full-calendar-day-view .tui-full-calendar-today .tui-full-calendar-timegrid-date {
                color: #1976d2 !important;
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                padding: 6px 8px !important;
                border-radius: 6px !important;
            }

            /* Sol taraftaki saat paneli - net ve temiz */
            .tui-full-calendar-week-view .tui-full-calendar-timegrid-hour,
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-hour {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
                border-right: 2px solid #dee2e6 !important;
                border-bottom: 1px solid #e9ecef !important;
                color: #495057 !important;
                font-weight: 700 !important;
                font-size: 0.75rem !important;
                text-align: center !important;
                padding: 0 !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 52px !important;
                max-height: 52px !important;
                min-height: 52px !important;
                overflow: hidden !important;
                line-height: 52px !important;
                flex-shrink: 0 !important;
            }

            /* İlk saati (08:00) gün başlığının altına al - kaldırıldı */
            
            .tui-full-calendar-week-view .tui-full-calendar-timegrid-hour span,
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-hour span {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 100% !important;
                height: 100% !important;
                line-height: 1 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .tui-full-calendar-week-view .tui-full-calendar-timegrid-left,
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-left {
                min-width: 70px !important;
                width: 70px !important;
                display: flex !important;
                flex-direction: column !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Son saati gizle (23:00) */
            .tui-full-calendar-week-view .tui-full-calendar-timegrid-hour:nth-last-child(1),
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-hour:nth-last-child(1) {
                display: none !important;
            }
            
            /* Karşılık gelen grid satırını da gizle */
            .tui-full-calendar-week-view .tui-full-calendar-timegrid-row:nth-last-child(1),
            .tui-full-calendar-day-view .tui-full-calendar-timegrid-row:nth-last-child(1) {
                display: none !important;
            }

            /* Seçili saat hücresi - soft yeşil */
            .tui-full-calendar-week-view .tui-full-calendar-time-cell.on-select,
            .tui-full-calendar-day-view .tui-full-calendar-time-cell.on-select {
                background-color: #a8e6c9 !important;
            }
            
            /* Seçili tarih için stil */
            .tui-full-calendar-month-view .selected-date {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                color: #1976d2 !important;
                border: 1px solid #90caf9 !important;
                border-radius: 6px !important;
                box-shadow: 0 2px 4px rgba(25, 118, 210, 0.2) !important;
                font-weight: 600 !important;
            }
            
            .tui-full-calendar-month-view .tui-full-calendar-day-grid-date.selected-date,
            .tui-full-calendar-month-view .tui-full-calendar-dayname-date-area.selected-date {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                color: #1976d2 !important;
                border: 1px solid #90caf9 !important;
                border-radius: 6px !important;
                box-shadow: 0 2px 4px rgba(25, 118, 210, 0.2) !important;
            }

            /* Seçili saat hücresindeki yazı - koyu yeşil ve belirgin */
            .tui-full-calendar-week-view .tui-full-calendar-time-cell.on-select .tui-full-calendar-time-cell-content,
            .tui-full-calendar-day-view .tui-full-calendar-time-cell.on-select .tui-full-calendar-time-cell-content,
            .tui-full-calendar-week-view .tui-full-calendar-time-cell.on-select span,
            .tui-full-calendar-day-view .tui-full-calendar-time-cell.on-select span {
                color: #00695c !important;
                font-weight: 800 !important;
                font-size: 0.95rem !important;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.08) !important;
            }

            /* Seçili hücre border */
            .tui-full-calendar-week-view .tui-full-calendar-time-cell.on-select,
            .tui-full-calendar-day-view .tui-full-calendar-time-cell.on-select {
                border: 2px solid #00897b !important;
            }

            /* Etkinlik kartları - soft yeşil */
            .tui-full-calendar-event {
                background-color: transparent !important;
                background: transparent !important;
                border-left: 3px solid #1976d2 !important;
                color: #1976d2 !important;
            }

            .tui-full-calendar-event:hover {
                background: rgba(25, 118, 210, 0.05) !important;
                box-shadow: 0 2px 8px rgba(25, 118, 210, 0.15) !important;
            }

            .tui-full-calendar-event .tui-full-calendar-event-title {
                color: #1976d2 !important;
                font-weight: 700 !important;
            }

            .tui-full-calendar-event .tui-full-calendar-event-time {
                color: #1976d2 !important;
                font-weight: 600 !important;
            }

            /* Time guide label - seçili saat aralığı yazısı */
            .tui-full-calendar-time-guide-creation-label {
                background-color: transparent !important;
                background: transparent !important;
                color: #1976d2 !important;
                font-weight: 700 !important;
                border: none !important;
                box-shadow: none !important;
            }

            .tui-full-calendar-time-guide-bottom {
                background-color: transparent !important;
                background: transparent !important;
                color: #1976d2 !important;
                font-weight: 700 !important;
                border: none !important;
                box-shadow: none !important;
            }
                font-size: 0.75rem !important;
                line-height: 1 !important;
                white-space: nowrap !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .tui-full-calendar-week-container {
                min-height: 600px;
            }
            
            .tui-full-calendar-timegrid-container {
                min-height: 500px;
            }
            
            .tui-full-calendar-time-date {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                line-height: 1.2;
            }
        `;
        document.head.appendChild(style);
    }

    function showHourLabels() {
        const hourDivs = document.querySelectorAll('.tui-full-calendar-timegrid-hour');
        hourDivs.forEach(function(hourDiv) {
            const span = hourDiv.querySelector('span');
            if (span && span.textContent.trim()) {
                const text = span.textContent.trim();
                // "8 am" veya "8 pm" formatından 24 saat formatına dönüştür
                let hour = parseInt(text);
                let converted = hour.toString().padStart(2, '0');
                
                if (text.includes('pm') && hour !== 12) {
                    converted = (hour + 12).toString().padStart(2, '0');
                } else if (text.includes('am') && hour === 12) {
                    converted = '00';
                }
                
                // Span'ı göster ve içeriğini güncelle
                span.textContent = converted + ':00';
                span.style.cssText = 'display: flex !important; align-items: center !important; justify-content: center !important; width: 100% !important; height: 100% !important; color: #495057 !important; font-weight: 700 !important; font-size: 0.85rem !important;';
                
                // Parent div'i de sağlam şekilde ayarla
                hourDiv.style.cssText = 'display: flex !important; align-items: center !important; justify-content: center !important; height: 52px !important; min-height: 52px !important; max-height: 52px !important; padding: 0 !important; margin: 0 !important;';
            }
        });

        // Seçili zaman aralığı (time guide) elementlerini temizle
        const timeGuideLabels = document.querySelectorAll('.tui-full-calendar-time-guide-creation-label');
        timeGuideLabels.forEach(function(label) {
            label.style.backgroundColor = 'transparent !important';
            label.style.background = 'transparent !important';
            label.style.border = 'none !important';
            label.style.boxShadow = 'none !important';
            label.style.color = '#1976d2 !important';
            label.style.fontWeight = '700 !important';
        });
    }

    // Yeni eklenen önizleme fonksiyonları
    function showSchedulePreview(schedule) {
        try {
            const previewModalElement = document.getElementById('calendarEventPreviewModal');
            if (!previewModalElement) {
                console.error('Preview modal element not found');
                // Fallback - doğrudan düzenleme modal'ını aç
                openModal({ mode: 'edit', schedule: schedule });
                return;
            }
            
            const previewModal = new bootstrap.Modal(previewModalElement);
            
            // Başlık ve içerik doldur
            const titleElement = document.getElementById('preview-event-title');
            const datetimeElement = document.getElementById('preview-event-datetime');
            
            if (titleElement) titleElement.textContent = schedule.title || 'Başlıksız Etkinlik';
            if (datetimeElement) datetimeElement.textContent = formatScheduleDateTime(schedule);
            
            // Açıklama
            const description = schedule.body || schedule.description || '';
            const descriptionElement = document.getElementById('preview-event-description');
            const descriptionContainer = document.getElementById('preview-event-description-container');
            if (descriptionElement && descriptionContainer) {
                descriptionElement.textContent = description;
                if (description) {
                    descriptionContainer.classList.remove('d-none');
                } else {
                    descriptionContainer.classList.add('d-none');
                }
            }
            
            // Konum
            const location = schedule.location || '';
            const locationElement = document.getElementById('preview-event-location');
            const locationText = document.getElementById('preview-location-text');
            if (locationElement && locationText) {
                locationText.textContent = location;
                if (location) {
                    locationElement.classList.remove('d-none');
                } else {
                    locationElement.classList.add('d-none');
                }
            }
            
            // Tür ve renk
            const calendarType = getCalendarTypeById(schedule.calendarId);
            const typeElement = document.getElementById('preview-event-type');
            const colorElement = document.getElementById('preview-event-color');
            
            if (typeElement && colorElement) {
                if (calendarType) {
                    typeElement.textContent = calendarType.name;
                    colorElement.style.backgroundColor = calendarType.bgColor || '#1abc9c';
                } else {
                    typeElement.textContent = 'Genel';
                    colorElement.style.backgroundColor = '#1abc9c';
                }
            }
            
            // Buton event'lerini ayarla
            setupPreviewButtons(schedule);
            
            // Modal'ı göster
            previewModal.show();
        } catch (error) {
            console.error('Error showing schedule preview:', error);
            // Hata durumunda fallback - doğrudan düzenleme modal'ını aç
            openModal({ mode: 'edit', schedule: schedule });
        }
    }

    function formatScheduleDateTime(schedule) {
        const start = moment(schedule.start);
        const end = moment(schedule.end);
        
        // Türkçe ay isimleri ve gün isimleri için locale ayarla
        moment.locale('tr');
        
        if (schedule.isAllDay) {
            if (start.isSame(end, 'day')) {
                return start.format('DD MMMM YYYY dddd') + ' - Tüm Gün';
            } else {
                return start.format('DD MMMM YYYY dddd') + ' - ' + end.format('DD MMMM YYYY dddd');
            }
        } else {
            if (start.isSame(end, 'day')) {
                return start.format('DD MMMM YYYY dddd') + ' Saat: ' + start.format('HH:mm') + ' - ' + end.format('HH:mm');
            } else {
                return start.format('DD MMMM YYYY dddd HH:mm') + ' - ' + end.format('DD MMMM YYYY dddd HH:mm');
            }
        }
    }

    // Calendar types fallback - eğer data element yoksa
    function getDefaultCalendarTypes() {
        return [
            { id: 'genel', name: 'Genel', bgColor: '#1abc9c' },
            { id: 'toplanti', name: 'Toplantı', bgColor: '#3498db' },
            { id: 'dogum', name: 'Doğum Günü', bgColor: '#e74c3c' },
            { id: 'odeme', name: 'Ödeme', bgColor: '#f39c12' },
            { id: 'bakim', name: 'Bakım', bgColor: '#9b59b6' },
            { id: 'duyuru', name: 'Duyuru', bgColor: '#e67e22' }
        ];
    }

    function getCalendarTypeById(calendarId) {
        try {
            const calendarTypesElement = document.getElementById('calendar-types-data');
            let calendarTypes;
            
            if (calendarTypesElement) {
                calendarTypes = JSON.parse(calendarTypesElement.textContent || '[]');
            } else {
                // Fallback - default calendar types
                calendarTypes = getDefaultCalendarTypes();
            }
            
            return calendarTypes.find(type => type.id === calendarId) || null;
        } catch (error) {
            console.error('Error parsing calendar types:', error);
            // Hata durumunda fallback kullan
            const calendarTypes = getDefaultCalendarTypes();
            return calendarTypes.find(type => type.id === calendarId) || null;
        }
    }

    function setupPreviewButtons(schedule) {
        try {
            // Düzenle butonu
            const editBtn = document.getElementById('preview-edit-btn');
            if (editBtn) {
                editBtn.onclick = function() {
                    try {
                        // Önizleme modal'ını kapat
                        const previewModal = bootstrap.Modal.getInstance(document.getElementById('calendarEventPreviewModal'));
                        if (previewModal) previewModal.hide();
                        
                        // Düzenleme modal'ını aç
                        setTimeout(function() {
                            openModal({ mode: 'edit', schedule: schedule });
                        }, 300);
                    } catch (error) {
                        console.error('Error opening edit modal:', error);
                        // Fallback - doğrudan düzenleme modal'ını aç
                        openModal({ mode: 'edit', schedule: schedule });
                    }
                };
            }
            
            // Sil butonu
            const deleteBtn = document.getElementById('preview-delete-btn');
            if (deleteBtn) {
                deleteBtn.onclick = function() {
                    if (confirm('Bu etkinliği silmek istediğinize emin misiniz?')) {
                        try {
                            // Önizleme modal'ını kapat
                            const previewModal = bootstrap.Modal.getInstance(document.getElementById('calendarEventPreviewModal'));
                            if (previewModal) previewModal.hide();
                            
                            // Silme işlemini gerçekleştir
                            setTimeout(function() {
                                persistSchedule({ action: 'delete', id: schedule.id })
                                    .then(refreshSchedules)
                                    .catch(function (error) {
                                        console.error(error);
                                        showAlert('Etkinlik silinemedi.');
                                    });
                            }, 300);
                        } catch (error) {
                            console.error('Error deleting schedule:', error);
                            showAlert('Etkinlik silinemedi.');
                        }
                    }
                };
            }
        } catch (error) {
            console.error('Error setting up preview buttons:', error);
        }
    }

    // Mutation observer - dinamik olarak eklenen elementleri temizle
    function initTimeGuideCleaner() {
        const observer = new MutationObserver(function() {
            const timeGuideLabels = document.querySelectorAll('.tui-full-calendar-time-guide-creation-label');
            timeGuideLabels.forEach(function(label) {
                if (label.style.border !== 'none') {
                    label.style.backgroundColor = 'transparent !important';
                    label.style.background = 'transparent !important';
                    label.style.border = 'none !important';
                    label.style.boxShadow = 'none !important';
                    label.style.color = '#1976d2 !important';
                    label.style.fontWeight = '700 !important';
                }
            });
        });

        const timegridContainer = document.querySelector('.tui-full-calendar-timegrid-container') || document.querySelector('.tui-full-calendar-week-view');
        if (timegridContainer) {
            observer.observe(timegridContainer, { childList: true, subtree: true });
        }
    }

    if (!window.tui || typeof tui.Calendar !== 'function') {
        console.error('tui.Calendar kütüphanesi yüklenemedi.');
        return;
    }

    if (typeof moment !== 'function') {
        console.error('moment.js kütüphanesi yüklenemedi.');
        return;
    }

    const calendarElement = document.getElementById('tui-calendar-init');
    if (!calendarElement) {
        return;
    }

    const calendars = (config.types || []).map(function (type) {
        return {
            id: type.id,
            name: type.name,
            color: type.color,
            bgColor: type.bgColor,
            dragBgColor: type.bgColor,
            borderColor: type.borderColor,
        };
    });

    const MONTH_VIEW_BASE_OPTIONS = {
        startDayOfWeek: 1,
        workweek: false,
        narrowWeekend: false,
        scheduleView: false,
        visibleScheduleCount: 4,
        isAlways6Weeks: false,
        visibleWeeksCount: 5,
    };

    const filters = new Set(calendars.map(function (item) { return item.id; }));
    const OUTSIDE_MONTH_HIDDEN_CLASS = 'calendar-hide-outside-month';
    const OUTSIDE_MONTH_EMPTY_WEEK_CLASS = 'calendar-hide-empty-week';
    const OUTSIDE_MONTH_STYLE_ID = 'calendar-hide-outside-month-style';

    const calendar = new tui.Calendar('#tui-calendar-init', {
        defaultView: 'month',
        calendars: calendars,
        useCreationPopup: false,
        useDetailPopup: false,
        taskView: false,
        scheduleView: ['time'],
        template: buildTemplates(),
        week: {
            startDayOfWeek: 1,
            showTimezoneCollapseButton: true,
            hourStart: 8,
            hourEnd: 23,
            daynames: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
            narrowWeekend: false,
        },
        month: Object.assign({}, MONTH_VIEW_BASE_OPTIONS),
        timezone: {
            zones: [
                {
                    timezoneName: config.timezone || 'Europe/Istanbul',
                    displayLabel: 'Yerel Saat',
                },
            ],
        },
    });

    const now = moment();
    const startOfMonth = now.clone().startOf('month');
    const startOfMonthWeek = startOfMonth.clone().startOf('week').add(1, 'day');
    calendar.setDate(startOfMonthWeek.toDate(), false);

    const modalElement = document.getElementById('calendarEventModal');
    const formElement = document.getElementById('calendar-event-form');
    const deleteButton = document.getElementById('calendar-delete-btn');
    const alertElement = document.getElementById('calendar-form-alert');
    const allDaySwitch = document.getElementById('calendar-event-all-day');
    const startInput = document.getElementById('calendar-event-start');
    const endInput = document.getElementById('calendar-event-end');
    const typeSelect = document.getElementById('calendar-event-type');
    const titleInput = document.getElementById('calendar-event-title');
    const locationInput = document.getElementById('calendar-event-location');
    const descriptionInput = document.getElementById('calendar-event-description');
    const hiddenIdInput = document.getElementById('calendar-event-id');
    const modalTitleElement = document.getElementById('calendarEventModalLabel');
    const modalSubtitleElement = document.getElementById('calendarEventModalSubtitle');
    const modalSaveButton = document.getElementById('calendar-save-btn');
    const newScheduleButton = document.getElementById('btn-new-schedule');

    const viewAllCheckbox = document.getElementById('viewAllSchedules');
    const filterInputs = document.querySelectorAll('#calendarList input.calendar-filter');
    const viewButtons = document.querySelectorAll('.calendar-view-option');
    const viewDropdownTrigger = document.getElementById('dropdownMenu-calendarType');
    const navigationButtons = document.querySelectorAll('.menu-navi button[data-action]');
    const renderRangeElement = document.getElementById('renderRange');
    const calendarTypeNameElement = document.getElementById('calendarTypeName');
    const calendarTypeIconElement = document.getElementById('calendarTypeIcon');

    let activeSchedule = null;
    let modalInstance = null;

    if (modalElement && window.bootstrap && typeof bootstrap.Modal === 'function') {
        modalInstance = new bootstrap.Modal(modalElement, { backdrop: 'static' });
    }

    initialiseFilters();
    initialiseViewControls();
    initialiseNavigation();
    initialiseForm();
    initialiseCalendarEvents();
    initialiseNewScheduleButton();
    addDropdownStyles();
    initialiseViewDropdown();
    calendar.setOptions({ month: getMonthOptionsForPreset('month') }, true);
    updateToolbar();
    refreshSchedules();

    function buildTemplates() {
        const dayNames = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        
        return {
            monthDayname: function (dayname) {
                return '<span class="calendar-dayname" style="font-weight: 700; font-size: 0.9rem; color: #2c3e50; text-transform: uppercase; letter-spacing: 0.5px;">' + dayNames[dayname.day % 7] + '</span>';
            },
            weekDayname: function (dayname) {
                const dayIndex = dayname.day % 7;
                return '<span class="calendar-dayname" style="font-weight: 700; font-size: 0.9rem; color: #2c3e50; text-transform: uppercase; letter-spacing: 0.5px;">' + dayNames[dayIndex] + '</span>';
            },
            monthGridHeader: function (model) {
                const date = new Date(model.date);
                const dayNumber = date.getDate();
                const isToday = moment(date).isSame(moment(), 'day');
                const todayStyle = isToday ? 'background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); color: #1976d2; border: 1px solid #90caf9; border-radius: 8px; padding: 6px 4px;' : '';
                
                return '<div class="calendar-grid-header" style="text-align: center; padding: 8px 4px; font-weight: 600; font-size: 1.1rem; color: #343a40; ' + todayStyle + '">' +
                       '<div class="day-number" style="font-weight: 600;">' + dayNumber + '</div>' +
                       '</div>';
            },
            weekGridHeader: function (model) {
                const date = new Date(model.date);
                const dayNumber = date.getDate();
                const isToday = moment(date).isSame(moment(), 'day');
                const todayStyle = isToday ? 'background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); color: #1976d2; border: 1px solid #90caf9; border-radius: 8px; padding: 4px 2px;' : '';
                
                return '<div class="calendar-grid-header" style="text-align: center; padding: 4px 2px; font-weight: 600; font-size: 0.95rem; color: #343a40; ' + todayStyle + '">' +
                       '<div class="day-number" style="font-weight: 600;">' + dayNumber + '</div>' +
                       '</div>';
            },
            monthGridHeaderExceed: function(hiddenSchedules) {
                return '<span class="calendar-more-count" style="color: #6c757d; font-size: 0.8rem;">+' + hiddenSchedules + ' daha</span>';
            },
            monthGridFooter: function() {
                return '';
            },
            monthGridFooterExceed: function(hiddenSchedules) {
                return '<span class="calendar-more-count" style="color: #6c757d; font-size: 0.8rem;">+' + hiddenSchedules + ' daha</span>';
            },
            milestone: function (schedule) {
                return '<span class="calendar-font-icon ic-milestone-b"></span> ' + schedule.title;
            },
            allday: function (schedule) {
                return schedule.title;
            },
            time: function (schedule) {
                const timeFormat = moment(schedule.start.getTime()).format('HH:mm');
                return '<strong>' + timeFormat + '</strong> ' + schedule.title;
            },
            popupIsAllDay: function () {
                return 'Tüm Gün';
            },
            popupStateFree: function () {
                return 'Müsait';
            },
            popupStateBusy: function () {
                return 'Meşgul';
            },
            popupDetailDate: function (isAllDay, start, end) {
                const formatString = isAllDay ? 'DD MMMM YYYY' : 'DD MMMM YYYY HH:mm';
                const startText = moment(start.getTime()).format(formatString);
                const endText = moment(end.getTime()).format(formatString);
                return startText + ' — ' + endText;
            },
            popupDetailLocation: function (schedule) {
                if (!schedule.location) {
                    return '';
                }
                return 'Konum: ' + schedule.location;
            },
            popupDetailBody: function (schedule) {
                if (!schedule.body) {
                    return '';
                }
                return 'Açıklama: ' + schedule.body;
            },
            popupDelete: function () {
                return 'Sil';
            },
            popupEdit: function () {
                return 'Düzenle';
            },
            popupSave: function () {
                return 'Kaydet';
            },
        };
    }

    function initialiseViewDropdown() {
        if (!viewDropdownTrigger) {
            return;
        }
        
        let isDropdownOpen = false;
        const dropdownMenu = viewDropdownTrigger.nextElementSibling;
        
        if (!dropdownMenu || !dropdownMenu.classList.contains('dropdown-menu')) {
            console.error('Dropdown menu bulunamadı');
            return;
        }
        
        viewDropdownTrigger.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            isDropdownOpen = !isDropdownOpen;
            
            if (isDropdownOpen) {
                dropdownMenu.classList.add('show');
                viewDropdownTrigger.setAttribute('aria-expanded', 'true');
            } else {
                dropdownMenu.classList.remove('show');
                viewDropdownTrigger.setAttribute('aria-expanded', 'false');
            }
        });
        
        document.addEventListener('click', function(event) {
            if (!viewDropdownTrigger.contains(event.target) && !dropdownMenu.contains(event.target)) {
                if (isDropdownOpen) {
                    dropdownMenu.classList.remove('show');
                    viewDropdownTrigger.setAttribute('aria-expanded', 'false');
                    isDropdownOpen = false;
                }
            }
        });
        
        if (window.bootstrap && typeof bootstrap.Dropdown === 'function') {
            try {
                bootstrap.Dropdown.getOrCreateInstance(viewDropdownTrigger);
            } catch (e) {
                console.log('Bootstrap dropdown başlatılamadı');
            }
        }
    }

    function changeCalendarView(view) {
        if (view === 'day') {
            const today = moment().toDate();
            calendar.setDate(today, true);
            calendar.changeView('day', true);
            setTimeout(showHourLabels, 50);
            setTimeout(initTimeGuideCleaner, 100);
            return;
        }
        
        if (view === 'week') {
            const today = moment().toDate();
            calendar.setDate(today, true);
            calendar.changeView('week', true);
            setTimeout(showHourLabels, 50);
            setTimeout(initTimeGuideCleaner, 100);
            return;
        }

        if (view === 'month') {
            calendar.setOptions({ month: getMonthOptionsForPreset('month') }, true);
            const firstDayOfMonth = moment().startOf('month').toDate();
            calendar.setDate(firstDayOfMonth, true);
            calendar.changeView('month', true);
            return;
        }

        if (view === '2weeks' || view === '3weeks') {
            calendar.setOptions({ month: getMonthOptionsForPreset(view) }, true);
            calendar.changeView('month', true);
            return;
        }

        calendar.setOptions({ month: getMonthOptionsForPreset('month') }, true);
        calendar.changeView('month', true);
    }

    function initialiseNavigation() {
        navigationButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                const action = event.currentTarget.getAttribute('data-action');
                if (action === 'move-prev') {
                    calendar.prev();
                }
                if (action === 'move-next') {
                    calendar.next();
                }
                if (action === 'move-today') {
                    calendar.today();
                }
                updateToolbar();
                refreshSchedules();
                setTimeout(showHourLabels, 50);
            });
        });
    }

    function initialiseForm() {
        if (!formElement) {
            return;
        }

        formElement.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAlert();

            if (!formElement.reportValidity()) {
                return;
            }

            const isAllDay = allDaySwitch.checked;
            const startValue = startInput.value;
            const endValue = endInput.value || startValue;
            const title = titleInput.value.trim();
            const calendarId = typeSelect.value;

            if (!title) {
                showAlert('Lütfen etkinlik başlığı giriniz.');
                return;
            }

            if (!startValue || !endValue) {
                showAlert('Başlangıç ve bitiş tarihlerini seçiniz.');
                return;
            }

            const rangeStart = moment(startValue);
            const rangeEnd = moment(endValue);

            if (rangeEnd.isBefore(rangeStart)) {
                showAlert('Bitiş tarihi başlangıç tarihinden önce olamaz.');
                return;
            }

            if (!filters.has(calendarId)) {
                filters.add(calendarId);
                syncFilterInputs();
            }

            const payload = {
                action: hiddenIdInput.value ? 'update' : 'create',
                id: hiddenIdInput.value || null,
                title: title,
                calendarId: calendarId,
                start: rangeStart.format('YYYY-MM-DDTHH:mm'),
                end: rangeEnd.format('YYYY-MM-DDTHH:mm'),
                isAllDay: isAllDay,
                location: locationInput.value.trim(),
                description: descriptionInput.value.trim(),
            };

            persistSchedule(payload)
                .then(function () {
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    resetForm();
                    refreshSchedules();
                })
                .catch(function (error) {
                    showAlert(error.message || 'Etkinlik kaydedilirken bir hata oluştu.');
                });
        });

        if (deleteButton) {
            deleteButton.addEventListener('click', function () {
                const scheduleId = hiddenIdInput.value;
                if (!scheduleId) {
                    return;
                }
                clearAlert();
                persistSchedule({ action: 'delete', id: scheduleId })
                    .then(function () {
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        resetForm();
                        refreshSchedules();
                    })
                    .catch(function (error) {
                        showAlert(error.message || 'Etkinlik silinemedi.');
                    });
            });
        }

        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', resetForm);
        }
    }

    function initialiseCalendarEvents() {
        let lastSelectedDate = null;
        
        // Tıklama olaylarını yönetmek için özel işleyici
        function clearDaySelections() {
            // Daha kapsamlı selector'lar - TUI Calendar'ın tüm element türleri
            const selectors = [
                '.tui-full-calendar-month-view .selected-date',
                '.tui-full-calendar-month-view [data-date].selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-day-grid-date.selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-date-area.selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-month-day-grid-item.selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-item.selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-date.selected-date',
                '.tui-full-calendar-month-view .tui-full-calendar-day-grid-item.selected-date'
            ];
            
            selectors.forEach(function(selector) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(function(elem) {
                    elem.classList.remove('selected-date');
                    elem.style.backgroundColor = '';
                    elem.style.background = '';
                    elem.style.boxShadow = '';
                    elem.style.color = '';
                    elem.style.fontWeight = '';
                });
            });
            
            // Ayrıca tüm data-date attribute'lu elementlerin stillerini temizle
            const allDateElements = document.querySelectorAll('.tui-full-calendar-month-view [data-date]');
            allDateElements.forEach(function(elem) {
                if (elem.classList.contains('selected-date')) {
                    elem.classList.remove('selected-date');
                }
                elem.style.backgroundColor = '';
                elem.style.background = '';
                elem.style.boxShadow = '';
                elem.style.color = '';
                elem.style.fontWeight = '';
            });
            
            // TUI Calendar'ın kendi seçim class'larını da temizle
            const tuiSelections = document.querySelectorAll('.tui-full-calendar-month-view .tui-full-calendar-state-selected, .tui-full-calendar-month-view .tui-is-selected');
            tuiSelections.forEach(function(elem) {
                elem.classList.remove('tui-full-calendar-state-selected', 'tui-is-selected');
                elem.style.backgroundColor = '';
                elem.style.background = '';
                elem.style.boxShadow = '';
                elem.style.color = '';
                elem.style.fontWeight = '';
            });
        }

        function selectDayElement(dateStr) {
            clearDaySelections();
            
            // TUI Calendar'ın spesifik class yapısına göre selector'lar
            let selectors = [
                // Ana tarih hücreleri
                '.tui-full-calendar-month-view [data-date="' + dateStr + '"]',
                // Gün grid içindeki tarihler
                '.tui-full-calendar-month-view .tui-full-calendar-day-grid-date[data-date="' + dateStr + '"]',
                // Gün isimleri alanı
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-date-area[data-date="' + dateStr + '"]',
                // Month day grid item'lar
                '.tui-full-calendar-month-view .tui-full-calendar-month-day-grid-item[data-date="' + dateStr + '"]',
                // Gün isim item'ları
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-item[data-date="' + dateStr + '"]',
                // TUI Calendar'ın kendi seçim class'ları
                '.tui-full-calendar-month-view .tui-full-calendar-day-grid-date[data-date="' + dateStr + '"] .tui-full-calendar-date',
                '.tui-full-calendar-month-view .tui-full-calendar-dayname-date[data-date="' + dateStr + '"]'
            ];
            
            let found = false;
            for (let selector of selectors) {
                let elements = document.querySelectorAll(selector);
                if (elements.length > 0) {
                    elements.forEach(function(element) {
                        element.classList.add('selected-date');
                        element.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important';
                        element.style.boxShadow = '0 2px 4px rgba(25, 118, 210, 0.2) !important';
                        element.style.color = '#1976d2 !important';
                        element.style.fontWeight = '600 !important';
                        element.style.borderRadius = '4px !important';
                    });
                    found = true;
                    lastSelectedDate = dateStr;
                    break;
                }
            }
            
            // Eğer element bulunamadıysa, daha genel bir yaklaşım dene
            if (!found) {
                const allElements = document.querySelectorAll('.tui-full-calendar-month-view [data-date]');
                allElements.forEach(function(elem) {
                    if (elem.getAttribute('data-date') === dateStr) {
                        elem.classList.add('selected-date');
                        elem.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important';
                        elem.style.boxShadow = '0 2px 4px rgba(25, 118, 210, 0.2) !important';
                        elem.style.color = '#1976d2 !important';
                        elem.style.fontWeight = '600 !important';
                        elem.style.borderRadius = '4px !important';
                        lastSelectedDate = dateStr;
                    }
                });
            }
        }

        // Manuel tıklama yönetimi - daha basit ve doğrudan
        function setupDirectClickHandling() {
            // Calendar container'ına tıklama olayını ekle
            const calendarContainer = document.getElementById('calendar');
            if (calendarContainer) {
                // Önceki event listener'ları temizle
                calendarContainer.removeEventListener('click', handleCalendarClick);
                calendarContainer.addEventListener('click', handleCalendarClick);
            }
        }
        
        function handleCalendarClick(e) {
            // Sadece boş alanlarda çalışsın - schedule'lara tıklanmadıysa
            if (!e.target.closest('.tui-full-calendar-schedule')) {
                // Tarih bilgisini bul
                let dateElement = e.target.closest('[data-date]');
                if (dateElement) {
                    const dateStr = dateElement.getAttribute('data-date');
                    if (dateStr) {
                        // Event propagation'ı durdur
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Önce tüm seçimleri agresif şekilde temizle
                        clearDaySelections();
                        
                        // Küçük bir gecikmeyle sonra seçimi uygula
                        setTimeout(function() {
                            selectDayElement(dateStr);
                        }, 10);
                        
                        // Küçük bir gecikmeyle modal'ı aç
                        setTimeout(function() {
                            const selectedDate = moment(dateStr).toDate();
                            openModal({
                                mode: 'create',
                                start: selectedDate,
                                end: moment(selectedDate).add(1, 'hours').toDate(),
                                isAllDay: false,
                            });
                        }, 50);
                    }
                }
            }
        }

        // beforeCreateSchedule olayını sadeleştir
        calendar.on('beforeCreateSchedule', function (event) {
            // Eğer event.start yoksa veya geçerli değilse, işlemi durdur
            if (!event.start) {
                return;
            }
            
            // Tarih nesnesini güvenli şekilde al
            let startDate, endDate;
            
            if (event.start) {
                if (typeof event.start.toISOString === 'function') {
                    startDate = event.start;
                } else if (event.start instanceof Date) {
                    startDate = event.start;
                } else {
                    startDate = new Date(event.start);
                }
            } else {
                startDate = new Date();
            }
            
            if (event.end) {
                if (typeof event.end.toISOString === 'function') {
                    endDate = event.end;
                } else if (event.end instanceof Date) {
                    endDate = event.end;
                } else {
                    endDate = new Date(event.end);
                }
            } else {
                endDate = moment(startDate).add(1, 'hours').toDate();
            }
            
            // Tarih seçimini göster
            const dateStr = moment(startDate).format('YYYY-MM-DD');
            selectDayElement(dateStr);
            
            // Modal'ı aç
            openModal({
                mode: 'create',
                start: startDate,
                end: endDate,
                isAllDay: event.isAllDay || false,
            });
        });

        // Manuel tıklama yönetimini başlat
        setupDirectClickHandling();

        // Global click handler - calendar dışına tıklanınca seçimi temizle
        document.addEventListener('click', function(e) {
            // Eğer calendar içine tıklanmadıysa seçimi temizle
            if (!e.target.closest('#calendar')) {
                clearDaySelections();
                lastSelectedDate = null;
            }
        });

        // Calendar tamamen yüklendikten sonra tıklama yönetimini tekrar başlat
        calendar.on('afterRender', function() {
            setTimeout(function() {
                setupDirectClickHandling();
            }, 100);
        });

        // Basit ve etkili seçim temizleme - her yeni görünümde
        calendar.on('beforeRender', function() {
            clearDaySelections();
            lastSelectedDate = null;
        });

        // Schedule oluşturma/güncelleme sonrası temizleme
        calendar.on('afterRender', function() {
            setTimeout(function() {
                clearDaySelections();
                lastSelectedDate = null;
            }, 50);
        });

        // Modal kapandığında temizle
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function() {
                clearDaySelections();
                lastSelectedDate = null;
            });
        }

        calendar.on('clickSchedule', function (event) {
            if (!event || !event.schedule) {
                return;
            }
            activeSchedule = event.schedule;
            // Önce önizleme modal'ını aç
            showSchedulePreview(event.schedule);
        });

        calendar.on('beforeUpdateSchedule', function (event) {
            if (!event || !event.schedule || !event.changes) {
                return;
            }
            const schedule = event.schedule;
            const changes = event.changes;
            const payload = buildUpdatePayload(schedule, changes);

            persistSchedule(payload)
                .then(function () {
                    calendar.updateSchedule(schedule.id, schedule.calendarId, changes);
                    calendar.render();
                    updateOutsideMonthVisibility();
                })
                .catch(function (error) {
                    console.error(error);
                    refreshSchedules();
                });
        });

        calendar.on('beforeDeleteSchedule', function (event) {
            if (!event || !event.schedule) {
                return;
            }
            persistSchedule({ action: 'delete', id: event.schedule.id })
                .then(refreshSchedules)
                .catch(function (error) {
                    console.error(error);
                });
        });
    }

    function initialiseNewScheduleButton() {
        if (!newScheduleButton) {
            return;
        }

        newScheduleButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const start = moment().startOf('hour');
            const end = moment(start).add(1, 'hours');

            openModal({
                mode: 'create',
                start: start.toDate(),
                end: end.toDate(),
                isAllDay: false,
            });
        });
    }

    function initialiseFilters() {
        if (viewAllCheckbox) {
            viewAllCheckbox.addEventListener('change', function () {
                const isChecked = viewAllCheckbox.checked;
                filterInputs.forEach(function (input) {
                    input.checked = isChecked;
                    if (isChecked) {
                        filters.add(input.value);
                    } else {
                        filters.delete(input.value);
                    }
                });
                refreshSchedules();
            });
        }

        filterInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                if (input.checked) {
                    filters.add(input.value);
                } else {
                    filters.delete(input.value);
                }
                updateViewAllCheckbox();
                refreshSchedules();
            });
        });

        syncFilterInputs();
    }

    function initialiseViewControls() {
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                
                const view = button.getAttribute('data-view');
                changeCalendarView(view);
                updateToolbar();
                
                if (viewDropdownTrigger && viewDropdownTrigger.nextElementSibling) {
                    viewDropdownTrigger.nextElementSibling.classList.remove('show');
                }
            });
        });
    }

    function refreshSchedules() {
        const filtersArray = Array.from(filters);
        const rangeStart = calendar.getDateRangeStart();
        const rangeEnd = calendar.getDateRangeEnd();

        requestSchedules({
            start: formatForApi(rangeStart),
            end: formatForApi(rangeEnd),
            calendars: filtersArray,
        })
            .then(function (schedules) {
                calendar.clear();
                calendar.createSchedules(schedules.map(mapSchedule));
                calendar.render();
                updateOutsideMonthVisibility();
            })
            .catch(function (error) {
                console.error(error);
                updateOutsideMonthVisibility();
            });
    }

    function requestSchedules(payload) {
        const body = {
            action: 'list',
            start: payload.start,
            end: payload.end,
            calendars: payload.calendars,
        };

        if (config.siteId) {
            body.siteId = config.siteId;
        }

        if (config.firmId) {
            body.firmId = config.firmId;
        }

        return sendRequest(body).then(function (response) {
            return response.data || [];
        });
    }

    function persistSchedule(payload) {
        const requestPayload = Object.assign({}, payload);

        if (config.siteId && !requestPayload.siteId) {
            requestPayload.siteId = config.siteId;
        }

        if (config.firmId && !requestPayload.firmId) {
            requestPayload.firmId = config.firmId;
        }

        return sendRequest(requestPayload).then(function (response) {
            if (response.status !== 'success') {
                throw new Error(response.message || 'İşlem tamamlanamadı.');
            }
            return response;
        });
    }

    function stripHtmlTags(text) {
        return text.replace(/<[^>]*>/g, '');
    }

    function sendRequest(payload) {
        return fetch(config.endpoints.events, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': config.csrfToken || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })
            .then(function (response) {
                const statusInfo = response.status ? ' (HTTP ' + response.status + ')' : '';

                return response
                    .clone()
                    .json()
                    .catch(function () {
                        return response.text().then(function (text) {
                            const cleaned = text ? stripHtmlTags(text).trim() : '';
                            if (cleaned) {
                                throw new Error(cleaned + statusInfo);
                            }
                            throw new Error('Sunucuya ulaşılamadı.' + statusInfo);
                        });
                    })
                    .then(function (data) {
                        if (!response.ok) {
                            const message = data && data.message ? data.message : 'Sunucuya ulaşılamadı.';
                            throw new Error(message + statusInfo);
                        }
                        return data;
                    });
            })
            .catch(function (error) {
                const message = error && error.message ? error.message : 'Sunucuya ulaşılamadı.';
                throw new Error(message);
            });
    }

    function toMoment(value) {
        if (!value) {
            return moment();
        }

        if (typeof value.toDate === 'function') {
            return moment(value.toDate());
        }

        if (value instanceof Date) {
            return moment(value);
        }

        return moment(value);
    }

    function toNativeDate(value) {
        if (!value) {
            return new Date();
        }

        if (typeof value.toDate === 'function') {
            return value.toDate();
        }

        if (value instanceof Date) {
            return value;
        }

        const parsed = new Date(value);
        return Number.isNaN(parsed.getTime()) ? new Date() : parsed;
    }

    function formatDateLabel(value, options) {
        const date = toNativeDate(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        return new Intl.DateTimeFormat('tr-TR', options).format(date);
    }

    function parseToDate(value) {
        if (!value) {
            return new Date();
        }

        if (typeof value.toDate === 'function') {
            return value.toDate();
        }

        if (value instanceof Date) {
            return value;
        }

        const isoCandidate = moment(value, moment.ISO_8601, true);
        if (isoCandidate.isValid()) {
            return isoCandidate.toDate();
        }

        const fallback = moment(value, 'YYYY-MM-DD HH:mm:ss', true);
        if (fallback.isValid()) {
            return fallback.toDate();
        }

        const generic = moment(value);
        return generic.isValid() ? generic.toDate() : new Date();
    }

    function mapSchedule(item) {
        const calendarType = findCalendar(item.calendarId);
        const baseColors = calendarType ? calendarType : {
            color: '#1976d2',
            bgColor: 'transparent',
            borderColor: '#1976d2',
        };

        // Eski turkuaz renklerini maviye çevir
        let bgColor = item.bgColor || baseColors.bgColor;
        let borderColor = item.borderColor || baseColors.borderColor;
        
        if (bgColor === '#1abc9c' || bgColor === '#16a085' || bgColor === '#a8e6c9') {
            bgColor = 'transparent';
        }
        if (borderColor === '#1abc9c' || borderColor === '#16a085' || borderColor === '#00897b') {
            borderColor = '#1976d2';
        }

        return {
            id: item.id,
            calendarId: item.calendarId,
            title: item.title,
            isAllDay: Boolean(item.isAllDay),
            category: item.isAllDay ? 'allday' : 'time',
            start: parseToDate(item.start),
            end: parseToDate(item.end),
            color: '#1976d2',
            bgColor: bgColor,
            dragBgColor: bgColor,
            borderColor: borderColor,
            location: item.location || '',
            body: item.description || '',
            raw: {
                description: item.description || '',
                bgColor: bgColor,
                borderColor: borderColor,
            },
        };
    }

    function buildUpdatePayload(schedule, changes) {
        const payload = {
            action: 'update',
            id: schedule.id,
            title: changes.title !== undefined ? changes.title : schedule.title,
            calendarId: changes.calendarId || schedule.calendarId,
            isAllDay: changes.isAllDay !== undefined ? changes.isAllDay : schedule.isAllDay,
            location: changes.location !== undefined ? changes.location : schedule.location,
            description: changes.body !== undefined ? changes.body : schedule.body,
        };

        const startValue = changes.start ? changes.start : schedule.start;
        const endValue = changes.end ? changes.end : schedule.end;

        payload.start = toMoment(startValue).format('YYYY-MM-DDTHH:mm');
        payload.end = toMoment(endValue).format('YYYY-MM-DDTHH:mm');

        return payload;
    }

    function openModal(options) {
        clearAlert();
        resetForm();

        if (modalTitleElement) {
            modalTitleElement.textContent = options.mode === 'edit' ? 'Etkinliği Düzenle' : 'Yeni Etkinlik';
        }

        if (modalSubtitleElement) {
            modalSubtitleElement.textContent = options.mode === 'edit'
                ? 'Etkinlik bilgilerini güncelleyin.'
                : 'Yeni bir etkinlik oluşturun.';
        }

        if (modalSaveButton) {
            modalSaveButton.textContent = options.mode === 'edit' ? 'Güncelle' : 'Kaydet';
        }

        if (options.mode === 'edit' && options.schedule) {
            const schedule = options.schedule;
            hiddenIdInput.value = schedule.id;
            typeSelect.value = schedule.calendarId;
            titleInput.value = schedule.title;
            locationInput.value = schedule.location || '';
            descriptionInput.value = schedule.body || '';
            allDaySwitch.checked = Boolean(schedule.isAllDay);

            const startDate = moment(schedule.start.toDate ? schedule.start.toDate() : schedule.start).toDate();
            const endDate = moment(schedule.end.toDate ? schedule.end.toDate() : schedule.end).toDate();
            startInput.value = moment(startDate).format('YYYY-MM-DDTHH:mm');
            endInput.value = schedule.isAllDay
                ? moment(endDate).subtract(1, 'minutes').format('YYYY-MM-DDTHH:mm')
                : moment(endDate).format('YYYY-MM-DDTHH:mm');

            if (deleteButton) {
                deleteButton.classList.remove('d-none');
            }
        } else {
            if (deleteButton) {
                deleteButton.classList.add('d-none');
            }

            const defaultStart = options.start ? moment(options.start) : moment();
            const defaultEnd = options.end ? moment(options.end) : moment(defaultStart).add(1, 'hours');
            startInput.value = defaultStart.format('YYYY-MM-DDTHH:mm');
            endInput.value = defaultEnd.format('YYYY-MM-DDTHH:mm');
            allDaySwitch.checked = Boolean(options.isAllDay);
        }

        if (modalInstance) {
            modalInstance.show();
        }
    }

    function resetForm() {
        if (formElement) {
            formElement.reset();
        }
        hiddenIdInput.value = '';
        if (deleteButton) {
            deleteButton.classList.add('d-none');
        }
        if (alertElement) {
            alertElement.textContent = '';
            alertElement.classList.add('d-none');
        }
        if (modalTitleElement) {
            modalTitleElement.textContent = 'Etkinlik Kaydı';
        }
        if (modalSubtitleElement) {
            modalSubtitleElement.textContent = 'Yeni bir etkinlik oluşturun.';
        }
        if (modalSaveButton) {
            modalSaveButton.textContent = 'Kaydet';
        }
    }

    function showAlert(message) {
        if (!alertElement) {
            alert(message);
            return;
        }
        alertElement.textContent = message;
        alertElement.classList.remove('d-none');
    }

    function clearAlert() {
        if (!alertElement) {
            return;
        }
        alertElement.textContent = '';
        alertElement.classList.add('d-none');
    }

    function syncFilterInputs() {
        filterInputs.forEach(function (input) {
            input.checked = filters.has(input.value);
        });
        updateViewAllCheckbox();
    }

    function updateViewAllCheckbox() {
        if (!viewAllCheckbox) {
            return;
        }
        const allChecked = Array.from(filterInputs).every(function (input) { return input.checked; });
        viewAllCheckbox.checked = allChecked;
    }

    function updateToolbar() {
        const viewName = calendar.getViewName();
        const options = calendar.getOptions();
        const currentDate = calendar.getDate();
        const iconMap = {
            day: 'feather-list',
            week: 'feather-umbrella',
            month: 'feather-grid',
            two: 'feather-sliders',
            three: 'feather-framer',
        };

        let label = 'Aylık';
        let icon = iconMap.month;
        if (viewName === 'day') {
            label = 'Günlük';
            icon = iconMap.day;
        } else if (viewName === 'week') {
            label = 'Haftalık';
            icon = iconMap.week;
        } else if (viewName === 'month') {
            const visibleWeeks = options.month && options.month.visibleWeeksCount;
            if (visibleWeeks === 2) {
                label = '2 Haftalık';
                icon = iconMap.two;
            } else if (visibleWeeks === 3) {
                label = '3 Haftalık';
                icon = iconMap.three;
            } else if (visibleWeeks === 5) {
                label = 'Aylık';
                icon = iconMap.month;
            }
        }

        if (calendarTypeNameElement) {
            calendarTypeNameElement.textContent = label;
        }
        if (calendarTypeIconElement) {
            calendarTypeIconElement.className = 'feather calendar-icon fs-12 me-1 ' + icon;
        }

        if (renderRangeElement) {
            let text = '';
            if (viewName === 'day') {
                text = formatDateLabel(currentDate, { day: '2-digit', month: 'long', year: 'numeric' });
            } else if (viewName === 'month') {
                text = formatDateLabel(currentDate, { month: 'long', year: 'numeric' });
            } else {
                const start = calendar.getDateRangeStart();
                const end = calendar.getDateRangeEnd();
                const startText = formatDateLabel(start, { day: '2-digit', month: 'long', year: 'numeric' });
                const endText = formatDateLabel(end, { day: '2-digit', month: 'long', year: 'numeric' });
                text = startText + ' - ' + endText;
            }
            renderRangeElement.textContent = text;
        }
    }

    function formatForApi(date) {
        return toMoment(date).format('YYYY-MM-DD HH:mm:ss');
    }

    function findCalendar(id) {
        return calendars.find(function (item) { return item.id === id; });
    }

    function ensureOutsideMonthStyle() {
        if (document.getElementById(OUTSIDE_MONTH_STYLE_ID)) {
            return;
        }

        const style = document.createElement('style');
        style.id = OUTSIDE_MONTH_STYLE_ID;
        style.textContent = '#tui-calendar-init .' + OUTSIDE_MONTH_HIDDEN_CLASS + ' { visibility: hidden; pointer-events: none; }\n' +
            '#tui-calendar-init .' + OUTSIDE_MONTH_HIDDEN_CLASS + ' .tui-full-calendar-schedule { display: none !important; }\n' +
            '#tui-calendar-init .' + OUTSIDE_MONTH_EMPTY_WEEK_CLASS + ' { display: none; }';
        document.head.appendChild(style);
    }

    function updateOutsideMonthVisibility() {
        if (!calendarElement) {
            return;
        }

        let dayCells = calendarElement.querySelectorAll('.tui-full-calendar-month-day');

        if (!dayCells.length) {
            dayCells = calendarElement.querySelectorAll('[data-date]');
        }

        if (!dayCells.length) {
            return;
        }

        const viewName = calendar.getViewName();
        const weekRows = calendarElement.querySelectorAll('.tui-full-calendar-month-week-item');

        if (viewName !== 'month') {
            dayCells.forEach(function (cell) {
                cell.classList.remove(OUTSIDE_MONTH_HIDDEN_CLASS);
            });
            weekRows.forEach(function (row) {
                row.classList.remove(OUTSIDE_MONTH_EMPTY_WEEK_CLASS);
            });
            return;
        }

        ensureOutsideMonthStyle();

        const currentDate = calendar.getDate();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        dayCells.forEach(function (cell) {
            let dateValue = cell.getAttribute('data-date');

            if (!dateValue && cell.dataset && cell.dataset.date) {
                dateValue = cell.dataset.date;
            }

            if (!dateValue) {
                const fallback = cell.querySelector('[data-date]');
                if (fallback) {
                    dateValue = fallback.getAttribute('data-date');
                }
            }

            if (!dateValue) {
                cell.classList.remove(OUTSIDE_MONTH_HIDDEN_CLASS);
                return;
            }

            const parsed = new Date(dateValue);

            if (!Number.isNaN(parsed.getTime()) && parsed.getMonth() === currentMonth && parsed.getFullYear() === currentYear) {
                cell.classList.remove(OUTSIDE_MONTH_HIDDEN_CLASS);
            } else {
                cell.classList.add(OUTSIDE_MONTH_HIDDEN_CLASS);
            }
        });

        weekRows.forEach(function (row) {
            const rowCells = row.querySelectorAll('.tui-full-calendar-month-day');
            if (!rowCells.length) {
                row.classList.remove(OUTSIDE_MONTH_EMPTY_WEEK_CLASS);
                return;
            }
            const hasVisibleDay = Array.from(rowCells).some(function (cell) {
                return !cell.classList.contains(OUTSIDE_MONTH_HIDDEN_CLASS);
            });
            if (hasVisibleDay) {
                row.classList.remove(OUTSIDE_MONTH_EMPTY_WEEK_CLASS);
            } else {
                row.classList.add(OUTSIDE_MONTH_EMPTY_WEEK_CLASS);
            }
        });
    }

    function getMonthOptionsForPreset(preset) {
        const options = Object.assign({}, MONTH_VIEW_BASE_OPTIONS);

        if (preset === '2weeks') {
            options.visibleWeeksCount = 2;
            options.isAlways6Weeks = false;
        } else if (preset === '3weeks') {
            options.visibleWeeksCount = 3;
            options.isAlways6Weeks = false;
        } else {
            options.visibleWeeksCount = 5;
            options.isAlways6Weeks = false;
        }

        return options;
    }
})();
