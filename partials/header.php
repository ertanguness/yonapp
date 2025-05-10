    <?php
    $firm_id = isset($_SESSION['firm_id']) ? $_SESSION['firm_id'] : 0;

    require_once "App/Helper/company.php";

    $company = new CompanyHelper();

    // Mevcut URL'yi al
    $current_url = $_SERVER['REQUEST_URI'];

    // URL'yi parse et
    $url_parts = parse_url($current_url);

    // Query string'i al ve parse et
    parse_str($url_parts['query'] ?? '', $query_params);

    // theme parametresini kontrol et ve değiştir
    if (isset($query_params['theme']) && $query_params['theme'] === 'dark') {
        $query_params['theme'] = 'light';
    } else {
        $query_params['theme'] = 'dark';
    }


    // Yeni query string oluştur
    $new_query_string = http_build_query($query_params);

    // Yeni URL oluştur
    $new_url = $url_parts['path'] . '?' . $new_query_string;

    ?>
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [Start] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                        <i class="feather-align-left"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
            </div>
            <!--! [End] Header Left !-->
            <div class="header-center d-flex mx-auto text-center">
                <div class="d-flex align-items-center">
                    <div class="col-6 d-flex align-items-center justify-content-center">
                        <label for="incexp_type" class="fw-semibold text-center text-dark mb-0" style="font-size:medium; line-height: 1.5; height: 38px; display: flex; align-items: center;">
                            İşlem Yaptığınız Site: &nbsp;&nbsp;&nbsp;
                        </label>
                    </div>
                    <div class="col-6 d-flex justify-content-center align-items-center">
                        <div class="input-group flex-nowrap w-100">
                            <div class="input-group-text"><i class="feather-globe"></i></div>
                            <?php
                            // Sayfa adını kontrol et
                            if (basename($_SERVER['PHP_SELF']) != 'company-list.php') {
                                echo $company->myCompanySelect("myFirm", $firm_id);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">

                    <div class="dropdown nxl-h-item nxl-header-search">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="feather-search"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-search-dropdown">
                            <div class="input-group search-form">
                                <span class="input-group-text">
                                    <i class="feather-search fs-6 text-muted"></i>
                                </span>
                                <input type="text" class="form-control search-input-field" placeholder="Search...." />
                                <span class="input-group-text">
                                    <button type="button" class="btn-close"></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>

                    <div class="dropdown nxl-h-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            <img src="assets/images/avatar/1.png" alt="user-image" class="img-fluid user-avtar me-0" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/avatar/1.png" alt="user-image" class="img-fluid user-avtar" />
                                    <div>
                                        <h6 class="text-dark mb-0"><?php echo $_SESSION["user"]->full_name; ?> <span class="badge bg-soft-success text-success ms-1">PRO</span></h6>
                                        <span class="fs-12 fw-medium text-muted"><?php echo $_SESSION["user"]->email; ?></span>
                                    </div>
                                </div>
                            </div>

                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-user"></i>
                                <span>Profil</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-lock"></i>
                                <span>Kilit Ekranı</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="feather-log-out"></i>
                                <span>Çıkış</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->