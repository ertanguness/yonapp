<?php
// Daire sakininin kendi giriş bilgileri ve üstte davet linkleri

use App\Helper\Security;
use Model\KisilerModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\UserModel;

$Kisiler = new KisilerModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();
$Users   = new UserModel();

// Üst sayfadan gelen kişi ID (ham id bekleniyor)
$kisiId = isset($id) ? (int)$id : 0;
$kisi   = $kisiId ? $Kisiler->KisiBilgileri($kisiId) : null;

// Yardımcılar
function yonapp_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $protocol . '://' . $host . ($base === '' ? '' : $base);
}

// Kişi bilgileri
$email   = $kisi->eposta ?? '';
$telefon = preg_replace('/\D+/', '', (string)($kisi->telefon ?? ''));
$blok    = $Bloklar->Blok($kisi->blok_id ?? null);
$daire   = $Daireler->DaireAdi($kisi->daire_id ?? null);
$encKisiId = Security::encrypt($kisiId);

// Davet linki (kayıt sayfasına e-posta ön-dolu)
// Davet linkini register-member.php'ye yönlendir, kisi (enc) ve email parametreleri ile
$inviteLinkBase = yonapp_base_url() . '/register-member.php';
$query = [];
if ($email) { $query['email'] = $email; }
if ($encKisiId) { $query['kisi'] = $encKisiId; }
$inviteLink = $inviteLinkBase . (!empty($query) ? ('?' . http_build_query($query)) : '');

// Kullanıcı ve giriş kayıtları
$user = null;
if ($email) {
    $user = $Users->getUserByEmail($email);
}

// Son giriş kayıtlarını çek (varsa)
$loginLogs = [];
try {
    if ($user && function_exists('getDbConnection')) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT id, user_id, login_time, logout_time, ip_address, user_agent FROM login_logs WHERE user_id = ? ORDER BY id DESC LIMIT 50");
        $stmt->execute([$user->id]);
        $loginLogs = $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
} catch (\Throwable $e) {
    // sessiz geç
}
?>

<?php if (!$kisi): ?>
    <div class="alert alert-warning">Kişi seçilmedi veya bulunamadı.</div>
    <?php return; endif; ?>

<!-- Davet Alanı (Tablonun Üstünde) -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div>
                <div class="fw-bold mb-1">Davet Linki</div>
                <div class="input-group input-group-sm" style="min-width:360px; max-width:640px;">
                    <input type="text" class="form-control" id="inviteLinkInput" readonly value="<?= htmlspecialchars($inviteLink) ?>">
                    <button type="button" class="btn btn-outline-secondary" id="copyInviteBtn" data-link="<?= htmlspecialchars($inviteLink) ?>"><i class="feather-copy"></i></button>
                </div>
            </div>
            <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary" id="sendInviteEmail" <?= $email ? '' : 'disabled' ?> data-email="<?= htmlspecialchars($email) ?>">
                    <i class="bi bi-envelope"></i> E-posta Gönder
                </button>
                <button class="btn btn-sm btn-outline-success" id="sendInviteWhatsapp" <?= $telefon ? '' : 'disabled' ?> data-phone="<?= htmlspecialchars($telefon) ?>">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </button>
                <button class="btn btn-sm btn-outline-info" id="sendInviteSms" <?= $telefon ? '' : 'disabled' ?> data-phone="<?= htmlspecialchars($telefon) ?>">
                    <i class="bi bi-send"></i> SMS Gönder
                </button>
            </div>
        </div>
        <div class="mt-2 small text-muted">
            <span class="me-3"><strong>Kişi:</strong> <?= htmlspecialchars($kisi->adi_soyadi ?? '-') ?></span>
            <span class="me-3"><strong>Blok:</strong> <?= htmlspecialchars($blok->blok_adi ?? '-') ?></span>
            <span class="me-3"><strong>Daire:</strong> <?= is_object($daire) ? htmlspecialchars($daire->daire_no) : '-' ?></span>
            <?php if ($user): ?>
                <span class="me-3"><strong>Hesap:</strong> <?= ($user->status ?? 0) ? 'Aktif' : 'Pasif' ?></span>
            <?php else: ?>
                <span class="me-3"><strong>Hesap:</strong> Yok</span>
            <?php endif; ?>
            <span class="me-3"><strong>Son Davet:</strong> <span id="lastInviteText">-</span></span>
        </div>
    </div>
    <input type="hidden" id="encKisiId" value="<?= htmlspecialchars($encKisiId) ?>">
    <input type="hidden" id="kisiEmail" value="<?= htmlspecialchars($email) ?>">
    <input type="hidden" id="kisiPhone" value="<?= htmlspecialchars($telefon) ?>">
 </div>

<!-- Kişinin Giriş Kayıtları -->
<div class="table-responsive">
    <table class="table table-hover datatables" id="kisiGirisKayitlari">
        <thead>
            <tr class="text-center">
                <th>#</th>
                <th>Giriş Zamanı</th>
                <th>Çıkış Zamanı</th>
                <th>IP Adresi</th>
                <th>Cihaz / Tarayıcı</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($user && !empty($loginLogs)): $i = 1; foreach ($loginLogs as $log): ?>
                <tr class="text-center">
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($log->login_time ?? '-') ?></td>
                    <td><?= htmlspecialchars($log->logout_time ?? '-') ?></td>
                    <td><?= htmlspecialchars($log->ip_address ?? '-') ?></td>
                    <td class="text-start" style="max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($log->user_agent ?? '-') ?>
                    </td>
                </tr>
            <?php endforeach ?>
               
            <?php endif; ?>
        </tbody>
    </table>
 </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const encKisiId = document.getElementById('encKisiId').value;
    const inviteLink = document.getElementById('inviteLinkInput').value;
    const email = document.getElementById('kisiEmail').value;
    const phone = document.getElementById('kisiPhone').value;
    const lastInviteText = document.getElementById('lastInviteText');

    // Güvenli kopyalama yardımcı fonksiyonu (HTTPS/HTTP fallback)
    async function copyToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            return true;
        } catch (e) { return false; }
    }

    // Kopyala
    const copyBtn = document.getElementById('copyInviteBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', async function() {
            const link = this.getAttribute('data-link');
            const ok = await copyToClipboard(link);
            toast(ok ? 'Davet linki kopyalandı' : 'Kopyalama başarısız', ok);
        });
    }

    // E-posta ile gönder
    document.getElementById('sendInviteEmail').addEventListener('click', async function() {
        if (!email) return;
        this.disabled = true;
        try {
            const res = await fetch('/pages/management/peoples/invite_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'send_invite_email', kisi_id: encKisiId, email: email, link: inviteLink })
            });
            const data = await res.json();
            notifyInviteResult(data);
        } catch (e) {
            notifyInviteResult({ status: 'error', message: 'E-posta gönderilemedi.' });
        } finally { this.disabled = false; }
    });

    // SMS ile gönder
    document.getElementById('sendInviteSms').addEventListener('click', async function() {
        if (!phone) return;
        this.disabled = true;
        try {
            const res = await fetch('/pages/management/peoples/invite_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'send_invite_sms', kisi_id: encKisiId, phone: phone, link: inviteLink })
            });
            const data = await res.json();
            notifyInviteResult(data);
        } catch (e) {
            notifyInviteResult({ status: 'error', message: 'SMS gönderilemedi.' });
        } finally { this.disabled = false; }
    });

    // WhatsApp ile gönder (yeni sekmede aç)
    document.getElementById('sendInviteWhatsapp').addEventListener('click', function() {
        if (!phone) return;
        let waPhone = (phone || '').replace(/\D+/g, '');
        if (waPhone.startsWith('0')) { waPhone = '90' + waPhone.substring(1); }
        else if (waPhone.length === 10) { waPhone = '90' + waPhone; }
        const message = encodeURIComponent('Merhaba, YonApp sistemine giriş için davet linkiniz: ' + inviteLink);
        const waUrl = 'https://wa.me/' + waPhone + '?text=' + message;
        window.open(waUrl, '_blank');
        notifyInviteResult({ status: 'success', message: 'WhatsApp yönlendirildi.' });
    });

    function notifyInviteResult(data) {
        const ok = data && data.status === 'success';
        const msg = data && data.message ? data.message : (ok ? 'Gönderildi' : 'İşlem başarısız');
        if (ok && lastInviteText) {
            const now = new Date();
            lastInviteText.textContent = now.toLocaleString('tr-TR');
        }
        toast(msg, ok);
    }

    function toast(text, ok) {
        if (window.Toastify) {
            Toastify({ text, duration: 2500, gravity: 'top', position: 'center', backgroundColor: ok ? '#28a745' : '#dc3545' }).showToast();
        } else { alert(text); }
    }
});
</script>