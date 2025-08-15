
<?php




$page = isset($_GET['p']) ? $_GET['p'] : '';



if (
    $page == 'dues/dues-defines/list' ||
    $page == 'offers/list' ||
    $page == 'reports/list' ||
    $page == 'kullanici/list' ||
    $page == 'kullanici-gruplari/list' ||
    $page == 'persons/list' ||
    $page == 'persons/manage' ||

    $page == 'missions/list' || $page == 'missions/process/list' ||
    $page == 'missions/headers/manage' || $page == 'missions/headers/list' ||
    $page =="uye/list"  ||
    $page == 'dues/debit/detail' || $page == 'dues/payment/list' || $page == 'dues/debit/list' ||
    $page == "management/peoples/list" || $page == "management/peoples/manage" ||
    $page == 'management/blocks/list' || $page == 'management/apartment/list' ||
    $page == 'defines/apartment-type/list' ||
    $page == 'dues/payment/tahsilat-onay' ||
    $page == "finans-yonetimi/kasa/list" ||
    $page == 'management/sites/list' ||
    $page == "dues/collections/list" || $page == "dues/collections/detail" ||
    $page == 'repair/list' || $page == 'repair/care/list' ||   $page == 'repair/cost/list' 

) { ?>
     <!-- echo '<script src="./dist/libs/datatable/datatables.min.js"></script>'; -->
     <script src="assets/vendors/js/dataTables.min.js"></script>
     <script src="assets/vendors/js/dataTables.bs5.min.js"></script>	
     
     

<?php  } 

//*************USERS********************************* */
// Kullanıcı ekleme ve düzenleme sayfası
if ($page == 'users/list' || $page == 'users/manage') {
    echo '<script src="./src/users/users.js"></script>';
}

// Kullanıcı rolü ekleme ve düzenleme sayfası
if ($page == 'kullanici-gruplari/duzenle' || $page == 'kullanici-gruplari/yetkiler') {
    echo '<script src="pages/kullanici-gruplari/js/duzenle.js"></script>';
}

//Kullanıcı ekleme ve düzenleme sayfası
if ($page ==  'kullanici/duzenle') {
    echo '<script src="pages/kullanici/js/user.js"></script>';
}

//Role Yetkileri ekleme ve düzenleme sayfası
if ($page == 'users/auths/auths') {
    echo '<script src="./src/users/auths.js"></script>';
}
//*************USERS********************************* */

//*************DUES******************************** */
// Dues Tanımlama sayfası
if (
    $page == 'dues/dues-defines/manage' ||   $page == 'dues/dues-defines/list') {
        echo '<script src="pages/dues/dues-defines/dues.js"></script>';
    }

//*************DUES******************************** */

//*************SITES******************************** */
// Site Tanımlama sayfası
if (
    $page == 'management/sites/manage' ||   $page == 'management/sites/list') {
        echo '<script src="pages/management/sites/sites.js"></script>';
    }

//*************SITES******************************** */


//*************DEBIT******************************** */
// Debit Tanımlama sayfası
if (
    $page == 'dues/debit/manage' ||
    $page == 'dues/debit/list' ||
    $page == 'dues/debit/single-manage' 
) { ?>
    <script src="pages/dues/debit/debit.js"></script>

<?php }
//*************DEBIT******************************** */

//*************BLOCKS******************************** */
// Site Tanımlama sayfası
if ($page == 'management/blocks/manage' ||   
    $page == 'management/blocks/list' || 
    $page == 'management/sites/manage') {
        echo '<script src="pages/management/blocks/blocks.js"></script>';
    }
//*************BLOCKS******************************** */

//*************APARTMENT******************************** */
if (
    $page == 'management/apartment/manage' ||   $page == 'management/apartment/list') {
        echo '<script src="pages/management/apartment/apartment.js"></script>';
    }
// Apartment upload from excel
if ($page == 'management/apartment/upload-from-xls') {
    echo '<script src="pages/management/apartment/js/upload-from-xls.js"></script>';
}
   //*************APARTMENT******************************** */

   //*************PEOPLES BAŞLANGIÇ******************************** */
if (
    $page == 'management/peoples/manage' ||   $page == 'management/peoples/list') {
        echo '<script src="pages/management/peoples/js/kisiBilgileri.js"></script>';
        echo '<script src="pages/management/peoples/js/aracBilgileri.js"></script>';
        echo '<script src="pages/management/peoples/js/acilDurumKisiBilgileri.js"></script>';
    }


if ($page == 'management/peoples/upload-from-xls') {
    echo '<script src="pages/management/peoples/js/upload-from-xls.js"></script>';
}
   //*************PEOPLES BİTİŞ******************************** */

 //*************BAKIM ONARIM ARIZA BAŞLANGIÇ******************************** */
 if ($page == 'repair/manage' || $page == 'repair/list') {
    echo '<script src="pages/repair/bakim.js"></script>';
}
  //************PERİYODİK BAKIM ONARIM ARIZA BİTİŞ************************************ */
  if ($page == 'repair/care/manage' || $page == 'repair/care/list') {
    echo '<script src="pages/repair/care/periyodikBakim.js"></script>';
}
  //************PERİYODİK BAKIM ONARIM ARIZA BİTİŞ************************************ */

    //************MALİYET / FATURALANDIRMA BAŞLANGIÇ************************************ */
    if ($page == 'repair/cost/manage' || $page == 'repair/cost/list') {
        echo '<script src="pages/repair/cost/maliyet.js"></script>';
    }
      //************MALİYET / FATURALANDIRMA BİTİŞ************************************ */

  //*************Define APARTMENT TYPES******************************** */
// Daire Tipi Tanımlama sayfası

if (
    $page == 'defines/apartment-type/manage' ||   $page == 'defines/apartment-type/list') {
        echo '<script src="pages/defines/apartment-type/apartment-type.js"></script>';
    }
//*************define APARTMENT TYPES******************************** */


if ($page == 'finans-yonetimi/kasa/duzenle' || $page == 'finans-yonetimi/kasa/list') {
    echo '<script src="pages/finans-yonetimi/kasa/js/kasa.js"></script>';
}

//Payment upload from excel
if ($page == 'dues/payment/upload-from-xls') {
    echo '<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.mini.min.js"></script>';
    echo '<script src="pages/dues/payment/js/upload.js"></script>';

}
if ($page == 'dues/payment/tahsilat-onay') {
    echo '<script src="pages/dues/payment/js/tahsilat-onay.js" defer></script>';

}

if($page == 'dues/payment/list') {
    echo '<script src="pages/dues/payment/js/tahsilat-gir.js" defer></script>';
}


if ($page == 'home') {
    // //echo '<script src="./dist/libs/apexcharts/dist/apexcharts.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/js/jsvectormap.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world-merc.js" defer></script>';
    // echo '<script src="./src/charts.js" defer></script>';
    echo '<script src="assets/vendors/js/apexcharts.min.js"></script>';
    echo ' <script src="assets/js/dashboard-init.min.js"></script>';
}


if($page == "home")
{
   echo '<script src="assets/vendors/js/daterangepicker.min.js"></script>';
    echo '<script src="assets/vendors/js/circle-progress.min.js"></script>';
	echo '<script src="assets/vendors/js/jquery.time-to.min.js "></script>';
}


?>









<script src="./src/jquery.inputmask.js" defer></script>


<script src="./assets/js/flatpickr.min.js" ></script>
<script src="./assets/js/flatpickr.tr.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js" defer></script>
<script src="./assets/js/jquery.validate.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" defer></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js" defer></script>
<script src="./assets/js/select2/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script src="./src/app.js" defer></script>
<!--<< All JS Plugins >>-->
