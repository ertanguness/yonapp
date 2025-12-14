<?php
use App\Services\Gate;
?>
<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Süperadmin Mesaj Gönderim Ayarları</h3>
                </div>
                <div class="card-body">
                    <form id="superadminSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">E-posta Gönderici</label>
                                <input type="text" class="form-control" name="email_from" placeholder="noreply@domain.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMS Başlığı</label>
                                <input type="text" class="form-control" name="sms_sender" placeholder="YONAPP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta Şablonu</label>
                                <textarea class="form-control" name="email_template" rows="4" placeholder="Merhaba {{name}}, {{amount}} tutarındaki ödemenizi lütfen tamamlayın."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMS Şablonu</label>
                                <textarea class="form-control" name="sms_template" rows="4" placeholder="Merhaba {{name}}, {{amount}} TL ödemenizi bekliyoruz."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp API URL</label>
                                <input type="text" class="form-control" name="whatsapp_api_url" placeholder="https://api.whatsapp.com/send">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Token</label>
                                <input type="text" class="form-control" name="whatsapp_token" placeholder="API_TOKEN">
                            </div>
                            <div class="col-12">
                                <label class="form-label">WhatsApp Şablonu</label>
                                <textarea class="form-control" name="whatsapp_template" rows="4" placeholder="Merhaba {{name}}, {{amount}} TL için ödeme hatırlatması."></textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="saveSettingsBtn">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('saveSettingsBtn').addEventListener('click', function(){
    var form = document.getElementById('superadminSettingsForm');
    var fd = new FormData(form);
    fd.append('action','settings_set_pairs');
    fetch((window.APP_BASE_PATH||'') + '/pages/panel/api.php', { method:'POST', body: fd })
      .then(r=>r.json()).then(j=>{ if (j && j.status==='success') { alert('Kaydedildi'); } else { alert('Kaydetme hatası'); } });
});
</script>
