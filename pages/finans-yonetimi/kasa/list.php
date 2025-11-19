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
            <a href="/kasa-ekle" class="btn btn-primary route-link">
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
                                $enc_id = Security::encrypt($kasa->id);
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
                                            <span class="badge bg-success is-default cursor-pointer"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Varsayılan kasa."
                                                data-id="<?= $enc_id ?>">Evet</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary is-default cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Varsayılan kasa yapmak için tıklayın."
                                                data-id="<?= $enc_id ?>">Hayır</span>
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
                                                    <a class="dropdown-item" href="/kasa-duzenle/<?php echo $enc_id; ?>">
                                                        <i class="feather feather-edit-3 me-3"></i>
                                                        <span>Düzenle</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item kasa-transfer"
                                                       href="javascript:void(0)"
                                                       data-source-id="<?= $enc_id ?>"
                                                       data-source-name="<?= htmlspecialchars($kasa->kasa_adi) ?>"
                                                       data-source-bakiye="<?= (float)($kasa->bakiye ?? 0) ?>">
                                                        <i class="feather feather-refresh-ccw me-3"></i>
                                                        <span>Transfer Yap</span>
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item" href="/gelir-gider-islemleri/<?php echo $enc_id; ?>">
                                                        <i class="feather feather-alert-octagon me-3"></i>
                                                        <span>Hareketleri Göster</span>
                                                    </a>
                                                </li>
                                                <li class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item bg-danger text-white kasa-sil" data-id="<?php echo $enc_id; ?>"
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


    $(document).on('click', '.kasa-transfer', function () {
        const encId = this.getAttribute('data-source-id');
        const name = this.getAttribute('data-source-name');
        const bakiye = parseFloat(this.getAttribute('data-source-bakiye') || '0');

        document.getElementById('transferSourceId').value = encId;
        document.getElementById('transferSourceName').textContent = name || '-';
        document.getElementById('transferSourceBalance').textContent = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(bakiye);

        const $sel = $('#target_kasa_id');
        $sel.empty();
        $sel.append(new Option('Seçiniz', ''));
        (window.kasalarOptions || []).forEach(function(k){
            if (k.enc !== encId) {
                $sel.append(new Option(k.name, k.enc));
            }
        });

        const modalEl = document.getElementById('kasaTransferModal');
        const m = new bootstrap.Modal(modalEl);
        m.show();

        $("#kasaTransferModal .select2").select2({ dropdownParent: $("#kasaTransferModal") });
        $("#transfer_tarih").flatpickr({ dateFormat: "Y-m-d", locale: "tr", defaultDate: new Date() });
    });
</script>

<!-- Kasa Transfer Modal -->
<div class="modal fade" id="kasaTransferModal" tabindex="-1" aria-labelledby="kasaTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-3 shadow-sm border-0">
            <div class="modal-header py-3 px-3">
                <h5 class="modal-title mb-0 d-flex align-items-center gap-2" id="kasaTransferModalLabel">
                    <span class="avatar-text avatar-sm bg-white text-primary rounded"><i class="feather-refresh-ccw"></i></span>
                    <span>Transfer: <span id="transferSourceName" class="fw-semibold">-</span></span>
                </h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
            <form id="kasaTransferForm" method="post">
                <div class="modal-body px-4 py-4">
                    <div class="card card-body bg-light mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Mevcut Bakiye</span>
                            <span id="transferSourceBalance" class="fw-bold text-dark"></span>
                        </div>
                    </div>

                    <input type="hidden" name="action" value="kasa_transfer">
                    <input type="hidden" name="source_kasa_id" id="transferSourceId" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::csrf(); ?>">

                    <div class="mb-3">
                        <label for="target_kasa_id" class="form-label">Hedef Kasa</label>
                        <select class="form-control select2" id="target_kasa_id" name="target_kasa_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_tutar" class="form-label">Transfer Tutarı</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="feather-dollar-sign"></i></span>
                            <input type="text" class="form-control money" id="transfer_tutar" name="transfer_tutar" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="transfer_tarih" class="form-label">Transfer Tarihi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="feather-calendar"></i></span>
                            <input type="text" class="form-control flatpickr" id="transfer_tarih" name="transfer_tarih" required>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="transfer_aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="transfer_aciklama" name="transfer_aciklama" minlength="10" required placeholder="Transfer nedeni, referans vb."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pt-0 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="kasaTransferSubmit">Transfer Yap</button>
                </div>
            </form>
        </div>
    </div>
    </div>
<script>
    window.kasalarOptions = [
        <?php foreach ($kasalar as $k) { ?>
        { enc: "<?php echo Security::encrypt($k->id); ?>", name: "<?php echo htmlspecialchars($k->kasa_adi); ?>" },
        <?php } ?>
    ];
</script>