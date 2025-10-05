<!-- Notification Settings Page -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3">Bildirim Ayarları</h6>

    <!-- Email Bildirim Ayarları -->
    <div class="mb-4">
        <h6 class="fw-semibold">E-posta Bildirim Ayarları</h6>

        <!-- SMTP Server Ayarları -->
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="smtpServer" class="fw-semibold">SMTP Sunucusu:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-server"></i>
                    </div>
                    <input type="text" class="form-control" id="smtpServer" name="smtpServer" placeholder="SMTP sunucu adresi">
                </div>
            </div>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="smtpPort" class="fw-semibold">Port Numarası:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <input type="number" class="form-control" id="smtpPort" name="smtpPort" placeholder="SMTP port numarası">
                </div>
            </div>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="emailAddress" class="fw-semibold">Gönderici E-posta:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="Gönderici e-posta adresi">
                </div>
            </div>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="emailPassword" class="fw-semibold">E-posta Şifresi:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" class="form-control" id="emailPassword" name="emailPassword" placeholder="E-posta şifresi">
                </div>
            </div>
        </div>

        <!-- Email Aktif/Pasif Checkbox -->
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label class="fw-semibold">E-posta Bildirimleri:</label>
            </div>
            <div class="col-lg-4">
                <label class="switch">
                    <input type="checkbox" id="emailStatus" name="emailStatus" checked>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <hr>

    <!-- SMS Bildirim Ayarları -->
    <div>
        <h6 class="fw-semibold">SMS Bildirim Ayarları</h6>

        <!-- SMS API Bilgileri -->
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="smsProvider" class="fw-semibold">SMS Servis Sağlayıcı:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <input type="text" class="form-control" id="smsProvider" name="smsProvider" placeholder="Servis sağlayıcı adı">
                </div>
            </div>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="smsApiKey" class="fw-semibold">API Anahtarı:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-key"></i>
                    </div>
                    <input type="text" class="form-control" id="smsApiKey" name="smsApiKey" placeholder="API anahtarı">
                </div>
            </div>
        </div>
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label for="smsSenderID" class="fw-semibold">Gönderici Kimliği:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" class="form-control" id="smsSenderID" name="smsSenderID" placeholder="Gönderici kimliği">
                </div>
            </div>
        </div>

        <!-- SMS Aktif/Pasif Checkbox -->
        <div class="row mb-3 align-items-center">
            <div class="col-lg-2">
                <label class="fw-semibold">SMS Bildirimleri:</label>
            </div>
            <div class="col-lg-4">
                <label class="switch">
                    <input type="checkbox" id="smsStatus" name="smsStatus" checked>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

   
</div>

<!-- Custom CSS for Switch (Checkbox style) -->
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        border-radius: 50%;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
    }

    input:checked + .slider {
        background-color: #4CAF50;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    /* Add some styles for disabled state */
    input:disabled + .slider {
        background-color: #bfbfbf;
    }

    input:disabled + .slider:before {
        background-color: #f1f1f1;
    }
</style>
