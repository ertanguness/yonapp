<?php

use App\Helper\FinansalHelper;
use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaModel;
use Model\TahsilatHavuzuModel;

Gate::authorizeOrDie("income_expense_add_update");

$Kasa = new KasaModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();

// Sadece banka hesaplarını al
$bankalar = $Kasa->getBankaHesaplari();

// Eşleşmemiş tahsilat sayısını al
$site_id = $_SESSION['site_id'] ?? 0;
$eslesmeyen_sayisi = $TahsilatHavuzu->getEslesmeyenSayisi($site_id);

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Banka Hesap Hareketleri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Banka Hesap Hareketleri Sorgula</li>
        </ul>
    </div>
</div>

<div class="main-content mb-5">
    
    <?php if ($eslesmeyen_sayisi > 0): ?>
    <!-- Eşleşmemiş İşlem Bildirimi -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="feather-alert-triangle me-3" style="font-size: 24px;"></i>
                <div class="flex-grow-1">
                    <strong><?= $eslesmeyen_sayisi ?> Eşleşmemiş Banka İşlemi Bulundu!</strong><br>
                    <span class="text-muted">Daha önce bankadan çekilen ancak henüz bir daireye atanmamış işlemler var.</span>
                </div>
                <a href="/eslesmeyen-odemeler" class="btn btn-warning">
                    <i class="feather-link me-2"></i>Şimdi Eşleştir
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-1">Banka API Sorgulama</h5>
                    <p class="text-muted small mb-0">Bankadan gerçek zamanlı hesap hareketlerini çekin ve otomatik eşleştirin</p>
                </div>
                <div class="card-body">
                    <form id="bankaSorguForm" method="GET" action="/banka-hesap-hareketleri">
                        <div class="row">
                            <!-- Banka Seçimi -->
                            <div class="col-md-6 mb-3">
                                <label for="banka_id" class="form-label">Banka Hesabı *</label>
                                <select name="banka_id" id="banka_id" class="form-select select2" required>
                                    <option value="">Banka Seçiniz</option>
                                    <?php foreach ($bankalar as $banka): ?>
                                        <option value="<?= $banka->id ?>">
                                            <?= htmlspecialchars($banka->kasa_adi) ?> 
                                            <?= $banka->iban ? '(' . htmlspecialchars($banka->iban) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Hareket Yönü -->
                            <div class="col-md-6 mb-3">
                                <label for="hareket_yonu" class="form-label">Hareket Yönü</label>
                                <select name="hareket_yonu" id="hareket_yonu" class="form-select select2">
                                    <option value="">Tümü</option>
                                    <option value="Gelir">Gelen</option>
                                    <option value="Gider">Giden</option>
                                </select>
                            </div>

                            <!-- Başlangıç Tarihi -->
                            <div class="col-md-6 mb-3">
                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi *</label>
                                <input type="text" 
                                       class="form-control flatpickr" 
                                       name="baslangic_tarihi" 
                                       id="baslangic_tarihi" 
                                       placeholder="Tarih seçiniz"
                                       required>
                            </div>

                            <!-- Bitiş Tarihi -->
                            <div class="col-md-6 mb-3">
                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi *</label>
                                <input type="text" 
                                       class="form-control flatpickr" 
                                       name="bitis_tarihi" 
                                       id="bitis_tarihi" 
                                       placeholder="Tarih seçiniz"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <small><strong>*</strong> işaretli alanlar zorunludur.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end d-flex justify-content-start gap-2">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="feather-x me-2"></i>Temizle
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-search me-2"></i>Sorgula
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nasıl Çalışır? -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info-subtle">
                    <h6 class="card-title text-info mb-0">
                        <i class="feather-info me-2"></i>Banka API Entegrasyonu Nasıl Çalışır?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="avatar-text avatar-lg bg-info-subtle text-info rounded-circle mx-auto mb-2">
                                <i class="feather-database" style="font-size: 24px;"></i>
                            </div>
                            <h6>1. API Çekimi</h6>
                            <p class="small text-muted">Seçtiğiniz tarih aralığındaki işlemler banka API'sinden gerçek zamanlı olarak çekilir.</p>
                        </div>
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="avatar-text avatar-lg bg-success-subtle text-success rounded-circle mx-auto mb-2">
                                <i class="feather-cpu" style="font-size: 24px;"></i>
                            </div>
                            <h6>2. Otomatik Analiz</h6>
                            <p class="small text-muted">İşlem açıklamaları analiz edilerek sakin ve daire bilgileri otomatik tespit edilir.</p>
                        </div>
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="avatar-text avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-2">
                                <i class="feather-link" style="font-size: 24px;"></i>
                            </div>
                            <h6>3. Eşleştirme</h6>
                            <p class="small text-muted">Yüksek güvenilirlikte eşleşenler otomatik, düşük güvenilirlikte olanlar manuel eşleştirilir.</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="avatar-text avatar-lg bg-primary-subtle text-primary rounded-circle mx-auto mb-2">
                                <i class="feather-check-circle" style="font-size: 24px;"></i>
                            </div>
                            <h6>4. Tahsilat Kaydı</h6>
                            <p class="small text-muted">Eşleşen işlemler sakinlerin hesaplarına tahsilat olarak kaydedilir.</p>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-light mb-0">
                        <strong>Not:</strong> Sistem şu anda <span class="badge bg-primary">DEMO</span> modunda çalışmaktadır. 
                        Gerçek banka API entegrasyonu için lütfen sistem yöneticinizle iletişime geçin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // Select2 initialization
    $('#banka_id').select2({
        placeholder: 'Banka Seçiniz',
    });

    // Flatpickr initialization - Başlangıç Tarihi
    $('#baslangic_tarihi').flatpickr({
        dateFormat: 'd.m.Y',
        locale: 'tr',
        defaultDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1), // Ayın ilk günü
        maxDate: 'today'
    });

    // Flatpickr initialization - Bitiş Tarihi
    $('#bitis_tarihi').flatpickr({
        dateFormat: 'd.m.Y',
        locale: 'tr',
        defaultDate: 'today',
        maxDate: 'today'
    });

    // Form validation
    $('#bankaSorguForm').on('submit', function(e) {
        var bankaId = $('#banka_id').val();
        var baslangicTarihi = $('#baslangic_tarihi').val();
        var bitisTarihi = $('#bitis_tarihi').val();

        if (!bankaId || !baslangicTarihi || !bitisTarihi) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Uyarı',
                text: 'Lütfen tüm zorunlu alanları doldurunuz.'
            });
            return false;
        }
    });
});
</script>
