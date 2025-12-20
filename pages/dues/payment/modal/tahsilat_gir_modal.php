<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\FinansalHelper;
use App\Helper\Aidat;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\KisiKredileriModel;
use Model\BorclandirmaDetayModel;
use Model\FinansalRaporModel;


use App\Services\Gate;

Gate::authorizeOrDie(
    permissionName: 'tahsilat_ekle_sil',
    customMessage: 'Bu sayfayı görüntüleme yetkiniz yok!',
    redirectUrl: false
);


$Aidat = new Aidat();
$Daire = new DairelerModel();
$Kisiler = new KisilerModel();
$KisiKredi = new KisiKredileriModel();
$BorcDetay = new BorclandirmaDetayModel();
$FinansalRapor = new FinansalRaporModel();

$id = Security::decrypt($_GET['kisi_id']) ?? 0;


//$borclandirmalar = $BorcDetay->KisiBorclandirmalari($id);

$kisi_guncel_borclar = $FinansalRapor->getKisiGuncelBorclar($id);
$secili_borc_ids = (array_map(Security::decrypt(...), explode(',', $_GET['borc_idler'] ?? '')));

$secili_borclar = $FinansalRapor->getKisiBorclarByIds($secili_borc_ids);
//Helper::dd(data: $secili_borclar);

$secilen_toplam_borc = 0;
foreach ($secili_borclar as $borc) {
    $secilen_toplam_borc += $borc->toplam_kalan_borc ?? 0;
}

$kredi = $KisiKredi->getKullanilabilirKrediByKisiId($id) ?? 0;

// Kullanıcının finansal durumunu al
$finansalDurum = $FinansalRapor->getKisiGuncelBorcOzet($id);


//kişinin bakiyesini getir
$bakiye = $finansalDurum->guncel_borc;


$enc_id = Security::decrypt($_GET["id"] ?? 0);
$kisi_id = $_GET["kisi_id"] ?? 0;

$kisi = $Kisiler->getKisiByDaireId(Security::decrypt($kisi_id));


if (!$kisi) {
    echo '<div class="alert alert-danger">Kişi bulunamadı.</div>';
    exit;
}

//Toplam Borcun Yüzdelik hesaplanması
$kisi_finans = $BorcDetay->KisiFinansalDurum(Security::decrypt($kisi_id));

$dnone = $kredi <= 0 ? 'd-none' : '';

?>


<div class="modal-header" style="background:linear-gradient(135deg, rgba(19,91,236,.12), rgba(16,185,129,.10));">
    <div>
        <div class="fw-bold" style="font-size:16px;">Tahsilat Oluştur</div>
        <div class="yd-muted" style="font-size:12px;">Seçilen borçlar için tahsilat kaydı oluşturun.</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form action="" method="POST" id="tahsilatForm">
         <input type="text" name="tahsilat_id" value="<?= $enc_id ?>" hidden>
                            <input type="text" name="kisi_id" value="<?= $kisi_id ?>" hidden>
    <div class="yd-card p-3 mb-3" style="border-radius:14px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="yd-muted" style="font-size:12px;">Daire : <span class="fw-bold"><?php echo $kisi->daire_kodu ?? '-'; ?></span></div>
                <div class="yd-muted" style="font-size:12px;">Kişi : <span class="fw-bold"><?php echo $kisi->adi_soyadi ?? '-'; ?></span></div>
                <div class="yd-muted mt-1" style="font-size:12px;">Kullanılabilir Kredi: <span class="fw-bold" id="ydTahsilatKredi">
<?php echo Helper::formattedMoney($kredi); ?>
                </span></div>
            </div>
            <div class="text-end">
                <div class="yd-muted" style="font-size:12px;">Toplam (seçilen)</div>
                <div class="fw-bold" style="font-size:18px;" id="ydTahsilatToplam"><?php echo Helper::formattedMoney($secilen_toplam_borc); ?></div>
                <div class="yd-muted" style="font-size:12px;">Kredi Kullan: <span class="fw-bold" id="ydTahsilatKrediKullan">0</span></div>
                <div class="yd-muted" style="font-size:12px;">Ödenecek: <span class="fw-bold" id="ydTahsilatNet">0</span></div>
                <div class="yd-muted" style="font-size:12px;">Kalan: <span class="fw-bold" id="ydTahsilatKalan">0</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">İşlem Tarihi</label>
            <input type="text" class="form-control flatpickr" name="islem_tarihi" id="islem_tarihi"
                                            value="<?php echo date("d.m.Y H:i") ?>">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Açıklama</label>
            <input type="text" class="form-control" id="ydTahsilatAciklama" placeholder="Örn: Aidat tahsilatı" />
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Kasa</label>
            <?php echo FinansalHelper::KasaSelect('kasa_id')  ?>
            <div class="form-text">Tahsilatın işleneceği kasayı seçin.</div>

        </div>
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Tahsil Edilen Tutar</label>
            <div class="input-group">
                <span class="input-group-text">₺</span>
                <input type="text" class="form-control money" name="tutar" id="tutar" 
                value="<?php echo Helper::formattedMoneyWithoutCurrency($secilen_toplam_borc); ?>" placeholder="₺ 0,00" required>
                <button type="button" class="btn btn-outline-secondary" id="ydTahsilEdilenNetYap">Net</button>
            </div>
            <div class="form-text">Kişinin gönderdiği/aldığınız tutar. Net tutardan farklı olabilir.</div>
        </div>
        <?php if ($kredi > 0): ?>
        <div class="col-12 col-md-6" id="ydKrediWrap">
            <label class="form-label fw-semibold">Kredi Kullanımı</label>
            <div class="input-group">
                <span class="input-group-text">₺</span>
                <input type="text" class="form-control" id="kullanilacak_kredi" name="kullanilacak_kredi" value="0" />
                <button type="button" class="btn btn-outline-secondary" id="ydKrediHepsiniKullan">Hepsini</button>
            </div>
            <div class="form-text">Kredi, tahsil edilecek tutardan düşer.</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Seçilen Borçlar</div>
            <span class="yd-chip" id="ydTahsilatCount">0 adet</span>
        </div>
        <div class="table-responsive mt-2" style="max-height:280px; overflow:auto; border:1px solid rgba(148,163,184,.25); border-radius:12px;">
            <table class="table mb-0">
                <thead style="background:#f8fafc; position:sticky; top:0; z-index:1;">
                    <tr>
                        <th class="px-3">Borç</th>
                        <th class="text-end">Kalan</th>
                    </tr>
                </thead>
                <tbody id="ydTahsilatSelectedList">
                        <?php if (!empty($secili_borclar)): ?>
                            <?php foreach ($secili_borclar as $borc): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-bold"><?php echo $borc->borc_adi ?? 'Borç'; ?></div>
                                        <div class="yd-muted" style="font-size:12px;"><?php echo $borc->aciklama ?? ''; ?></div>
                                    <div class="yd-muted" style="font-size:12px;">Son Ödeme: <?php echo Date::dmY($borc->bitis_tarihi ?? ''); ?></div>
                                    </td>
                                    <td class="text-end">
                                        <div class="yd-moneyline">
                                            <div class="fw-bold"><?php echo Helper::formattedMoney($borc->toplam_kalan_borc ?? 0); ?></div>
                                                <div class="sub">G.Zammı: <?php echo Helper::formattedMoney($borc->hesaplanan_gecikme_zammi); ?></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                  
                        <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    </form>

</div>
<div class="modal-footer" style="background:#fff;">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
    <button type="button" class="btn btn-primary" id="tahsilatKaydet">
        <i class="feather-check me-1"></i>Kaydet
    </button>
</div>