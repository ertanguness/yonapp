<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yeni Anket Oluştur</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anket Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="notice/admin/survey-list">
                <i class="feather-arrow-left me-2"></i> Listeye Dön
            </button>
            <button type="submit" class="btn btn-primary" id="saveNotification">
                <i class="feather-send me-2"></i> Yayınla
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="pollForm">
                            <div class="card-body">
                                <!-- Anket Başlığı -->
                                <div class="mb-4">
                                    <label for="pollTitle" class="form-label fw-semibold">Anket Başlığı</label>
                                    <input type="text" class="form-control" id="pollTitle" placeholder="Anket başlığını giriniz..." required>
                                </div>

                                <!-- Açıklama (opsiyonel) -->
                                <div class="mb-4">
                                    <label for="pollDescription" class="form-label fw-semibold">Açıklama (Opsiyonel)</label>
                                    <textarea class="form-control" id="pollDescription" rows="3" placeholder="Açıklama giriniz..."></textarea>
                                </div>

                                <!-- Seçenekler -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Oylama Seçenekleri</label>
                                    <div id="optionsWrapper">
                                        <div class="input-group mb-2">
                                            <input type="text" name="options[]" class="form-control" placeholder="Seçenek 1" required>
                                            <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="text" name="options[]" class="form-control" placeholder="Seçenek 2" required>
                                            <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary mt-2" id="addOption">+ Seçenek Ekle</button>
                                </div>

                                <!-- Bitiş Tarihi -->
                                <div class="mb-4">
                                    <label for="pollEndDate" class="form-label fw-semibold">Bitiş Tarihi</label>
                                    <input type="date" class="form-control" id="pollEndDate" required>
                                </div>

                                <!-- Yayınla Butonu -->
                               
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS: Seçenek Ekle / Sil -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const addBtn = document.getElementById('addOption');
    const wrapper = document.getElementById('optionsWrapper');

    addBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');
        div.innerHTML = `
            <input type="text" name="options[]" class="form-control" placeholder="Yeni Seçenek" required>
            <button type="button" class="btn btn-outline-danger removeOption">Sil</button>
        `;
        wrapper.appendChild(div);
    });

    wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeOption')) {
            e.target.closest('.input-group').remove();
        }
    });
});
</script>
