<?php
use App\Helper\Security;
use Model\PeriyodikBakimModel;
use Model\UserModel;

$PeriyodikBakimlar = new PeriyodikBakimModel();
$Kullanıcılar = new UserModel(); 
$BakimListesi = $PeriyodikBakimlar->PeriyodikBakimlar();

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Bakım ve Arıza Takip</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Periyodik Bakım Takip Listesi</li>
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
                 <a href="maliyet-faturalandirma" class="btn btn-success route-link">
                    <i class="feather-file-plus me-2"></i>
                    <span>İşlem Makbuzu Ekle</span>
                </a>
                <a href="periyodik-bakim-ekle" class="btn btn-primary route-link" >
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
    $title = "Periyodik Bakım Takip Listesi!";
    $text = "Periyodik bakım takip sistemi, cihazlarınızın düzenli bakımını takip etmenizi sağlar. 
             Bu sistem sayesinde bakım süreçlerini verimli yönetebilir ve cihazlarınızın ömrünü uzatabilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="periyodikBakimList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Sıra</th>
                                            <th>Bakım No</th>
                                            <th>Bakım Adı</th>
                                            <th>Bakım Yeri</th>
                                            <th>Başlangıç Tarihi</th>
                                            <th>Bitiş Tarihi</th>
                                            <th>Sorumlu Firma/Kişi</th>
                                            <th>Planlanan Bakım Durumu</th>
                                            <th>Kayıt Oluşturan</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($BakimListesi as $item):
                                        $enc_id = Security::encrypt($item->id);
                                    ?>
                                        <tr class="text-center">
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo htmlspecialchars($item->talep_no); ?></td>
                                            <td><?php echo htmlspecialchars($item->bakim_adi); ?></td>
                                            <td><?php echo htmlspecialchars($item->bakim_yeri); ?></td>
                                            <td>
                                                <?php
                                                $baslangic = !empty($item->sonBakim_tarihi) ? date('d-m-Y', strtotime($item->sonBakim_tarihi)) : '';
                                                echo htmlspecialchars($baslangic);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $bitis = !empty($item->planlanan_bakim_tarihi) ? date('d-m-Y', strtotime($item->planlanan_bakim_tarihi)) : '';
                                                echo htmlspecialchars($bitis);
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item->sorumlu_firma); ?></td>
                                           
                                            <td>
                                                <?php
                                                $today = new DateTime();
                                                $planDate = !empty($item->planlanan_bakim_tarihi) ? new DateTime($item->planlanan_bakim_tarihi) : null;

                                                if ($planDate) {
                                                    $diff = $today->diff($planDate);
                                                    $days = (int)$diff->format('%r%a');

                                                    if ($days > 0) {
                                                        // Bakım günü yaklaşmamış
                                                        echo '<span class="text-info">
                                                                <i class="feather-clock"></i> ' . $days . ' gün kaldı
                                                            </span>';
                                                    } elseif ($days === 0) {
                                                        // Bugün bakım günü
                                                        echo '<span class="text-warning">
                                                                <i class="feather-alert-circle"></i> Bakım günü!
                                                            </span>';
                                                    } else {
                                                        // Bakım günü geçmiş
                                                        echo '<span class="text-danger">
                                                                <i class="feather-x-circle"></i> Bakım geçmiş (' . abs($days) . ' gün önce)
                                                            </span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-secondary">
                                                            <i class="feather-help-circle"></i> Tarih yok
                                                        </span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $user = $Kullanıcılar->getUser($item->olusturan);
                                                echo htmlspecialchars($user->full_name ?? "Bilinmiyor");
                                                ?>
                                            </td>
                                            <td>
                                                <div class="hstack gap-2">
                                                
                                                    <a href="periyodik-bakim-duzenle/<?php echo $enc_id; ?>" class="avatar-text avatar-md">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                            data-name="<?php echo $item->talep_no ?>"
                                                            data-id="<?php echo $enc_id ?>"
                                                            class="avatar-text avatar-md sil-periyodikBakim"
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
