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
                    <option value="bankTransfer">Banka Havalesi / EFT</option>
                    <option value="creditCard">Kredi Kartı</option>
                    <option value="cash">Nakit</option>
                </select>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="mb-3"><i class="fa fa-university me-2 text-primary"></i>Banka Hesap Bilgileri</h6>

    <!-- Hesap Sahibi Adı -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="accountHolder" class="fw-semibold">Hesap Sahibi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" id="accountHolder" name="accountHolder" placeholder="Hesap sahibinin adını giriniz">
            </div>
        </div>
    </div>

    <!-- Banka Adı -->
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

    <!-- Şube Adı -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="branchName" class="fw-semibold">Şube Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-building"></i></span>
                <input type="text" class="form-control" id="branchName" name="branchName" placeholder="Şube adını yazınız">
            </div>
        </div>
    </div>

    <!-- Şube Kodu -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="branchCode" class="fw-semibold">Şube Kodu:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-code"></i></span>
                <input type="text" class="form-control" id="branchCode" name="branchCode" placeholder="Kodu">
            </div>
        </div>
    </div>

    <!-- IBAN -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="bankAccountNumber" class="fw-semibold">IBAN:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                <input type="text" class="form-control" id="bankAccountNumber" name="bankAccountNumber" placeholder="TR00 0000 0000 0000 0000 0000 00">
            </div>
        </div>
    </div>

    <!-- Hesap Numarası -->
    <div class="row mb-3 align-items-center">
        <div class="col-lg-2">
            <label for="accountNumber" class="fw-semibold">Hesap Numarası:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-list-ol"></i></span>
                <input type="text" class="form-control" id="accountNumber" name="accountNumber" placeholder="Hesap numarasını giriniz">
            </div>
        </div>
    </div>

   

    <!-- Açıklama -->
    <div class="row mb-3 align-items-start">
        <div class="col-lg-2">
            <label for="bankDescription" class="fw-semibold">Açıklama:</label>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-comment-dots"></i></span>
                <textarea class="form-control" id="bankDescription" name="bankDescription" rows="3" placeholder="Ek bilgi veya açıklama yazabilirsiniz"></textarea>
            </div>
        </div>
    </div>

    <!-- Kaydet Butonu -->
    <div class="row">
        <div class="col-lg-6 offset-lg-2">
            <button type="button" class="btn btn-primary px-4">
                <i class="fa fa-save me-2"></i>Kaydet
            </button>
        </div>
    </div>
</div>
