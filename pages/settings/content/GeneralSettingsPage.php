<!-- Site Bilgileri -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3"><i class="fas fa-info-circle"></i> Site Bilgileri</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="siteName" class="fw-semibold">Site Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-home"></i>
                </div>
                <input type="text" class="form-control" id="siteName" name="siteName" placeholder="Site adı yazınız" required>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="blockCount" class="fw-semibold">Blok Sayısı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-th-large"></i>
                </div>
                <input type="number" class="form-control" id="blockCount" name="blockCount" placeholder="Blok sayısı yazınız" required>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="apartmentCount" class="fw-semibold">Daire Sayısı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-building"></i>
                </div>
                <input type="number" class="form-control" id="apartmentCount" name="apartmentCount" placeholder="Daire sayısı yazınız">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="address" class="fw-semibold">Adres:</label>
        </div>
        <div class="col-lg-4">
            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Site adresini yazınız" required></textarea>
        </div>
    </div>
</div>

<!-- İletişim Bilgileri -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3"><i class="fas fa-phone-alt"></i> İletişim Bilgileri</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="email" class="fw-semibold">E-posta:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </div>
                <input type="email" class="form-control" id="email" name="email" placeholder="E-posta adresi yazınız" required>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="phone" class="fw-semibold">Telefon:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-phone"></i>
                </div>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Telefon numarası yazınız" required>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="emergencyContact" class="fw-semibold">Acil İletişim:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <input type="tel" class="form-control" id="emergencyContact" name="emergencyContact" placeholder="Acil iletişim numarası">
            </div>
        </div>
    </div>
</div>

<!-- Varsayılan Parametreler -->
<div class="card-body personal-info">
    <h6 class="mb-3"><i class="fas fa-cogs"></i> Varsayılan Ayarlar</h6>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="currency" class="fw-semibold">Para Birimi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <select class="form-select" id="currency" name="currency">
                    <option value="TL">Türk Lirası (₺)</option>
                    <option value="USD">Dolar ($)</option>
                    <option value="EUR">Euro (€)</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="paymentCycle" class="fw-semibold">Ödeme Döngüsü:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <select class="form-select" id="paymentCycle" name="paymentCycle">
                    <option value="monthly">Aylık</option>
                    <option value="quarterly">Üç Aylık</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="lateFee" class="fw-semibold">Geç Ödeme Cezası (%):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-percentage"></i>
                </div>
                <input type="number" class="form-control" id="lateFee" name="lateFee" placeholder="Oran yazınız">
            </div>
        </div>
    </div>
</div>
