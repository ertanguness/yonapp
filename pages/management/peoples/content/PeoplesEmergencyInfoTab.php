<?php
use App\Helper\Security;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$kisiId = isset($id) ? (int)$id : 0;
$kisi   = $kisiId ? $Kisiler->KisiBilgileri($kisiId) : null;

if (!$kisi) {
    echo '<div class="alert alert-warning">Kişi seçilmedi veya henüz kaydedilmedi. Acil durum kişileri ekleyebilmek için önce kişi kaydını tamamlayın.</div>';
    return;
}

$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'], 'acil', $kisiId);
?>
<div class="table-responsive">
    <table class="table table-hover datatables w-100" id="acilDurumKisileriList">
        <thead>
            <tr class="text-center">
                <th>#</th>
                <th>Blok</th>
                <th>Daire</th>
                <th>Site/Apartman Sakini</th>
                <th>Acil Durum Kişisi</th>
                <th>Telefon</th>
                <th>Yakınlık Derecesi</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($kisiListesi as $row):
                $enc_id = Security::encrypt($row->acil_id);
                $blok = $Bloklar->Blok($row->blok_id ?? null);
                $daire = $Daireler->DaireAdi($row->daire_id ?? null);
            ?>
            <tr data-id="<?= $enc_id; ?>" class="text-center">
                <td class="sira-no"><?= $i++; ?></td>
                <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                <td><?= htmlspecialchars(is_object($daire) ? ($daire->daire_no ?? '-') : '-') ?></td>
                <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                <td><?= htmlspecialchars($row->acil_adi_soyadi ?? '-') ?></td>
                <td><?= htmlspecialchars($row->acil_telefon ?? '-') ?></td>
                <td><?= Helper::RELATIONSHIP[$row->acil_yakinlik ?? ''] ?? '-' ?></td>
                <td>
                    <div class="hstack gap-2">
                        <a href="javascript:void(0);" class="avatar-text avatar-md edit-acilDurumKisi" title="Düzenle" data-id="<?= $enc_id ?>">
                            <i class="feather-edit"></i>
                        </a>
                        <a href="javascript:void(0);" data-name="<?= htmlspecialchars($row->acil_adi_soyadi ?? '-') ?>" data-id="<?= $enc_id; ?>" class="avatar-text avatar-md delete-acilDurumKisi">
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
    const editBtn = e.target.closest('.edit-acilDurumKisi');
    if (!editBtn) return;
    e.preventDefault();
    Pace.restart();
    const encId = editBtn.getAttribute('data-id');
    const baseUrl = '/pages/management/peoples/content/AcilDurumModal.php';
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
                // Eski modalı kaldır (varsa)
                const oldModal = document.getElementById('acilDurumEkleModal');
                if (oldModal && oldModal.parentElement) {
                    oldModal.parentElement.removeChild(oldModal);
                }
                // Debug: gelen içerik
                console.log('[AcilDurumModal] İlk fetch length:', html.length);
                // Gelen HTML modal id'sini içeriyor mu kontrol et; yoksa fallback dene
                if (!/id=["']acilDurumEkleModal["']/i.test(html)) {
                    console.warn('Beklenen modal id bulunamadı. İlk deneme başarısız. Snippet:', html.substring(0,180));
                    // Fallback ikinci deneme (id parametresiz)
                    return fetch(baseUrl)
                        .then(r2 => r2.text())
                        .then(html2 => {
                            console.log('[AcilDurumModal] Fallback fetch length:', html2.length);
                            if (!/id=["']acilDurumEkleModal["']/i.test(html2)) {
                                console.error('Fallback da modal içermiyor. Minimal modal inject edilecek.');
                                const minimal = `\n<div class="modal fade" id="acilDurumEkleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Acil Durum Bilgisi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="alert alert-danger">İçerik yüklenemedi. Lütfen sayfayı yenileyin.</div></div></div></div></div>`;
                                container.insertAdjacentHTML('beforeend', minimal);
                                const modalEl = document.getElementById('acilDurumEkleModal');
                                const acilModal = new bootstrap.Modal(modalEl);
                                acilModal.show();
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire('Hata', 'Modal içeriği alınamadı. Lütfen sayfayı yenileyin.', 'error');
                                }
                                return;
                            }
                            container.insertAdjacentHTML('beforeend', html2);
                            const modalEl2 = document.getElementById('acilDurumEkleModal');
                            const acilModal2 = new bootstrap.Modal(modalEl2);
                            acilModal2.show();
                            if ($(modalEl2).find('.select2').length) {
                                $(modalEl2).find('.select2').select2({ dropdownParent: $('#acilDurumEkleModal') });
                            }
                        });
                }
                // Yeni HTML'i ekle
                container.insertAdjacentHTML('beforeend', html);
                const modalEl = document.getElementById('acilDurumEkleModal');
                if (!modalEl) throw new Error('Modal element bulunamadı');
                const acilModal = new bootstrap.Modal(modalEl);
                acilModal.show();
                // Select2 init (varsa)
                if ($(modalEl).find('.select2').length) {
                    $(modalEl).find('.select2').select2({ dropdownParent: $('#acilDurumEkleModal') });
                }
            } catch(innerErr) {
                console.error('Modal işlem hatası:', innerErr);
                Swal && Swal.fire ? Swal.fire('Hata', 'Modal açılırken bir hata oluştu.', 'error') : console.error('SweetAlert2 yok');
            }
        })
        .catch(err => console.error('Modal yüklenirken hata oluştu:', err));
});
</script>