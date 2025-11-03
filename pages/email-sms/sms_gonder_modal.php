<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use Model\KisilerModel;
use Model\TahsilatModel;
use Model\FinansalRaporModel;
use Model\SitelerModel;

use App\Services\Gate;

use App\Helper\Security;

//Gate::authorizeOrDie('sms_gonderme','Sms Gönderme Yetkiniz Bulunmamaktadır.', false);


$SiteModel = new SitelerModel();
$KisiModel = new KisilerModel();
$TahsilatModel = new TahsilatModel();
$FinansalRaporModel = new FinansalRaporModel();


$id = Security::decrypt($_GET['id'] ?? 0);
$kisi_id = Security::decrypt($_GET['kisi_id'] ?? 0);

$includeFile = $_GET['includeFile'];



$kisi = $KisiModel->find($kisi_id);
$site = $SiteModel->find($_SESSION['site_id']);
// Telefon numarasını temizle ve hazırla



if($includeFile && file_exists("on-hazirlik/{$includeFile}")){
    include_once  "on-hazirlik/{$includeFile}";
}



?>
<style>
    .sms-sender-card {
        background-color: #fff;
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .sms-sender-card .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }

    .sms-sender-card .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    /* Telefon Önizleme Stilleri */
    .phone-preview {
        position: relative;
        width: 280px;
        height: 550px;
        background-color: #1c1c1e;
        border-radius: 40px;
        padding: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        margin: 0 auto;
    }

    .phone-notch {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 25px;
        background-color: #1c1c1e;
        border-radius: 0 0 15px 15px;
        z-index: 2;
    }

    .phone-screen {
        width: 100%;
        height: 100%;
        background-color: #f0f2f5;
        border-radius: 25px;
        padding: 35px 15px 15px;
        overflow-y: auto;
    }

    .sender-id-preview {
        text-align: center;
        color: #888;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .message-bubble {
        background-color: #e9e9eb;
        color: #000;
        padding: 10px 15px;
        border-radius: 20px;
        max-width: 85%;
        word-wrap: break-word;
        line-height: 1.4;
    }

    /* Etiket (Tag) Giriş Alanı */
    .tag-input-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        cursor: text;
        min-height: 45px;
        padding: 8px 12px;
    }

    .tag-input-container input {
        border: none;
        outline: none;
        flex-grow: 1;
        padding: 0;
        background: transparent;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        background-color: #1C84EE;
        color: #fff;
        padding: 4px 8px;
        border-radius: 16px;
        transition: opacity 0.3s ease, transform 0.3s ease;
        animation: slideDownFadeIn 0.4s ease-out;
    }

    .tag .close-tag {
        cursor: pointer;
        margin-left: 6px;
        font-weight: bold;
    }

    .form-floating {
        width: 100% !important;
    }

    .toastr {
        border-radius: 6px;
        padding: 12px;
    }

    #collapseWidthExample .d-inline-block:has(#scheduleTime) {
        position: relative;
    }

    #scheduleTime::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }

    @keyframes slideDownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="sms-sender-card shadow-lg" data-kisi-telefon="<?php echo htmlspecialchars($telefonNumarasi ?? 0, ENT_QUOTES); ?>">
    <!-- KART BAŞLIĞI -->
    <div class="card-header">
        <h4 class="mb-0 d-flex align-items-center">
            <i class="fas fa-paper-plane me-2"></i>
            Yeni SMS Gönder
        </h4>
    </div>

    <!-- KART GÖVDESİ -->
    <div class="card-body p-4">
        <div class="row g-5">
            <!-- Sol Taraf: Form Alanları -->
            <div class="col-lg-7">
                <form id="smsForm">
                    <!-- Gönderen Adı (Alfanümerik) -->
                    <div class="mb-4">
                        <select name="senderId" id="senderId" class="form-control select2">
                            <option value="USKUPEVLSIT" selected>USKUPEVLSIT</option>
                            <option value="FIRMAUNVANI">FIRMAUNVANI</option>
                            <option value="Diger">Diğer</option>
                        </select>
                    </div>

                    <!-- Alıcılar (Tag Sistemi) -->
                    <div class="mb-4">
                        <label for="recipients" class="form-label fw-bold d-flex justify-content-between">
                            <span>Alıcılar</span>
                            <a href="#" class="text-decoration-none small">
                                <i class="fas fa-address-book me-1"></i> Kişilerden Seç
                            </a>
                        </label>
                        <div class="tag-input-container form-control" id="recipients-container">
                            <div class="tag-list" id="recipients-list"></div>
                            <input type="text" id="recipients-input" placeholder="Numara yazıp Enter'a basın...">
                        </div>
                        <div class="form-text">Birden fazla numara ekleyebilirsiniz. (Numara yazıp Enter'a basın...)</div>
                    </div>

                    <!-- Mesaj Alanı -->
                    <div class="mb-2">
                        <label for="message" class="form-label fw-bold d-flex justify-content-between">
                            <span>Mesaj</span>
                            <a href="#" class="text-decoration-none small">
                                <i class="fas fa-paste me-1"></i> Şablon Kullan
                            </a>
                        </label>
                        <div class="form-floating">
                            <textarea class="form-control" id="message" style="height: 350px;"><?php echo htmlspecialchars($mesaj_metni ?? '', ENT_QUOTES); ?></textarea>
                            <label for="message">Mesajınızı yazın...</label>
                        </div>
                        <!-- Karakter Sayacı -->
                        <div class="d-flex justify-content-end text-muted small" id="char-counter">
                            <span>0 / 160 (1 SMS)</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sağ Taraf: Telefon Önizlemesi -->
            <div class="col-lg-5 d-flex justify-content-center align-items-center">
                <div class="phone-preview">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <div class="sender-id-preview" id="sender-preview">
                            USKUPEVLSIT
                        </div>
                        <div class="message-bubble">
                            <p id="message-preview">Mesajınız burada görünecek...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KART ALT BİLGİ -->
    <div class="card-footer text-end p-3">
        <div class="d-flex justify-content-end align-items-center">
            <!-- Bootstrap Collapse Mekanizması -->
            <div class="collapse-horizontal collapse" id="scheduleCollapse">
                <div class="d-flex" style="width: 350px;">
                    <div class="me-2">
                        <input type="text" class="form-control flatpickr" placeholder="Tarih" id="scheduleDate">
                    </div>
                    <div>
                        <input type="time" class="form-control me-3" id="scheduleTime">
                    </div>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="p-2">
                
                <button type="button" form="smsForm" id="smsGonderBtn" class="btn btn-primary px-4 ms-2">
                    <i class="fas fa-paper-plane me-2"></i> Gönder
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// Kişi telefon numarasını JavaScript'e aktar
window.kisiTelefonNumarasi = '<?php echo htmlspecialchars($telefonNumarasi ?? '', ENT_QUOTES); ?>';
</script>