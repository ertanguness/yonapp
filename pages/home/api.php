<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\Gate;
use Model\KisilerModel;

$KisiModel = new KisilerModel();



// value: 1,
//name: "WrapCoders",
//avatar: null,
//email: "wrapcode.info@gmail.com",


if($_POST['action'] == 'get_tags'){
$site_id = Security::decrypt($_POST['site_id'] ?? null);

//Gate::can('email');
$kisiler = $KisiModel->getKisilerForEmail($site_id);

echo json_encode([
   'users' => $kisiler
]);
    exit;
}