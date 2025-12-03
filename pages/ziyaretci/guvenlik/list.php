<?php

use App\Helper\Security;
use Model\UserModel;
use Model\GuvenlikModel;
use Model\GuvenlikVardiyaModel;
use Model\GuvenlikGorevYeriModel;
use App\Helper\Date;

$Kullanıcılar = new UserModel();
$Guvenlik = new GuvenlikModel();
$vardiyaModel = new GuvenlikVardiyaModel();
$GörevYeriModel = new GuvenlikGorevYeriModel();

$GuvenlikVardiyalar = $Guvenlik->GuvenlikVardiyalari();
$Vardiyalar = $vardiyaModel->Vardiyalar();
$GorevYerleri = $GörevYeriModel->GorevYerleri();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Güvenlik Yönetimi</li>
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

                <a href="guvenlik-yeni-gorev-ekle" class="btn btn-primary route-link" >
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Görev Ekle</span>
                </a>
                <a href="personel-listesi" class="btn btn-info route-link" >
                    <i class="feather-user-plus me-2"></i>
                    <span>Personel Ekle</span>
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
    <?php /*
    $title = "Güvenlik Personeli";
    $text = "Burada tüm güvenlik personellerini görüntüleyebilir, ekleyebilir veya düzenleyebilirsiniz.";
    require_once 'pages/components/alert.php'; */
    ?>

    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover datatables" id="guvenlikList">
                    <thead>
                        <tr>
                            <th style="width:7%">Sıra</th>
                            <th style="width:20%">Personel</th>
                            <th style="width:15%">Telefon</th>
                            <th style="width:20%">Görev Yeri</th>
                            <th style="width:10%">Vardiya</th>
                            <th style="width:10%">Başlama Tarihi</th>
                            <th style="width:10%">Bitiş Tarihi</th>
                            <th style="width:8%">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sira = 0;
                        foreach ($GuvenlikVardiyalar as $item):
                            $enc_id = Security::encrypt($item->id);
                        ?>
                            <tr class="text-center <?= ($item->durum == 0 ? 'exit-hidden' : ''); ?>">
                                <td><?= htmlspecialchars(++$sira); ?></td>
                                <td><?= htmlspecialchars($item->personel_adi ?? '-'); ?></td>
                                <td><?= htmlspecialchars($item->personel_telefon ?? '-'); ?></td>
                                <td>
                                    <?php
                                    $gorevYeriAdi = '-';
                                    if (!empty($item->gorev_yeri_id)) {
                                        foreach ($GorevYerleri as $gorevYeri) {
                                            if ($gorevYeri->id == $item->gorev_yeri_id) {
                                                $gorevYeriAdi = htmlspecialchars($gorevYeri->ad);
                                                break;
                                            }
                                        }
                                    }
                                    echo $gorevYeriAdi;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $vardiyaAdi = '-';
                                    if (!empty($item->vardiya_id)) {
                                        foreach ($Vardiyalar as $vardiya) {
                                            if ($vardiya->id == $item->vardiya_id) {
                                                $vardiyaAdi = htmlspecialchars($vardiya->vardiya_adi) . ' (' . 
                                                    htmlspecialchars($vardiya->vardiya_baslangic) . ' - ' . 
                                                    htmlspecialchars($vardiya->vardiya_bitis) . ')';
                                                break;
                                            }
                                        }
                                    }
                                    echo $vardiyaAdi;
                                    ?>
                                </td>
                                <td>
                                    <?= !empty($item->baslama_tarihi) ? Date::dmY($item->baslama_tarihi, 'd.m.Y') : '-'; ?>
                                </td>
                                <td>
                                    <?= !empty($item->bitis_tarihi) ? Date::dmY($item->bitis_tarihi, 'd.m.Y') : '-'; ?>
                                </td>
                                
                                <td>
                                    <div class="hstack gap-2">
                                        <a href="guvenlik-gorev-duzenle/<?= $enc_id; ?>" class="avatar-text avatar-md">
                                            <i class="feather-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);"
                                            data-name="<?= htmlspecialchars($item->personel_adi ?? '-'); ?>"
                                            data-id="<?= $enc_id; ?>"
                                            class="avatar-text avatar-md sil-guvenlik">
                                            <i class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
