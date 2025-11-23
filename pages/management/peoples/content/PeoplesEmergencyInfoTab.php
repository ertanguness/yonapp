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
    fetch('pages/management/peoples/content/AcilDurumModal.php?id=' + encodeURIComponent(encId))
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalContainer').innerHTML = html;
            const acilModal = new bootstrap.Modal(document.getElementById('acilDurumEkleModal'));
            acilModal.show();
            $(".select2").select2({ dropdownParent: $('#acilDurumEkleModal') });
        })
        .catch(err => console.error('Modal yüklenirken hata oluştu:', err));
});
</script>