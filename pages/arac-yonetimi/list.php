<?php

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$kisiListesi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'] ?? 0, 'arac', $id ?? null);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Araç Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Site Araçları</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/site-araclari-excel?format=xlsx" class="btn btn-outline-secondary">
                    <i class="bi bi-filetype-xlsx me-2"></i>

                </a>
                <a href="javascript:void(0)" id="btnYeniArac" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    Yeni Araç
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <?php
            $title = 'Site Araçları Listesi!';
            $text = 'Seçili siteye ait araçları görüntüleyip ekleme, düzenleme, silme ve ilgili siteye yeni araç tanımlama işlemlerinizi yapabilirsiniz.';
            require 'pages/components/alert.php';
            ?>

            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
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
                                            <th>Kayıt Yapan</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1;
                                        foreach ($kisiListesi as $row):
                                            if (empty($row->plaka)) {
                                                continue;
                                            }
                                            $enc_id = Security::encrypt($row->arac_id);
                                            $blok = $Bloklar->Blok($row->blok_id ?? null);
                                            $daire = $Daireler->DaireAdi($row->daire_id ?? null);
                                        ?>
                                            <tr data-id="<?= $enc_id ?>" class="text-center">
                                                <td class="sira-no"><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($blok->blok_adi ?? '-') ?></td>
                                                <td><?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></td>
                                                <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row->telefon ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row->plaka) ?></td>
                                                <td><?= htmlspecialchars($row->marka_model ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row->kayit_yapan ?? '-') ?></td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md btn-edit" title="Düzenle" data-id="<?= $enc_id ?>">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md btn-del" data-id="<?= $enc_id ?>" data-name="<?= htmlspecialchars($row->plaka) ?>">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
<script src="/src/blok-daire.js"></script>
<script src="/src/daire-kisi.js"></script>

<div id="carModal" class="custom-modal">
    <div class="modal fade-scale" id="mdlCar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/pages/arac-yonetimi/js/araclar.js"></script>