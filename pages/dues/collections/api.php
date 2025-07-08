<?php 
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\TahsilatDetayModel;
use App\Helper\Date;



// action: 'get_tahsilat_detaylari'
if (isset($_POST['action']) && $_POST['action'] == 'get_tahsilat_detaylari') {
    header('Content-Type: application/json');

    try {
        if (empty($_POST['id'])) {
            throw new Exception("Tahsilat ID'si gönderilmedi.");
        }

        $tahsilatId = Security::decrypt($_POST['id']);

        // TahsilatDetayModel'iniz olduğunu varsayıyorum
        $tahsilatDetayModel = new TahsilatDetayModel();
        // Bu metot, bir tahsilat ID'si alıp tüm detaylarını getirmeli
        $detaylar = $tahsilatDetayModel->getDetaylarForList($tahsilatId);

        echo json_encode(['status' => 'success', 'data' => $detaylar]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}


