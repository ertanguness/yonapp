<?php

use App\Helper\Security;
use App\Helper\Helper;


use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'], 'acil');
$relationOptions = [
    1  => "Anne",
    2  => "Baba",
    3  => "Kardeş",
    4  => "Eş",
    5  => "Çocuk",
    6  => "Dede",
    7  => "Babaanne",
    8  => "Anneanne",
    9  => "Amca",
    10 => "Dayı",
    11 => "Teyze",
    12 => "Hala",
    13 => "Kuzen",
    14 => "Diğer"
];
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
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                    <td><?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></td>
                    <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->acil_adi_soyadi ?? '-') ?></td>
                    <td><?= htmlspecialchars($row->acil_telefon) ?></td>
                    <td>
                      <?php
                        $relation = $row->acil_yakinlik ?? null;
                        echo isset($relationOptions[$relation]) ? $relationOptions[$relation] : '-';
                        ?>
                    </td>
                    <td>
                        <div class="hstack gap-2">
                            <a href="index?p=management/peoples/manage&id=<?= $enc_id ?>" class="avatar-text avatar-md" title="Görüntüle">
                                <i class="feather-eye"></i>
                            </a>
                            <a href="index?p=management/peoples/manage&id=<?= $enc_id ?>" class="avatar-text avatar-md" title="Düzenle">
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
