<!-- Payment Settings Page -->
<div class="card-body personal-info mb-4">
    <h6 class="mb-3">Ödeme Ayarları</h6>

    <!-- Varsayılan Ödeme Yöntemi -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="defaultPaymentMethod" class="fw-semibold">Varsayılan Ödeme Yöntemi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                <select class="form-select" id="defaultPaymentMethod" name="defaultPaymentMethod">
                    <option value="bankTransfer">Banka Havalesi/EFT</option>
                    <option value="creditCard">Kredi Kartı</option>
                    <option value="cash">Nakit</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Banka Hesap Bilgileri -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="bankAccountName" class="fw-semibold">Banka Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-home"></i></span>
                <input type="text" class="form-control" id="bankAccountName" name="bankAccountName" placeholder="Banka adı yazınız">
            </div>
        </div>
    </div>
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="bankAccountNumber" class="fw-semibold">IBAN:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                <input type="text" class="form-control" id="bankAccountNumber" name="bankAccountNumber" placeholder="IBAN yazınız">
            </div>
        </div>
    </div>

    <!-- Ödeme Bildirim Ayarları -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="paymentReminder" class="fw-semibold">Ödeme Hatırlatmaları:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-bell"></i></span>
                <select class="form-select" id="paymentReminder" name="paymentReminder">
                    <option value="enabled">Aktif</option>
                    <option value="disabled">Pasif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Otomatik Ödeme Ayarı -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="autoPayment" class="fw-semibold">Otomatik Ödeme:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-refresh"></i></span>
                <select class="form-select" id="autoPayment" name="autoPayment">
                    <option value="enabled">Aktif</option>
                    <option value="disabled">Pasif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Komisyon Ücreti -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="transactionFee" class="fw-semibold">Komisyon Ücreti (%):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-percent"></i></span>
                <input type="number" class="form-control" id="transactionFee" name="transactionFee" placeholder="Komisyon oranı yazınız">
            </div>
        </div>
    </div>

    
</div>
