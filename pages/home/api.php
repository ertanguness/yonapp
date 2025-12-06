<?php
require_once dirname(__DIR__, levels: 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\Gate;
use Model\KisilerModel;
use Model\UserDashBoardModel;

$KisiModel = new KisilerModel();
$UserDashBoardModel = new UserDashBoardModel();



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



if($_POST['action'] == 'save_dashboard_order'){


    /** test için gelen veriyi gönder */
    //echo json_encode($_POST);exit;



    $user_id = $_SESSION['user']->id ?? 0;
    $order = $_POST['order'] ?? [];
    if (is_string($order)) {
        $decoded = json_decode($order, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $order = $decoded;
        }
    }
    if(is_array($order)){
        if (!empty($order) && is_array($order[0])) {
            $order = array_values(array_map(function($item){ return $item['widget_key'] ?? $item; }, $order));
        }
        $order = array_values(array_filter($order, function($v){ return is_string($v) && $v !== ''; }));
        $UserDashBoardModel->saveUserDashboardOrder($user_id, $order);
        echo json_encode([
            'status' => 'success',
            'message' => 'Dashboard order saved'
        ]);
        exit;
    }else{
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user or order'
        ]);
        exit;
    }
}

if($_POST['action'] == 'save_dashboard_layout'){
    $user_id = $_SESSION['user']->id ?? 0;
    $items = $_POST['items'] ?? [];
    if (is_string($items)) {
        $decoded = json_decode($items, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $items = $decoded;
        }
    }
    if (is_array($items)) {
        $norm = [];
        foreach ($items as $it) {
            if (is_array($it)) {
                $k = $it['widget_key'] ?? null;
                if (is_string($k) && $k !== '') {
                    $norm[] = [
                        'widget_key' => $k,
                        'position' => (int)($it['position'] ?? 0),
                        'column' => (int)($it['col'] ?? ($it['column'] ?? 1)),
                    ];
                }
            }
        }
        $UserDashBoardModel->saveUserDashboardLayout($user_id, $norm);
        echo json_encode([
            'status' => 'success',
            'message' => 'Dashboard layout saved'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid layout payload'
        ]);
        exit;
    }
}


