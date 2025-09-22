<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takip</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">İcra Takip</li>
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
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php';
                ?>
                <a href="#" class="btn btn-primary route-link" data-page="levy/manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni İcra Başlat</span>
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
    $title = "İcra Takip Listesi!";
    $text = "Kişilere ait açılan icra dosyalarını görüntüleyebilir, tahsilat ve süreç takibi yapabilir, yeni icra takibi başlatabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
      
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="icraTakipList">
                                    <thead>
                                    <tr>
                                    <th>#</th>
                                    <th>Kişi /  Daire</th>
                                    <th>Borç Tutarı</th>
                                    <th>Başlangıç Tarihi</th>
                                    <th>Faiz Oranı (%)</th>
                                    <th>İcra Dairesi</th>
                                    <th>Dosya No</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ÖRNEK VERİLER -->
                                <tr>
                                    <td>1</td>
                                    <td>Ahmet Yılmaz / A-12</td>
                                    <td>12.500 ₺</td>
                                    <td>2025-04-01</td>
                                    <td>15</td>
                                    <td>İstanbul 5. İcra</td>
                                    <td>2025/1578</td>
                                    <td>
                                        <p><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Devam Ediyor</span></p>
                                    </td>
                                    <td class="text-end">
                                        <div class="hstack gap-2">
                                            <a href="#" class="avatar-text avatar-md route-link"  data-page="levy/detail">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="icra-duzenle.php?id=3" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="icra-sil.php?id=3" class="avatar-text avatar-md" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td>Mehmet Koç / B-5</td>
                                    <td>7.800 ₺</td>
                                    <td>2025-03-15</td>
                                    <td>12</td>
                                    <td>Bakırköy 3. İcra</td>
                                    <td>2025/1021</td>
                                    <td>
                                        <p><span class="badge bg-success text-white"><i class="fas fa-check me-1"></i>Kapandı</span></p>
                                    </td>
                                    <td class="text-end">
                                        <div class="hstack gap-2">
                                            <a href="#" class="avatar-text avatar-md route-link"  data-page="levy/detail">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="icra-duzenle.php?id=3" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="icra-sil.php?id=3" class="avatar-text avatar-md" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>3</td>
                                    <td>Ayşe Demir / C-9</td>
                                    <td>5.200 ₺</td>
                                    <td>2025-02-20</td>
                                    <td>10</td>
                                    <td>Üsküdar 1. İcra</td>
                                    <td>2025/789</td>
                                    <td>
                                        <p><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Devam Ediyor</span></p>
                                    </td>
                                    <td class="text-end">
                                        <div class="hstack gap-2">
                                            <a href="#" class="avatar-text avatar-md route-link"  data-page="levy/detail">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="icra-duzenle.php?id=3" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="icra-sil.php?id=3" class="avatar-text avatar-md" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- /ÖRNEK VERİLER -->
                            </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            
        </div>
    </div>
</div> 
