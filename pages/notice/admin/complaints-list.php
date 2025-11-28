<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yönetim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Şikayet ve Öneriler</li>
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
    $title = "Şikayet ve Öneriler!";
    $text = "Site sakinlerinden gelen şikayet ve önerileri görüntüleyebilir, işlem yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="complaintsList">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Gönderen</th>
                                            <th>Tür</th>
                                            <th>Başlık</th>
                                            <th>Mesaj</th>
                                            <th>Tarih</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Ahmet Yılmaz</td>
                                            <td><span class="badge bg-danger">Şikayet</span></td>
                                            <td>Asansör bozuk</td>
                                            <td>2 gündür çalışmıyor, acil bakım gerekiyor.</td>
                                            <td>12.04.2025</td>
                                            <td><span class="badge bg-warning">Bekliyor</span></td>
                                            <td>
                                                <div class="hstack gap-2 justify-content-center">
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Görüntüle" data-bs-target="#cevapGosterModal"> <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md archive-item" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Arşivle"> <i class="feather-archive"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md delete-item" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Sil"> <i class="feather-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>Mehmet Koç</td>
                                            <td><span class="badge bg-info">Öneri</span></td>
                                            <td>Çocuk parkı yapılsın</td>
                                            <td>Boş alana çocuklar için bir alan yapılabilir.</td>
                                            <td>10.04.2025</td>
                                            <td><span class="badge bg-success">Okundu</span></td>
                                            <td>
                                                <div class="hstack gap-2 justify-content-center">
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="modal" data-bs-target="#cevapGosterModal">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md archive-item">
                                                        <i class="feather-archive"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="avatar-text avatar-md delete-item">
                                                        <i class="feather-trash-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
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

<!-- Modal: Görüntüleme -->
<div class="modal fade" id="cevapGosterModal" tabindex="-1" aria-labelledby="cevapGosterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Şikayet/Öneri Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p><strong>Başlık:</strong> Örnek Başlık</p>
                <p><strong>Mesaj:</strong> Burada detaylı mesaj içeriği görüntülenir...</p>
                <p><strong>Gönderen:</strong> Ahmet Yılmaz</p>
                <p><strong>Tarih:</strong> 12.04.2025</p>
                <p><strong>Durum:</strong> Bekliyor</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
