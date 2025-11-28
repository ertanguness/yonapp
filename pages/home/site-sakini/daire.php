<?php
use Model\KisilerModel;
use Model\DairelerModel;
use Model\BloklarModel;
use Model\AraclarModel;
use App\Helper\Helper;

$user_id = (int) ($_SESSION['user']->kisi_id ?? ($_SESSION['user']->id ?? 0));
$site_id = (int) ($_SESSION['site_id'] ?? 0);
$sessionEmail = trim((string) ($_SESSION['user']->email ?? ''));
$sessionPhone = trim((string) ($_SESSION['user']->phone ?? ''));
$sessionName  = trim((string) ($_SESSION['user']->full_name ?? ''));

$Kisiler = new KisilerModel();
$Daireler = new DairelerModel();
$Bloklar = new BloklarModel();
$Araclar = new AraclarModel();

// Tüm site kişileri
$allPersons = $Kisiler->SiteTumKisileri($site_id);

// Kullanıcı ile eşleşen tüm malik kayıtları (çoklu daire)
$ownerPersons = array_values(array_filter($allPersons, function($p) use ($sessionEmail,$sessionPhone,$sessionName){
    $e = trim((string)($p->eposta ?? ''));
    $ph= trim((string)($p->telefon ?? ''));
    $n = trim((string)($p->adi_soyadi ?? ''));
    $isOwner = (mb_strtolower((string)($p->uyelik_tipi ?? '')) === mb_strtolower('Kat Maliki'));
    $match = ($sessionName && $n && mb_strtolower($sessionName) === mb_strtolower($n))
        || ($sessionEmail && $e && strcasecmp($sessionEmail, $e) === 0)
        || ($sessionPhone && $ph && $sessionPhone === $ph);
    return $isOwner && $match;
}));

$ownerApartmentIds = array_values(array_unique(array_map(function($p){ return (int)($p->daire_id ?? 0); }, $ownerPersons)));

// Bu dairelere ait kiracılar (aktif/eski)
$tenants = array_values(array_filter($allPersons, function($p) use ($ownerApartmentIds){
    return in_array((int)($p->daire_id ?? 0), $ownerApartmentIds, true)
        && mb_strtolower((string)($p->uyelik_tipi ?? '')) === mb_strtolower('Kiracı');
}));

// Seçilen bağlam
$selectedApartmentId = (int)($_GET['daire_id'] ?? ($ownerApartmentIds[0] ?? 0));
$selectedTenantId    = (int)($_GET['kisi_id'] ?? 0);

// Daire ve blok bilgileri
$apartment = $selectedApartmentId ? $Daireler->DaireBilgisi($site_id, $selectedApartmentId) : null;
$blokAdi   = $apartment ? ($Bloklar->BlokAdi((int)($apartment->blok_id ?? 0)) ?? '-') : ($ownerPersons[0]->blok_kodu ?? '-');

// Malik bilgisi: Öncelik aktif malik (cikis_tarihi boş), yoksa son kayıt
$ownerForSelected = null;
if ($selectedApartmentId) {
    $ownersInApt = array_values(array_filter($allPersons, function($p) use ($selectedApartmentId){
        return (int)($p->daire_id ?? 0) === $selectedApartmentId && mb_strtolower((string)($p->uyelik_tipi ?? '')) === mb_strtolower('Kat Maliki');
    }));
    $activeOwners = array_values(array_filter($ownersInApt, function($p){ return empty($p->cikis_tarihi) || $p->cikis_tarihi === '0000-00-00'; }));
    $ownerForSelected = $activeOwners[0] ?? ($ownersInApt[0] ?? null);
}

// Kiracı bilgisi: Seçili kişi varsa o, yoksa aktif kiracı; yoksa son kiracı
$tenantForSelected = null;
if ($selectedTenantId) {
    $tenantForSelected = $Kisiler->getPersonById($selectedTenantId);
} elseif ($selectedApartmentId) {
    $tenantsInApt = array_values(array_filter($tenants, function($p) use ($selectedApartmentId){ return (int)($p->daire_id ?? 0) === $selectedApartmentId; }));
    $activeTenants = array_values(array_filter($tenantsInApt, function($p){ return empty($p->cikis_tarihi) || $p->cikis_tarihi === '0000-00-00'; }));
    $tenantForSelected = $activeTenants[0] ?? ($tenantsInApt[0] ?? null);
}

// Araç bilgileri (öncelik seçili kiracı, yoksa malik)
$carOwnerId = (int)($tenantForSelected->id ?? ($ownerForSelected->id ?? 0));
$cars = $carOwnerId ? $Araclar->KisiAracBilgileri($carOwnerId) : [];
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Daire ve Kiracı Bilgileri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Daire Bilgileri</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daire/Kiracı Seçimi</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="fw-semibold mb-2">Dairelerim</div>
                            <div class="hstack gap-2 flex-wrap">
                                <?php foreach ($ownerApartmentIds as $did) { $d = $Daireler->DaireBilgisi($site_id, $did); $blk = $Bloklar->BlokAdi((int)($d->blok_id ?? 0)) ?? '-'; ?>
                                    <a href="/sakin/daire?daire_id=<?php echo (int)$did; ?>" class="btn btn-sm btn-light<?php echo ($selectedApartmentId === (int)$did) ? ' active' : ''; ?>">
                                        <?php echo htmlspecialchars($d->daire_kodu ?? ($blk . ' D' . ($d->daire_no ?? ''))); ?>
                                    </a>
                                <?php } ?>
                                <?php if (empty($ownerApartmentIds)) { ?>
                                    <span class="text-muted">Eşleşen daire bulunamadı</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="fw-semibold mb-2">Kiracılarım</div>
                            <div class="hstack gap-2 flex-wrap">
                                <?php foreach ($tenants as $t) { $aktif = empty($t->cikis_tarihi) || $t->cikis_tarihi === '0000-00-00'; ?>
                                    <a href="/sakin/daire?kisi_id=<?php echo (int)$t->id; ?>&daire_id=<?php echo (int)($t->daire_id ?? 0); ?>" class="btn btn-sm btn-light<?php echo ($selectedTenantId === (int)$t->id) ? ' active' : ''; ?>">
                                        <?php echo htmlspecialchars($t->adi_soyadi ?? '-'); ?>
                                        <span class="badge ms-2 <?php echo $aktif ? 'bg-soft-success text-success' : 'bg-soft-secondary text-secondary'; ?>"><?php echo $aktif ? 'Aktif' : 'Eski'; ?></span>
                                    </a>
                                <?php } ?>
                                <?php if (empty($tenants)) { ?>
                                    <span class="text-muted">Kiracı kaydı bulunamadı</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daire Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                            <i class="feather-home"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Daire: <?php echo htmlspecialchars($apartment->daire_kodu ?? '-'); ?></div>
                            <div class="fs-12 text-muted">Blok: <?php echo htmlspecialchars($blokAdi ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted">m²</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($apartment->net_alan ?? $apartment->brut_alan ?? '-'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Tip</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($ownerForSelected->uyelik_tipi ?? 'Kat Maliki'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Malik Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded">
                            <?php echo Helper::getInitials($ownerForSelected->adi_soyadi ?? ($_SESSION['user']->full_name ?? '')); ?>
                        </div>
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($ownerForSelected->adi_soyadi ?? ($_SESSION['user']->full_name ?? '')); ?></div>
                            <div class="fs-12 text-muted"><?php echo htmlspecialchars($ownerForSelected->eposta ?? ($_SESSION['user']->email ?? '')); ?></div>
                            <div class="fs-12 text-muted"><?php echo htmlspecialchars($ownerForSelected->telefon ?? ($_SESSION['user']->phone ?? '')); ?></div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted">Sözleşme Başlangıç</div>
                            <div class="fw-semibold">-</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Sözleşme Bitiş</div>
                            <div class="fw-semibold">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Kiracı Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded">
                            <i class="feather-user"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($tenantForSelected->adi_soyadi ?? '-'); ?></div>
                            <div class="fs-12 text-muted"><?php echo htmlspecialchars($tenantForSelected->eposta ?? '-'); ?></div>
                            <div class="fs-12 text-muted"><?php echo htmlspecialchars($tenantForSelected->telefon ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted">Sözleşme Başlangıç</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($tenantForSelected->giris_tarihi ?? '-'); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Sözleşme Bitiş</div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($tenantForSelected->cikis_tarihi ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Araçlarım</h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#carModal">Ekle</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($cars)) { foreach ($cars as $car) { ?>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-md bg-soft-info text-info border-soft-info rounded">
                                <i class="feather-car"></i>
                            </div>
                            <div class="fw-semibold"><?php echo htmlspecialchars(($car->plaka ?? '-')) . ' ' . htmlspecialchars(($car->marka_model ?? '')); ?></div>
                        </div>
                        <a href="javascript:void(0);" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#carModal" data-car-id="<?php echo (int)($car->id ?? 0); ?>" data-car-plaka="<?php echo htmlspecialchars($car->plaka ?? ''); ?>" data-car-marka="<?php echo htmlspecialchars($car->marka_model ?? ''); ?>">Düzenle</a>
                    </div>
                    <hr class="border-dashed my-3">
                    <?php } } else { ?>
                        <div class="alert alert-info mb-0">Kayıtlı araç bulunamadı.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Acil İletişim</h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#emergencyModal">Ekle</button>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-md bg-soft-danger text-danger border-soft-danger rounded">
                                <i class="feather-phone"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">-</div>
                                <div class="fs-12 text-muted">-</div>
                            </div>
                        </div>
                        <a href="javascript:void(0);" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#emergencyModal" data-em-name="<?php echo htmlspecialchars($ownerForSelected->adi_soyadi ?? ''); ?>" data-em-phone="<?php echo htmlspecialchars($ownerForSelected->telefon ?? ''); ?>" data-em-relation="Yakın">Düzenle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="carModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Araç</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="car-id">
        <div class="mb-3">
          <label class="form-label">Plaka</label>
          <input type="text" class="form-control" id="car-plaka">
        </div>
        <div class="mb-0">
          <label class="form-label">Marka/Model</label>
          <input type="text" class="form-control" id="car-marka">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
  </div>

<div class="modal fade" id="emergencyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Acil İletişim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Ad Soyad</label>
          <input type="text" class="form-control" id="em-name">
        </div>
        <div class="mb-3">
          <label class="form-label">Telefon</label>
          <input type="text" class="form-control" id="em-phone">
        </div>
        <div class="mb-0">
          <label class="form-label">Yakınlık</label>
          <input type="text" class="form-control" id="em-relation">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var carModal = document.getElementById('carModal');
  carModal.addEventListener('show.bs.modal', function(e){
    var btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('car-id').value = btn.getAttribute('data-car-id')||'';
    document.getElementById('car-plaka').value = btn.getAttribute('data-car-plaka')||'';
    document.getElementById('car-marka').value = btn.getAttribute('data-car-marka')||'';
  });
  var emModal = document.getElementById('emergencyModal');
  emModal.addEventListener('show.bs.modal', function(e){
    var btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('em-name').value = btn.getAttribute('data-em-name')||'';
    document.getElementById('em-phone').value = btn.getAttribute('data-em-phone')||'';
    document.getElementById('em-relation').value = btn.getAttribute('data-em-relation')||'';
  });
});
</script>