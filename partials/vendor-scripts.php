<?php




$page = isset($_GET['p']) ? $_GET['p'] : '';



if (
    $page == 'companies/list' ||
    $page == 'offers/list' ||
    $page == 'reports/list' ||
    $page == 'users/list' ||
    $page == 'users/roles/list' ||
    $page == 'persons/list' ||
    $page == 'persons/manage' ||

    $page == 'missions/list' || $page == 'missions/process/list' ||
    $page == 'missions/headers/manage' || $page == 'missions/headers/list' 
) {
    // echo '<script src="./dist/libs/datatable/datatables.min.js"></script>';
}

//*************USERS********************************* */
// Kullanıcı ekleme ve düzenleme sayfası
if ($page == 'users/list' || $page == 'users/manage') {
    echo '<script src="./src/users/users.js"></script>';
}

// Kullanıcı rolü ekleme ve düzenleme sayfası
if ($page == 'users/roles/list' || $page == 'users/roles/manage') {
    echo '<script src="./src/users/roles.js"></script>';
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
        echo '<script src="/pages/dues/dues-defines/dues.js"></script>';
    }

//*************DUES******************************** */

//*************SITES******************************** */
// Site Tanımlama sayfası
if (
    $page == 'management/sites/manage' ||   $page == 'management/sites/list') {
        echo '<script src="/pages/management/sites/sites.js"></script>';
    }

//*************SITES******************************** */


//*************DEBIT******************************** */
// Debit Tanımlama sayfası
if (
    $page == 'dues/debit/manage' ||
    $page == 'dues/debit/list'
) { ?>
    <script src="/pages/dues/debit/debit.js"></script>

<?php }
//*************DEBIT******************************** */

//*************BLOCKS******************************** */
// Site Tanımlama sayfası
if (
    $page == 'management/blocks/manage' ||   $page == 'management/blocks/list' || 'management/sites/manage') {
        echo '<script src="/pages/management/blocks/blocks.js"></script>';
    }

//*************BLOCKS******************************** */

//*************APARTMENT TYPES******************************** */
// Daire Tipi Tanımlama sayfası

if (
    $page == 'defines/apartment-type/manage' ||   $page == 'defines/apartment-type/list') {
        echo '<script src="/pages/defines/apartment-type/apartment-type.js"></script>';
    }
//*************APARTMENT TYPES******************************** */




if ($page == 'home') {
    // //echo '<script src="./dist/libs/apexcharts/dist/apexcharts.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/js/jsvectormap.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world-merc.js" defer></script>';
    // echo '<script src="./src/charts.js" defer></script>';


}
?>
<script src="./src/jquery.inputmask.js"></script>
<script src="./assets/js/flatpickr.min.js"></script>
<script src="./assets/js/flatpickr.tr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="./assets/js/select2/js/select2.min.js"></script>
<script src="./assets/js/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>




<script src="./src/app.js" defer??></script>