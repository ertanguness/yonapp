<?php
use App\Helper\Security;
use Model\BakimMaliyetModel;

require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

$id = Security::decrypt($_GET['id'] ?? '');

$BakimMaliyet = new BakimMaliyetModel();
$Makbuzlar = $BakimMaliyet->MakbuzaAitTumDosyalar($id);

if (!$Makbuzlar || count($Makbuzlar) === 0) {
    echo '<div class="p-3 text-center text-danger">Makbuz bulunamadı.</div>';
    exit;
}

// Dosya türüne göre icon ve yazı döndüren fonksiyon
function getFileIconAndLabel($ext) {
    $ext = strtolower($ext);
    switch ($ext) {
        case 'pdf':
            return ['icon' => 'file-text', 'label' => 'PDF'];
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return ['icon' => 'image', 'label' => strtoupper($ext)];
        case 'doc':
        case 'docx':
            return ['icon' => 'file-text', 'label' => 'DOC'];
        case 'xls':
        case 'xlsx':
            return ['icon' => 'file-text', 'label' => 'XLS'];
        case 'txt':
            return ['icon' => 'file-text', 'label' => 'TXT'];
        default:
            return ['icon' => 'file', 'label' => strtoupper($ext)];
    }
}
?>

<div class="modal-header">
    <h5 class="modal-title">Yüklenen Makbuzlar</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
</div>
<div class="modal-body">
    <div class="list-group">
        <?php foreach ($Makbuzlar as $makbuz):
            $dosyaAdi = basename($makbuz['dosya_yolu']);
            $url = "/files/Bakim_Makbuzlari/" . $dosyaAdi;
            $dosyaExt = strtolower(pathinfo($dosyaAdi, PATHINFO_EXTENSION));
            $idAttr = 'makbuz-' . md5($makbuz['dosya_yolu']);
            $iconData = getFileIconAndLabel($dosyaExt);
        ?>
        <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex align-items-center"
           onclick="previewMakbuz('<?= $url ?>', '<?= $dosyaExt ?>', '<?= $idAttr ?>')">
           <i class="feather-<?= $iconData['icon'] ?> me-2"></i>
           <span class="flex-grow-1"><?= date('d.m.Y H:i', strtotime($makbuz['kayit_tarihi'])) ?> tarihinde yüklenen makbuz</span>
           <span class="badge bg-secondary ms-2" style="font-size: 0.8rem;"><?= $iconData['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-4" id="makbuz-preview-area" style="display:none;">
        <hr>
        <div id="makbuz-preview-content" class="text-center"></div>
    </div>
</div>
