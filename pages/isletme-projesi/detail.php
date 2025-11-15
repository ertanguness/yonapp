<?php

use App\Helper\Security;
use App\Helper\Helper;
use Model\IsletmeProjesiModel;

Security::ensureSiteSelected();

$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0) ?? 0;

$Model = new IsletmeProjesiModel();
$summary = $Model->getProjectSummary($id);
$gelirKalemleri = $Model->getKalemleri($id, 'gelir');
$giderKalemleri = $Model->getKalemleri($id, 'gider');

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İşletme Projesi Detay</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İşletme Projesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <a href="/isletme-projesi" class="btn btn-icon btn-light-secondary route-link" title="Listeye Dön">
                <i class="feather-arrow-left"></i>
            </a>
            <a href="/isletme-projesi-duzenle/<?php echo $enc_id; ?>" class="btn btn-icon btn-light-primary route-link" title="Düzenle">
                <i class="feather-edit"></i>
            </a>
            <a href="/isletme-projesi-pdf/<?php echo $enc_id; ?>" target="_blank" class="btn btn-icon btn-light-info" title="PDF">
                <i class="bi bi-filetype-pdf"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo htmlspecialchars($summary->proje->proje_adi ?? ''); ?></h5>
                            <div class="text-muted">
                                Dönem: <?php echo date('d.m.Y', strtotime($summary->proje->donem_baslangic ?? '')); ?> - <?php echo date('d.m.Y', strtotime($summary->proje->donem_bitis ?? '')); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="hstack gap-2 text-muted text-success mb-2">
                                        <div class="avatar-text avatar-sm"><i class="feather-trending-up"></i></div>
                                        <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($summary->toplam_gelir); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="hstack gap-2 text-muted text-danger mb-2">
                                        <div class="avatar-text avatar-sm"><i class="feather-trending-down"></i></div>
                                        <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($summary->toplam_gider); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="hstack gap-2 text-muted mb-2">
                                        <div class="avatar-text avatar-sm"><i class="feather-archive"></i></div>
                                        <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($summary->net_yillik_gider); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="hstack gap-2 text-muted mb-2">
                                        <div class="avatar-text avatar-sm"><i class="feather-calendar"></i></div>
                                        <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($summary->aylik_avans_toplam); ?> / Ay</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><div class="text-muted">Genel Kurul Türü</div><div><?php echo htmlspecialchars($summary->proje->genel_kurul_turu ?? '') ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Genel Kurul Tarihi</div><div><?php echo isset($summary->proje->genel_kurul_tarihi)?date('d.m.Y', strtotime($summary->proje->genel_kurul_tarihi)):'' ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Kurul Onayı</div><div><?php echo htmlspecialchars($summary->proje->kurul_onay_durumu ?? '') ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Onay Tarihi</div><div><?php echo isset($summary->proje->kurul_onay_tarihi)?date('d.m.Y', strtotime($summary->proje->kurul_onay_tarihi)):'' ?></div></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><div class="text-muted">Divan Tutanak No</div><div><?php echo htmlspecialchars($summary->proje->divan_tutanak_no ?? '') ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Bildirim Yöntemi</div><div><?php echo htmlspecialchars($summary->proje->bildirim_yontemi ?? '') ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Bildirim Tarihi</div><div><?php echo isset($summary->proje->bildirim_tarihi)?date('d.m.Y', strtotime($summary->proje->bildirim_tarihi)):'' ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Kesinleşme Tarihi</div><div><?php echo isset($summary->proje->kesinlesme_tarihi)?date('d.m.Y', strtotime($summary->proje->kesinlesme_tarihi)):'' ?></div></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><div class="text-muted">İtiraz</div><div><?php echo (($summary->proje->itiraz_var_mi ?? 0)==1)?'Var':'Yok' ?></div></div>
                                <div class="col-md-3"><div class="text-muted">İtiraz Tarihi</div><div><?php echo isset($summary->proje->itiraz_tarihi)?date('d.m.Y', strtotime($summary->proje->itiraz_tarihi)):'' ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Karar Tarihi</div><div><?php echo isset($summary->proje->itiraz_karar_tarihi)?date('d.m.Y', strtotime($summary->proje->itiraz_karar_tarihi)):'' ?></div></div>
                                <div class="col-md-3"><div class="text-muted">Sonuç</div><div><?php echo htmlspecialchars($summary->proje->itiraz_sonucu ?? '') ?></div></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><div class="text-muted">Paylandırma Esası</div><div><?php echo htmlspecialchars($summary->proje->paylandirma_esasi ?? '') ?></div></div>
                                <div class="col-md-6"><div class="text-muted">Yönetim Planı Referansı</div><div><?php echo nl2br(htmlspecialchars($summary->proje->yonetim_plani_referans ?? '')) ?></div></div>
                                <div class="col-md-3"><div class="text-muted">İmza Oranı</div><div><?php echo htmlspecialchars($summary->proje->imza_orani ?? '') ?></div></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Gelir Kalemleri</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead><tr><th>Kategori</th><th class="text-end">Tutar</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($gelirKalemleri as $k): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($k->kategori); ?></td>
                                                        <td class="text-end"><?php echo Helper::formattedMoney($k->tutar); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Gider Kalemleri</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead><tr><th>Kategori</th><th class="text-end">Tutar</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($giderKalemleri as $k): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($k->kategori); ?></td>
                                                        <td class="text-end"><?php echo Helper::formattedMoney($k->tutar); ?></td>
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
            </div>
        </div>
    </div>
</div>