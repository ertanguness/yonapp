<!-- Fee Settings Page -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3">Aidat Ayarları</h6>

    <!-- Aidat Tutarı -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="monthlyFee" class="fw-semibold">Aylık Aidat Tutarı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <input type="number" class="form-control" id="monthlyFee" name="monthlyFee" placeholder="Aidat tutarı yazınız" required>
            </div>
        </div>
    </div>

    <!-- Geç Ödeme Faizi -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="lateFeeRate" class="fw-semibold">Geç Ödeme Faizi (%):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-percent"></i>
                </div>
                <input type="number" class="form-control" id="lateFeeRate" name="lateFeeRate" placeholder="Faiz oranı yazınız" required>
            </div>
        </div>
    </div>

    <!-- Varsayılan Ödeme Döngüsü -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="defaultPaymentCycle" class="fw-semibold">Ödeme Döngüsü:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <select class="form-select" id="defaultPaymentCycle" name="defaultPaymentCycle">
                    <option value="monthly">Aylık</option>
                    <option value="quarterly">Üç Aylık</option>
                    <option value="yearly">Yıllık</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Aidat Artışı Otomasyonu -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="feeIncreaseRate" class="fw-semibold">Aidat Artış Oranı (%):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-chart-line"></i>
                </div>
                <input type="number" class="form-control" id="feeIncreaseRate" name="feeIncreaseRate" placeholder="Yıllık artış oranı yazınız">
            </div>
        </div>
    </div>

    <!-- Son Ödeme Tarihi -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="dueDate" class="fw-semibold">Son Ödeme Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <input type="date" class="form-control" id="dueDate" name="dueDate" required>
            </div>
        </div>
    </div>

 
</div>
