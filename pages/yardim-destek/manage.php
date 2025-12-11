<?php require_once dirname(__DIR__, 2) . '/configs/bootstrap.php'; ?>
<?php
use Model\DestekModel;
use Model\DestekMesajModel;
use App\Helper\Security;
use App\Helper\Helper;

$Threads = new DestekModel();
$Messages = new DestekMesajModel();
$id = $id ?? 0;
$thread = $id ? $Threads->find($id, true) : null;

//Helper::dd($thread);


// Mesajları ve ilk yetkili yanıtını kontrol et
$messages = [];
$lastSenderSupport = false;
if ($thread) {
    $messages = $Messages->findWhere(['talep_id' => (int)($thread->id ?? 0)], 'id ASC');
    if (!empty($messages)) {
        $last = $messages[count($messages) - 1];
        $lastSenderSupport = (($last->gonderen_tip ?? '') === 'destek');
    }
}
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yardım & Destek</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Destek</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="row row-deck row-cards mb-5">
            <div class="col-12">

                <?php if ($thread): ?>
                    <?php $isClosed = in_array(strtolower($thread->durum ?? ''), ['kapandi','kapandı']); ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between w-100">
                                <div>
                                    <h6 class="mb-0">#<?= (int)($thread->id ?? 0); ?> - <?= htmlspecialchars($thread->konu ?? ''); ?></h6>
                                    <span class="text-muted">Durum: <strong><?= htmlspecialchars($thread->durum ?? 'Açık'); ?></strong></span>
                                </div>
                                <?php if (!$isClosed): ?>
                                    <button type="button" class="btn btn-outline-danger" id="closeThreadBtn" data-id="<?= Security::encrypt((int)($thread->id ?? 0)); ?>">Bildirimi Kapat</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body custom-card-action">
                            <?php if ($isClosed): ?>
                                <div class="alert alert-warning">Bu destek bildirimi kapalıdır. Farklı bir sorun için yeni bildirim oluşturun.</div>
                            <?php endif; ?>

                            <ul class="list-unstyled mb-0 activity-feed-1">
                                <?php foreach ($messages as $m): ?>
                                    <?php
                                        $isSupport = ($m->gonderen_tip ?? 'musteri') === 'destek';
                                        $feedClass = $isSupport ? 'feed-item-success' : 'feed-item-primary';
                                        $badgeClass = $isSupport ? 'text-success' : 'text-teal';
                                        $ts = isset($m->tarih) ? strtotime($m->tarih) : 0;
                                        $ago = '';
                                        if ($ts) {
                                            $diff = max(0, time() - $ts);
                                            if ($diff < 3600) { $ago = floor($diff/60) . ' dk önce'; }
                                            elseif ($diff < 86400) { $ago = floor($diff/3600) . ' saat önce'; }
                                            else { $ago = floor($diff/86400) . ' gün önce'; }
                                        } else { $ago = ''; }
                                    ?>
                                    <li class="feed-item <?= $feedClass; ?>">
                                        <div class="d-flex gap-4 justify-content-between">
                                            <div>
                                                <div class="mb-2 text-truncate-1-line">
                                                    <a href="javascript:void(0)" class="fw-semibold text-dark">
                                                        <?= $isSupport ? 'Yetkili Yanıtı' : 'Müşteri Mesajı'; ?>
                                                    </a>
                                                </div>
                                                <p class="fs-12 text-muted mb-3 text-truncate-2-line">
                                                    <?= nl2br(htmlspecialchars($m->mesaj ?? '')); ?>
                                                </p>
                                                <div>
                                                    <a href="javascript:void(0)" class="badge <?= $badgeClass; ?> border border-dashed border-gray-500"><?= $isSupport ? 'destek' : 'musteri'; ?></a>
                                                </div>
                                            </div>
                                            <div class="fs-10 fw-medium text-uppercase text-muted text-nowrap"><?= $ago; ?></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (!$isClosed && $lastSenderSupport): ?>
                                 <hr class="my-4" />
                                 <form id="replyForm">
                                     <input type="hidden" name="thread_id" value="<?= Security::encrypt((int)($thread->id ?? 0)); ?>">
                                     <div class="mb-2">
                                         <label class="form-label">Yanıtınız</label>
                                         <textarea class="form-control" name="message" rows="4" placeholder="Mesajınızı yazın..."></textarea>
                                     </div>
                                     <button type="button" class="btn btn-primary" id="sendReplyBtn">Yanıt Gönder</button>
                                 </form>
                            <?php elseif (!$isClosed && !$lastSenderSupport): ?>
                                <div class="alert alert-info mt-4">Bildirimi ilettiniz. Müşteri hizmetleri yanıt verene kadar cevap yazma alanı gizlidir.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <script src="/pages/yardim-destek/js/thread.js?<?= filemtime('pages/yardim-destek/js/thread.js') ?>"></script>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Yeni Destek Bildirimi</h6></div>
                        <div class="card-body">
                            <form id="createThreadForm">
                                <div class="mb-3">
                                    <label class="form-label">Konu</label>
                                    <input type="text" class="form-control" name="subject" placeholder="Örn. HTTPS Sorunu">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mesaj</label>
                                    <textarea class="form-control" name="message" rows="5" placeholder="Sorununuzu ayrıntılı olarak açıklayın..."></textarea>
                                </div>
                                <button type="button" class="btn btn-primary" id="createThreadBtn">Bildirim Gönder</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h6 class="mb-0">Son Bildirimler</h6></div>
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive w-100 overflow-visible" style="overflow: visible;">
                                <table class="table table-hover dttables w-100" id="supportsTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%">Sıra</th>
                                            <th>Konu</th>
                                            <th>Açıklama</th>
                                            <th style="width:10%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <script src="/pages/yardim-destek/js/support.js?<?= filemtime('pages/yardim-destek/js/support.js') ?>"></script>
                    <script src="/pages/yardim-destek/js/thread.js?<?= filemtime('pages/yardim-destek/js/thread.js') ?>"></script>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
