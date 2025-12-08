<?php

use App\Helper\Date;
use App\Helper\Helper;

use App\Services\Gate;
use Model\BloklarModel;
use Model\KisilerModel;
use App\Helper\Security;
use Model\DairelerModel;
use Model\TahsilatModel;

use Model\FinansalRaporModel;
use Model\BorclandirmaDetayModel;

$Blok = new BloklarModel();
$Daire = new DairelerModel();
$KisiModel = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();
$FinansalRapor = new FinansalRaporModel();


Gate::authorizeOrDie('yonetici_aidat_odeme');

//$kisiler = $KisiModel->SiteKisiBorcOzet($_SESSION['site_id']);

$guncel_borclar = $FinansalRapor->getGuncelBorclarGruplu($_SESSION['site_id']);


?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borç Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>

                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <!-- <a href="/onay-bekleyen-tahsilatlar" class="btn btn-outline-success">
                    <i class="feather-check me-2"></i>Onay Bekleyen Ödemeler
                </a>
                <a href="/eslesmeyen-odemeler" class="btn btn-outline-secondary">
                    <i class="feather-copy me-2"></i>Eşleşmeyen Ödemeler
                </a>
                <a href="/excelden-odeme-yukle" class="btn btn-outline-primary">
                    <i class="feather-file-plus me-2"></i>Excelden Ödeme Yükle
                </a> -->
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside">
                        <i class="feather-more-vertical"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="/onay-bekleyen-tahsilatlar" class="dropdown-item">
                            <i class="feather-check-square me-3"></i>
                            <span>Onay Bekleyen Ödemeler</span>
                        </a>
                        <a href="/eslesmeyen-odemeler" class="dropdown-item">
                            <i class="feather-x-circle me-3"></i>
                            <span>Eşleşmeyen Ödemeler</span>
                        </a>
                        <a href="/excelden-odeme-yukle" class="dropdown-item">
                            <i class="feather-file-plus me-3"></i>
                            <span>Excelden Ödeme Yükle </span>
                        </a>
                        <a href="/excelden-odeme-yukle" class="dropdown-item">
                            <i class="feather-send me-3"></i>
                            <span>Mesaj Gönder </span>
                        </a>


                    </div>
                </div>
                <div class="dropdown">
                    <?php $qParamInline = trim($_GET['q'] ?? ''); ?>
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside">
                        <i class="feather-filter<?= ($qParamInline !== '' ? ' text-primary' : '') ?>"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" id="filterDropdown">
                        <?php if ($qParamInline !== '') { ?>
                        <div class="px-3 py-2">
                            <span id="activeListFilterTagDropdown" class="badge bg-soft-primary py-2 text-primary border-soft-primary">
                                <i class="feather-search me-1"></i><?= htmlspecialchars($qParamInline, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php } ?>
                        <a href="javascript:void(0);" class="dropdown-item" id="js-filter-all">
                            <i class="feather-eye me-3"></i>
                            <span>Filtreyi Temizle</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-send me-3"></i>
                            <span>Borcu olmayanları da getir</span>
                        </a>
                    </div>
                </div>
             
                <div class="dropdown">
                    <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="feather-paperclip"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="">
                       
                        <a href="javascript:void(0);" class="dropdown-item js-export-excel"
                            target="_blank">
                            <i class="bi bi-filetype-exe me-3"></i>
                            <span>Excele Aktar</span>
                        </a>

                        <div class="dropdown-divider"></div>
                        <a href="/pages/dues/payment/export/tum_sakinler_ozet_liste.php?format=xlsx" class="dropdown-item">
                            <i class="bi bi-filetype-exe me-3"></i>
                            <span>Detaylı Ödeme Listesi Al</span>
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Blok ve Daireye Göre Toplam Aidat Borç Takibi";
    $text = "Bu sayfada blok ve daire bazında toplam aidat borçlarını takip edebilir, detay butonu ile borç detaylarına ulaşabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

<style>
   table {
    min-width: 300px;
   }

    </style>
    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">

                            <div class="table-responsive m-3">
                                <table class="table table-hover datatables" id="tahsilatTable">
                                    <thead>
                                        <tr>
                                            <th class="wd-30 no-sorting" style="width: 40px;">
                                                Sıra
                                            </th>
                                            <th class="all" style="width:7%" data-filter="string">Daire Kodu</th>
                                            <th class="all" style="min-width: 200px;" data-filter="string">
                                           
                                            Ad Soyad
                                            </th>
                                            <th data-filter="date">Giriş Tarihi</th>
                                            <th data-filter="date">Çıkış Tarihi</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Borç Tutarı</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Gecikme Zammı</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Toplam Borç</th>
                                            <th class="text-end" style="width:11%" data-filter="number">Kredi Tutarı</th>
                                            <th class="all text-end" style="width:11%" data-filter="number">Kalan Borç</th>

                                            <th class="all">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="kisiBorcDetay" tabindex="-1" role="dialog" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content borc-detay">


        </div>
    </div>
</div>
<style>
    .card-body{
        overflow-x: auto;
    }
</style>

<div class="modal fade" id="tahsilatGir" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="hstack justify-content-between">
                    <div>
                        <h5 class="modal-title" id="modalTitleId">Tahsilat Ekle</h5>
                        <span class="text-muted text-danger">Girdiğiniz tutarlar aynı zamanda kasaya Gelir/Gider olarak kaydedilecektir</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tahsilat-modal-body">
                <!-- Spinner -->
                <div class="d-flex justify-content-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <a class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</a>
                <a href="javascript:void(0);" class="btn btn-outline-primary" id="tahsilatKaydet">
                    <i class="feather-save me-2"></i>Kaydet
                </a>
            </div>
        </div>
    </div>
</div>
<!-- list.php'nin en altına ekle -->
<script src="/pages/email-sms/js/sms.js"></script>
<style>
    /* Daha üstteki backdrop biraz daha koyu */
    .modal-backdrop.stacked-backdrop {
        z-index: 1057;
        /* Varsayılan z-index değerini al */
    }
</style>


<div class="modal fade" id="SendMessage" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content sms-gonder-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>


<div class="modal fade" id="borcEkle" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content borc-ekle-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>


<div class="modal fade" id="makbuzGoster" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content makbuz-goster-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="kisilerdenSecOffcanvas" aria-labelledby="offcanvasEndLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasEndLabel">Kişilerden Seç</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body kisilerden-sec-offcanvas">
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
        </div>
    </div>
</div>

<style>
    .borc-satiri.secili-satir {
        background-color: #f0f9ff;
        /* Açık mavi bir arkaplan */
        border-left: 3px solid #0d6efd;
        /* Sol tarafa mavi bir çizgi */
    }

    .table tr:hover {
        cursor: pointer;
    }

    #islem_tarihi .flatpickr-calendar {
        position: static;
    }
</style>

<script type="module" src="/pages/dues/payment/js/borc-ekle.js?v=<?php echo filemtime("pages/dues/payment/js/borc-ekle.js"); ?>"></script>
<script>
    if (typeof window.onDataTablesReady !== 'function') {
        window.onDataTablesReady = function(cb){
            var tries = 0;
            (function wait(){
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && typeof window.initDataTable === 'function') { cb(); return; }
                if (tries++ > 100) { console.error('DataTables veya initDataTable yüklenemedi'); return; }
                setTimeout(wait, 100);
            })();
        };
    }

        window.onDataTablesReady(function(){
         
            table = initDataTable('#tahsilatTable',{
            processing: true,
            serverSide: true,
            retrieve: true,
            ajax: {
                url: '/pages/dues/payment/server_processing.php',
                type: 'GET'
            },
            columns: [
                { data: null, orderable: false, render: function(data, type, row, meta){ return meta.row + 1 + meta.settings._iDisplayStart; } },
                { data: 'daire_kodu' },
                { data: 'adi_soyadi_html' },
                { data: 'giris_tarihi' },
                { data: 'cikis_tarihi' },
                { data: 'kalan_anapara_formatted', className: 'text-end' },
                { data: 'hesaplanan_gecikme_zammi_formatted', className: 'text-end' },
                { data: 'toplam_kalan_borc_formatted', className: 'text-end' },
                { data: 'kredi_tutari_formatted', className: 'text-end' },
                { data: 'net_borc_formatted', className: 'text-end' },
                { data: 'islem_html', orderable: false }
            ],
            order: [[1, 'asc']],
            // initComplete ortak fonksiyonda geliyor (attachDtColumnSearch)
        });
        try {
            var params = new URLSearchParams(window.location.search);
            var q = params.get('q');
            if (q && $.fn.dataTable.isDataTable('#tahsilatTable')) {
                var dt = $('#tahsilatTable').DataTable();
                dt.search(q).draw();
            }
        } catch(e) {}
        $(document).on('click', '#clearListFilter', function(){
            var dt = $('#tahsilatTable').DataTable();
            dt.search('').draw();
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('q');
                window.history.replaceState(null, '', url.toString());
            } catch(e) {}
            $('#activeListFilterTag').remove();
            $('#clearListFilter').remove();
            $('#activeListFilterTagDropdown').remove();
        });
        $(document).on('click', '#js-filter-all', function(){
            var dt = $('#tahsilatTable').DataTable();
            dt.search('').draw();
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('q');
                window.history.replaceState(null, '', url.toString());
            } catch(e) {}
            $('#activeListFilterTag, #activeListFilterTagDropdown, #clearListFilter').remove();
            // $("#filterDropdown").removeClass("show");   

        });
        var __rt;
        $(window).on('resize', function(){
            clearTimeout(__rt);
            __rt = setTimeout(function(){
                if ($.fn.dataTable.isDataTable('#tahsilatTable')) {
                    var dt = $('#tahsilatTable').DataTable();
                    dt.columns.adjust().draw(false);
                }
            }, 150);
        });
        $(document).on('click', '.js-export-excel', function(e){
            e.preventDefault();
            var dt = $('#tahsilatTable').DataTable();
            var params = (dt.ajax && typeof dt.ajax.params === 'function') ? dt.ajax.params() : {};
            if (params) {
                delete params.start;
                delete params.length;
                delete params.draw;
            }
            params = params || {};
            params.format = 'xlsx';
            var url = '/pages/dues/payment/export/borc_listesi_excel.php?' + $.param(params);
            window.open(url, '_blank');
        });
    });
</script>
<script>
    $(document).on('click', '.js-close-send', function(e) {
        e.stopPropagation();
        $('#SendMessage').modal('hide'); // Sadece bu
    });



    (function() {
        const BASE = 1050,
            STEP = 10;
        document.addEventListener('show.bs.modal', function(e) {
            const openCount = document.querySelectorAll('.modal.show').length;
            const modalZ = BASE + (STEP * (openCount + 1)) + 5;
            e.target.style.zIndex = modalZ;
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const bd = backdrops[backdrops.length - 1];
                if (bd) {
                    bd.style.zIndex = modalZ - 5;
                    bd.classList.add('stacked-backdrop');
                }
                document.body.classList.add('modal-open');
            }, 10);
        });

        document.addEventListener('show.bs.offcanvas', function(e) {
            const offcanvasZ = BASE + 1050;
            e.target.style.zIndex = offcanvasZ;
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.offcanvas-backdrop');
                const bd = backdrops[backdrops.length - 1];
                if (bd) {
                    bd.style.zIndex = offcanvasZ - 5;
                }
            }, 10);
        });

        document.addEventListener('hidden.bs.modal', function() {
            if (document.querySelectorAll('.modal.show').length === 0) {
                document.body.classList.remove('modal-open');
            }
        });
    })();

    //get ile dosya içeriğini al

    $(document).ready(function() {
        $(document).on('click', '.mesaj-gonder', function() {
            var id = $(this).data('id');
            kisiId = $(this).data('kisi-id');
            var phone = ($(this).data('phone') || '').toString();
            var daire = ($(this).data('daire') || $(this).data('daire-kodu') || '').toString();
            makbuz_bildirim = $(this).data('makbuz-bildirim') || "false";

            $.get("/pages/email-sms/sms_gonder_modal.php", {
                id: id,
                kisi_id: kisiId,
                includeFile: "kisiye-mesaj-gonder.php", // telefon, mesaj gibi değişkenleri almak için,
                makbuz_bildirim: makbuz_bildirim
            }, function(data) {
                // Verileri tabloya ekle
                $('.modal-content.sms-gonder-modal').html(data);
                // Script'i manuel yükle

                // Modal'ı göster
                $('#SendMessage').modal('show');

                setTimeout(function() {
                    if (phone) { try { window.kisiTelefonNumarasi = ''; } catch(e){} }
                    if (typeof window.initSmsModal === 'function') {
                        window.initSmsModal();
                    }
                    if (typeof window.addPhoneToSMS === 'function' && phone) {
                        window.addPhoneToSMS({ phone: phone, id: id, daire: daire });
                    }
                }, 100);
            });
        });

    });


    $(document).ready(function() {
        var modalURL = "/pages/dues/payment/modal/modal_borc_ekle.php";

        $(document).on('click', '.borc-ekle', function() {
            var id = $(this).data('id');
            kisiId = $(this).data('kisi-id');
            $.get(modalURL, {
                id: id,
                kisi_id: kisiId
            }, function(data) {
                // Verileri tabloya ekle
                $('.modal-content.borc-ekle-modal').html(data);
                // Script'i manuel yükle

                // Modal'ı göster
                $('#borcEkle').modal('show');

                //Select2'yi başlat
                setTimeout(function() {
                    if ($(".select2").length > 0) {
                        $(".select2").select2({
                            placeholder: "Borç Türü Seçiniz",
                            dropdownParent: $('#borcEkle'),
                        });
                    }
                    if ($("#borc_islem_tarihi").length > 0) {

                        $("#islem_tarihi").flatpickr({
                            dateFormat: "d.m.Y H:i",
                            //defaultDate: [new Date()],
                            locale: "tr",
                            enableTime: true,
                            minuteIncrement: 1,

                        })
                    }
                }, 100);


            });
        });

        //Borç Düzenle butonuna tıklanınca
        $(document).on('click', '.borc-duzenle', function() {
            let borcDetayId = $(this).data('id');
            let kisiId = $(this).data('kisi-id');
            let modal = $("#borcEkle");
            // Borç bilgilerini getir
            $.get(modalURL, {
                borc_detay_id: borcDetayId,
                kisi_id: kisiId
            }, function(data) {
                // Verileri modalda göster
                modal.find('.borc-ekle-modal').html(data);

                setTimeout(function() {
                    if ($(".select2").length > 0) {
                        $(".select2").select2({
                            placeholder: "Borç Türü Seçiniz",
                            dropdownParent: $('#borcEkle'),
                        });
                    }
                    if ($("#borc_islem_tarihi").length > 0) {

                        $("#islem_tarihi").flatpickr({
                            dateFormat: "d.m.Y H:i",
                            locale: "tr",
                            enableTime: true,
                            minuteIncrement: 1,

                        })
                    }
                }, 100);

                // Modal'ı göster
                modal.modal('show');
            });
        });


        var urlMakbuz = '/pages/dues/payment/modal/modal_makbuz_goster.php';
        $(document).on('click', '.makbuz-goster', function() {
            let makbuzId = $(this).data('id');
            let kisiId = $(this).data('kisi-id');
            let modalMakbuz = $("#makbuzGoster");
            // Borç bilgilerini getir
            $.get(urlMakbuz, {
                makbuz_id: makbuzId,
                kisi_id: kisiId
            }, function(data) {
                // Verileri modalda göster
                modalMakbuz.find('.makbuz-goster-modal').html(data);

                // Modal'ı göster
                modalMakbuz.modal('show');
            });
        });


    });
</script>


<script>

</script>

<script>
    var kisiId;
    var secilenBorcIdleri = []; // Seçilen borç ID'lerini tutacak dizi
    let secilenBorcToplami = 0; // Seçilen borçların toplam tutarını tutacak değişken

    $(document).on('click', '.kisi-borc-detay', function() {
        var id = $(this).data('id');
        kisiId = $(this).data('id');
        table = $('#tahsilatTable').DataTable();
        row = table.row($(this).closest('tr'));


        $.get("pages/dues/payment/tahsilat-detay.php", {
            id: id,
            kisi_id: kisiId
        }, function(data) {
            // Verileri tabloya ekle
            $('.borc-detay').html(data);
            // Modal'ı göster
            $('#kisiBorcDetay').modal('show');
        });
    });

    $(document).on('click', '.tahsilat-gir', function() {
        kisiId = $(this).data('kisi-id');
        table = $('#tahsilatTable').DataTable();
        row = table.row($(this).closest('tr'));
        secilenBorcIdleri = []; // Her yeni tahsilat girişinde seçilen borç ID'lerini sıfırla   

        $.get("pages/dues/payment/tahsilat_gir_modal.php", {
            kisi_id: kisiId
        }, function(data) {
            // Verileri tabloya ekle
            $('.tahsilat-modal-body').html(data);

            // Modal'ı göster
            if ($(".modal").length > 0) {
                $('#tahsilatGir').modal('show');
            }
            if ($(".select2").length > 0) {
                $(".select2").select2({
                    placeholder: "Kasa Seçiniz",
                    dropdownParent: $('#tahsilatGir'),
                });

                $("#tahsilat_turu").select2({
                    tags: true,
                    dropdownParent: $('#tahsilatGir'),
                });

            }
            if ($("#islem_tarihi").length > 0) {

                $("#islem_tarihi").flatpickr({
                    dateFormat: "d.m.Y H:i",
                    locale: "tr",
                    enableTime: true,
                    minuteIncrement: 1,

                })
            }

        });

        // Modal'ı göster
    });




    $(document).ready(function() {

        /**
         * Sunucudan toplam tutarı getirir ve ekranı günceller.
         */
        function guncelleToplamTutar() {
            // Eğer hiç borç seçilmemişse, sunucuya istek atmadan sıfırla.
            if (secilenBorcIdleri.length === 0) {
                $('#secilen-tahsilat-tutari').text('0.00');
                $("#tutar").text('0.00');

                return;
            }

            const formData = new FormData();
            secilenBorcIdleri.forEach(id => {
                formData.append('borc_idler[]', id);
            });
            formData.append('action', 'hesapla_toplam_tutar');


            fetch(url, {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const toplam = parseFloat(data.toplam_tutar).toFixed(2);
                        $('#secilen-tahsilat-tutari').text(toplam) +
                            ' TL'; // Seçilen tahsilat tutarını güncelle
                        $("#tutar").val((toplam.replace(".",
                            ","))); // Tahsilat gir modalındaki tutar alanını güncelle
                        secilenBorcToplami = toplam; // Seçilen borçların toplam tutarını güncelle
                        console.log("Toplam tutar:", data);
                    } else {
                        console.error('Hata:', data.message);
                        alert('Toplam tutar hesaplanırken bir hata oluştu.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        /**
         * Butonun ve satırın görünümünü günceller.
         * @param {jQuery} $button - Tıklanan buton nesnesi.
         * @param {string} action - 'ekle' veya 'cikar'.
         */
        function guncelleArayuzu($button, action) {
            const $satir = $button.closest('.borc-satiri');
            if (action === 'ekle') {
                $button.text('Çıkar').data('action', 'cikar').addClass('text-danger');
                $satir.addClass('secili-satir'); // Arkaplanı değiştirmek için (CSS'te tanımlanmalı)
            } else { // action === 'cikar'
                $button.text('Ekle').data('action', 'ekle').removeClass('text-danger');
                $satir.removeClass('secili-satir');
            }
        }

        // "Ekle/Çıkar" butonuna tıklandığında çalışacak ana fonksiyon

        $(document).on('click', '.borc-satiri, .tahsilat-islem-btn', function(e) {
            e.preventDefault();
            e.stopPropagation(); // ÖNEMLİ: İç içe tıklamaların birbirini tetiklemesini engeller

            const $satir = $(this).closest('.borc-satiri');
            const borcId = $satir.data('borc-id');

            // Satırın içindeki butonu bul
            const $button = $satir.find('.tahsilat-islem-btn');

            // Butonun mevcut 'data-action' durumuna göre ne yapılacağına karar ver
            const mevcutAction = $button.data('action');

            if (mevcutAction === 'ekle') {
                // EKLEME İŞLEMİ
                if (!secilenBorcIdleri.includes(borcId)) {
                    secilenBorcIdleri.push(borcId);
                }
                guncelleArayuzu($button, 'ekle'); // Butonun durumunu değiştir

            } else { // mevcutAction === 'cikar'
                // ÇIKARMA İŞLEMİ
                const index = secilenBorcIdleri.indexOf(borcId);
                if (index > -1) {
                    secilenBorcIdleri.splice(index, 1);
                }
                guncelleArayuzu($button, 'cikar'); // Butonun durumunu değiştir
            }

            // Toplam tutarı her zaman güncelle
            guncelleToplamTutar();

            console.log("Seçilen ID'ler:", secilenBorcIdleri);
        });


        $(document).on('click', '.kredi-kullan', function() {
            $("#kullanilacak_kredi").val($(this).data("kredi"));
            updateTahsilatTutari();
        });
        // //Kredi kullanımını kontrol et
        // $(document).on('input', '#kullanilacak_kredi', function() {
        //     var kredi = ($(this).val() || 0).replace(',', '.');
        //     kredi = parseFloat(kredi);

        //     if (isNaN(kredi) || kredi < 0) {
        //         kredi = 0;
        //     }

        //     // Tahsilat tutarını güncelle
        //     var toplamTutar = secilenBorcToplami - kredi;
        //     $("#tutar").val(toplamTutar.toFixed(2).replace('.', ','));

        // });

        function updateTahsilatTutari() {
            var kredi = ($("#kullanilacak_kredi").val() || 0).replace(',', '.');
            kredi = parseFloat(kredi);

            if (isNaN(kredi) || kredi < 0) {
                kredi = 0;
            }

            // Tahsilat tutarını güncelle
            var toplamTutar = secilenBorcToplami - kredi;
            $("#tutar").val(toplamTutar.toFixed(2).replace('.', ','));
        }



    });
</script>
