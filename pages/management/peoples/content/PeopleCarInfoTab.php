<?php

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'] ?? null);
?>
<div class="table-responsive">
    <table class="table table-hover datatables" id="aracList">
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
            ?>
                <tr data-id="<?php echo $enc_id; ?>" class="text-center">
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->daire_id ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->telefon ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->plaka) ?></td>
                    <td><?= htmlspecialchars($row->marka_model ?? '-') ?></td>
                    <td>
                        <div class="hstack gap-2">
                            <a href="index?p=management/peoples/manage&id=<?= $enc_id ?>" class="avatar-text avatar-md" title="Görüntüle">
                                <i class="feather-eye"></i>
                            </a>
                            <a href="index?p=management/peoples/manage&id=<?= $enc_id ?>" class="avatar-text avatar-md" title="Düzenle">
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