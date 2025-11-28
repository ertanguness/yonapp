<?php

use App\Helper\Security;
use Model\UserModel;

$User = new UserModel();

// Type paramatresi Route ile geliyor

    if (isset($type) && $type != 0) {
        $users = $User->getUsers($type);
    } else {
        $users = $User->getUsers();
    };
?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Kullanıcılar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Kullanıcı Listesi</li>
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
                <?php
                require_once 'pages/components/search.php';
                require_once 'pages/components/download.php'
                ?>

                <a href="kullanici-ekle" class="btn btn-primary route-link">
                    <i class="feather-plus me-2"></i>
                    <span>Yeni Kullanıcı</span>
                </a>
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
    <?php
    $title = "Kullanıcı Listesi!";
    $text = "Seçili site için dilediğiniz kadar kullanıcı ekleyebilir ve bu
                    kullanıcılara istediğiniz yetkileri verebilirsiniz.
                    Hesap oluşturma aşamasında oluşturulan kullanıcı silinemez!";
    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive">
                                <table class="table table-hover datatables" id="userTable">
                                    <thead>
                                        <tr>
                                            <th style="width:7%">Sıra</th>
                                            <th style="width:10%">Pozisyon</th>
                                            <th>Adı Soyadı</th>
                                            <th style="width:20%">Email</th>
                                            <th style="width:10%">Telefon</th>
                                            <th style="width:10%">Ana Kullanıcı</th>
                                            <th style="width:7%">Durum</th>
                                            <th style="width:7%">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        <?php
                                        $i = 1;
                                        foreach ($users as $user):
                                            $id = Security::encrypt($user->id);
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i; ?></td>
                                                <td><?php echo $user->role_name ; ?></td>
                                                <td><?php echo $user->full_name; ?></td>
                                                <td><?php echo $user->email; ?></td>
                                                <td class="text-start"><?php echo $user->phone; ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    if ($user->is_main_user == 1) {
                                                        echo "<i class='ti ti-check text-success fs-24'></i>";
                                                    }

                                                    ?>
                                                </td>
                                                <td><?php echo $user->status; ?></td>
                                                <td>
                                                    <div class="hstack gap-2 ">
                                                        <a href="kullanici-duzenle?id=<?php echo $id ?>"
                                                            class="avatar-text avatar-md" >
                                                            <i class="feather-edit"></i>
                                                        </a>
                                                        <?php if ($user->is_main_user != 1) { ?>
                                                            <a href="javascript:void(0);"
                                                                class="avatar-text avatar-md kullanici-sil"
                                                                data-id="<?php echo $id ?>">
                                                                <i class="feather-trash-2"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $i++;
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>