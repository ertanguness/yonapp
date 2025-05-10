<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anket Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anket Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">   
            <a href="#" class="btn btn-primary route-link" data-page="notice/admin/survey-manage">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Anket Oluştur</span>
                </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>Bitiş Tarihi</th>
                                        <th>Durum</th>
                                        <th>Toplam Oy</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Örnek Satır -->
                                    <tr>
                                        <td>1</td>
                                        <td>Yeni otopark düzenlemesi hakkında ne düşünüyorsunuz?</td>
                                        <td>2025-05-01</td>
                                        <td><span class="badge bg-success">Aktif</span></td>
                                        <td>48</td>
                                        <td>
                                            <div class="btn-group align-items-baseline">
                                                <a href="#" class="btn btn-outline-info btn-sm route-link" data-page="notice/admin/survey-result">
                                                <i class="feather-bar-chart-2"></i> Sonuçlar
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm">
                                                    <i class="feather-trash-2"></i> Sil
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- /Örnek Satır -->
                                    <tr>
                                        <td>2</td>
                                        <td>Giriş güvenliği artırılsın mı?</td>
                                        <td>2025-04-10</td>
                                        <td><span class="badge bg-secondary">Sona Erdi</span></td>
                                        <td>75</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="index?p=polls/admin/results&id=2" class="btn btn-outline-info btn-sm">
                                                    <i class="feather-bar-chart-2"></i> Sonuçlar
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm">
                                                    <i class="feather-trash-2"></i> Sil
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Daha fazla anket burada listelenebilir -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
