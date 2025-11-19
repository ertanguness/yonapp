<?php
use App\Controllers\AuthController;

$user = AuthController::user();
if (!$user) {
    header('Location: /sign-in');
    exit;
}

// Profil kilitli mi kontrol et
$profileLocked = !isset($_SESSION['profile_unlocked']) || $_SESSION['profile_unlocked'] !== true;

// Profil avatar yolunu belirle
$profileAvatar = "/assets/images/avatar/1.png";
try {
    if (isset($user->id)) {
        $uid = (int)$user->id;
        $baseFs = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/') . "/uploads/avatars/user_{$uid}";
        foreach (["jpg","jpeg","png","webp"] as $ext) {
            $candidate = $baseFs . "." . $ext;
            if (file_exists($candidate)) {
                $profileAvatar = "/uploads/avatars/user_{$uid}.{$ext}";
                break;
            }
        }
    }
} catch (\Throwable $e) {}

// Global kilit ekranını göster/gizle
if ($profileLocked) {
    echo '<script>
        localStorage.setItem("lockScreenState", "locked");
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lockScreenManager) {
                window.lockScreenManager.lock();
            }
        });
    </script>';
}
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Profilim</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Profil</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-primary" id="profileSaveBtn">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="card stretch">
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($user->full_name ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon</label>
                                <input type="text" name="phone" id="phone" class="form-control phone-mask" value="<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0(5xx) xxx xx xx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Görev</label>
                                <input type="text" name="job" id="job" class="form-control" value="<?= htmlspecialchars($user->job ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ünvan</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($user->title ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sicil No</label>
                                <input type="text" name="sicil_no" id="sicil_no" class="form-control" value="<?= htmlspecialchars($user->sicil_no ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3">Şifre Değiştir</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Mevcut Şifre</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" autocomplete="current-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Yeni Şifre</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" autocomplete="new-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Not: Şifre alanlarını boş bırakırsanız mevcut şifreniz değişmez.</small>
                        <input type="hidden" name="action" value="updateProfile">
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <img id="profileAvatarImg" src="<?= htmlspecialchars($profileAvatar, ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle user-avtar" width="64" height="64" alt="avatar">
                        <div>
                            <div class="fw-semibold mb-1"><?= htmlspecialchars($user->full_name ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-muted fs-12">Rol: <?= htmlspecialchars($user->roles ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-muted fs-12">Durum: <?= isset($user->status) && (int)$user->status === 1 ? 'Aktif' : 'Pasif' ?></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <input type="file" id="avatarInput" accept="image/png,image/jpeg,image/webp" class="form-control" style="display:none;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="changeAvatarBtn">
                            <i class="feather-image me-1"></i> Profil Resmini Değiştir
                        </button>
                        <div class="text-muted fs-12 mt-2">Desteklenen türler: JPG, PNG, WEBP. Maks 2 MB, 4000x4000 px.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
