<?php 

use \App\Services\Gate;
use App\Helper\BlokHelper;
use App\Helper\KisiHelper;

$BlokHelper = new BlokHelper();
$KisiHelper = new KisiHelper();

Gate::authorizeOrDie('announcements_admin_page'); 





?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Duyuru Oluştur</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Duyuru Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="duyuru-talep/admin/announcements-list">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </button>
            <button type="submit" class="btn btn-primary" id="saveAnnouncement">
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
                                                <input type="text" name="title" id="title" class="form-control" placeholder="Başlık" required>
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
                                            <input type="hidden" name="content" id="content">
                                        </div>
                                    </div>

                                    <!-- Hedef ve Tarihler -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Hedef:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-users"></i></span>
                                                <select name="target_type" id="target_type" class="form-select select2" required>
                                                    <option value="all">Tüm Site</option>
                                                    <option value="block">Blok</option>
                                                    <option value="kisi">Kişi</option>
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
                                                <?php echo $BlokHelper->BlokSelect("block_id", false); ?>
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
                                            <?php echo $KisiHelper->KisiSelect("kisi_ids", null, false, true, true); ?>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Yayın Başlangıcı:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="date" name="start_date" id="start_date" class="form-control">
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Yayın Bitişi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="date" name="end_date" id="end_date" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Durum:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <select name="status" id="status" class="form-select">
                                                <option value="draft">Taslak</option>
                                                <option value="published">Yayınlandı</option>
                                                <option value="archived">Arşivlendi</option>
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
