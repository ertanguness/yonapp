<?php
use Model\KasaModel;
use App\Helper\Helper;
use App\Helper\Security;

$Kasa = new KasaModel();


$kasalar = $Kasa->SiteKasaListesiFinansOzet($_SESSION['site_id'] ?? 0);

?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Kasa Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <a href="#" class="btn btn-primary route-link" data-page="finans-yonetimi/kasa/duzenle">
                <i class="feather-plus me-2"></i>
                Yeni Kasa Ekle
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = 'Kasa Listesi!';
    $text = 'Tanımlı kasalarınızı görüntüleyebilir, yeni kasa ekleyebilir veya düzenleyebilirsiniz. Gelir/Gider işlemleri için varsayılan kasayı unutmayın!';
    require_once 'pages/components/alert.php';
    ?>

    <div class="row row-deck row-cards mb-5 ">
        <div class="col-12">
            <div class="card mb-5">
                <div class="card-header">
                    <h4 class="card-title">Tüm Kasalar</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered datatables">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kasa Adı</th>
                                <th>Kasa Türü</th>
                                <th>Banka Adı</th>
                                <th>Şube Adı</th>
                                <th>İban No</th>
                                <th>Varsayılan mı?</th>
                                <th>Gelir-Gider</th>
                                <th>Açıklama</th>
                                <th class="text-center">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Örnek veri — Gerçek veriler veritabanından alınmalı

                            foreach ($kasalar as $kasa) {
                                $enc_id =Security::encrypt($kasa->id);
                              ?>

                            <tr>
                                <td><?= $kasa->id; ?></td>
                                <td><?= $kasa->kasa_adi; ?></td>
                                <td><?= $kasa->kasa_tipi; ?></td>
                                <td><?= $kasa->banka_adi ?: '-'; ?></td>
                                <td><?= $kasa->sube_kodu ?: '-'; ?></td>
                                <td><?= $kasa->iban ?: '-'; ?></td>
                                <td>
                                    <?php if ($kasa->varsayilan_mi): ?>
                                    <span class="badge bg-success">Evet</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Hayır</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                  
                                        <div class="hstack gap-2 text-muted text-success mb-2">
                                            <div class="avatar-text avatar-sm">
                                                <i class="feather-trending-up"></i>
                                            </div>
                                            <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($kasa->toplam_gelir) ?></span>
                                        </div>
                                
                                        <div class="hstack gap-2 text-muted text-danger mb-2">
                                            <div class="avatar-text avatar-sm">
                                                <i class="feather-trending-down"></i>
                                            </div>
                                            <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($kasa->toplam_gider) ?></span>
                                        </div>
                                        <div class="hstack gap-2 text-muted mb-2">
                                            <div class="avatar-text avatar-sm">
                                                <i class="feather-archive"></i>
                                            </div>
                                            <span class="text-truncate-1-line"><?php echo Helper::formattedMoney($kasa->bakiye) ?></span>
                                        </div>
                                 
                                </td>
                                <td><?= $kasa->aciklama ?: '-'; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md"
                                            data-bs-toggle="dropdown" data-bs-offset="0,21" aria-expanded="false">
                                            <i class="feather feather-more-horizontal"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)">
                                                    <i class="feather feather-edit-3 me-3"></i>
                                                    <span>Düzenle</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item printBTN" href="javascript:void(0)">
                                                    <i class="feather feather-refresh-ccw me-3"></i>
                                                    <span>Transfer Yap</span>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item" href="index?p=finans-yonetimi/kasa/hareketler&id=<?php echo $enc_id; ?>#kasa-hareketleri">
                                                    <i class="feather feather-alert-octagon me-3"></i>
                                                    <span>Hareketleri Göster</span>
                                                </a>
                                            </li>
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item bg-danger text-white" id="kasa-sil"
                                                    href="javascript:void(0)">
                                                    <i class="feather feather-trash-2 me-3"></i>
                                                    <span>Sil</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Kasa Detay Modal -->

<div class="modal fade" id="kasaDetayModal" tabindex="-1" aria-labelledby="kasaDetayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-3 shadow-sm border-0">

            <!-- Başlık -->
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title mb-0" id="kasaDetayModalLabel">
                    <i class="feather-info me-2"></i> Kasa Detayları
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"
                    aria-label="Kapat"></button>
            </div>

            <!-- İçerik -->
            <div class="modal-body px-4 py-3">
                <div class="mb-2">
                    <small class="text-muted d-block">Kasa Adı</small>
                    <div id="detayKasaAdi" class="fw-semibold text-dark">-</div>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Kasa Türü</small>
                    <div id="detayKasaTur" class="fw-semibold text-dark">-</div>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Para Birimi</small>
                    <div id="detayKasaKur" class="fw-semibold text-dark">-</div>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Varsayılan mı?</small>
                    <div id="detayKasaDefault" class="fw-semibold text-dark">-</div>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Güncel Bakiye</small>
                    <div id="detayKasaBakiye" class="fw-semibold text-dark">-</div>
                </div>

                <div class="mb-1">
                    <small class="text-muted d-block">Açıklama</small>
                    <div id="detayKasaAciklama" class="fw-semibold text-dark">-</div>
                </div>
            </div>

            <!-- Alt Buton -->
            <div class="modal-footer border-0 px-4 pt-0 pb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>

        </div>
    </div>
</div>


<script>
function showKasaDetails(kasa) {
    document.getElementById('detayKasaAdi').innerText = kasa.adi || '-';
    document.getElementById('detayKasaTur').innerText = kasa.tur || '-';
    document.getElementById('detayKasaKur').innerText = kasa.kur || '-';
    document.getElementById('detayKasaDefault').innerHTML = kasa.default ?
        '<span class="badge bg-success">Evet</span>' : '<span class="badge bg-secondary">Hayır</span>';
    document.getElementById('detayKasaBakiye').innerText = new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(kasa.bakiye);
    document.getElementById('detayKasaAciklama').innerText = kasa.aciklama || '-';
}
</script>