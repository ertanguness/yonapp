<?php 
use Model\DuyuruModel;
use App\Controllers\AuthController;

AuthController::checkAuthentication();
$Announcements = new DuyuruModel();
$rows = $Announcements->all();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyurular</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Duyurular</li>
        </ul>
    </div>
</div>

<div class="main-content">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Yönetim Duyuruları (Salt Okunur)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-vcenter datatables" id="duyurularTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th style="width:40%">İçerik</th>
                                        <th>Başlangıç</th>
                                        <th>Bitiş</th>
                                        <th>Durum</th>
                                        <th>Hedef Türü</th>
                                        <th>Hedef ID'ler</th>
                                        <th>Oluşturulma</th>
                                        <th>Silinme</th>
                                        <th>Silen Kullanıcı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): 
                                        $icerikOzet = mb_strimwidth(strip_tags($row->icerik ?? ''), 0, 160, '...');
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo (int)($row->id ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($row->baslik ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($icerikOzet ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->baslangic_tarihi ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->bitis_tarihi ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><span class="badge bg-secondary text-uppercase"><?php echo htmlspecialchars($row->durum ?? '-', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->target_type ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row->target_ids ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->olusturulma_tarihi ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->silinme_tarihi ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row->silen_kullanici ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
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
