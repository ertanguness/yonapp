<?php

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use Model\SitelerModel;
use Model\IsletmeProjesiModel;

Security::ensureSiteSelected();

$SiteModel = new SitelerModel();

$site = $SiteModel->find($_SESSION['site_id']);

$site_adi = $site->site_adi ?? '';
$site_adresi = $site->tam_adres ?? '';

$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0) ?? 0;
$Model = new IsletmeProjesiModel();
$proje = $id ? $Model->find($id) : null;

$baslangic = $proje->donem_baslangic ?? Date::firstDay(Date::getMonth(), Date::getYear());
$bitis     = $proje->donem_bitis ?? Date::lastDay(Date::getMonth(), Date::getYear());
$gelirKalemleri = $id ? $Model->getKalemleri($id, 'gelir') : [];
$giderKalemleri = $id ? $Model->getKalemleri($id, 'gider') : [];
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İşletme Projesi <?php echo $id ? 'Düzenle' : 'Ekle'; ?></h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İşletme Projesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
            <a href="/isletme-projesi" class="btn btn-outline-secondary route-link">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </a>
            <button type="button" class="btn btn-primary" id="save_project">
                <i class="feather-save me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0 ">
    <div class="d-flex align-items-center justify-content-between">
        <div class="nav-tabs-wrapper page-content-left-sidebar-wrapper">
            <ul class="nav nav-tabs nav-tabs-custom-style" id="projectTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#projeBilgileriTab">Proje Bilgileri</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gelirGiderTab">Gelir-Gider Bilgileri</button>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="projectForm" method="POST">
                            <input type="hidden" name="id" id="proje_id" value="<?php echo $enc_id ?? 0; ?>">
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade active show" id="projeBilgileriTab">
                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Proje Adı</label>
                                        <input type="text" class="form-control" name="proje_adi" id="proje_adi" value="<?php echo htmlspecialchars($proje->proje_adi ?? '') ?>" required>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Apartman/Site Adı</label>
                                        <input type="text" class="form-control" name="apartman_site_adi" id="apartman_site_adi" value="<?php echo htmlspecialchars($proje->apartman_site_adi ?? $site_adi) ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-12">
                                        <label class="fw-semibold">Adres</label>
                                        <textarea class="form-control" name="adres" id="adres" rows="2"><?php echo htmlspecialchars($proje->adres ?? $site_adresi) ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Dönem Başlangıç</label>
                                        <input type="text" class="form-control flatpickr" name="donem_baslangic" id="donem_baslangic" value="<?php echo Date::dmY($baslangic) ?>" autocomplete="off" required>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Dönem Bitiş</label>
                                        <input type="text" class="form-control flatpickr" name="donem_bitis" id="donem_bitis" value="<?php echo Date::dmY($bitis) ?>" autocomplete="off" required>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Enflasyon Etkisi (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="enflasyon_oran" id="enflasyon_oran" value="<?php echo $proje->enflasyon_oran ?? 0 ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Rezerv Tutar (₺)</label>
                                        <input type="text" class="form-control money" name="rezerv_tutar" id="rezerv_tutar" value="<?php echo $proje->rezerv_tutar ?? '0' ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Kanuni Dayanak</label>
                                        <textarea class="form-control" name="kanuni_dayanak" id="kanuni_dayanak" rows="3"><?php echo htmlspecialchars($proje->kanuni_dayanak ?? 'Kat Mülkiyeti Kanunu madde 20 uyarınca işletme projesi düzenlenmiştir.') ?></textarea>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Varsayımlar ve Metodoloji</label>
                                        <textarea class="form-control" name="varsayimlar" id="varsayimlar" rows="3"><?php echo htmlspecialchars($proje->varsayimlar ?? '') ?></textarea>
                                        <textarea class="form-control mt-2" name="metodoloji" id="metodoloji" rows="3"><?php echo htmlspecialchars($proje->metodoloji ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Ödeme Planı/Takvimi</label>
                                        <textarea class="form-control" name="odeme_plani" id="odeme_plani" rows="3"><?php echo htmlspecialchars($proje->odeme_plani ?? '') ?></textarea>
                                      
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Geçerlilik ve Güncelleme Mekanizması</label>
                                        <textarea class="form-control" name="guncelleme_mekanizmasi" id="guncelleme_mekanizmasi" rows="3"><?php echo htmlspecialchars($proje->guncelleme_mekanizmasi ?? '') ?></textarea>
                                    </div>
                                </div>
<hr class="text-blue">
                                <div class="row mb-3">
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Genel Kurul Türü</label>
                                        <select class="form-select select2" name="genel_kurul_turu" id="genel_kurul_turu">
                                            <option value="">Seçiniz</option>
                                            <option value="olagan" <?php echo (($proje->genel_kurul_turu ?? '')=='olagan')?'selected':''; ?>>Olağan</option>
                                            <option value="olaganustu" <?php echo (($proje->genel_kurul_turu ?? '')=='olaganustu')?'selected':''; ?>>Olağanüstü</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Genel Kurul Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="genel_kurul_tarihi" id="genel_kurul_tarihi" value="<?php echo isset($proje->genel_kurul_tarihi)?Date::dmY($proje->genel_kurul_tarihi):''; ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Kurul Onay Durumu</label>
                                        <select class="form-select select2" name="kurul_onay_durumu" id="kurul_onay_durumu">
                                            <option value="beklemede" <?php echo (($proje->kurul_onay_durumu ?? 'beklemede')=='beklemede')?'selected':''; ?>>Beklemede</option>
                                            <option value="kabul" <?php echo (($proje->kurul_onay_durumu ?? '')=='kabul')?'selected':''; ?>>Kabul</option>
                                            <option value="red" <?php echo (($proje->kurul_onay_durumu ?? '')=='red')?'selected':''; ?>>Red</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Kurul Onay Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="kurul_onay_tarihi" id="kurul_onay_tarihi" value="<?php echo isset($proje->kurul_onay_tarihi)?Date::dmY($proje->kurul_onay_tarihi):''; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Divan Tutanak No</label>
                                        <input type="text" class="form-control" name="divan_tutanak_no" id="divan_tutanak_no" value="<?php echo htmlspecialchars($proje->divan_tutanak_no ?? '') ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Bildirim Yöntemi</label>
                                        <select class="form-select select2" name="bildirim_yontemi" id="bildirim_yontemi">
                                            <option value="">Seçiniz</option>
                                            <option value="elden" <?php echo (($proje->bildirim_yontemi ?? '')=='elden')?'selected':''; ?>>Elden İmza</option>
                                            <option value="taahhutlu" <?php echo (($proje->bildirim_yontemi ?? '')=='taahhutlu')?'selected':''; ?>>İadeli Taahhütlü</option>
                                            <option value="diger" <?php echo (($proje->bildirim_yontemi ?? '')=='diger')?'selected':''; ?>>Diğer</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Bildirim Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="bildirim_tarihi" id="bildirim_tarihi" value="<?php echo isset($proje->bildirim_tarihi)?Date::dmY($proje->bildirim_tarihi):''; ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Kesinleşme Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="kesinlesme_tarihi" id="kesinlesme_tarihi" value="<?php echo isset($proje->kesinlesme_tarihi)?Date::dmY($proje->kesinlesme_tarihi):''; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">İtiraz Var mı?</label>
                                        <select class="form-select" name="itiraz_var_mi" id="itiraz_var_mi">
                                            <option value="0" <?php echo (($proje->itiraz_var_mi ?? 0)==0)?'selected':''; ?>>Hayır</option>
                                            <option value="1" <?php echo (($proje->itiraz_var_mi ?? 0)==1)?'selected':''; ?>>Evet</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">İtiraz Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="itiraz_tarihi" id="itiraz_tarihi" value="<?php echo isset($proje->itiraz_tarihi)?Date::dmY($proje->itiraz_tarihi):''; ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">İtiraz Karar Tarihi</label>
                                        <input type="text" class="form-control flatpickr" name="itiraz_karar_tarihi" id="itiraz_karar_tarihi" value="<?php echo isset($proje->itiraz_karar_tarihi)?Date::dmY($proje->itiraz_karar_tarihi):''; ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">İtiraz Sonucu</label>
                                        <input type="text" class="form-control" name="itiraz_sonucu" id="itiraz_sonucu" value="<?php echo htmlspecialchars($proje->itiraz_sonucu ?? '') ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">Paylandırma Esası</label>
                                        <select class="form-select select2" name="paylandirma_esasi" id="paylandirma_esasi">
                                            <option value="">Seçiniz</option>
                                            <option value="arsa_payi" <?php echo (($proje->paylandirma_esasi ?? '')=='arsa_payi')?'selected':''; ?>>Arsa Payı</option>
                                            <option value="metrekare" <?php echo (($proje->paylandirma_esasi ?? '')=='metrekare')?'selected':''; ?>>Metrekare</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="fw-semibold">Yönetim Planı Referansı</label>
                                        <textarea class="form-control" name="yonetim_plani_referans" id="yonetim_plani_referans" rows="2"><?php echo htmlspecialchars($proje->yonetim_plani_referans ?? '') ?></textarea>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="fw-semibold">İmza Oranı (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="imza_orani" id="imza_orani" value="<?php echo $proje->imza_orani ?? '' ?>">
                                    </div>
                                </div>

                                    </div>
                                    <div class="tab-pane fade" id="gelirGiderTab">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card border">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Yıllık Gelir Kalemleri</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_income_row">
                                                    <i class="feather-plus me-1"></i>Ekle
                                                </button>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table mb-0" id="income_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Kategori</th>
                                                            <th>Tutar (₺)</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($gelirKalemleri as $k): ?>
                                                            <tr>
                                                                <td><input type="text" class="form-control" name="gelir_kategori[]" value="<?php echo htmlspecialchars($k->kategori) ?>"></td>
                                                                <td><input type="text" class="form-control money" name="gelir_tutar[]" value="<?php echo Helper::formattedMoney($k->tutar) ?>"></td>
                                                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary remove-row"><i class="feather-x"></i></button></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card border">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Yıllık Gider Kalemleri</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="add_expense_row">
                                                    <i class="feather-plus me-1"></i>Ekle
                                                </button>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table mb-0" id="expense_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Kategori</th>
                                                            <th>Tutar (₺)</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($giderKalemleri as $k): ?>
                                                            <tr>
                                                                <td><input type="text" class="form-control" name="gider_kategori[]" value="<?php echo htmlspecialchars($k->kategori) ?>"></td>
                                                                <td><input type="text" class="form-control money" name="gider_tutar[]" value="<?php echo Helper::formattedMoney($k->tutar) ?>"></td>
                                                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary remove-row"><i class="feather-x"></i></button></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="p-2 text-muted fs-12">Kategori önerileri: Bakım-Onarım, Temizlik, Güvenlik, Sigorta, Yönetici Ücretleri, Diğer, Beklenmedik (Rezerv ayrı alan olarak yukarıda)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import '/pages/isletme-projesi/js/projects.js';
</script>