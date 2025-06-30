<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=debts/add" class="btn btn-primary">
            <i class="feather-plus me-2"></i>
            Yeni Borç Ekle
        </a>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Borç Listesi";
    $text = "Tüm borçlandırmaları buradan yönetebilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="debtList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Başlık</th>
                                            <th>Tutar</th>
                                            <th>Son Ödeme</th>
                                            <th>Kime</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Örnek veri listesi
                                        $debtList = [
                                            ['id' => 1, 'title' => 'Asansör Tamiri', 'amount' => '250.00', 'due' => '2025-04-30', 'target' => 'Tüm Sakinler', 'status' => 'Bekliyor'],
                                            ['id' => 2, 'title' => 'Çatı Bakımı', 'amount' => '120.50', 'due' => '2025-05-10', 'target' => 'Blok A', 'status' => 'Ödendi'],
                                        ];

                                        $i = 1;
                                        foreach ($debtList as $debt):
                                        ?>
                                            <tr class="text-center">
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $debt['title']; ?></td>
                                                <td><?php echo $debt['amount']; ?> ₺</td>
                                                <td><?php echo $debt['due']; ?></td>
                                                <td><?php echo $debt['target']; ?></td>
                                                <td>
                                                    <?php if ($debt['status'] == 'Ödendi'): ?>
                                                        <span class="badge bg-success">Ödendi</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Bekliyor</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2">
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Görüntüle">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Düzenle">
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Sil">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
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
</div>
