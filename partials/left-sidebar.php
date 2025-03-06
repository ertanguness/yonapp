<?php

//Model sayfaya dahil edilir
require_once "Model/Menus.php";
require_once "Model/Auths.php";

//Modelden yeni bir nesne oluşturulur
$menus = new Menus();
$Auths = new Auths();

?>
<!--! ================================================================ !-->
<!--! [Start] Navigation Manu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.php" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="assets/images/yonapp-logo.svg" alt="" class="logo logo-lg" style="width: 80%; height: auto;" />
                <img src="assets/images/yonapp-logo-sm.svg" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Yönetim Paneli</label>
                </li>
                <ul class="nxl-navbar " id="side-menu">
                    <?php
                    // Aktif sayfa alınır
                    $active_page = $_GET['p'] ?? '';

                    // Menü isimleri Model altındaki Menus.php sayfası ile tablodan getirilir
                    $top_menus = $menus->getMenus();

                    // Gelen menü isimlerinde döngüye girilir
                    foreach ($top_menus as $menu) {
                        // Eğer menü yetkiye tabi ise yetki kontrolü yapılır
                        if ($menu->is_authorize == 1) {
                            // Sayfa Adından Auths tablosundaki title alanı ile sorgulanarak yetki id alınır
                            $auth_id = $Auths->getAuthIdByTitle($menu->page_name)->id;

                            // Yetki id'si gelen sayfa için yetki kontrolü yapılır
                            if (!$Auths->AuthorizeByAuthId($auth_id)) {
                                continue;
                            }
                        }

                        // Eğer aktif sayfa menü ismi ile aynı ise active classı eklenir
                        if ($active_page == $menu->page_link) {
                            $active = 'active';
                        } else {
                            $active = '';
                        }

                        // Menü altında başka menüler var mı kontrol edilir
                        $sub_menus = $menus->getSubMenusisMenu($menu->id);

                        // Menü altında başka menüler var ve menü olarak görünür ise 
                        // üst menü için aşağı açılan ok oluşturulur
                        if (count($sub_menus) > 0) {
                            $dropdown = 'has-arrow';
                        } else {
                            $dropdown = '';
                        }

                        // Menü altında başka menüler var mı kontrol edilir
                        // ve menü olarak görünür ise dropdown menü oluşturulur
                        $sub_menus = $menus->getSubMenus($menu->id);
                        $show = '';
                        $active_id = 0;
                        foreach ($sub_menus as $sub_menu) {
                            // Aktif sayfa döngüdeki sayfa ise show classı eklenir
                            if ($active_page == $sub_menu->page_link) {
                                $show = 'nxl-trigger';
                                $active = 'active';
                                $active_id = $menu->id;
                            } elseif ($sub_menu->parent_id != $active_id) {
                                $show = '';
                            }
                        }
                    ?>

                        <!-- Menü oluşturulur -->
                        <li class="nxl-item <?php echo $active . " " . $show; ?>">
                            <?php if (count($sub_menus) > 0) { ?>
                                <!-- Eğer alt menüler varsa, ana menüye link ekleme, sadece başlık olacak -->
                                <a href="javascript: void(0);" class="nxl-link <?php echo $dropdown; ?>">
                                    <span class="nxl-micon"><i class="<?php echo $menu->icon; ?>"></i></span>
                                    <span class="nxl-mtext"><?php echo $menu->page_name; ?></span>
                                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                                </a>

                                <!-- Menü altında başka menüler varsa dropdown menü oluşturulur -->
                                <ul class="nxl-submenu" aria-expanded="false">
                                    <?php foreach ($sub_menus as $sub_menu) {
                                        // Eğer menü yetkiye tabi ise yetki kontrolü yapılır
                                        if ($sub_menu->is_authorize == 1) {
                                            // Sayfa Adından Auths tablosundaki title alanı ile sorgulanarak yetki id alınır
                                            $auth_id = $Auths->getAuthIdByTitle($sub_menu->page_name)->id ?? 0;

                                            // Yetki id'si gelen sayfa için yetki kontrolü yapılır
                                            if (!$Auths->AuthorizeByAuthId($auth_id)) {
                                                continue;
                                            }
                                        }

                                        $active_link = $active_page == $sub_menu->page_link ? 'active-link' : '';
                                        // Menu altında göstermek istemiyorsak veritabanındaki isMenu alanı 0 yapılır
                                        if ($sub_menu->isMenu > 0) { ?>
                                            <li class="nxl-item"><a href="index.php?p=<?php echo $sub_menu->page_link; ?>" class="nxl-link <?php echo $active_link; ?>"><?php echo $sub_menu->page_name; ?></a></li>
                                    <?php }
                                    } ?>
                                </ul>
                            <?php } else { ?>
                                <!-- Eğer alt menü yoksa, ana menüye doğrudan link ekle -->
                                <a href="index.php?p=<?php echo $menu->page_link; ?>" class="nxl-link <?php echo $active; ?>">
                                    <span class="nxl-micon"><i class="<?php echo $menu->icon; ?>"></i></span>
                                    <span class="nxl-mtext"><?php echo $menu->page_name; ?></span>
                                </a>
                            <?php } ?>
                        </li>


                    <?php } ?>
                </ul>
            </ul>

        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End]  Navigation Manu !-->
<!--! ================================================================ !-->