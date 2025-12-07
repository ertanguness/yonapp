<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';
$site_id = $_SESSION['site_id'] ?? 0;

use App\Helper\Security;
use Model\KisilerModel;
use Model\KisiNotModel;

$Kisiler = new KisilerModel();
$Notlar = new KisiNotModel();

$kisi_id = isset($_GET['kisi_id']) ? Security::decrypt($_GET['kisi_id']) : 0;
$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;

$not = $id ? $Notlar->NotBilgileri($id) : null;
if (!empty($kisi_id)) {
    $kisiBilgileri = $Kisiler->KisiBilgileri($kisi_id);
} else {
    $kisiBilgileri = $Kisiler->KisiBilgileri($not->kisi_id ?? null);
}
if (!$not || !is_object($not)) {
    $not = (object)['icerik' => '', 'kisi_id' => $kisiBilgileri->id ?? null];
}
?>
<div class="modal fade" id="kisiNotModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kişi Notu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="kisiNotForm">
                    <input type="hidden" name="not_id" id="not_id" value="<?php echo $_GET['id'] ?? 0; ?>">
                    <input type="hidden" name="kisi_id" value="<?= htmlspecialchars($kisiBilgileri->id ?? '') ?>">
                    <div class="mb-3">
                        <label for="modalKisiNotIcerik" class="form-label fw-semibold">Not</label>
                        <textarea id="modalKisiNotIcerik" name="icerik" class="form-control" rows="5" placeholder="Not içeriği yazınız"><?php echo htmlspecialchars($not->icerik ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="KisiNotKaydet" name="KisiNotKaydet" class="btn btn-success">Kaydet</button>
                <button class="btn btn-danger" data-bs-dismiss="modal">İptal</button>
            </div>
        </div>
    </div>
</div>
