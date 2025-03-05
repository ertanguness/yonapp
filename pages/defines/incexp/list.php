<?php
require_once "App/Helper/helper.php";
require_once "App/Helper/date.php";
require_once "Model/DefinesModel.php";

use App\Helper\Helper;
use App\Helper\Date;

$defines = new DefinesModel();

$items = $defines->getIncExpTypesByFirm();

$user_id = $_SESSION['user']->id;


?>
<div class="container-xl mt-3">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gelir/Gider Türü Listesi</h3>
                    <div class="col-auto ms-auto">
                        <a href="#" class="btn btn-primary route-link" data-page="defines/incexp/manage">
                            <i class="ti ti-plus icon me-2"></i> Yeni
                        </a>
                    </div>
                </div>


                <div class="table-responsive">
                    <table class="table card-table text-nowrap datatable">
                        <thead>
                            <tr>
                                <th style="width:7%">ID</th>
                                <th>Adı</th>
                                <th>Türü</th>
                                <th>Açıklama</th>
                                <th>Eklenme Tarihi</th>
                                <th>İşlem</th>

                            </tr>
                        </thead>
                        <tbody>


                            <?php foreach ($items as $item) :
                            ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><?php echo $item->name; ?></td>
                                    <td><?php echo Helper::getIncExpTypeName($item->type_id); ?></td>
                                    <td><?php echo $item->description; ?></td>
                                    <td><?php echo Date::dmY($item->created_at); ?></td>


                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">İşlem</button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item route-link" data-page="defines/incexp/manage&id=<?php echo $item->id ?>" href="#">
                                                    <i class="ti ti-edit icon me-3"></i> Güncelle
                                                </a>
                                                <a class="dropdown-item delete-incexp" href="#" data-id="<?php echo $item->id ?>">
                                                    <i class="ti ti-trash icon me-3"></i> Sil
                                                </a>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>