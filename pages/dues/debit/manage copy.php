<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ekle</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="dues/debit/list">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </button>
            <button type="button" class="btn btn-primary" id="debit_kaydet">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    /* $title = $pageTitle;
 if ($pageTitle === 'Borç Ekle') {
      $text = "Borç Ekleme sayfasındasınız. Bu sayfada yeni bir borç ekleyebilirsiniz.";
 } else {
      $text = "Borç Güncelleme sayfasındasınız. Bu sayfada borç bilgilerini güncelleyebilirsiniz.";
 }
 require_once 'pages/components/alert.php'; */
    ?>
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form id="debtForm" action="" method="POST">
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label for="title" class="fw-semibold">Borç Başlığı:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                <input type="text" class="form-control" name="title" id="title" placeholder="Borç başlığı giriniz" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label for="amount" class="fw-semibold">Tutar (₺):</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                <input type="number" class="form-control" name="amount" id="amount" placeholder="Tutar giriniz" required step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label for="due_date" class="fw-semibold">Son Ödeme Tarihi:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                <input type="date" class="form-control" name="due_date" id="due_date" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label for="penalty" class="fw-semibold">Ceza Oranı (%):</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                <input type="number" class="form-control" name="penalty" id="penalty" placeholder="Ceza oranı" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label for="target_type" class="fw-semibold">Kime Borçlandırılacak:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-users"></i></div>
                                <select name="target_type" id="target_type" class="form-control select2-single" required>
                                    <option value="">Seçiniz</option>
                                    <option value="all">Tüm Sakinler</option>
                                    <option value="block">Blok Seç</option>
                                    <option value="person">Kişi Seç</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label for="target_person" class="fw-semibold">Kişi(ler):</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                <select class="form-control select2-multiple" name="target_person[]" id="target_person" multiple>
                                    <?php foreach ($persons as $person): ?>
                                        <option value="<?= $person->id ?>"><?= $person->full_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label for="status" class="fw-semibold">Durum:</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group flex-nowrap w-100">
                                <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                <select name="status" id="status" class="form-control select2" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Pasif">Pasif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        // Select2 başlatma
        $('.select2-single').select2({
            placeholder: 'Seçiniz',
            width: '100%',
            minimumResultsForSearch: Infinity // "Seçiniz" gibi kısa listelerde arama gizle
        });

        $('.select2-multiple').select2({
            placeholder: 'Kişi seçiniz',
            width: '100%'
        });

        // Seçim türüne göre davranış
        $('#target_type').on('change', function () {
            const type = $(this).val();
            const $targetPerson = $('#target_person');

            if (type === 'person') {
                $targetPerson.prop('disabled', false);
                $targetPerson.val(null).trigger('change');
            } else if (type === 'all') {
                const allValues = $targetPerson.find('option').map(function () {
                    return $(this).val();
                }).get();
                $targetPerson.val(allValues).trigger('change');
                $targetPerson.prop('disabled', true);
            } else if (type === 'block') {
                $targetPerson.val(null).trigger('change');
                $targetPerson.prop('disabled', true);
            } else {
                $targetPerson.val(null).trigger('change');
                $targetPerson.prop('disabled', true);
            }
        });
    });
</script>
