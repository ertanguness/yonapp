<?php 
//$page = isset($_GET['p']) ? $_GET['p'] : '';

$page = isset($page) ? $page : 'ana-sayfa';

?>
<head>
    <script>
        (function() {
            try {
                var appSkin = localStorage.getItem('app-skin');
                if (appSkin) {
                    document.documentElement.classList.add(appSkin);
                }
                
                var appNavigation = localStorage.getItem('app-navigation');
                if (appNavigation) {
                    document.documentElement.classList.add(appNavigation);
                }
                
                var appHeader = localStorage.getItem('app-header');
                if (appHeader) {
                    document.documentElement.classList.add(appHeader);
                }
                
                var fontFamily = localStorage.getItem('font-family');
                if (fontFamily) {
                    document.documentElement.classList.add(fontFamily);
                }
            } catch (e) {}
        })();
    </script>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="maryinpris" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>YonApp / Site Yönetim</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/logo/favicon.svg" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css" />
    <!--! END: Bootstrap CSS-->
  
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/select2-theme.min.css">
    
    
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/vendors.min.css" />
    <!-- Development version -->

    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <?php 
// Sadece anasayfa için gerekli olan css dosyaları


    if(isset($page) && ($page == "ana-sayfa" || $page == "takvim" || $page == "notlar")){
    ?>
      <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/daterangepicker.min.css" />
	
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/jquery-jvectormap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/jquery.time-to.min.css">	
	
    <!-- <link rel="stylesheet" type="text/css" href="/assets/vendors/css/tagify.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/tagify-data.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/quill.min.css"> -->

    <link type="text/css" rel="stylesheet" href="/assets/vendors/css/tui-calendar.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/vendors/css/tui-theme.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/vendors/css/tui-time-picker.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/vendors/css/tui-date-picker.min.css">

	<link type="text/css" rel="stylesheet" href="/assets/vendors/css/emojionearea.min.css">	

	<link rel="stylesheet" type="text/css" href="/assets/vendors/css/jquery.time-to.min.css">
	<?php } ?>
    
    
    
    <link rel="stylesheet" type="text/css" href="/assets/css/theme.min.css" />
    
	<link rel="stylesheet" type="text/css" href="/assets/vendors/css/dataTables.bs5.min.css">	
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
 
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/style.css" />
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/flatpickr.min.css" />

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
    <?php echo (isset($css) ? $css   : '')?>

	<?php if($page == "bildirimler" || $page == "ana-sayfa" || $page == "duyuru-ekle" || $page == "duyuru-duzenle") { ?>
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/tagify.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/tagify-data.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/quill.min.css">
            <?php } ?>                 

    <script src="/assets/js/jquery.3.7.1.min.js"></script>
    <script src="/assets/js/common-init.min.js"></script>
    <link rel="manifest" href="/manifest.json" />
    

</head>
