<?php 
use App\Helper\Helper;
use Model\SikayetOneriModel;
use App\Controllers\AuthController;
use App\Helper\Security;

AuthController::checkAuthentication();
$user = AuthController::user();
$siteId = $_SESSION['site_id'] ?? null;
$model = new SikayetOneriModel();
$rows = $model->listByUser((int)$user->id, $siteId ? (int)$siteId : null);

// Helper::dd($rows);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Şikayet / Önerilerim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Taleplerim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="/sakin/sikayet-oneri-ekle" class="btn btn-primary">
            <i class="feather-plus me-2"></i> Yeni Talep Ekle
        </a>
    </div>
</div>

<script>

</script>
<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Gönderdiğim Talepler</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-vcenter datatables">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th style="width: 40%;">İçerik</th>
                                            <th>Durum</th>
                                            <th>Oluşturulma</th>
                                            <th class="text-center" style="width: 10%;">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($rows)):
                                            $i = 1;
                                            foreach ($rows as $r):
                                                $enc_id = Security::encrypt($r->id);
                                                $statusBadge = ($r->status === 'Cevaplandı') ? 'success' : (($r->status === 'İnceleniyor') ? 'warning' : 'secondary');
                                                $contentShort = htmlspecialchars(($r->message ?? ''), ENT_QUOTES, 'UTF-8');
                                               
                                                if (mb_strlen($contentShort) > 80) { $contentShort = mb_substr($contentShort, 0, 77) . '...'; }
                                        ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($r->title ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo $contentShort ?: '-'; ?></td>
                                            <td><span class="badge bg-<?php echo $statusBadge; ?>"><?php echo htmlspecialchars($r->status ?? 'Yeni', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars($r->created_at ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="hstack gap-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md duyuru-goruntule" data-id="<?= $enc_id ?>">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="/sakin/sikayet-oneri-duzenle/<?php echo $enc_id; ?>" class="avatar-text avatar-md sikayet-oneri-duzenle" title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" data-id="<?php echo $enc_id; ?>" class="avatar-text avatar-md sikayet-oneri-sil">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
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

<script src="/pages/duyuru-talep/users/js/sikayet-oneri.js"></script>
