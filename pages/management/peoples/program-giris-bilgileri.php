<?php

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use Model\KisilerModel;
use Model\UserModel;
use Model\BloklarModel;
use Model\DairelerModel;

$Kisiler = new KisilerModel();
$Users   = new UserModel();
$Bloklar = new BloklarModel();
$Daireler = new DairelerModel();

$siteId = $_SESSION['site_id'] ?? null;
$aktifKisiler = $siteId ? $Kisiler->getAktifKisilerBySite((int)$siteId) : [];

function formatPhone($tel){
    $digits = preg_replace('/\D+/', '', (string)$tel);
    if (!$digits) return '';
    return $digits;
}

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Program Giriş Bilgileri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Sakinler</li>
            <li class="breadcrumb-item active">Program Giriş Bilgileri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <div class="btn-group" role="group" aria-label="Toplu Davet">
                    <button class="btn btn-primary btn-sm" id="bulkInviteEmail" disabled>
                        <i class="bi bi-envelope-paper"></i> E-posta Davet
                    </button>
                    <button class="btn btn-info btn-sm" id="bulkInviteSms" disabled>
                        <i class="bi bi-chat-dots"></i> SMS Davet
                    </button>
                    <button class="btn btn-success btn-sm" id="bulkInviteWhatsapp" disabled>
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp Davet
                    </button>
                </div>
                <span class="badge bg-light text-dark ms-2" id="selectedCount">Seçili: 0</span>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Aktif Sakinler – Program Giriş Bilgileri";
    $text = "Aktif site sakinlerinin hesabı ve giriş bilgileri. Seçerek E‑posta, SMS veya WhatsApp üzerinden davet gönderebilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover datatables w-100" id="aktifSakinProgramGiris">
                    <thead>
                        <tr class="text-center">
                            <th style="width:36px"><input type="checkbox" id="selectAll"></th>
                            <th>Blok</th>
                            <th>Daire</th>
                            <th>Adı Soyadı</th>
                            <th>E‑posta</th>
                            <th>Telefon</th>
                            <th>Hesap</th>
                            <th style="width:7%">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aktifKisiler as $row):
                            $encId = Security::encrypt($row->id);
                            $email = $row->eposta ?? '';
                            $phone = formatPhone($row->telefon ?? '');
                            $user = $email ? $Users->getUserByEmail($email) : null;
                            $hesap = $user ? (($user->status ?? 0) ? 'Aktif' : 'Pasif') : 'Yok';
                            $daire = $Daireler->DaireAdi($row->daire_id ?? null);
                            $daireNo = is_object($daire) ? htmlspecialchars($daire->daire_no) : '-';
                        ?>
                        <tr class="text-center">
                            <td>
                                <input type="checkbox" class="rowSelect" 
                                       data-id="<?= htmlspecialchars($encId) ?>" 
                                       data-email="<?= htmlspecialchars($email) ?>" 
                                       data-phone="<?= htmlspecialchars($phone) ?>">
                            </td>
                            <td><?= htmlspecialchars($row->blok_adi ?? '-') ?></td>
                            <td><?= htmlspecialchars($daireNo) ?></td>
                            <td><?= htmlspecialchars($row->adi_soyadi ?? '-') ?></td>
                            <td><?= htmlspecialchars($email ?: '-') ?></td>
                            <td><?= htmlspecialchars($phone ?: '-') ?></td>
                            <td><?= htmlspecialchars($hesap) ?></td>
                            <td>
                                <div class="dropdown align-self-start icon-demo-content">
                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-list-ul font-size-20 text-dark"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="javascript:void(0)" class="dropdown-item action-copy" data-id="<?= htmlspecialchars($encId) ?>">
                                            <i class="bi bi-clipboard"></i> Linki Kopyala
                                        </a>
                                        <a href="javascript:void(0)" class="dropdown-item action-email" data-id="<?= htmlspecialchars($encId) ?>" data-email="<?= htmlspecialchars($email) ?>">
                                            <i class="bi bi-envelope-paper"></i> E‑posta Gönder
                                        </a>
                                        <a href="javascript:void(0)" class="dropdown-item action-sms" data-id="<?= htmlspecialchars($encId) ?>" data-phone="<?= htmlspecialchars($phone) ?>">
                                            <i class="bi bi-chat-dots"></i> SMS Gönder
                                        </a>
                                        <a href="javascript:void(0)" class="dropdown-item action-wa" data-id="<?= htmlspecialchars($encId) ?>" data-phone="<?= htmlspecialchars($phone) ?>">
                                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                        </a>
                                        <a href="javascript:void(0)" class="dropdown-item action-detail" data-numeric-id="<?= (int)$row->id ?>">
                                            <i class="feather-eye"></i> Giriş Detayı
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="programGirisDetay"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const table = document.getElementById('aktifSakinProgramGiris');
    const selectAll = document.getElementById('selectAll');
    const selectedCount = document.getElementById('selectedCount');
    const bulkEmail = document.getElementById('bulkInviteEmail');
    const bulkSms = document.getElementById('bulkInviteSms');
    const bulkWa = document.getElementById('bulkInviteWhatsapp');

    function getSelected(){
        const rows = Array.from(document.querySelectorAll('.rowSelect:checked'));
        return rows.map(r => ({ id: r.dataset.id, email: r.dataset.email || '', phone: (r.dataset.phone || '').replace(/\D+/g,'') }));
    }
    function updateSelectedUI(){
        const sel = getSelected();
        selectedCount.textContent = 'Seçili: ' + sel.length;
        const has = sel.length > 0;
        bulkEmail.disabled = !has; bulkSms.disabled = !has; bulkWa.disabled = !has;
    }

    if (selectAll){
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.rowSelect').forEach(cb => cb.checked = selectAll.checked);
            updateSelectedUI();
        });
    }
    document.addEventListener('change', function(e){
        if (e.target.classList.contains('rowSelect')) updateSelectedUI();
    });

    async function apiCall(payload){
        const res = await fetch('/pages/management/peoples/invite_api.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        return res.json();
    }
    async function copyLink(encId){
        const data = await apiCall({ action: 'generate_short_link', kisi_id: encId });
        const toCopy = (data && data.short_link) ? data.short_link : (data && data.full_link ? data.full_link : '');
        return await copyToClipboard(toCopy);
    }
    async function copyToClipboard(text){
        try { if (navigator.clipboard){ await navigator.clipboard.writeText(text); return true; } } catch(e){}
        const ta = document.createElement('textarea'); ta.value = text; ta.style.position='fixed'; ta.style.opacity='0'; ta.style.left='-9999px';
        document.body.appendChild(ta); ta.focus(); ta.select(); const ok = document.execCommand('copy'); document.body.removeChild(ta); return ok;
    }
    function toast(text, ok){ if (window.Toastify){ Toastify({ text, duration:2500, gravity:'top', position:'center', backgroundColor: ok ? '#28a745' : '#dc3545' }).showToast(); } else { alert(text); } }

    table.addEventListener('click', async function(e){
        const a = e.target.closest('a.dropdown-item'); if (!a) return;
        e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        if (a.classList.contains('action-copy')){
            const ok = await copyLink(a.dataset.id); toast(ok ? 'Davet linki kopyalandı' : 'Kopyalama başarısız', ok); return;
        }
        if (a.classList.contains('action-email')){
            const email = a.dataset.email; if (!email) return toast('E-posta yok', false);
            const data = await apiCall({ action:'send_invite_email', kisi_id: a.dataset.id, email }); toast(data.message || (data.status==='success'?'E-posta gönderildi':'Gönderilemedi'), data.status==='success'); return;
        }
        if (a.classList.contains('action-sms')){
            const phone = (a.dataset.phone||'').replace(/\D+/g,''); if (!phone) return toast('Telefon yok', false);
            const data = await apiCall({ action:'send_invite_sms', kisi_id: a.dataset.id, phone }); toast(data.message || (data.status==='success'?'SMS gönderildi':'Gönderilemedi'), data.status==='success'); return;
        }
        if (a.classList.contains('action-wa')){
            let phone = (a.dataset.phone||'').replace(/\D+/g,''); if (!phone) return toast('Telefon yok', false);
            const data = await apiCall({ action:'generate_short_link', kisi_id: a.dataset.id, phone });
            let waPhone = phone; if (waPhone.startsWith('0')) waPhone = '90' + waPhone.substring(1); else if (waPhone.length === 10) waPhone = '90' + waPhone;
            const linkText = (data && data.short_link) ? data.short_link : (data && data.full_link ? data.full_link : '');
            const message = encodeURIComponent('Merhaba, YonApp sistemine giriş için davet linkiniz: ' + linkText);
            const waUrl = 'https://wa.me/' + waPhone + '?text=' + message; window.open(waUrl, '_blank'); toast('WhatsApp yönlendirildi', true); return;
        }
        if (a.classList.contains('action-detail')){
            const id = a.dataset.numericId;
            fetch('pages/management/peoples/content/GirisBilgileri.php?id=' + id)
                .then(r => r.text()).then(html => {
                    document.getElementById('programGirisDetay').innerHTML = html;
                }).catch(() => toast('Detay yüklenemedi', false));
            return;
        }
    });

    async function runBulk(action){
        const sel = getSelected(); if (sel.length === 0) return;
        const failures = []; let okCount = 0;
        for (const s of sel){
            try{
                let data;
                if (action==='email'){ if (!s.email){ failures.push('E-posta yok'); continue; } data = await apiCall({ action:'send_invite_email', kisi_id:s.id, email:s.email }); }
                else if (action==='sms'){ if (!s.phone){ failures.push('Telefon yok'); continue; } data = await apiCall({ action:'send_invite_sms', kisi_id:s.id, phone:s.phone }); }
                else if (action==='wa'){
                    if (!s.phone){ failures.push('Telefon yok'); continue; }
                    const dataLink = await apiCall({ action:'generate_short_link', kisi_id:s.id, phone:s.phone });
                    let waPhone = s.phone; if (waPhone.startsWith('0')) waPhone = '90' + waPhone.substring(1); else if (waPhone.length === 10) waPhone = '90' + waPhone;
                    const linkText = (dataLink && dataLink.short_link) ? dataLink.short_link : (dataLink && dataLink.full_link ? dataLink.full_link : '');
                    const message = encodeURIComponent('Merhaba, YonApp sistemine giriş için davet linkiniz: ' + linkText);
                    const waUrl = 'https://wa.me/' + waPhone + '?text=' + message; window.open(waUrl, '_blank'); data = { status:'success', message:'WhatsApp yönlendirildi' };
                }
                if (data && data.status==='success') okCount++; else failures.push(data && data.message ? data.message : 'Hata');
            }catch(e){ failures.push('Hata'); }
            await new Promise(res => setTimeout(res, 150));
        }
        toast('Başarılı: ' + okCount + ', Hatalı: ' + failures.length, failures.length===0);
    }
    bulkEmail.addEventListener('click', function(e){ e.preventDefault(); runBulk('email'); });
    bulkSms.addEventListener('click', function(e){ e.preventDefault(); runBulk('sms'); });
    bulkWa.addEventListener('click', function(e){ e.preventDefault(); runBulk('wa'); });
});
</script>
