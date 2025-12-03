<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Date;
use App\Helper\Security;
use App\Helper\Helper;

use Model\TahsilatModel;
use Model\BorclandirmaDetayModel;
use Model\FinansalRaporModel;
use Model\SitelerModel;

use Model\KisilerModel;
use Random\Engine\Secure;

$Kisi = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Tahsilat = new TahsilatModel();
$FinansalRapor = new FinansalRaporModel();
$SiteModel = new SitelerModel();




$id = Security::decrypt($_GET['kisi_id']) ?? 0;
$kisi = $Kisi->find($id);



$finansalDurum = $FinansalRapor->KisiFinansalDurum($id);
$bakiye_color = $finansalDurum->bakiye < 0 ? 'text-danger' : 'text-success';

//$borclandirmalar = $BorcDetay->KisiBorclandirmalari($id);

$kisi_borclar = $FinansalRapor->getKisiBorclar($id);
$tahsilatlar = $Tahsilat->KisiTahsilatlariWithDetails($id);


?>
<div class="modal-header">
    <h5 class="modal-title" id="modalTitleId"> <?php echo $kisi->adi_soyadi ?> Tahsilat Onay Detayları </h5>
    <div class="ms-auto">

        <div class="d-flex align-items-center justify-content-center">
            <a href="javascript:void(0)" class="d-flex me-1 mesaj-gonder" data-alert-target="SendMessage"
                data-id="<?php echo $kisi->id; ?>"
                data-kisi-id="<?php echo Security::encrypt($kisi->id); ?>">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Mesaj Gönder">
                    <i class="feather feather-send"></i>
                </div>
            </a>


            <?php
            $site_id = $_SESSION['site_id'];
            $site = $SiteModel->find($site_id);
            // WhatsApp mesajı oluştur (URL encode edilmiş)
            $wa_telefon = preg_replace('/[^0-9]/', '', $kisi->telefon); // Sadece rakamlar
            if (substr($wa_telefon, 0, 1) === '0') {
                $wa_telefon = '90' . substr($wa_telefon, 1); // 0 varsa 90 ile değiştir (Türkiye kodu)
            } elseif (strlen($wa_telefon) === 10) {
                $wa_telefon = '90' . $wa_telefon; // 10 hane ise başına 90 ekle
            }

            // Mesaj metni oluştur
            $wa_mesaj_ham = "Sayın {$kisi->adi_soyadi},

    Güncel bakiye bilginiz aşağıdaki gibidir:

    📊 Bakiye: " . Helper::formattedMoney($finansalDurum->bakiye) . ";

    Saygılarımızla,
    {$site->site_adi} YÖNETİMİ";

            // URL encode (WhatsApp formatında %0A = satır atla)
            $wa_mesaj = urlencode($wa_mesaj_ham);
            $wa_link = "https://wa.me/{$wa_telefon}?text={$wa_mesaj}";
            ?>

            <!-- WhatsApp'tan mesaj gönder -->
            <a href="<?php echo $wa_link; ?>" target="_blank" class="d-flex me-1 whatsapp-mesaj-gonder"
                data-bs-toggle="tooltip" title="WhatsApp'tan Mesaj Gönder"
                data-id="<?php echo $kisi->id; ?>"
                data-kisi-id="<?php echo Security::encrypt($kisi->id); ?>">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                    data-bs-original-title="WhatsApp'tan Mesaj Gönder">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
            </a>



            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $kisi->id; ?>&&format=html"
                target="_blank" class="d-flex me-1 printBTN">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Print Invoice" aria-label="Print Invoice"><i
                        class="feather feather-printer"></i></div>
            </a>

            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $kisi->id; ?>" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Download Invoice" aria-label="Download Invoice">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
            </a>
            <a href="/pages/dues/payment/export/kisi_borc_tahsilat.php?kisi_id=<?php echo $kisi->id; ?>&format=xlsx" class="d-flex me-1 file-download">
                <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title=""
                    data-bs-original-title="Download Invoice" aria-label="Download Invoice">
                    <i class="fa-regular fa-file-excel"></i>
                </div>
            </a>

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>

</div>
<style>
    /* .card-body {
        margin: 0;
        padding: 0;
    } */
    .action-col{padding:0 6px}
    .action-vertical{gap:4px}
    .action-vertical .avatar-text{width:24px;height:24px;line-height:24px;border-radius:50%;padding:0;margin:0}
    .action-vertical i{font-size:11px;color:#6c757d;transition:color .2s ease}
    .action-vertical a.makbuz-goster:hover i{color:#0d6efd}
    .action-vertical a.mesaj-gonder:hover i{color:#6c757d}
    .action-vertical a.tahsilat-sil:hover i{color:#dc3545}
    .payment-right{padding-right:16px !important}
    .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
    .summary-card{display:flex;align-items:center;background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(16,24,40,.04)}
    .summary-icon{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;margin-right:12px}
    .summary-label{font-size:12px;color:#6c757d}
    .summary-value{font-size:20px;font-weight:700}
</style>

<div class="modal-body ">



    <div class="summary-grid mb-3">
        <div class="summary-card">
            <div class="summary-icon bg-soft-danger text-danger border-soft-danger">
                <i class="feather-user-minus"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">BORÇ (TL)</div>
                <div class="summary-value text-danger">
                    <?php echo Helper::formattedMoney(-$finansalDurum->toplam_borc); ?>
                </div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon bg-soft-success text-success border-soft-success">
                <i class="feather-user-check"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">TAHSİLAT (TL)</div>
                <div class="summary-value text-success">
                    <?php echo Helper::formattedMoney($finansalDurum->toplam_tahsilat); ?>
                </div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon bg-soft-primary text-primary border-soft-primary">
                <i class="feather-users"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">BAKİYE (TL)</div>
                <div class="summary-value <?php echo $bakiye_color; ?>">
                    <?php echo Helper::formattedMoney($finansalDurum->bakiye); ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">

            <div class="card widget-tickets-content">
                <div class="card-header">
                    <h5 class="card-title">Borçlar</h5>

                    <div class="d-flex gap-3 align-items-center borc-ekle"
                        data-kisi-id="<?php echo Security::encrypt($kisi->id); ?>">
                        <div>
                            <div class="fw-semibold text-dark">Yeni Borç</div>
                        </div>
                        <div class="avatar-text" accesskey="0">
                            <i class="feather feather-plus"></i>
                        </div>
                    </div>

                </div>
                <div class="overflow-auto tasks-items-wrapper" style="height: calc(100vh - 480px);">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive tickets-items-wrapper">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($kisi_borclar as $borc): ?>
                                        <tr>
                                            <td style="width:4%;">
                                                <div class="avatar-text bg-gray-100">
                                                    <a href="javascript:void(0);">
                                                        <?php echo Helper::getInitials($borc->borc_adi); ?>
                                                    </a>
                                                </div>

                                            </td>
                                            <td>
                                                <a href="javascript:void(0);"><?php echo $borc->borc_adi; ?> <span
                                                        class="fs-12 fw-normal text-muted">
                                                        <?php echo $borc->daire_kodu; ?> </span>
                                                    </span> </a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <?php echo $borc->aciklama; ?>
                                                </p>
                                                <div class="tickets-list-action d-flex align-items-center gap-3">

                                                    <a href="javascript:void(0);"
                                                        data-id="<?php echo Security::encrypt($borc->id); ?>"
                                                        data-kisi-id="<?php echo Security::encrypt($kisi->id ?? 0); ?>"
                                                        class="text-secondary borc-duzenle">Düzenle</a> |
                                                    <a href="javascript:void(0);" data-id="<?php echo Security::encrypt($borc->id); ?>" class="text-danger borc-sil">Sil</a>
                                                </div>
                                            </td>
                                            <TD>
                                                <div class="mt-2 mt-md-0 text-md-end mg-l-60 ms-md-0">
                                                    <a href="javascript:void(0);" class="fw-bold d-block">
                                                        <?php echo Helper::formattedMoney($borc->tutar); ?>

                                                    </a>
                                                    <span class="fs-12 text-danger">
                                                        <?php echo "G. Zammı : " . Helper::formattedMoney($borc->hesaplanan_gecikme_zammi); ?>
                                                    </span>
                                                </div>
                                            </TD>
                                        </tr>
                                    <?php endforeach ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Kayıt yok ise  -->
                <?php if (empty($kisi_borclar)): ?>
                    <div class="text-center text-muted">
                        <p>Kayıt Bulunamadı!!!</p>

                    </div>
                <?php endif ?>



            </div>
        </div>
        <div class="col-lg-6">

            <div class="card widget-tickets-content">
                <div class="card-header">
                    <h5 class="card-title">Ödemeler</h5>
                    <div class="card-header-action">
                        <div class="card-header-btn">

                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                                <a href="#" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                            </div>
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                                <a href="#" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                    <i class="feather-more-vertical"></i>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>
                <div class="overflow-auto " style="height: calc(100vh - 460px);">
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive ">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <?php foreach ($tahsilatlar as $tahsilat):
                                        $enc_id = Security::encrypt($tahsilat['id']);
                                    ?>
                                        <tr class="cursor-pointer">
                                            <td class="action-col" style="width:4%;">
                                                <div class="d-flex flex-column align-items-center action-vertical">
                                                    <a href="javascript:void(0);"
                                                       data-id="<?php echo $enc_id ?>"
                                                       data-kisi-id="<?php echo Security::encrypt($kisi->id); ?>"
                                                       class="action-link makbuz-goster"
                                                       data-bs-toggle="tooltip" data-bs-placement="right" title="Makbuz Yazdır">
                                                        <div class="avatar-text bg-gray-100">
                                                            <i class="fa fa-print fa-sm"></i>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                       data-id="<?php echo $enc_id ?>"
                                                       data-kisi-id="<?php echo Security::encrypt($kisi->id); ?>"
                                                       data-makbuz-bildirim="true"
                                                       class="action-link mesaj-gonder"
                                                       data-bs-toggle="tooltip" data-bs-placement="right" title="Mesaj Gönder">
                                                        <div class="avatar-text bg-gray-100">
                                                            <i class="fa fa-comment fa-sm"></i>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0);" data-id="<?php echo $enc_id ?>"
                                                       class="action-link tahsilat-sil"
                                                       data-bs-toggle="tooltip" data-bs-placement="right" title="Sil">
                                                        <div class="avatar-text bg-gray-100">
                                                            <i class="fa fa-trash fa-sm"></i>
                                                        </div>
                                                    </a>
                                                </div>
                                            </td>
                                            <td style="width:60%; vertical-align: top;">
                                                <!-- Ana Açıklama ve Eylemler -->
                                                <a href="javascript:void(0);" class="fw-bold">
                                                    Tahsilat Fişi #<?php echo $tahsilat['id']; ?>
                                                </a>
                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc" 
                                                   data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="<?php echo htmlspecialchars(!empty($tahsilat['ana_aciklama']) ? $tahsilat['ana_aciklama'] : 'Genel Tahsilat'); ?>">
                                                    <?php echo !empty($tahsilat['ana_aciklama']) ? Helper::short($tahsilat['ana_aciklama'],60) : "Genel Tahsilat"; ?>
                                                </p>
                                                

                                                <!-- TAHSİLAT DETAYLARI ALT LİSTESİ -->
                                                <?php if (!empty($tahsilat['detaylar'])): ?>
                                                    <ul class="list-unstyled mt-2 fs-12 text-muted">
                                                        <?php foreach ($tahsilat['detaylar'] as $detay): ?>
                                                            <li class="mb-2">
                                                                <div class="ps-3">
                                                                    <i class="fa fa-check text-success me-1"></i>
                                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars(Helper::short($detay['borc_adi'], 30)); ?></span>
                                                                    <div class="ps-3 mt-1 text-muted">
                                                                        <?php echo Helper::short($detay['aciklama'], 30); ?>
                                                                        : <span class="fw-bold text-primary"><?php echo Helper::formattedMoney($detay['tutar']); ?></span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>

                                            </td>
                                            <td class="text-end payment-right" style="width: 35%; vertical-align: top;">
                                                <!-- Toplam Tutar ve Tarih -->
                                                <a href="javascript:void(0);" class="fw-bold d-block">
                                                    <?php echo Helper::formattedMoney($tahsilat['toplam_tutar']); ?>
                                                </a>
                                                <?php if (!empty($tahsilat['kullanilan_kredi']) && $tahsilat['kullanilan_kredi'] > 0): ?>
                                                    <span class="fs-12 text-muted d-block">
                                                        Kullanılan Kredi: <?php echo Helper::formattedMoney($tahsilat['kullanilan_kredi']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="fs-12 text-muted text-wrap">
                                                    <?php echo Date::dmYHis($tahsilat['islem_tarihi']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kayıt yok ise -->
                <?php if (empty($tahsilatlar)): ?>
                    <div class="text-center text-muted p-4">
                        <p>Bu kişiye ait herhangi bir tahsilat kaydı bulunamadı.</p>
                    </div>
                <?php endif; ?>



            </div>
        </div>

    </div>
</div>
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        try { new bootstrap.Tooltip(el); } catch (e) {}
    });
</script>
