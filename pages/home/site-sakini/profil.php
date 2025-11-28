<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Profil & Ayarlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Profil</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bilgiler</h5>
                </div>
                <div class="card-body">
                    <a href="/profile" class="btn btn-light">Profil Düzenle</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card rounded-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tema</h5>
                </div>
                <div class="card-body d-flex gap-2">
                    <button type="button" class="btn btn-light" id="btnLightTheme">Açık Tema</button>
                    <button type="button" class="btn btn-dark" id="btnDarkTheme">Koyu Tema</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var btnLight = document.getElementById('btnLightTheme');
    var btnDark = document.getElementById('btnDarkTheme');
    if (btnLight) btnLight.addEventListener('click', function(){
        localStorage.setItem('app-skin', '');
        document.documentElement.classList.remove('app-skin-dark');
    });
    if (btnDark) btnDark.addEventListener('click', function(){
        localStorage.setItem('app-skin', 'app-skin-dark');
        document.documentElement.classList.add('app-skin-dark');
    });
});
</script>