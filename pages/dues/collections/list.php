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
                    <table id="tahsilatlarTable" class="table datatables table-hover" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Makbuz No</th>
                                <th>İşlem Tarihi</th>
                                <th>Kişi / Daire</th>
                                <th>Açıklama / Kasa</th>
                                <th class="text-end">Tutar</th>
                                <th class="text-center">Detay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tumTahsilatlar as $tahsilat):
                                $sifreli_tahsilat_id = Security::encrypt($tahsilat->id);
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light-secondary">
                                            <?= htmlspecialchars($tahsilat->makbuz_no ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= Date::dmY($tahsilat->islem_tarihi) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($tahsilat->adi_soyadi) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($tahsilat->daire_kodu) ?></small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($tahsilat->aciklama ?: 'Genel Tahsilat') ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($tahsilat->kasa_adi) ?>
                                        </small>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?= Helper::formattedMoney($tahsilat->tutar) ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- DİKKAT: Artık bir link değil, sadece bir buton -->
                                        <button 
                                            class="btn btn-sm btn-outline-primary tahsilat-detay-goster"
                                            data-id="<?= $sifreli_tahsilat_id ?>"
                                            title="Tahsilat Detaylarını Görüntüle">
                                            <i class="feather-eye"></i>
                                        </button>
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

        if (row.child.isShown()) {
            // Zaten açıksa kapat
            row.child.hide();
            tr.removeClass('details-shown');
        } else {
            // Kapalıysa, "Yükleniyor..." göster ve AJAX isteği yap
            row.child('<div class="p-3 text-center">Detaylar yükleniyor...</div>').show();
            tr.addClass('details-shown');
            
            const tahsilatId = $button.data('id');

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
                        row.child('<div class="alert alert-danger m-3">Hata: ' + response.message + '</div>').show();
                    }
                },
                error: function() {
                    row.child('<div class="alert alert-danger m-3">Detaylar yüklenirken bir sunucu hatası oluştu.</div>').show();
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

    let html = '<div class="p-3 border rounded"><h6 class="mb-3">Tahsilat Dağılımı</h6><ul class="list-group list-group-flush">';

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
        // html += `
        //     <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
        //         <span>
        //             <i class="feather-arrow-right-circle me-2 text-primary"></i>
        //             ${detay.aciklama} <small>${detay.kayit_tarihi}</small>
        //         </span>
        //         <span class="badge bg-primary rounded-pill">${$.fn.dataTable.render.number('.', ',', 2, '', ' ₺').display(detay.odenen_tutar)}</span>
        //     </li>
        // `;

        html += `
            <div class="d-flex align-items-center justify-content-between py-2 ${index < detaylar.length - 1 ? 'border-bottom' : ''}">
                <div class="d-flex align-items-center">
                    <!-- Baş Harf Avatarı -->
                    <div class="avatar-text avatar-md ${bgColorClass} rounded-circle me-3">
                        ${basHarf}
                    </div>
                    <!-- Açıklama ve Borç Adı -->
                    <div>
                        <div class="fw-bold">${detay.borc_aciklama}</div>
                        <p class="fs-12 text-muted mb-0">İlgili Borç: ${detay.aciklama || 'Belirtilmemiş'}</p>
                    </div>
                </div>
                <!-- Tutar ve Tarih -->
                <div class="text-end">
                    <div class="fw-bold">${$.fn.dataTable.render.number('.', ',', 2, '', ' ₺').display(detay.odenen_tutar)}</div>
                    <span class="fs-12 text-muted">${new Date(detay.islem_tarihi).toLocaleDateString('tr-TR')}</span>
                </div>
            </div>
        `;
    });

    html += '</ul></div>';
    return html;
}
</script>