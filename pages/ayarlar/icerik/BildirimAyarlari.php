<?php
use Model\SettingsModel;
use App\Helper\Helper;

$Settings = new SettingsModel();


$site_id =$_SESSION['site_id'];
$user_id =$_SESSION['user']->id;
$kv = $Settings->getAllSettingsAsKeyValue($site_id, $user_id) ?? [];

$loginNotification = $kv['login_notification'] ?? 0;
$personnelNotification = $kv['personnel_notification'] ?? 0;

$loginNotificationisCheck = $loginNotification == 1 ? "checked" : "";
$personnelNotificationisCheck = $personnelNotification == 1 ? "checked" : "";



?>
<div class="card-body personal-info mb-4">
    <form action="" id="notificationSettingsForm">
        <div class="notify-activity-section">
            <div class="px-4 mb-4 d-flex justify-content-between">
                <h5 class="fw-bold">Bildirim Ayarları</h5>
            </div>
            <div class="px-4">

                <div
                    class="hstack justify-content-between p-4 mb-3 border border-dashed border-gray-3 rounded-1 toggle-switch-row c-pointer">
                    <div class="hstack me-4">
                        <div class="avatar-text">
                            <i class="feather-message-square"></i>
                        </div>
                        <div class="ms-4">
                            <a href="javascript:void(0);" class="fw-bold mb-1 text-truncate-1-line">Girişte bildirim
                                gönder</a>
                            <div class="fs-12 text-muted text-truncate-1-line">Hesabınıza giriş yapıldığında mail
                                adresinize bildirim gelir.</div>
                        </div>
                    </div>
                    <div class="form-check form-switch form-switch-sm">
                        <label class="form-check-label fw-500 text-dark c-pointer"
                            for="formSwitchLoginNotification"></label>
                        <input class="form-check-input c-pointer" type="checkbox" value="1"
                            name="formSwitchLoginNotification" id="formSwitchLoginNotification" <?php echo $loginNotificationisCheck; ?>>
                    </div>
                </div>
                <div
                    class="hstack justify-content-between p-4 mb-3 border border-dashed border-gray-3 rounded-1 toggle-switch-row c-pointer">
                    <div class="hstack me-4">
                        <div class="avatar-text">
                            <i class="feather-briefcase"></i>
                        </div>
                        <div class="ms-4">
                            <a href="javascript:void(0);" class="fw-bold mb-1 text-truncate-1-line">Personele bildirim
                                gönder</a>
                            <div class="fs-12 text-muted text-truncate-1-line">Personele görev ataması yapıldığında sms
                                ile bildirim gönder.</div>
                        </div>
                    </div>
                    <div class="form-check form-switch form-switch-sm">
                        <label class="form-check-label fw-500 text-dark c-pointer"
                            for="formSwitchPersonnelNotification"></label>
                        <input class="form-check-input c-pointer" type="checkbox" value="1" <?php echo $personnelNotificationisCheck; ?> name="formSwitchPersonnelNotification"
                            id="formSwitchPersonnelNotification">
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    
</script>