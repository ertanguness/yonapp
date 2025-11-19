<?php 
use App\Controllers\AuthController;
AuthController::checkAuthentication();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Yeni Şikayet / Öneri</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Taleplerim</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <a href="index?p=sakin/sikayet-oneri-listem" class="btn btn-outline-secondary">
            <i class="feather-arrow-left me-2"></i> Listeye Dön
        </a>
    </div>
    </div>

<div class="main-content">
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Form</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tür</label>
                                    <select class="form-select" id="inpType">
                                        <option value="Şikayet">Şikayet</option>
                                        <option value="Öneri">Öneri</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Başlık</label>
                                    <input type="text" class="form-control" id="inpTitle" placeholder="Kısa başlık">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">İçerik</label>
                                    <textarea class="form-control" id="inpContent" rows="6" placeholder="Detaylı açıklama"></textarea>
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <button class="btn btn-secondary" onclick="history.back()">İptal</button>
                                <button class="btn btn-primary" id="btnSubmit">Gönder</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
  $('#btnSubmit').on('click', function(){
    const type = $('#inpType').val();
    const title = $('#inpTitle').val().trim();
    const content = $('#inpContent').val().trim();
    if(!title || !content){
      swal.fire({ title:'Hata', text:'Başlık ve içerik zorunludur', icon:'error' });
      return;
    }
    const fd = new FormData();
    fd.append('action','create');
    fd.append('type', type);
    fd.append('title', title);
    fd.append('content', content);
    fetch('/pages/duyuru-talep/users/api/APISikayet_oneri.php', { method:'POST', body: fd })
      .then(r=>r.json())
      .then(data=>{
        var titleMsg = data.status === 'success' ? 'Başarılı' : 'Hata';
        swal.fire({ title: titleMsg, text: data.message, icon: data.status, confirmButtonText:'Tamam' })
          .then(()=>{ if(data.status==='success'){ window.location.href = 'index?p=sakin/sikayet-oneri-listem'; } });
      });
  });
});
</script>