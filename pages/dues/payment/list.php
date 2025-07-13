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

                <a href="index?p=dues/payment/tahsilat-onay" class="btn btn-outline-success">
                    <i class="feather-check me-2"></i>Onay Bekleyen Ödemeler
                </a>
                <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-secondary">
                    <i class="feather-copy me-2"></i>Eşleşmeyen Ödemeler
                </a>
                <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-primary">
                    <i class="feather-file-plus me-2"></i>Excelden Ödeme Yükle
                </a>
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
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        foreach ($guncel_borclar as $index => $borc):
                                            $enc_id = Security::encrypt($borc->kisi_id);
                                            $tahsilat_color = 'secondary';
                                            //$color = $kalan_borc < 0 ? 'danger' : 'success';

                                        ?>
                                            <tr>

                                                <td><?php echo $index + 1 ?></td>
                                                <td class="text-center"><?= ($borc->daire_kodu) ?> </td>
                                             
                                                <td><?= $borc->adi_soyadi ?>
                                                <div>
                                                    <?php 
                                                        $uyelik_tipi = $borc->uyelik_tipi;
                                                        $badge_color = $uyelik_tipi == "Kiracı" ? "danger" : "teal"
                                                    ?>
                                                <a href="javascript:void(0)" class="badge text-<?= $badge_color ?> border border-dashed border-gray-500"><?= $uyelik_tipi ?></a>
                                                </div>

                                                </td>
                                                <td class="text-end">
                                                    <i class="feather-trending-down fw-bold text-danger"></i>

                                                    <?= Helper::formattedMoney($borc->kalan_anapara)   ?>
                                                </td>
                                                <td class="text-end"><?= Helper::formattedMoney($borc->hesaplanan_gecikme_zammi) ?>
                                                </td>
                                                <td class="text-end"><?= Helper::formattedMoney($borc->toplam_kalan_borc) ?></td>
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
<style>
    .borc-satiri.secili-satir {
        background-color: #f0f9ff;
        /* Açık mavi bir arkaplan */
        border-left: 3px solid #0d6efd;
        /* Sol tarafa mavi bir çizgi */
    }
</style>
<script>
    var kisiId;
    var secilenBorcIdleri = []; // Seçilen borç ID'lerini tutacak dizi

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
            $('#tahsilatGir').modal('show');
            $(".select2").select2({
                placeholder: "Kasa Seçiniz",
                dropdownParent: $('#tahsilatGir'),
            });

            $("#tahsilat_turu").select2({
                tags: true,
                dropdownParent: $('#tahsilatGir'),
            });

            $(".flatpickr").flatpickr({
                dateFormat: "d.m.Y",
                locale: "tr", // locale for this instance only
            });
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
                        $('#secilen-tahsilat-tutari').text(toplam) + ' TL'; // Seçilen tahsilat tutarını güncelle
                        $("#tutar").val((toplam.replace(".",","))); // Tahsilat gir modalındaki tutar alanını güncelle
                        //console.log("Toplam tutar:", data);
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
        $(document).on('click', '.tahsilat-islem-btn', function(e) {
            e.preventDefault(); // a tag'inin varsayılan davranışını engelle

            const $button = $(this);
            const action = $button.data('action');
            // Satırın kendisinden ID'yi alıyoruz
            const borcId = $button.closest('.borc-satiri').data('borc-id');

            if (action === 'ekle') {
                // Eğer ID zaten dizide yoksa ekle (güvenlik önlemi)
                if (!secilenBorcIdleri.includes(borcId)) {
                    secilenBorcIdleri.push(borcId);
                }
                guncelleArayuzu($button, 'ekle');
            } else { // action === 'cikar'
                // ID'yi diziden çıkar
                const index = secilenBorcIdleri.indexOf(borcId);
                if (index > -1) {
                    secilenBorcIdleri.splice(index, 1);
                }
                guncelleArayuzu($button, 'cikar');
            }

            // Diziyi güncelledikten sonra sunucudan yeni toplamı iste
            guncelleToplamTutar();

            // Konsolda güncel diziyi görebilirsiniz (hata ayıklama için)
            //console.log("Seçilen ID'ler:", secilenBorcIdleri);
        });

    });
</script>