<!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="assets/vendors/js/vendors.min.js"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="assets/vendors/js/daterangepicker.min.js"></script>
	
    <script src="assets/vendors/js/apexcharts.min.js"></script>
	
    <script src="assets/vendors/js/circle-progress.min.js"></script>
	
    <script src="assets/vendors/js/select2.min.js"></script>	
    <script src="assets/vendors/js/select2-active.min.js"></script>
	
    <script src="assets/vendors/js/tagify.min.js"></script>
    <script src="assets/vendors/js/tagify-data.min.js"></script>
	
    <script src="assets/vendors/js/quill.min.js"></script>	
	
    <script src="assets/vendors/js/apexcharts.min.js"></script>
	
    <script src="assets/vendors/js/jquery.time-to.min.js "></script>
	
    <script src="assets/vendors/js/circle-progress.min.js"></script>	

    <script src="assets/vendors/js/tui-code-snippet.min.js"></script>
    <script src="assets/vendors/js/tui-time-picker.min.js"></script>
    <script src="assets/vendors/js/tui-date-picker.min.js"></script>
    <script src="assets/vendors/js/moment.min.js"></script>
    <script src="assets/vendors/js/chance.min.js"></script>

    <script src="assets/vendors/js/time-tracker.min.js"></script>
    <script src="assets/vendors/js/emojionearea.min.js"></script>

	<script src="assets/vendors/js/jquery.time-to.min.js "></script>	
	<script src="assets/vendors/js/jquery.calendar.min.js"></script>

    <script src="assets/vendors/js/dataTables.min.js"></script>
    <script src="assets/vendors/js/dataTables.bs5.min.js"></script>	
	<script src="assets/vendors/js/lslstrength.min.js"></script>
	
	<script src="assets/vendors/js/cleave.min.js"></script>
	
	<script src="assets/vendors/js/jquery.print.min.js"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="assets/js/common-init.min.js"></script>
    <script src="assets/js/dashboard-init.min.js"></script>
	
	<script src="assets/js/apps-email-init.min.js"></script>
	
	<!-- <script src="assets/js/analytics-init.min.js"></script>	 -->
	
	<script src="assets/js/apps-storage-init.min.js"></script>
	
	<script src="assets/js/apps-tasks-init.min.js"></script>
	
	<script src="assets/js/apps-calendar-init.min.js"></script>
	
	<script src="assets/js/apps-chat-init.min.js"></script>	
	
	<script src="assets/js/settings-init.min.js"></script>	

	<script src="assets/js/widgets-charts-init.min.js"></script>	

	<script src="assets/js/widgets-lists-init.min.js"></script>	

	<script src="assets/js/widgets-miscellaneous-init.min.js"></script>
	
	<script src="assets/js/widgets-statistics-init.min.js"></script>
    <!--! END: Apps Init !-->
    <!-- Sidebar submenu collapse fix (clears inline styles that can block collapse) -->
    <script src="assets/js/sidebar-fix.js"></script>

    <!--! BEGIN: Theme Customizer  !-->
    <script src="assets/js/theme-customizer-init.min.js"></script>

	<script src="assets/js/widgets-tables-init.min.js"></script>

	<script src="assets/js/proposal-create-init.min.js"></script>
	<script src="assets/js/proposal-view-init.min.js"></script>	
	
	<script src="assets/js/reports-leads-init.min.js"></script>
	<script src="assets/js/reports-project-init.min.js"></script>
	<script src="assets/js/reports-sales-init.min.js"></script>
	<script src="assets/js/reports-tmesheets-init.min.js"></script>
	
	<script src="assets/js/leads-view-init.min.js"></script>
	
	<script src="assets/js/leads-init.min.js"></script>
	
	<script src="assets/js/payment-init.min.js"></script>
	
	<script src="assets/js/customers-view-init.min.js"></script>
	
	<script src="assets/js/invoice-create-init.min.js"></script>
	
	<script src="assets/js/invoice-view-init.min.js"></script>
    <!--! END: Theme Customizer !-->
    
    <!-- Global Modal Z-Index Manager - Final Solution -->
    <style>
        /* Bootstrap'in backdrop'lerini sakla */
        .modal-backdrop {
            visibility: hidden !important;
            opacity: 0 !important;
            z-index: -99999 !important;
            pointer-events: none !important;
        }
        
        /* Bootstrap'in offcanvas backdrop'ini de sakla */
        .offcanvas-backdrop {
            visibility: hidden !important;
            opacity: 0 !important;
            z-index: -99999 !important;
            pointer-events: none !important;
        }
        
        /* Özel backdrop stili */
        body > .custom-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: auto;
            pointer-events: auto;
        }

        /* Select2 + Input Group alignment (global) */
        .input-group .select2-container { flex: 1 1 auto; width: 1% !important; }
        .select2-container .select2-selection--single { height: 38px; }
        .select2-container .select2-selection--single .select2-selection__rendered { line-height: 38px; }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 38px; }
    </style>
    
    <script>
        (function() {
            const BASE_Z = 1050;
            const STEP = 100;
            let activeModals = []; // Açık modallar listesi
            let activeOffcanvas = []; // Açık offcanvas'lar listesi
            let backdrops = {}; // Modal/Offcanvas ID -> Backdrop DOM node

            // MODAL events
            document.addEventListener('show.bs.modal', function(e) {
                const modal = e.target;
                const modalId = modal.id || 'modal-' + Date.now() + Math.random();
                modal.id = modalId;

                // Debug log
                try { console.debug('[ZMANAGER] show.bs.modal for', modalId, 'activeModals(before)=', activeModals.slice()); } catch(err){}

                // Bootstrap backdrop'lerini sil
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());
                }, 0);

                if (!activeModals.includes(modalId)) {
                    activeModals.push(modalId);
                }

                // Backdrop oluştur
                const backdrop = document.createElement('div');
                backdrop.className = 'custom-backdrop';
                backdrop.setAttribute('data-for', modalId);
                document.body.insertBefore(backdrop, document.body.firstChild);
                backdrops[modalId] = backdrop;

                updateZIndexes();
                try { console.debug('[ZMANAGER] after show.bs.modal activeModals=', activeModals.slice(), 'backdrops=', Object.keys(backdrops)); } catch(err){}
            });

            document.addEventListener('hidden.bs.modal', function(e) {
                const modal = e.target;
                const modalId = modal.id;

                // Bootstrap backdrop'lerini sil
                document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());

                if (backdrops[modalId]) {
                    backdrops[modalId].remove();
                    delete backdrops[modalId];
                }

                activeModals = activeModals.filter(id => id !== modalId);
                updateZIndexes();
            });

            // debug hidden
            document.addEventListener('hidden.bs.modal', function(e) {
                try { console.debug('[ZMANAGER] hidden.bs.modal for', e.target.id, 'activeModals=', activeModals.slice()); } catch(err){}
            });

            // OFFCANVAS events
            document.addEventListener('show.bs.offcanvas', function(e) {
                const offcanvas = e.target;
                const offcanvasId = offcanvas.id || 'offcanvas-' + Date.now() + Math.random();
                offcanvas.id = offcanvasId;

                try { console.debug('[ZMANAGER] show.bs.offcanvas for', offcanvasId, 'activeOffcanvas(before)=', activeOffcanvas.slice()); } catch(err){}

                // Bootstrap backdrop'lerini sil
                setTimeout(() => {
                    document.querySelectorAll('.offcanvas-backdrop').forEach(bd => bd.remove());
                }, 0);

                if (!activeOffcanvas.includes(offcanvasId)) {
                    activeOffcanvas.push(offcanvasId);
                }

                // Backdrop oluştur
                const backdrop = document.createElement('div');
                backdrop.className = 'custom-backdrop';
                backdrop.setAttribute('data-for', offcanvasId);
                document.body.insertBefore(backdrop, document.body.firstChild);
                backdrops[offcanvasId] = backdrop;

                updateZIndexes();
                try { console.debug('[ZMANAGER] after show.bs.offcanvas activeOffcanvas=', activeOffcanvas.slice(), 'backdrops=', Object.keys(backdrops)); } catch(err){}
            });

            document.addEventListener('hidden.bs.offcanvas', function(e) {
                const offcanvas = e.target;
                const offcanvasId = offcanvas.id;

                // Bootstrap backdrop'lerini sil
                document.querySelectorAll('.offcanvas-backdrop').forEach(bd => bd.remove());

                if (backdrops[offcanvasId]) {
                    backdrops[offcanvasId].remove();
                    delete backdrops[offcanvasId];
                }

                activeOffcanvas = activeOffcanvas.filter(id => id !== offcanvasId);
                updateZIndexes();
            });

            document.addEventListener('hidden.bs.offcanvas', function(e) {
                try { console.debug('[ZMANAGER] hidden.bs.offcanvas for', e.target.id, 'activeOffcanvas=', activeOffcanvas.slice()); } catch(err){}
            });

            function updateZIndexes() {
                // Tüm açık modal ve offcanvas'ları al
                const allOpen = [
                    ...Array.from(document.querySelectorAll('.modal.show')),
                    ...Array.from(document.querySelectorAll('.offcanvas.show'))
                ];

                allOpen.forEach((element, index) => {
                    const backdropZ = BASE_Z + (STEP * index) - 1;
                    const elementZ = BASE_Z + (STEP * index);

                    element.style.zIndex = elementZ;

                    if (backdrops[element.id]) {
                        backdrops[element.id].style.zIndex = backdropZ;
                    }
                });
            }

            // DINAMIK MODAL/OFFCANVAS KAYDET FONKSIYONU
            // AJAX sonrası çağrılacak - yeni modal/offcanvas'ı z-index manager'a kaydeder
            window.registerNewModal = function(elementId) {
                try { console.debug('[ZMANAGER] registerNewModal called for', elementId); } catch(err){}
                const element = document.getElementById(elementId);
                if (!element) {
                    console.warn(`registerNewModal: Element with ID "${elementId}" not found`);
                    return;
                }

                // Modal mı, Offcanvas mı kontrol et
                const isModal = element.classList.contains('modal');
                const isOffcanvas = element.classList.contains('offcanvas');

                if (!isModal && !isOffcanvas) {
                    console.warn(`registerNewModal: Element "${elementId}" is neither modal nor offcanvas`);
                    return;
                }

                // Eğer element'in id'si yoksa ata (id zorunlu takip için)
                if (!element.id) {
                    element.id = elementId;
                }

                // Eğer modal ise hemen kayıt et ve bir backdrop oluştur (show'dan önce)
                if (isModal) {
                    if (!activeModals.includes(element.id)) {
                        activeModals.push(element.id);
                    }

                    if (!backdrops[element.id]) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'custom-backdrop';
                        backdrop.setAttribute('data-for', element.id);
                        document.body.insertBefore(backdrop, document.body.firstChild);
                        backdrops[element.id] = backdrop;
                    }

                    // Eğer zaten .show ise manuel olarak show event'ini tetikle (yedek)
                    if (element.classList.contains('show')) {
                        const event = new Event('show.bs.modal', { bubbles: true });
                        element.dispatchEvent(event);
                    }
                }

                // Offcanvas için benzer işlem
                if (isOffcanvas) {
                    if (!activeOffcanvas.includes(element.id)) {
                        activeOffcanvas.push(element.id);
                    }

                    if (!backdrops[element.id]) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'custom-backdrop';
                        backdrop.setAttribute('data-for', element.id);
                        document.body.insertBefore(backdrop, document.body.firstChild);
                        backdrops[element.id] = backdrop;
                    }

                    if (element.classList.contains('show')) {
                        const event = new Event('show.bs.offcanvas', { bubbles: true });
                        element.dispatchEvent(event);
                    }
                }

                // Güncelle
                updateZIndexes();
            };
        })();
    </script>
    
<?php echo (isset($script) ? $script   : '')?>