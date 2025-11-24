<?php 

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gelir Gider İşlemleri</li>
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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="income-expense/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveIncomeExpense" >
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>
<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="#" id="incomeExpenseForm" enctype="multipart/form-data">
                            <div class="card-body personal-info">
                                <!-- Ad ve Tip -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="transactionName" class="fw-semibold">İşlem Adı:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-tag"></i></div>
                                            <input type="text" class="form-control" id="transactionName" name="transactionName" placeholder="Gelir/Gider adı">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="transactionType" class="fw-semibold">Tipi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-layers"></i></div>
                                            <select class="form-select" id="transactionType" name="transactionType">
                                                <option value="">Seçiniz</option>
                                                <option value="income">Gelir</option>
                                                <option value="expense">Gider</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tutar, Tarih -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="amount" class="fw-semibold">Tutar:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                            <input type="number" class="form-control" id="amount" name="amount" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="date" class="fw-semibold">Tarih:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input type="date" class="form-control" id="date" name="date">
                                        </div>
                                    </div>
                                </div>

                                <!-- Kategori Seçimi -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="category" class="fw-semibold">Kategori:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-folder"></i></div>
                                            <select class="form-select" id="category" name="category">
                                                <option value="">Seçiniz</option>
                                                <option value="aidat">Aidat</option>
                                                <option value="elektrik">Elektrik</option>
                                                <option value="su">Su</option>
                                                <option value="kira">Kira</option>
                                                <option value="diğer">Diğer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Açıklama -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="description" class="fw-semibold">Açıklama:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-file-text"></i></div>
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="İsteğe bağlı açıklama yazınız"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dosya Yükleme -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="document" class="fw-semibold">Belge Yükle:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-upload"></i></div>
                                            <input type="file" class="form-control" id="document" name="document">
                                        </div>
                                    </div>
                                </div>

                               

                                <!-- Gizli Alanlar -->
                                <input type="hidden" name="action" value="saveIncomeExpense">
                                <input type="hidden" name="id" value="0">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
