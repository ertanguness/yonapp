<?php

use App\Helper\Date;
use App\Helper\Helper;
use Model\BloklarModel;
use Model\KisilerModel;
use App\Helper\Security;
use Model\DairelerModel;


$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();
$kisi = $Kisiler->SiteKisileriJoin($_SESSION['site_id'] ?? null);

?>
<style>
  #siteSakiniDetayOffcanvas {
    z-index: 1060 !important;
  }
</style>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Sakinler</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
               // require_once 'pages/components/download.php'
                ?>
           
              <a href="/program-giris-bilgileri" class="btn btn-icon has-tooltip tooltip-bottom"
                   data-tooltip="Program giriş bilgileri">
                    <i class="feather-log-in me-2"></i>
                    Program Giriş Bilgileri
                </a>
                <a href="/excelden-site-sakini-yukle" class="btn btn-icon has-tooltip tooltip-bottom"
                   data-tooltip="Site Sakinlerini Excelden Yükle">
                    <i class="feather-upload me-2"></i>
                    Excelden Yükle
                </a>
      
  
                <a href="/site-sakini-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus"></i>
                    <span>Yeni Kişi Ekle</span>

                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Site Sakinleri Listesi!";
    $text = "Seçili siteye ait Site Sakinlerini görüntüleyip ekleme, düzenleme, silme ve ilgili siteye yeni Sakin(Kişi) tanımlama işlemlerinizi  yapabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards mb-5">
                <div class="col-12">
                    <div class="card">
                    
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">

                                <table class="table table-hover datatables" id="peoplesList">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="all">#</th>
                                            <th class="all">Blok Adı</th>
                                            <th class="all">Daire No</th>
                                            <th class="all">Adı Soyadı</th>
                                            <th>Telefon</th>
                                            <th>Araç Plakası</th>
                                            <th>İkamet Türü</th>
                                            <th data-filter="date">Giriş Tarihi</thd>
                                            <th>Çıkış Tarihi</th>
                                            <th>Durumu</th>
                                            <th class="all">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($kisi as $row):
                                            $daire = $Daireler->DaireAdi($row->daire_id ?? null);

                                            $enc_id = Security::encrypt($row->id);
                                            $blok = $Bloklar->Blok(isset($row->blok_id) ? htmlspecialchars($row->blok_id) : '-');
                                            $daire_no = is_object($daire) ? htmlspecialchars($daire->daire_no) : '-';
                                            $adi_soyadi = isset($row->adi_soyadi) ? htmlspecialchars($row->adi_soyadi) : '-';
                                            $telefon = isset($row->telefon) && !empty($row->telefon)
                                                ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '+90 $1 $2 $3 $4', preg_replace('/\D/', '', $row->telefon))
                                                : '-';
                                            $ikametTuruList = Helper::ikametTuru;
                                            $ikamet_turu =  $ikametTuruList[$row->uyelik_tipi];

                                            $giris_tarihi = !empty($row->giris_tarihi) && $row->giris_tarihi != '0000-00-00'
                                                ? Date::dmY($row->giris_tarihi)
                                                : '-';

                                            $cikis_tarihi = !empty($row->cikis_tarihi) && $row->cikis_tarihi != '0000-00-00'
                                                ? Date::dmY($row->cikis_tarihi)
                                                : '-';

                                            /** Çıkış Tarihi dolu ise pasif badge yap */
                                            if (!empty($row->cikis_tarihi) && $row->cikis_tarihi != '0000-00-00') {
                                                $durum = '<span class="badge text-danger border border-dashed border-gray-500">Pasif</span>';
                                            }else{
                                                $durum = '<span class="badge text-teal border border-dashed border-gray-500">Aktif</span>';
                                            }

                                            $plaka = !empty($row->plaka_listesi)
                                                ? nl2br(htmlspecialchars_decode($row->plaka_listesi))
                                                : '-';
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $blok->blok_adi; ?></td>
                                                <td><?php echo $daire_no; ?></td>
                                                <td><?php echo $adi_soyadi; ?></td>
                                                <td><?php echo $telefon; ?></td>
                                                <td><?php echo $plaka; ?></td>
                                                <td><?php echo $ikamet_turu; ?></td>
                                                <td><?php echo $giris_tarihi; ?></td>
                                                <td><?php echo $cikis_tarihi; ?></td>
                                                <td><?php echo $durum; ?></td>
                                                
                                                <td style="width: 7%;">
                                                    <div class="hstack gap-1">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md opensiteSakiniDetay" data-id="<?= $enc_id ?>">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="/site-sakini-duzenle/<?= $enc_id ?>" class="avatar-text avatar-md" title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?= $adi_soyadi ?>" data-id="<?= $enc_id ?>" class="avatar-text avatar-md delete-peoples">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
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
    <div id="siteSakiniDetay"></div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.opensiteSakiniDetay');
            if (target) {
                const id = target.getAttribute('data-id');
                Pace.restart(); // varsa

                fetch('pages/management/peoples/content/siteSakiniDetay.php?id=' + id)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('siteSakiniDetay').innerHTML = html;
                        const canvasElement = document.getElementById('siteSakiniDetayOffcanvas');

                        if (canvasElement) {
                            const offcanvasInstance = new bootstrap.Offcanvas(canvasElement);
                            offcanvasInstance.show();
                        } else {
                            alert('Detay paneli yüklenemedi. Lütfen tekrar deneyin.');
                            console.error("Offcanvas elementi bulunamadı.");
                        }
                    })
                    .catch(error => {
                        console.error('Detay yüklenemedi:', error);
                        alert('Bir hata oluştu.');
                    });
            }
        });
    });
</script>
