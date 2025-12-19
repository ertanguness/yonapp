<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use Model\SitelerModel;

$site_id = $_SESSION['site_id'] ?? 0;
$Siteler = new SitelerModel();
$site = $Siteler->find($site_id);
$site_adi = $site->site_adi ?? 'Site';

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Raporlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Raporlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Rapor Merkezi";
    $text = "Aşağıdaki raporlardan istediğinizi seçerek özelleştirebilir ve farklı formatlarda indirebilirsiniz.";
    $type = "info";
    require_once 'pages/components/alert.php'
    ?>

    <div class="row">
        <div class="col-12 mb-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="feather-file-text me-2"></i>
                        Rapor Listesi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="raporlarAccordion">

                        <!-- Hazirun Listesi Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingHazirun">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseHazirun"
                                    aria-expanded="false" aria-controls="collapseHazirun">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded me-3">
                                            <i class="feather-users fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Hazirun Listesi</span>
                                            <small class="text-muted">Genel kurul için hazır bulunanlar listesi</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseHazirun" class="accordion-collapse collapse"
                                aria-labelledby="headingHazirun" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formHazirun" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, genel kurul toplantısı için tüm daire sahiplerinin listesini ve imza alanlarını içerir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="hazirun_baslik" class="form-label">
                                                <i class="feather-edit-3 me-1"></i>Rapor Başlığı
                                            </label>
                                            <input type="text" class="form-control" id="hazirun_baslik"
                                                name="hazirun_baslik" placeholder="Örn: OLAĞANÜSTÜ GENEL KURUL"
                                                value="OLAĞANÜSTÜ GENEL KURUL">
                                            <small class="text-muted">Rapor başlığında görünecek metin</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="hazirun_tarih" class="form-label">
                                                <i class="feather-calendar me-1"></i>Toplantı Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="hazirun_tarih"
                                                name="hazirun_tarih" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                            <small class="text-muted">Genel kurul toplantı tarihi</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="hazirun_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-control select2" id="hazirun_format" name="hazirun_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>
                                        <hr class="my-2">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                            data-rapor="hazirun">
                                            <i class="feather-eye me-2"></i>Önizle
                                        </button>
                                        <button type="button" class="btn btn-primary btn-rapor-indir"
                                            data-rapor="hazirun">
                                            <i class="feather-download me-2"></i>Raporu İndir
                                        </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tarihler Arası Borç/Alacak Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingBorcAlacak">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseBorcAlacak"
                                    aria-expanded="false" aria-controls="collapseBorcAlacak">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded me-3">
                                            <i class="feather-dollar-sign fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Tarihler Arası Borç/Alacak Raporu</span>
                                            <small class="text-muted">Belirli tarih aralığındaki borç ve alacak durumu</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseBorcAlacak" class="accordion-collapse collapse"
                                aria-labelledby="headingBorcAlacak" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formBorcAlacak" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, seçilen tarih aralığındaki tüm borç ve alacak hareketlerini detaylı olarak gösterir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="borc_baslangic" class="form-label">
                                                <i class="feather-calendar me-1"></i>Başlangıç Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="borc_baslangic"
                                                name="borc_baslangic" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('01.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="borc_bitis" class="form-label">
                                                <i class="feather-calendar me-1"></i>Bitiş Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="borc_bitis"
                                                name="borc_bitis" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="borc_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-control select2" id="borc_format" name="borc_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                           
                                            <div class="btn-group gap-2" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="borc" data-range="this-month">Bu Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="borc" data-range="last-month">Geçen Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="borc" data-range="this-year">Bu Yıl</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="borc" data-range="last-year">Geçen Yıl</button>
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="borc-alacak">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-rapor-indir"
                                                data-rapor="borc-ozet">
                                                <i class="feather-list me-2"></i>Borç Bazında Özet
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="borc-alacak">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Aidat Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingAidat">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseAidat"
                                    aria-expanded="false" aria-controls="collapseAidat">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded me-3">
                                            <i class="feather-credit-card fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Aidat Raporu</span>
                                            <small class="text-muted">Aylık aidat ödemeleri ve borç durumu</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseAidat" class="accordion-collapse collapse"
                                aria-labelledby="headingAidat" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formAidat" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, seçilen ayın aidat ödemelerini ve borçlu daireleri gösterir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="aidat_ay" class="form-label">
                                                <i class="feather-calendar me-1"></i>Ay
                                            </label>
                                            <select class="form-control select2" id="aidat_ay" name="aidat_ay">
                                                <?php

                                                $aylar = [
                                                    1 => 'Ocak',
                                                    2 => 'Şubat',
                                                    3 => 'Mart',
                                                    4 => 'Nisan',
                                                    5 => 'Mayıs',
                                                    6 => 'Haziran',
                                                    7 => 'Temmuz',
                                                    8 => 'Ağustos',
                                                    9 => 'Eylül',
                                                    10 => 'Ekim',
                                                    11 => 'Kasım',
                                                    12 => 'Aralık'
                                                ];

                                                foreach ($aylar as $i => $ay): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo date('n') == $i ? 'selected' : ''; ?>>
                                                        <?php echo $ay; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="aidat_yil" class="form-label">
                                                <i class="feather-calendar me-1"></i>Yıl
                                            </label>
                                            <select class="form-control select2" id="aidat_yil" name="aidat_yil">
                                                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="aidat_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-control select2" id="aidat_format" name="aidat_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" id="aidat_sadece_borclu" name="aidat_sadece_borclu">
                                                <span class="form-check-label">Sadece Borçlu Daireleri Göster</span>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" id="aidat_iletisim_bilgileri" name="aidat_iletisim_bilgileri" checked>
                                                <span class="form-check-label">İletişim Bilgilerini Dahil Et</span>
                                            </label>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="aidat">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="aidat">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Kategori Bazlı Özet Gelir-Gider Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingGelirGiderKategoriBazli">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseGelirGiderKategoriBazli"
                                    aria-expanded="false" aria-controls="collapseGelirGiderKategoriBazli">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-info text-info border-soft-info rounded me-3">
                                            <i class="feather-trending-up fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Kategori Bazlı Özet Gelir-Gider Raporu</span>
                                            <small class="text-muted">
                                                Belirli bir dönemdeki gelir ve giderlerin kategori bazında özeti
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseGelirGiderKategoriBazli" class="accordion-collapse collapse"
                                aria-labelledby="headingGelirGiderKategoriBazli" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formGelirGiderKategori" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, belirtilen dönemdeki tüm gelir ve giderleri kategorilere göre özet olarak gösterir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_kategori_baslangic" class="form-label">
                                                <i class="feather-calendar me-1"></i>Başlangıç Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="gelir_kategori_baslangic"
                                                name="gelir_kategori_baslangic" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('01.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_kategori_bitis" class="form-label">
                                                <i class="feather-calendar me-1"></i>Bitiş Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="gelir_kategori_bitis"
                                                name="gelir_kategori_bitis" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_kategori_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-select" id="gelir_kategori_format" name="gelir_kategori_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                    

                                        <div class="col-12">
                                            <div class="btn-group gap-2" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih-kategori"
                                                    data-rapor="gelir-kategori" data-range="this-month">Bu Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih-kategori"
                                                    data-rapor="gelir-kategori" data-range="last-month">Geçen Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih-kategori"
                                                    data-rapor="gelir-kategori" data-range="this-year">Bu Yıl</button>
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="gelir-gider-kategori">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="gelir-gider-kategori">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>


                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Gelir-Gider Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingGelirGider">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseGelirGider"
                                    aria-expanded="false" aria-controls="collapseGelirGider">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-info text-info border-soft-info rounded me-3">
                                            <i class="feather-trending-up fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Gelir-Gider Raporu</span>
                                            <small class="text-muted">Detaylı gelir ve gider analizi</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseGelirGider" class="accordion-collapse collapse"
                                aria-labelledby="headingGelirGider" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formGelirGider" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, belirtilen dönemdeki tüm gelir ve giderleri kategorilere göre gösterir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_baslangic" class="form-label">
                                                <i class="feather-calendar me-1"></i>Başlangıç Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="gelir_baslangic"
                                                name="gelir_baslangic" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('01.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_bitis" class="form-label">
                                                <i class="feather-calendar me-1"></i>Bitiş Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="gelir_bitis"
                                                name="gelir_bitis" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gelir_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-select" id="gelir_format" name="gelir_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">
                                                <i class="feather-filter me-1"></i>Rapor Türü
                                            </label>
                                            <div class="d-flex gap-3 flex-wrap">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="radio" name="gelir_tur" value="hepsi" checked>
                                                    <span class="form-check-label">Hepsi</span>
                                                </label>
                                                <label class="form-check">
                                                    <input class="form-check-input" type="radio" name="gelir_tur" value="gelir">
                                                    <span class="form-check-label">Sadece Gelir</span>
                                                </label>
                                                <label class="form-check">
                                                    <input class="form-check-input" type="radio" name="gelir_tur" value="gider">
                                                    <span class="form-check-label">Sadece Gider</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12 gap-2">
                                          
                                            <div class="btn-group gap-2" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="gelir" data-range="this-month">Bu Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="gelir" data-range="last-month">Geçen Ay</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary hizli-tarih"
                                                    data-rapor="gelir" data-range="this-year">Bu Yıl</button>
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="gelir-gider">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="gelir-gider">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mizan Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingMizan">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseMizan"
                                    aria-expanded="false" aria-controls="collapseMizan">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-secondary text-secondary border-soft-secondary rounded me-3">
                                            <i class="feather-grid fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Mizan Raporu</span>
                                            <small class="text-muted">Tüm hesapların borç/alacak toplamları ve bakiye</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseMizan" class="accordion-collapse collapse"
                                aria-labelledby="headingMizan" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formMizan" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Seçilen döneme göre kasa/banka, gelir/gider ve alacaklar üzerinden mizan oluşturulur.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="mizan_baslangic" class="form-label">
                                                <i class="feather-calendar me-1"></i>Başlangıç Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="mizan_baslangic"
                                                name="mizan_baslangic" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('01.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="mizan_bitis" class="form-label">
                                                <i class="feather-calendar me-1"></i>Bitiş Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="mizan_bitis"
                                                name="mizan_bitis" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="mizan_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-select" id="mizan_format" name="mizan_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv" disabled>CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="mizan">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="mizan">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Daire Bazlı Rapor -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingDaire">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseDaire"
                                    aria-expanded="false" aria-controls="collapseDaire">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-danger text-danger border-soft-danger rounded me-3">
                                            <i class="feather-home fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Daire Sakinleri Raporu</span>
                                            <small class="text-muted">Tüm daireler ve sakinleri listesini alabilecğiniz rapor</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseDaire" class="accordion-collapse collapse"
                                aria-labelledby="headingDaire" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formDaire" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, tüm daireleri, blokları ve sakinleri detaylı olarak listeler.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="daire_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-select" id="daire_format" name="daire_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" id="daire_iletisim" name="daire_iletisim" checked>
                                                <span class="form-check-label">İletişim Bilgilerini Dahil Et</span>
                                            </label>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="daire">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="daire">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>


                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Hesap Özeti Raporu (Blok Bazlı) -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingHesapOzeti">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseHesapOzeti"
                                    aria-expanded="false" aria-controls="collapseHesapOzeti">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded me-3">
                                            <i class="feather-file-text fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Toplu Hesap Özeti Raporu</span>
                                            <small class="text-muted">Blok bazlı veya tüm site için kişi hesap hareketleri</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseHesapOzeti" class="accordion-collapse collapse"
                                aria-labelledby="headingHesapOzeti" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formHesapOzeti" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bu rapor, seçilen blok veya tüm sitedeki kişilerin detaylı hesap hareketlerini gösterir.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="hesap_blok" class="form-label">
                                                <i class="feather-layers me-1"></i>Blok Seçimi
                                            </label>
                                            <select class="form-control select2" id="hesap_blok" name="hesap_blok">
                                                <option value="0">Tüm Site</option>
                                                <?php
                                                $Bloklar = new Model\BloklarModel();
                                                $bloklar = $Bloklar->SiteBloklari($_SESSION['site_id']);
                                                foreach ($bloklar as $blok):
                                                ?>
                                                    <option value="<?php echo $blok->id; ?>"><?php echo $blok->blok_adi; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Belirli bir blok veya tüm siteyi seçebilirsiniz</small>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="hesap_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-control select2" id="hesap_format" name="hesap_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" id="hesap_sadece_borclu" name="hesap_sadece_borclu">
                                                <span class="form-check-label">Sadece Borçlu Kişiler</span>
                                            </label>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="hesap-ozeti">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="hesap-ozeti">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Kişi Bazında Detaylı Hesap Dökümü -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingKisiDetay">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseKisiDetay"
                                    aria-expanded="false" aria-controls="collapseKisiDetay">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded me-3">
                                            <i class="feather-user-check fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Kişi Bazında Detaylı Hesap Dökümü</span>
                                            <small class="text-muted">Seçilen kişilerin detaylı hesap hareketleri</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseKisiDetay" class="accordion-collapse collapse"
                                aria-labelledby="headingKisiDetay" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formKisiDetay" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Bir veya birden fazla kişiyi seçerek ayrıntılı hesap dökümlerini alabilirsiniz.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <label for="kisi_ids" class="form-label">
                                                <i class="feather-users me-1"></i>Kişiler
                                            </label>
                                            <select class="form-control select2" id="kisi_ids" name="kisi_ids[]" multiple>
                                                <?php
                                                $Kisiler = new Model\KisilerModel();
                                                $kisiList = $Kisiler->getAktifKisilerBySite($_SESSION['site_id']);
                                                foreach ($kisiList as $k): ?>
                                                    <option value="<?php echo $k->id; ?>">
                                                        <?php echo ($k->blok_adi ?? '') . ' - ' . ($k->daire_kodu ?? '') . ' - ' . ($k->adi_soyadi ?? ''); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Çoklu seçim yapabilirsiniz</small>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="kisi_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-control select2" id="kisi_format" name="kisi_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>

                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="kisi-detay">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="kisi-detay">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Gecikmiş Ödemeler Raporu -->
                        <div class="accordion-item border border-dashed border-gray-300 mb-3">
                            <h2 class="accordion-header" id="headingGecikenOdemeler">
                                <button class="accordion-button collapsed bg-light" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseGecikenOdemeler"
                                    aria-expanded="false" aria-controls="collapseGecikenOdemeler">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="avatar-text avatar-lg bg-soft-danger text-danger border-soft-danger rounded me-3">
                                            <i class="feather-alert-circle fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">Gecikmiş Ödemeler</span>
                                            <small class="text-muted">Seçilen tarihten önceki gecikmiş borçların detaylı listesi</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseGecikenOdemeler" class="accordion-collapse collapse"
                                aria-labelledby="headingGecikenOdemeler" data-bs-parent="#raporlarAccordion">
                                <div class="accordion-body bg-white">
                                    <form id="formGecikenOdemeler" class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light border-0 mb-3">
                                                <i class="feather-info me-2"></i>
                                                <small>Belirttiğiniz bitiş tarihinden önceki, ödemesi eksik kalmış borçlar kişi bazında detaylı olarak listelenir.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="geciken_bitis" class="form-label">
                                                <i class="feather-calendar me-1"></i>Bitiş Tarihi
                                            </label>
                                            <input type="text" class="form-control flatpickr" id="geciken_bitis"
                                                name="geciken_bitis" placeholder="gg.aa.yyyy"
                                                value="<?php echo date('d.m.Y'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="geciken_format" class="form-label">
                                                <i class="feather-download me-1"></i>İndirme Formatı
                                            </label>
                                            <select class="form-select select2" id="geciken_format" name="geciken_format">
                                                <option value="pdf">PDF Belgesi</option>
                                                <option value="xlsx">Excel (XLSX)</option>
                                                <option value="csv">CSV Dosyası</option>
                                                <option value="html">HTML Sayfası</option>
                                            </select>
                                        </div>
                                        <hr class="my-4">
                                        <div class="col-12 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-rapor-onizle"
                                                data-rapor="geciken-odemeler">
                                                <i class="feather-eye me-2"></i>Önizle
                                            </button>
                                            <button type="button" class="btn btn-primary btn-rapor-indir"
                                                data-rapor="geciken-odemeler">
                                                <i class="feather-download me-2"></i>Raporu İndir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Flatpickr Tarihleri
    if (window.flatpickr) {
        flatpickr('.flatpickr', {
            dateFormat: 'd.m.Y',
            allowInput: true,
            locale: 'tr'
        });
    }


    //Select2 Başlatma
    $(function() {
        $('.select2').select2({
            width: '100%'
        });
    });

    // Yardımcı Fonksiyonlar
    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function dmy(d) {
        return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}`;
    }

    function ymd(d) {
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    }

    function parseDMY(s) {
        const m = s.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
        if (!m) return null;
        return new Date(parseInt(m[3]), parseInt(m[2]) - 1, parseInt(m[1]));
    }

    function toIsoFromDMY(s) {
        const d = parseDMY(s);
        return d ? ymd(d) : s;
    }

    // Hızlı Tarih Seçimi
    function rangePreset(type) {
        const now = new Date();
        const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        const firstDayOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastDayOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
        const firstDayOfYear = new Date(now.getFullYear(), 0, 1);
        const lastDayOfYear = new Date(now.getFullYear(), 11, 31);
        const firstDayOfLastYear = new Date(now.getFullYear() - 1, 0, 1);
        const lastDayOfLastYear = new Date(now.getFullYear() - 1, 11, 31);

        switch (type) {
            case 'this-month':
                return [dmy(firstDayOfMonth), dmy(lastDayOfMonth)];
            case 'last-month':
                return [dmy(firstDayOfLastMonth), dmy(lastDayOfLastMonth)];
            case 'this-year':
                return [dmy(firstDayOfYear), dmy(lastDayOfYear)];
            case 'last-year':
                return [dmy(firstDayOfLastYear), dmy(lastDayOfLastYear)];
            default:
                return [dmy(now), dmy(now)];
        }
    }

    function selectRangeDate(button, startInputId, endInputId) {
        const range = button.dataset.range;
        const [start, end] = rangePreset(range);
        $(startInputId).val(start);
        $(endInputId).val(end);
    }


    // Hızlı Tarih Butonları
    document.querySelectorAll('.hizli-tarih').forEach(btn => {
        btn.addEventListener('click', function() {
            const rapor = this.dataset.rapor;
            const range = this.dataset.range;
            const [start, end] = rangePreset(range);

            if (rapor === 'borc') {
                selectRangeDate(this, '#borc_baslangic', '#borc_bitis');
            } else if (rapor === 'gelir') {
                selectRangeDate(this, '#gelir_baslangic', '#gelir_bitis');
            }
        });
    });

    // Hızlı Tarih Butonları Kategori Bazlı
    $(document).on('click', '.hizli-tarih-kategori', function() {
        selectRangeDate(this, '#gelir_kategori_baslangic', '#gelir_kategori_bitis');
    });




    // Rapor İndirme Fonksiyonları
    document.querySelectorAll('.btn-rapor-indir').forEach(btn => {
        btn.addEventListener('click', function() {
            const rapor = this.dataset.rapor;
            let url = '';

                switch (rapor) {
                case 'hazirun':
                    const hazirunFormat = document.getElementById('hazirun_format').value;
                    const hazirunBaslik = encodeURIComponent(document.getElementById('hazirun_baslik').value);
                    const hazirunTarih = encodeURIComponent(document.getElementById('hazirun_tarih').value);
                    url = `pages/raporlar/export/hazirun-listesi.php?format=${hazirunFormat}&baslik=${hazirunBaslik}&tarih=${hazirunTarih}`;
                    break;

                case 'borc-alacak':
                    const borcBaslangic = document.getElementById('borc_baslangic').value;
                    const borcBitis = document.getElementById('borc_bitis').value;
                    const borcFormat = document.getElementById('borc_format').value;

                    if (!validateDates(borcBaslangic, borcBitis)) return;

                    const startISO = toIsoFromDMY(borcBaslangic);
                    const endISO = toIsoFromDMY(borcBitis);
                    url = `pages/raporlar/export/tarihler_arasi_borc_alacak.php?start=${encodeURIComponent(startISO)}&end=${encodeURIComponent(endISO)}&format=${borcFormat}`;
                    break;

                case 'borc-ozet':
                    const borcBaslangic2 = document.getElementById('borc_baslangic').value;
                    const borcBitis2 = document.getElementById('borc_bitis').value;
                    const borcFormat2 = document.getElementById('borc_format').value;

                    if (!validateDates(borcBaslangic2, borcBitis2)) return;

                    const startISO2 = toIsoFromDMY(borcBaslangic2);
                    const endISO2 = toIsoFromDMY(borcBitis2);
                    url = `pages/dues/payment/export/borc_bazli_ozet.php?start=${encodeURIComponent(startISO2)}&end=${encodeURIComponent(endISO2)}&format=${borcFormat2}`;
                    break;

                case 'aidat':
                    const aidatAy = document.getElementById('aidat_ay').value;
                    const aidatYil = document.getElementById('aidat_yil').value;
                    const aidatFormat = document.getElementById('aidat_format').value;
                    const aidatSadeceBorclu = document.getElementById('aidat_sadece_borclu').checked ? '1' : '0';
                    const aidatIletisim = document.getElementById('aidat_iletisim_bilgileri').checked ? '1' : '0';
                    url = `pages/raporlar/export/aidat-raporu.php?ay=${aidatAy}&yil=${aidatYil}&format=${aidatFormat}&sadece_borclu=${aidatSadeceBorclu}&iletisim_bilgileri=${aidatIletisim}`;

                    break;



                case 'gelir-gider-kategori':
                    const gelirBaslangicKategori = document.getElementById('gelir_kategori_baslangic').value;
                    const gelirBitisKategori = document.getElementById('gelir_kategori_bitis').value;
                    const gelirFormatKategori = document.getElementById('gelir_kategori_format').value;
                    const gelirKategoriTableView = document.getElementById('gelir_kategori_table_view')?.checked ? 'table' : '';

                    if (!validateDates(gelirBaslangicKategori, gelirBitisKategori)) return;

                    const gelirStartKategoriISO = toIsoFromDMY(gelirBaslangicKategori);
                    const gelirEndKategoriISO = toIsoFromDMY(gelirBitisKategori);

                    url = `pages/raporlar/export/kategori-bazli-gelir-gider-raporu.php?start=${encodeURIComponent(gelirStartKategoriISO)}&end=${encodeURIComponent(gelirEndKategoriISO)}&format=${gelirFormatKategori}${gelirKategoriTableView ? `&view=${gelirKategoriTableView}` : ''}`;
                    break;


                case 'gelir-gider':
                    const gelirBaslangic = document.getElementById('gelir_baslangic').value;
                    const gelirBitis = document.getElementById('gelir_bitis').value;
                    const gelirFormat = document.getElementById('gelir_format').value;
                    const gelirTur = document.querySelector('input[name="gelir_tur"]:checked').value;

                    if (!validateDates(gelirBaslangic, gelirBitis)) return;

                    const gelirStartISO = toIsoFromDMY(gelirBaslangic);
                    const gelirEndISO = toIsoFromDMY(gelirBitis);
                    url = `pages/raporlar/export/gelir-gider-raporu.php?start=${encodeURIComponent(gelirStartISO)}&end=${encodeURIComponent(gelirEndISO)}&format=${gelirFormat}&tur=${gelirTur}`;
                    break;

                case 'mizan':
                    const mizanBaslangic = document.getElementById('mizan_baslangic').value;
                    const mizanBitis = document.getElementById('mizan_bitis').value;
                    const mizanFormat = document.getElementById('mizan_format').value;

                    if (!validateDates(mizanBaslangic, mizanBitis)) return;

                    const mizanStartISO = toIsoFromDMY(mizanBaslangic);
                    const mizanEndISO = toIsoFromDMY(mizanBitis);
                    url = `pages/raporlar/export/mizan-raporu.php?start=${encodeURIComponent(mizanStartISO)}&end=${encodeURIComponent(mizanEndISO)}&format=${mizanFormat}`;
                    break;

                case 'daire':
                    const daireFormat = document.getElementById('daire_format').value;
                    const daireIletisim = document.getElementById('daire_iletisim').checked ? '1' : '0';
                    url = `pages/raporlar/export/daire-raporu.php?format=${daireFormat}&iletisim=${daireIletisim}`;
                    break;

                case 'hesap-ozeti':
                    const hesapBlok = document.getElementById('hesap_blok').value;
                    const hesapFormat = document.getElementById('hesap_format').value;
                    const hesapSadeceBorclu = document.getElementById('hesap_sadece_borclu').checked ? '1' : '0';

                    url = `pages/raporlar/export/blok_kisi_hesap_ozetleri_export.php?blok_id=${hesapBlok}&format=${hesapFormat}&sadece_borclu=${hesapSadeceBorclu}`;
                    break;
                case 'kisi-detay':
                    const kisiSecimler = $('#kisi_ids').val() || [];
                    const kisiFormat = document.getElementById('kisi_format').value;
                    if (!kisiSecimler.length) { alert('Lütfen en az bir kişi seçiniz.'); return; }
                    const kisiParam = encodeURIComponent(kisiSecimler.join(','));
                    url = `pages/raporlar/export/kisi_bazinda_hesap_dokumu.php?kisi_ids=${kisiParam}&format=${kisiFormat}`;
                    break;
                case 'geciken-odemeler':
                    const gecikenBitis = document.getElementById('geciken_bitis').value;
                    const gecikenFormat = document.getElementById('geciken_format').value;
                    const gecikenEndISO = toIsoFromDMY(gecikenBitis);
                    url = `pages/raporlar/export/geciken-odemeler.php?end=${encodeURIComponent(gecikenEndISO)}&format=${gecikenFormat}`;
                    break;
            }

            if (url) {
                window.open(url, '_blank');
            }
        });
    });

    // Önizleme Butonu
    document.querySelectorAll('.btn-rapor-onizle').forEach(btn => {
        btn.addEventListener('click', function() {
            const rapor = this.dataset.rapor;
            let url = '';

            switch (rapor) {
                case 'hazirun':
                    const hazirunBaslik = encodeURIComponent(document.getElementById('hazirun_baslik').value);
                    const hazirunTarih = encodeURIComponent(document.getElementById('hazirun_tarih').value);
                    url = `pages/raporlar/export/hazirun-listesi.php?format=html&baslik=${hazirunBaslik}&tarih=${hazirunTarih}`;
                    break;

                case 'borc-alacak':
                    const borcBaslangic = document.getElementById('borc_baslangic').value;
                    const borcBitis = document.getElementById('borc_bitis').value;

                    if (!validateDates(borcBaslangic, borcBitis)) return;

                    const startISO = toIsoFromDMY(borcBaslangic);
                    const endISO = toIsoFromDMY(borcBitis);
                    url = `pages/raporlar/export/tarihler_arasi_borc_alacak.php?start=${encodeURIComponent(startISO)}&end=${encodeURIComponent(endISO)}&format=html`;
                    break;

                case 'aidat':
                    const aidatAy = document.getElementById('aidat_ay').value;
                    const aidatYil = document.getElementById('aidat_yil').value;
                    const aidatSadeceBorclu = document.getElementById('aidat_sadece_borclu').checked ? '1' : '0';
                    const aidatIletisim = document.getElementById('aidat_iletisim_bilgileri').checked ? '1' : '0';
                    url = `pages/raporlar/export/aidat-raporu.php?ay=${aidatAy}&yil=${aidatYil}&format=html&sadece_borclu=${aidatSadeceBorclu}&iletisim_bilgileri=${aidatIletisim}`;
                    break;

                case 'gelir-gider-kategori':
                    const gelirBaslangicKategori = document.getElementById('gelir_kategori_baslangic').value;
                    const gelirBitisKategori = document.getElementById('gelir_kategori_bitis').value;
                    const gelirKategoriTableView2 = document.getElementById('gelir_kategori_table_view')?.checked ? 'table' : '';
                    if (!validateDates(gelirBaslangicKategori, gelirBitisKategori)) return;

                    const gelirStartKategoriISO = toIsoFromDMY(gelirBaslangicKategori);
                    const gelirEndKategoriISO = toIsoFromDMY(gelirBitisKategori);
                    url = `pages/raporlar/export/kategori-bazli-gelir-gider-raporu.php?start=${encodeURIComponent(gelirStartKategoriISO)}&end=${encodeURIComponent(gelirEndKategoriISO)}&format=html${gelirKategoriTableView2 ? `&view=${gelirKategoriTableView2}` : ''}`;
                    break;

                case 'gelir-gider':
                    const gelirBaslangic = document.getElementById('gelir_baslangic').value;
                    const gelirBitis = document.getElementById('gelir_bitis').value;
                    const gelirTur = document.querySelector('input[name="gelir_tur"]:checked').value;

                    if (!validateDates(gelirBaslangic, gelirBitis)) return;

                    const gelirStartISO = toIsoFromDMY(gelirBaslangic);
                    const gelirEndISO = toIsoFromDMY(gelirBitis);
                    url = `pages/raporlar/export/gelir-gider-raporu.php?start=${encodeURIComponent(gelirStartISO)}&end=${encodeURIComponent(gelirEndISO)}&format=html&tur=${gelirTur}`;
                    break;

                case 'mizan':
                    const mizanBaslangic = document.getElementById('mizan_baslangic').value;
                    const mizanBitis = document.getElementById('mizan_bitis').value;
                    if (!validateDates(mizanBaslangic, mizanBitis)) return;
                    const mizanStartISO = toIsoFromDMY(mizanBaslangic);
                    const mizanEndISO = toIsoFromDMY(mizanBitis);
                    url = `pages/raporlar/export/mizan-raporu.php?start=${encodeURIComponent(mizanStartISO)}&end=${encodeURIComponent(mizanEndISO)}&format=html`;
                    break;

                case 'daire':
                    const daireIletisim = document.getElementById('daire_iletisim').checked ? '1' : '0';
                    url = `pages/raporlar/export/daire-raporu.php?format=html&iletisim=${daireIletisim}`;
                    break;

                case 'hesap-ozeti':
                    const hesapBlok = document.getElementById('hesap_blok').value;
                    const hesapSadeceBorclu = document.getElementById('hesap_sadece_borclu').checked ? '1' : '0';
                    url = `pages/raporlar/export/blok_kisi_hesap_ozetleri_export.php?blok_id=${hesapBlok}&format=html&sadece_borclu=${hesapSadeceBorclu}`;
                    break;
                case 'kisi-detay':
                    const kisiSecimler2 = $('#kisi_ids').val() || [];
                    if (!kisiSecimler2.length) { alert('Lütfen en az bir kişi seçiniz.'); return; }
                    const kisiParam2 = encodeURIComponent(kisiSecimler2.join(','));
                    url = `pages/raporlar/export/kisi_bazinda_hesap_dokumu.php?kisi_ids=${kisiParam2}&format=html`;
                    break;
                case 'geciken-odemeler':
                    const gecikenBitis2 = document.getElementById('geciken_bitis').value;
                    const gecikenEndISO2 = toIsoFromDMY(gecikenBitis2);
                    url = `pages/raporlar/export/geciken-odemeler.php?end=${encodeURIComponent(gecikenEndISO2)}&format=html`;
                    break;
            }

            if (url) {
                // Yeni sekmede aç
                window.open(url, '_blank');
            }
        });
    });

    // Tarih Doğrulama
    function validateDates(start, end) {
        const sd = parseDMY(start);
        const ed = parseDMY(end);

        if (!sd || !ed) {
            alert('Lütfen geçerli tarih giriniz (gg.aa.yyyy formatında).');
            return false;
        }

        if (sd > ed) {
            alert('Başlangıç tarihi bitiş tarihinden büyük olamaz!');
            return false;
        }

        return true;
    }

    // Toast Bildirimleri (Varsa)
    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
</script>

<style>
    /* Accordion Özelleştirmeleri */
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, .125);
    }

    .accordion-body {
        padding: 1.5rem;
    }

    .avatar-text {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Form Düzenlemeleri */
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-label i {
        font-size: 14px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Buton Grupları */
    .btn-group .btn {
        white-space: nowrap;
    }

    /* Alert Kutuları */
    .alert-light {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }

    /* Responsive Ayarlar */
    @media (max-width: 768px) {
        .accordion-button {
            font-size: 0.9rem;
            padding: 1rem;
        }

        .avatar-text {
            width: 40px;
            height: 40px;
        }

        .btn-group {
            flex-direction: column;
        }
    }
</style>
