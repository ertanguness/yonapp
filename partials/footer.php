<?php 
use App\Services\Gate;
?>


<!-- [ Mobile Bottom Navigation ] start -->


<style>
    /* Mobile - Bottom Navigation */
    .mobile-quick-actions {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        z-index: 1060;
        padding: 6px 8px 10px 8px;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 -6px 20px rgba(0,0,0,0.12);
        border-top: 1px solid #eaeaea;
        backdrop-filter: saturate(180%) blur(6px);
    }

    .mobile-quick-actions-wrapper {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 56px minmax(0, 1fr);
        align-items: center;
        gap: 8px;
        padding: 12px 12px 10px 12px;
        min-height: 64px;
        position: relative;
    }

    .mobile-actions-left,
    .mobile-actions-right {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        align-items: center;
    }

    .mobile-actions-spacer { width: 56px; height: 1px; }

    .mobile-quick-actions-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        padding: 10px 6px;
        text-decoration: none;
        color: #555;
        font-size: 12px;
        flex: 0 0 auto;
        transition: all 0.3s ease;
        border: none;
        background: none;
        cursor: pointer;
        position: relative;
        min-width: 0;
        width: 100%;
    }

    .mobile-quick-actions-item.active {
        color: #667eea;
        background: #f1f4ff;
        border-radius: 8px;
    }

    .mobile-quick-actions-item.active::after {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 26px;
        height: 3px;
        background: #667eea;
        border-radius: 2px;
    }

    .mobile-quick-actions-item:hover {
        color: #667eea;
        background: #f8f9fa;
    }

    .mobile-quick-actions-item i {
        font-size: 24px;
        margin-bottom: 4px;
    }

    .mobile-quick-actions-item p {
        margin: 0;
        font-size: 12px;
        text-align: center;
        line-height: 1.2;
    }

    /* Dropdown Menu */
    .mobile-dropdown-menu {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 72px;
        background: #ffffff;
        border-top: 1px solid #e0e0e0;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 -10px 20px rgba(0, 0, 0, 0.18);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        z-index: 1001;
        opacity: 0;
        visibility: hidden;
        transform: translateY(16px);
        transition: transform 0.25s ease, opacity 0.25s ease;
        pointer-events: none;
        height: auto;
    }

    .mobile-dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }

    .mobile-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 22px 16px;
        color: #555;
        text-decoration: none;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .mobile-dropdown-menu a:last-child {
        border-bottom: none;
    }

    .mobile-dropdown-menu a:hover {
        background: #f8f9fa;
        color: #667eea;
    }

    .mobile-dropdown-menu i {
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .mobile-fab {
        position: absolute;
        top: -28px;
        left: 50%;
        transform: translateX(-50%);
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8a5cff, #667eea);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.35);
        border: none;
        cursor: pointer;
        z-index: 1002;
    }
    .mobile-fab-sakin{
        background: linear-gradient(135deg, #34c38f, #28a745);
    }

    .mobile-fab-spacer {
        flex: 0 0 64px;
        height: 1px;
    }

    .mobile-fab i {
        font-size: 24px;
        margin: 0;
    }

    .mobile-gesture-bar {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        border-radius: 4px;
        background: #d7d7dc;
        opacity: 0.9;
    }

    html.app-skin-dark .mobile-quick-actions {
        background: #1f2231;
        border-color: #2a2e40;
        box-shadow: 0 10px 25px rgba(0,0,0,0.35);
    }

    html.app-skin-dark .mobile-dropdown-menu {
        background: #1f2231;
        border-color: #2a2e40;
        box-shadow: 0 -10px 20px rgba(0,0,0,0.6);
    }

    html.app-skin-dark .mobile-quick-actions-item {
        color: #d0d3de;
    }

    html.app-skin-dark .mobile-quick-actions-item.active {
        color: #b7c2ff;
        background: rgba(102, 126, 234, 0.12);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .mobile-quick-actions {
            display: flex;
        }

        body {
            padding-bottom: 80px;
        }

        footer {
            display: none !important;
           
        }
    
    }
</style>

<!-- Mobile Hızlı İşlemler Bottom Navigation -->
<div class="mobile-quick-actions">
    <div class="mobile-quick-actions-wrapper">
        
        
        <div class="mobile-actions-left">
            <a href="/ana-sayfa" class="mobile-quick-actions-item" title="Ana Sayfa">
                <i class="bi bi-house-fill"></i>
                <p>Ana Sayfa</p>
            </a>
            <?php if(!Gate::isResident()){ ?>
            <a href="company-list.php" class="mobile-quick-actions-item" title="Site Seçimi">
                <i class="bi bi-diagram-3"></i>
                <p>Site Seçimi</p>
            </a>
            <?php }else{ ?>
            <a href="/sakin/finans" class="mobile-quick-actions-item" title="Aidat Ödeme">
                <i class="bi bi-wallet"></i>
                <p>Fins.İşl.</p>
            </a>
           <?php } ?>
        </div>

        <div class="mobile-actions-spacer" aria-hidden="true"></div>
        
        <?php if(!Gate::isResident()){ ?>
            <button class="mobile-fab" id="mobileMoreBtn" title="Daha Fazla">
                <i class="bi bi-list"></i>
            </button>
        <?php } else { ?>
            <button class="mobile-fab mobile-fab-sakin" id="mobileMoreBtnSakin" title="Daha Fazla">
                <i class="bi bi-list"></i>
            </button>
        <?php } ?>

        <div class="mobile-actions-right">
            <?php if(!Gate::isResident()){ ?>
            <a href="/yonetici-aidat-odeme" class="mobile-quick-actions-item" title="Aidat Ödeme">
                <i class="bi bi-credit-card"></i>
                <p>Aidat Öde</p>
            </a>
            <a href="/borclandirma-yap" class="mobile-quick-actions-item" title="Borçlandırma">
                <i class="bi bi-clipboard-plus"></i>
                <p>Borçlandır</p>
            </a>
            <?php } else { ?>
    
            <a href="/sakin/taleplerim" class="mobile-quick-actions-item" title="Taleplerim">
                <i class="bi bi-wallet"></i>
                <p>Talepler.</p>
            </a>
            <a href="/sakin/daire" class="mobile-quick-actions-item" title="Daireler">
                <i class="bi bi-clipboard-plus"></i>
                <p>Daireler</p>
            </a>
            <?php } ?>
        </div>

        <div class="mobile-gesture-bar"></div>
    </div>

    <div class="mobile-dropdown-backdrop" id="mobileDropdownBackdrop"></div>
    <?php if(!Gate::isResident()){ ?>
    <div class="mobile-dropdown-menu" id="mobileDropdownMenu">
        <a href="site-ekle">
            <i class="bi bi-plus-circle"></i>
            <span>Site Ekle</span>
        </a>
        <a href="blok-ekle">
            <i class="bi bi-building"></i>
            <span>Blok Ekle</span>
        </a>
        <a href="daire-ekle">
            <i class="bi bi-textarea"></i>
            <span>Daire Ekle</span>
        </a>
        <a href="/site-sakini-ekle">
            <i class="feather-user-plus"></i>
            <span>Kişi Ekle</span>
        </a>
        <a href="#" class="gelir-ekle">
            <i class="bi bi-credit-card"></i>
            <span>Gelir Ekle</span>
        </a>
        <a href="#" class="gider-ekle">
            <i class="bi bi-credit-card-2-back"></i>
            <span>Gider Ekle</span>
        </a>
        <a href="/gelir-gider-islemleri">
            <i class="bi bi-wallet2"></i>
            <span>Finansal İşlemler</span>
        </a>
        <a href="/aidat-turu-tanimlama">
            <i class="bi bi-folder-plus"></i>
            <span>Aidat Tanımla</span>
        </a>
        <a href="#" class="mail-gonder">
            <i class="bi bi-envelope"></i>
            <span>Email Gönder</span>
        </a>
        <a href="javascript:void(0);" class="sms-gonder">
            <i class="bi bi-send-plus"></i>
            <span>SMS Gönder</span>
        </a>
        <a href="/raporlar">
            <i class="bi bi-card-checklist"></i>
            <span>Raporlar</span>
        </a>
        <a href="/logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Çıkış Yap</span>
        </a>
    </div>
    <?php } else { ?>
    <div class="mobile-dropdown-menu" id="mobileDropdownMenuSakin">
        <a href="#">
            <i class="bi bi-people-fill"></i>
            <span>Ziyaretçi Kaydı</span>
        </a>
        <a href="/sakin/talep-ekle">
            <i class="bi bi-person-vcard"></i>
            <span>Talep Ekle</span>
        </a>
        <a href="/logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Çıkış Yap</span>
        </a>
    </div>
    <?php } ?>
</div>


<script>
    $(document).ready(function() {
        var dropdownOpenMenu = null;
        function positionMenu($menu){
            var navH = $('.mobile-quick-actions').outerHeight();
            var avail = Math.max(160, window.innerHeight - navH - 12);
            $menu.css({ bottom: navH + 'px', maxHeight: avail + 'px', height: 'auto' });
        }
        function openMenu($menu){
            positionMenu($menu);
            $menu.addClass('active');
            $('#mobileDropdownBackdrop').show();
            dropdownOpenMenu = $menu.attr('id');
        }
        function closeMenu(){
            $('.mobile-dropdown-menu').removeClass('active');
            $('#mobileDropdownBackdrop').hide();
            dropdownOpenMenu = null;
        }
        $('#mobileMoreBtn, #mobileMoreBtnSakin').on('click', function(e){
            e.preventDefault();
            var targetMenu = $(this).is('#mobileMoreBtnSakin') ? '#mobileDropdownMenuSakin' : '#mobileDropdownMenu';
            var $menu = $(targetMenu);
            if ($menu.hasClass('active')) { closeMenu(); } else { closeMenu(); openMenu($menu); }
        });
        $(document).on('click', function(e){
            if (!$(e.target).closest('#mobileMoreBtn, #mobileMoreBtnSakin, #mobileDropdownMenu, #mobileDropdownMenuSakin').length) {
                closeMenu();
            }
        });
        $('.mobile-dropdown-menu a').on('click', function(){
            closeMenu();
        });
        $(window).on('resize', function(){
            var $active = $('.mobile-dropdown-menu.active');
            if($active.length){ positionMenu($active); }
        });
        var currentPath = window.location.pathname.replace(/\/+$/, '');
        $(".mobile-quick-actions a.mobile-quick-actions-item").each(function() {
            var href = $(this).attr('href');
            if (!href || href === '#' || href.indexOf('javascript') === 0) return;
            var hrefPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '');
            if (currentPath === hrefPath || (hrefPath !== '/' && currentPath.indexOf(hrefPath) === 0)) {
                $(".mobile-quick-actions a.mobile-quick-actions-item").removeClass('active');
                $(this).addClass('active');
                return false;
            }
        });
    });
</script>
<!-- [ Mobile Bottom Navigation ] end -->

<!-- [ Footer ] start -->
<footer class="footer" style="position: fixed; bottom: 0; width: 100%; z-index: 1000;">
    <div class="d-flex w-100 justify-content-center text-center">
        <span class="fs-11 text-muted fw-medium text-uppercase mb-0">
            YonApp - Apartman & Site Yönetim Sistemi&nbsp;&nbsp; / &nbsp;&nbsp;Copyright ©
            <script>
                document.write(new Date().getFullYear());
            </script>
        </span>
    </div>
</footer>
