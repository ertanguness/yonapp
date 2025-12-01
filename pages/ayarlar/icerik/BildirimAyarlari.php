<!-- Bildirim Ayarları (Çalışır ve Düzeltildi) -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-4">Bildirim Ayarları</h6>

    <div class="row">
        <!-- Sol ikon sekmeler -->
        <div class="col-12 col-md-2 col-lg-1 border-end d-flex flex-md-column align-items-center justify-content-start py-2 overflow-auto">
            <div class="nav flex-md-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button
                    class="nav-link active icon-tab mb-md-3 has-tooltip tooltip-bottom"
                    data-tooltip="E-posta Ayarları"
                    id="v-pills-email-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#v-pills-email"
                    type="button"
                    role="tab"
                    aria-controls="v-pills-email"
                    aria-selected="true">
                    <i class="fas fa-envelope fs-5"></i>
                </button>
                <button
                    class="nav-link icon-tab  mb-md-3 has-tooltip tooltip-bottom"
                    data-tooltip="Sms Ayarları"
                    id="v-pills-sms-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#v-pills-sms"
                    type="button"
                    role="tab"
                    aria-controls="v-pills-sms"
                    aria-selected="false">
                    <i class="fas fa-sms fs-5"></i>
                </button>

                <button
                    class="nav-link icon-tab has-tooltip tooltip-bottom"
                    data-tooltip="Whatsapp Ayarları"
                    id="v-pills-Whatsapp-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#v-pills-whatsapp"
                    type="button"
                    role="tab"
                    aria-controls="v-pills-whatsapp"
                    aria-selected="false">
                    <i class="fab fa-whatsapp fs-5"></i>
                </button>
            </div>
        </div>

        <!-- Sağ içerik -->
        <div class="col-12 col-md-10 col-lg-11 mt-3 mt-md-0">
            <div class="tab-content" id="v-pills-tabContent">

                <!-- E-POSTA -->
                <div class="tab-pane fade show active" id="v-pills-email" role="tabpanel" aria-labelledby="v-pills-email-tab">
                    <h6 class="fw-semibold mb-3">E-posta Bildirim Ayarları</h6>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="smtpServer" class="fw-semibold">SMTP Sunucusu:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="smtpServer" name="smtpServer" placeholder="smtp.yourdomain.com" value="<?php echo $AyarlarKV['smtp_server'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="smtpPort" class="fw-semibold">Port Numarası:</label></div>
                        <div class="col-lg-6"><input type="number" class="form-control" id="smtpPort" name="smtpPort" placeholder="465 / 587" value="<?php echo $AyarlarKV['smtp_port'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="smtpUser" class="fw-semibold">E-posta Adresi:</label></div>
                        <div class="col-lg-6"><input type="email" class="form-control" id="smtpUser" name="smtpUser" placeholder="ornek@domain.com" value="<?php echo $AyarlarKV['smtp_user'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="smtpPassword" class="fw-semibold">Şifre:</label></div>
                        <div class="col-lg-6"><input type="password" class="form-control" id="smtpPassword" name="smtpPassword" placeholder="E-posta şifresi" value="<?php echo $AyarlarKV['smtp_password'] ?? ''; ?>"></div>
                    </div>
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="emailAktif" class="fw-semibold">E-mail Aktif</label></div>
                        <div class="col-lg-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="emailDurum" name="emailDurum" value="0"
                                    <?php echo (isset($AyarlarKV['smtp_durum']) && (int)$AyarlarKV['smtp_durum'] == 1) ? 'checked' : ''; ?>
                                    onclick="this.value=this.checked?1:0;">
                                <label class="custom-control-label" for="emailDurum">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS -->
                <div class="tab-pane fade" id="v-pills-sms" role="tabpanel" aria-labelledby="v-pills-sms-tab">
                    <h6 class="fw-semibold mb-3">SMS Bildirim Ayarları</h6>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="smsProvider" class="fw-semibold">Servis Sağlayıcı:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="smsProvider" name="smsProvider" placeholder="NetGSM, IletiMerkezi, vb." value="<?php echo $AyarlarKV['sms_provider'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="smsUsername" class="fw-semibold">Kullanıcı Adı:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="smsUsername" name="smsUsername" placeholder="API kullanıcı adı" value="<?php echo $AyarlarKV['sms_username'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="smsPassword" class="fw-semibold">Şifre:</label></div>
                        <div class="col-lg-6"><input type="password" class="form-control" id="smsPassword" name="smsPassword" placeholder="API şifresi" value="<?php echo $AyarlarKV['sms_password'] ?? ''; ?>"></div>
                    </div>
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="smsAktif" class="fw-semibold">Sms Aktif</label></div>
                        <div class="col-lg-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="smsDurum" name="smsDurum" value="0"
                                    <?php echo (isset($AyarlarKV['sms_durum']) && (int)$AyarlarKV['sms_durum'] == 1) ? 'checked' : ''; ?>
                                    onclick="this.value=this.checked?1:0;">
                                <label class="custom-control-label" for="smsDurum">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WHATSAPP -->
                <div class="tab-pane fade" id="v-pills-whatsapp" role="tabpanel" aria-labelledby="v-pills-whatsapp-tab">
                    <h6 class="fw-semibold mb-3">WhatsApp Bildirim Ayarları</h6>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="whatsappApiUrl" class="fw-semibold">API URL:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="whatsappApiUrl" name="whatsappApiUrl" placeholder="https://api.whatsapp.com/send" value="<?php echo $AyarlarKV['whatsapp_api_url'] ?? ''; ?>" ></div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-lg-3"><label for="whatsappToken" class="fw-semibold">API Token:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="whatsappToken" name="whatsappToken" placeholder="API erişim anahtarı" value="<?php echo $AyarlarKV['whatsapp_token'] ?? ''; ?>"></div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="whatsappSender" class="fw-semibold">Gönderen Numarası:</label></div>
                        <div class="col-lg-6"><input type="text" class="form-control" id="whatsappSender" name="whatsappSender" placeholder="+905xxxxxxxxx" value="<?php echo $AyarlarKV['whatsapp_sender'] ?? ''; ?>"></div>
                    </div>
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-3"><label for="whatsappAktif" class="fw-semibold">WhatsApp Aktif</label></div>
                        <div class="col-lg-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="whatsappDurum" name="whatsappDurum" value="0"
                                    <?php echo (isset($AyarlarKV['whatsapp_durum']) && (int)$AyarlarKV['whatsapp_durum'] == 1) ? 'checked' : ''; ?>
                                    onclick="this.value=this.checked?1:0;">
                                <label class="custom-control-label" for="whatsappDurum">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Stil (projenin stiline uyuyorsa aynısını kullanabilirsin) -->
<style>
    .icon-tab {
        background-color: #f8f9fa;
        border: none;
        border-radius: 8px;
        color: #555;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
        padding: 0;
    }

    .icon-tab:hover {
        background-color: #e9ecef;
    }

    .nav .icon-tab.active {
        background-color: #0d6efd;
        color: #fff;
    }

    @media (max-width: 768px) {
        .nav.flex-md-column {
            flex-direction: row !important;
        }

        .nav.flex-md-column .icon-tab {
            margin-right: 8px;
            margin-bottom: 0;
            width: 44px;
            height: 44px;
        }

        .border-end {
            border-right: none !important;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
    }

    .tooltip-inner {
        background-color: #333 !important;
        color: #fff !important;
        font-size: 0.85rem;
        border-radius: 6px;
        padding: 5px 8px;
    }

    .tooltip-arrow {
        display: none;
    }
</style>
