<?php

use App\Helper\Security;
use App\Helper\Helper;

use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\BorclandirmaDetayModel;
use Model\TahsilatModel;
use Model\FinansalRaporModel;

use App\Services\Gate;

$Blok = new BloklarModel();
$Daire = new DairelerModel();
$KisiModel = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();
$FinansalRapor = new FinansalRaporModel();



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
            
                <a href="/onay-bekleyen-tahsilatlar" class="btn btn-outline-success">
                    <i class="feather-check me-2"></i>Onay Bekleyen Ödemeler
                </a>
                <a href="/eslesmeyen-odemeler" class="btn btn-outline-secondary">
                    <i class="feather-copy me-2"></i>Eşleşmeyen Ödemeler
                </a>
                <a href="/excelden-odeme-yukle" class="btn btn-outline-primary">
                    <i class="feather-file-plus me-2"></i>Excelden Ödeme Yükle
                </a>  
                <div class="dropdown">
                                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside">
                                    <i class="feather-filter"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-eye me-3"></i>
                                        <span>Tümü</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-send me-3"></i>
                                        <span>Borcu olmayanları da getir</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-book-open me-3"></i>
                                        <span>Open</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-archive me-3"></i>
                                        <span>Draft</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-bell me-3"></i>
                                        <span>Revised</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-shield-off me-3"></i>
                                        <span>Declined</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-check me-3"></i>
                                        <span>Accepted</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-briefcase me-3"></i>
                                        <span>Leads</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-wifi-off me-3"></i>
                                        <span>Expired</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="feather-users me-3"></i>
                                        <span>Customers</span>
                                    </a>
                                </div>
                            </div>  
            <div class="dropdown">
                                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 12" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="feather-paperclip"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" style="">
                                    <a href="/pages/dues/payment/export/tum_sakinler_ozet_liste.php?format=pdf" 
                                    target="_blank"
                                    class="dropdown-item">
                                        <i class="bi bi-filetype-pdf me-3"></i>
                                        <span>PDF'e Aktar</span>
                                    </a>
                                    <a href="/pages/dues/payment/export/tum_sakinler_ozet_liste.php?format=xlsx" class="dropdown-item"
                                    target="_blank" 
                                    >
                                        <i class="bi bi-filetype-exe me-3"></i>
                                        <span>Excele Aktar</span>
                                    </a>
                                 
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item">
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
                                            <th style="width:7%">Daire Kodu</th>
                                            <th>Ad Soyad</th>
                                            <th class="text-end" style="width:11%">Borç Tutarı</th>
                                            <th class="text-end" style="width:11%">Gecikme Zammı</th>
                                            <th class="text-end" style="width:11%">Toplam Borç</th>
                                            <th class="text-end" style="width:11%">Kredi Tutarı</th>
                                            <th class="text-end" style="width:11%">Kalan Borç</th>

                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        foreach ($guncel_borclar as $index => $borc):
                                            $enc_id = Security::encrypt($borc->kisi_id);
                                            $tahsilat_color = 'secondary';
                                            $net_borc = $borc->kredi_tutari - $borc->toplam_kalan_borc;
                                            $color = $net_borc < 0 ? 'danger' : 'success';

                                            $oturum_durum_color = $borc->durum == "Aktif" ? "success" : "danger";

                                        ?>
                                            <tr>

                                                <td><?php echo $index + 1 ?></td>
                                                <td class="text-center"><?= ($borc->daire_kodu) ?> </td>

                                                <td><?= $borc->adi_soyadi ?>
                                                    <div>
                                                        <?php
                                                        $uyelik_tipi = $borc->uyelik_tipi;
                                                        $badge_color = $uyelik_tipi == "Kiracı" ? "warning" : "teal"
                                                        ?>
                                                        <a href="javascript:void(0)"
                                                            class="badge text-<?= $badge_color ?> border border-dashed border-gray-500"><?= $uyelik_tipi ?></a>
                                                        <a href="javascript:void(0)"
                                                            class="badge text-<?= $oturum_durum_color ?> border border-dashed border-gray-500"><?= $borc->durum ?></a>
                                                    </div>

                                                </td>
                                                <td class="text-end">
                                                    <i class="feather-trending-down fw-bold text-danger"></i>

                                                    <?= Helper::formattedMoney($borc->kalan_anapara)   ?>
                                                </td>
                                                <td class="text-end">
                                                    <?= Helper::formattedMoney($borc->hesaplanan_gecikme_zammi) ?>
                                                </td>
                                                <td class="text-end"><?= Helper::formattedMoney($borc->toplam_kalan_borc) ?>
                                                </td>
                                                <td>
                                                    <?= Helper::formattedMoney($borc->kredi_tutari ?? 0) ?>
                                                </td>
                                                <td class="text-end text-<?= $color ?>">
                                                    <?= Helper::formattedMoney(($net_borc)) ?>
                                                </td>
                                                <td style="width:5%;">
                                                    <div class="hstack gap-2 ">
                                                        <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                            class="avatar-text avatar-md kisi-borc-detay">
                                                            <i class="feather-eye"></i>
                                                        </a>

                                                        <?php if (Gate::allows('tahsilat_ekle_sil')) {; ?>
                                                            <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                                title="Tahsilat Gir"
                                                                data-kisi-id="<?php echo Security::encrypt($borc->kisi_id) ?>"
                                                                class="avatar-text avatar-md tahsilat-gir">
                                                                <i class="bi bi-credit-card-2-front"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="kisiBorcDetay" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content borc-detay">


        </div>
    </div>
</div>

<div class="modal fade" id="tahsilatGir" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tahsilat Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body tahsilat-modal-body">

                <!-- Overlay (Modal içi) -->



            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</a>
                <a href="javascript:void(0);" class="btn btn-outline-primary" id="tahsilatKaydet">
                    <i class="feather-save me-2"></i>Kaydet
                </a>
            </div>
        </div>
    </div>
</div>
<!-- list.php'nin en altına ekle -->
<script src="/pages/email-sms/sms.js"></script>
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

            $.get("/pages/email-sms/sms_gonder_modal.php", {
                id: id,
                kisi_id: kisiId
            }, function(data) {
                // Verileri tabloya ekle
                $('.modal-content.sms-gonder-modal').html(data);
                // Script'i manuel yükle

                // Modal'ı göster
                $('#SendMessage').modal('show');

                // Modal açıldıktan sonra SMS JS'ini başlat
                setTimeout(function() {
                    if (typeof window.initSmsModal === 'function') {


                        //$('#message').text('Merhaba, olarak size hatırlatmak isteriz ki, toplam borcunuz dir. Lütfen en kısa sürede ödeme yapınız.\n\nTeşekkürler.\nSite Yönetimi');

                        window.initSmsModal();
                        //Textareaya örnek değer ata

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