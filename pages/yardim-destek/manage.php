<?php
use Model\DestekModel;
use Model\DestekMesajModel;
use App\Helper\Security;

$Threads = new DestekModel();
$Messages = new DestekMesajModel();
$id = $_GET['id'] ?? 0;
$thread = $id ? $Threads->find($id, true) : null;
?>

<?php if ($thread): ?>
    <?php $isClosed = ($thread->durum ?? '') === 'Kapandı'; ?>
    <?php if ($isClosed): ?>
        <div class="alert alert-warning">Bu destek bildirimi kapalıdır. Farklı bir sorun için yeni bildirim oluşturun.</div>
    <?php endif; ?>
    <div class="mb-3">
        <h5 class="mb-1">#<?= (int)($thread->id ?? 0); ?> - <?= htmlspecialchars($thread->konu ?? ''); ?></h5>
        <div class="text-muted">Bildirim Durumu: <strong><?= htmlspecialchars($thread->durum ?? 'Açık'); ?></strong></div>
    </div>
    <div class="vstack gap-3">
        <?php foreach ($Messages->findWhere(['talep_id' => (int)($thread->id ?? 0)], 'id ASC') as $m): ?>
            <div class="card border-<?= ($m->gonderen_tip ?? 'musteri') === 'destek' ? 'success' : 'secondary'; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <strong><?= ($m->gonderen_tip ?? 'musteri') === 'destek' ? 'Yetkili' : 'Müşteri'; ?></strong>
                        <span class="text-muted"><?= htmlspecialchars($m->tarih ?? ''); ?></span>
                    </div>
                    <div class="mt-2"><?= nl2br(htmlspecialchars($m->mesaj ?? '')); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$isClosed): ?>
    <div class="card mt-4">
        <div class="card-body">
            <form id="replyForm">
                <input type="hidden" name="thread_id" value="<?= Security::encrypt((int)($thread->id ?? 0)); ?>">
                <div class="mb-2">
                    <label class="form-label">Yanıtınız</label>
                    <textarea class="form-control" name="message" rows="4" placeholder="Mesajınızı yazın..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="sendReplyBtn">Yanıt Gönder</button>
                    <button type="button" class="btn btn-outline-danger" id="closeThreadBtn" data-id="<?= Security::encrypt((int)($thread->id ?? 0)); ?>">Bildirimi Kapat</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

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

    <div class="mt-4">
        <h6 class="mb-2">Son Bildirimler</h6>
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
        <script src="/pages/yardim-destek/js/support.js?<?= filemtime('pages/yardim-destek/js/support.js') ?>"></script>
        <script src="/pages/yardim-destek/js/thread.js?<?= filemtime('pages/yardim-destek/js/thread.js') ?>"></script>
    </div>
<?php endif; ?>
