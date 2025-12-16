<?php


use Model\UserModel;
use App\Services\Gate;
use App\Helper\Helper;
use App\Helper\Security;
use App\Helper\UserHelper;


$User = new UserModel();
$UserHelper = new UserHelper();

//Sayfa başlarında eklenecek alanlar
Gate::authorizeOrDie("kullanici_ekle_guncelle_sil");


$id =  Security::decrypt($id) ?? 0;
$new_id = $id;

//Eğer url'den id yazılmışsa veya id boş ise projeler sayfasına gider
if ($id == null && isset($_GET['id'])) {
    header("Location: /kullanicilar");
    exit;
}
$user = $User->find($id);
$site_ids = $user ? json_decode($user->siteler_ids ?? '[]', true) : [];

// Helper::dd($site_ids);
//$Auths->checkFirm();
?>



<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Kullanıcılar </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Yeni Kullanıcı Ekle</li>
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

                <a href="/superadmin-kullanicilar" type="button" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="userSaveBtn">
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
<div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0">
    <div class="d-md-none d-flex align-items-center justify-content-between">
        <a href="javascript:void(0)" class="page-content-left-open-toggle">
            <i class="feather-align-left fs-20"></i>
        </a>
        <a href="javascript:void(0)" class="page-content-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
    <div class="d-flex align-items-center justify-content-between">
        <div class="nav-tabs-wrapper page-content-left-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-left-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <ul class="nav nav-tabs nav-tabs-custom-style" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#proposalTab">
                    <i class="feather-home"></i>    
                    Genel Bilgiler</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#loginsTab">
                    <i class="feather-log-in"></i>    
                    Giriş Kayıtları</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notesTab">
                    <i class="feather-file-text"></i>    
                    Aktiviteler</button>
                </li>
               
            </ul>
        </div>
        <div class="page-content-right-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
         
        </div>
    </div>
</div>




<div class="main-content">
    <div class="tab-content">
        <div class="tab-pane fade active show" id="proposalTab">
            <?php

            $title = 'Yeni Kullanıcı Ekleme/Super Admin';
            $text = "Gerekli bilgileri girerek yeni kullanıcı ekleyebilir ve yetkilendirebilirsiniz.";

            require_once 'pages/components/alert.php'
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <form action="" id="userForm">
                                <?php require_once "content/0-home.php" ?>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="tab-pane fade" id="loginsTab" data-user-id="<?php echo (int)$id; ?>">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <table id="userLoginsTable" class="table table-striped table-bordered datatables" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Giriş Zamanı</th>
                                        <th>Çıkış Zamanı</th>
                                        <th>IP</th>
                                        <th>Tarayıcı</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="notesTab">

        </div>
 
    </div>
</div>
<script>
   

    // Kullanım örneği:
    //setupShortcut('s', function() {
        // Burada yapılacak işlemi tanımlıyoruz
       // $('#userSaveBtn').trigger('click');
    //});

    document.addEventListener('shown.bs.tab', function (e) {
        var target = e.target && e.target.getAttribute('data-bs-target');
        if (target === '#loginsTab') {
            if (!$('#userLoginsTable').hasClass('dataTable')) {
                const userId = document.getElementById('loginsTab').dataset.userId || 0;
                $('#userLoginsTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '/pages/kullanici/api/logins.php',
                        type: 'GET',
                        data: function(d){ d.user_id = userId; }
                    },
                    columns: [
                        { data: 0 },
                        { data: 1 },
                        { data: 2 },
                        { data: 3 }
                    ],
                    order: [[0, 'desc']],
                    responsive: true,
                });
            }
        }
    });
</script>