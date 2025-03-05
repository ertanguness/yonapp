<?php
session_start();

$user_id = $_SESSION['user']->id;
$email = $_SESSION['user']->email;


// require_once "Model/Company.php";
require_once "Model/MyFirmModel.php";
// $companyObj = new Company();
$myFirmObj = new MyFirmModel();

// $myCompanies = $companyObj->getMyCompanies($user_id);
//$myFirms = $myFirmObj->getAuthorizedMyFirmsByEmail($email);

$myFirms = $myFirmObj->getMyFirmByUserId();
if(count($myFirms) == 1){
    $_SESSION['firm_id'] = $myFirms[0]->id;
    header('Location: /index.php?p=home');
    exit();
}

?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Apartman/Site Listesi | YonApp - Apartman/Site Takip Sistemi
    </title>
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css?1692870487" rel="stylesheet" />
    <link href="./dist/css/demo.min.css?1692870487" rel="stylesheet" />
    <link href="./dist/css/style.css?1692870487" rel="stylesheet" />
  <link rel="icon" href="./static/favicon.ico" type="image/x-icon" />

    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .list-item {
            cursor: pointer;
        }

        .list-item:hover {
            background-color: rgba(var(--tblr-secondary-rgb), .08);
        }
    </style>
</head>

<body>

    <?php
    if ($_POST && isset($_POST['firm_id'])) {
        $firm_id = $_POST['firm_id'];
        $_SESSION['firm_id'] = $firm_id;



        // returnUrl parametresini kontrol edin ve varsayılan değeri ayarlayın
        $redirectUri = isset($_GET['returnUrl']) && !empty($_GET['returnUrl']) ? $_GET['returnUrl'] : '/index.php?p=home';
        header('Location: ' . $redirectUri);
        exit();

    }

    ?>
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page">
        <!-- Navbar -->

        <?php include_once "inc/topbar.php" ?>

        <div class="page-wrapper">
            <!-- Page header -->
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">

                        <div class="col text-center">
                            <h1 class="text-muted">
                                Firma Seçiniz
                            </h1>
                        </div>
                        <!-- Page title actions -->

                    </div>
                </div>
            </div>
            <style>
                .img-fluid {
                    max-width: 400px;
                    height: auto;
                }
            </style>
            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="subheader">Last checked at</div>
                                    <div class="h3 m-0">27 seconds ago</div>
                                </div>
                            </div>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="subheader">Last checked at</div>
                                    <div class="h3 m-0">27 seconds ago</div>
                                </div>
                            </div>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="subheader">Last checked at</div>
                                    <div class="h3 m-0">27 seconds ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row row-cards">
                                <div class="space-y ">

                                    <?php

                                    foreach ($myFirms as $myfirm) { 
                                        
                                        
                                        ?>
                                        <form action="#" method="post">


                                            <div class="card list-item" data-id="<?php echo $myfirm->id ?>">
                                                <div class="row g-0">
                                                    <div class="col-auto">
                                                        <div class="card-body">
                                                            <div class="avatar avatar-md"
                                                                style="background-image: url(./static/jobs/job-1.jpg)">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col">
                                                        <div class="card-body ps-0">
                                                            <div class="row">
                                                                <div class="col">
                                                                    <input type="text" class="d-none" name="firm_id"
                                                                        value="<?php echo $myfirm->id ?>">
                                                                    <h3 class="mb-0">
                                                                        <a><?php echo $myfirm->firm_name; ?></a>
                                                                    </h3>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md">
                                                                    <div
                                                                        class="mt-3 list-inline list-inline-dots mb-0 text-secondary d-sm-block d-none">
                                                                        <div class="list-inline-item">
                                                                            <!-- Download SVG icon from http://tabler-icons.io/i/building-community -->
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                width="24" height="24" viewBox="0 0 24 24"
                                                                                fill="none" stroke="currentColor"
                                                                                stroke-width="2" stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                class="icon icon-inline">
                                                                                <path stroke="none" d="M0 0h24v24H0z"
                                                                                    fill="none"></path>
                                                                                <path
                                                                                    d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8">
                                                                                </path>
                                                                                <path d="M13 7l0 .01"></path>
                                                                                <path d="M17 7l0 .01"></path>
                                                                                <path d="M17 11l0 .01"></path>
                                                                                <path d="M17 15l0 .01"></path>
                                                                            </svg>
                                                                        </div>
                                                                        <div class="list-inline-item">
                                                                            <!-- Download SVG icon from http://tabler-icons.io/i/license -->
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                width="24" height="24" viewBox="0 0 24 24"
                                                                                fill="none" stroke="currentColor"
                                                                                stroke-width="2" stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                class="icon icon-inline">
                                                                                <path stroke="none" d="M0 0h24v24H0z"
                                                                                    fill="none"></path>
                                                                                <path
                                                                                    d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11">
                                                                                </path>
                                                                                <path d="M9 7l4 0"></path>
                                                                                <path d="M9 11l4 0"></path>
                                                                            </svg>
                                                                            <!-- <?php //echo $myCompany->description; ?> -->
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                                <div class="col-md-auto">
                                                                    <div class="mt-3 badges">
                                                                        <a href="#"
                                                                            class="badge badge-outline border-success text-secondary fw-normal badge-pill">
                                                                            Aktif
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    <?php } ?>
                                    <div class="row justify-content-center">

                                        <img src="static/illustrations/loading.avif" alt="Your Image Description"
                                            class="img-fluid text-center">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3">
                            <div class="card">
                                <div class="ribbon bg-red">NEW</div>
                                <div class="card-body">
                                    <h3 class="card-title">Card with text ribbon</h3>
                                    <p class="text-secondary">Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                                        Architecto at consectetur culpa ducimus eum fuga fugiat, ipsa iusto, modi
                                        nostrum recusandae reiciendis saepe.</p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
                <div class="col-lg-auto ms-lg-auto">
                    <ul class="list-inline list-inline-dots mb-0">
                        <li class="list-inline-item"><a href="https://tabler.io/docs" target="_blank"
                                class="link-secondary" rel="noopener">Documentation</a></li>
                        <li class="list-inline-item"><a href="./license.html" class="link-secondary">License</a></li>
                        <li class="list-inline-item"><a href="https://github.com/tabler/tabler" target="_blank"
                                class="link-secondary" rel="noopener">Source code</a></li>
                        <li class="list-inline-item">
                            <a href="https://github.com/sponsors/codecalm" target="_blank" class="link-secondary"
                                rel="noopener">
                                <!-- Download SVG icon from http://tabler-icons.io/i/heart -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-pink icon-filled icon-inline"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
                                </svg>
                                Sponsor
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <ul class="list-inline list-inline-dots mb-0">
                        <li class="list-inline-item">
                            Copyright &copy; 2023
                            <a href="." class="link-secondary">Tabler</a>.
                            All rights reserved.
                        </li>
                        <li class="list-inline-item">
                            <a href="./changelog.html" class="link-secondary" rel="noopener">
                                v1.0.0-beta20
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="./dist/js/tabler.min.js?1692870487" defer></script>
    <script src="./dist/js/demo.min.js?1692870487" defer></script>
    <script src="./dist/js/jquery.3.7.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.list-item').each(function () {
                $(this).click(function () {
                    $(this).closest("form").submit();
                });
            });
        });
    </script>
</body>

</html>