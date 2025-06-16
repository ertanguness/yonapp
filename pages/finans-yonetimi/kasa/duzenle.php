

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Kasa Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="finans-yonetimi/kasa/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-primary" id="kasa_kaydet">
                    <i class="feather-save  me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <?php /*
    $title = $pageTitle;
    $text = $pageTitle === 'Yeni Banka Tanımlama'
        ? "Yeni banka bilgisi tanımlayabilirsiniz."
        : "Seçtiğiniz banka bilgilerini güncelleyebilirsiniz.";
    require_once 'pages/components/alert.php'; */
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form action="" id="kasaForm">
                            <div class="card-body custom-card-action p-0">
                                <div class="card-body personal-info">
                                    <div class="row mb-4 align-items-center">
                                        <!-- Hidden Row -->
                                        <div class="row d-none">
                                            <div class="col-md-4">
                                                <input type="text" name="id" class="form-control" value="<?= $bank->id ?? 0 ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="action" value="saveBank" class="form-control">
                                            </div>
                                        </div>
                                        <!-- Hidden Row -->

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Banka Adı:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-credit-card"></i></div>
                                                <input type="text" class="form-control" name="bank_name" value="<?= $bank->bank_name ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">IBAN:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                                <input type="text" class="form-control" name="iban" id="iban" value="<?= $bank->iban ?? 'TR' ?>" maxlength="32" placeholder="IBAN giriniz">
                                                </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Şube Adı:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                                <input type="text" class="form-control" name="branch_name" value="<?= $bank->branch_name ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Şube Kodu:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                                <input type="text" class="form-control" name="branch_code" value="<?= $bank->branch_code ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Hesap Sahibi:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user"></i></div>
                                                <input type="text" class="form-control" name="account_owner" value="<?= $bank->account_owner ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Hesap Numarası:</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                                <input type="text" class="form-control" name="account_number" value="<?= $bank->account_number ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-2">
                                            <label class="fw-semibold">Açıklama:</label>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-info"></i></div>
                                                <textarea class="form-control" name="description" rows="3"><?= $bank->description ?? '' ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 
<script>
    
document.getElementById("saveIncExpType").addEventListener("click", function (e) {
    e.preventDefault();
    const form = document.getElementById("bankForm");
    const iban = form.iban.value.trim().toUpperCase();
    const branchCode = form.branch_code.value.trim();
    const accNumber = form.account_number.value.trim();

    // Zorunlu alanlar kontrolü
    const requiredFields = [
        { name: "bank_name", label: "Banka Adı" },
        { name: "iban", label: "IBAN" },
        { name: "branch_name", label: "Şube Adı" },
        { name: "branch_code", label: "Şube Kodu" },
        { name: "account_owner", label: "Hesap Sahibi" },
        { name: "account_number", label: "Hesap Numarası" }
    ];

    for (let field of requiredFields) {
        if (!form[field.name].value.trim()) {
            toastr.error(`${field.label} alanı boş bırakılamaz!`);
            form[field.name].focus();
            return false;
        }
    }

    // IBAN doğrulama
    if (!/^TR\d{2}[0-9A-Z]{0,30}$/.test(iban)) {
        toastr.error("Geçerli bir IBAN giriniz. (TR ile başlamalı ve 26-34 karakter olmalı)");
        form.iban.focus();
        return false;
    }

    // Şube kodu ve hesap numarası yalnızca rakam içermeli
    if (!/^\d+$/.test(branchCode)) {
        toastr.error("Şube kodu sadece rakamlardan oluşmalıdır.");
        form.branch_code.focus();
        return false;
    }

    if (!/^\d+$/.test(accNumber)) {
        toastr.error("Hesap numarası sadece rakamlardan oluşmalıdır.");
        form.account_number.focus();
        return false;
    }

    // Başarılıysa submit
    form.submit();
});
</script>
<script>
document.querySelector('[name="iban"]').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase(); // İlgili karakterleri alıp boşlukları temizle ve büyük harfe çevir
    let formatted = value.match(/.{1,4}/g)?.join(' ') || ''; // 4 karakterlik gruplar halinde ayır
    e.target.value = formatted; // Formatlanmış değeri input'a uygula
});

document.getElementById("iban").addEventListener("input", function (e) {
    let value = e.target.value.toUpperCase(); // Kullanıcının girdiği değeri al
    if (!value.startsWith("TR")) { // "TR" başta olmalı
        value = "TR" + value.replace(/^.{0,2}/, ''); // "TR" kısmını ekleyip fazlalıkları temizle
    }

    // IBAN'ın 26 karakteri geçmesini engelle
    if (value.length > 26) {
        value = value.slice(0, 32); // En fazla 26 karakter
    }

    e.target.value = value; // Güncellenmiş değeri input'a uygula
});

</script> -->

