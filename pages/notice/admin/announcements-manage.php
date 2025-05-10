<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Bildirim Gönderimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Haberleşme</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="notice/admin/announcements-list">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </button>
            <button type="submit" class="btn btn-primary" id="saveNotification">
                <i class="feather-send me-2"></i> Gönder
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Bildirim Formu";
    $text = "Seçilen kişilere e-posta veya SMS gönderimi yapabilirsiniz. Lütfen bildirim türünü ve mesaj içeriğini eksiksiz doldurunuz.";
    require_once 'pages/components/alert.php';
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="notificationForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body">

                                    <!-- Bildirim Türü -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label for="notificationType" class="fw-semibold">Bildirim Türü:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-bell"></i></span>
                                                <select name="notificationType" id="notificationType" class="form-select" required>
                                                    <option value="">Seçiniz</option>
                                                    <option value="email">E-Posta</option>
                                                    <option value="sms">SMS</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Kime Gönderilecek -->
                                        <div class="col-lg-2">
                                            <label for="recipientIds" class="fw-semibold">Kime Gönderilecek:</label>
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-users"></i></span>
                                                <select name="recipientIds[]" id="recipientIds" class="form-select" multiple required>
                                                    <option value="1">Ali Veli</option>
                                                    <option value="2">Ayşe Yılmaz</option>
                                                    <option value="3">Fatma Kaya</option>
                                                    <option value="4">Mehmet Demir</option>
                                                </select>
                                            </div>
                                            <div class="mt-2">
                                                <!-- Tümünü Seç Checkbox -->
                                                <label>
                                                    <input type="checkbox" id="selectAllCheckbox"> Tümünü Seç
                                                </label>
                                            </div>
                                            <small class="text-muted">CTRL veya SHIFT ile çoklu seçim yapabilirsiniz.</small>

                                        </div>
                                    </div>

                                    <!-- E-Posta Konu -->
                                    <div class="row mb-4 align-items-center d-none" id="emailFields">
                                        <div class="col-lg-2">
                                            <label for="subject" class="fw-semibold">Konu Başlığı:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-tag"></i></span>
                                                <input type="text" name="subject" id="subject" class="form-control" placeholder="Konu başlığını giriniz">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mesaj Alanı -->
                                    <div class="row mb-4 align-items-start">
                                        <div class="col-lg-2">
                                            <label for="message" class="fw-semibold">Mesaj:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text"><i class="feather-message-circle"></i></span>
                                                <textarea name="message" id="message" rows="5" class="form-control" placeholder="Mesajınızı yazınız..." required></textarea>
                                            </div>
                                            <div id="smsCharCount" class="text-muted small mt-2 d-none">Karakter sayısı: <span id="charCount">0</span> / 160</div>
                                        </div>
                                    </div>

                                    <!-- E-Posta Önizleme -->
                                    <div class="row mb-4 d-none" id="emailPreviewSection">
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const typeSelect = document.getElementById('notificationType');
    const messageInput = document.getElementById('message');
    const emailFields = document.getElementById('emailFields');
    const charCountDisplay = document.getElementById('smsCharCount');
    const charCount = document.getElementById('charCount');
    const previewSection = document.getElementById('emailPreviewSection');
    const previewSubject = document.getElementById('previewSubject');
    const previewMessage = document.getElementById('previewMessage');
    const subjectInput = document.getElementById('subject');
    const recipientSelect = document.getElementById('recipientIds');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    // Bildirim türü seçildiğinde
    typeSelect.addEventListener('change', function () {
        const type = this.value;

        if (type === 'email') {
            emailFields.classList.remove('d-none');
            charCountDisplay.classList.add('d-none');
            previewSection.classList.remove('d-none');
        } else if (type === 'sms') {
            emailFields.classList.add('d-none');
            charCountDisplay.classList.remove('d-none');
            previewSection.classList.add('d-none');
        } else {
            emailFields.classList.add('d-none');
            charCountDisplay.classList.add('d-none');
            previewSection.classList.add('d-none');
        }
    });

    // Mesaj yazıldığında
    messageInput.addEventListener('input', function () {
        charCount.innerText = this.value.length;
        previewMessage.innerText = this.value;

        if (typeSelect.value === 'email') {
            previewSection.classList.remove('d-none');
        }
    });

    // Konu başlığı yazıldığında
    subjectInput.addEventListener('input', function () {
        previewSubject.innerText = this.value;
    });

    // "Tümünü Seç" checkbox'ı tıklandığında
    selectAllCheckbox.addEventListener('change', function () {
        const options = recipientSelect.options;
        for (let i = 0; i < options.length; i++) {
            options[i].selected = this.checked;
        }
    });
});
</script>
