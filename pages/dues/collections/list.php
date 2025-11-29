<?php

use App\Services\Gate;
use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Date;

use Model\TahsilatModel;

$TahsilatModel = new TahsilatModel();



//$kisiler = $KisiModel->SiteKisiBorcOzet($_SESSION['site_id']);

$tumTahsilatlar = $TahsilatModel->getTumTahsilatlar($_SESSION['site_id']);

//Sayfaya erişim yetkisi kontrolü
//Eğer yetki yoksa yetki yok sayfasına yönlendir
Gate::authorizeOrDie('tahsilat_listele');


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


<style>

table tr td:has(> .p-3.border.rounded.text-center) {
    margin: 0 !important;
    padding:  5px 0 !important;

}
table tr td .p-3.border.rounded.text-center {
    background: #f2f5fa !important;
}

</style>

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
                    <table id="tahsilatlarTable" class="table" style="width:100%;">
                        <thead>
                            <tr >
                                <th>Makbuz No</th>
                                <th class="text-start">Ödeme Tarihi</th>
                                <th>Kişi / Daire</th>
                                <th>Açıklama / Kasa</th>
                                <th class="text-end">Tutar</th>
                                <th class="text-center" style="width:10%">Detay</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let url = "pages/dues/collections/api.php";
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
    window.onDataTablesReady(function() {
        table = initDataTable('#tahsilatlarTable',{
            processing: true,
            serverSide: true,
            ajax: '/pages/dues/collections/server_processing.php',
            columns: [
                { data: 0, name: 'makbuz_no' },
                { data: 1, name: 'odeme_tarihi' },
                { data: 2, name: 'kisi_daire' },
                { data: 3, name: 'aciklama_kasa' },
                { data: 4, name: 'tutar' },
                { data: 5, name: 'detay' }
            ],
            columnDefs: [
              { targets: 5, orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            pageLength: 25
        });

        // processing göstergesi için DataTables'in kendi elemanı kullanılacak

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
        const tahsilatId = $(this).data('id');
        row = $(this).closest('tr');
       
       
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
                        console.log('tahsilat silinme sonrası ' + response);
                        
                        if (response.status === 'success') {
                            row.remove();
                            swal.fire({
                                icon: 'success',
                                title: 'Başarılı',
                                text: response.message
                            })
                        
                        } else {
                            swal.fire({
                                icon: 'error',
                                title: 'Hata',
                                text: response.message
                            });
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


    });

    /**Tahsilatı Silme
     * Bu fonksiyon, tahsilat silme işlemini gerçekleştirir.
     * @param {string} tahsilatId - Silinecek tahsilatın ID'si (şifrelenmiş).
     * @return {void}
     */
    function deleteTahsilat(tahsilatId) {
        

    }
</script>
<style>
  .card-body { overflow-x: hidden; }
  #tahsilatlarTable { width: 100%; }
  .dataTables_processing { position: static !important; margin: .5rem 0; padding: .5rem 1rem; border-radius: 8px; background: #f8f9fa; box-shadow: 0 1px 2px rgba(0,0,0,.06); font-size: 14px; }
</style>
