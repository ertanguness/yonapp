<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Maliyet ve Faturalandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Maliyet Takip</li>
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
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="repair/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="saveRepairCost">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Maliyet ve Faturalandırma";
    $text = "Bu modülde bakım işlemlerinin maliyetlerini ve faturalarını takip edebilirsiniz. 
             Yapılan işlemler ve ödemeler sistemde kayıtlı tutulur.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="costForm">
                            <input type="hidden" id="cost_id" value="">
                            <div class="card-body cost-info">

                                <!-- Bakım Türü ve Fatura No -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimTuru" class="fw-semibold">Bakım Türü:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-tools"></i></div>
                                            <select class="form-control" id="bakimTuru">
                                                <option value="Elektrik">Elektrik</option>
                                                <option value="Su Tesisatı">Su Tesisatı</option>
                                                <option value="Havuz Bakımı">Havuz Bakımı</option>
                                                <option value="Spor Salonu">Spor Salonu</option>
                                                <option value="Otopark">Otopark</option>
                                                <option value="Diğer">Diğer</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="faturaNo" class="fw-semibold">Fatura No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-receipt"></i></div>
                                            <input type="text" class="form-control" id="faturaNo" placeholder="Fatura Numarasını Giriniz">
                                        </div>
                                    </div>
                                </div>

                                <!-- Toplam Maliyet, Ödenen Tutar, Kalan Borç -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="toplamMaliyet" class="fw-semibold">Toplam Maliyet (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-money-bill-wave"></i></div>
                                            <input type="number" class="form-control" id="toplamMaliyet" placeholder="Maliyeti Giriniz" oninput="hesaplaKalanBorç()">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="odenenTutar" class="fw-semibold">Ödenen Tutar (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-credit-card"></i></div>
                                            <input type="number" class="form-control" id="odenenTutar" placeholder="Ödenen Tutarı Giriniz" oninput="hesaplaKalanBorç()">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="kalanBorc" class="fw-semibold">Kalan Borç (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-exclamation-circle"></i></div>
                                            <input type="text" class="form-control" id="kalanBorc" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ödeme Durumu ve Tarihi -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="odemeDurumu" class="fw-semibold">Ödeme Durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                                            <select class="form-control" id="odemeDurumu">
                                                <option value="Ödendi">Ödendi</option>
                                                <option value="Ödenmedi">Ödenmedi</option>
                                                <option value="Kısmi Ödeme">Kısmi Ödeme</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="odemeTarihi" class="fw-semibold">Ödeme Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="date" class="form-control" id="odemeTarihi">
                                        </div>
                                    </div>
                                </div>

                                <!-- Açıklama -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="aciklama" class="fw-semibold">Açıklama:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                            <textarea class="form-control" id="aciklama" placeholder="Açıklama Giriniz" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- İşlem  
                                <div class="hstack gap-2 ">
                                    <a href="javascript:void(0);" class="avatar-text avatar-md">
                                        <i class="feather-eye"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="avatar-text avatar-md">
                                        <i class="feather-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="avatar-text avatar-md">
                                        <i class="feather-trash-2"></i>
                                    </a>-->
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function hesaplaKalanBorç() {
        var toplam = parseFloat(document.getElementById("toplamMaliyet").value) || 0;
        var odenen = parseFloat(document.getElementById("odenenTutar").value) || 0;
        document.getElementById("kalanBorc").value = (toplam - odenen).toFixed(2);
    }
</script>