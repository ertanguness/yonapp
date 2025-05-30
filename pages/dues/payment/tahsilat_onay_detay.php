<?php
require_once '../../../vendor/autoload.php';

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
                            <h2 class="fs-10 fw-bold text-uppercase tx-spacing-1 mb-0">Storage</h2>
                            <div class="fs-10 text-muted text-uppercase">
                                <span class="text-truncate-1-line">286.45GB used</span>
                            </div>
                        </div>
                        <div class="progress ht-5">
                            <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="50" aria-valuemin="0"
                                aria-valuemax="100" style="width: 26%"></div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="me-1">
                                <i class="feather-clock fs-10 text-muted"></i>
                            </div>
                            <div class="fs-11 fw-normal text-muted text-truncate-1-line">Last Activity: 36 Mins Ago
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Tahsilat Adı</th>
                                    <th scope="col">İşlem Tarih</th>
                                    <th scope="col">Durun</th>
                                    <th scope="col">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($islenen_tahsilatlar as $iTahsilat): ?>

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
                                            açıklama buraya
                                        </a>
                                    </td>
                                    <td><a href="javascript:void(0);"><?php echo Helper::formattedMoney($iTahsilat->tutar); ?></a></td>
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