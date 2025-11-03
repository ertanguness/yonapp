<?php
/**
 * Bu dosya, session'daki tüm flash mesajlarını
 * önceden tanımlanmış HTML yapısıyla ekrana basar.
 */
use App\Services\FlashMessageService; // Gerekli sınıfı dahil et

if (FlashMessageService::hasMessages()) {
    $messages = FlashMessageService::getMessages();

    foreach ($messages as $msg) {
        // Mesaj türüne göre Bootstrap alert sınıfını belirle
        $alertClass = 'alert-info'; // varsayılan
        if ($msg['type'] === 'success') $alertClass = 'alert-success';
        if ($msg['type'] === 'error' || $msg['type'] === 'danger') $alertClass = 'alert-danger';
        if ($msg['type'] === 'warning') $alertClass = 'alert-warning';
?>
<!-- HTML yapısı burada, PHP içinde echo ile değil, doğrudan yazılıyor. -->
<div class="alert alert-dismissible <?= $alertClass ?> bg-white text-start font-weight-600 mb-4" role="alert">
    <div class="d-flex align-items-center">
        <div>
            <img src="assets/images/icons/<?= htmlspecialchars($msg['icon']) ?>" alt="ikon"
                style="width: 36px; height: 36px;">
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>

        </div>
        <div style="margin-left: 10px;">
            <h4 class="alert-title"><?= htmlspecialchars($msg['title']) ?></h4>
            <div class="text-secondary"><?= ($msg['message']) ?></div>
        </div>
    </div>
</div>
<?php
    unset($_SESSION['flash_messages']);

    } // foreach döngüsü biter
} // if biter
?>