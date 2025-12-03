<?php

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;
use App\Helper\Helper;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$kisi = $Kisiler->DaireKisileri($id ?? null);
$silinen_kisi = $Kisiler->SilinenDaireKisileri($id ?? null);
$daire = $daireModel->DaireBilgisi($site_id, $id ?? 0);
$blocks = $Block->Blok($daire->blok_id ?? 0);

?>
<div class="main-content">

    <div class="row">
        <div class="container-xl">
            <!-- Blok Adı ve Daire No Bilgisi -->
            <div class="row mb-3 justify-content-center text-center p-1">
                <div class="col-md-4">
                    <strong>Blok Adı:</strong>
                    <?php echo isset($blocks->blok_adi) ? htmlspecialchars($blocks->blok_adi) : '-'; ?>
                </div>
                <div class="col-md-4">
                    <strong>Daire No:</strong>
                    <?php echo isset($daire->daire_no) ? htmlspecialchars($daire->daire_no) : '-'; ?>
                </div>
                <div class="col-md-4">
                    <strong>Daire Kodu:</strong>
                    <?php echo isset($daire->daire_kodu) ? htmlspecialchars($daire->daire_kodu) : '-'; ?>
                </div>
            </div>
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover  w-100 datatables" id="peoplesList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Adı Soyadı</th>
                                            <th>Telefon</th>
                                            <th>İkamet Türü</th>
                                            <th>Kullanım Durumu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;

                                        $tumKisiler = [];

                                        // Önce aktif olanları ekle
                                        foreach ($kisi as $row) {
                                            $row->satir_stil = ''; // normal satır
                                            $tumKisiler[] = $row;
                                        }

                                        // Sonra silinmiş olanları ekle
                                        if (!empty($silinen_kisi)) {
                                           
                                            foreach ($silinen_kisi as $row) {
                                                $row->satir_stil = 'table-danger';
                                                $tumKisiler[] = $row;
                                            }
                                        }

                                        foreach ($tumKisiler as $row):
                                            if (isset($row->is_header) && $row->is_header):
                                        ?>
                                              
                                            <?php
                                            else:
                                                $adi_soyadi = htmlspecialchars($row->adi_soyadi ?? '-');
                                                $telefon = htmlspecialchars($row->telefon ?? '-');
                                                $ikametTuruList = Helper::ikametTuru;
                                                $ikamet_turu = isset($ikametTuruList[$row->uyelik_tipi]) ? $ikametTuruList[$row->uyelik_tipi] : '-';
                                                $aktif_pasif = $row->aktif_mi
                                                    ? '<span class="text-success"><i class="fa fa-check-circle"></i> Aktif</span>'
                                                    : '<span class="text-danger"><i class="fa fa-times-circle"></i> Pasif</span>';
                                                if (isset($row->satir_stil) && $row->satir_stil === 'table-danger') {
                                                    $kullanim_durumu = '<span class="text-muted"><i class="fa fa-user-clock"></i> Eski Daire Sakini</span>';
                                                } else {
                                                    $kullanim_durumu = $row->kullanim_durumu
                                                        ? '<span class="text-success"><i class="fa fa-user-check"></i> Kullanıyor</span>'
                                                        : '<span class="text-secondary"><i class="fa fa-user-times"></i> Kullanmıyor</span>';
                                                }
                                            ?>
                                                <tr class="text-center <?php echo $row->satir_stil ?? ''; ?>">
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo $adi_soyadi; ?></td>
                                                    <td><?php echo $telefon; ?></td>
                                                    <td><?php echo $ikamet_turu; ?></td>
                                                    <td><?php echo $kullanim_durumu; ?></td>
                                                </tr>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>