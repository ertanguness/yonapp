<?php

use Model\BakimModel;
use App\Helper\Security;

$Bakimlar = new BakimModel();
$id = Security::decrypt($id ?? 0);


// -------------Bakım ve Arıza Takip Sistemi için talep numarası oluşturma------------
$bugün = date('Ymd'); // Örn: 20250724
$talepNo = $bugün . '-' . $Bakimlar->BakimSonID()['last_id'];
// ------------------------------------------------------------------------------
$bakim=$Bakimlar->BakimBilgileri($id);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Bakım ve Arıza Takip </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Bakım ve Arıza Takip</li>
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

                <a href="/bakim-ariza-takip" class="btn btn-outline-secondary route-link me-2" > 
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="bakim_kaydet">
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

    $title = 'Yeni Bakım, Onarım ve Arıza Takip Ekleme';
    $text = "Bu sayfadan yeni bakım, onarım ve arıza takibi ekleyebilirsiniz.";

    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form  id="bakimForm" method="POST">
                            <input type="hidden" id="bakim_id" name="bakim_id" value="<?php echo Security::encrypt($id) ?? 0; ?>">
                            <div class="card-body repair-info">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepno" class="fw-semibold">Talep no:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-hashtag"></i></div>
                                            <input type="text" class="form-control fw-bold" id="talepno" name="talepno" value="<?php echo ($id == 0 || empty($id)) ? $talepNo : ($bakim->talep_no ?? $talepNo); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!-- talep bilgileri -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepeden" class="fw-semibold">Talep eden:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-user"></i></div>
                                            <input type="text" class="form-control " id="talepeden" name="talepeden" placeholder="Talep eden kişi / birim giriniz" value="<?php echo $bakim->talep_eden ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="taleptarihi" class="fw-semibold">Talep tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="text" class="form-control flatpickr" id="taleptarihi" name="taleptarihi" placeholder="Talep tarihi seçiniz" value="<?php echo $bakim->talep_tarihi ?? ''; ?>">

                                        </div>
                                    </div>
                                </div>

                                <!-- kategori seçimi -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="kategori" class="fw-semibold">Kategori:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-list"></i></div>
                                            <select class="form-control select2 w-100" id="kategori" name="kategori">
                                                <option value="Bakım" <?php echo ($id != 0 && !empty($id) && ($bakim->kategori ?? '') == 'Bakım') ? 'selected' : ''; ?>>Bakım</option>
                                                <option value="Onarım" <?php echo ($id != 0 && !empty($id) && ($bakim->kategori ?? '') == 'Onarım') ? 'selected' : ''; ?>>Onarım</option>
                                                <option value="Arıza" <?php echo ($id != 0 && !empty($id) && ($bakim->kategori ?? '') == 'Arıza') ? 'selected' : ''; ?>>Arıza</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="state" class="fw-semibold">Bakım/Arıza durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-tasks"></i></div>
                                            <select class="form-control select2 w-100" id="state" name="state">
                                                <option value="0" <?php echo ($id == 0 || empty($id) || ($bakim->durum ?? '0') == '0') ? 'selected' : ''; ?>>Bekliyor</option>
                                                <option value="1" <?php echo (($bakim->durum ?? '') == '1') ? 'selected' : ''; ?>>İşlemde</option>
                                                <option value="2" <?php echo (($bakim->durum ?? '') == '2') ? 'selected' : ''; ?>>Tamamlandı</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- firma / kişi atama -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="firmakisi" class="fw-semibold">Atanan Firma / Kişi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <input type="text" class="form-control" id="firmakisi" name="firmakisi" placeholder="Firma veya kişi adı giriniz" value="<?php echo $bakim->firma_kisi ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="atandimi" class="fw-semibold">Atama Durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                                            <select class="form-control select2 w-100" id="atandimi" name="atandimi">
                                                <option value="1" <?php echo ($id == 0 || empty($id) || ($bakim->atama_durumu ?? '') == 'evet') ? 'selected' : ''; ?>>Evet</option>
                                                <option value="0" <?php echo (($bakim->atama_durumu ?? '') == 'hayır') ? 'selected' : ''; ?>>Hayır</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- açıklama alanı -->
                                <div class="row mb-4 align-items-center"> 
                                    <div class="col-lg-2">
                                        <label for="aciklama" class="fw-semibold">Açıklama:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-info-circle"></i> </div>
                                            <textarea class="form-control" id="aciklama" name="aciklama" placeholder="Açıklama Giriniz" rows="3"><?php echo $bakim->aciklama ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
