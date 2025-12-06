<?php
use Model\KisilerModel;
use Model\DairelerModel;
use Model\BloklarModel;
use Model\AraclarModel;
use Model\AcilDurumKisileriModel;
use App\Helper\Helper;
use App\Helper\Security;

$user_id = (int) ($_SESSION['user']->kisi_id ?? ($_SESSION['user']->id ?? 0));
$site_id = (int) ($_SESSION['site_id'] ?? 0);
$sessionEmail = trim((string) ($_SESSION['user']->email ?? ''));
$sessionPhone = trim((string) ($_SESSION['user']->phone ?? ''));
$sessionName  = trim((string) ($_SESSION['user']->full_name ?? ''));

$Kisiler = new KisilerModel();
$Daireler = new DairelerModel();
$Bloklar = new BloklarModel();
$Araclar = new AraclarModel();
$AcilDurum = new AcilDurumKisileriModel();

// Tüm site kişileri
$allPersons = $Kisiler->SiteTumKisileri($site_id);

if (isset($_GET['clear_context'])) {
    unset($_SESSION['selected_apartment_id'], $_SESSION['selected_person_id']);
    header('Location: /sakin/daire');
    exit;
}

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

$selectedApartmentId = (int)($_SESSION['selected_apartment_id'] ?? 0);
$selectedTenantId    = (int)($_SESSION['selected_person_id'] ?? 0);

if (isset($_GET['daire_id'])) {
    $selectedApartmentId = (int)$_GET['daire_id'];
    $_SESSION['selected_apartment_id'] = $selectedApartmentId;
    if (!isset($_GET['kisi_id'])) {
        $selectedTenantId = 0;
        unset($_SESSION['selected_person_id']);
    }
}

if (isset($_GET['kisi_id'])) {
    $selectedTenantId = (int)$_GET['kisi_id'];
    $_SESSION['selected_person_id'] = $selectedTenantId;
    if (isset($_GET['daire_id'])) {
        $_SESSION['selected_apartment_id'] = (int)$_GET['daire_id'];
    }
}

if (!$selectedApartmentId) {
    $selectedApartmentId = (int)($ownerApartmentIds[0] ?? 0);
}

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
    if (!$tenantForSelected && $selectedApartmentId) {
        $tenantsInApt = array_values(array_filter($tenants, function($p) use ($selectedApartmentId){ return (int)($p->daire_id ?? 0) === $selectedApartmentId; }));
        $activeTenants = array_values(array_filter($tenantsInApt, function($p){ return empty($p->cikis_tarihi) || $p->cikis_tarihi === '0000-00-00'; }));
        $tenantForSelected = $activeTenants[0] ?? ($tenantsInApt[0] ?? null);
    }
} elseif ($selectedApartmentId) {
    $tenantsInApt = array_values(array_filter($tenants, function($p) use ($selectedApartmentId){ return (int)($p->daire_id ?? 0) === $selectedApartmentId; }));
    $activeTenants = array_values(array_filter($tenantsInApt, function($p){ return empty($p->cikis_tarihi) || $p->cikis_tarihi === '0000-00-00'; }));
    $tenantForSelected = $activeTenants[0] ?? ($tenantsInApt[0] ?? null);
}

// Araç bilgileri (öncelik seçili kiracı, yoksa malik)
$carOwnerId = (int)($tenantForSelected->id ?? ($ownerForSelected->id ?? 0));
$cars = $carOwnerId ? $Araclar->KisiAracBilgileri($carOwnerId) : [];
$emergencyList = $carOwnerId ? $AcilDurum->findWhere(['kisi_id' => $carOwnerId], 'id DESC') : [];
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
                            <div class="vstack gap-2">
                                <?php foreach ($ownerApartmentIds as $did) { $d = $Daireler->DaireBilgisi($site_id, $did); $blk = $Bloklar->BlokAdi((int)($d->blok_id ?? 0)) ?? '-'; ?>
                                    <a href="/sakin/daire?daire_id=<?php echo (int)$did; ?>" class="d-flex align-items-center justify-content-between border rounded px-3 py-2 text-decoration-none<?php echo ($selectedApartmentId === (int)$did) ? ' bg-soft-primary border-soft-primary' : ''; ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                                                <i class="feather-home"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-semibold"><?php echo htmlspecialchars($d->daire_kodu ?? ($blk . ' D' . ($d->daire_no ?? ''))); ?></div>
                                                <div class="fs-12 text-muted fw-normal"><?php echo 'Blok: ' . htmlspecialchars($blk ?? '-'); ?></div>
                                            </div>
                                        </div>
                                        <div class="avatar-text avatar-md">
                                            <i class="feather feather-arrow-right"></i>
                                        </div>
                                    </a>
                                <?php } ?>
                                <?php if (empty($ownerApartmentIds)) { ?>
                                    <span class="text-muted">Eşleşen daire bulunamadı</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="fw-semibold mb-2">Kiracılarım</div>
                            <div class="vstack gap-2">
                                <?php foreach ($tenants as $t) { $aktif = empty($t->cikis_tarihi) || $t->cikis_tarihi === '0000-00-00'; ?>
                                    <a href="/sakin/finans?kisi_id=<?php echo (int)$t->id; ?>" class="d-flex align-items-center justify-content-between border rounded px-3 py-2 text-decoration-none<?php echo ($selectedTenantId === (int)$t->id) ? ' bg-soft-success border-soft-success' : ''; ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded">
                                                <i class="feather-user"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-semibold"><?php echo htmlspecialchars($t->adi_soyadi ?? '-'); ?></div>
                                                <div class="fs-12 text-muted fw-normal"><?php echo $aktif ? 'Aktif' : 'Eski'; ?></div>
                                            </div>
                                        </div>
                                        <div class="avatar-text avatar-md">
                                            <i class="feather feather-arrow-right"></i>
                                        </div>
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
                    <?php if (!empty($cars)) { foreach ($cars as $car) { $encId = \App\Helper\Security::encrypt((int)($car->id ?? 0)); ?>
                    <div class="hstack gap-3 justify-content-between" data-car-row-id="<?php echo (int)($car->id ?? 0); ?>">
                        <div class="hstack gap-3">
                            <div class="wd-7 ht-7 bg-info rounded-circle"></div>
                            <div class="ps-3 border-start border-3 border-info rounded">
                                <a href="javascript:void(0);" class="fw-semibold mb-1 text-truncate-1-line">
                                    <?php echo htmlspecialchars($car->plaka ?? '-') . ' • ' . htmlspecialchars($car->marka_model ?? ''); ?>
                                </a>
                                <a href="javascript:void(0);" class="fs-12 text-muted">
                                    <i class="feather-car fs-10 me-1"></i>
                                    <span class="fw-normal"><?php echo htmlspecialchars($car->renk ?? '-') . ' • ' . htmlspecialchars($car->arac_tipi ?? '-'); ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="hstack gap-2">
                            <a href="javascript:void(0);" class="avatar-text avatar-md" data-car-id="<?php echo $encId; ?>" data-bs-toggle="modal" data-bs-target="#carModal"><i class="feather-edit"></i></a>
                            <a href="javascript:void(0);" class="avatar-text avatar-md delete-car" data-id="<?php echo $encId; ?>"><i class="feather-trash-2"></i></a>
                        </div>
                    </div>
                    <hr class="border-dashed my-3">
                    <?php } } else { ?>
                        <div class="alert alert-info mb-0">Kayıtlı araç bulunamadı.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6 mb-5">
            <div class="card rounded-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Acil İletişim</h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#emergencyModal">Ekle</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($emergencyList)) { foreach ($emergencyList as $em) { $relText = App\Helper\Helper::RELATIONSHIP[$em->yakinlik ?? ''] ?? '-'; $emEncId = \App\Helper\Security::encrypt((int)($em->id ?? 0)); ?>
                    <div class="hstack gap-3 justify-content-between" data-em-row-id="<?php echo Security::encrypt($em->id ?? 0); ?>">
                        <div class="hstack gap-3">
                            <div class="wd-7 ht-7 bg-danger rounded-circle"></div>
                            <div class="ps-3 border-start border-3 border-danger rounded">
                                <a href="javascript:void(0);" class="fw-semibold mb-1 text-truncate-1-line"><?php echo htmlspecialchars($em->adi_soyadi ?? '-'); ?></a>
                                <a href="javascript:void(0);" class="fs-12 text-muted">
                                    <i class="feather-phone fs-10 me-1"></i>
                                    <span class="fw-normal"><?php echo htmlspecialchars($em->telefon ?? '-'); ?> • <?php echo htmlspecialchars($relText); ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="hstack gap-2">
                            <a href="javascript:void(0);" class="avatar-text avatar-md" data-em-id="<?php echo $emEncId; ?>" data-bs-toggle="modal" data-bs-target="#emergencyModal"><i class="feather-edit"></i></a>
                            <a href="javascript:void(0);" class="avatar-text avatar-md delete-em" data-id="<?php echo $emEncId; ?>"><i class="feather-trash-2"></i></a>
                        </div>
                    </div>
                    <hr class="border-dashed my-3">
                    <?php } } else { ?>
                        <div class="alert alert-info mb-0">Kayıtlı acil iletişim kişisi bulunamadı.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="carModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="car-form">
      <div class="modal-header">
        <div class="hstack gap-3 align-items-center">
          <div class="avatar-text avatar-md bg-soft-info text-info border-soft-info rounded"><i class="feather-car"></i></div>
          <h5 class="modal-title mb-0">Araç</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="car-id" name="id" value="<?php echo \App\Helper\Security::encrypt(0); ?>">
        <input type="hidden" id="car-kisi-id" name="kisi_id" value="<?php echo (int)$carOwnerId; ?>">
        <div class="mb-3">
          <label class="form-label">Plaka</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-hash"></i></span>
            <input type="text" class="form-control" id="car-plaka" name="plaka" required pattern="^[A-Z0-9\s-]{5,12}$">
          </div>
          <div class="form-text">Örn: 34 ABC 123</div>
        </div>
        <div class="mb-0">
          <label class="form-label">Marka/Model</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-truck"></i></span>
            <input type="text" class="form-control" id="car-marka" name="marka_model" required>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Renk</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-droplet"></i></span>
            <input type="text" class="form-control" id="car-renk" name="renk">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Araç Tipi</label>
            <div class="input-group flex-nowrap w-100">

            <span class="input-group-text"><i class="feather-truck"></i></span>
            <select class="form-select select2" id="car-tipi" name="arac_tipi">
              <option value="">Seçiniz</option>
              <option value="Otomobil">Otomobil</option>
              <option value="Motosiklet">Motosiklet</option>
              <option value="Kamyonet">Kamyonet</option>
              <option value="Diğer">Diğer</option>
            </select>
          </div>
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
    <form class="modal-content" id="em-form">
      <div class="modal-header">
        <div class="hstack gap-3 align-items-center">
          <div class="avatar-text avatar-md bg-soft-danger text-danger border-soft-danger rounded"><i class="feather-phone"></i></div>
          <h5 class="modal-title mb-0">Acil İletişim</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="em-id" name="id" value="<?php echo \App\Helper\Security::encrypt(0); ?>">
        <input type="hidden" id="em-kisi-id" name="kisi_id" value="<?php echo (int)$carOwnerId; ?>">
        <div class="mb-3">
          <label class="form-label">Ad Soyad</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-user"></i></span>
            <input type="text" class="form-control" id="em-name" name="adi_soyadi" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Telefon</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-smartphone"></i></span>
            <input type="text" class="form-control" id="em-phone" name="telefon" required pattern="^\d{10}$">
          </div>
          <div class="form-text">10 haneli, boşluksuz</div>
        </div>
        <div class="mb-0">
           <label class="form-label">Yakınlık</label>
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text">
                    <i class="feather-briefcase"></i>
                </div>
                <?php echo Helper::relationshipSelect('yakinlik',1); ?>
            </div>
        </div>
        <div class="mt-3">
          <label class="form-label">İletişim Notları</label>
          <div class="input-group">
            <span class="input-group-text"><i class="feather-edit-2"></i></span>
            <textarea class="form-control" id="em-notes" name="notlar" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
        <button type="submit" class="btn btn-primary">Kaydet</button>
      </div>
    </form>
  </div>
  </div>

<script src="/pages/site-sakini/js/sakin.js"></script>
