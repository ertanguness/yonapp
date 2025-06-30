<?php
// Gerekli sınıfları dahil et
use Model\MenuModel;

// Hata ayıklama için (isteğe bağlı, production'da kapatılmalı)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Modelden yeni bir nesne oluşturulur.
$menuModel = new MenuModel();

// 1. OTURUMDAN KULLANICI ID'SİNİ AL
// Bu satırı kendi session yapınıza göre düzenlemeniz GEREKİR.
if (!isset($_SESSION['user']->id)) {
    // Eğer kullanıcı girişi yoksa, menü oluşturulamaz.
    // Burada bir yönlendirme veya hata mesajı göstermek daha iyi olabilir.
    die("Hata: Oturum başlatılmamış veya kullanıcı ID bulunamadı.");
}
$userId = (int)$_SESSION['user']->id;

// 2. AKTİF SAYFAYI BELİRLE
$activePage = $_GET['p'] ?? '';

// 3. MODEL'DEN YETKİLENDİRİLMİŞ VE HİYERARŞİK MENÜYÜ ÇEK
// Bu tek fonksiyon çağrısı, yetki kontrolü, veritabanı sorguları ve cacheleme dahil her şeyi yapar.
// Dönen veri: ['Grup Adı' => [menu_item_1, menu_item_2], 'Diğer Grup' => [...]]
$menuTree = $menuModel->getHierarchicalMenuForRole($userId);


/**
 * Menü öğelerini ekrana basmak için kullanılan özyinelemeli (recursive) fonksiyon.
 * Bu fonksiyon, iç içe geçmiş menüleri (alt menüleri) doğru bir şekilde oluşturur.
 *
 * @param array $items  Oluşturulacak menü öğeleri dizisi.
 * @param string $activePage Mevcut aktif sayfanın linki.
 */
function renderMenuItems(array $items, string $activePage)
{
    foreach ($items as $item) {
        $hasChildren = !empty($item->children);
        $isActive = ($item->menu_link === $activePage);
        $isParentOfActive = false;

        // Eğer alt menülerden biri aktifse, bu üst menüyü de "açık" olarak işaretle
        if ($hasChildren) {
            foreach ($item->children as $child) {
                if ($child->menu_link === $activePage) {
                    $isParentOfActive = true;
                    break; // Aktif çocuğu bulduk, döngüden çıkabiliriz.
                }
            }
        }

        // CSS sınıflarını bir diziye toplayıp sonra birleştirmek daha temiz bir yoldur.
        $liClasses = ['nxl-item'];
        if ($isActive || $isParentOfActive) {
            $liClasses[] = 'active';
        }
        if ($isParentOfActive) {
            // Aktif bir alt menüsü varsa, menünün açık kalması için bu sınıf eklenir.
            $liClasses[] = 'nxl-trigger';
        }

        echo '<li class="' . implode(' ', $liClasses) . '">';

        // Alt menüsü olanlar için farklı, olmayanlar için farklı link yapısı
        if ($hasChildren) {
            echo '<a href="javascript:void(0);" class="nxl-link has-arrow">';
            echo '  <span class="nxl-micon"><i class="' . htmlspecialchars($item->icon) . '"></i></span>';
            echo '  <span class="nxl-mtext">' . htmlspecialchars($item->page_name) . '</span>';
            echo '  <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>';
            echo '</a>';
            echo '<ul class="nxl-submenu">';
            // Alt menüler için fonksiyonu tekrar çağır (recursion)
            renderMenuItems($item->children, $activePage);
            echo '</ul>';
        } else {
            // Alt menüsü yoksa, direkt link
            echo '<a href="index.php?p=' . htmlspecialchars($item->menu_link) . '" class="nxl-link">';
            echo '  <span class="nxl-micon"><i class="' . htmlspecialchars($item->icon) . '"></i></span>';
            echo '  <span class="nxl-mtext">' . htmlspecialchars($item->page_name) . '</span>';
            echo '</a>';
        }

        echo '</li>';
    }
}

?>
<!--! ================================================================ !-->
<!--! [Start] Navigation Manu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header p-3">
            <a href="index.php" class="b-brand text-center p-5">
                <!-- ========   change your logo hear   ============ -->
                <img src="assets/images/logo/logo.svg" alt="" class="logo logo-lg text-center" style="width: 60%; height: auto;" />
                <img src="assets/images/logo/logo.svg" alt="" style="width: 20%;" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar " id="side-menu">
                <?php
                // Modelden gelen gruplanmış menüleri döngüye al
                if (!empty($menuTree)) {
                    foreach ($menuTree as $groupName => $items) {
                        // Her grup için bir başlık oluştur (nxl-caption)
                        echo '<li class="nxl-item nxl-caption"><label>' . htmlspecialchars($groupName) . '</label></li>';

                        // Bu gruba ait menü öğelerini oluşturmak için yardımcı fonksiyonu çağır
                        renderMenuItems($items, $activePage);
                    }
                } else {
                    // Kullanıcının görebileceği hiç menü yoksa bir mesaj gösterilebilir
                    echo '<li class="nxl-item"><span class="nxl-mtext px-3">Görüntülenecek menü bulunamadı.user id :' .$userId .'</span></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End]  Navigation Manu !-->
<!--! ================================================================ !-->