<?php

use App\Helper\Security;
use App\Helper\Helper;

use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'], 'acil', $id ?? null);

?>

<div class="table-responsive">
    <table class="table table-hover datatables" id="acilDurumKisileriList">
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
                <tr data-id="<?php echo $enc_id; ?>" class="text-center">
                    <td class="sira-no"><?= $i++; ?></td>
                    <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                    <td><?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></td>
                    <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->acil_adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->acil_telefon) ?></td>
                    <td>
                        <?php
                        $relation = $row->acil_yakinlik ?? null;
                        $relationshipOptions = Helper::RELATIONSHIP;
                        echo isset($relationshipOptions[$relation]) ? $relationshipOptions[$relation] : '-';
                        ?>
                    </td>
                    <td>
                        <div class="hstack gap-2">
                            <a href="javascript:void(0);"
                                class="avatar-text avatar-md edit-acilDurumKisi"
                                title="Düzenle"
                                data-id="<?= $enc_id ?>">
                                <i class="feather-edit"></i>
                            </a>
                            <a href="javascript:void(0);" data-name="<?php echo $row->acil_adi_soyadi; ?>" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md delete-acilDurumKisi" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $row->acil_adi_soyadi; ?>">
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

        if (editBtn) {
            e.preventDefault();
            Pace.restart();

            const encId = editBtn.getAttribute('data-id');

            fetch('pages/management/peoples/content/AcilDurumModal.php?id=' + encodeURIComponent(encId))
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContainer').innerHTML = html;

                    const acilModal = new bootstrap.Modal(document.getElementById('acilDurumEkleModal'));
                    acilModal.show();

                    $(".select2").select2({
                        dropdownParent: $('#acilDurumEkleModal'),
                    });
                })
                .catch(error => console.error('Modal yüklenirken hata oluştu:', error));
        }
    });
</script>