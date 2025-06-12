<?php
use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$kisi = $Kisiler ->SiteKisileriJoin($_SESSION['site_id'] ?? null);

$ikametTuru = [
    '1' => 'Kat Maliki',
    '2' => 'Kiracı',
    '3' => 'Çalışan',
    '4' => 'Misafir',
    '5' => 'Mirasçı'
];

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
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
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php'
                ?>

                <a href="#" class="btn btn-primary route-link" data-page="management/peoples/manage">
                    <i class="feather-plus me-2"></i>
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
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">

                                <table class="table table-hover datatables" id="peoplesList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Blok Adı</th>
                                            <th>Daire No</th>
                                            <th>Adı Soyadı</th>
                                            <th>Telefon</th>
                                            <th>Araç Plakası</th>
                                            <th>İkamet Türü</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($kisi as $row):
                                            $enc_id = Security::encrypt($row->id);
                                            $blok = $Bloklar->Blok(isset($row->blok_id) ? htmlspecialchars($row->blok_id) : '-');
                                            $daire_no = isset($row->daire_id) ? htmlspecialchars($row->daire_id) : '-';
                                            $adi_soyadi = isset($row->adi_soyadi) ? htmlspecialchars($row->adi_soyadi) : '-';
                                            $telefon = isset($row->telefon) ? htmlspecialchars($row->telefon) : '-';
                                            $ikamet_turu = isset($ikametTuru[$row->uyelik_tipi]) ? $ikametTuru[$row->uyelik_tipi] : '-';
                                            ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $blok->blok_adi; ?></td>
                                                <td><?php echo $daire_no; ?></td>
                                                <td><?php echo $adi_soyadi; ?></td>
                                                <td><?php echo $telefon; ?></td>
                                                <td><?php echo "Plaka Tablosundan gelecek" ?></td>
                                                <td><?php echo $ikamet_turu; ?></td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="index?p=management/peoples/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" title="Görüntüle">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="index?p=management/peoples/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md" title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-name="<?php echo $adi_soyadi; ?>" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md delete-peoples" data-id="<?php echo $enc_id; ?>" data-name="<?php echo $adi_soyadi; ?>">
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
</div>