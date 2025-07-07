<?php
// Gerekli sınıfları dahil et
use Model\MenuModel;

// Hata ayıklama için (isteğe bağlı, production'da kapatılmalı)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Modelden yeni bir nesne oluşturulur.
$menuModel = new MenuModel();

// 1. OTURUMDAN KULLANICI ID'SİNİ AL
if (!isset($_SESSION['user']->id)) {
    die("Hata: Oturum başlatılmamış veya kullanıcı ID bulunamadı.");
}
$userId = (int)$_SESSION['user']->id;

// 2. AKTİF SAYFA LİNKİNİ BELİRLE
$activePageLink = $_GET['p'] ?? '';

// 3. GÖRÜNÜR MENÜ AĞACINI ÇEK
$menuTree = $menuModel->getHierarchicalMenuForRole($userId);

// 4. YENİ: AKTİF MENÜ BİLGİLERİNİ ÇEK
// Bu, is_menu=0 olan sayfalar için bile doğru ana menüyü ve üstlerini bulur.
$activeMenuInfo = $menuModel->findActiveMenuInfoByLink($activePageLink);


/**
 * YENİLENMİŞ renderMenuItems FONKSİYONU
 * Menü öğelerini ekrana basmak için kullanılan özyinelemeli (recursive) fonksiyon.
 *
 * @param array $items Oluşturulacak menü öğeleri dizisi.
 * @param array|null $activeMenuInfo Aktif menü ve üstlerinin ID'lerini içeren dizi.
 */
function renderMenuItems(array $items, ?array $activeMenuInfo)
{
    foreach ($items as $item) {
        $hasChildren = !empty($item->children);
        
        // Aktiflik kontrolünü yeni yapıya göre yap
        $isActive = $activeMenuInfo && $item->id == $activeMenuInfo['active_id'];
        $isParentOfActive = $activeMenuInfo && in_array($item->id, $activeMenuInfo['ancestor_ids']);

        // CSS sınıflarını topla
        $liClasses = ['nxl-item'];
        if ($isActive || $isParentOfActive) {
            // Eğer kendisi aktifse VEYA aktif bir öğenin üst menüsü ise 'active' sınıfını ekle
            $liClasses[] = 'active';
        }
        if ($hasChildren && ($isActive || $isParentOfActive)) {
            // Eğer alt menüsü varsa VE aktif bir öğenin yolu üzerindeyse menüyü açık tut
            $liClasses[] = 'nxl-trigger';
        }

        echo '<li class="' . implode(' ', $liClasses) . '">';

        // Link yapısı aynı kalabilir
        if ($hasChildren) {
            echo '<a href="javascript:void(0);" class="nxl-link has-arrow">';
            echo '  <span class="nxl-micon"><i class="' . htmlspecialchars($item->icon) . '"></i></span>';
            echo '  <span class="nxl-mtext">' . htmlspecialchars($item->page_name) . '</span>';
            echo '  <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>';
            echo '</a>';
            echo '<ul class="nxl-submenu">';
            // Alt menüler için fonksiyonu tekrar çağır (recursion), aktiflik bilgisini de gönder
            renderMenuItems($item->children, $activeMenuInfo);
            echo '</ul>';
        } else {
            echo '<a href="index.php?p=' . htmlspecialchars($item->menu_link) . '" class="nxl-link">';
            echo '  <span class="nxl-micon"><i class="' . htmlspecialchars($item->icon) . '"></i></span>';
            echo '  <span class="nxl-mtext">' . htmlspecialchars($item->page_name) . '</span>';
            echo '</a>';
        }

        echo '</li>';
    }
}

?>
<style>
    .w-90 {
        width: 90px !important;
    }
</style>
<!--! ================================================================ !-->
<!--! [Start] Navigation Manu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header p-3 d-flex justify-content-center align-items-center">
            <!-- Logo... -->
            <img src="assets/images/logo/logo.svg" alt="" class="logo logo-lg w-90 "  />
            <img src="assets/images/logo/logo.svg" alt="" class="logo logo-sm" />
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar " id="side-menu">
                <?php
                if (!empty($menuTree)) {
                    foreach ($menuTree as $groupName => $items) {
                        echo '<li class="nxl-item nxl-caption"><label>' . htmlspecialchars($groupName) . '</label></li>';
                        
                        // YARDIMCI FONKSİYONU YENİ PARAMETRE İLE ÇAĞIR
                        renderMenuItems($items, $activeMenuInfo);
                    }
                } else {
                    echo '<li class="nxl-item"><span class="nxl-mtext px-3">Görüntülenecek menü bulunamadı.</span></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End]  Navigation Manu !-->
<!--! ================================================================ !-->