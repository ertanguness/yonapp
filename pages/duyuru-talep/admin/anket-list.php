<?php \App\Services\Gate::authorizeOrDie('survey_admin_page'); ?>
<?php $rows = (new \Model\AnketModel())->all(); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anket Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anket Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">   
            <a href="/anket-ekle" class="btn btn-primary route-link">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Anket Oluştur</span>
                </a>
        </div>
    </div>
</div>

<div class="main-content">
      <?php
    $title = "Anket Listesi";
    $text = "Yayınlanan anketlerinizi görüntüleyebilir, detaylara ulaşabilir ve düzenleme işlemleri yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <style>
        .table-responsive{ overflow: visible !important; }
        .dropdown-menu{ z-index: 1090; }
    </style>
    <div class="row">
        <div class="container-xl">
            
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive" style="overflow: visible;">
                            <table class="table table-hover table-bordered datatables" id="surveyList">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>Oluşturulma</th>
                                        <th>Bitiş</th>
                                        <th>Durum</th>
                                        <th>Oy</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $idx => $r): ?>
                                        <?php $idEnc = \App\Helper\Security::encrypt($r->id); ?>
                                        <?php $statusBadge = ($r->status === 'Aktif') ? 'success' : (($r->status === 'Taslak') ? 'warning' : 'secondary'); ?>
                                        <tr>
                                            <td><?= $idx+1 ?></td>
                                            <td><?= htmlspecialchars($r->title) ?></td>
                                            <td><?= htmlspecialchars($r->created_at ?? '') ?></td>
                                            <td><?= htmlspecialchars($r->end_date ?? '') ?></td>
                                            <td><span class="badge bg-<?= $statusBadge ?>"><?= htmlspecialchars($r->status ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($r->total_votes ?? 0) ?></td>
                                            <td>
                                                <div class="btn-group align-items-baseline">
                                                    <a href="/anket-ekle?survey_id=<?= (int)$r->id ?>" class="btn btn-outline-primary btn-sm route-link"><i class="feather-edit-2"></i> Düzenle</a>
                                                    <button class="btn btn-outline-danger btn-sm btn-del" data-id="<?= $idEnc ?>"><i class="feather-trash-2"></i> Sil</button>
                                                    <a href="/anket-sonuc/<?= $idEnc ?>" class="btn btn-outline-info btn-sm route-link"><i class="feather-bar-chart-2"></i> Sonuçlar</a>
                                                    <div class="dropdown d-inline-block ms-1">
                                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="feather-shuffle"></i> Durum</button>
                                                        <div class="dropdown-menu">
                                                            <?php foreach (["Taslak","Onay Bekliyor","Yayında","Aktif","Pasif","Reddedildi"] as $st): ?>
                                                                <a href="#" class="dropdown-item change-status" data-id="<?= (int)$r->id ?>" data-status="<?= $st ?>"><?= $st ?></a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
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

<script src="/pages/duyuru-talep/admin/js/anket.js"></script>
