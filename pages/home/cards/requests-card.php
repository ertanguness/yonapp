<?php
use App\Helper\Helper;
use Model\SikayetOneriModel;

$SikayetOneriModel = new SikayetOneriModel();
$requests = $SikayetOneriModel->getLatestWithUser($site_id, 5, 'Yeni');
?>

<div class="col-xxl-12 col-lg-6 card-wrapper" data-card="requests-card">
    <div class="card stretch stretch-full">

        <div class="card-header">
            <h5 class="card-title">Talep ve Öneriler</h5>
            <div class="card-header-action">
                <a href="?p=sikayet-oneri-listesi" class="btn btn-primary">Tümü</a>
                <span class="drag-handle" title="Taşı"><i class="bi bi-arrows-move"></i></span>
            </div>
        </div>

        <div class="card-body custom-card-action">
           <?php if(empty($requests)): ?>
               <div class="text-center text-muted p-3">Henüz talep veya öneri yok.</div>
           <?php else: ?>
               <?php foreach ($requests as $req): ?>
               <div class="d-md-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                       <div class="avatar-text avatar-lg rounded bg-soft-primary text-primary border-soft-primary me-3">
                        <?php echo Helper::getInitials($req->adi_soyadi ?? '?', 2) ?>
                    </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($req->title) ?></h6>
                            <p class="fs-12 text-muted mb-0">
                                <span class="text-primary"><?= htmlspecialchars($req->type) ?></span> - 
                                <?= htmlspecialchars($req->adi_soyadi ?? 'Bilinmeyen') ?> 
                                <?= !empty($req->daire_kodu) ? '('.htmlspecialchars($req->daire_kodu).')' : '' ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                        <?php
                            $statusClass = 'warning';
                            if ($req->status == 'Yeni') $statusClass = 'danger';
                            elseif ($req->status == 'Cevaplandı' || $req->status == 'Tamamlandı') $statusClass = 'success';
                            elseif ($req->status == 'İşlemde') $statusClass = 'info';
                        ?>
                        <span class="badge bg-soft-<?= $statusClass ?> text-<?= $statusClass ?>">
                            <?= htmlspecialchars($req->status) ?>
                        </span>
                        <div class="fs-11 text-muted mt-1"><?= date('d.m.Y H:i', strtotime($req->created_at)) ?></div>
                    </div>
                </div>  
                <hr class="border-dashed my-3">
               <?php endforeach; ?>
           <?php endif; ?>
        </div>
    </div>
</div>
