<?php

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;


$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

// Dış kapsamdaki $id (manage.php içinde decrypt edildi) mevcut kişi ID'si; yeni kayıt ise 0/false
$kisiId = isset($id) ? (int)$id : 0;
$kisi   = $kisiId ? $Kisiler->KisiBilgileri($kisiId) : null;

// Kişi yoksa yeni kayıt ekranındayız: uyarı ver ve listeyi göstermeyelim
if (!$kisi) {
    echo '<div class="alert alert-warning">Kişi seçilmedi veya henüz kaydedilmedi. Araç bilgileri ekleyebilmek için önce kişi kaydını tamamlayın.</div>';
    return;
}

// Kişi varsa sadece o kişiye ait araç kayıtlarını getir
$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'], 'arac', $kisiId);
?>
<div class="table-responsive">
    <table class="table table-hover datatables w-100" id="aracList">
        <thead>
            <tr class="text-center">
                <th>#</th>
                <th>Blok</th>
                <th>Daire</th>
                <th>Adı Soyadı</th>
                <th>Telefon</th>
                <th>Plaka</th>
                <th>Marka/Model</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($kisiListesi as $row):
                // Plaka boşsa bu satırı atla
                if (empty($row->plaka)) {
                    continue;
                }

                $enc_id = Security::encrypt($row->arac_id);
                $blok = $Bloklar->Blok($row->blok_id ?? null);
                $daire = $Daireler->DaireAdi($row->daire_id ?? null);

            ?>
                <tr data-id="<?php echo $enc_id; ?>" class="text-center">
                    <td class="sira-no"><?= $i++; ?></td>
                    <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                    <td><?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></td>
                    <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->telefon ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->plaka) ?></td>
                    <td><?= htmlspecialchars($row->marka_model ?? '-') ?></td>
                    <td>
                        <div class="hstack gap-2">
                            <a href="javascript:void(0);"
                                class="avatar-text avatar-md edit-car"
                                title="Düzenle"
                                data-id="<?= $enc_id ?>">
                                <i class="feather-edit"></i>
                            </a>


                            <a href="javascript:void(0);" data-name="<?php echo $row->plaka; ?>" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md delete-car" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $row->plaka; ?>">
                                <i class="feather-trash-2"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>
<script>
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-car');
        if (!editBtn) return;
        e.preventDefault();
        Pace.restart();
        const encId = editBtn.getAttribute('data-id');

        // Mutlak yol kullan (404 sorununu düzeltmek için başa / eklendi)
        const baseUrl = '/pages/management/peoples/content/AracModal.php';
        const firstUrl = baseUrl + '?id=' + encodeURIComponent(encId);
        fetch(firstUrl)
            .then(r => r.text())
            .then(html => {
                try {
                    let container = document.getElementById('modalContainer');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'modalContainer';
                        document.body.appendChild(container);
                    }
                    const oldModal = document.getElementById('aracEkleModal');
                    if (oldModal && oldModal.parentElement) {
                        oldModal.parentElement.removeChild(oldModal);
                    }
                    console.log('[AracModal] İlk fetch length:', html.length);
                    if (!/id=["']aracEkleModal["']/i.test(html)) {
                        console.warn('Arac modal id bulunamadı. Fallback denenecek. Snippet:', html.substring(0,180));
                        return fetch(baseUrl)
                            .then(r2 => r2.text())
                            .then(html2 => {
                                console.log('[AracModal] Fallback fetch length:', html2.length);
                                if (!/id=["']aracEkleModal["']/i.test(html2)) {
                                    console.error('Arac fallback da başarısız. Minimal modal inject ediliyor.');
                                    const minimal = `\n<div class="modal fade" id="aracEkleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Araç Bilgisi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="alert alert-danger">İçerik yüklenemedi. Lütfen sayfayı yenileyin.</div></div></div></div></div>`;
                                    container.insertAdjacentHTML('beforeend', minimal);
                                    const modalEl = document.getElementById('aracEkleModal');
                                    const aracModal = new bootstrap.Modal(modalEl);
                                    aracModal.show();
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire('Hata', 'Araç modal içeriği alınamadı. Yenileyin.', 'error');
                                    }
                                    return;
                                }
                                container.insertAdjacentHTML('beforeend', html2);
                                const modalEl2 = document.getElementById('aracEkleModal');
                                const aracModal2 = new bootstrap.Modal(modalEl2);
                                aracModal2.show();
                                if ($(modalEl2).find('.select2').length) {
                                    $(modalEl2).find('.select2').select2({ dropdownParent: $('#aracEkleModal') });
                                }
                            });
                    }
                    container.insertAdjacentHTML('beforeend', html);
                    const modalEl = document.getElementById('aracEkleModal');
                    if (!modalEl) throw new Error('Araç modal elementi yok');
                    const aracModal = new bootstrap.Modal(modalEl);
                    aracModal.show();
                    if ($(modalEl).find('.select2').length) {
                        $(modalEl).find('.select2').select2({ dropdownParent: $('#aracEkleModal') });
                    }
                } catch(err) {
                    console.error('Araç modal hata:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Hata', 'Araç modal açılırken bir hata oluştu.', 'error');
                    }
                }
            })
            .catch(error => console.error('Modal yüklenirken hata oluştu:', error));
    });
</script>