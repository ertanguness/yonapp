<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Model\KisilerModel;

$KisiModel = new KisilerModel();
$site_id = $_SESSION['site_id'];

// Kişileri getir
$kisiler = $KisiModel->SiteTumKisileri($site_id);

// Verileri formatlı hale getir
$data = [];
if ($kisiler) {
    foreach ($kisiler as $kisi) {
        $telefon_temiz = preg_replace('/\D/', '', $kisi->telefon);
        
        $data[] = [
            'id' => $kisi->id,
            'daire_kodu' => htmlspecialchars($kisi->daire_kodu),
            'adi_soyadi' => htmlspecialchars($kisi->adi_soyadi),
            'telefon' => htmlspecialchars($kisi->telefon),
            'telefon_temiz' => $telefon_temiz,
            'uyelik_tipi' => htmlspecialchars($kisi->uyelik_tipi),
            'giris_tarihi' => date('d.m.Y', strtotime($kisi->giris_tarihi)),
            'cikis_tarihi' => $kisi->cikis_tarihi !== '0000-00-00' && !empty($kisi->cikis_tarihi) ? date('d.m.Y', strtotime($kisi->cikis_tarihi)) : '-',
            'aktif_mi' => $kisi->aktif_mi,
            'status' => $kisi->aktif_mi ? 'Aktif' : 'Pasif'
        ];
    }
}
?>

<style>
.kisilerden-sec-wrapper {
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
}

.kisiler-filters {
    background-color: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

.filter-group {
    margin-bottom: 12px;
}

.filter-group:last-child {
    margin-bottom: 0;
}

.filter-group label {
    font-weight: 500;
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
}

.checkbox-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.checkbox-item input[type="checkbox"] {
    cursor: pointer;
}

.search-box {
    width: 100%;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.aktif {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.pasif {
    background-color: #f8d7da;
    color: #721c24;
}

.kisiler-table-wrapper {
    flex: 1;
    overflow-y: auto;
    flex-shrink: 1;
}

#kisilerTable {
    margin-bottom: 0 !important;
}

#kisilerTable tbody tr {
    cursor: pointer;
}

#kisilerTable tbody tr:hover {
    background-color: #f8f9fa;
}

.selected-info {
    background-color: #d1ecf1;
    color: #0c5460;
    padding: 10px 15px;
    border-bottom: 1px solid #bee5eb;
    font-weight: 500;
    font-size: 13px;
    flex-shrink: 0;
}

.offcanvas-footer {
    padding: 15px;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    display: flex;
    gap: 10px;
    flex-shrink: 0;
    position: sticky;
    bottom: 0;
    z-index: 5;
}

.offcanvas-footer button {
    flex: 1;
}

.select-all-wrapper {
    padding: 10px 15px;
    background-color: #fff;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 4;
}
</style>

<!-- Seçilen Sayısı -->
<div class="selected-info">
    <i class="fas fa-info-circle me-2"></i><strong id="selectedCount">0</strong> kişi seçildi
</div>

<div class="kisilerden-sec-wrapper">
    <!-- Filtreler -->
    <div class="kisiler-filters">
        <!-- Arama Kutusu -->
        <div class="filter-group">
            <input type="text" id="searchBox" class="form-control search-box" placeholder="Ad, Soyadı veya Telefon Ara...">
        </div>

        <!-- Durum Filtreleri -->
        <div class="filter-group">
            <label>Durum</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="filterAktif" class="filter-checkbox" value="aktif" checked>
                    <label for="filterAktif" class="form-check-label mb-0">Aktif</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="filterPasif" class="filter-checkbox" value="pasif" checked>
                    <label for="filterPasif" class="form-check-label mb-0">Pasif</label>
                </div>
            </div>
        </div>

        <!-- Üyelik Tipi Filtreleri -->
        <div class="filter-group">
            <label>Üyelik Tipi</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="filterEvSahibi" class="filter-checkbox" value="Kat Maliki" checked>
                    <label for="filterEvSahibi" class="form-check-label mb-0">Ev Sahibi</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="filterKiraci" class="filter-checkbox" value="Kiracı" checked>
                    <label for="filterKiraci" class="form-check-label mb-0">Kiracı</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablo Header -->
    <div class="select-all-wrapper">
        <input type="checkbox" id="selectAll" class="form-check-input">
        <label for="selectAll" class="form-check-label mb-0">Tümünü Seç</label>
    </div>

    <!-- Tablo -->
    <div class="kisiler-table-wrapper">
        <table id="kisilerTable" class="table table-hover mb-0">
            <thead >
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Daire</th>
                    <th>Adı Soyadı</th>
                    <th>Telefon</th>
                    <th>Üyelik Tipi</th>
                    <th>Giriş Tarihi</th>
                    <th>Çıkış Tarihi</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data as $kisi): ?>
                        <tr class="kisi-row" data-id="<?php echo $kisi['id']; ?>" data-phone="<?php echo $kisi['telefon_temiz']; ?>" data-aktif="<?php echo $kisi['aktif_mi'] ? '1' : '0'; ?>" data-uyelik="<?php echo $kisi['uyelik_tipi']; ?>">
                            <td>
                                <input type="checkbox" class="kisi-checkbox form-check-input" data-phone="<?php echo $kisi['telefon_temiz']; ?>" data-name="<?php echo $kisi['adi_soyadi']; ?>">
                            </td>
                            <td><strong><?php echo $kisi['daire_kodu']; ?></strong></td>
                            <td><?php echo $kisi['adi_soyadi']; ?></td>
                            <td><code><?php echo $kisi['telefon']; ?></code></td>
                            <td><small><?php echo $kisi['uyelik_tipi']; ?></small></td>
                            <td><small><?php echo $kisi['giris_tarihi']; ?></small></td>
                            <td><small><?php echo $kisi['cikis_tarihi']; ?></small></td>
                            <td><span class="status-badge <?php echo $kisi['aktif_mi'] ? 'aktif' : 'pasif'; ?>"><?php echo $kisi['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Kişi bulunamadı</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Footer -->
<div class="offcanvas-footer">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="offcanvas">İptal</button>
    <button type="button" class="btn btn-primary btn-sm" id="seciliEkleBtn">
        <i class="fas fa-check me-2"></i>Seçilenleri Ekle
    </button>
</div>
