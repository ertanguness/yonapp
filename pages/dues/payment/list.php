
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borç Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Blok ve Daireye Göre Toplam Aidat Borç Takibi";
    $text = "Bu sayfada blok ve daire bazında toplam aidat borçlarını takip edebilir, detay butonu ile borç detaylarına ulaşabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="debtListTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Blok</th>
                                            <th>Daire No</th>
                                            <th>Ad Soyad</th>
                                            <th>Borç Tutarı</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Örnek borç listesi, veritabanından çekilecek
                                        $debts = [
                                            ['block' => 'A', 'flat_no' => 1, 'name' => 'Ali Veli', 'total_debt' => 500],
                                            ['block' => 'B', 'flat_no' => 2, 'name' => 'Ayşe Kaya', 'total_debt' => 800],
                                            ['block' => 'C', 'flat_no' => 3, 'name' => 'Mehmet Can', 'total_debt' => 200],
                                        ];
                                        foreach ($debts as $index => $debt):
                                        ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td><?= $debt['block']; ?></td>
                                                <td><?= $debt['flat_no']; ?></td>
                                                <td><?= $debt['name']; ?></td>
                                                <td><?= $debt['total_debt']; ?> ₺</td>
                                                
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="#" class="btn btn-light-secondary route-link px-2 py-2"
                                                            data-page="dues/payment/manage"
                                                            data-block="<?= $debt['block']; ?>"
                                                            data-flat-no="<?= $debt['flat_no']; ?>"
                                                            style="display: inline-flex; align-items: center; justify-content: center; padding: 0 10px;">
                                                            <i class="feather-eye me-2"></i>
                                                            <span>Detay</span>
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