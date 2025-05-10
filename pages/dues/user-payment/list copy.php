<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borçlarım</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlarım</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=dues/user-payment/manage" class="btn btn-success">
            <i class="feather-credit-card me-2"></i>
            Borç Öde
        </a>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatables">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Başlık</th>
                                <th>Tutar (₺)</th>
                                <th>Ceza Tutarı (₺)</th>
                                <th>Son Tarih</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><i class="fas fa-file-invoice me-1 text-primary"></i> Ocak Aidatı</td>
                                <td><strong>500.00</strong></td>
                                <td><strong>25.00</strong></td>
                                <td>31.01.2025</td>
                                <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                <td>
                                    <div class="hstack gap-2"></div>
                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Detay" data-bs-toggle="modal" data-bs-target="#borcDetayModal">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><i class="fas fa-file-invoice me-1 text-primary"></i> Şubat Aidatı</td>
                                <td><strong>500.00</strong></td>
                                <td><strong>0.00</strong></td>
                                <td>29.02.2025</td>
                                <td><span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ödendi</span></td>
                                <td>
                                    <div class="hstack gap-2"></div>
                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Detay" data-bs-toggle="modal" data-bs-target="#borcDetayModal">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>3</td>
                                <td><i class="fas fa-wrench me-1 text-danger"></i> Tesisat Gideri</td>
                                <td><strong>300.00</strong></td>
                                <td><strong>15.00</strong></td>
                                <td>15.03.2025</td>
                                <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                <td>
                                    <div class="hstack gap-2">
                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Detay" data-bs-toggle="modal" data-bs-target="#borcDetayModal">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>4</td>
                                <td><i class="fas fa-tools me-1 text-secondary"></i> Ortak Alan Temizliği</td>
                                <td><strong>150.00</strong></td>
                                <td><strong>0.00</strong></td>
                                <td>10.04.2025</td>
                                <td><span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Ödendi</span></td>
                                <td>
                                    <div class="hstack gap-2"></div>
                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Detay" data-bs-toggle="modal" data-bs-target="#borcDetayModal">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>5</td>
                                <td><i class="fas fa-bolt me-1 text-warning"></i> Elektrik Gideri</td>
                                <td><strong>220.50</strong></td>
                                <td><strong>11.03</strong></td>
                                <td>05.05.2025</td>
                                <td><span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></td>
                                <td>
                                    <div class="hstack gap-2">
                                        <a href="javascript:void(0);" class="avatar-text avatar-md" title="Detay" data-bs-toggle="modal" data-bs-target="#borcDetayModal">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Eğer hiç borç yoksa -->
                            <!--
                            <tr>
                                <td colspan="7" class="text-center text-muted">Henüz herhangi bir borcunuz bulunmamaktadır.</td>
                            </tr>
                            -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Borç Detay Modal -->
<div class="modal fade" id="borcDetayModal" tabindex="-1" aria-labelledby="borcDetayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borcDetayModalLabel">
                        <div class="d-flex justify-content-center align-items-center">
                                <i class="feather-info me-3 text-primary" style="font-size: 2.0rem;"></i>
                                <span>Borç Detayı</span>
                        </div>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Başlık:</strong> Ocak Aidatı</p>
                        <p><strong>Tutar:</strong> 500.00 ₺</p>
                        <p><strong>Oluşturulma:</strong> 01.01.2025</p>

                        <p><strong>Son Ödeme Tarihi:</strong> 31.01.2025</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ceza Oranı:</strong> %5</p>
                        <p><strong>Ceza Tutarı:</strong> 25.00 ₺</p>
                        <p><strong>Gecikme Gün sayısı:</strong> 3 </p>
                        <p><strong>Durum:</strong> <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Bekliyor</span></p>
                    </div>
                </div>
                <p><strong>Açıklama:</strong> Bu borç Ocak ayına ait aidat ödemesini kapsamaktadır. Gecikme halinde ceza uygulanır.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary"><i class="feather-credit-card me-1"></i>Ödeme Yap</button>
            </div>
        </div>
    </div>
</div>
