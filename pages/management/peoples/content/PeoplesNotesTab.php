<?php

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisiNotModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();
$Notlar = new KisiNotModel();

$kisiId = isset($id) ? (int)$id : 0;
$kisi   = $kisiId ? $Kisiler->KisiBilgileri($kisiId) : null;

if (!$kisi) {
    echo '<div class="alert alert-warning">Kişi seçilmedi veya henüz kaydedilmedi. Not ekleyebilmek için önce kişi kaydını tamamlayın.</div>';
    return;
}

$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'], 'not', $kisiId);
?>
<div class="table-responsive">
    <table class="table table-hover datatables w-100" id="kisiNotList">
        <thead>
            <tr class="text-center">
                <th>#</th>
                <th>Blok</th>
                <th>Daire</th>
                <th>Adı Soyadı</th>
                <th>Telefon</th>
                <th>Not</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($kisiListesi as $row):
                if (empty($row->icerik)) {
                    continue;
                }
                $enc_id = Security::encrypt($row->not_id);
                $blok = $Bloklar->Blok($row->blok_id ?? null);
                $daire = $Daireler->DaireAdi($row->daire_id ?? null);
            ?>
                <tr data-id="<?php echo $enc_id; ?>" class="text-center">
                    <td class="sira-no"><?= $i++; ?></td>
                    <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                    <td><?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></td>
                    <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->telefon ?? '-') ?></td>
                    <td class="text-start"><?= htmlspecialchars($row->icerik) ?></td>
                    <td>
                        <div class="hstack gap-2">
                            <a href="javascript:void(0);"
                               class="avatar-text avatar-md edit-note"
                               title="Düzenle"
                               data-id="<?= $enc_id ?>">
                                <i class="feather-edit"></i>
                            </a>
                            <a href="javascript:void(0);" data-name="<?= htmlspecialchars(substr($row->icerik,0,30)) ?>" data-id="<?= $enc_id ?>" class="avatar-text avatar-md delete-note">
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
        const editBtn = e.target.closest('.edit-note');
        if (!editBtn) return;
        e.preventDefault();
        Pace.restart();
        const encId = editBtn.getAttribute('data-id');
        const baseUrl = '/pages/management/peoples/content/KisiNotModal.php';
        const firstUrl = baseUrl + '?id=' + encodeURIComponent(encId);
        fetch(firstUrl)
            .then(r => r.text())
            .then(html => {
                let container = document.getElementById('modalContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'modalContainer';
                    document.body.appendChild(container);
                }
                const oldModal = document.getElementById('kisiNotModal');
                if (oldModal && oldModal.parentElement) {
                    oldModal.parentElement.removeChild(oldModal);
                }
                container.insertAdjacentHTML('beforeend', html);
                const modalEl = document.getElementById('kisiNotModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Hata', 'Not modal açılırken bir hata oluştu.', 'error');
                }
            });
    });
</script>
