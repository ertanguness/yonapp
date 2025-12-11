<?php

use App\Helper\FinansalHelper;
use App\Helper\Security;
use App\Helper\Helper;
use App\Services\Gate;
use App\Services\BankaApiService;
use Model\KasaModel;
use Model\TahsilatHavuzuModel;

Gate::authorizeOrDie("income_expense_add_update");

$Kasa = new KasaModel();
$TahsilatHavuzu = new TahsilatHavuzuModel();

$site_id = $_SESSION['site_id'] ?? 0;

// Form parametrelerini al
$banka_id = $_GET['banka_id'] ?? null;
$baslangic_tarihi = $_GET['baslangic_tarihi'] ?? null;
$bitis_tarihi = $_GET['bitis_tarihi'] ?? null;
$hareket_yonu = $_GET['hareket_yonu'] ?? '';

// Parametreleri kontrol et
if (!$banka_id || !$baslangic_tarihi || !$bitis_tarihi) {
    echo "<script>window.location.href = '/banka-hesap-sorgula';</script>";
    exit;
}

// Banka bilgisini al
$banka = $Kasa->getById($banka_id);
if (!$banka) {
    echo "<script>alert('Banka bulunamadı!'); window.location.href = '/banka-hesap-sorgula';</script>";
    exit;
}

// Tarihleri SQL formatına çevir
$baslangic_sql = DateTime::createFromFormat('d.m.Y', $baslangic_tarihi);
$bitis_sql = DateTime::createFromFormat('d.m.Y', $bitis_tarihi);

if (!$baslangic_sql || !$bitis_sql) {
    echo "<script>alert('Geçersiz tarih formatı!'); window.location.href = '/banka-hesap-sorgula';</echo>";
    exit;
}

$baslangic_tarihi_sql = $baslangic_sql->format('Y-m-d');
$bitis_tarihi_sql = $bitis_sql->format('Y-m-d');

// Banka API servisini başlat
// TODO: Gerçek banka kodu ve credentials database'den veya config'den alınacak
$bankaApi = new BankaApiService('demo', [
    'api_url' => $banka->api_url ?? '',
    'api_key' => $banka->api_key ?? '',
    'api_secret' => $banka->api_secret ?? ''
]);

// Banka API'sinden işlemleri çek
$apiIslemler = $bankaApi->getHesapHareketleri(
    $banka->hesap_no ?? $banka->iban ?? '',
    $baslangic_tarihi_sql,
    $bitis_tarihi_sql
);

// Yeni işlemleri tahsilat havuzuna ekle
$yeniIslemSayisi = 0;
$mevcutIslemSayisi = 0;

foreach ($apiIslemler as $islem) {
    // Hareket yönü filtresi varsa uygula
    if (!empty($hareket_yonu) && $islem['hareket_yonu'] !== $hareket_yonu) {
        continue;
    }
    
    // Bu işlem daha önce eklenmiş mi kontrol et
    if ($TahsilatHavuzu->isRefExists($islem['banka_ref_no'], $site_id)) {
        $mevcutIslemSayisi++;
        continue;
    }
    
    // Sakin bilgilerini çıkarmaya çalış
    $residentInfo = $bankaApi->extractResidentInfo($islem['aciklama']);
    
    // Havuza ekle
    $inserted = $TahsilatHavuzu->insertBankaIslemi([
        'site_id' => $site_id,
        'kasa_id' => $banka_id,
        'islem_tarihi' => $islem['islem_tarihi'],
        'aciklama' => $islem['aciklama'] . 
            ($residentInfo['confidence'] > 50 ? " [Otomatik: {$residentInfo['isim']} - {$residentInfo['daire_no']}]" : ""),
        'tahsilat_tutari' => $islem['tutar'],
        'kalan_tutar' => $islem['tutar'],
        'hareket_yonu' => $islem['hareket_yonu'],
        'banka_ref_no' => $islem['banka_ref_no']
    ]);
    
    if ($inserted) {
        $yeniIslemSayisi++;
    }
}

// Havuzdaki tüm işlemleri getir (bu banka ve tarih aralığı için)
$hareketler = $TahsilatHavuzu->TahsilatHavuzu($site_id);

// API'den gelen işlemlerle özet hesaplamalar
$toplam_gelen = 0;
$toplam_giden = 0;
$hareket_sayisi = count($apiIslemler);
$eslesmeyen_sayisi = 0;

foreach ($apiIslemler as $islem) {
    // Hareket yönü filtresi
    if (!empty($hareket_yonu) && $islem['hareket_yonu'] !== $hareket_yonu) {
        continue;
    }
    
    if ($islem['hareket_yonu'] === 'Gelir') {
        $toplam_gelen += $islem['tutar'];
    } else {
        $toplam_giden += $islem['tutar'];
    }
}

$net_hareket = $toplam_gelen - $toplam_giden;

// Eşleşmemiş işlem sayısı
$eslesmeyen_sayisi = $TahsilatHavuzu->getEslesmeyenSayisi($site_id);

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Banka Hesap Hareketleri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="/banka-hesap-sorgula">Hesap Hareketleri</a></li>
            <li class="breadcrumb-item">Sonuçlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <a href="/banka-hesap-sorgula" class="btn btn-light-brand">
                <i class="feather-arrow-left me-2"></i>Yeni Sorgulama
            </a>
            <div class="dropdown" data-bs-toggle="tooltip" data-bs-placement="top" title="Verileri Dışa Aktar">
                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside">
                    <i class="feather-download"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="pdf">
                        <i class="bi bi-filetype-pdf me-3"></i>
                        <span>PDF</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="csv">
                        <i class="bi bi-filetype-csv me-3"></i>
                        <span>CSV</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="excel">
                        <i class="bi bi-filetype-exe me-3"></i>
                        <span>Excel</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="print">
                        <i class="bi bi-printer me-3"></i>
                        <span>Yazdır</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    
    <?php if ($yeniIslemSayisi > 0 || $mevcutIslemSayisi > 0): ?>
    <!-- Senkronizasyon Bildirimi -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="feather-info me-3" style="font-size: 24px;"></i>
                <div>
                    <strong>Banka API Senkronizasyonu Tamamlandı</strong><br>
                    <span class="text-muted">
                        <?= $yeniIslemSayisi ?> yeni işlem havuza eklendi. 
                        <?= $mevcutIslemSayisi ?> işlem zaten sistemde kayıtlı.
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($eslesmeyen_sayisi > 0): ?>
    <!-- Eşleşmemiş İşlem Uyarısı -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="feather-alert-triangle me-3" style="font-size: 24px;"></i>
                <div class="flex-grow-1">
                    <strong><?= $eslesmeyen_sayisi ?> Eşleşmemiş Tahsilat Bulundu!</strong><br>
                    <span class="text-muted">Bu işlemler henüz bir daireye atanmadı. Lütfen eşleştirme işlemini yapın.</span>
                </div>
                <a href="/eslesmeyen-odemeler" class="btn btn-warning">
                    <i class="feather-link me-2"></i>Eşleştir
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Sorgu Bilgileri -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>Banka:</strong><br>
                            <?= htmlspecialchars($banka->kasa_adi) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>IBAN:</strong><br>
                            <?= htmlspecialchars($banka->iban ?? '-') ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Tarih Aralığı:</strong><br>
                            <?= $baslangic_tarihi ?> - <?= $bitis_tarihi ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Hareket Yönü:</strong><br>
                            <?= $hareket_yonu ? ($hareket_yonu === 'Gelir' ? 'Gelen' : 'Giden') : 'Tümü' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Özet Kartlar -->
    <div class="row">
        <div class="col-xxl-3 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4"><?= Helper::formattedMoney($toplam_gelen) ?></h5>
                        <span class="text-muted">Toplam Gelen</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-success text-white rounded">
                        <i class="feather-trending-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4"><?= Helper::formattedMoney($toplam_giden) ?></h5>
                        <span class="text-muted">Toplam Giden</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-danger text-white rounded">
                        <i class="feather-trending-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4"><?= Helper::formattedMoney($net_hareket) ?></h5>
                        <span class="text-muted">Net Hareket</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-primary text-white rounded">
                        <i class="feather-bar-chart-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4"><?= number_format($hareket_sayisi, 0, ',', '.') ?></h5>
                        <span class="text-muted">Toplam İşlem</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-info text-white rounded">
                        <i class="feather-list"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste Tablosu -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Banka API İşlemleri</h5>
                    <p class="text-muted mb-0">Bankadan çekilen işlemler aşağıda listelenmektedir</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="bankaHareketleriTable" class="table table-hover table-bordered datatables">
                            <thead >
                                <tr>
                                    <th>Tarih</th>
                                    <th>Referans No</th>
                                    <th>İşlem Türü</th>
                                    <th>Tutar</th>
                                    <th>Açıklama</th>
                                    <th>Otomatik Eşleşme</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($apiIslemler)): ?>
                                    <?php foreach ($apiIslemler as $islem): ?>
                                        <?php
                                            // Hareket yönü filtresi
                                            if (!empty($hareket_yonu) && $islem['hareket_yonu'] !== $hareket_yonu) {
                                                continue;
                                            }
                                            
                                            // Tarih
                                            $tarih = date('d.m.Y H:i', strtotime($islem['islem_tarihi']));
                                            
                                            // İşlem tipi
                                            $islemTipiHtml = ($islem['hareket_yonu'] === 'Gelir')
                                                ? '<span class="badge bg-success">Gelen</span>'
                                                : '<span class="badge bg-danger">Giden</span>';
                                            
                                            // Tutar
                                            $tutarHtml = ($islem['hareket_yonu'] === 'Gelir')
                                                ? '<span class="text-success fw-bold">+' . Helper::formattedMoney($islem['tutar']) . '</span>'
                                                : '<span class="text-danger fw-bold">-' . Helper::formattedMoney($islem['tutar']) . '</span>';
                                            
                                            // Açıklama
                                            $aciklama = htmlspecialchars($islem['aciklama']);
                                            
                                            // Referans no
                                            $refNo = htmlspecialchars($islem['banka_ref_no']);
                                            
                                            // Otomatik eşleşme analizi
                                            $residentInfo = $bankaApi->extractResidentInfo($islem['aciklama']);
                                            
                                            $eslesmeBadge = '';
                                            if ($residentInfo['confidence'] >= 70) {
                                                $eslesmeBadge = '<span class="badge bg-success-subtle text-success"><i class="feather-check-circle me-1"></i>Yüksek (' . $residentInfo['confidence'] . '%)</span>';
                                                $eslesmeDetay = ($residentInfo['isim'] ? $residentInfo['isim'] . '<br>' : '') .
                                                               ($residentInfo['daire_no'] ? 'Daire: ' . $residentInfo['daire_no'] : '');
                                            } elseif ($residentInfo['confidence'] >= 40) {
                                                $eslesmeBadge = '<span class="badge bg-warning-subtle text-warning"><i class="feather-alert-circle me-1"></i>Orta (' . $residentInfo['confidence'] . '%)</span>';
                                                $eslesmeDetay = ($residentInfo['isim'] ? $residentInfo['isim'] . '<br>' : '') .
                                                               ($residentInfo['daire_no'] ? 'Daire: ' . $residentInfo['daire_no'] : '');
                                            } else {
                                                $eslesmeBadge = '<span class="badge bg-secondary-subtle text-secondary"><i class="feather-x-circle me-1"></i>Düşük (' . $residentInfo['confidence'] . '%)</span>';
                                                $eslesmeDetay = '<small class="text-muted">Manuel eşleştirme gerekli</small>';
                                            }
                                            
                                            // Durum kontrolü (havuzda var mı?)
                                            $durumBadge = $TahsilatHavuzu->isRefExists($refNo, $site_id)
                                                ? '<span class="badge bg-primary"><i class="feather-database me-1"></i>Havuzda</span>'
                                                : '<span class="badge bg-light text-dark"><i class="feather-clock me-1"></i>Yeni</span>';
                                        ?>
                                        <tr>
                                            <td class="fs-11"><?= $tarih ?></td>
                                            <td class="fs-11"><code><?= $refNo ?></code></td>
                                            <td><?= $islemTipiHtml ?></td>
                                            <td><?= $tutarHtml ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" data-bs-toggle="tooltip" title="<?= $aciklama ?>">
                                                    <?= $aciklama ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $eslesmeBadge ?><br>
                                                <small class="text-muted"><?= $eslesmeDetay ?></small>
                                            </td>
                                            <td><?= $durumBadge ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="feather-info fs-3 mb-2"></i><br>
                                            Seçilen tarih aralığında hareket bulunamadı.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // Tooltip'leri başlat
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Export işlemleri için örnek
    $('.export').on('click', function() {
        var format = $(this).data('format');
        var url = '/banka-hesap-export?format=' + format + 
                  '&banka_id=<?= $banka_id ?>' + 
                  '&baslangic_tarihi=<?= $baslangic_tarihi ?>' + 
                  '&bitis_tarihi=<?= $bitis_tarihi ?>' + 
                  '&hareket_yonu=<?= $hareket_yonu ?>';
        
        if (format === 'print') {
            window.print();
        } else {
            window.location.href = url;
        }
    });

    // Silme işlemi
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Silme işlemi burada yapılacak
                window.location.href = '/gelir-gider-sil/' + id;
            }
        });
    });
});
</script>
