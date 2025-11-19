<?php \App\Services\Gate::authorizeOrDie('complaints_peoples_page'); ?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Şikayet / Öneri Bildirimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Şikayet / Öneri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">
           
            <button type="submit" class="btn btn-primary" id="saveComplaint">
                <i class="feather-send me-2"></i> Gönder
            </button>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <form id="complaintForm">
                        <div class="card-body">
                            <!-- Başlık -->
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">Başlık <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" class="form-control" placeholder="Kısa bir başlık giriniz..." required>
                            </div>

                            <!-- Tür -->
                            <div class="mb-4">
                                <label for="type" class="form-label fw-semibold">Konu Türü <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Şikayet">Şikayet</option>
                                    <option value="Öneri">Öneri</option>
                                    <option value="Talep">Talep</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>

                            <!-- Açıklama -->
                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">Açıklama <span class="text-danger">*</span></label>
                                <textarea id="message" name="message" class="form-control" rows="5" placeholder="Detaylı açıklama giriniz..." required></textarea>
                            </div>

                            <!-- Dosya Ekle -->
                            <div class="mb-4">
                                <label for="attachment" class="form-label fw-semibold">Dosya Ekle (isteğe bağlı)</label>
                                <input type="file" id="attachment" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">PDF veya görsel (jpg, png) yükleyebilirsiniz.</small>
                            </div>

                          
                        </div>
                    </form>

                    <!-- Bilgilendirme -->
                    <div id="formResult" class="alert alert-success m-3 d-none">
                        <i class="feather-check-circle me-2"></i> Bildiriminiz başarıyla iletildi!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript (Sadece tasarım için simülasyon) -->
<script>
document.getElementById("complaintForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action','complaint_save');
    fetch('/pages/duyuru-talep/peoples/api.php', { method:'POST', body: fd })
      .then(r=>r.json())
      .then(data=>{
        var title = data.status === 'success' ? 'Başarılı' : 'Hata';
        swal.fire({ title, text: data.message, icon: data.status, confirmButtonText:'Tamam' });
        if(data.status==='success') { this.reset(); }
      });
});
</script>
