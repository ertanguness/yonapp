


<?php


function versionedScript($relPath, $module = false)
{
    $fs = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/') . $relPath;
    $ver = file_exists($fs) ? filemtime($fs) : time();
    $type = $module ? ' type="module"' : '';
    echo '<script' . $type . ' src="' . $relPath . '?v=' . $ver . '"></script>' . PHP_EOL;
}

//$page = isset($_GET['p']) ? $_GET['p'] : '';

if (
    $page == 'dues/dues-defines/list' ||
    $page == 'offers/list' ||
    $page == 'reports/list' ||
    $page == 'kullanici/list' ||
    $page == 'kullanici-gruplari' ||
    $page == 'bakim-ariza-takip' || $page == 'periyodik-bakim' ||  $page == 'maliyet-faturalandirma' ||
    $page == 'missions/list' || $page == 'missions/process/list' ||
    $page == 'missions/headers/manage' || $page == 'missions/headers/list' ||
    $page == "uye/list"  ||
    $page == 'dues/debit/detail' || $page == 'dues/payment/list' || $page == 'dues/debit/list' ||
    $page == 'daire-turu-listesi' ||
    $page == 'dues/payment/tahsilat-onay' ||
    $page == "finans-yonetimi/kasa/list" ||
    $page == 'siteler' ||
    $page == "dues/collections/list" || $page == "dues/collections/detail" ||
    $page == "aidat-turu-listesi" ||
    $page == "yonetici-aidat-odeme"  || $page == "tahsilatlar"  ||  $page == "borclandirma" ||
    $page == "eslesen-odemeler" || $page ==  "eslesmeyen-odemeler" ||
    $page == "kasa-hareketleri"  || $page == "kasa-listesi" ||
    $page == "gelir-gider-islemleri" ||
    $page == "borclandirma-detayi" || $page == "tahsilat-detayi" ||
    $page == "daire-ekle" || $page == "daire-duzenle" ||
    $page == "siteler" || $page == "site-bloklari" || $page == "site-daireleri" || $page == "site-sakinleri" || $page == "site-araclari" || $page == "arac-yonetimi" ||
    $page == "site-sakini-ekle" || $page == "site-sakini-duzenle"  ||
    $page == "kullanici-gruplari" || $page == "kullanici-listesi" ||
    $page == "onay-bekleyen-tahsilatlar" ||
    $page == "icralarim" || $page == "icra-detay" || $page == "icra-listesi" ||  $page == "icra-takibi" ||
    $page == "dues/collections/list" || $page == "dues/collections/detail" ||
    $page == "ziyaretci-listesi" ||    $page == "ziyaretci-ekle" ||    $page == "ziyaretci-duzenle" ||
    $page == "guvenlik" ||  $page == "guvenlik-gorev-yerleri"  ||
    $page == "vardiya-listesi" ||
    $page == "personel-listesi" ||
    $page == "bildirimler" || $page == "banka-hesap-hareketleri"  ||
    $page == "daire-tipi-listesi" || 
    $page == "gelir-gider-tipi-listesi" || $page == "gelir-gider-tipi-ekle" || $page == "gelir-gider-tipi-duzenle" ||
    $page == "aidat-turu-tanimlama" || $page== "aidat-turu-duzenle" ||   $page == "borclandirma-yap" ||
    $page == "personeller" || $page == "personel-ekle" || $page == "personel-duzenle" ||
    $page == "isletme-projesi" || $page == "isletme-projesi-ekle" || $page == "isletme-projesi-duzenle" ||
    $page == "bildirimler" ||
    $page == "acil-durum-kisileri" || $page == "sikayet-oneri-listesi" || $page == "duyuru-listesi" ||
    $page == "sakin/taleplerim" || $page == "talep-duzenle" ||
    $page == "anket-listesi" || $page == "duyuru-listesi" ||
    $page == "arac-yonetimi" ||
    $page == "kullanici-ekle" || $page == "kullanici-duzenle" ||
    $page == "sakin/duyurular" || $page == "toplu-sms" ||
    $page == "sakin/anket-listesi"  || $page == "program-giris-bilgileri" ||
    $page == "gecikmis-odemeler" ||
    $page == "superadmin-anasayfa" || $page == "superadmin-panel" || $page == "superadmin-temsilciler" || $page == "superadmin" || 
    $page == "superadmin-yetki-gruplari" || $page == "superadmin-kullanicilar" || $page == "superadmin-kullanici-duzenle"
) { 
    ?>
    <!-- echo '<script src="./dist/libs/datatable/datatables.min.js"></script>'; -->
 
<!-- <script src="https://cdn.datatables.net/v/bs5/dt-2.3.4/r-3.0.7/datatables.min.js" integrity="sha384-O4V7rOTTcSRflQBTMk8URAYWhGGEMgmmLFrqu3e83FQtze3vmndvrH3GcRdrfXRu" crossorigin="anonymous"></script> -->
    <script src="/assets/vendors/js/dataTables.min.js"></script>
    <script src="/assets/vendors/js/dataTables.bs5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

        <script>
        (function(){
            function initDtIn(root){
                if (!root || !window.jQuery) return;
                $(root).find('.datatables').each(function(){
                    if (!$.fn.DataTable) return;
                    if ($.fn.DataTable.isDataTable(this)) return;
                    try {
                        if (typeof window.initDataTable === 'function') {
                            window.initDataTable(this, {
                                responsive: true,
                                order: [[1, 'desc']],
                                pageLength: 12,
                                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json" }
                            });
                        } else {
                            $(this).DataTable({
                                responsive: true,
                                order: [[1, 'desc']],
                                pageLength: 12,
                                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json" }
                            });
                        }
                    } catch(e){ console.warn('DataTables init error', e); }
                });
            }
            function initObserver(){
                var target = document.getElementById('siteDetailContent');
                if (!target) return;
                initDtIn(target);
                var observer = new MutationObserver(function(muts){
                    muts.forEach(function(m){
                        if (m.addedNodes && m.addedNodes.length) {
                            initDtIn(target);
                        }
                    });
                });
                observer.observe(target, { childList: true, subtree: true });
            }
            function onReady(){
                initObserver();
                var modalEl = document.getElementById('siteDetailModal');
                if (modalEl) {
                    modalEl.addEventListener('shown.bs.modal', function(){
                        initDtIn(document.getElementById('siteDetailContent'));
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', onReady);
            } else {
                onReady();
            }
        })();
    </script>


<?php  }

//*************USERS********************************* */
// Kullanıcı ekleme ve düzenleme sayfası
if ($page == 'users/list' || $page == 'users/manage') {
    echo '<script src="./src/users/users.js"></script>';
}



echo '<script src="/assets/vendors/js/tagify.min.js"></script>';
echo '<script src="/assets/vendors/js/quill.min.js"></script>';
//Kullanıcı Ekle
if ($page == 'kullanici-ekle' || $page == 'kullanici-duzenle' || $page == 'kullanici-listesi') {
    echo '<script src="/pages/kullanici/js/user.js"></script>';
}

// Kullanıcı rolü ekleme ve düzenleme sayfası
if ($page == 'kullanici-grubu-duzenle' || $page == 'kullanici-grubu-ekle' || $page == 'kullanici-gruplari') {
    echo '<script src="/pages/kullanici-gruplari/js/duzenle.js"></script>';
}

if ($page == 'yetki-yonetimi') {
    echo '<script src="/pages/kullanici-gruplari/js/yetkiler.js"></script>';
}

if($page == "superadmin-kullanici-ekle" || $page == "superadmin-kullanici-duzenle" || $page == "superadmin-kullanicilar"  ) {
    echo '<script src="/pages/panel/kullanicilar/js/kullanici.js?v='. filemtime("pages/panel/kullanicilar/js/kullanici.js").'"></script>';
}


if ($page == "daire-tipi-listesi" || $page == "daire-tipi-ekle" || $page == "daire-tipi-duzenle" ) {
    echo "<script src='/pages/defines/apartment-type/apartment-type.js'></script>";
}


//Role Yetkileri ekleme ve düzenleme sayfası
if ($page == 'users/auths/auths') {
    echo '<script src="./src/users/auths.js"></script>';
}
//*************USERS********************************* */

//*************DUES******************************** */
// Dues Tanımlama sayfası
if (
    $page == 'aidat-turu-tanimlama' ||   $page == 'aidat-turu-listesi' || $page == 'aidat-turu-duzenle'
) {
    echo '<script type="module" src="/pages/dues/dues-defines/dues.js"></script>';
}

//*************DUES******************************** */

//*************SITES******************************** */
// Site Tanımlama sayfası
if (
    $page == 'site-ekle' ||   $page == 'siteler' || $page == 'site-duzenle'
) {
    echo '<script src="/pages/management/sites/sites.js"></script>';
}

//*************SITES******************************** */


//*************DEBIT******************************** */
// Debit Tanımlama sayfası
if (
    $page == 'borclandirma-yap' ||
    $page == 'borclandirma' ||
    $page == 'borclandirma-duzenle'
) { ?>

    <?php versionedScript('/pages/dues/debit/js/debit.js', true); ?>

<?php }
if ($page == 'dues/debit/upload-from-xls') {
    echo '<script src="pages/dues/debit/js/upload-from-xls.js"></script>';
}
if ($page == 'dues/debit/detail') {
    echo '<script src="pages/dues/debit/js/detail.js"></script>';
}
//cho "<script>console.log('".$page."')</script>";
if ($page == 'excelden-odeme-yukle') {
    echo '<script src="/pages/dues/payment/js/upload.js"></script>';
}


if ($page == 'excelden-gelir-gider-yukle') {
    echo '<script src="/pages/finans-yonetimi/gelir-gider/upload/upload-from-xls.js"></script>';
}



/* Tanımlamalar /Gelir Gider Tipi Tanımlama */
if ($page == 'gelir-gider-tipi-listesi' ||   
    $page == 'gelir-gider-tipi-ekle' || 
    $page == 'gelir-gider-tipi-duzenle'
) {
    echo '<script src="/pages/defines/gelir-gider-tipi/js/gelir-gider-tipi.js?v=' . filemtime('pages/defines/gelir-gider-tipi/js/gelir-gider-tipi.js') . '"></script>';
}

//Tahsilat Detay Sayfası
if ($page == 'tahsilat-detayi') {
    echo '<script src="/pages/dues/collections/js/tahsilat.js"></script>';
}

//Tekil Borçlandırma Sayfası
if ($page == 'borclandirma-kisi-ekle' || $page == 'borclandirma-kisi-duzenle') {
    echo '<script src="/pages/dues/debit/js/single-manage.js"></script>';
}

//*************DEBIT******************************** */

//*************BLOCKS******************************** */
// Site Tanımlama sayfası
if (
    $page == 'blok-ekle' || $page == 'site-bloklari' ||  $page == 'blok-duzenle'
) {
    echo '<script src="/pages/management/blocks/blocks.js"></script>';
}
//*************BLOCKS******************************** */

//*************APARTMENT******************************** */
if (
    $page == 'daire-ekle' ||   $page == 'daire-duzenle'  ||   $page == 'site-daireleri'
) {
    echo '<script src="/pages/management/apartment/apartment.js"></script>';
}
// Apartment upload from excel
if ($page == 'excelden-daire-yukle') {
    echo '<script src="pages/management/apartment/js/upload-from-xls.js"></script>';
}
//*************APARTMENT******************************** */

//*************PEOPLES BAŞLANGIÇ******************************** */
if ($page == 'site-sakinleri' || $page == 'site-sakini-ekle' || $page == 'site-sakini-duzenle') {
    echo '<script src="/pages/management/peoples/js/kisiBilgileri.js?v=' . filemtime('pages/management/peoples/js/kisiBilgileri.js') . '"></script>';
    echo '<script src="/pages/management/peoples/js/aracBilgileri.js"></script>';
    echo '<script src="/pages/management/peoples/js/acilDurumKisiBilgileri.js"></script>';
    echo '<script src="/pages/management/peoples/js/kisiNotlar.js"></script>';
}

if ($page == 'arac-yonetimi' || $page == 'arac-ekle' || $page == 'arac-duzenle') {
    echo '<script src="/pages/arac-yonetimi/js/araclar.js"></script>';
}


if ($page == 'management/peoples/upload-from-xls') {
    echo '<script src="/pages/management/peoples/js/upload-from-xls.js"></script>';
}
if ($page == 'kisileri-yukle') {
    echo '<script src="/pages/management/peoples/js/upload-from-xls.js"></script>';
}
//*************PEOPLES BİTİŞ******************************** */

//*************BAKIM ONARIM ARIZA BAŞLANGIÇ******************************** */
if ($page == 'bakim-ariza-ekle' || $page == 'bakim-ariza-duzenle' || $page == 'bakim-ariza-takip') {
    echo '<script src="/pages/repair/bakim.js"></script>';
}
if ($page == 'periyodik-bakim-ekle' || $page == 'periyodik-bakim-duzenle' || $page == 'periyodik-bakim') {
    echo '<script src="/pages/repair/care/periyodikBakim.js"></script>';
}
if ($page == 'maliyet-fatura-ekle' || $page == 'maliyet-fatura-duzenle' || $page == 'maliyet-faturalandirma') {
    echo '<script src="/pages/repair/cost/maliyet.js"></script>';
}
//************PERİYODİK BAKIM ONARIM ARIZA BİTİŞ************************************ */


//*************ZİYARETÇİ BAŞLANGIÇ******************************** */
if ($page == 'ziyaretci-ekle' || $page == 'ziyaretci-duzenle' || $page == 'ziyaretci-listesi') {
    echo '<script src="/pages/ziyaretci/ziyaretci.js"></script>';
}
//************ ZİYARETÇİ BİTİŞ************************************ */

//*************GÜVENLİK BAŞLANGIÇ******************************** */
if ($page == 'guvenlik-yeni-gorev-ekle' || $page == 'guvenlik-gorev-duzenle' || $page == 'guvenlik') {
    echo '<script src="/pages/ziyaretci/guvenlik/guvenlik.js"></script>';
}
if ($page == 'guvenlik-gorev-yeri-ekle' || $page == 'guvenlik-gorev-yeri-duzenle' || $page == 'guvenlik-gorev-yerleri') {
    echo '<script src="/pages/ziyaretci/guvenlik/GorevYeri/gorevYeri.js"></script>';
}
if ($page == 'vardiya-ekle' || $page == 'vardiya-duzenle' || $page == 'vardiya-listesi') {
    echo '<script src="/pages/ziyaretci/guvenlik/Vardiya/vardiya.js"></script>';
}
if ($page == 'personel-ekle' || $page == 'personel-duzenle' || $page == 'personel-listesi') {
    echo '<script src="/pages/ziyaretci/guvenlik/Personel/guvenlikPersonel.js"></script>';
}

//************ GÜVENLİK BİTİŞ************************************ */



//************* TANIMLAMALAR ******************************** */
// Daire Tipi Tanımlama sayfası
if (
    $page == 'daire-turu-ekle' || $page == 'daire-turu-duzenle' ||  $page == 'daire-turu-listesi'
) {
    echo '<script src="/pages/defines/apartment-type/apartment-type.js"></script>';
}
//Daireleri excelden yükleme
if ($page == 'daireleri-excelden-yukle') {
    echo '<script src="/pages/management/apartment/js/upload-from-xls.js"></script>';
}

//*************define APARTMENT TYPES******************************** */




//************İCRA BAŞLANGIÇ************************************ */
if ($page == 'icra-ekle' || $page == 'icra-duzenle' || $page == 'icra-listesi' || $page == 'icra-takibi') {
    echo '<script src="/pages/icra/icra.js"></script>';
}

if ($page == 'icra-detay') {
    echo '<script src="/pages/icra/detay/detay.js"></script>';
}
//************İCRA BİTİŞ************************************ */

if ($page == 'finans-yonetimi/kasa/duzenle' || $page == 'finans-yonetimi/kasa/list') {
    echo '<script src="pages/finans-yonetimi/kasa/js/kasa.js"></script>';
}

//************GELİR GİDER KAYDET************************************ */
if ($page == 'gelir-gider-islemleri') {
    echo '<script src="/pages/finans-yonetimi/gelir-gider/js/gelir-gider.js"></script>';
}

//Payment upload from excel
if ($page == 'dues/payment/upload-from-xls') {
    echo '<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.mini.min.js"></script>';
    echo '<script src="pages/dues/payment/js/upload.js"></script>';
}
if ($page == 'onay-bekleyen-tahsilatlar') {
    echo '<script src="pages/dues/payment/js/tahsilat-onay.js" defer></script>';
}

if ($page == 'yonetici-aidat-odeme') {
    echo '<script src="pages/dues/payment/js/tahsilat-gir.js" defer></script>';
    echo '<script src="pages/dues/payment/js/tahsilat-detay.js" defer></script>';
}


//KASA İŞLEMLERİ
if ($page == 'kasa-ekle' || $page == 'kasa-duzenle' || $page == 'kasa-listesi') {
    echo '<script src="/pages/finans-yonetimi/kasa/js/kasa.js"></script>';
}

//**************************AYARLAR ******************************/
if ($page == 'ayarlar') {
    echo '<script src="/pages/ayarlar/ayarlar.js"></script>';
}

if ($page == 'ana-sayfa') {
    // //echo '<script src="./dist/libs/apexcharts/dist/apexcharts.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/js/jsvectormap.min.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world.js" defer></script>';
    // echo '<script src="./dist/libs/jsvectormap/dist/maps/world-merc.js" defer></script>';
    // echo '<script src="./src/charts.js" defer></script>';
    echo '<script src="/assets/vendors/js/apexcharts.min.js"></script>';
    echo '<script src="assets/vendors/js/daterangepicker.min.js"></script>';
    echo '<script src="assets/vendors/js/circle-progress.min.js"></script>';
    echo '<script src="assets/vendors/js/jquery.time-to.min.js "></script>';
    echo ' <script src="/assets/js/dashboard-init.min.js"></script>';
}


if($page == "email-sms/sms_gonder_modal"){ 
    echo "<script src='/pages/email-sms/js/sms_gonder.js?v=<?php echo filemtime('pages/email-sms/js/sms_gonder.js'); ?>' defer></script>";  
}


// Profil sayfası scripti
if ($page == 'profile') {
    echo '<script src="/pages/profile/profile.js"></script>';
}

// Global kilit ekranı - tüm sayfalarda
echo '<link rel="stylesheet" href="/assets/css/lock-screen.css">';
echo '<script src="/assets/js/lock-screen.js"></script>';


?>











<script src="/src/jquery.inputmask.js" defer></script>


<script src="/assets/js/flatpickr.min.js"></script>
<script src="/assets/js/flatpickr.tr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js" defer></script>
<script src="/assets/js/jquery.validate.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" defer></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js" defer></script>
<script src="/assets/js/select2/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script src="/src/app.js?v=<?php echo filemtime('src/app.js'); ?>" defer></script>


<script src="/partials/mobile-menu.js"></script>
<!-- Fixes mobile hamburger menu toggle issues on some browsers -->
<script src="/assets/js/hamburger-fix.js"></script>
<script src="/pages/email-sms/js/sms.js"></script>


<!--<< All JS Plugins >>-->
