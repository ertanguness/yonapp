<?php
require_once '../../../vendor/autoload.php';

use App\Helper\Date;
use App\Helper\Security;
use App\Helper\Helper;
use Model\TahsilatModel;
use Model\TahsilatOnayModel;
use Model\KisilerModel;
use Model\DairelerModel;

$Tahsilat = new TahsilatModel();
$TahsilatOnay = new TahsilatOnayModel();
$Kisi = new KisilerModel();
$Daire = new DairelerModel();

$id = Security::decrypt($_GET['id'] ?? 0);
$tahsilat = $TahsilatOnay->find($id);

$islenen_tahsilatlar = $Tahsilat->IslenenTahsilatlar($id);
$islenen_toplam_tahsilat = $TahsilatOnay->OnaylanmisTahsilatToplami($id);
$tahsilat_tamanlanma_orani =$tahsilat->tutar > 0 ? ($islenen_toplam_tahsilat / $tahsilat->tutar) * 100 : 0;
$tahsilat_son_hareket = Date::dmY($Tahsilat->SonHareketTarihi($id));

$red = max(255 - (2.55 * $tahsilat_tamanlanma_orani), 0); // Kırmızı değeri azalır
$green = min(2.55 * $tahsilat_tamanlanma_orani, 200); // Yeşil değeri artar
$progress_color = "rgb($red, $green, 0)"; // Dinamik renk

?>

<div class="modal-header">
    <h5 class="modal-title" id="modalTitleId">Tahsilat Onay Detayları</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="notes-box">
        <div class="notes-content">
            <form action="javascript:void(0);" id="addnotesmodalTitle">
                <div class="row g-0 m-3 p-3 align-items-center border border-dashed rounded-3 mb-4">
                    <div class="col-lg-8">
                        <div class="d-lg-flex align-items-center">
                            <div class="row g-4">
                                <div class="col-12">
                                    <a href="javascript:void(0);"
                                        class="d-block p-4 text-center border border-dashed border-soft-primary rounded position-relative">
                                        <div
                                            class="avatar-text avatar-md bg-soft-primary text-primary border-soft-primary position-absolute top-0 start-50 translate-middle">
                                            <i class="feather-airplay"></i>
                                        </div>
                                        <div>
                                            <div class="fs-12 text-muted mb-2">Daire</div>
                                            <h3><?php echo $Daire->DaireKodu($tahsilat->daire_id); ?></h3>
                                        </div>
                                    </a>
                                </div>

                            </div>




                            <div class="px-3">
                                <a href="javascript:void(0);" class="fs-14 fw-bold text-truncate-1-line">
                                    <?php echo $tahsilat->aciklama; ?> <span
                                        class="badge bg-gray-200 text-dark ms-2"></span></a>
                                <div class="fs-12 mt-3">
                                    <div class="hstack gap-2 text-muted mb-2">
                                        <div class="avatar-text avatar-sm">
                                            <i class="feather-calendar"></i>
                                        </div>
                                        <span class="text-truncate-1-line"><?php echo $tahsilat->islem_tarihi; ?></span>
                                    </div>
                                    <div class="hstack gap-2 text-muted mb-2">
                                        <div class="avatar-text avatar-sm">
                                            <i class="feather-user"></i>
                                        </div>
                                        <span
                                            class="text-truncate-1-line"><?php echo $Kisi->KisiAdi($tahsilat->kisi_id); ?></span>
                                    </div>
                                    <div class="hstack gap-2 text-muted mb-3">
                                        <div class="avatar-text avatar-sm">
                                            <i class="feather-briefcase"></i>
                                        </div>
                                        <span class="text-truncate-1-line">
                                            <strong><?php echo Helper::formattedMoney($tahsilat->tutar); ?></strong>

                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 storage-status mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h2 class="fs-10 fw-bold text-uppercase tx-spacing-1 mb-0">
                                <?php echo Helper::formattedMoney($islenen_toplam_tahsilat); ?></h2>
                            <div class="fs-10 text-muted text-uppercase">
                                <span
                                    class="text-truncate-1-line fw-bold"><?php echo Helper::formattedMoney($tahsilat->tutar); ?></span>
                            </div>
                        </div>
                        <div class="progress ht-5">

                            <div class="progress-bar" role="progressbar"
                                aria-valuenow="<?php echo $tahsilat_tamanlanma_orani; ?>" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width: <?php echo $tahsilat_tamanlanma_orani; ?>%; background-color: <?php echo $progress_color; ?>;">
                            </div>
                            <!-- <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="50" aria-valuemin="0"
                                aria-valuemax="100" style="width: <?php //echo $tahsilat_tamanlanma_orani; ?>%"></div>-->
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="me-1">
                                <i class="feather-clock fs-10 text-muted"></i>
                            </div>
                            <div class="fs-11 fw-normal text-muted text-truncate-1-line">Son İşlem Tarihi :
                                <?php echo $tahsilat_son_hareket; ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover datatables" id="islenenTahsilatlarTable">
                            <thead>
                                <tr>
                                    <th scope="col">Tahsilat Adı</th>
                                    <th scope="col">İşlem Tarih</th>
                                    <th scope="col">Durum</th>
                                    <th scope="col">Tutar</th>
                                    <th>Sil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($islenen_tahsilatlar as $iTahsilat): 
                                    $enc_id = Security::encrypt($iTahsilat->id);
                                    ?>

                                <tr>
                                    <td class="position-relative">
                                        <div
                                            class="ht-50 position-absolute start-0 top-50 translate-middle border-start border-5 border-success rounded">
                                        </div>
                                        <a href="javascript:void(0);">
                                            <?php echo $iTahsilat->tahsilat_tipi; ?>
                                        </a>
                                    </td>

                                    <td>
                                        <?php echo $iTahsilat->islem_tarihi; ?>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" class="badge bg-soft-success text-success">
                                            Onaylandı
                                        </a>
                                    </td>
                                    <td><a
                                            href="javascript:void(0);"><?php echo Helper::formattedMoney($iTahsilat->tutar); ?></a>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>" class="text-danger onayli-tahsilat-sil">
                                            <i class="feather-trash-2"></i>
                                        </a>
                                </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
<script>

    $("#islenenTahsilatlarTable").DataTable({
        "dom": 'tip',
        "language": {
            url: "/assets/js/tr.json",
        }
    });
</script>