<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Bakım ve Arıza Takip </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Periyodik Bakım Takip</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="repair/care/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="care_kaydet">
                    <i class="feather-save  me-2"></i>
                    Kaydet
                </button>
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
    $title = 'Periyodik Bakım  Ekle';
    $text = "Bu sayfadan periyodik bakım takibi ekleyebilirsiniz.";

    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="careForm">
                            <input type="hidden" id="care_id" value="">
                            <div class="card-body care-info">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepNo" class="fw-semibold">Talep No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-hashtag"></i></div>
                                            <input type="text" class="form-control" id="talepNo" placeholder="Veritabanından çekilip tanımlanacak" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!-- Bakım Planlama Bilgileri -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimAdi" class="fw-semibold">Bakım Adı:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-tools"></i></div>
                                            <input type="text" class="form-control" id="bakimAdi" placeholder="Bakım Adını Giriniz">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="bakimPeriyot" class="fw-semibold">Bakım Periyodu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-sync-alt"></i></div>
                                            <select class="form-control" id="bakimPeriyot">
                                                <option value="Aylık">Aylık</option>
                                                <option value="3 Aylık">3 Aylık</option>
                                                <option value="6 Aylık">6 Aylık</option>
                                                <option value="Yıllık">Yıllık</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bakım Yapılacak Yer -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimYeri" class="fw-semibold">Bakım Yapılacak Yer:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div>
                                            <select class="form-control" id="bakimYeri" onchange="toggleBlokSecimi()">
                                                <option value="Blok">Blok</option>
                                                <option value="Site Bahçesi">Site Bahçesi</option>
                                                <option value="Havuz">Havuz</option>
                                                <option value="Spor Salonu">Spor Salonu</option>
                                                <option value="Otopark">Otopark</option>
                                                <option value="Diğer">Diğer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="blokSecimi" class="fw-semibold">Blok Seçimi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <select class="form-control" id="blokSecimi" disabled>
                                                <option value="A Blok">A Blok</option>
                                                <option value="B Blok">B Blok</option>
                                                <option value="C Blok">C Blok</option>
                                                <option value="D Blok">D Blok</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sorumlu Kişi/Firma -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="sorumluFirma" class="fw-semibold">Sorumlu Firma / Kişi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <input type="text" class="form-control" id="sorumluFirma" placeholder="Firma veya Kişi Adı">
                                        </div>
                                    </div>
                                </div>

                                <!-- Son Bakım Tarihi ve Planlanan Tarih -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="sonBakimTarihi" class="fw-semibold">Son Bakım Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="date" class="form-control" id="sonBakimTarihi">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="planlananBakimTarihi" class="fw-semibold">Planlanan Bakım Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                                            <input type="date" class="form-control" id="planlananBakimTarihi">
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
                            </div>

                            <script>
                                function toggleBlokSecimi() {
                                    var bakimYeri = document.getElementById("bakimYeri");
                                    var blokSecimi = document.getElementById("blokSecimi");

                                    if (bakimYeri.value === "Blok") {
                                        blokSecimi.disabled = false;
                                    } else {
                                        blokSecimi.disabled = true;
                                    }
                                }

                                // Call the function on page load
                                document.addEventListener("DOMContentLoaded", function() {
                                    toggleBlokSecimi();
                                });
                            </script>

                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>