<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use App\Helper\Helper;
use App\Services\Gate;
use Model\KisilerModel;
use Model\SitelerModel;
use App\Helper\Security;



use Model\TahsilatModel;

use Model\FinansalRaporModel;
use App\Helper\SettingsHelper;

//Gate::authorizeOrDie('sms_gonderme','Sms Gönderme Yetkiniz Bulunmamaktadır.', false);


$SiteModel = new SitelerModel();
$KisiModel = new KisilerModel();
$TahsilatModel = new TahsilatModel();
$FinansalRaporModel = new FinansalRaporModel();
$SettingsHelper = new SettingsHelper();



$id = Security::decrypt($_GET['id'] ?? 0);
$kisi_id = Security::decrypt($_GET['kisi_id'] ?? 0);

$includeFile = $_GET['includeFile'] ?? null;



$kisi = $KisiModel->find($kisi_id);
$site = $SiteModel->find($_SESSION['site_id']);
// Telefon numarasını temizle ve hazırla

// Helper::dd($kisi);

if ($includeFile && file_exists("on-hazirlik/{$includeFile}")) {
    include_once  "on-hazirlik/{$includeFile}";
}



?>
<style>
.sms-sender-card {
        background-color: #fff;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(16, 24, 40, 0.12);
    }

    .sms-sender-card .card-header {
        background: linear-gradient(180deg,#ffffff 0%,#f8f9fa 100%);
        border-bottom: 1px solid #eef2f7;
        padding: 1rem 1.25rem;
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

    .btn-outline-primary.btn-sm, .btn-outline-success.btn-sm, .btn-outline-secondary.btn-sm{
        border-radius: 4px;
    }

    /* Telefon Önizleme Stilleri */
    .phone-preview {
        position: relative;
        width: 300px;
        height: 560px;
        background: linear-gradient(135deg, #1c1c1e 0%, #222325 100%);
        border-radius: 38px;
        padding: 14px;
        box-shadow: 0 18px 40px rgba(0,0,0,.25);
        margin: 0 auto;
    }

    .phone-notch {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 25px;
        background-color: #1c1c1e;
        border-radius: 0 0 15px 15px;
        z-index: 2;
    }

    .phone-screen {
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, #f7f9fc 0%, #eef2f7 100%);
        border-radius: 24px;
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
        padding: 8px 8px;
        border-radius: 12px 0px;
        max-width: 100%;
        word-wrap: break-word;
        line-height: 1.45;
        box-shadow: 0 6px 14px rgba(16,24,40,.06);
    }

    /* Etiket (Tag) Giriş Alanı */
    .tag-input-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        cursor: text;
        min-height: 48px;
        padding: 10px 12px;
        max-height: 140px;
        overflow-y: auto;
        padding-right: 12px;
        border-radius: 12px;
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
        padding: 6px 10px;
        border-radius: 16px;
        transition: opacity 0.3s ease, transform 0.3s ease, box-shadow .2s;
        animation: slideDownFadeIn 0.4s ease-out;
        box-shadow: 0 6px 14px rgba(28,132,238,.25);
    }
    .tag .label{font-weight:600}
    .tag .meta{color:#eaf3ff;background:transparent;margin-left:6px}

    .tag .close-tag {
        cursor: pointer;
        margin-left: 8px;
        font-weight: 600;
        background: rgba(255,255,255,.2);
        width: 18px;
        height: 18px;
        line-height: 18px;
        display: inline-block;
        text-align: center;
        border-radius: 50%;
    }

    .form-floating {
        width: 100% !important;
    }

    #message.form-control{
        border-radius: 14px;
        border: 1px solid #eef2f7;
        box-shadow: 0 8px 20px rgba(16,24,40,.04);
    }
    #message.form-control:focus{
        border-color: #b6d4fe;
        box-shadow: 0 0 0 .25rem rgba(13,110,253,.15);
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
    .card-footer{
        position: sticky;
        bottom: 0 !important;
        background-color: #f8f9fa;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .modal-dots{display:flex;gap:.35rem;align-items:center;margin-left:auto}
    .modal-dots .dot{width:10px;height:10px;border-radius:50%}
    .modal-dots .dot.dot-r{background:#ff6b6b}
    .modal-dots .dot.dot-y{background:#feca57}
    .modal-dots .dot.dot-g{background:#1dd1a1}

    .char-pill{border-radius:999px;padding:.25rem .6rem;font-weight:500}

    .message-input-wrap{display:flex;align-items:flex-start;gap:12px}
    .template-actions{width:220px}
    .template-actions .btn{width:100%;display:flex;align-items:center;gap:.4rem;justify-content:flex-start}
    .template-actions .btn i{font-size:14px}
    .template-actions.compact{width:52px}
    .template-actions.compact .btn{justify-content:center}
    .template-actions.compact .btn .label{display:none}
    @media (max-width: 992px){
        .message-input-wrap{flex-direction:column}
        .template-actions{width:100%;display:flex;gap:.5rem;justify-content:flex-start}
        .template-actions.compact{width:100%}
        .template-actions .btn{width:auto}
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

    .template-modal .modal-content{
        border: none;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(13,110,253,.15);
    }
    .template-modal .modal-header{
        background: linear-gradient(180deg,#ffffff 0%,#f8f9fa 100%);
        border-bottom: 1px solid #eef2f7;
        padding: 1rem 1.25rem;
    }
    .template-modal .modal-footer{
        background-color: #f8f9fa;
        border-top: 1px solid #eef2f7;
    }
    .template-toolbar{
        display:flex;align-items:center;gap:.75rem;
    }
    .modal-dots{display:flex;gap:.35rem;align-items:center;margin-left:auto}
    .modal-dots .dot{width:10px;height:10px;border-radius:50%}
    .modal-dots .dot.dot-r{background:#ff6b6b}
    .modal-dots .dot.dot-y{background:#feca57}
    .modal-dots .dot.dot-g{background:#1dd1a1}

    .avatar-text{display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:#eef2f7;color:#6c757d;transition:all .2s}
    .avatar-sm{width:34px;height:34px}
    .avatar-text:hover{background:#e2e8f0;color:#212529;transform:translateY(-1px)}

    .template-list .list-group-item{border:1px solid #eef2f7;border-radius:14px;margin-bottom:.75rem;box-shadow:0 8px 20px rgba(16,24,40,.04)}
    .template-list .list-group-item:hover{box-shadow:0 12px 24px rgba(16,24,40,.06)}
    .template-item-title{font-weight:600;color:#0b1220}
    .template-item-sub{color:#6c757d}

    .skeleton-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(90deg,#e9ecef 25%,#f8f9fa 50%,#e9ecef 75%);background-size:200% 100%;animation:skeleton 1s infinite}
    .skeleton-line{height:12px;border-radius:6px;background:linear-gradient(90deg,#e9ecef 25%,#f8f9fa 50%,#e9ecef 75%);background-size:200% 100%;animation:skeleton 1s infinite}
    @keyframes skeleton{0%{background-position:200% 0}100%{background-position:-200% 0}}

</style>

<div class="sms-sender-card shadow-lg" data-kisi-telefon="<?php echo htmlspecialchars($telefonNumarasi ?? 0, ENT_QUOTES); ?>" style="max-height:85vh; overflow:auto;">
    <!-- KART BAŞLIĞI -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center w-100">
            <h4 class="mb-0 d-flex align-items-center me-3">
                <i class="fas fa-paper-plane me-2"></i>
                Yeni SMS Gönder
            </h4>
            <span class="badge bg-light text-dark">SMS</span>
            <div class="modal-dots ms-auto"><span class="dot dot-r"></span><span class="dot dot-y"></span><span class="dot dot-g"></span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <!-- KART GÖVDESİ -->
    <div class="card-body p-4">
        <div class="row g-5">
            <!-- Sol Taraf: Form Alanları -->
            <div class="col-lg-7">
                <form id="smsForm">
                    <!-- Gönderen Adı (Alfanümerik) -->
                    <div class="mb-4">
                        <?php echo $SettingsHelper->getMessageSubjects(); ?>
                    </div>

                    <!-- Alıcılar (Tag Sistemi) -->
                    <div class="mb-4">
                        <label for="recipients" class="form-label fw-bold d-flex justify-content-between">
                            <span>Alıcılar</span>
                            <a href="#" class="text-decoration-none small" data-bs-toggle="offcanvas" data-bs-target="#kisilerdenSecOffcanvas">
                                <i class="fas fa-address-book me-1"></i> Kişilerden Seç
                            </a>
                        </label>
                        <div class="tag-input-container form-control" id="recipients-container">
                            <div class="tag-list" id="recipients-list"></div>
                            <input type="text" id="recipients-input" placeholder="Numara yazıp Enter'a basın...">
                        </div>
                        <div class="d-flex justify-content-between align-items-center">

                        <div class="form-text">Birden fazla numara ekleyebilirsiniz. (Numara yazıp Enter'a basın...)</div>
                        <div class="form-text"><strong>Toplam Numara:</strong> <span id="recipients-total">0</span></div>
                    </div>
                </div>

                    <!-- Mesaj Alanı -->
                    <div class="mb-2">
                        <label for="message" class="form-label fw-bold d-flex align-items-center">
                            <span>Mesaj</span>
                            <div class="variables-toolbar d-flex flex-wrap gap-2 ms-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm js-insert-var" data-var="{ADISOYADI}">{ADISOYADI}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm js-insert-var" data-var="{DAİREKODU}">{DAİREKODU}</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm js-insert-var" data-var="{BORÇBAKİYESİ}">{BORÇBAKİYESİ} TL</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm js-insert-var" data-var="{SİTEADI}">{SİTEADI}</button>
                            </div>
                              <div class="ms-auto d-flex gap-2">
                                
                                  <button type="button" class="btn btn-outline-primary btn-sm" id="selectTemplateBtn" data-bs-toggle="tooltip" title="Şablonlardan Seç" aria-label="Şablonlardan Seç">
                                    <i class="fas fa-list-ul"></i>
                                    <span class="label"></span>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm float-end" id="saveTemplateBtn" data-bs-toggle="tooltip" title="Şablon olarak Kaydet" aria-label="Şablon olarak Kaydet">
                                    <i class="fas fa-save"></i>
                                    <span class="label"></span>
                                </button>
                            </div>
                        </label>
                        <div class="message-input-wrap">
                            <div class="flex-grow-1">
                                <div class="form-floating">
                                    <textarea class="form-control" id="message" style="height: 200px;border:1px solid #ced4da;"><?php echo htmlspecialchars($mesaj_metni ?? '', ENT_QUOTES); ?></textarea>
                                    <label for="message">Mesajınızı yazın...</label>
                                </div>
                                <!-- Karakter Sayacı -->
                                <div class="d-flex justify-content-end text-muted small mt-2" id="char-counter">
                                    <span class="char-pill badge bg-light text-dark">0 / 160 (1 SMS)</span>
                                </div>
                            </div>
                          
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
            <div class="p-2 d-flex">
                <!-- Kapat Butonu ekle -->
                 <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Kapat
                </button>

                <button type="button" form="smsForm" id="smsGonderBtn" class="btn btn-primary px-4 ms-2">
                    <i class="fas fa-paper-plane me-2"></i> Gönder
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Kişilerden Seç Offcanvas (Modal Yerine) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="kisilerdenSecOffcanvas" aria-labelledby="offcanvasEndLabel" data-bs-backdrop="false" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 id="offcanvasEndLabel">Kişilerden Seç</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body kisilerden-sec-offcanvas p-0" style="display: flex; flex-direction: column; height: 100%;">
        <div class="d-flex justify-content-center align-items-center" style="height: 200px; flex: 1;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
        </div>
    </div>
</div>

<!-- Şablon Seçimi Modal -->
<div class="modal fade template-modal" id="smsTemplateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center w-100">
          <h5 class="modal-title me-3">Şablonlar</h5>
          <span class="badge bg-light text-dark" id="templatesCount">0</span>
          <div class="modal-dots"><span class="dot dot-r"></span><span class="dot dot-y"></span><span class="dot dot-g"></span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="input-group" style="max-width: 360px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="templateSearch" placeholder="Şablon ara...">
          </div>
          <div class="template-toolbar">
            <button class="btn btn-outline-secondary btn-sm" id="refreshTemplatesBtn"><i class="fas fa-sync-alt me-1"></i> Yenile</button>
          </div>
        </div>
        <ul class="list-group template-list" id="templatesList"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
  </div>

<!-- Şablon Kaydet Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Şablonu Kaydet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Şablon Adı</label>
          <input type="text" class="form-control" id="templateNameInput" placeholder="Örn: Borç Hatırlatma">
        </div>
        <div class="mb-3">
          <label class="form-label">İçerik</label>
          <textarea class="form-control" id="templateBodyInput" rows="6"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-primary" id="confirmSaveTemplateBtn">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Kişi telefon numarasını JavaScript'e aktar
    window.kisiTelefonNumarasi = '<?php echo htmlspecialchars($telefonNumarasi ?? '', ENT_QUOTES); ?>';
    window.csrfToken = '<?php echo Security::csrf(); ?>';
    if (!window.__smsInsertVarHandlerBound) {
        window.__smsInsertVarHandlerBound = true;
        document.addEventListener('click', function(e){
            var btn = e.target.closest('.js-insert-var');
            if (!btn) return;
            var ta = document.getElementById('message');
            var v = btn.getAttribute('data-var') || '';
            if (!ta) return;
            var start = ta.selectionStart || ta.value.length;
            var end = ta.selectionEnd || ta.value.length;
            ta.value = ta.value.slice(0,start) + v + ta.value.slice(end);
            ta.dispatchEvent(new Event('input'));
        });
    }
    (function(){
        var el = document.getElementById('includeNameSwitch');
        if (el && !el.dataset.bound) {
            el.dataset.bound = '1';
            el.addEventListener('change', function(){
                var ta = document.getElementById('message');
                var prefix = '{ADISOYADI} ';
                if (!ta) return;
                var val = ta.value;
                var has = val.startsWith(prefix);
                if (this.checked && !has) { ta.value = prefix + val; }
                else if (!this.checked && has) { ta.value = val.slice(prefix.length); }
                ta.dispatchEvent(new Event('input'));
            });
        }
    })();

    (function(){
        var actions = document.querySelector('.template-actions');
        if (!actions || actions.dataset.bound) return;
        actions.dataset.bound = '1';
        actions.classList.add('compact');
        var ttEls = actions.querySelectorAll('[data-bs-toggle="tooltip"]');
        ttEls.forEach(function(el){ new bootstrap.Tooltip(el); });
    })();
    (function(){
        var selectBtn = document.getElementById('selectTemplateBtn');
        var saveBtn = document.getElementById('saveTemplateBtn');
        var tplModalEl = document.getElementById('smsTemplateModal');
        var saveModalEl = document.getElementById('saveTemplateModal');
        var tplList = document.getElementById('templatesList');
        var tplSearch = document.getElementById('templateSearch');
        var refreshBtn = document.getElementById('refreshTemplatesBtn');
        var nameInput = document.getElementById('templateNameInput');
        var bodyInput = document.getElementById('templateBodyInput');
        var confirmSaveBtn = document.getElementById('confirmSaveTemplateBtn');
        if (tplModalEl && tplModalEl.parentNode !== document.body) { document.body.appendChild(tplModalEl); }
        if (saveModalEl && saveModalEl.parentNode !== document.body) { document.body.appendChild(saveModalEl); }
        if (tplModalEl) { tplModalEl.setAttribute('data-bs-backdrop','static'); }
        if (saveModalEl) { saveModalEl.setAttribute('data-bs-backdrop','static'); }
        var bsTplModal = tplModalEl ? new bootstrap.Modal(tplModalEl) : null;
        var bsSaveModal = saveModalEl ? new bootstrap.Modal(saveModalEl) : null;

        function setTemplatesCount(n){
            var el = document.getElementById('templatesCount');
            if (el) el.textContent = String(n||0);
        }

        function renderSkeleton(){
            tplList.innerHTML = '';
            for(var i=0;i<5;i++){
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center justify-content-between';
                var left = document.createElement('div'); left.className = 'd-flex align-items-center w-100';
                var av = document.createElement('div'); av.className = 'skeleton-avatar me-3';
                var wrap = document.createElement('div'); wrap.className = 'w-100';
                var l1 = document.createElement('div'); l1.className = 'skeleton-line mb-2'; l1.style.width = '40%';
                var l2 = document.createElement('div'); l2.className = 'skeleton-line'; l2.style.width = '70%';
                wrap.appendChild(l1); wrap.appendChild(l2);
                left.appendChild(av); left.appendChild(wrap);
                li.appendChild(left);
                tplList.appendChild(li);
            }
            setTemplatesCount(0);
        }

        function renderTemplates(items){
            tplList.innerHTML = '';
            var q = (tplSearch.value || '').toLowerCase();
            items.filter(function(it){
                return !q || (it.name || '').toLowerCase().includes(q) || (it.body || '').toLowerCase().includes(q);
            }).forEach(function(it){
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center justify-content-between';
                var left = document.createElement('div');
                left.className = 'd-flex align-items-center';
                var avatar = document.createElement('div');
                avatar.className = 'rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3';
                avatar.style.width = '36px'; avatar.style.height = '36px'; avatar.textContent = (it.name || '?').charAt(0).toUpperCase();
                var text = document.createElement('div');
                var title = document.createElement('div'); title.className = 'fw-semibold'; title.textContent = it.name || '';
                var subtitle = document.createElement('div'); subtitle.className = 'text-muted small'; subtitle.textContent = (it.body || '').replace(/\s+/g,' ').slice(0,100);
                text.appendChild(title); text.appendChild(subtitle);
                left.appendChild(avatar); left.appendChild(text);
                var right = document.createElement('div');
                right.className = 'ms-3 d-flex gap-2 align-items-center';
                var applyBtn = document.createElement('a'); applyBtn.href = 'javascript:void(0)'; applyBtn.className = 'avatar-text avatar-sm'; applyBtn.setAttribute('data-bs-toggle','tooltip'); applyBtn.setAttribute('data-bs-trigger','hover'); applyBtn.setAttribute('title','Uygula'); applyBtn.setAttribute('aria-label','Uygula'); applyBtn.innerHTML = '<i class="feather feather-check fs-12"></i>';
                applyBtn.addEventListener('click', function(){
                    var ta = document.getElementById('message');
                    ta.value = it.body || '';
                    ta.dispatchEvent(new Event('input'));
                    if (bsTplModal) bsTplModal.hide();
                });
                var editBtn = document.createElement('a'); editBtn.href = 'javascript:void(0)'; editBtn.className = 'avatar-text avatar-sm'; editBtn.setAttribute('data-bs-toggle','tooltip'); editBtn.setAttribute('data-bs-trigger','hover'); editBtn.setAttribute('title','Düzenle'); editBtn.setAttribute('aria-label','Düzenle'); editBtn.innerHTML = '<i class="feather feather-eye fs-12"></i>';
                editBtn.addEventListener('click', function(){
                    nameInput.value = it.name || '';
                    bodyInput.value = it.body || '';
                    confirmSaveBtn.dataset.editId = String(it.id);
                    if (bsTplModal) bsTplModal.hide();
                    if (bsSaveModal) bsSaveModal.show();
                });
                var delBtn = document.createElement('a'); delBtn.href = 'javascript:void(0)'; delBtn.className = 'avatar-text avatar-sm'; delBtn.setAttribute('data-bs-toggle','tooltip'); delBtn.setAttribute('data-bs-trigger','hover'); delBtn.setAttribute('title','Sil'); delBtn.setAttribute('aria-label','Sil'); delBtn.innerHTML = '<i class="feather feather-more-vertical"></i>';
                delBtn.addEventListener('click', function(){
                    if (!confirm('Şablon silinsin mi?')) return;
                    fetch('/pages/email-sms/api/templates.php?id='+encodeURIComponent(it.id)+'&csrf_token='+encodeURIComponent(window.csrfToken||''), { method: 'DELETE' })
                        .then(function(r){ return r.json(); })
                        .then(function(){ loadTemplates(); });
                });
                right.appendChild(applyBtn); right.appendChild(editBtn); right.appendChild(delBtn);
                li.appendChild(left); li.appendChild(right);
                tplList.appendChild(li);
            });
            if (!tplList.children.length) {
                var empty = document.createElement('div'); empty.className = 'text-muted text-center p-3'; empty.textContent = 'Şablon bulunamadı';
                tplList.appendChild(empty);
            }
            var tooltipEls = tplList.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipEls.forEach(function(el){ new bootstrap.Tooltip(el); });
            setTemplatesCount(items.length || 0);
        }

        var lastItems = [];
        function loadTemplates(){
            renderSkeleton();
            fetch('/pages/email-sms/api/templates.php?type=sms')
                .then(function(r){ return r.json(); })
                .then(function(d){ lastItems = d.items || []; renderTemplates(lastItems); });
        }

        selectBtn?.addEventListener('click', function(){
            loadTemplates();
            bsTplModal && bsTplModal.show();
        });
        refreshBtn?.addEventListener('click', function(){ loadTemplates(); });
        tplSearch?.addEventListener('input', function(){ renderTemplates(lastItems || []); });

        saveBtn?.addEventListener('click', function(){
            nameInput.value = '';
            var ta = document.getElementById('message');
            bodyInput.value = ta ? ta.value : '';
            delete confirmSaveBtn.dataset.editId;
            bsSaveModal && bsSaveModal.show();
        });
        confirmSaveBtn?.addEventListener('click', function(){
            var payload = {
                type: 'sms',
                name: nameInput.value || '',
                body: bodyInput.value || '',
                csrf_token: window.csrfToken || ''
            };
            var isEdit = !!this.dataset.editId;
            var url = '/pages/email-sms/api/templates.php';
            var method = isEdit ? 'PUT' : 'POST';
            if (isEdit) { payload.id = parseInt(this.dataset.editId, 10) || 0; }
            fetch(url, { method: method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
                .then(function(r){ return r.json(); })
                .then(function(){
                    bsSaveModal && bsSaveModal.hide();
                    loadTemplates();
                });
        });
    })();
</script>
