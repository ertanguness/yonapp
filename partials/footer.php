<!-- [ Mobile Bottom Navigation ] start -->
<style>
    /* Mobile - Bottom Navigation */
    .mobile-quick-actions {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid #e0e0e0;
        z-index: 1060;
        padding: 0;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    }

    .mobile-quick-actions-wrapper {
        display: flex;
        justify-content: space-around;
        align-items: center;
    }

    .mobile-quick-actions-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px;
        text-decoration: none;
        color: #555;
        font-size: 12px;
        flex: 1;
        transition: all 0.3s ease;
        border: none;
        background: none;
        cursor: pointer;
        position: relative;
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
        font-size: 11px;
        text-align: center;
        line-height: 1.2;
    }

    /* Dropdown Menu */
    .mobile-dropdown-menu {
        display: none;
        position: absolute;
        bottom: 65px;
        right: 0;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        z-index: 1001;
    }

    .mobile-dropdown-menu.active {
        display: block;
    }

    .mobile-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
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
        <!-- Ana Sayfa -->
        <a href="?p=home" class="mobile-quick-actions-item" title="Ana Sayfa">
            <i class="bi bi-house-fill"></i>
            <p>Ana Sayfa</p>
        </a>

        <!-- Site Seçimi -->
        <a href="company-list.php" class="mobile-quick-actions-item" title="Site Seçimi">
            <i class="bi bi-diagram-3"></i>
            <p>Site Seçimi</p>
        </a>

        <!-- Aidat Ödeme -->
        <a href="/yonetici-aidat-odeme" class="mobile-quick-actions-item" title="Aidat Ödeme">
            <i class="bi bi-credit-card"></i>
            <p>Aidat Öde</p>
        </a>

        <!-- Borçlandırma -->
        <a href="/borclandirma-yap" class="mobile-quick-actions-item" title="Borçlandırma">
            <i class="bi bi-clipboard-plus"></i>
            <p>Borçlandır</p>
        </a>

        <!-- Diğer (Dropdown) -->
        <button class="mobile-quick-actions-item" id="mobileMoreBtn" title="Diğer">
            <i class="bi bi-three-dots-vertical"></i>
            <p>Diğer</p>
        </button>
    </div>

    <!-- Dropdown Menu -->
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
        <a href="/raporlar" class="sms-gonder">
            <i class="bi bi-card-checklist"></i>
            <span>Raporlar</span>
        </a>
        <!-- bootstrap icons -->
    </div>
</div>

<script>
    // Mobile Dropdown Menu
    $(document).ready(function() {
        var dropdownOpen = false;

        // Diğer butonuna tıklama
        $('#mobileMoreBtn').click(function(e) {
            e.preventDefault();
            var $dropdown = $('#mobileDropdownMenu');
            
            if (dropdownOpen) {
                $dropdown.removeClass('active');
                dropdownOpen = false;
            } else {
                $dropdown.addClass('active');
                dropdownOpen = true;
            }
        });

        // Dropdown dışında tıklama
        $(document).click(function(e) {
            if (!$(e.target).closest('#mobileMoreBtn, #mobileDropdownMenu').length) {
                $('#mobileDropdownMenu').removeClass('active');
                dropdownOpen = false;
            }
        });

        // Dropdown menü item'lerine tıklama
        $('#mobileDropdownMenu a').click(function() {
            $('#mobileDropdownMenu').removeClass('active');
            dropdownOpen = false;
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
<!-- [ Footer ] end -->