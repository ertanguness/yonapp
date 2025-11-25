<?php
$user_id = $_SESSION['user']->id;

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\IcraModel;
use Model\KisilerModel;

$Icra = new IcraModel();
$kisiler = new KisilerModel();

$icralarim = $Icra->SakinIcraBilgileri($user_id);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Dosyalarım</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İcra Dosyalarım</li>
        </ul>
    </div>
</div>

<div class="main-content">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover datatables">
                                <thead>
                                    <tr class="text-center">
                                        <th>İcra Dairesi</th>
                                        <th>Dosya No</th>
                                        <th>Borç Tutarı</th>
                                        <th>Durum</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($icralarim)) : ?>
                                        <?php foreach ($icralarim as $icra) :
                                            $enc_id = Security::encrypt($icra->id);

                                        ?>
                                            <tr class="text-center">
                                                <td><?= htmlspecialchars($icra->icra_dairesi ?? '-') ?></td>
                                                <td><?= htmlspecialchars($icra->dosya_no ?? '-') ?></td>
                                                <td><?= number_format($icra->borc_tutari, 2, ',', '.') ?> ₺</td>
                                                <td>
                                                    <?php
                                                    $durumKey = $icra->durum ?? 0;
                                                    $durum = Helper::Durum[$durumKey] ?? Helper::Durum[0];
                                                    ?>
                                                    <span class="badge <?= $durum['class']; ?>">
                                                        <i class="<?= $durum['icon']; ?>"></i> <?= htmlspecialchars($durum['label']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="icra-sakin-detay/<?php echo $enc_id; ?>"
                                                        class="avatar-text avatar-md route-link d-inline-flex justify-content-center align-items-center"
                                                        title="Detay">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                </td>


                                            </tr>
                                        <?php endforeach; ?>
                 
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>

                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            </div> <!-- /.col-12 -->
        </div> <!-- /.row-cards -->
</div> <!-- /.main-content -->