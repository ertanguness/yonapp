<?php

use Model\PersonelOdemeModel;
use Model\UserModel;
use App\Helper\Helper;
use App\Helper\Security;
use App\Helper\Date;

$PersonelOdeme = new PersonelOdemeModel();
$User = new UserModel();

// Mevcut personel ID'si (incexp $incexp->id)
$personel_id = $incexp->id ?? 0;

// Personelin ödemelerini getir
$odemeler = $personel_id ? $PersonelOdeme->getOdemelerByPersonel($personel_id) : [];
$odemeStats = $personel_id ? $PersonelOdeme->getTotalOdemelerByPersonel($personel_id) : null;

// Ödeme türleri listesi
$odeme_turleri = [
    'salary' => 'Maaş',
    'bonus' => 'Prim/Bonus',
    'advance' => 'Ön Ödeme',
    'commission' => 'Komisyon',
    'incentive' => 'Teşvik',
    'other' => 'Diğer'
];

// Kullanıcı admin mi kontrol et (role_id == 1 admin)
$current_user = $_SESSION['user'] ?? null;
$is_admin = ($current_user->role_id ?? 0) == 1;
?>

<div class="card-body payments-info">
    <!-- İstatistikler Kartları -->
    <?php if ($odemeStats && $odemeStats->toplam_tutar): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted small">
                        <i class="bi bi-coin me-1"></i>Toplam Ödeme
                    </h6>
                    <h4 class="text-primary fw-bold mb-0">
                        <?= Helper::formattedMoney($odemeStats->toplam_tutar ?? 0) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted small">
                        <i class="bi bi-list-check me-1"></i>Ödeme Sayısı
                    </h6>
                    <h4 class="text-info fw-bold mb-0">
                        <?= $odemeStats->odeme_sayisi ?? 0 ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted small">
                        <i class="bi bi-graph-up me-1"></i>Ort. Ödeme
                    </h6>
                    <h4 class="text-success fw-bold mb-0">
                        <?= Helper::formattedMoney($odemeStats->ortalama_tutar ?? 0) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <hr class="mb-4">
    <?php endif; ?>

    <!-- Ödeme Ekle Butonu -->
    <div class="mb-4">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOdemeModal">
            <i class="bi bi-plus-circle me-2"></i>Ödeme Ekle
        </button>
    </div>

    <!-- Ödemeler Tablosu -->
    <div class="table-responsive">
        <table class="table table-hover datatables" id="odemelerTable">
            <thead class="table-light">
                <tr>
                    <th width="50">Sıra</th>
                    <th>Ödeme Tarihi</th>
                    <th>Tutar</th>
                    <th>Ödeme Türü</th>
                    <th>Açıklama</th>
                    <?php if ($is_admin): ?>
                        <th>Yönetici Notu</th>
                    <?php endif; ?>
                    <th width="5">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($odemeler)): ?>
                    <?php foreach ($odemeler as $index => $odeme): ?>
                        <?php $kayit_yapan = $User->find($odeme->kayit_yapan_id ?? 0); ?>
                        <tr>
                            <td><small class="text-muted"><?= $index + 1 ?></small></td>
                            <td>
                                <strong><?= date('d.m.Y', strtotime($odeme->odeme_tarihi)) ?></strong>
                                <?php if ($kayit_yapan): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($kayit_yapan->username ?? 'Bilinmiyor') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-success">
                                    <?= Helper::formattedMoney($odeme->tutar) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= $odeme_turleri[$odeme->odeme_turu] ?? $odeme->odeme_turu ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($odeme->aciklama ?? '') ?>">
                                    <?= htmlspecialchars($odeme->aciklama ?? '-') ?>
                                </div>
                            </td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($odeme->yonetici_notu ?? '') ?>">
                                        <small><?= htmlspecialchars($odeme->yonetici_notu ?? '-') ?></small>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm edit-odeme" 
                                        data-odeme-id="<?= Security::encrypt($odeme->id) ?>" 
                                        title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-odeme" 
                                        data-odeme-id="<?= Security::encrypt($odeme->id) ?>" 
                                        title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                    
            </tbody>
        </table>
    </div>
</div>

<!-- Ödeme Ekle/Düzenle Modal -->
<div class="modal fade" id="addOdemeModal" tabindex="-1" aria-labelledby="addOdemeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="odemeForm">
                <div class="modal-header bg-primary bg-opacity-10">
                    <h5 class="modal-title" id="addOdemeModalLabel">
                        <i class="bi bi-plus-circle me-2"></i><span id="modalTitle">Ödeme Ekle</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="save_personel_odeme">
                    <input type="hidden" name="odeme_id" id="odemeId">
                    <input type="hidden" name="personel_id" value="<?= Security::encrypt($personel_id) ?>">

                    <!-- Ödeme Tarihi -->
                    <div class="mb-3">
                        <label for="odemeTarihi" class="form-label fw-semibold">
                            Ödeme Tarihi <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="odemeTarihi" name="odeme_tarihi" required>
                    </div>

                    <!-- Ödeme Tutarı -->
                    <div class="mb-3">
                        <label for="tutar" class="form-label fw-semibold">
                            Ödeme Tutarı (₺) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="tutar" name="tutar" 
                               placeholder="0.00" step="0.01" min="0" required>
                    </div>

                    <!-- Ödeme Türü -->
                    <div class="mb-3">
                        <label for="odemeTuru" class="form-label fw-semibold">
                            Ödeme Türü <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="odemeTuru" name="odeme_turu" required>
                            <option value="">-- Seçiniz --</option>
                            <?php foreach ($odeme_turleri as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Açıklama -->
                    <div class="mb-3">
                        <label for="aciklama" class="form-label fw-semibold">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" 
                                  rows="2" placeholder="Ödeme hakkında açıklama..."></textarea>
                    </div>

                    <!-- Yönetici Notu (Sadece Admin) -->
                    <?php if ($is_admin): ?>
                    <div class="mb-3">
                        <label for="yoneticiNotu" class="form-label fw-semibold">
                            <i class="bi bi-shield-lock me-1"></i>Yönetici Notu
                            <small class="text-muted">(Sadece Yönetici Görecek)</small>
                        </label>
                        <textarea class="form-control" id="yoneticiNotu" name="yonetici_notu" rows="2" 
                                  placeholder="Yalnızca yöneticinin göreceği notlar..."></textarea>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i><span id="submitBtnText">Kaydet</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const odemeForm = document.getElementById('odemeForm');
    const modal = new bootstrap.Modal(document.getElementById('addOdemeModal'));
    const modalElement = document.getElementById('addOdemeModal');
    const personelId = '<?= Security::encrypt($personel_id) ?>';

    // Bugünün tarihini varsayılan olarak ayarla
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('odemeTarihi').value = today;

    // Modal açıldığında temizle
    modalElement.addEventListener('show.bs.modal', function() {
        odemeForm.reset();
        document.getElementById('odemeId').value = '';
        document.getElementById('odemeTarihi').value = today;
        document.getElementById('modalTitle').textContent = 'Ödeme Ekle';
        document.getElementById('submitBtnText').textContent = 'Kaydet';
    });

    // Form gönder
    odemeForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(odemeForm);
        
        try {
            const response = await fetch('/pages/persons/api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showToast(data.message || 'Ödeme başarıyla kaydedildi', 'success');
                bootstrap.Modal.getInstance(modalElement).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'İşlem başarısız', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('İşlem sırasında hata oluştu', 'error');
        }
    });

    // Ödeme Düzenle
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-odeme')) {
            const button = e.target.closest('.edit-odeme');
            const odemeId = button.dataset.odemeId;
            const row = button.closest('tr');
            
            // Form alanlarını doldur
            const dateStr = row.cells[1].textContent.trim().split('\n')[0];
            const date = new Date(dateStr.split('.').reverse().join('-'));
            const tutar = row.cells[2].textContent.replace(/[^\d.,]/g, '').replace('.', '').replace(',', '.');
            const tur = row.cells[3].textContent.trim().toLowerCase();
            const aciklama = row.cells[4].getAttribute('title') || '';
            
            document.getElementById('odemeId').value = odemeId;
            document.getElementById('odemeTarihi').value = date.toISOString().split('T')[0];
            document.getElementById('tutar').value = tutar;
            document.getElementById('odemeTuru').value = Object.keys(<?= json_encode($odeme_turleri) ?>).find(k => 
                <?= json_encode($odeme_turleri) ?>[k].toLowerCase() === tur
            ) || '';
            document.getElementById('aciklama').value = aciklama;
            
            document.getElementById('modalTitle').textContent = 'Ödeme Düzenle';
            document.getElementById('submitBtnText').textContent = 'Güncelle';
            
            modal.show();
        }
    });

    // Ödeme Sil
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-odeme')) {
            const odemeId = e.target.closest('.delete-odeme').dataset.odemeId;
            if (confirm('Bu ödemeyi silmek istediğinize emin misiniz?')) {
                deleteOdeme(odemeId);
            }
        }
    });

    async function deleteOdeme(odemeId) {
        const formData = new FormData();
        formData.append('action', 'delete_personel_odeme');
        formData.append('odeme_id', odemeId);
        
        try {
            const response = await fetch('/pages/persons/api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showToast('Ödeme başarıyla silindi', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Silme işlemi başarısız', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('İşlem sırasında hata oluştu', 'error');
        }
    }

    function showToast(message, type) {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message,
                duration: 3000,
                gravity: 'top',
                position: 'center',
                backgroundColor: type === 'success' ? '#28a745' : '#dc3545'
            }).showToast();
        } else {
            alert(message);
        }
    }
});
</script>
