<?php
use App\Helper\Helper;
use App\Helper\Date;
use Model\UserPaymentModel;
use Model\FinansalRaporModel;
use Model\KisilerModel;

$UserPayment = new UserPaymentModel();
$Rapor = new FinansalRaporModel();
$user_id = $_SESSION['user']->kisi_id ?? ($_SESSION['user']->id ?? 0);
$hesap_ozet = $Rapor->KisiFinansalDurum((int)$user_id);
$bakiye_color = ($hesap_ozet->bakiye ?? 0) < 0 ? "danger" : "success";
$BorcTahsilatDetay = $UserPayment->kisiBorcTahsilatDetay(user_id: $user_id);
$sonTahsilat = null;
foreach ($BorcTahsilatDetay as $it) {
    if (($it->islem_turu ?? '') === 'tahsilat') {
        if (!$sonTahsilat || strtotime($it->islem_tarihi) > strtotime($sonTahsilat)) {
            $sonTahsilat = $it->islem_tarihi;
        }
    }
}
usort($BorcTahsilatDetay, function($a,$b){
    return strtotime($b->islem_tarihi) <=> strtotime($a->islem_tarihi);
});
$sonHareketler = array_slice($BorcTahsilatDetay, 0, 5);

$Kisiler = new KisilerModel();
$kisi = $Kisiler->getKisiByDaireId($user_id);
$site_id = (int) ($_SESSION['site_id'] ?? 0);
$sessionEmail = trim((string) ($_SESSION['user']->email ?? ''));
$sessionPhone = trim((string) ($_SESSION['user']->phone ?? ''));
$sessionName  = trim((string) ($_SESSION['user']->full_name ?? ''));
$tumKisiler = $Kisiler->SiteTumKisileri($site_id);
$kisiAdaylari = array_values(array_filter($tumKisiler, function($k) use ($sessionEmail, $sessionPhone, $sessionName){
    $e = trim((string) ($k->eposta ?? ''));
    $p = trim((string) ($k->telefon ?? ''));
    $n = trim((string) ($k->adi_soyadi ?? ''));
    $nameMatch = ($sessionName && $n && mb_strtolower($sessionName) === mb_strtolower($n));
    $emailMatch = ($sessionEmail && $e && strcasecmp($sessionEmail, $e) === 0);
    $phoneMatch = ($sessionPhone && $p && $sessionPhone === $p);
    return $nameMatch || $emailMatch || $phoneMatch;
}));
?>

<div class="main-content" style="margin-bottom: 50px;">
    <div class="row g-4 align-items-stretch mb-5">
        
        <div class="col-12 d-block d-md-none">
            <div class="card shadow-md rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                        <?php echo Helper::getInitials($kisi->adi_soyadi ?? ($_SESSION['user']->full_name ?? '')); ?>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold"><?php echo htmlspecialchars($kisi->adi_soyadi ?? ($_SESSION['user']->full_name ?? '')); ?></div>
                        <div class="fs-12 text-muted"><?php echo htmlspecialchars($kisi->daire_kodu ?? ''); ?></div>
                    </div>
                    <a href="/bildirimler" class="avatar-text avatar-md bg-soft-info text-info border-soft-info">
                        <i class="bi bi-bell"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card rounded-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-md bg-soft-primary text-primary border-soft-primary rounded">
                            <i class="feather-smile"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['user']->full_name ?? ''); ?></h5>
                            <div class="fs-12 text-muted">Blok/Daire: <?php echo htmlspecialchars($kisi->daire_kodu ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="d-none d-md-flex align-items-center gap-2">
                        <a href="/sakin/profil" class="btn btn-light">Profil</a>
                        <a href="/ayarlar" class="btn btn-light">Ayarlar</a>
                    </div>
                </div>
            </div>
        </div>

        

        <div class="col-xxl-3 col-md-6 mt-0">
            <div class="card rounded-3 h-100">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <div class="text-muted">Güncel Kalan Borç</div>
                            <h4 class="text-<?php echo $bakiye_color; ?>"><?php echo Helper::formattedMoney($hesap_ozet->bakiye ?? 0); ?></h4>
                        </div>
                        <i class="feather-credit-card fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6 mt-0">
            <div class="card rounded-3 h-100">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <div class="text-muted">Son Ödeme</div>
                            <h4 class="text-success"><?php echo $sonTahsilat ? Date::dmy($sonTahsilat) : '-'; ?></h4>
                        </div>
                        <i class="feather-calendar fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6 mt-0">
            <div class="card rounded-3 h-100">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <div class="text-muted">Bildirim Sayısı</div>
                            <h4 class="text-info">0</h4>
                        </div>
                        <i class="feather-bell fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6 mt-0">
            <div class="card rounded-3 h-100">
                <div class="card-body">
                    <div class="hstack justify-content-between">
                        <div>
                            <div class="text-muted">Gecikme Durumu</div>
                            <h4 class="text-<?php echo $bakiye_color; ?>"><?php echo ($hesap_ozet->bakiye ?? 0) > 0 ? 'Var' : 'Yok'; ?></h4>
                        </div>
                        <i class="feather-alert-triangle fs-2 text-<?php echo $bakiye_color; ?>"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Hızlı İşlemler</h5>
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                     <a href="/sakin/daire" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-home"></i>
                        <span class="fs-12 text-muted d-block">Daire Bilgileri</span>
                    </a>
                    <a href="/sakin/finans" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="bi bi-wallet2"></i>
                        <span class="fs-12 text-muted d-block">Borç / Ödeme</span>
                    </a>
                    <a href="/sakin/duyurular" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-speaker"></i>
                        <span class="fs-12 text-muted d-block">Duyurular</span>
                    </a>
                    <a href="/sakin/talep" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-edit"></i>
                        <span class="fs-12 text-muted d-block">Şikayet / Talep</span>
                    </a>
                    <a href="/sakin/anketler" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-list"></i>
                        <span class="fs-12 text-muted d-block">Anketler</span>
                    </a>
                   
                    <a href="/sakin/profil" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-user"></i>
                        <span class="fs-12 text-muted d-block">Profil</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 mt-0">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Son Hareketler</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($sonHareketler as $h): $col = (($h->islem_turu ?? '') === 'tahsilat' ? 'success' : 'danger'); ?>
                            <li class="py-2 border-bottom">
                                <div class="d-flex gap-3 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-text avatar-md bg-soft-<?php echo $col; ?> text-<?php echo $col; ?> border-soft-<?php echo $col; ?> rounded">
                                            <i class="feather-<?php echo $col === 'success' ? 'check' : 'minus'; ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($h->borc_adi ?? ''); ?></div>
                                            <div class="fs-12 text-muted"><?php echo Date::dmy($h->islem_tarihi); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-<?php echo $col; ?>">
                                            <?php echo Helper::formattedMoneyWithoutCurrency($h->tutar); ?> ₺
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xxl-8 col-12 mt-0">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Son Duyurular</h5>
                    <a href="/sakin/duyurular" class="btn btn-light">Tümünü Gör</a>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="duyuruPreview">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">Henüz duyuru yok.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-12 mt-0">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktif Anket</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center justify-content-between"><span>Koşu Bandı</span><span class="badge bg-soft-primary text-primary">%45</span></div>
                        <div class="d-flex align-items-center justify-content-between"><span>Dambıl Seti</span><span class="badge bg-soft-primary text-primary">%30</span></div>
                        <div class="d-flex align-items-center justify-content-between"><span>Pilates Topu</span><span class="badge bg-soft-primary text-primary">%25</span></div>
                    </div>
                    <a href="/sakin/anketler" class="btn btn-primary mt-3 w-100">Oy Ver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var container = document.getElementById('duyuruPreview');
    if (!container) return;
    fetch('/pages/duyuru-talep/admin/api/APIDuyuru.php?datatables=1')
        .then(function(r){ return r.ok ? r.json() : null; })
        .then(function(json){
            if (!json || !json.data || !Array.isArray(json.data) || json.data.length === 0) return;
            container.innerHTML = '';
            json.data.slice(0,3).forEach(function(item){
                var col = document.createElement('div');
                col.className = 'col-12';
                var card = document.createElement('div');
                card.className = 'border rounded-3 p-3 hstack gap-3';
                var icon = document.createElement('div');
                icon.className = 'avatar-text avatar-md bg-soft-primary text-primary border-soft-primary rounded';
                icon.innerHTML = '<i class="feather-speaker"></i>';
                var body = document.createElement('div');
                body.className = 'flex-fill';
                var title = document.createElement('div');
                title.className = 'fw-semibold text-dark';
                title.textContent = item.baslik || 'Duyuru';
                var summary = document.createElement('div');
                summary.className = 'fs-12 text-muted';
                summary.textContent = (item.icerik || '').substring(0,120);
                body.appendChild(title);
                body.appendChild(summary);
                var right = document.createElement('div');
                right.className = 'text-end';
                right.innerHTML = '<a href="/sakin/duyurular" class="btn btn-light btn-sm">Detay</a>';
                card.appendChild(icon);
                card.appendChild(body);
                card.appendChild(right);
                col.appendChild(card);
                container.appendChild(col);
            });
        })
        .catch(function(){});
});
</script>