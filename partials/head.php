<?php 
//$page = isset($_GET['p']) ? $_GET['p'] : '';

$page = isset($page) ? $page : 'ana-sayfa';

?>
<head>
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

    
    
    <?php 
// Sadece anasayfa için gerekli olan css dosyaları


    if(isset($page) && $page == "ana-sayfa"){
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
<!-- <link href="https://cdn.datatables.net/v/bs5/dt-2.3.4/r-3.0.7/datatables.min.css" rel="stylesheet" integrity="sha384-RaJlMsTv+nhuWA/3SQzc3dPVUOKfEb08YW4YZsaNK3UNFUhjvLkn/SwJEfKSavGD" crossorigin="anonymous"> -->
 
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

	<?php if($page == "bildirimler" || $page == "ana-sayfa") { ?>
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/tagify.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/tagify-data.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/css/quill.min.css">
            <?php } ?>                 

    <script src="/assets/js/jquery.3.7.1.min.js"></script>
    <script src="/assets/js/common-init.min.js"></script>
    

</head>