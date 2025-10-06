<?php

use App\Helper\Security;

use Model\GuvenlikGorevYeriModel;
use App\Helper\Date;

$GorevYerleri = new GuvenlikGorevYeriModel();

$id =  Security::decrypt($id ?? 0) ;
$gorevYeri = $GorevYerleri->GorevYeriBilgileri($id);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Güvenlik ve Ziyaretçi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Görev Yeri Ekle</li>
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
                <a href="/guvenlik-gorev-yerleri" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="gorevYeriKaydet">
                    <i class="feather-save me-2"></i>
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
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="GorevYeriForm" method="post">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!-- HIDDEN FIELDS -->
                                        <input type="hidden" name="gorevYeri_id" id="gorevYeri_id" value="<?php echo Security::encrypt($id) ?? 0; ?>">
                              
                                        <!-- Görev Yeri Adı -->
                                        <div class="col-lg-2">
                                            <label for="ad" class="fw-semibold">
                                                <i class="feather-map-pin me-1"></i>Görev Yeri Adı:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                                <input type="text" class="form-control" id="ad" name="ad" 
                                                value="<?= htmlspecialchars($gorevYeri->ad ?? ''); ?>">
                                                </div>
                                        </div>

                                        <!-- Açıklama -->
                                        <div class="col-lg-2">
                                            <label for="aciklama" class="fw-semibold">
                                                <i class="feather-type me-1"></i>Açıklama:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-type"></i></div>
                                                <textarea class="form-control" id="aciklama" name="aciklama" rows="2"><?php echo $gorevYeri->aciklama ?? '' ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <!-- Aktif Durum -->
                                        <div class="col-lg-2">
                                            <label for="aktif" class="fw-semibold">
                                                <i class="feather-check-circle me-1"></i>Durum:
                                            </label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap w-100">
                                                <div class="input-group-text"><i class="feather-check-circle"></i></div>
                                                <select class="form-select select2 w-100" id="durum" name="durum">
                                                    <option value="1" <?php echo (isset($gorevYeri->durum) && $gorevYeri->durum == 1) ? 'selected' : '' ?>>
                                                        <i class="feather-check"></i> Aktif
                                                    </option>
                                                    <option value="0" <?php echo (isset($gorevYeri->durum) && $gorevYeri->durum == 0) ? 'selected' : '' ?>>
                                                        <i class="feather-x"></i> Pasif
                                                    </option>
                                                </select>
                                            </div>
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
