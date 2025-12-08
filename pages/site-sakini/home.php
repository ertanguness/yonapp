<?php
use App\Helper\Helper;
use App\Helper\Date;
use Model\UserPaymentModel;
use Model\FinansalRaporModel;
use Model\KisilerModel;
use Model\AnketModel;
use Model\AnketVoteModel;
use Model\AnketOyModel;

$UserPayment = new UserPaymentModel();
$Rapor = new FinansalRaporModel();
$selectedApartmentId = (int)($_SESSION['selected_apartment_id'] ?? 0);
$user_id = $_SESSION['user']->kisi_id ?? ($_SESSION['user']->id ?? 0);
if ($selectedApartmentId > 0) {
    $Kisiler = new KisilerModel();
    $aktifOturan = $Kisiler->AktifKisiByDaire((int)$selectedApartmentId);
    if ($aktifOturan && (int)($aktifOturan->id ?? 0) > 0) {
        $user_id = (int)$aktifOturan->id;
    } else {
        $aktifMalik = $Kisiler->AktifKisiByDaireId((int)$selectedApartmentId, 'Kat Maliki');
        if ($aktifMalik && (int)($aktifMalik->id ?? 0) > 0) {
            $user_id = (int)$aktifMalik->id;
        }
    }
}
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

$Kisiler = isset($Kisiler) ? $Kisiler : new KisilerModel();
$kisi = $Kisiler->getKisiByDaireId($user_id);
$site_id = (int) ($_SESSION['site_id'] ?? 0);
$sessionEmail = trim((string) ($_SESSION['user']->email ?? ''));
$sessionPhone = trim((string) ($_SESSION['user']->phone ?? ''));
$sessionName  = trim((string) ($_SESSION['user']->full_name ?? ''));
$currentUserId = (int) ($_SESSION['user']->id ?? 0);
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

$Anket = new AnketModel();
$latestActiveList = $Anket->findWhere(['status' => 'Aktif'], 'created_at DESC', null);
$aktifAnket = null;
if (!empty($latestActiveList)) {
    foreach ($latestActiveList as $row) {
        $endDateStr = trim((string)($row->end_date ?? ''));
        $isActiveByDate = true;
        if ($endDateStr !== '') {
            try {
                $endDate = new \DateTimeImmutable($endDateStr);
                $today = new \DateTimeImmutable('today');
                $isActiveByDate = $endDate >= $today;
            } catch (\Throwable $e) { $isActiveByDate = true; }
        }
        if ($isActiveByDate) { $aktifAnket = $row; break; }
    }
}
if (!$aktifAnket) {
    $latestAny = $Anket->findWhere([], 'created_at DESC', 1);
    $aktifAnket = $latestAny[0] ?? null;
}
$aktifAnketOptions = [];
$aktifAnketPercentages = [];
$kullaniciOy = null;
$isPassiveByDate = false;
$isActiveHeader = false;
if ($aktifAnket) {
    $aktifAnketOptions = json_decode($aktifAnket->options_json ?? '[]', true) ?: [];
    $Vote = new AnketVoteModel();
    $VoteLegacy = new AnketOyModel();
    $normalize = function($s){ return mb_strtolower(trim((string)$s)); };
    if ($currentUserId > 0) {
        $kullaniciOy = $Vote->getUserVote((int)$aktifAnket->id, $currentUserId);
    }
    $countsNew = $Vote->getCountsByOption((int)$aktifAnket->id);
    $legacy = $VoteLegacy->getResults((int)$aktifAnket->id);
    $countMap = [];
    $total = 0;
    foreach ($countsNew as $c) {
        $opt = $normalize($c['option_text']);
        $countMap[$opt] = ($countMap[$opt] ?? 0) + (int)$c['c'];
    }
    foreach ($legacy['rows'] as $r) {
        $opt = $normalize($r['option_text']);
        $countMap[$opt] = ($countMap[$opt] ?? 0) + (int)$r['votes'];
    }
    foreach ($countMap as $k => $v) { $total += (int)$v; }
    foreach ($aktifAnketOptions as $opt) {
        $v = $countMap[$normalize($opt)] ?? 0;
        $p = $total > 0 ? round($v * 100 / $total) : 0;
        $aktifAnketPercentages[$opt] = $p;
    }
    $endDateStr = trim((string)($aktifAnket->end_date ?? ''));
    if ($endDateStr !== '') {
        try {
            $endDate = new \DateTimeImmutable($endDateStr);
            $today = new \DateTimeImmutable('today');
            $isPassiveByDate = $endDate < $today;
            $isActiveHeader = !$isPassiveByDate && (($aktifAnket->status ?? '') === 'Aktif');
        } catch (\Throwable $e) {
            $isPassiveByDate = false;
            $isActiveHeader = (($aktifAnket->status ?? '') === 'Aktif');
        }
    } else {
        $isActiveHeader = (($aktifAnket->status ?? '') === 'Aktif');
    }
}
?>

<div class="main-content" style="margin-bottom: 50px;">
    <div class="row g-4 align-items-stretch mb-5">
        
        <div class="col-12 d-block d-md-none mb-2">
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

        

        <div class="col-xxl-3 col-md-6 mt-0 mb-2 mt-4">
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
        <div class="col-xxl-3 col-md-6 mt-0 mb-2 mt-4">
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
        <div class="col-xxl-3 col-md-6 mt-0 mb-2 mt-4">
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
        <div class="col-xxl-3 col-md-6 mt-0 mb-2 mt-4">
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
                        <span class="fs-12 text-muted d-block">Finansal İşlemler</span>
                    </a>
                    <a href="/sakin/duyurular" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
                        <i class="feather-speaker"></i>
                        <span class="fs-12 text-muted d-block">Duyurular</span>
                    </a>
                    <a href="/sakin/taleplerim" class="flex-fill py-3 px-4 rounded-1 cursor-pointer border border-dashed border-gray-5 text-decoration-none">
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


        <div class="col-xxl-8 col-12 mt-0 ">
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
                    <h5 class="card-title mb-0"><?php echo ($isActiveHeader ? 'Aktif Anket' : 'Anket'); ?></h5>
                    <a href="/sakin/anket-listesi" class="btn btn-light">Tümünü Gör</a>
                    

                    
                </div>
                <div class="card-body">
                    <?php if ($aktifAnket && $kullaniciOy) { ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($aktifAnketOptions as $opt) { ?>
                            <div class="d-flex align-items-center justify-content-between"><span><?php echo htmlspecialchars($opt); ?></span><span class="badge bg-soft-primary text-primary">%<?php echo (int)($aktifAnketPercentages[$opt] ?? 0); ?></span></div>
                            <?php } ?>
                        </div>
                        <a href="/sakin/anketler?id=<?php echo (int)($aktifAnket->id ?? 0); ?>" class="btn btn-light mt-3 w-100">Görüntüle</a>
                    <?php } elseif ($aktifAnket) { ?>
                        <div class="d-flex flex-column gap-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($aktifAnket->title ?? ''); ?></div>
                            <div class="text-muted"><?php echo htmlspecialchars($aktifAnket->description ?? ''); ?></div>
                            <div class="fs-12 text-muted">Başlangıç: <?php echo $aktifAnket->start_date ? \App\Helper\Date::dmy($aktifAnket->start_date) : '-'; ?></div>
                            <div class="fs-12 text-muted">Bitiş: <?php echo $aktifAnket->end_date ? \App\Helper\Date::dmy($aktifAnket->end_date) : '-'; ?></div>
                        </div>
                        <?php if (!$isPassiveByDate) { ?>
                        <a href="/sakin/anketler?id=<?php echo (int)($aktifAnket->id ?? 0); ?>" class="btn btn-primary mt-3 w-100">Oy Ver</a>
                        <?php } else { ?>
                        <a href="/sakin/anketler?id=<?php echo (int)($aktifAnket->id ?? 0); ?>" class="btn btn-light mt-3 w-100">Görüntüle</a>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="alert alert-info mb-0">Henüz anket yok.</div>
                    <?php } ?>
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var container = document.getElementById('duyuruPreview');
    if (!container) return;
    function statusClass(s){
        if (!s) return 'bg-soft-secondary text-secondary';
        var v = String(s).toLowerCase();
        if (v === 'published' || v === 'yayinda' || v === 'aktif') return 'bg-soft-success text-success';
        if (v === 'archived' || v === 'arsivlendi' || v === 'pasif') return 'bg-soft-dark text-dark';
        return 'bg-soft-secondary text-secondary';
    }
    function formatDate(s){
        if (!s) return '-';
        var parts = String(s).split('-');
        if (parts.length === 3) return parts[2] + '.' + parts[1] + '.' + parts[0];
        return s;
    }
    fetch('/pages/duyuru-talep/admin/api/APIDuyuru.php')
        .then(function(r){ return r.ok ? r.json() : null; })
        .then(function(json){
            if (!json) return;
            var rows = [];
            if (Array.isArray(json.data)) {
                if (json.data.length > 0 && !Array.isArray(json.data[0])) {
                    rows = json.data;
                } else {
                    rows = json.data.map(function(row){
                        return {
                            id: row[0],
                            baslik: row[1],
                            icerik: row[2],
                            baslangic_tarihi: row[3],
                            bitis_tarihi: row[4],
                            durum: row[5]
                        };
                    });
                }
            }
            if (!rows.length) return;
            container.innerHTML = '';
            rows.slice(0,3).forEach(function(item){
                var col = document.createElement('div');
                col.className = 'col-12';
                var card = document.createElement('div');
                card.className = 'border rounded-3 p-3 hstack gap-3';
                var icon = document.createElement('div');
                icon.className = 'avatar-text avatar-md bg-soft-primary text-primary border-soft-primary rounded';
                icon.innerHTML = '<i class="feather-speaker"></i>';
                var body = document.createElement('div');
                body.className = 'flex-fill';
                var header = document.createElement('div');
                header.className = 'd-flex align-items-center justify-content-between';
                var title = document.createElement('div');
                title.className = 'fw-semibold text-dark';
                title.textContent = item.baslik || 'Duyuru';
                var status = document.createElement('span');
                status.className = 'badge ' + statusClass(item.durum || '');
                status.textContent = item.durum || '';
                header.appendChild(title);
                header.appendChild(status);
                var summary = document.createElement('div');
                summary.className = 'fs-12 text-muted';
                var plain = String(item.icerik || '').replace(/<[^>]*>/g, '');
                summary.textContent = plain.substring(0,120);
                var dates = document.createElement('div');
                dates.className = 'fs-12 text-muted';
                var start = formatDate(item.baslangic_tarihi || '');
                var end = formatDate(item.bitis_tarihi || '');
                dates.textContent = 'Başlangıç: ' + start + ' · Bitiş: ' + end;
                body.appendChild(header);
                body.appendChild(summary);
                body.appendChild(dates);
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
