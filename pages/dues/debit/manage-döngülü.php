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
                            <label for="borclandir_tip" class="fw-semibold">Kime Borçlandırılacak:</label>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-control select2" id="borclandir_tip" name="borclandir_tip" required>
                                <option value="">Seçiniz</option>
                                <option value="tum">Tüm Sakinler</option>
                                <option value="blok">Blok Seç</option>
                                <option value="kisi">Kişi Seç</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label for="borclandir_kisiler" class="fw-semibold">Kişiler:</label>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-control select2" id="borclandir_kisiler" name="borclandir_kisiler[]" multiple required>
                                <?php foreach ($persons as $person): ?>
                                    <option data-blok="<?= $person->block ?>" value="<?= $person->id ?>">
                                        <?= $person->full_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-4 align-items-center">
                        <div class="col-lg-2">
                            <label for="status" class="fw-semibold">Durum:</label>
                        </div>
                        <div class="col-lg-4">
                            <select name="status" id="status" class="form-control select2" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Pasif">Pasif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    const $borclandirTip = $('#borclandir_tip');
    const $kisiler = $('#borclandir_kisiler');

    $borclandirTip.on('change', function () {
        const secim = $(this).val();

        $kisiler.val(null).trigger('change'); // Önce sıfırla

        if (secim === 'tum') {
            // Tüm kişiler seçilsin
            let tumIDs = [];
            $kisiler.find('option').each(function () {
                tumIDs.push($(this).val());
            });
            $kisiler.val(tumIDs).trigger('change');
        }

        else if (secim === 'blok') {
            // Örneğin sadece A Blok seçilsin (geliştirilebilir)
            let blok = 'A Blok';
            let blokIDs = [];

            $kisiler.find('option').each(function () {
                if ($(this).data('blok') === blok) {
                    blokIDs.push($(this).val());
                }
            });
            $kisiler.val(blokIDs).trigger('change');
        }

        else if (secim === 'kisi') {
            // Hiçbiri seçili olmasın, manuel seçim yapılacak
            // (Boş bırakıldı zaten yukarıda)
        }
    });
});
</script>
