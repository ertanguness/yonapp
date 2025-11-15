<?php

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Services\MailGonderService;



if($_POST['action'] == 'email_gonder'){
    $toEmail = json_decode($_POST['to'] ?? '', true);
    $cc = json_decode($_POST['cc'] ??'', true);
    $bcc = json_decode($_POST['bcc'] ??'', true);
    $konu = $_POST['subject'] ?? 'Varsayılan Konu';
    $mesaj = $_POST['message'] ?? 'Varsayılan Mesaj';
    //mail adreslerini array olarak al
    $toEmail = is_array($toEmail) ? $toEmail : [$toEmail];

if(!is_array($toEmail) || empty($toEmail)){
    throw new Exception("Geçerli bir alıcı listesi gönderilmedi.");
}


    if(MailGonderService::gonder(
         $toEmail,
         $konu,
         $mesaj,
            [],
            $cc,
            $bcc
        )){
            // Başarılı gönderim işlemi
            $status = "success";
            $message = count($toEmail) . " alıcıya email başarıyla gönderildi.";
            try {
                $pdo = getDbConnection();
                $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(10) NOT NULL,
                    recipients TEXT NOT NULL,
                    subject VARCHAR(255) NULL,
                    message TEXT NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
                $ins = $pdo->prepare("INSERT INTO notifications (type, recipients, subject, message, status) VALUES (?,?,?,?,?)");
                $ins->execute(['email', json_encode($toEmail, JSON_UNESCAPED_UNICODE), $konu, $mesaj, 'success']);
            } catch (Exception $e) {}
            
    } else {
        $status = "error";
        $message = "Email gönderilemedi.";
        //throw new Exception("Email gönderilemedi.");
    }

    $res = [
        'status' => $status,
        'message'=> $message,
        'tomail' => $toEmail
 
    ];

    
    echo json_encode($res);
    exit;

}
// file_put_contents('php://input', $json_data);