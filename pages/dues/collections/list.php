<?php

use App\Services\Gate;
use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Date;

use Model\TahsilatModel;

$TahsilatModel = new TahsilatModel();



//$kisiler = $KisiModel->SiteKisiBorcOzet($_SESSION['site_id']);

$tumTahsilatlar = $TahsilatModel->getTumTahsilatlar($_SESSION['site_id']);

//Gate::authorizeOrDie('tahsilat_listele');


?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Tahsilat</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Tahsilat Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/debit/manage" class="btn btn-outline-secondary">
            <i class="feather-file-plus me-2"></i>
            Excele Aktar
        </a>
    </div>
</div>
<!-- ... (page-header kısmı aynı kalabilir) ... -->

<div class="main-content">
    <?php
    $title = "Tahsilat Listesi";
    $text = "Bu sayfada siteye ait tüm tahsilatları görüntüleyebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="tahsilatlarTable" class="table datatables" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Makbuz No</th>
                                <th>İşlem Tarihi</th>
                                <th>Kişi / Daire</th>
                                <th>Açıklama / Kasa</th>
                                <th class="text-end">Tutar</th>
                                <th class="text-center" style="width:10%">Detay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tumTahsilatlar as $tahsilat):
                                $enc_id = Security::encrypt($tahsilat->id);
                                $kasa_id = Security::encrypt($tahsilat->kasa_id);
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light-secondary">
                                            <?= htmlspecialchars($tahsilat->makbuz_no ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= ($tahsilat->islem_tarihi) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($tahsilat->adi_soyadi) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($tahsilat->daire_kodu) ?></small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($tahsilat->aciklama ?: 'Genel Tahsilat') ?></div>
                                        <small class="text-muted" data-bs-toggle="tooltip" data-bs-original-title="Hareketleri Görüntüle">
                                            <a href="index?p=finans-yonetimi/kasa/hareketler&id=<?= $kasa_id ?>">
                                                <i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($tahsilat->kasa_adi) ?>
                                            </a>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold">
                                            <?= Helper::formattedMoney($tahsilat->tutar) ?>
                                        </div>
                                        <?php if($tahsilat->kullanilan_kredi >0 ) { ?>
                                        <div>
                                            <?= "Kredi : " . Helper::formattedMoney($tahsilat->kullanilan_kredi) ?>
                                        </div>
                                        <?php } ?>
                                     
                                    </td>
                                    <td class="text-center">
                                        <div class="text-center d-flex justify-content-center align-items-center gap-1">

                                            <button class="avatar-text avatar-md tahsilat-detay-goster"
                                                data-id="<?= $enc_id ?>" title="Tahsilat Detaylarını Görüntüle">
                                                <i class="feather-chevron-down"></i>
                                            </button>
                                            <a href="#" id="delete-tahsilat"
                                                data-id="<?= $enc_id ?>"
                                                class="avatar-text avatar-md" title="Tahsilatı Sil">
                                                <i class="feather-trash-2"></i>

                                            </a>
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

<script>
    let url = "pages/dues/collections/api.php"; // API URL'si
    $(function() {

        // 2. Detay Butonuna Tıklama Olayını Dinle
        $('#tahsilatlarTable tbody').on('click', 'button.tahsilat-detay-goster', function() {
            const $button = $(this);
            const tr = $button.closest('tr');
            const row = table.row(tr);

            //butonun ikonunu değiştir
            if ($button.html().includes('chevron-up')) {
                $button.html('<i class="feather-chevron-down"></i>');
            } else {
                $button.html('<i class="feather-chevron-up"></i>');
            }




            if (row.child.isShown()) {
                // Zaten açıksa kapat
                row.child.hide();
                tr.removeClass('details-shown');
            } else {
                // Kapalıysa, "Yükleniyor..." göster ve AJAX isteği yap
                row.child('<div class="p-3 text-center">Detaylar yükleniyor...</div>').show();
                tr.addClass('details-shown');

                const tahsilatId = $button.data('id');

               // console.log('tahsilatId:', tahsilatId); // Debug için konsola yazdır
                

                $.ajax({
                    url: url, // Ana API url'niz
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'get_tahsilat_detaylari',
                        id: tahsilatId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            console.log(response.data); // Gelen veriyi konsola yazdır (debug için)
                            // Gelen veriyle alt satırın içeriğini oluştur
                            const content = formatTahsilatDetay(response.data);
                            row.child(content).show();
                        } else {
                            row.child('<div class="alert alert-danger m-3">Hata: ' + response
                                .message + '</div>').show();
                        }
                    },
                    error: function() {
                        row.child(
                            '<div class="alert alert-danger m-3">Detaylar yüklenirken bir sunucu hatası oluştu.</div>'
                        ).show();
                    }
                });
            }
        });
    });


    /**
     * Gelen tahsilat detay verisini formatlı bir HTML'e dönüştürür.
     * @param {Array} detaylar - API'den gelen detaylar dizisi.
     * @returns {string} Oluşturulan HTML içeriği.
     */
    function formatTahsilatDetay(detaylar) {
        if (detaylar.length === 0) {
            return '<div class="p-3 text-center text-muted">Bu tahsilat için detaylı bir borç eşleşmesi bulunamadı.</div>';
        }

        let html =
            '<div class="p-3 border rounded text-center"><h6 >:::TAHSİLAT DAĞILIMI:::</h6><ul class="list-group list-group-flush">';

        $.each(detaylar, function(index, detay) {
            // İsimden baş harfi alıyoruz (referanstaki gibi bir avatar oluşturmak için)
            // 'Anapara' için 'A', 'Gecikme' için 'G' gibi.
            const aciklama = detay.aciklama || '';
            const basHarf = aciklama.charAt(0).toUpperCase();

            // Baş harfe göre renk belirleyelim (isteğe bağlı, daha şık görünür)
            let bgColorClass = 'bg-soft-primary text-primary'; // Varsayılan renk
            if (aciklama.toLowerCase().includes('gecikme')) {
                bgColorClass = 'bg-soft-danger text-danger'; // Gecikme zammı için kırmızı tonları
            } else if (aciklama.toLowerCase().includes('anapara')) {
                bgColorClass = 'bg-soft-success text-success'; // Anapara için yeşil tonları
            }

            html += `
            <div class="d-flex align-items-center justify-content-between py-2 ${index < detaylar.length - 1 ? 'border-bottom' : ''}">
                <div class="d-flex align-items-center">
                    <!-- Baş Harf Avatarı -->
                    <div class="avatar-text avatar-md ${bgColorClass} rounded-circle me-3">
                        ${basHarf}
                    </div>
                    <!-- Açıklama ve Borç Adı -->
                    <div class="flex-grow-1 text-start">
                        <div class="fw-bold">${detay.aciklama }</div>
                        <p class="fs-12 text-muted mb-0"> ${detay.borc_aciklama || 'Belirtilmemiş'}</p>
                    </div>
                </div>
                <!-- Tutar ve Tarih -->
                <div class="text-end">
                    
                    <div class="fw-bold">${detay.odenen_tutar} ₺</div>
                    <span class="fs-12 text-muted">${detay.islem_tarihi}</span>
                </div>
            </div>
        `;
        });

        html += '</ul></div>';
        return html;
    }


    $(document).on('click', '#delete-tahsilat', function() {
        const id = $(this).data('id');
        deleteTahsilat(id);

    });

    /**Tahsilatı Silme
     * Bu fonksiyon, tahsilat silme işlemini gerçekleştirir.
     * @param {string} tahsilatId - Silinecek tahsilatın ID'si (şifrelenmiş).
     * @return {void}
     */
    function deleteTahsilat(tahsilatId) {
        swal.fire({

            title: 'Tahsilatı Sil',
            text: 'Bu tahsilatı silmek istediğinize emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'Hayır, İptal Et'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete_tahsilat',
                        id: tahsilatId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            swal.fire({
                                icon: 'success',
                                title: 'Başarılı',
                                text: response.message
                            }).then(() => {
                                table.row()
                                    .remove()
                                    .draw(); // Tahsilat silindikten sonra tabloyu güncelle
                            });
                        
                        } else {
                            alert('Hata: ' + response.message);
                        }
                    },
                    error: function() {
                        swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: 'Tahsilat silinirken bir hata oluştu. Lütfen tekrar deneyin.'
                        });
                    }
                });
            }
        });


    }
</script>