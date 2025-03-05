<?php

require_once "Model/User.php";

$userObj = new User();
$authorities = $userObj->authorities();
?>

<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Yetkileri Düzenle
                    </h2>
                </div>

                <!-- Page title actions -->
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-outline-secondary route-link" data-page="users/list">
                        <i class="ti ti-list icon me-2"></i>
                        Listeye Dön
                    </button>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" id="kullanici_kaydet">
                        <i class="ti ti-device-floppy icon me-2"></i>
                        Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .perm-border {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            margin: 5px;

        }
    </style>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2 mt-3">
                        <?php
                        foreach ($authorities as $authority) {
                        ?>
                            <div class="col-auto">
                                <label class="form-colorinput">
                                    <?php echo $authority->authTitle ?>
                                </label>
                            </div>
                            <div class="perm-border">
                                <div class="col-auto">
                                    <label class="form-colorinput">
                                        <input name="color<?php echo $authority->id; ?>" type="radio" value="red" class="form-colorinput-input">
                                        <span class="form-colorinput-color bg-red"></span>
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <label class="form-colorinput">
                                        <input name="color<?php echo $authority->id; ?>" type="radio" value="orange" class="form-colorinput-input">
                                        <span class="form-colorinput-color bg-orange" style="color:orange"></span>
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <label class="form-colorinput">
                                        <input name="color<?php echo $authority->id; ?>" type="radio" value="yellow" class="form-colorinput-input">
                                        <span class="form-colorinput-color bg-yellow"></span>
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <label class="form-colorinput">
                                        <input name="color<?php echo $authority->id; ?>" type="radio" value="lime" class="form-colorinput-input">
                                        <span class="form-colorinput-color bg-lime"></span>
                                    </label>
                                </div>
                            </div>
                           


                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>