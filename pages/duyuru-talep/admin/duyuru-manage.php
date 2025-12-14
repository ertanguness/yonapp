<?php 

use App\Helper\Date;
use App\Helper\Helper;
use Model\DuyuruModel;
use \App\Services\Gate;
use App\Helper\BlokHelper;
use App\Helper\KisiHelper;

$BlokHelper = new BlokHelper();
$KisiHelper = new KisiHelper();
$DuyuruModel = new DuyuruModel();

Gate::authorizeOrDie('announcements_admin_page'); 

$id = $id ?? 0;

$duyuru = $DuyuruModel->find($id,true);
$hedef_kisiler = json_decode($duyuru->target_ids ?? '');
$hedef_bloklar = json_decode($duyuru->target_ids ?? '');
//Helper::dd($hedef_bloklar);


?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyuru Oluştur</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Duyuru Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <a href="/duyuru-listesi" type="button" class="btn btn-outline-secondary me-2" data-page="">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </a>
            <button type="button" class="btn btn-primary" id="saveAnnouncement">
                <i class="feather-send me-2"></i> Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Duyuru Formu";
    $text = "Başlık, içerik ve hedef bilgileriyle duyuru oluşturabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="announcementForm">
                            <input type="hidden" class="form-control" name="id" id="id" value="<?php echo $id ?? 0; ?>">
                            
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body">

                                    <!-- Başlık -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="title" class="fw-semibold">Başlık:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-tag"></i></span>
                                                <input type="text" 
                                                    value="<?php echo $duyuru->baslik ?? ''; ?>"
                                                name="title" id="title" class="form-control" placeholder="Başlık" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- İçerik -->
                                    <div class="row mb-4 align-items-start">
                                        <div class="col-lg-2">
                                            <label for="content" class="fw-semibold">İçerik:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div id="contentEditor" class="form-control" style="min-height: 200px;"></div>
                                            <input type="hidden" name="content" id="content" 
                                                value="<?php echo $duyuru->icerik ?? ''; ?>"
                                            >
                                        </div>
                                    </div>

                                    <?php 
                                        $hedef = $duyuru->target_type ?? '';
                                    ?>

                                    <!-- Hedef ve Tarihler -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Hedef:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-users"></i></span>
                                                <select name="target_type" id="target_type" class="form-select select2" required>
                                                    <option value="all"  <?php echo $hedef == 'all' ? 'selected' : ''; ?>>Tüm Site</option>
                                                    <option value="block" <?php echo $hedef == 'block' ? 'selected' : ''; ?>>Blok</option>
                                                    <option value="kisi" <?php echo $hedef == 'kisi' ? 'selected' : ''; ?>>Kişi</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Blok Seçimi -->
                                    <div class="row mb-4 align-items-center d-none" id="blockSelectRow">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Blok:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-home"></i></span>
                                                <?php echo $BlokHelper->BlokSelect("block_id",true, $hedef_bloklar,true); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kişi Seçimi (çoklu) -->
                                    <div class="row mb-4 align-items-center d-none" id="kisiSelectRow">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Kişiler:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="form-text mb-2">Bir veya birden fazla kişi seçebilirsiniz.</div>
                                            <?php echo $KisiHelper->KisiSelect("kisi_ids", $hedef_kisiler, false, true, true); ?>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Yayın Başlangıcı:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" autocomplete="off" value="<?php echo Date::dmY($duyuru->baslangic_tarihi ?? ''); ?>" name="start_date" id="start_date" class="form-control flatpickr">
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Yayın Bitişi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" autocomplete="off" value="<?php echo Date::dmY($duyuru->bitis_tarihi ?? ''); ?>" name="end_date" id="end_date" class="form-control flatpickr">
                                        </div>
                                    </div>

                                    <?php $durum = $duyuru->durum ?? ''; ?>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Durum:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <select name="status" id="status" class="form-select">
                                                <option value="draft" <?php echo $durum == 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                                <option value="published" <?php echo $durum == 'published' ? 'selected' : ''; ?>>Yayınlandı</option>
                                                <option value="archived" <?php echo $durum == 'archived' ? 'selected' : ''; ?>>Arşivlendi</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- E-Posta Önizleme -->
                                    <div class="row mb-4" id="emailPreviewSection">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Önizleme:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="border rounded p-3 bg-light">
                                                <h6 class="fw-bold" id="previewSubject">[Konu]</h6>
                                                <p id="previewMessage" class="mb-0">[Mesaj]</p>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- .card-body -->
                            </div> <!-- .card-body -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/pages/duyuru-talep/admin/js/duyuru.js"></script>
