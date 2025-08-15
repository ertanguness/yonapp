<?php

use App\Helper\Security;
use Model\BakimModel;
use Model\UserModel;

$Bakimlar = new BakimModel();
$Kullanıcılar = new UserModel();

$Bakim = $Bakimlar->Bakimlar();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Bakım ve Arıza Takip</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bakım Takip Listesi</li>
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
                <a href="#" class="btn btn-success route-link" data-page="repair/cost/manage">
                    <i class="feather-file-plus me-2"></i>
                    <span>İşlem Makbuzu Ekle</span>
                </a>
                <a href="#" class="btn btn-primary route-link" data-page="repair/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İşlem</span>
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
    $title = "Bakım ve Arıza Takip Listesi!";
    $text = "Bakım takip sistemi, cihazlarınızın bakımını ve arızalarını takip etmenizi sağlar. 
             Bu sistem sayesinde bakım süreçlerini verimli yönetebilir ve arızaları hızlıca giderebilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">

                                <table class="table table-hover datatables" id="BakimList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Talep No</th>
                                            <th>Talep Türü</th>
                                            <th>Talep Eden</th>
                                            <th>Talep Tarihi</th>
                                            <th>Atanan Firma/Kişi</th>
                                            <th>Durum</th>
                                            <th>Atama Durumu</th>
                                            <th>Kayıt Oluşturan</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($Bakim as $item):
                                            $enc_id = Security::encrypt($item->id);
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo htmlspecialchars($item->talep_no); ?></td>
                                                <td>
                                                    <?php
                                                    switch ($item->kategori) {
                                                        case 'Bakım':
                                                            echo '<span class="text-primary"><i class="feather-tool"></i> Bakım</span>';
                                                            break;
                                                        case 'Onarım':
                                                            echo '<span class="text-warning"><i class="feather-refresh-cw"></i> Onarım</span>';
                                                            break;
                                                        case 'Arıza':
                                                            echo '<span class="text-danger"><i class="feather-alert-triangle"></i> Arıza</span>';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($item->kategori);
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item->talep_eden); ?></td>
                                                <td>
                                                    <?php
                                                    // Tarihi gün-ay-yıl formatında göster
                                                    $date = date('d-m-Y', strtotime($item->talep_tarihi));
                                                    echo htmlspecialchars($date);
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item->firma_kisi); ?></td>
                                                <td>
                                                    <?php
                                                    switch ($item->durum) {
                                                        case 0:
                                                            echo '<span class="text-secondary"><i class="feather-clock"></i> Bekliyor</span>';
                                                            break;
                                                        case 1:
                                                            echo '<span class="text-warning"><i class="feather-loader"></i> İşlemde</span>';
                                                            break;
                                                        case 2:
                                                            echo '<span class="text-success"><i class="feather-check-circle"></i> Tamamlandı</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="text-muted">Bilinmiyor</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($item->atama_durumu == 1): ?>
                                                        <span class="text-success">
                                                            <i class="feather-check-circle"></i> Evet
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-secondary">
                                                            <i class="feather-x-circle"></i> Hayır
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $user = $Kullanıcılar->getUser($item->olusturan);
                                                    echo htmlspecialchars($user->full_name ?? "Bilinmiyor");
                                                    ?>
                                                </td>

                                                <td>
                                                    <div class="hstack gap-2">

                                                        <a href="index?p=repair/manage&id=<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            data-name="<?php echo $item->talep_no ?>"
                                                            data-id="<?php echo $enc_id ?>"
                                                            class="avatar-text avatar-md sil-Bakim"
                                                            data-id="<?php echo $enc_id; ?>"
                                                            data-name="<?php echo $item->talep_no; ?>">
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