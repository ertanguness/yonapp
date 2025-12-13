<?php

use App\Helper\Form;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\KisiHelper;
use App\Helper\Security;
use Model\TahsilatHavuzuModel;
use Model\DairelerModel;

$DaireModel = new DairelerModel();
$HavuzModel = new TahsilatHavuzuModel();
$KisiHelper = new KisiHelper();

$site_id = $_SESSION['site_id'] ?? 0;

$tahsilat_havuzu = $HavuzModel->TahsilatHavuzu($site_id);

$daireler  = $DaireModel->SitedekiDaireler($site_id);
// Daire seçiniz diye option ekle
$daireler = array_merge([['id' => '', 'daire_kodu' => 'Daire Seçiniz']], $daireler);
$optionsForSelect = array_column($daireler, 'daire_kodu', 'id');



?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tahsilat Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Tahsilatlar</li>
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

                <a href="/yonetici-aidat-odeme" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
                <a href="/onay-bekleyen-tahsilatlar" class="btn btn-outline-primary">
                    <i class="feather-check-circle me-2"></i>Eşleşen Ödemeler
                </a>

            </div>
        </div>
    </div>
</div>
<div class="main-content">
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5>Eşleşmeyen Tahsilat Yüklemeleri</h5>
                </div>
                <div class="card-body p-0 ">
                    <div class="table-responsive ">
                        <div class="row">
                            <div class="col-sm-12 projectList_wrapper">
                                <table class="table table-hover datatables no-footer" id="projectList"
                                    aria-describedby="projectList_info">
                                    <thead>


                                        <tr>
                                            <th>İşlem Tarihi</th>
                                            <th>Açıklama</th>
                                            <th class="text-end">Tahsilat Tutarı </th>
                                            <th>İşlenen Tutar</th>
                                            <th>Kalan Tutar</th>
                                            <th>Daire No</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tahsilat_havuzu as $havuz) {
                                            $enc_id = Security::encrypt($havuz->id);
                                            $kalan_tutar = $havuz->tahsilat_tutari - $havuz->islenen_tutar;
                                            $olarak_aktar = $havuz->tahsilat_tutari > 0 ? "Gelir olarak" : "Gider olarak";
                                            $aktar_color = $havuz->tahsilat_tutari > 0 ? "success" : "danger";
                                        ?>
                                            <tr class="single-item odd">
                                                <td class="sorting_1">
                                                    <?php echo Date::YmdHis($havuz->islem_tarihi); ?>
                                                </td>
                                                <td class="project-name-td">
                                                    <div class="hstack gap-4">

                                                        <div>
                                                            <a href="#"
                                                                class="text-truncate-1-line description"
                                                                data-bs-popover="<?php echo $havuz->ham_aciklama; ?>">
                                                                <?php echo $havuz->ham_aciklama; ?>
                                                            </a>

                                                            <p
                                                                class="fs-12 text-muted mt-2 text-truncate-1-line project-list-desc">
                                                                <?php echo $havuz->referans_no; ?>
                                                                .</p>
                                                            <div
                                                                class="project-list-action fs-12 d-flex align-items-center gap-3 mt-2">
                                                                <a href="javascript:void(0);" class="text-primary eslesen-havuza-gonder"
                                                                    data-id="<?php echo $enc_id; ?>">Eşleşen Havuza Gönder</a>
                                                                <span class="vr text-muted"></span>
                                                                <a href="javascript:void(0);" class="text-<?php echo $aktar_color; ?> kasaya-aktar"
                                                                    data-id="<?php echo $enc_id; ?>"> <?php echo $olarak_aktar; ?> kasaya Aktar</a>
                                                                <span class="vr text-muted"></span>
                                                                <a href="javascript:void(0);"
                                                                    data-id="<?php echo $enc_id; ?>"
                                                                    class="text-danger eslesmeyen-odeme-sil">Sil</a>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-end">
                                                    <?php echo Helper::formattedMoney($havuz->tahsilat_tutari); ?>
                                                </td>
                                                <td class="text-end" style="width:120px">
                                                    <?php echo Helper::formattedMoney($havuz->islenen_tutar); ?>

                                                </td>
                                                <td class="text-end" style="width:120px">
                                                    <input type="text" data-kalan-tutar="<?php echo Helper::formattedMoneyToNumber($kalan_tutar); ?>" class="form-control form-control-sm text-end money" placeholder="İşlenecek Tutar" value="<?php echo Helper::formattedMoney($kalan_tutar); ?>" />
                                                </td>
                                                <td>
                                                    <select class="form-control kisi-ajax-select" style="width: 100%;">
                                                        <!-- Başlangıçta boş olacak -->
                                                    </select>

                                                    <?php //echo $KisiHelper->KisiSelect(
                                                    // "kisi_id" . uniqid(),
                                                    ///   0,
                                                    // false,
                                                    // true) 
                                                    ?>

                                                </td>

                                            </tr>
                                        <?php } ?>


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

<!-- Özel Açıklama Modal'i -->
<div id="aciklama-modal" class="aciklama-modal" style="display: none;">
    <div class="aciklama-modal-content">
        <div class="aciklama-modal-header">
            <h5>Ödeme Açıklaması</h5>
            <button type="button" class="aciklama-modal-close">&times;</button>
        </div>
        <div class="aciklama-modal-body">
            <p id="aciklama-text"></p>
        </div>
    </div>
</div>


<div class="modal fade" id="kasayaAktar" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content kasaya-aktar-modal">
            <!-- İçerik AJAX ile yüklenecek -->
        </div>
    </div>
</div>



<!-- Modal için CSS -->
<style>
    .aciklama-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        animation: fadeIn 0.3s ease;
    }

    .aciklama-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        max-width: 500px;
        width: 90%;
        max-height: 400px;
        animation: slideIn 0.3s ease;
    }

    .aciklama-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa;
        border-radius: 12px 12px 0 0;
    }

    .aciklama-modal-header h5 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    .aciklama-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .aciklama-modal-close:hover {
        background-color: #e9ecef;
        color: #495057;
    }

    .aciklama-modal-body {
        padding: 20px;
        max-height: 300px;
        overflow-y: auto;
    }

    .aciklama-modal-body p {
        margin: 0;
        line-height: 1.6;
        color: #495057;
        font-size: 14px;
        word-wrap: break-word;
    }

    .description {
        cursor: pointer;
        text-decoration: none !important;
        position: relative;
        transition: color 0.2s ease;
    }

    .description:hover {
        color: #0d6efd !important;
    }

    .description::after {
        content: '🔍';
        font-size: 12px;
        margin-left: 5px;
        opacity: 0.6;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translate(-50%, -60%);
        }

        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }

    /* Responsive */
    @media (max-width: 576px) {
        .aciklama-modal-content {
            width: 95%;
            margin: 20px;
        }

        .aciklama-modal-header,
        .aciklama-modal-body {
            padding: 15px;
        }
    }
</style>

<script>
    var url = 'pages/dues/payment/api.php'; // Define the URL for the AJAX request

    $(function() {
        $(document).on('click', '.description', function(e) {

            e.preventDefault();
            var $this = $(this);
            var content = $this.data('bs-popover');

            // Modal'ı göster
            $('#aciklama-text').text(content);
            $('#aciklama-modal').fadeIn(300);


        });
    })

    // Modal'ı kapat - X butonu
    $(document).on('click', '.aciklama-modal-close', function() {
        $('#aciklama-modal').fadeOut(300);
    });

    // Modal'ı kapat - dış alan tıklama
    $(document).on('click', '#aciklama-modal', function(e) {
        if (e.target === this) {
            $('#aciklama-modal').fadeOut(300);
        }
    });

    // ESC tuşu ile kapat
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#aciklama-modal').is(':visible')) {
            $('#aciklama-modal').fadeOut(300);
        }
    });

    //Select2 kişileri getirme
    $(function() {
        $('.kisi-ajax-select').select2({
            placeholder: 'Daire veya Kişi Ara...',
            minimumInputLength: 2, // Arama yapmak için en az 2 karakter girilmesini sağla
            ajax: {
                url: url, // İstek yapılacak URL
                dataType: 'json',
                delay: 250, // Kullanıcı yazmayı bıraktıktan sonra 250ms bekle
                type: 'POST',
                data: function(params) {
                    // Sunucuya gönderilecek veriler
                    return {
                        term: params.term, // Arama terimi
                        action: 'kisi_ara' // API dosyasında hangi işlemi yapacağımızı belirtir
                    };
                },
                processResults: function(data) {

                    // Sunucudan gelen JSON verisini Select2 formatına dönüştür
                    return {
                        results: data.results
                    };
                },
                cache: true // Tekrarlanan aramalar için sonuçları önbelleğe al
            },
            language: {
                inputTooShort: function() {
                    return "Arama yapmak için en az 2 karakter giriniz.";
                }
            } // Türkçe dil desteği için (isteğe bağlı)
        })

    });


    $(function() {
        //inputta değişiklik olunca sadece rakam ve nokta kabul et
        $('.money').on('blur', function() {

            var value = $(this).val();
            let kalan_tutar = $(this).data('kalan-tutar');

            //tutarlar tahsilat tutarlarından büyük olamaz
            if (value > kalan_tutar) {
                $(this).val(kalan_tutar);
                Toastify({
                    text: "İşlenecek tutar, tahsilat tutarından büyük olamaz!",
                    duration: 3000,
                    close: true,
                    gravity: "top", // `top` or `bottom`
                    position: "center", // `left`, `center` or `right`
                    borderradius: "10px",
                    style: {
                        background: "linear-gradient(to right, #ff6b6b, #ff6b6b)",
                        borderRadius: "6px",
                    },
                }).showToast();

            }
        });

        $(document).on('click', '.eslesen-havuza-gonder', function() {
            var $this = $(this);
            var id = $this.data('id');
            row = $this.closest('tr');
            var daire_kisi = row.find('select').val();
            var islenen_tutar = row.find('.money').val();


            if (!daire_kisi) {
                swal.fire(
                    'Hata!',
                    'Lütfen bir daire seçiniz.',
                    'error'
                );
                return;
            }

            var formData = new FormData();
            formData.append('id', id);
            formData.append('kisi_id', daire_kisi);
            formData.append('islenen_tutar', islenen_tutar);
            formData.append('action', 'eslesen_havuza_gonder');

            fetch(url, {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.status === 'success') {
                        // İşlem başarılı, satırı kaldır
                        if (data.status === 'success') {
                            if (data.kalan_tutar == 0) {
                                row.fadeOut(500, function() {
                                    $(this).remove();
                                    // table.row(row).remove().draw(true);
                                });
                            } else {
                                //kalan tutarı güncelle 2.sutün
                                row.find('td:nth-child(4)').text(data.islenen_tutar_formatted);
                                row.find('.money').attr('data-kalan-tutar', data.kalan_tutar).val(data.kalan_tutar);
                            }
                            Toastify({
                                text: "Tahsilat eşleşti ve havuza gönderildi.",
                                duration: 3000,
                                close: true,
                                gravity: "top", // `top` or `bottom`
                                position: "center", // `left`, `center` or `right`
                                style: {
                                    background: "linear-gradient(to right, #199b5aff, #199b5aff)",
                                    borderRadius: "6px",
                                },
                            }).showToast();
                        }

                    } else {
                        swal.fire(
                            'Hata!',
                            data.message,
                            data.status
                        );
                    }

                })
        });

        $(document).on('click', '.eslesmeyen-odeme-sil', function() {

            var $this = $(this);
            var id = $this.data('id');
            var row = $this.closest('tr');



            var formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'eslesmeyen_odeme_sil');

            swal.fire({
                title: 'Emin misiniz?',
                text: "Bu tahsilatı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet, Sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                            method: 'POST',
                            body: formData
                        }).then(response => response.json())
                        .then(data => {
                            console.log(data);
                            if (data.status === 'success') {
                                //Eğer kalan tutar 0 ise
                                // İşlem başarılı, satırı kaldır
                                table.row(row).remove().draw(false);

                                //tooltipleri kapat
                                Toastify({
                                    text: "Tahsilat silindi.",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top", // `top` or `bottom`
                                    position: "center", // `left`, `center` or `right`
                                    style: {
                                        background: "linear-gradient(to right, #199b5aff, #199b5aff)",
                                        borderRadius: "6px",
                                    },
                                }).showToast();
                            } else {
                                swal.fire(
                                    'Hata!',
                                    data.message,
                                    data.status
                                );
                            }

                        })
                }
            });


        });


        $(document).on('click', '.kasaya-aktar', function() {
            var $this = $(this);
            var id = $this.data('id');
            row = $this.closest('tr');
            $.get('/pages/dues/payment/modal/kasaya_aktar_modal.php', {
                    id: id
                },
                function(data) {
                    $('#kasayaAktar .kasaya-aktar-modal').html(data);
                    $('#kasayaAktar').modal('show');


                    $(".modal-content .select2").select2({
                        dropdownParent: $('#kasayaAktar')
                    });

                    gelirgiderkalemleri();
                });


        });

        /** Gelir Gider olarak kasaya Aktar */
        $(document).on('click', '#kasayaKaydetBtn', function() {
             var $this = $(this);
            // var id = $this.data('id');
            // row = $this.closest('tr');
            var form = $("#kasayaAktarForm");

            var formData = new FormData(form[0]);
            formData.append('action', 'kasaya_aktar');
            formData.append('kategori', form.find('#gelir_gider_tipi option:selected').text());

            // for(var pair of formData.entries()) {
            //     console.log(pair[0]+ ', ' + pair[1]); 
            // }
            // return;

            /**butonu disabled yap */
            $this.prop('disabled', true);

            /**Kaydediliyor yaz */
            $this.text('Kaydediliyor...');


            fetch(url, {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.status === 'success') {
                        // İşlem başarılı, satırı kaldır
                        if (data.status === 'success') {

                            row.fadeOut(500, function() {
                                $(this).remove();
                                // table.row(row).remove().draw(true);
                            });
                            $('#kasayaAktar').modal('hide');

                            //butonu enabled yap
                            $this.prop('disabled', false);
                            //Kaydediliyor yazını sil
                            $this.text('Kasaya Aktar');   

                            Toastify({
                                text: "Tahsilat kasaya aktarıldı.",
                                duration: 3000,
                                close: true,
                                gravity: "top", // `top` or `bottom`
                                position: "center", // `left`, `center` or `right`
                                style: {
                                    background: "linear-gradient(to right, #199b5aff, #199b5aff)",
                                    borderRadius: "6px",
                                },
                            }).showToast();
                        }

                    } else {
                        swal.fire(
                            'Hata!',
                            data.message,
                            data.status
                        );
                    }

                }).catch(error => {
                    console.error('Hata:', error);
                    swal.fire(
                        'Hata!',
                        'İstek sırasında bir hata oluştu.',
                        'error'
                    );
                    //butonu enabled yap
                    $this.prop('disabled', false);
                    //Kaydediliyor yazını sil
                    $this.text('Kasaya Aktar');   

                });
                

        });


    })

    /**gelir_gider_tipi */
    function gelirgiderkalemleri() {
        var selected = $("#gelir_gider_tipi option:selected").text(); // kategori_id
        var type = $("#type").val() == "Gelir" ? 6 : 7;
        //console.log("type", type);

        $.get(url, {
            action: 'gelir_gider_kalemi_getir',
            kategori_adi: selected,
            kategori_tipi: type
        }, function(res) {
            var data = typeof res === 'string' ? JSON.parse(res) : res;
            console.log("değişti", data);
            if (data.status === 'success') {
                $('#gelir_gider_kalemi').html(data.options).trigger('change');
            } else {
                console.error('Hata:', data.message || 'Bilinmeyen hata');
            }
        });

    }
    $(function() {

        $(document).on('change', '#gelir_gider_tipi', function() {
            gelirgiderkalemleri();
        });

      
    })
</script>